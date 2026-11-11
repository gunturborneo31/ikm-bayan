<?php

namespace App\Http\Controllers;

use App\Models\NilaiUnsur;
use App\Support\PpmCatalog;
use App\Support\SurveyOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Survei publik PPM Bayan.
 *
 * Satu kiriman = satu penilaian (satu rincian program). Identitas responden
 * disimpan di session, jadi responden yang sama bisa langsung menilai
 * program lain tanpa mengisi ulang identitas.
 */
class PpmSurveyController extends Controller
{
    public const SESSION_KEY = 'ppm_responden';

    private const IDENTITAS = ['nama', 'nohp', 'alamat', 'jenkel', 'usia', 'pendidikan', 'pekerjaan', 'jenis_responden'];

    public function show(Request $request)
    {
        return view('skm', [
            'catalog' => PpmCatalog::forSurvey(),
            'responden' => $request->session()->get(self::SESSION_KEY),
            'jenisRespondenOptions' => SurveyOptions::JENIS_RESPONDEN,
            'tidakTahu' => PpmCatalog::TIDAK_TAHU,
        ]);
    }

    public function store(Request $request)
    {
        $responden = $request->session()->get(self::SESSION_KEY);
        $lanjutan = $responden && $request->boolean('lanjutan');

        $rules = [
            'desa_id' => ['required', 'integer', Rule::in(array_keys(PpmCatalog::desa()))],
            'rincian_id' => ['required', 'string', Rule::in(array_keys(PpmCatalog::rincian()))],
            'perusahaan_jawaban' => ['required', 'string', 'max:12'],
            'saran' => ['nullable', 'string', 'max:600'],
        ];
        foreach (range(1, 9) as $i) {
            $rules["u{$i}"] = ['required', 'integer', 'between:1,4'];
        }
        if (! $lanjutan) {
            $rules += [
                'nama' => ['required', 'string', 'max:150'],
                'nohp' => ['nullable', 'string', 'max:16'],
                'alamat' => ['nullable', 'string', 'max:300'],
                'jenkel' => ['required', Rule::in(SurveyOptions::JENKEL)],
                'usia' => ['required', Rule::in(array_keys(SurveyOptions::USIA))],
                'pendidikan' => ['required', Rule::in(SurveyOptions::PENDIDIKAN)],
                'pekerjaan' => ['required', Rule::in(SurveyOptions::PEKERJAAN)],
                'jenis_responden' => ['required', Rule::in(SurveyOptions::JENIS_RESPONDEN)],
            ];
        }

        $data = $request->validate($rules, [
            'required' => ':attribute wajib diisi.',
            'in' => ':attribute tidak valid.',
            'between' => ':attribute harus bernilai 1 sampai 4.',
            'max' => ':attribute terlalu panjang.',
        ], [
            'desa_id' => 'Desa',
            'rincian_id' => 'Rincian program',
            'perusahaan_jawaban' => 'Perusahaan pelaksana',
            'nohp' => 'No. HP',
            'jenkel' => 'Jenis kelamin',
            'jenis_responden' => 'Jenis responden',
        ]);

        $desaId = (int) $data['desa_id'];

        if ($lanjutan) {
            // Responden lanjutan tetap di desa yang sama dengan identitasnya.
            if ($desaId !== (int) $responden['desa_id']) {
                throw ValidationException::withMessages(['desa_id' => 'Desa tidak sesuai dengan identitas responden.']);
            }
            if (in_array($data['rincian_id'], $responden['dinilai'] ?? [], true)) {
                throw ValidationException::withMessages(['rincian_id' => 'Program ini sudah Anda nilai. Silakan pilih program lain.']);
            }
            $identitas = $responden;
        } else {
            $identitas = array_intersect_key($data, array_flip(self::IDENTITAS));
            $identitas['uuid'] = (string) Str::uuid();
            $identitas['desa_id'] = $desaId;
            $identitas['dinilai'] = [];
        }

        $atribusi = PpmCatalog::attribute($data['rincian_id'], $desaId, $data['perusahaan_jawaban']);
        if ($atribusi === null) {
            throw ValidationException::withMessages([
                'perusahaan_jawaban' => 'Pilihan perusahaan tidak sesuai dengan program di desa ini.',
            ]);
        }

        $rincian = PpmCatalog::rincian()[$data['rincian_id']];
        $programId = $rincian['program_id'];
        $pilarId = PpmCatalog::program()[$programId]['pilar_id'];

        $row = [
            'tahun' => (int) date('Y'),
            'jenisPelayanan' => PpmCatalog::pilar()[$pilarId]['kode'] ?? null,
            'responden_uuid' => $identitas['uuid'],
            'desa_id' => $desaId,
            'pilar_id' => $pilarId,
            'program_id' => $programId,
            'rincian_id' => $data['rincian_id'],
            'perusahaan_jawaban' => $data['perusahaan_jawaban'],
            'saran' => $data['saran'] ?? null,
            'sumber_input' => 'survei', // diisi sendiri oleh responden
        ] + $atribusi;

        foreach (self::IDENTITAS as $field) {
            $row[$field] = $identitas[$field] ?? null;
        }
        foreach (range(1, 9) as $i) {
            $row["u{$i}"] = (int) $data["u{$i}"];
        }

        NilaiUnsur::create($row);

        $identitas['dinilai'][] = $data['rincian_id'];
        $request->session()->put(self::SESSION_KEY, $identitas);

        return redirect('/terimakasih');
    }

    /** Responden selesai: hapus identitas dari session. */
    public function selesai(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect('/');
    }

    /** Ganti responden (perangkat dipakai bergantian). */
    public function baru(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect('/skm#awal');
    }
}
