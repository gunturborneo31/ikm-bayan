<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\NilaiUnsur;
use App\Models\User;
use App\Support\BagianOptions;


class DashboardController extends Controller
{
    public function index()
    {
        // echo (""+Session::get('subbidang'));
        $selects = DB::table('survey_responses')
            ->get();

        return view('dashboard', compact('selects'));
    }

     public function welcome()
    {
        // $tahun = date("Y");
        // dd($tahun);
          // echo (""+Session::get('subbidang'));
        $responden = [];
        $singkatan = [];


        // Ambil data agregat seluruh tahun
        $allYears = DB::table('survey_responses')
            ->select(DB::raw('YEAR(created_at) as tahun'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('tahun')
            ->get();

        $responden = [];
        $singkatan = [];
        foreach ($allYears as $i => $row) {
            $responden[$i] = $row->total;
            $singkatan[$i] = $row->tahun;
        }
        $jml = count($responden);


        $responden = json_encode($responden);
        $singkatan = json_encode($singkatan);

        // Pastikan $selects terdefinisi untuk compact()
        $selects = DB::table('survey_responses')->get();

        $i = '/' . ltrim(request()->path(), '/');
        // dd ($jml);

        $bln = null;
        if($i == '/remake'){
            // ...existing code...
        } else {
            // Kirim data tren tahunan ke view, label grafik batang: "Tren SKM Tahunan"
            return view('welcome', compact('selects','responden','bln','jml', 'singkatan'));
        }
        // return view('welcome', compact('selects','responden','bln','jml', 'singkatan'));
    }


    public function cariDashboard(Request $request)
    {
        $keyword = $request->search;
        $datas = NilaiUnsur::where('tahun', 'like', "%" . $keyword . "%")->get();
        return view('dashboard.index', compact('datas'));
    }

    /**
     * Show admin dashboard landing page.
     */
    public function create()
    {
        return view('dashboardAdmin');
    }

    /**
     * Handle admin login.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = (string) $validated['username'];
        $password = (string) $validated['password'];

        // Look up the user by username (stored in the users.username column).
        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withInput($request->only('username'))->withErrors(['username' => 'Username atau password salah.']);
        }
        if (isset($user->is_active) && ! $user->is_active) {
            return back()->withInput($request->only('username'))->withErrors(['username' => 'Akun ini sedang dinonaktifkan. Hubungi admin.']);
        }

        $request->session()->regenerate();

        // Store the authenticated user id and role in the session.
        Session::put('user_id', $user->id);
        Session::put('username', $user->username);
        Session::put('keterangan', $user->keterangan ?? 'admin');
        Session::put('nama', $user->name);
        $user->forceFill(['last_login_at' => now()])->save();

        $role = $user->keterangan ?? 'admin';

        $tahun = date('Y');

        return redirect("/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun={$tahun}&keterangan=" . urlencode($role));
    }

    public function logout(Request $request)
    {
        Session::forget(['user_id', 'username', 'keterangan', 'nama']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Edit dashboard data.
     */
    public function edit(request $request)
    {
        // echo ($request->jenis);
        // $datas = Dashboard::latest()->when(request()->search, function ($data_dasars) {
        //     $data_dasars = $data_dasars->where('id', 'like', '%' . request()->id_dashboard . '%');
        // })
        //     ->where('id', $_GET['id_dashboard'])
        //     ->get();

        // return view('Dashboard.detail', compact('datas'));
    }

    /**
     * destroy
     *
     * @return void
     */
    public function destroy($id)
    {
        // $Dashboard = Dashboard::findOrFail($id);
        // $Dashboard->delete();

        // if ($Dashboard) {
        //     //redirect dengan pesan sukses
        //     return redirect()->route('Dashboard.index')->with(['pesan' => 'Data Berhasil Dihapus!']);
        // } else {
        //     //redirect dengan pesan error
        //     return redirect()->route('Dashboard.index')->with(['pesan' => 'Data Gagal Dihapus!']);
        // }
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $Dashboard
     * @return void
     */
    // public function update(Request $request, Dashboard $Dashboard)
    // {
    //     // print_r($request->post());
    //     $Dashboard->update([
    //         'nm_dashboard' => $request->nm_dashboard,
    //         'ket' => $request->ket,
    //     ]);

    //     return redirect()->route('Dashboard.index');
    // }
}
