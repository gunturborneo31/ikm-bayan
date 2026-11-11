<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Session;
use App\Models\NilaiUnsur;
use App\Support\BagianOptions;


class RekapTotalController extends Controller
{
    public function index()
    {
               error_reporting(0);

        // Filter tahunan (Tahun + Pilar); view masih membaca $_GET, jadi isi dengan nilai yang sudah divalidasi.
        $_GET['Tahun'] = \App\Support\SurveyFilter::year();
        $_GET['bagian'] = \App\Support\SurveyFilter::pilarCsv();

        $query = \App\Support\SurveyFilter::query();

        // Filter penginput: id akun, atau "survei" (diisi sendiri oleh responden)
        $penginput = (string) request('penginput', '');
        $byPenginput = function ($q) use ($penginput) {
            if ($penginput === 'survei') {
                $q->where('survey_responses.sumber_input', 'survei');
            } elseif (ctype_digit($penginput)) {
                $q->where('survey_responses.input_oleh', (int) $penginput);
            }
        };
        $byPenginput($query);

        if (Schema::hasTable('sub_jenis')) {
            $query->leftJoin('sub_jenis', 'survey_responses.id_sub_jenis', '=', 'sub_jenis.id')
                ->select('survey_responses.*', 'sub_jenis.jenis as sub_jenis_jenis');
        } else {
            $query->select('survey_responses.*', DB::raw('NULL as sub_jenis_jenis'));
        }

        $selects = $query->orderByDesc('survey_responses.created_at')->paginate(10)->withQueryString();

        // Total nilai per unsur untuk semua data sesuai filter (bukan hanya halaman ini)
        $totals = tap(\App\Support\SurveyFilter::query(), $byPenginput)
            ->selectRaw('COUNT(*) as n, ' . implode(', ', array_map(fn ($i) => "COALESCE(SUM(u{$i}),0) as u{$i}", range(1, 9))))
            ->first();

        // Nama untuk kolom desa, pilar dan rincian program
        $desaNames = Schema::hasTable('ppm_desa') ? DB::table('ppm_desa')->pluck('nama', 'id')->all() : [];
        $pilarNames = \App\Support\BagianOptions::codeNameMap();
        $rincianNames = Schema::hasTable('ppm_rincian') ? DB::table('ppm_rincian')->pluck('nama', 'id')->all() : [];

        $akunOptions = DB::table('users')->orderBy('name')->pluck('name', 'id')->all();

        return view('RekapTotal.index', compact('selects', 'totals', 'desaNames', 'pilarNames', 'rincianNames', 'penginput', 'akunOptions'));
    }

    /**
     * Detail satu penilaian: identitas responden, program yang dinilai,
     * atribusi perusahaan, jawaban 9 unsur, saran, dan penilaian lain
     * oleh responden yang sama.
     */
    public function show(string $id)
    {
        $data = DB::table('survey_responses')->where('id', $id)->first();
        abort_if(! $data, 404, 'Data penilaian tidak ditemukan.');

        $nama = fn (string $table, ?string $key, string $col = 'id') => $key === null || $key === ''
            ? null
            : DB::table($table)->where($col, $key)->value('nama');

        $detail = [
            'desa' => $data->desa_id ? DB::table('ppm_desa')->where('id', $data->desa_id)->value('nama') : null,
            'pilar' => $nama('ppm_pilar', $data->pilar_id)
                ?? \App\Support\BagianOptions::codeNameMap()[$data->jenisPelayanan] ?? $data->jenisPelayanan,
            'program' => $nama('ppm_program', $data->program_id),
            'rincian' => $nama('ppm_rincian', $data->rincian_id),
            'grup' => $data->rincian_id ? (bool) DB::table('ppm_rincian')->where('id', $data->rincian_id)->value('dinilai_sebagai_grup') : false,
            'opsi' => array_values(array_filter(explode(',', (string) $data->perusahaan_opsi))),
        ];

        // Penilaian lain oleh responden yang sama (identitas diisi sekali)
        $lainnya = $data->responden_uuid
            ? DB::table('survey_responses')
                ->leftJoin('ppm_rincian', 'ppm_rincian.id', '=', 'survey_responses.rincian_id')
                ->where('responden_uuid', $data->responden_uuid)
                ->where('survey_responses.id', '!=', $data->id)
                ->orderBy('survey_responses.created_at')
                ->get(['survey_responses.id', 'survey_responses.created_at', 'survey_responses.perusahaan_dinilai',
                    'ppm_rincian.nama as rincian_nama',
                    DB::raw('(COALESCE(u1,0)+COALESCE(u2,0)+COALESCE(u3,0)+COALESCE(u4,0)+COALESCE(u5,0)+COALESCE(u6,0)+COALESCE(u7,0)+COALESCE(u8,0)+COALESCE(u9,0)) as skor')])
            : collect();

        return view('RekapTotal.show', compact('data', 'detail', 'lainnya'));
    }


    public function cariNilaiUnsur(Request $request)
    {
        $keyword = $request->search;
        $datas = NilaiUnsur::where('nilaiUnsur', 'like', "%" . 'like', "%" . $keyword . "%");
        return view('nilaiUnsur.index', compact('datas'));
    }

    /**
     * create
     *
     * @return mixed
     */
    public function create()
    {
        return view('Nilai.create');
    }

    /**
     * store
     *
     * @return mixed
     */
    public function store(Request $request)
     {
        // echo ("request");
        // return $request;

        $data = new NilaiUnsur();
        // $data->nm_kelas = $request->nm_kelas;
        // $data->ket = $request->ket;
        // $data->save();
        // return redirect()->back();

        $store = NilaiUnsur::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'pekerjaan' => $request->pekerjaan,
            'jenkel' => $request->jenkel,
            'usia' => $request->usia,
            'nohp' => $request->nohp,
            'pendidikan' => $request->pendidikan,
            'nik' => $request->nik,
            'u1' => $request->persyaratan,
            'u2' => $request->kompetensi,
            'u3' => $request->prosedur,
            'u4' => $request->perilaku,
            'u5' => $request->kecepatan,
            'u6' => $request->sarana,
            'u7' => $request->biaya,
            'u8' => $request->penanganan,
            'u9' => $request->kesesuaian,
            'saran' => $request->saran,
        ]);

        if ($store) {
            //redirect dengan pesan sukses
            return redirect('/');
        } else {
        //     //redirect dengan pesan error
            return redirect('/');
        }
    }

    /**
     * edit
     *
     * @return mixed
     */
    public function edit(request $request)
    {
        // echo ($request->jenis);
        $datas = NilaiUnsur::latest()->when(request()->search, function ($data_dasars) {
            $data_dasars = $data_dasars->where('id', 'like', '%' . request()->id_nilaiUnsur . '%');
        })
            ->where('id', $_GET['id_nilaiUnsur'])
            ->get();

        return view('Nilai.detail', compact('datas'));
    }

    /**
     * destroy
     *
     * @return mixed
     */
    public function destroy(String $id)
    {
        $NilaiUnsur = NilaiUnsur::findOrFail($id);
        $NilaiUnsur->delete();

        // Kembali ke daftar Resume pada tahun data yang dihapus
        return redirect('/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun=' . ($NilaiUnsur->tahun ?? date('Y')));
    }

    /**
     * update
     *
     * @return mixed
     */
    public function update(Request $request, NilaiUnsur $NilaiUnsur)
    {
        // print_r($request->post());
        $NilaiUnsur->update([
            'nm_nilaiUnsur' => $request->nm_nilaiUnsur,
            'ket' => $request->ket,
        ]);

        return redirect()->route('NilaiUnsur.index');
    }
}



