<?php

namespace App\Http\Controllers;

use App\Models\NilaiUnsur;
use App\Support\BagianOptions;
use App\Support\PpmCatalog;
use App\Support\SurveyOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin page for entering respondent data by hand.
 * The page works offline: it keeps entries on the device and sends them
 * to the JSON endpoints below once the connection is back.
 *
 * Alurnya sama dengan survei publik: desa -> program -> rincian -> perusahaan
 * pelaksana (atau "Tidak tahu") -> identitas -> 9 unsur. Perusahaan yang dinilai
 * ditentukan dengan aturan yang sama (PpmCatalog::attribute).
 */
class AdminRespondenController extends Controller
{
    /** Pilihan jumlah baris per halaman; yang pertama dipakai sebagai default. */
    private const PER_PAGE = [10, 25, 50];

    private const IDENTITAS = ['nama', 'nohp', 'alamat', 'jenkel', 'usia', 'pendidikan', 'pekerjaan', 'jenis_responden', 'saran'];

    public function page()
    {
        return view('admin.input-responden', [
            'catalog' => $this->allowedCatalog(),
            'tidakTahu' => PpmCatalog::TIDAK_TAHU,
            'jenkelOptions' => SurveyOptions::JENKEL,
            'usiaOptions' => SurveyOptions::USIA,
            'pendidikanOptions' => SurveyOptions::PENDIDIKAN,
            'pekerjaanOptions' => SurveyOptions::PEKERJAAN,
            'jenisRespondenOptions' => SurveyOptions::JENIS_RESPONDEN,
        ]);
    }

    /** Fresh CSRF token, used by the page before syncing (the cached page may hold an old one). */
    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    /**
     * Satu halaman daftar (default 10 baris). Data hanya dimuat per halaman saat dibuka,
     * tidak sekaligus semuanya.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', self::PER_PAGE[0]);
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = $this->scopedQuery()->orderByDesc('created_at')->orderByDesc('id');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('nohp', 'like', "%{$search}%");
            });
        }

        $page = $query->paginate($perPage);

        return response()->json([
            'data' => collect($page->items())->map(fn ($row) => $this->present($row)),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'from' => $page->firstItem(),
                'to' => $page->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        // Retried sync after a dropped connection: return the record we already have.
        if (! empty($data['client_uuid'])) {
            $existing = NilaiUnsur::query()->where('client_uuid', $data['client_uuid'])->first();
            if ($existing) {
                return response()->json(['data' => $this->present($existing), 'duplicate' => true]);
            }
        }

        $record = new NilaiUnsur();
        $this->fill($record, $data);
        $record->client_uuid = $data['client_uuid'] ?? null;
        $record->save();

        return response()->json(['data' => $this->present($record)], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = $this->scopedQuery()->find($id);
        if (! $record) {
            return response()->json(['message' => 'Data tidak ditemukan di sistem (mungkin sudah dihapus).'], 404);
        }

        $this->fill($record, $this->validated($request));
        $record->save();

        return response()->json(['data' => $this->present($record)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $record = $this->scopedQuery()->find($id);
        if ($record) {
            $record->delete();
        }

        // Already gone counts as success: the goal (record removed) is met.
        return response()->json(['deleted' => true]);
    }

    private function validated(Request $request): array
    {
        $catalog = $this->allowedCatalog();

        $rules = [
            'client_uuid' => ['nullable', 'string', 'max:36'],
            'responden_uuid' => ['nullable', 'string', 'max:36'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'desa_id' => ['required', 'integer', Rule::in(array_keys($catalog['desa']))],
            'rincian_id' => ['required', 'string', Rule::in(array_keys($catalog['rincian']))],
            'perusahaan_jawaban' => ['required', 'string', 'max:12'],
            'jenis_responden' => ['required', Rule::in(SurveyOptions::JENIS_RESPONDEN)],
            'nama' => ['required', 'string', 'max:150'],
            'nohp' => ['nullable', 'string', 'max:16'],
            'alamat' => ['nullable', 'string', 'max:300'],
            'jenkel' => ['required', Rule::in(SurveyOptions::JENKEL)],
            'usia' => ['required', Rule::in(array_keys(SurveyOptions::USIA))],
            'pendidikan' => ['required', Rule::in(SurveyOptions::PENDIDIKAN)],
            'pekerjaan' => ['required', Rule::in(SurveyOptions::PEKERJAAN)],
            'saran' => ['nullable', 'string', 'max:600'],
        ];
        foreach (range(1, 9) as $i) {
            $rules["u{$i}"] = ['required', 'integer', 'between:1,4'];
        }

        $attributes = [
            'tanggal' => 'Tanggal survei', 'desa_id' => 'Desa', 'rincian_id' => 'Rincian program',
            'perusahaan_jawaban' => 'Perusahaan pelaksana', 'jenis_responden' => 'Jenis responden',
            'nohp' => 'No. HP', 'jenkel' => 'Jenis kelamin',
        ];
        foreach (range(1, 9) as $i) {
            $attributes["u{$i}"] = "Unsur {$i}";
        }

        $data = $request->validate($rules, [
            'required' => ':attribute wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal survei tidak boleh melebihi hari ini.',
            'date' => ':attribute bukan tanggal yang valid.',
            'in' => ':attribute tidak valid.',
            'between' => ':attribute harus bernilai 1 sampai 4.',
            'integer' => ':attribute harus berupa angka.',
            'max' => ':attribute terlalu panjang.',
        ], $attributes);

        // Perusahaan harus pelaksana rincian ini di desa tersebut (atau "Tidak tahu")
        $atribusi = PpmCatalog::attribute($data['rincian_id'], (int) $data['desa_id'], $data['perusahaan_jawaban']);
        if ($atribusi === null) {
            throw ValidationException::withMessages([
                'perusahaan_jawaban' => 'Perusahaan pelaksana tidak sesuai dengan program di desa ini.',
            ]);
        }

        return $data + $atribusi;
    }

    private function fill(NilaiUnsur $record, array $data): void
    {
        $date = Carbon::parse($data['tanggal']);
        // Keep the time of day of the original entry (or now) so ordering stays sensible.
        $time = $record->created_at ? Carbon::parse($record->created_at) : now();
        $record->created_at = $date->setTime($time->hour, $time->minute, $time->second);
        $record->tahun = (int) $date->format('Y');

        $programId = PpmCatalog::rincian()[$data['rincian_id']]['program_id'];
        $pilarId = PpmCatalog::program()[$programId]['pilar_id'];

        $record->desa_id = (int) $data['desa_id'];
        $record->pilar_id = $pilarId;
        $record->program_id = $programId;
        $record->rincian_id = $data['rincian_id'];
        $record->jenisPelayanan = PpmCatalog::pilar()[$pilarId]['kode'] ?? null;
        $record->perusahaan_jawaban = $data['perusahaan_jawaban'];
        $record->perusahaan_dinilai = $data['perusahaan_dinilai'];
        $record->sumber_atribusi = $data['sumber_atribusi'];
        $record->perusahaan_opsi = $data['perusahaan_opsi'];
        // Responden yang sama (nilai program lain) berbagi responden_uuid
        $record->responden_uuid = $data['responden_uuid'] ?? $record->responden_uuid ?? (string) Str::uuid();

        // Penginput dicatat saat data dibuat; mengubah data tidak mengganti penginput aslinya
        if (! $record->exists) {
            $record->input_oleh = \App\Support\Akses::userId();
            $record->sumber_input = 'input_admin';
        }

        foreach (self::IDENTITAS as $field) {
            $record->{$field} = $data[$field] ?? null;
        }
        foreach (range(1, 9) as $i) {
            $record->{"u{$i}"} = (int) $data["u{$i}"];
        }
    }

    private function present(NilaiUnsur $row): array
    {
        $out = [
            'id' => $row->id,
            'client_uuid' => $row->client_uuid,
            'responden_uuid' => $row->responden_uuid,
            'tanggal' => $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : null,
            'desa_id' => $row->desa_id,
            'program_id' => $row->program_id,
            'rincian_id' => $row->rincian_id,
            'perusahaan_jawaban' => $row->perusahaan_jawaban,
            'perusahaan_dinilai' => $row->perusahaan_dinilai,
            'penginput' => \App\Support\Akses::penginputLabel($row->input_oleh, $row->sumber_input),
            'updated_at' => $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null,
        ];
        foreach (self::IDENTITAS as $field) {
            $out[$field] = $row->{$field};
        }
        foreach (range(1, 9) as $i) {
            $out["u{$i}"] = $row->{"u{$i}"};
        }

        return $out;
    }

    /** Kode pilar yang boleh diinput admin ini, berdasarkan role saat login. */
    private function allowedPilarCodes(): array
    {
        return BagianOptions::codesForRole(session('keterangan', 'admin'));
    }

    /** Katalog survei (desa, program, rincian, perusahaan) yang dibatasi pilar milik admin. */
    private function allowedCatalog(): array
    {
        return PpmCatalog::forRole(session('keterangan', 'admin'));
    }

    /**
     * Data yang terlihat (dan boleh diubah/dihapus) di halaman ini:
     * - data yang diinput akun ini sendiri, dan
     * - data tanpa keterangan penginput (survei online / data lama) untuk semua akun.
     * Data yang diinput akun lain tidak ditampilkan.
     * Admin dengan role terbatas pilar juga hanya melihat pilarnya.
     */
    private function scopedQuery()
    {
        $userId = \App\Support\Akses::userId();
        $query = NilaiUnsur::query()->where(function ($q) use ($userId) {
            $q->whereNull('input_oleh');
            if ($userId) {
                $q->orWhere('input_oleh', $userId);
            }
        });
        $allowed = $this->allowedPilarCodes();

        if (count($allowed) < count(BagianOptions::codes())) {
            $query->whereIn('jenisPelayanan', $allowed);
        }

        return $query;
    }
}
