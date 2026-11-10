<?php

namespace App\Http\Controllers;

use App\Services\PpmMasterData;
use App\Support\PpmCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Menu "Master Data PPM": desa, perusahaan, pilar -> program -> rincian,
 * serta lokasi pelaksana (perusahaan x desa) tiap rincian.
 *
 * Data yang sudah dipakai penilaian tidak bisa dihapus (hanya dinonaktifkan),
 * supaya laporan lama tetap utuh.
 */
class MasterPpmController extends Controller
{
    private const TABS = ['program', 'desa', 'perusahaan', 'json'];

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'program';

        $desa = DB::table('ppm_desa')->orderBy('urutan')->orderBy('nama')->get();
        $pemakaianDesa = DB::table('survey_responses')->whereNotNull('desa_id')->selectRaw('desa_id, COUNT(*) n')->groupBy('desa_id')->pluck('n', 'desa_id');
        $rincianPerDesa = DB::table('ppm_rincian_lokasi')->whereNotNull('desa_id')->selectRaw('desa_id, COUNT(DISTINCT rincian_id) n')->groupBy('desa_id')->pluck('n', 'desa_id');

        $perusahaan = DB::table('ppm_perusahaan')->orderBy('urutan')->get();
        $pemakaianPerusahaan = DB::table('survey_responses')->whereNotNull('perusahaan_dinilai')->selectRaw('perusahaan_dinilai k, COUNT(*) n')->groupBy('perusahaan_dinilai')->pluck('n', 'k');

        $pilar = DB::table('ppm_pilar')->orderBy('urutan')->get();
        $program = DB::table('ppm_program')->orderBy('urutan')->get()->groupBy('pilar_id');
        $rincian = DB::table('ppm_rincian')->orderBy('urutan')->get()->groupBy('program_id');
        $pemakaianRincian = DB::table('survey_responses')->whereNotNull('rincian_id')->selectRaw('rincian_id, COUNT(*) n')->groupBy('rincian_id')->pluck('n', 'rincian_id');
        $desaPerRincian = DB::table('ppm_rincian_lokasi')->whereNotNull('desa_id')->selectRaw('rincian_id, COUNT(DISTINCT desa_id) n')->groupBy('rincian_id')->pluck('n', 'rincian_id');

        return view('master-ppm.index', compact(
            'tab', 'desa', 'pemakaianDesa', 'rincianPerDesa', 'perusahaan', 'pemakaianPerusahaan',
            'pilar', 'program', 'rincian', 'pemakaianRincian', 'desaPerRincian'
        ));
    }

    // ── Desa ─────────────────────────────────────────────────────────────

    public function storeDesa(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('ppm_desa', 'nama')],
        ], $this->messages(), ['nama' => 'Nama desa']);

        DB::table('ppm_desa')->insert([
            'nama' => trim($data['nama']),
            'urutan' => (int) DB::table('ppm_desa')->max('urutan') + 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->back('desa', "Desa \"{$data['nama']}\" ditambahkan. Atur program yang berjalan di desa ini lewat tab Program → Atur.");
    }

    public function updateDesa(Request $request, int $id)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('ppm_desa', 'nama')->ignore($id)],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->messages(), ['nama' => 'Nama desa']);

        DB::table('ppm_desa')->where('id', $id)->update([
            'nama' => trim($data['nama']),
            'urutan' => (int) ($data['urutan'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ]);

        return $this->back('desa', 'Desa diperbarui.');
    }

    public function destroyDesa(int $id)
    {
        if (DB::table('survey_responses')->where('desa_id', $id)->exists()) {
            return $this->back('desa', 'Desa ini sudah dipakai pada penilaian, jadi tidak dapat dihapus. Nonaktifkan saja agar tidak tampil di survei.', true);
        }

        DB::transaction(function () use ($id) {
            DB::table('ppm_rincian_lokasi')->where('desa_id', $id)->delete();
            DB::table('ppm_desa')->where('id', $id)->delete();
        });

        return $this->back('desa', 'Desa dihapus.');
    }

    // ── Perusahaan ───────────────────────────────────────────────────────

    public function storePerusahaan(Request $request)
    {
        $request->merge(['kode' => strtoupper(trim((string) $request->input('kode')))]);
        $data = $request->validate([
            'kode' => ['required', 'alpha_num', 'max:10', Rule::notIn([PpmCatalog::TIDAK_TAHU]), Rule::unique('ppm_perusahaan', 'kode')],
            'nama' => ['required', 'string', 'max:100'],
        ], $this->messages(), ['kode' => 'Kode', 'nama' => 'Nama perusahaan']);

        DB::table('ppm_perusahaan')->insert([
            'kode' => $data['kode'],
            'nama' => trim($data['nama']),
            'is_grup' => false,
            'urutan' => (int) DB::table('ppm_perusahaan')->max('urutan') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->back('perusahaan', "Perusahaan {$data['kode']} ditambahkan.");
    }

    public function updatePerusahaan(Request $request, string $kode)
    {
        $data = $request->validate(['nama' => ['required', 'string', 'max:100']], $this->messages(), ['nama' => 'Nama perusahaan']);
        DB::table('ppm_perusahaan')->where('kode', $kode)->update(['nama' => trim($data['nama']), 'updated_at' => now()]);

        return $this->back('perusahaan', 'Perusahaan diperbarui.');
    }

    public function destroyPerusahaan(string $kode)
    {
        if ($kode === PpmCatalog::GRUP) {
            return $this->back('perusahaan', 'BAYAN (grup) tidak dapat dihapus.', true);
        }
        $dipakai = DB::table('survey_responses')->where('perusahaan_dinilai', $kode)->orWhere('perusahaan_jawaban', $kode)->exists();
        if ($dipakai) {
            return $this->back('perusahaan', "Perusahaan {$kode} sudah dipakai pada penilaian, jadi tidak dapat dihapus.", true);
        }

        DB::transaction(function () use ($kode) {
            DB::table('ppm_rincian_lokasi')->where('perusahaan_kode', $kode)->delete();
            foreach (DB::table('ppm_rincian')->where('penilai', 'like', "%{$kode}%")->get(['id', 'penilai']) as $r) {
                $sisa = array_values(array_diff(explode(',', (string) $r->penilai), [$kode]));
                DB::table('ppm_rincian')->where('id', $r->id)->update(['penilai' => implode(',', $sisa)]);
            }
            DB::table('ppm_perusahaan')->where('kode', $kode)->delete();
        });

        return $this->back('perusahaan', "Perusahaan {$kode} dihapus.");
    }

    // ── Pilar ────────────────────────────────────────────────────────────

    public function storePilar(Request $request)
    {
        $request->merge(['kode' => Str::slug((string) ($request->input('kode') ?: $request->input('nama')), '-')]);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:40', Rule::unique('ppm_pilar', 'kode')],
        ], $this->messages(), ['nama' => 'Nama pilar', 'kode' => 'Kode pilar']);

        $id = 'P' . ($this->maxSuffix(DB::table('ppm_pilar')->pluck('id'), 'P') + 1);
        DB::table('ppm_pilar')->insert([
            'id' => $id,
            'nama' => trim($data['nama']),
            'kode' => $data['kode'],
            'urutan' => (int) DB::table('ppm_pilar')->max('urutan') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->back('program', "Pilar {$id} ditambahkan.");
    }

    public function updatePilar(Request $request, string $id)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], $this->messages(), ['nama' => 'Nama pilar']);

        // Kode pilar tidak diubah: kode tersimpan di setiap penilaian (jenisPelayanan).
        DB::table('ppm_pilar')->where('id', $id)->update([
            'nama' => trim($data['nama']),
            'urutan' => (int) ($data['urutan'] ?? 0),
            'updated_at' => now(),
        ]);

        return $this->back('program', "Pilar {$id} diperbarui.");
    }

    public function destroyPilar(string $id)
    {
        if (DB::table('ppm_program')->where('pilar_id', $id)->exists()) {
            return $this->back('program', "Pilar {$id} masih berisi program. Hapus atau pindahkan programnya dulu.", true);
        }
        if (DB::table('survey_responses')->where('pilar_id', $id)->exists()) {
            return $this->back('program', "Pilar {$id} sudah dipakai pada penilaian, jadi tidak dapat dihapus.", true);
        }
        DB::table('ppm_pilar')->where('id', $id)->delete();

        return $this->back('program', "Pilar {$id} dihapus.");
    }

    // ── Program ──────────────────────────────────────────────────────────

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'pilar_id' => ['required', Rule::exists('ppm_pilar', 'id')],
            'nama' => ['required', 'string', 'max:255'],
        ], $this->messages(), ['nama' => 'Nama program']);

        $pilarId = $data['pilar_id'];
        $id = $pilarId . '.' . ($this->maxSuffix(DB::table('ppm_program')->where('pilar_id', $pilarId)->pluck('id'), $pilarId . '.') + 1);
        DB::table('ppm_program')->insert([
            'id' => $id,
            'pilar_id' => $pilarId,
            'nama' => trim($data['nama']),
            'urutan' => (int) DB::table('ppm_program')->where('pilar_id', $pilarId)->max('urutan') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->back('program', "Program {$id} ditambahkan.", false, "pilar-{$pilarId}");
    }

    public function updateProgram(Request $request, string $id)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], $this->messages(), ['nama' => 'Nama program']);

        DB::table('ppm_program')->where('id', $id)->update([
            'nama' => trim($data['nama']),
            'urutan' => (int) ($data['urutan'] ?? 0),
            'updated_at' => now(),
        ]);
        $pilarId = DB::table('ppm_program')->where('id', $id)->value('pilar_id');

        return $this->back('program', "Program {$id} diperbarui.", false, "pilar-{$pilarId}");
    }

    public function destroyProgram(string $id)
    {
        $pilarId = DB::table('ppm_program')->where('id', $id)->value('pilar_id');
        if (DB::table('ppm_rincian')->where('program_id', $id)->exists()) {
            return $this->back('program', "Program {$id} masih berisi rincian. Hapus rinciannya dulu.", true, "pilar-{$pilarId}");
        }
        if (DB::table('survey_responses')->where('program_id', $id)->exists()) {
            return $this->back('program', "Program {$id} sudah dipakai pada penilaian, jadi tidak dapat dihapus.", true, "pilar-{$pilarId}");
        }
        DB::table('ppm_program')->where('id', $id)->delete();

        return $this->back('program', "Program {$id} dihapus.", false, "pilar-{$pilarId}");
    }

    // ── Rincian ──────────────────────────────────────────────────────────

    public function storeRincian(Request $request)
    {
        $data = $request->validate([
            'program_id' => ['required', Rule::exists('ppm_program', 'id')],
            'nama' => ['required', 'string', 'max:255'],
        ], $this->messages(), ['nama' => 'Nama rincian']);

        $programId = $data['program_id'];
        $id = $programId . '.' . ($this->maxSuffix(DB::table('ppm_rincian')->where('program_id', $programId)->pluck('id'), $programId . '.') + 1);
        DB::table('ppm_rincian')->insert([
            'id' => $id,
            'program_id' => $programId,
            'nama' => trim($data['nama']),
            'penilai' => '',
            'dinilai_sebagai_grup' => false,
            'is_active' => false, // aktif setelah lokasi & penilai diatur
            'urutan' => (int) DB::table('ppm_rincian')->where('program_id', $programId)->max('urutan') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master-ppm.rincian', $id)
            ->with('success', "Rincian {$id} dibuat. Centang perusahaan pelaksana per desa dan penilainya, lalu aktifkan.");
    }

    public function editRincian(string $id)
    {
        $rincian = DB::table('ppm_rincian')->where('id', $id)->first();
        abort_if(! $rincian, 404);

        $program = DB::table('ppm_program')->where('id', $rincian->program_id)->first();
        $pilar = $program ? DB::table('ppm_pilar')->where('id', $program->pilar_id)->first() : null;
        $desa = DB::table('ppm_desa')->orderBy('urutan')->orderBy('nama')->get();
        $perusahaan = DB::table('ppm_perusahaan')->orderBy('urutan')->get();
        $pelaksana = DB::table('ppm_perusahaan')->where('kode', '!=', PpmCatalog::GRUP)->orderBy('urutan')->get();

        $lokasi = DB::table('ppm_rincian_lokasi')->where('rincian_id', $id)->get();
        $terpilih = [];
        $lokasiLain = [];
        foreach ($lokasi as $row) {
            if ($row->desa_id) {
                $terpilih[$row->perusahaan_kode][$row->desa_id] = true;
            } elseif ($row->lokasi_lain) {
                $lokasiLain[$row->perusahaan_kode][] = $row->lokasi_lain;
            }
        }

        $jumlahPenilaian = DB::table('survey_responses')->where('rincian_id', $id)->count();
        $penilai = array_filter(explode(',', (string) $rincian->penilai));

        return view('master-ppm.rincian', compact(
            'rincian', 'program', 'pilar', 'desa', 'perusahaan', 'pelaksana', 'terpilih', 'lokasiLain', 'jumlahPenilaian', 'penilai'
        ));
    }

    public function updateRincian(Request $request, string $id)
    {
        abort_if(! DB::table('ppm_rincian')->where('id', $id)->exists(), 404);

        $kodes = DB::table('ppm_perusahaan')->pluck('kode')->all();
        $desaIds = DB::table('ppm_desa')->pluck('id')->map(fn ($v) => (int) $v)->all();

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'penilai' => ['nullable', 'array'],
            'penilai.*' => [Rule::in($kodes)],
            'lokasi' => ['nullable', 'array'],
            'lokasi.*' => ['array'],
            'lokasi.*.*' => ['integer', Rule::in($desaIds)],
            'lokasi_lain' => ['nullable', 'array'],
            'lokasi_lain.*' => ['nullable', 'string', 'max:255'],
        ], $this->messages(), ['nama' => 'Nama rincian']);

        $lokasi = array_intersect_key($data['lokasi'] ?? [], array_flip($kodes));
        $adaPelaksana = collect($lokasi)->flatten()->isNotEmpty();
        $grup = $request->boolean('dinilai_sebagai_grup');
        $penilai = array_values(array_unique($data['penilai'] ?? []));
        if ($grup && ! in_array(PpmCatalog::GRUP, $penilai, true)) {
            $penilai[] = PpmCatalog::GRUP;
        }
        $aktif = $request->boolean('is_active');

        if ($aktif && ! $adaPelaksana) {
            return back()->withInput()->withErrors(['is_active' => 'Rincian tidak bisa ditampilkan di survei sebelum ada minimal satu desa pelaksana.']);
        }
        if ($aktif && $penilai === []) {
            return back()->withInput()->withErrors(['penilai' => 'Pilih minimal satu penilai (atau centang "dinilai sebagai BAYAN") sebelum menampilkan di survei.']);
        }

        DB::transaction(function () use ($id, $data, $lokasi, $penilai, $grup, $aktif) {
            DB::table('ppm_rincian')->where('id', $id)->update([
                'nama' => trim($data['nama']),
                'catatan' => $data['catatan'] ?? null,
                'urutan' => (int) ($data['urutan'] ?? 0),
                'penilai' => implode(',', $penilai),
                'dinilai_sebagai_grup' => $grup,
                'is_active' => $aktif,
                'updated_at' => now(),
            ]);

            DB::table('ppm_rincian_lokasi')->where('rincian_id', $id)->delete();
            $rows = [];
            foreach ($lokasi as $kode => $desaList) {
                foreach (array_unique($desaList) as $desaId) {
                    $rows[] = ['rincian_id' => $id, 'perusahaan_kode' => $kode, 'desa_id' => (int) $desaId, 'lokasi_lain' => null];
                }
            }
            foreach ($data['lokasi_lain'] ?? [] as $kode => $teks) {
                foreach (array_filter(array_map('trim', explode(';', (string) $teks))) as $lain) {
                    $rows[] = ['rincian_id' => $id, 'perusahaan_kode' => $kode, 'desa_id' => null, 'lokasi_lain' => $lain];
                }
            }
            if ($rows) {
                DB::table('ppm_rincian_lokasi')->insert($rows);
            }
        });

        return redirect()->route('master-ppm.rincian', $id)->with('success', "Rincian {$id} disimpan.");
    }

    public function destroyRincian(string $id)
    {
        $programId = DB::table('ppm_rincian')->where('id', $id)->value('program_id');
        $pilarId = DB::table('ppm_program')->where('id', $programId)->value('pilar_id');
        if (DB::table('survey_responses')->where('rincian_id', $id)->exists()) {
            return $this->back('program', "Rincian {$id} sudah dipakai pada penilaian, jadi tidak dapat dihapus. Matikan \"Tampilkan di survei\" saja.", true, "pilar-{$pilarId}");
        }

        DB::transaction(function () use ($id) {
            DB::table('ppm_rincian_lokasi')->where('rincian_id', $id)->delete();
            DB::table('ppm_rincian')->where('id', $id)->delete();
        });

        return $this->back('program', "Rincian {$id} dihapus.", false, "pilar-{$pilarId}");
    }

    // ── JSON ─────────────────────────────────────────────────────────────

    public function export()
    {
        $json = json_encode(app(PpmMasterData::class)->export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="master_ppm_' . now()->format('Ymd_His') . '.json"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048'],
            'konfirmasi' => ['accepted'],
        ], [
            'file.required' => 'Pilih file JSON terlebih dahulu.',
            'konfirmasi.accepted' => 'Centang konfirmasi bahwa master data akan diganti.',
        ]);

        try {
            $data = json_decode(file_get_contents($request->file('file')->getRealPath()), true, 512, JSON_THROW_ON_ERROR);
            $summary = app(PpmMasterData::class)->import($data);
        } catch (Throwable $e) {
            return $this->back('json', 'Impor gagal, master data tidak berubah: ' . $e->getMessage(), true);
        }

        return $this->back('json', sprintf(
            'Impor berhasil: %d desa, %d perusahaan, %d pilar, %d program, %d rincian.',
            $summary['desa'], $summary['perusahaan'], $summary['pilar'], $summary['program'], $summary['rincian']
        ));
    }

    // ── Helper ───────────────────────────────────────────────────────────

    private function back(string $tab, string $message, bool $error = false, ?string $anchor = null)
    {
        return redirect(route('master-ppm.index', ['tab' => $tab]) . ($anchor ? "#{$anchor}" : ''))
            ->with($error ? 'error' : 'success', $message);
    }

    /** Angka terbesar setelah prefix, mis. P1.1.7 -> 7 untuk prefix "P1.1.". */
    private function maxSuffix($ids, string $prefix): int
    {
        return (int) collect($ids)
            ->filter(fn ($id) => str_starts_with($id, $prefix) && ctype_digit(substr($id, strlen($prefix))))
            ->map(fn ($id) => (int) substr($id, strlen($prefix)))
            ->max();
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah ada.',
            'max' => ':attribute terlalu panjang.',
            'alpha_num' => ':attribute hanya boleh huruf dan angka.',
            'in' => ':attribute tidak valid.',
            'not_in' => ':attribute tidak boleh dipakai.',
            'exists' => ':attribute tidak ditemukan.',
            'integer' => ':attribute harus angka.',
        ];
    }
}
