<?php

error_reporting(0);   

$startDate = \App\Support\SurveyFilter::year() . '-01-01';

$endDate = \App\Support\SurveyFilter::year() . '-12-31';


$terms = \App\Support\SurveyFilter::selectedPilar();

$indexTu = ['u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9'];
if($_GET['Tahun']=='2024'){
        $lastDate = ['2024-01-31', '2024-02-29', '2024-03-31', '2024-04-30', '2024-05-31', '2024-06-30', '2024-07-31', '2024-08-31', '2024-09-30', '2024-10-31', '2024-11-30', '2024-12-31'];
    } else if($_GET['Tahun']=='2025'){
        $lastDate = ['2025-01-31', '2025-02-28', '2025-03-31', '2025-04-30', '2025-05-31', '2025-06-30', '2025-07-31', '2025-08-31', '2025-09-30', '2025-10-31', '2025-11-30', '2025-12-31'];
    } else if($_GET['Tahun']=='2026'){
        $lastDate = ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30', '2026-05-31', '2026-06-30', '2026-07-31', '2026-08-31', '2026-09-30', '2026-10-31', '2026-11-30', '2026-12-31'];
    }


    $kode = \App\Support\BagianOptions::normalizeCode($_GET['bagian'] ?? '');
    $bagian = strtoupper(\App\Support\BagianOptions::labelForCode($kode));

    // dd( $bagian , $kode); // jika tidak ditemukan kembalikan '-'


$total = \App\Support\SurveyFilter::query()
    ->get()
    ->count();

$jenkel_l = \App\Support\SurveyFilter::query()
    ->where('jenkel', '=', 'Laki - Laki')
    ->get()
    ->count();

$jenkel_p = \App\Support\SurveyFilter::query()
    ->where('jenkel', '=', 'Perempuan')
    ->get()
    ->count();

$umur_1 = \App\Support\SurveyFilter::query()
    ->whereBetween('usia', [0, 29])
    ->get()
    ->count();

$umur_2 = \App\Support\SurveyFilter::query()
    ->whereBetween('usia', [30, 40])
    ->get()
    ->count();

$umur_3 = \App\Support\SurveyFilter::query()
    ->whereBetween('usia', [41, 50])
    ->get()
    ->count();

$umur_4 = \App\Support\SurveyFilter::query()
    ->whereBetween('usia', [51, 100])
    ->get()
    ->count();

$pendidikan_1 = \App\Support\SurveyFilter::query()
    ->where('pendidikan', '=', 'SD')
    ->get()
    ->count();

$pendidikan_2 = \App\Support\SurveyFilter::query()
    ->where('pendidikan', '=', 'SMP')
    ->get()
    ->count();

$pendidikan_3 = \App\Support\SurveyFilter::query()
    ->where('pendidikan', '=', 'SMA / SMK')
    ->get()
    ->count();

$pendidikan_4 = \App\Support\SurveyFilter::query()
    ->where('pendidikan', '=', 'D-I / D-III')
    ->get()
    ->count();

$pendidikan_5 = \App\Support\SurveyFilter::query()
    ->where('pendidikan', '=', 'S1 / Setara')
    ->get()
    ->count();

$pendidikan_6 = \App\Support\SurveyFilter::query()
    ->where('pendidikan', '=', 'S2 / S3')
    ->get()
    ->count();

$tu = [[]];
$x = [];
$nn = [];
$nrr = [[]];
$nrrT = [[]];
$nrrTT = [];
$nilaiSkm = [];

array_push($nn, '');

for ($i = 1; $i <= 12; $i++) {
    $abc = $lastDate[$i - 1];
    for ($j = 1; $j <= 9; $j++) {
        $xyz = $indexTu[$j - 1];
        // echo $xyz;
        $nilaiTu = \App\Support\SurveyFilter::query()
            ->whereBetween('created_at', [$startDate, $abc])
            ->get()
            ->sum($xyz);
        array_push($x, $nilaiTu);
    }

    $nnn = \App\Support\SurveyFilter::query()
        ->whereBetween('created_at', [$startDate, $abc])
        ->get()
        ->count();

    
    array_push($tu, $x);
    array_push($nn, $nnn);
    $x = [];
}





for ($i = 1; $i <= 12; $i++) {
    $x;
    for ($j = 1; $j <= 9; $j++) {
        if ($nn[$i] == 0) {
            array_push($x, 0);
        } else {
            $nrrx = $tu[$i][$j - 1] / $nn[$i];
            array_push($x, round($nrrx, 2));
        }
    }
    // print_r($x);
    // echo '<br>';
    array_push($nrr, $x);
    $x = [];
}


array_push($nrrTT, '');
for ($i = 1; $i <= 12; $i++) {
    $x;
    $y;
    for ($j = 1; $j <= 9; $j++) {
        $nrrTx = $nrr[$i][$j - 1] * (1/9);
        array_push($x, round($nrrTx, 2));
        $y = $y + $nrrTx;
    }
    array_push($nrrT, $x);
    array_push($nrrTT, round($y, 2));
    $x = [];
    $y = 0;
}


for ($i = 0; $i <= 11; $i++) {
    if ($_GET['Tahun'] == '2023' or $_GET['Tahun'] == '2023') {
        array_push($nilaiSkm, 0);
    } else {
        array_push($nilaiSkm, $nrrTT[$i + 1] * 25);
    }
}

// dd($nrrTT);

$tu1 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u1');

$tu2 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u2');

$tu3 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u3');

$tu4 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u4');

$tu5 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u5');

$tu6 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u6');

$tu7 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u7');

$tu8 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u8');

$tu9 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u9');

$n = \App\Support\SurveyFilter::query()
    ->get()
    ->count();

$tu9 = \App\Support\SurveyFilter::query()
    ->get()
    ->sum('u9');

// unset($nilaiSkm[0]);

$nilaiSkm = json_encode($nilaiSkm);

// dd($responden);
// dd($nilaiSkm);

if ($n == 0) {
    $nrr1 = 0;
    $nrr2 = 0;
    $nrr3 = 0;
    $nrr4 = 0;
    $nrr5 = 0;
    $nrr6 = 0;
    $nrr7 = 0;
    $nrr8 = 0;
    $nrr9 = 0;

    $nrr = 0;
    $nrr = 0;

    $nrrt1 = 0;
    $nrrt2 = 0;
    $nrrt3 = 0;
    $nrrt4 = 0;
    $nrrt5 = 0;
    $nrrt6 = 0;
    $nrrt7 = 0;
    $nrrt8 = 0;
    $nrrt9 = 0;

    $nrrT = 0;
    $nrrT = 0;

    $nilaiSKM = $nrrT * 25;
} else {
    $nrr1 = sprintf('%0.3f', $tu1 / $n);
    $nrr2 = sprintf('%0.3f', $tu2 / $n);
    $nrr3 = sprintf('%0.3f', $tu3 / $n);
    $nrr4 = sprintf('%0.3f', $tu4 / $n);
    $nrr5 = sprintf('%0.3f', $tu5 / $n);
    $nrr6 = sprintf('%0.3f', $tu6 / $n);
    $nrr7 = sprintf('%0.3f', $tu7 / $n);
    $nrr8 = sprintf('%0.3f', $tu8 / $n);
    $nrr9 = sprintf('%0.3f', $tu9 / $n);

    $nrr = $nrr1 + $nrr2 + $nrr3 + $nrr4 + $nrr5 + $nrr6 + $nrr7 + $nrr8 + $nrr9;
    $nrr = sprintf('%0.3f', $nrr);

    $nrrt1 = sprintf('%0.3f', $nrr1 * (1/9));
    $nrrt2 = sprintf('%0.3f', $nrr2 * (1/9));
    $nrrt3 = sprintf('%0.3f', $nrr3 * (1/9));
    $nrrt4 = sprintf('%0.3f', $nrr4 * (1/9));
    $nrrt5 = sprintf('%0.3f', $nrr5 * (1/9));
    $nrrt6 = sprintf('%0.3f', $nrr6 * (1/9));
    $nrrt7 = sprintf('%0.3f', $nrr7 * (1/9));
    $nrrt8 = sprintf('%0.3f', $nrr8 * (1/9));
    $nrrt9 = sprintf('%0.3f', $nrr9 * (1/9));

    $nrrT = $nrrt1 + $nrrt2 + $nrrt3 + $nrrt4 + $nrrt5 + $nrrt6 + $nrrt7 + $nrrt8 + $nrrt9;
    $nrrT = sprintf('%0.3f', $nrrT);

    $nilaiSKM = $nrrT * 25;

    // $responden = [];
    // $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nop', 'Des'];

    // if ($_GET['tw'] == '') {
    //     for ($x = 0; $x <= date('m'); $x++) {
    //         $responden[$x][0] = \App\Support\SurveyFilter::query()
    //             ->whereMonth('created_at', '=', $x + 1)
    //             ->get()
    //             ->count();
    //         $responden[$x][1] = $bulan[$x];
    //     }
    // } else {
    //     for ($x = 0; $x <= $_GET['tw']; $x++) {
    //         $responden[$x][0] = \App\Support\SurveyFilter::query()
    //             ->whereMonth('created_at', '=', $x + 1)
    //             ->get()
    //             ->count();
    //         $responden[$x][1] = $bulan[$x];
    //     }
    // }
    // dd($responden);

    if ($_GET['Tahun'] == '2023' or $_GET['Tahun'] == '2023' or $_GET['Tahun'] == '2023') {
        $tu = [[]];
        $x = [];
        $nn = [];
        $nrr = [[]];
        $nrrT = [[]];
        $nrrTT = [];
        // $nilaiSkm = [];
    }
}
// echo $nrrTotal;
// echo $nrrTertimbang;
$jmlResponden = \App\Support\SurveyFilter::query()
    ->get()
    ->count();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Indeks Kepuasan Masyarakat</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

    </style>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#1d4ed8",
                        "background-light": "#f9fafb",
                        "background-dark": "#111827",
                        "text-light": "#1f2937",
                        "text-dark": "#f9fafb",
                        "border-light": "#000000",
                        "border-dark": "#374151",
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        };

    </script>
    <link href="https://rsms.me/inter/inter.css" rel="stylesheet" />
</head>

<body
    class="bg-background-light dark:bg-background-dark font-sans flex items-center justify-center min-h-screen p-4 relative bg-gradient-to-br from-[#18bdde] from-60%  to-[#fffb61] to-95">

    <button onclick="history.back()" class="absolute top-6 left-6 bg-white text-gray-800 px-3 py-1 rounded-xl shadow hover:bg-gray-100 transition">
        ← Kembali
    </button>

    <div class="absolute top-6 right-6 flex flex-col items-end gap-4">

        <!-- Tahun -->
        <div class="flex flex-row items-center gap-3 justify-end w-full">
            <label for="tahun"
                class="font-bold text-gray-900 text-4xl lg:text-base drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                Tahun
            </label>

            <?php if($_GET['Tahun'] == ''){ ?>
            <select id="tahun"
                onChange="document.location.href='resumeKhusus?tw=' + {{ $_GET['tw'] == null ? '2024' : $_GET['tw'] }} + '&Tahun=' + this.value"
                class="bg-white border border-gray-300 text-gray-900 font-bold text-4xl lg:text-sm rounded-full pr-8 py-1 text-center">
                <option value="2023" <?php if(date('Y')=='2023') echo 'selected'; ?>>2023</option>
                <option value="2024" <?php if(date('Y')=='2024') echo 'selected'; ?>>2024</option>
                <option value="2025" <?php if(date('Y')=='2025') echo 'selected'; ?>>2025</option>
                <option value="2026" <?php if(date('Y')=='2026') echo 'selected'; ?>>2026</option>
            </select>
            <?php } else { ?>
            <select id="tahun"
                onChange="document.location.href='resumeKhusus?tw=' + this.value + '&Tahun={{ $_GET['Tahun'] ?? '2026' }}'"
                class="bg-white border border-gray-300 text-gray-900 font-bold text-4xl lg:text-sm rounded-full pr-8 py-1 text-center">
                <option value="2023" <?php if($_GET['Tahun']=='2023') echo 'selected'; ?>>2023</option>
                <option value="2024" <?php if($_GET['Tahun']=='2024') echo 'selected'; ?>>2024</option>
                <option value="20256 <?php if($_GET['Tahun']=='2025') echo 'selected'; ?>>2025</option>
                <option value="2026" <?php if($_GET['Tahun']=='2026') echo 'selected'; ?>>2026</option>
            </select>
            <?php } ?>
        </div>

        <!-- Triwulan -->
        <div class="flex flex-row items-center gap-3 justify-end w-full">
            <label for="triwulan"
                class="font-bold text-gray-900 text-4xl lg:text-base drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                Triwulan
            </label>

            <select id="triwulan"
                onChange="document.location.href='resumeKhusus?tw=' + this.value + '&Tahun={{ $_GET['Tahun'] ?? '2026' }}'"
                class="bg-white border border-gray-300 text-gray-900 font-bold text-4xl lg:text-sm rounded-full pr-8 py-1 text-center">
                <option value="1" <?php if($_GET['tw']==1) echo 'selected'; ?>>TW 1</option>
                <option value="2" <?php if($_GET['tw']==2 || $_GET['tw']==3) echo 'selected'; ?>>TW 2</option>
                <option value="4" <?php if($_GET['tw']==4 || $_GET['tw']==5) echo 'selected'; ?>>TW 3</option>
                <option value="6" <?php if($_GET['tw']==6 || $_GET['tw']==7) echo 'selected'; ?>>TW 4</option>
            </select>
        </div>

        <!-- Tombol -->
        <a href="{{ route('ikm.download', [
            'tw' => $tw,
            'Tahun' => request('Tahun', date('Y')),
            'nilaiSKM' => $nilaiSKM,
            'total' => $total,
            'jenkel_l' => $jenkel_l,
            'jenkel_p' => $jenkel_p,
            'pendidikan_1' => $pendidikan_1,
            'pendidikan_2' => $pendidikan_2,
            'pendidikan_3' => $pendidikan_3,
            'pendidikan_4' => $pendidikan_4,
            'pendidikan_5' => $pendidikan_5,
            'pendidikan_6' => $pendidikan_6,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]) }}" class="bg-white text-gray-800 px-4 py-2 rounded-xl shadow hover:bg-gray-100 transition w-fit text-right">
        ↓ Download PDF
        </a>

    </div>


    <div
        class="bg-white rounded-2xl w-1/2 max-w-4xl border border-border-light dark:border-border-dark p-6 space-y-4 text-center text-text-light dark:text-text-dark">
        <header class="space-y-0.5">
            <h1 class="uppercase text-xl font-bold">INDEKS KEPUASAN MASYARAKAT (IKM)</h1>
            <p class="uppercase text-sm">Bagian {{ $bagian }} Kabupaten Mahakam Ulu</p>
            <p class="uppercase text-sm">TRIWULAN {{ $tw }} TAHUN {{ $_GET['Tahun'] }}</p>
        </header>
        <main class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div class="md:col-span-2 border border-border-light dark:border-border-dark p-4 flex flex-col rounded-xl">
                <h2 class="text-lg font-semibold border-b-2 border-black dark:border-white pb-1 mb-2">NILAI IKM</h2>
                <div class="flex-grow flex items-center justify-center">
                    <span
                        class="text-9xl font-bold">{{ ($_GET['Tahun'] == '2023' ) ? '00' : sprintf('%0.0f', $nilaiSKM) }}</span>
                </div>
            </div>
            <div
                class="md:col-span-3 border border-border-light dark:border-border-dark p-4 space-y-2 text-left rounded-xl">
                <div class="border-b-2 border-black dark:border-white pb-1 mb-2">
                    <p class="font-semibold">NAMA LAYANAN :</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2 text-center">RESPONDEN</h3>
                    <table class="w-full text-left text-xs text-text-light dark:text-text-dark">
                        <tbody>
                            <!-- Baris JUMLAH -->
                            <tr class="border-b border-white dark:border-gray-700">
                                <th class="w-[110px] pr-2 py-1 font-medium" scope="row">JUMLAH</th>
                                <td class="w-2 px-1 py-1 text-center">:</td>
                                <td class="pl-2 py-1">{{ $total }} orang</td>
                            </tr>

                            <!-- Baris JENIS KELAMIN -->
                            <tr class="border-b border-white dark:border-gray-700">
                                <th class="w-[110px] pr-2 py-1 font-medium" scope="row">JENIS KELAMIN</th>
                                <td class="w-2 px-1 py-1 text-center">:</td>
                                <td class="pl-2 py-1">L = {{ $jenkel_l }} orang / P = {{ $jenkel_p }} orang</td>
                            </tr>

                            <!-- Baris PENDIDIKAN -->
                            <tr class="border-b border-white dark:border-gray-700 align-top">
                                <th class="w-[110px] pr-2 py-1 font-medium align-top" scope="row">PENDIDIKAN</th>
                                <td class="w-2 px-1 py-1 align-top text-center">:</td>
                                <!-- colspan untuk tabel kecil -->
                                <td class="pl-2 py-1" colspan="1">
                                    <table class="w-full text-xs">
                                        <tbody>
                                            <tr>
                                                <td class="w-[40px] py-1">SD</td>
                                                <td class="w-2 text-center">=</td>
                                                <td class="pl-2">{{ $pendidikan_1 }} orang</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1">SMP</td>
                                                <td class="text-center">=</td>
                                                <td class="pl-2">{{ $pendidikan_2 }} orang</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1">SMA</td>
                                                <td class="text-center">=</td>
                                                <td class="pl-2">{{ $pendidikan_3 }} orang</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1">DIII</td>
                                                <td class="text-center">=</td>
                                                <td class="pl-2">{{ $pendidikan_4 }} orang</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1">S1</td>
                                                <td class="text-center">=</td>
                                                <td class="pl-2">{{ $pendidikan_5 }} orang</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1">S2</td>
                                                <td class="text-center">=</td>
                                                <td class="pl-2">{{ $pendidikan_6 }} orang</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class=" text-xs">
                    <p>Periode Survei = {{date("d-m-Y", strtotime($startDate))}} s/d {{date("d-m-Y", strtotime($endDate))}} </p>
                </div>
            </div>
        </main>
        <footer class="pt-1 text-sm">
            <p class="font-semibold">TERIMA KASIH ATAS PENILAIAN YANG TELAH ANDA BERIKAN</p>
            <p>MASUKAN ANDA SANGAT BERMANFAAT UNTUK KEMAJUAN UNIT KAMI AGAR TERUS MEMPERBAIKI DAN MENINGKATKAN KUALITAS
                PELAYANAN BAGI MASYARAKAT</p>
        </footer>
    </div>

</body>

</html>




