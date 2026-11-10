<?php

namespace App\Http\Controllers;

use App\Models\BagianLayanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Session;
use App\Models\NilaiUnsur;


class NilaiUnsurController extends Controller
{
    public function index()
    {
               error_reporting(0);

        // Filter tahunan (Tahun + Pilar); view masih membaca $_GET, jadi isi dengan nilai yang sudah divalidasi.
        $_GET['Tahun'] = \App\Support\SurveyFilter::year();
        $_GET['bagian'] = \App\Support\SurveyFilter::pilarCsv();

$selects = \App\Support\SurveyFilter::query()
            ->paginate(10);

        return view('NilaiUnsur.index', compact('selects'));
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
        

        // return $request->jenkel['jenkel'];
        $data = new NilaiUnsur();

        $jenis_pelayanan = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('bagian_layanan')) {
            $bagian = BagianLayanan::find((int) $request->bagian);
            if ($bagian) {
                $jenis_pelayanan = $bagian->kode;
            }
        }

        if (!$jenis_pelayanan) {
            if($request->bagian==1){
                $jenis_pelayanan = "organisasi";
            } else if($request->bagian==2){
                $jenis_pelayanan = "umum";
            } else if($request->bagian==3){
                $jenis_pelayanan = "pemerintahan";
            } else if($request->bagian==4){
                $jenis_pelayanan = "adbang";
            } else if($request->bagian==5){
                $jenis_pelayanan = "prokopim";
            } else if($request->bagian==6){
                $jenis_pelayanan = "kesra";
            } else if($request->bagian==7){
                $jenis_pelayanan = "pbj";
            } else if($request->bagian==8){
                $jenis_pelayanan = "ekosda";
            } else if($request->bagian==9){
                $jenis_pelayanan = "hukum";
            }
        }
        // $data->nm_kelas = $request->nm_kelas;
        // $data->ket = $request->ket;
        // $data->save();
        // return redirect()->back();

        $store = NilaiUnsur::create([
            'jenisPelayanan' => $jenis_pelayanan,
            'tahun' => (int) ($request->input('tahun') ?: date('Y')),
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'pekerjaan' => $request->pekerjaan,
            'jenkel' => $request->jenkel['jenkel'],
            'usia' => $request->usia,
            'nohp' => $request->nohp,
            'pendidikan' => $request->pendidikan,
            'nik' => $request->nik,
            'u1' => $request->u1,
            'u2' => $request->u2,
            'u3' => $request->u3,
            'u4' => $request->u4,
            'u5' => $request->u5,
            'u6' => $request->u6,
            'u7' => $request->u7,
            'u8' => $request->u8,
            'u9' => $request->u9,
            'saran' => $request->saran,
            'id_sub_jenis' => $request->filled('jenis') ? (int) $request->jenis : null,
        ]);

        if ($store) {
            //redirect dengan pesan sukses
            return redirect('/terimakasih');
        } else {
        // //     //redirect dengan pesan error
            return redirect('/terimakasih');
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
    public function destroy($id)
    {
        $NilaiUnsur = NilaiUnsur::findOrFail($id);
        $NilaiUnsur->delete();

        if ($NilaiUnsur) {
            //redirect dengan pesan sukses
            return redirect()->route('NilaiUnsur.index')->with(['pesan' => 'Data Berhasil Dihapus!']);
        } else {
            //redirect dengan pesan error
            return redirect()->route('NilaiUnsur.index')->with(['pesan' => 'Data Gagal Dihapus!']);
        }
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



