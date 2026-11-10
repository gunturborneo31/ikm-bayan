<?php

namespace App\Http\Controllers;

use App\Models\NilaiUnsur;
use App\Support\BagianOptions;
use App\Support\SurveyOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Admin page for entering respondent data by hand.
 * The page works offline: it keeps entries on the device and sends them
 * to the JSON endpoints below once the connection is back.
 */
class AdminRespondenController extends Controller
{
    private const LIST_LIMIT = 200;

    public function page()
    {
        return view('admin.input-responden', [
            'bagianOptions' => $this->allowedBagian(),
            'jenkelOptions' => SurveyOptions::JENKEL,
            'usiaOptions' => SurveyOptions::USIA,
            'pendidikanOptions' => SurveyOptions::PENDIDIKAN,
            'pekerjaanOptions' => SurveyOptions::PEKERJAAN,
        ]);
    }

    /** Fresh CSRF token, used by the page before syncing (the cached page may hold an old one). */
    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->scopedQuery()->orderByDesc('created_at')->orderByDesc('id');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('nohp', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->limit(self::LIST_LIMIT)->get()->map(fn ($row) => $this->present($row)),
            'limit' => self::LIST_LIMIT,
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
        $rules = [
            'client_uuid' => ['nullable', 'string', 'max:36'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'bagian' => ['required', Rule::in(array_keys($this->allowedBagian()))],
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

        $attributes = ['tanggal' => 'Tanggal survei', 'bagian' => 'Pilar', 'nohp' => 'No. HP', 'jenkel' => 'Jenis kelamin'];
        foreach (range(1, 9) as $i) {
            $attributes["u{$i}"] = "Unsur {$i}";
        }

        return $request->validate($rules, [
            'required' => ':attribute wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal survei tidak boleh melebihi hari ini.',
            'date' => ':attribute bukan tanggal yang valid.',
            'in' => ':attribute tidak valid.',
            'between' => ':attribute harus bernilai 1 sampai 4.',
            'integer' => ':attribute harus berupa angka.',
            'max' => ':attribute terlalu panjang.',
        ], $attributes);
    }

    private function fill(NilaiUnsur $record, array $data): void
    {
        $date = Carbon::parse($data['tanggal']);
        // Keep the time of day of the original entry (or now) so ordering stays sensible.
        $time = $record->created_at ? Carbon::parse($record->created_at) : now();
        $record->created_at = $date->setTime($time->hour, $time->minute, $time->second);
        $record->tahun = (int) $date->format('Y');
        $record->jenisPelayanan = $data['bagian'];

        foreach (['nama', 'nohp', 'alamat', 'jenkel', 'usia', 'pendidikan', 'pekerjaan', 'saran'] as $field) {
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
            'tanggal' => $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : null,
            'bagian' => $row->jenisPelayanan,
            'updated_at' => $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null,
        ];
        foreach (['nama', 'nohp', 'alamat', 'jenkel', 'usia', 'pendidikan', 'pekerjaan', 'saran'] as $field) {
            $out[$field] = $row->{$field};
        }
        foreach (range(1, 9) as $i) {
            $out["u{$i}"] = $row->{"u{$i}"};
        }

        return $out;
    }

    /** Bagian (pilar) this admin may use, based on the role saved at login. */
    private function allowedBagian(): array
    {
        $codes = BagianOptions::codesForRole(session('keterangan', 'admin'));

        return array_intersect_key(BagianOptions::codeNameMap(), array_flip($codes));
    }

    /**
     * Full admins see every record (older entries may have no bagian);
     * role-limited admins only see their own bagian.
     */
    private function scopedQuery()
    {
        $query = NilaiUnsur::query();
        $allowed = array_keys($this->allowedBagian());

        if (count($allowed) < count(BagianOptions::codes())) {
            $query->whereIn('jenisPelayanan', $allowed);
        }

        return $query;
    }
}
