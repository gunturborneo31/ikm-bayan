<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\WordController;
use App\Http\Controllers\JenisKelaminController;
use App\Http\Controllers\UsiaController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\PendidikanController;
use App\Http\Controllers\SaranMasukanController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NilaiUnsurController;
use App\Http\Controllers\RekapTotalController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SubJenisController;
use App\Http\Controllers\PpmSurveyController;
use App\Http\Controllers\RekapPpmController;
use App\Http\Controllers\MasterPpmController;
use Illuminate\Support\Facades\Route;
use App\Support\BagianOptions;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/portal', [PortalController::class, 'index']);
Route::get('/portal/{slug}', [PortalController::class, 'slug']);
Route::get('/', [DashboardController::class, 'welcome']);
Route::view('/remake', 'home');

Route::get('/login', function () {
    if (session()->has('user_id')) {
        $role = session('keterangan', 'admin');
        $tahun = date('Y');

        return redirect("/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun={$tahun}&keterangan=" . urlencode($role));
    }

    return view('login');
})->name('login');

Route::post('/login', [DashboardController::class, 'store'])->name('login.store');
Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');

// Survei publik PPM: desa -> program -> rincian -> perusahaan -> identitas -> kuesioner
Route::get('/skm', [PpmSurveyController::class, 'show']);
Route::post('/skm', [PpmSurveyController::class, 'store'])->name('skm.store');
Route::get('/skm/selesai', [PpmSurveyController::class, 'selesai'])->name('skm.selesai');
Route::get('/skm/baru', [PpmSurveyController::class, 'baru'])->name('skm.baru');

Route::get('/terimakasih', function () {
    return view('terimakasih', [
        'responden' => session(PpmSurveyController::SESSION_KEY),
    ]);
});

// Route::get('word', function () {
//     return view('word');
// });

// Laporan SKM PPM (.docx), mengikuti filter Tahun + Pilar + Perusahaan. Hanya untuk admin.
Route::middleware('admin.session')->group(function () {
    Route::get('export', [\App\Http\Controllers\LaporanSkmController::class, 'download'])->name('export');
    Route::get('/exports', function () {
        return view('exports');
    });
});

Route::resource('dashboard', DashboardController::class)->only(['index']);

Route::middleware('admin.session')->group(function () {
    Route::resource('dashboardAdmin', DashboardController::class)->except(['store']);
    Route::resource('jenisKelamin', JenisKelaminController::class);
    Route::resource('usia', UsiaController::class);
    Route::resource('pekerjaan', PekerjaanController::class);
    Route::resource('pendidikan', PendidikanController::class);
    Route::resource('saranDanMasukan', SaranMasukanController::class);
    Route::resource('nilaiRekap', NilaiController::class);
    Route::resource('nilaiUnsur', NilaiUnsurController::class);
    Route::resource('rekapTotal', RekapTotalController::class);
    Route::resource('pengaturanAdmin', PengaturanController::class);

    Route::get('exports/download', [ExportController::class, 'export'])->name('exports.download');

    Route::get('/rekap-ppm', [RekapPpmController::class, 'index'])->name('rekap-ppm');

    // Halaman lama "Sub Pelayanan" (tabel bagian_layanan/sub_jenis) sudah tidak dipakai survei.
    // Master data yang dipakai survei & laporan diatur di /master-ppm.
    Route::redirect('/sub-jenis', '/master-ppm')->name('subjenis.index');

    // Master Data PPM: desa, perusahaan, pilar -> program -> rincian, lokasi pelaksana
    Route::prefix('master-ppm')->name('master-ppm.')->controller(MasterPpmController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export', 'export')->name('export');
        Route::post('/import', 'import')->name('import');

        Route::post('/desa', 'storeDesa')->name('desa.store');
        Route::put('/desa/{id}', 'updateDesa')->whereNumber('id')->name('desa.update');
        Route::delete('/desa/{id}', 'destroyDesa')->whereNumber('id')->name('desa.destroy');

        Route::post('/perusahaan', 'storePerusahaan')->name('perusahaan.store');
        Route::put('/perusahaan/{kode}', 'updatePerusahaan')->name('perusahaan.update');
        Route::delete('/perusahaan/{kode}', 'destroyPerusahaan')->name('perusahaan.destroy');

        Route::post('/pilar', 'storePilar')->name('pilar.store');
        Route::put('/pilar/{id}', 'updatePilar')->name('pilar.update');
        Route::delete('/pilar/{id}', 'destroyPilar')->name('pilar.destroy');

        Route::post('/program', 'storeProgram')->name('program.store');
        Route::put('/program/{id}', 'updateProgram')->name('program.update');
        Route::delete('/program/{id}', 'destroyProgram')->name('program.destroy');

        Route::post('/rincian', 'storeRincian')->name('rincian.store');
        Route::get('/rincian/{id}', 'editRincian')->name('rincian');
        Route::put('/rincian/{id}', 'updateRincian')->name('rincian.update');
        Route::delete('/rincian/{id}', 'destroyRincian')->name('rincian.destroy');
    });
});

// Offline SKM Admin
Route::middleware('admin.session')->group(function () {
    // Import Excel: download format -> upload -> analisis duplikat -> import baris terpilih
    Route::get('/admin/offline-skm', [\App\Http\Controllers\OfflineSkmController::class, 'page'])->name('admin.offline-skm');
    Route::get('/admin/offline-skm/format', [\App\Http\Controllers\OfflineSkmController::class, 'template'])->name('admin.offline-skm.template');
    Route::post('/admin/offline-skm/analisis', [\App\Http\Controllers\OfflineSkmController::class, 'analyze'])->name('admin.offline-skm.analyze');
    Route::post('/admin/import-offline-skm', [\App\Http\Controllers\OfflineSkmController::class, 'import'])->name('admin.import-offline-skm');

    // Input responden oleh admin (bisa offline, data disinkronkan lewat endpoint JSON ini)
    Route::get('/admin/input-responden', [\App\Http\Controllers\AdminRespondenController::class, 'page'])->name('admin.input-responden');
    Route::get('/admin/responden/token', [\App\Http\Controllers\AdminRespondenController::class, 'token']);
    Route::get('/admin/responden', [\App\Http\Controllers\AdminRespondenController::class, 'index']);
    Route::post('/admin/responden', [\App\Http\Controllers\AdminRespondenController::class, 'store']);
    Route::put('/admin/responden/{id}', [\App\Http\Controllers\AdminRespondenController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/responden/{id}', [\App\Http\Controllers\AdminRespondenController::class, 'destroy'])->whereNumber('id');
});

// API endpoints (AJAX)
Route::prefix('sub-jenis')->group(function () {
    Route::get('/list',   [SubJenisController::class, 'list']);   // ?bagian=1&q=xyz
    Route::middleware('admin.session')->group(function () {
        Route::get('/bagian/list', [SubJenisController::class, 'bagianList']);
        Route::post('/bagian/store', [SubJenisController::class, 'bagianStore']);
        Route::put('/bagian/{id}', [SubJenisController::class, 'bagianUpdate']);
        Route::delete('/bagian/{id}', [SubJenisController::class, 'bagianDestroy']);
        Route::post('/store', [SubJenisController::class, 'store']);
        Route::put('/jenis',  [SubJenisController::class, 'updateJenis']);
        Route::delete('/jenis', [SubJenisController::class, 'destroyJenis']);
        Route::put('/{id}',   [SubJenisController::class, 'update']);
        Route::delete('/{id}',[SubJenisController::class, 'destroy']);
    });
});


