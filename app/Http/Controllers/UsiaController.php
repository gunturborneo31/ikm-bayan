<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Session;
use App\Models\NilaiUnsur;


class UsiaController extends Controller
{
    public function index()
    {
                error_reporting(0);

         $baseQuery = \App\Support\SurveyFilter::query();

        if($_GET['filter']==null or $_GET['filter']==1){
            $selects = $baseQuery->paginate(10);
        } else if($_GET['filter']==2) {
            $selects = (clone $baseQuery)->whereBetween('usia', [20, 29])->paginate(10);
        } else if($_GET['filter']==3) {
            $selects = (clone $baseQuery)->whereBetween('usia', [30, 39])->paginate(10);
        } else if($_GET['filter']==4) {
            $selects = (clone $baseQuery)->whereBetween('usia', [40, 49])->paginate(10);
        } else if($_GET['filter']==5) {
            $selects = (clone $baseQuery)->whereBetween('usia', [50, 100])->paginate(10);
        } else {
            $selects = $baseQuery->paginate(10);
        }

        return view('Usia.index', compact('selects'));
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
        return view('Usia.create');
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

        return view('Usia.detail', compact('datas'));
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


