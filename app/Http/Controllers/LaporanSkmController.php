<?php

namespace App\Http\Controllers;

use App\Services\LaporanSkmWord;

/**
 * Download laporan SKM PPM (.docx) sesuai filter aktif (?Tahun=&bagian=&perusahaan=).
 */
class LaporanSkmController extends Controller
{
    public function download(LaporanSkmWord $laporan)
    {
        $file = $laporan->build();

        return response()->download($file['path'], $file['name'])->deleteFileAfterSend(true);
    }
}
