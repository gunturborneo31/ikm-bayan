<?php

namespace App\Http\Controllers;

use App\Models\NilaiUnsur;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\Chart;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use App\Support\BagianOptions;


class WordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // script php word
        // Creating the new document...
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        /* Note: any element you append to a document must reside inside of a Section. */

        // Adding an empty Section to the document...
        $section = $phpWord->addSection();
        // Adding Text element to the Section having font styled by default...
        $section->addText(
            '"Learn from yesterday, live for today, hope for tomorrow. '
                . 'The important thing is not to stop questioning." '
                . '(Albert Einstein)'
        );

        /*
        * Note: it's possible to customize font style of the Text element you add in three ways:
        * - inline;
        * - using named font style (new font style object will be implicitly created);
        * - using explicitly created font style object.
        */

        // Adding Text element with font customized inline...
        $section->addText(
            '"Great achievement is usually born of great sacrifice, '
                . 'and is never the result of selfishness." '
                . '(Napoleon Hill)',
            array('name' => 'Tahoma', 'size' => 10)
        );

        // Adding Text element with font customized using named font style...
        $fontStyleName = 'oneUserDefinedStyle';
        $phpWord->addFontStyle(
            $fontStyleName,
            array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
        );
        $section->addText(
            '"The greatest accomplishment is not in never falling, '
                . 'but in rising again after you fall." '
                . '(Vince Lombardi)',
            $fontStyleName
        );

        // Adding Text element with font customized using explicitly created font style object...
        $fontStyle = new \PhpOffice\PhpWord\Style\Font();
        $fontStyle->setBold(true);
        $fontStyle->setName('Tahoma');
        $fontStyle->setSize(13);
        $myTextElement = $section->addText('"Believe you can and you\'re halfway there." (Theodor Roosevelt)');
        $myTextElement->setFontStyle($fontStyle);

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('helloWorld.docx');

        // Saving the document as ODF file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
        $objWriter->save('helloWorld.odt');

        // Saving the document as HTML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $objWriter->save('helloWorld.html');

        /* Note: we skip RTF, because it's not XML-based and requires a different example. */
        /* Note: we skip PDF, because "HTML-to-PDF" approach is used to create PDF documents. */


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function export(Request $request){
        // dd($_GET['tw'], $_GET['Tahun']);
        $date = new DateTime();

        // Extract day, month, and year
        $hariIni = $date->format('d');
        $bulanIni = $date->format('m');
        $tahunIni = $date->format('Y');

        // dd($hari, $bulan, $tahun);
        //-----------------------------------------------------------------------------------------------------------------

        error_reporting(0);

        $startDate = \App\Support\SurveyFilter::year() . '-01-01';

        $endDate = \App\Support\SurveyFilter::year() . '-12-31';


        // Pilar mengikuti filter (?bagian=...); "setkab"/kosong = semua pilar.
        $terms = \App\Support\SurveyFilter::selectedPilar();

        $indexTu = ['u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9'];
        // Akhir tiap bulan pada tahun terpilih (untuk grafik SKM kumulatif per bulan)
        $tahunLaporan = \App\Support\SurveyFilter::year();
        $lastDate = array_map(fn ($m) => date('Y-m-t 23:59:59', mktime(0, 0, 0, $m, 1, $tahunLaporan)), range(1, 12));
        // dd($indexTu);

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

        $y=0;

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
            if ($_GET['Tahun'] == '2023') {
                array_push($nilaiSkm, 0);
            } else {
                array_push($nilaiSkm, $nrrTT[$i + 1] * 25);
            }
        }

        // dd($nilaiSkm);

        $baseQuery = \App\Support\SurveyFilter::query();

        $tu1 = (clone $baseQuery)->get()->sum('u1');
        $tu2 = (clone $baseQuery)->get()->sum('u2');
        $tu3 = (clone $baseQuery)->get()->sum('u3');
        $tu4 = (clone $baseQuery)->get()->sum('u4');
        $tu5 = (clone $baseQuery)->get()->sum('u5');
        $tu6 = (clone $baseQuery)->get()->sum('u6');
        $tu7 = (clone $baseQuery)->get()->sum('u7');
        $tu8 = (clone $baseQuery)->get()->sum('u8');
        $tu9 = (clone $baseQuery)->get()->sum('u9');
        $n = (clone $baseQuery)->get()->count();

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
            // }
            // dd($responden);

            if ($_GET['Tahun'] == '2023') {
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
        $baseStatsQuery = \App\Support\SurveyFilter::query();

        $jmlResponden = (clone $baseStatsQuery)->count();
        $jenkel_l = (clone $baseStatsQuery)->where('jenkel', 'Laki - Laki')->count();
        $jenkel_p = (clone $baseStatsQuery)->where('jenkel', 'Perempuan')->count();

        $umur_1 = (clone $baseStatsQuery)->whereBetween('usia', [0, 29])->count();
        $umur_2 = (clone $baseStatsQuery)->whereBetween('usia', [30, 40])->count();
        $umur_3 = (clone $baseStatsQuery)->whereBetween('usia', [41, 50])->count();
        $umur_4 = (clone $baseStatsQuery)->whereBetween('usia', [51, 100])->count();

        $pendidikan_1 = (clone $baseStatsQuery)->where('pendidikan', 'SD')->count();
        $pendidikan_2 = (clone $baseStatsQuery)->where('pendidikan', 'SMP')->count();
        $pendidikan_3 = (clone $baseStatsQuery)->where('pendidikan', 'SMA / SMK')->count();
        $pendidikan_4 = (clone $baseStatsQuery)->where('pendidikan', 'D-I / D-III')->count();
        $pendidikan_5 = (clone $baseStatsQuery)->where('pendidikan', 'S1 / Setara')->count();
        $pendidikan_6 = (clone $baseStatsQuery)->where('pendidikan', 'S2 / S3')->count();

        $pekerjaan_1 = (clone $baseStatsQuery)->where('pekerjaan', 'ASN')->count();
        $pekerjaan_2 = (clone $baseStatsQuery)->where('pekerjaan', 'TNI / POLRI')->count();
        $pekerjaan_3 = (clone $baseStatsQuery)->where('pekerjaan', 'Swasta')->count();
        $pekerjaan_4 = (clone $baseStatsQuery)->where('pekerjaan', 'Pengusaha')->count();
        $pekerjaan_5 = (clone $baseStatsQuery)->where('pekerjaan', 'Pelajar')->count();
        $pekerjaan_6 = (clone $baseStatsQuery)->where('pekerjaan', 'Lainnya')->count();

        $safeTotal = $jmlResponden > 0 ? $jmlResponden : 1;
        $jenkel_l_psn = $jenkel_l * 100 / $safeTotal;
        $jenkel_p_psn = $jenkel_p * 100 / $safeTotal;
        $pendidikan_1_psn = $pendidikan_1 * 100 / $safeTotal;
        $pendidikan_2_psn = $pendidikan_2 * 100 / $safeTotal;
        $pendidikan_3_psn = $pendidikan_3 * 100 / $safeTotal;
        $pendidikan_4_psn = $pendidikan_4 * 100 / $safeTotal;
        $pendidikan_5_psn = $pendidikan_5 * 100 / $safeTotal;
        $pendidikan_6_psn = $pendidikan_6 * 100 / $safeTotal;
        $pekerjaan_1_psn = $pekerjaan_1 * 100 / $safeTotal;
        $pekerjaan_2_psn = $pekerjaan_2 * 100 / $safeTotal;
        $pekerjaan_3_psn = $pekerjaan_3 * 100 / $safeTotal;
        $pekerjaan_4_psn = $pekerjaan_4 * 100 / $safeTotal;
        $pekerjaan_5_psn = $pekerjaan_5 * 100 / $safeTotal;
        $pekerjaan_6_psn = $pekerjaan_6 * 100 / $safeTotal;

        //-----------------------------------------------------------------------------------------------------------------

        //Open template with ${table}
        // Laporan PPM memakai template umum. Nama pilar ditulis bila hanya satu pilar dipilih.
        $templateProcessor = new TemplateProcessor(public_path('word-template/exportsetkab.docx'));
        $pilarTerpilih = \App\Support\SurveyFilter::selectedPilar();
        $namaBagian = count($pilarTerpilih) === 1
            ? 'Pilar ' . (\App\Support\SurveyFilter::pilarOptions()[$pilarTerpilih[0]] ?? '')
            : '';

        // Penilaian bersifat tahunan: periode laporan selalu Januari - Desember.
        $tahun = $tahunLaporan;
        $bulanAwal = "Januari";
        $bulanAkhir = "Desember";
        $semester = "2";

        if ($nilaiSKM >= 0 and $nilaiSKM <= 24.99) {
            $mutu = 'E';
        } elseif ($nilaiSKM >= 25.0 and $nilaiSKM <= 64.99) {
            $mutu = 'D';
        } elseif ($nilaiSKM >= 65.0 and $nilaiSKM <= 76.6) {
            $mutu = 'C';
        } elseif ($nilaiSKM >= 76.61 and $nilaiSKM <= 88.3) {
            $mutu = 'B';
        } elseif ($nilaiSKM >= 88.4) {
            $mutu = 'A';
        } else {
            $mutu = 'E';
        }

        if ($nilaiSKM >= 0 and $nilaiSKM <= 24.99) {
            $kinerja = 'SANGAT TIDAK MEMUASKAN';
        } elseif ($nilaiSKM >= 25.0 and $nilaiSKM <= 64.99) {
            $kinerja = 'TIDAK MEMUASKAN';
        } elseif ($nilaiSKM >= 65.0 and $nilaiSKM <= 76.6) {
            $kinerja = 'KURANG MEMUASKAN';
        } elseif ($nilaiSKM >= 76.61 and $nilaiSKM <= 88.3) {
            $kinerja = 'MEMUASKAN';
        } elseif ($nilaiSKM >= 88.4) {
            $kinerja = 'SANGAT MEMUASKAN';
        } else {
            $kinerja = 'SANGAT TIDAK MEMUASKAN';
        }

        $terbanyakUsia = "";
        $terbanyakPendidikan = "";
        $terbanyakPekerjaan = "";

        $max = 0;
        if($umur_1 >= $max){
            $max = $umur_1;
            $terbanyakUsia = "Kurang dari 30 Tahun";
        }

        if($umur_2 >= $max){
            $max = $umur_2;
            $terbanyakUsia = "30 s/d 40 Tahun";
        }

        if($umur_3 >= $max){
            $max = $umur_3;
            $terbanyakUsia = "41 s/d 50 Tahun";
        }

        if($umur_4 >= $max){
            $max = $umur_4;
            $terbanyakUsia = "Lebih dari 50 Tahun";
        }

        $max = 0;
        if($pendidikan_1 >= $max){
            $max = $pendidikan_1;
            $terbanyakPendidikan = "SD";
        }

        if($pendidikan_2 >= $max){
            $max = $pendidikan_2;
            $terbanyakPendidikan = "SMP";
        }

        if($pendidikan_3 >= $max){
            $max = $pendidikan_3;
            $terbanyakPendidikan = "SMA/SMK";
        }

        if($pendidikan_4 >= $max){
            $max = $pendidikan_4;
            $terbanyakPendidikan = "DI/DIII";
        }

        if($pendidikan_5 >= $max){
            $max = $pendidikan_5;
            $terbanyakPendidikan = "SI/SETARA";
        }

        if($pendidikan_6 >= $max){
            $max = $pendidikan_6;
            $terbanyakPendidikan = "S2/S3";
        }

        $max = 0;
        if($pekerjaan_1 >= $max){
            $max = $pekerjaan_1;
            $terbanyakPekerjaan = "ASN";
        }

        if($pekerjaan_2 >= $max){
            $max = $pekerjaan_2;
            $terbanyakPekerjaan = "TNI/POLRI";
        }

        if($pekerjaan_3 >= $max){
            $max = $pekerjaan_3;
            $terbanyakPekerjaan = "Swasta";
        }

        if($pekerjaan_4 >= $max){
            $max = $pekerjaan_4;
            $terbanyakPekerjaan = "Pengusaha";
        }

        if($pekerjaan_5 >= $max){
            $max = $pekerjaan_5;
            $terbanyakPekerjaan = "Pelajar";
        }

        if($pekerjaan_6 >= $max){
            $max = $pekerjaan_6;
            $terbanyakPekerjaan = "Lainnya";
        }
        // dd($tahun);
        // Replace mark by xml code of table
        $checkedBox='<w:sym w:font="Wingdings" w:char="F0FE"/>';
        $unCheckedBox = '<w:sym w:font="Wingdings" w:char="F0A8"/>';
        $templateProcessor->setValue('checkBox',$checkedBox);

        $templateProcessor->setValue('bulanAwal', $bulanAwal);
        $templateProcessor->setValue('bulanAkhir', $bulanAkhir);
        $templateProcessor->setValue('semester', $semester);
        $templateProcessor->setValue('tahun', $tahun);
        $templateProcessor->setValue('jmlResponden', $jmlResponden);
        $templateProcessor->setValue('jenkel_p', $jenkel_p);
        $templateProcessor->setValue('jenkel_l', $jenkel_l);
        $templateProcessor->setValue('jenkel_p_psn', $jenkel_p_psn);
        $templateProcessor->setValue('jenkel_l_psn', $jenkel_l_psn);
        $templateProcessor->setValue('umur_1', $umur_1);
        $templateProcessor->setValue('umur_2', $umur_2);
        $templateProcessor->setValue('umur_3', $umur_3);
        $templateProcessor->setValue('umur_4', $umur_4);
        $templateProcessor->setValue('pendidikan_1', $pendidikan_1);
        $templateProcessor->setValue('pendidikan_2', $pendidikan_2);
        $templateProcessor->setValue('pendidikan_3', $pendidikan_3);
        $templateProcessor->setValue('pendidikan_4', $pendidikan_4);
        $templateProcessor->setValue('pendidikan_5', $pendidikan_5);
        $templateProcessor->setValue('pendidikan_6', $pendidikan_6);
        $templateProcessor->setValue('pendidikan_1_psn', $pendidikan_1_psn);
        $templateProcessor->setValue('pendidikan_2_psn', $pendidikan_2_psn);
        $templateProcessor->setValue('pendidikan_3_psn', $pendidikan_3_psn);
        $templateProcessor->setValue('pendidikan_4_psn', $pendidikan_4_psn);
        $templateProcessor->setValue('pendidikan_5_psn', $pendidikan_5_psn);
        $templateProcessor->setValue('pendidikan_6_psn', $pendidikan_6_psn);
        $templateProcessor->setValue('pekerjaan_1', $pekerjaan_1);
        $templateProcessor->setValue('pekerjaan_2', $pekerjaan_2);
        $templateProcessor->setValue('pekerjaan_3', $pekerjaan_3);
        $templateProcessor->setValue('pekerjaan_4', $pekerjaan_4);
        $templateProcessor->setValue('pekerjaan_5', $pekerjaan_5);
        $templateProcessor->setValue('pekerjaan_6', $pekerjaan_6);
        $templateProcessor->setValue('pekerjaan_1_psn', $pekerjaan_1_psn);
        $templateProcessor->setValue('pekerjaan_2_psn', $pekerjaan_2_psn);
        $templateProcessor->setValue('pekerjaan_3_psn', $pekerjaan_3_psn);
        $templateProcessor->setValue('pekerjaan_4_psn', $pekerjaan_4_psn);
        $templateProcessor->setValue('pekerjaan_5_psn', $pekerjaan_5_psn);
        $templateProcessor->setValue('pekerjaan_6_psn', $pekerjaan_6_psn);
        $templateProcessor->setValue('pekerjaan_1_psn', $pekerjaan_1_psn);
        $templateProcessor->setValue('pekerjaan_2_psn', $pekerjaan_2_psn);
        $templateProcessor->setValue('pekerjaan_3_psn', $pekerjaan_3_psn);
        $templateProcessor->setValue('pekerjaan_4_psn', $pekerjaan_4_psn);
        $templateProcessor->setValue('pekerjaan_5_psn', $pekerjaan_5_psn);
        $templateProcessor->setValue('pekerjaan_6_psn', $pekerjaan_6_psn);
        $templateProcessor->setValue('nilaiSkm', sprintf('%0.2f', $nilaiSKM));
        $templateProcessor->setValue('nrrT', $nrrT);
        $templateProcessor->setValue('mutu', $mutu);
        $templateProcessor->setValue('kinerja', $kinerja);
        $templateProcessor->setValue('hariIni', $hariIni);
        $templateProcessor->setValue('bulanIni', $bulanIni);
        $templateProcessor->setValue('tahunIni', $tahunIni);
        $templateProcessor->setValue( 'terbanyakUsia', $terbanyakUsia);
        $templateProcessor->setValue('terbanyakPendidikan', $terbanyakPendidikan);
        $templateProcessor->setValue('terbanyakPekerjaan', $terbanyakPekerjaan);
        // $templateProcessor->setValue('jmlResponden', $jmlResponden);

        $style = array('width' => Converter::cmToEmu(5), 'height' => Converter::cmToEmu(4), '3d' => true);

        $categories = array('Laki - Laki', 'Perempuan');
        $series1 = array($jenkel_l, $jenkel_p);
        $chartJenkel = new Chart('column', $categories, $series1);
        $chartJenkel->getStyle()->setWidth(Converter::inchToEmu(4))->setHeight(Converter::inchToEmu(2));
        $templateProcessor->setChart('chartJenkel', $chartJenkel);

        $chartStyle2 = array(
            'width' => "800"
        );

        $categories = array('Kurang dari 30 Tahun', '30 s/d 40 Tahun', '41 s/d 50 Tahun', 'Lebih dari 50 Tahun');
        $series1 = array($umur_1, $umur_2, $umur_3, $umur_4);
        $chartUmur = new Chart('column', $categories, $series1, $chartStyle2);
        $chartUmur->getStyle()->setWidth(Converter::inchToEmu(4))->setHeight(Converter::inchToEmu(2));
        $templateProcessor->setChart('chartUmur', $chartUmur);

        $chartStyle3 = array(
            'width' => 800,
            'height'  => 300,
            'tittle' => "Gambar 3 Tingkat Pendidikan Responden"
        );


        $categories = array('SD', 'SMP', 'SMA/SMK', 'DI/DIII', 'S1/SETARA', 'S2/S3');
        $series1 = array($pendidikan_1, $pendidikan_2, $pendidikan_3, $pendidikan_4, $pendidikan_5, $pendidikan_6);
        $chartPendidikan = new Chart('column', $categories, $series1, $chartStyle3);
        $chartPendidikan->getStyle()->setWidth(Converter::inchToEmu(4))->setHeight(Converter::inchToEmu(2));
        $templateProcessor->setChart('chartPendidikan', $chartPendidikan);

        $chartStyle4 = array(
            'width' => 800,
            'height'  => 300,
            'tittle' => "Gambar 4 Jenis Pekerjaan Responden"
        );

        $categories = array('ASN', 'TNI/POLRI', 'Swasta', 'Pengusaha', 'Pelajar', 'Lainnya');
        $series1 = array($pekerjaan_1, $pekerjaan_2, $pekerjaan_3, $pekerjaan_4, $pekerjaan_5, $pekerjaan_6);
        $chartPekerjaan = new Chart('column', $categories, $series1, $chartStyle4);
        $chartPekerjaan->getStyle()->setWidth(Converter::inchToEmu(4))->setHeight(Converter::inchToEmu(2));
        $templateProcessor->setChart('chartPekerjaan', $chartPekerjaan);

        $baseReportQuery = \App\Support\SurveyFilter::query();

        $nilaiUnsur = (clone $baseReportQuery)->get();

        // dd($nilaiUnsur);

        $table = new Table(array('unit' => TblWidth::TWIP));
        // foreach ($details as $detail) {
        $table->addRow();
        $table->addCell(1500)->addText("No.");
        $table->addCell(800)->addText("U1");
        $table->addCell(800)->addText("U2");
        $table->addCell(800)->addText("U3");
        $table->addCell(800)->addText("U4");
        $table->addCell(800)->addText("U5");
        $table->addCell(800)->addText("U6");
        $table->addCell(800)->addText("U7");
        $table->addCell(800)->addText("U8");
        $table->addCell(800)->addText("U9");
        $table->addCell(800)->addText("HASIL");

        foreach($nilaiUnsur as $k=>$item){
            $table->addRow();
            $table->addCell(1500)->addText( $k++);
            $table->addCell(800)->addText( $item->u1);
            $table->addCell(800)->addText( $item->u2);
            $table->addCell(800)->addText( $item->u3);
            $table->addCell(800)->addText( $item->u4);
            $table->addCell(800)->addText( $item->u5);
            $table->addCell(800)->addText( $item->u6);
            $table->addCell(800)->addText( $item->u7);
            $table->addCell(800)->addText( $item->u8);
            $table->addCell(800)->addText( $item->u9);
            $table->addCell(800)->addText( '');
        }

        $tu1 = (clone $baseReportQuery)->sum('u1');
        $tu2 = (clone $baseReportQuery)->sum('u2');
        $tu3 = (clone $baseReportQuery)->sum('u3');
        $tu4 = (clone $baseReportQuery)->sum('u4');
        $tu5 = (clone $baseReportQuery)->sum('u5');
        $tu6 = (clone $baseReportQuery)->sum('u6');
        $tu7 = (clone $baseReportQuery)->sum('u7');
        $tu8 = (clone $baseReportQuery)->sum('u8');
        $tu9 = (clone $baseReportQuery)->sum('u9');
        $n = (clone $baseReportQuery)->count();

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
            $nilaiSKM = sprintf('%0.3f', $nilaiSKM);
        }

        $table->addRow();
        $table->addCell(1500)->addText( 'Total Nilai Unsur');
        $table->addCell(800)->addText( $tu1);
        $table->addCell(800)->addText( $tu2);
        $table->addCell(800)->addText( $tu3);
        $table->addCell(800)->addText( $tu4);
        $table->addCell(800)->addText( $tu5);
        $table->addCell(800)->addText( $tu6);
        $table->addCell(800)->addText( $tu7);
        $table->addCell(800)->addText( $tu8);
        $table->addCell(800)->addText( $tu9);
        $table->addCell(800)->addText( '');

        $table->addRow();
        $table->addCell(1500)->addText( 'NRR Per Unsur');
        $table->addCell(800)->addText( $nrr1);
        $table->addCell(800)->addText( $nrr2);
        $table->addCell(800)->addText( $nrr3);
        $table->addCell(800)->addText( $nrr4);
        $table->addCell(800)->addText( $nrr5);
        $table->addCell(800)->addText( $nrr6);
        $table->addCell(800)->addText( $nrr7);
        $table->addCell(800)->addText( $nrr8);
        $table->addCell(800)->addText( $nrr9);
        $table->addCell(800)->addText( $nrr);

        if ($nrr1 >= 0.0 and $nrr1 <= 0.99) { $knrr1 =  'E'; } elseif ($nrr1 >= 1.0 and $nrr1 <= 2.599) { $knrr1 =  'D'; } elseif ($nrr1 >= 2.6 and $nrr1 <= 3.064) { $knrr1 =  'C'; } elseif ($nrr1 >= 3.064 and $nrr1 <= 3.532) { $knrr1 =  'B'; } else { $knrr1 =  'A'; }
        if ($nrr2 >= 0.0 and $nrr2 <= 0.99) { $knrr2 =  'E'; } elseif ($nrr2 >= 2.0 and $nrr2 <= 2.599) { $knrr2 =  'D'; } elseif ($nrr2 >= 2.6 and $nrr2 <= 3.064) { $knrr2 =  'C'; } elseif ($nrr2 >= 3.064 and $nrr2 <= 3.532) { $knrr2 =  'B'; } else { $knrr2 =  'A'; }
        if ($nrr3 >= 0.0 and $nrr3 <= 0.99) { $knrr3 =  'E'; } elseif ($nrr3 >= 3.0 and $nrr3 <= 2.599) { $knrr3 =  'D'; } elseif ($nrr3 >= 2.6 and $nrr3 <= 3.064) { $knrr3 =  'C'; } elseif ($nrr3 >= 3.064 and $nrr3 <= 3.532) { $knrr3 =  'B'; } else { $knrr3 =  'A'; }
        if ($nrr4 >= 0.0 and $nrr4 <= 0.99) { $knrr4 =  'E'; } elseif ($nrr4 >= 4.0 and $nrr4 <= 2.599) { $knrr4 =  'D'; } elseif ($nrr4 >= 2.6 and $nrr4 <= 3.064) { $knrr4 =  'C'; } elseif ($nrr4 >= 3.064 and $nrr4 <= 3.532) { $knrr4 =  'B'; } else { $knrr4 =  'A'; }
        if ($nrr5 >= 0.0 and $nrr5 <= 0.99) { $knrr5 =  'E'; } elseif ($nrr5 >= 5.0 and $nrr5 <= 2.599) { $knrr5 =  'D'; } elseif ($nrr5 >= 2.6 and $nrr5 <= 3.064) { $knrr5 =  'C'; } elseif ($nrr5 >= 3.064 and $nrr5 <= 3.532) { $knrr5 =  'B'; } else { $knrr5 =  'A'; }
        if ($nrr6 >= 0.0 and $nrr6 <= 0.99) { $knrr6 =  'E'; } elseif ($nrr6 >= 6.0 and $nrr6 <= 2.599) { $knrr6 =  'D'; } elseif ($nrr6 >= 2.6 and $nrr6 <= 3.064) { $knrr6 =  'C'; } elseif ($nrr6 >= 3.064 and $nrr6 <= 3.532) { $knrr6 =  'B'; } else { $knrr6 =  'A'; }
        if ($nrr7 >= 0.0 and $nrr7 <= 0.99) { $knrr7 =  'E'; } elseif ($nrr7 >= 7.0 and $nrr7 <= 2.599) { $knrr7 =  'D'; } elseif ($nrr7 >= 2.6 and $nrr7 <= 3.064) { $knrr7 =  'C'; } elseif ($nrr7 >= 3.064 and $nrr7 <= 3.532) { $knrr7 =  'B'; } else { $knrr7 =  'A'; }
        if ($nrr8 >= 0.0 and $nrr8 <= 0.99) { $knrr8 =  'E'; } elseif ($nrr8 >= 8.0 and $nrr8 <= 2.599) { $knrr8 =  'D'; } elseif ($nrr8 >= 2.6 and $nrr8 <= 3.064) { $knrr8 =  'C'; } elseif ($nrr8 >= 3.064 and $nrr8 <= 3.532) { $knrr8 =  'B'; } else { $knrr8 =  'A'; }
        if ($nrr9 >= 0.0 and $nrr9 <= 0.99) { $knrr9 =  'E'; } elseif ($nrr9 >= 9.0 and $nrr9 <= 2.599) { $knrr9 =  'D'; } elseif ($nrr9 >= 2.6 and $nrr9 <= 3.064) { $knrr9 =  'C'; } elseif ($nrr9 >= 3.064 and $nrr9 <= 3.532) { $knrr9 =  'B'; } else { $knrr9 =  'A'; }

        $templateProcessor->setValue(search: 'nrr1', replace: $nrr1);
        $templateProcessor->setValue(search: 'nrr2', replace: $nrr2);
        $templateProcessor->setValue(search: 'nrr3', replace: $nrr3);
        $templateProcessor->setValue(search: 'nrr4', replace: $nrr4);
        $templateProcessor->setValue(search: 'nrr5', replace: $nrr5);
        $templateProcessor->setValue(search: 'nrr6', replace: $nrr6);
        $templateProcessor->setValue(search: 'nrr7', replace: $nrr7);
        $templateProcessor->setValue(search: 'nrr8', replace: $nrr8);
        $templateProcessor->setValue(search: 'nrr9', replace: $nrr9);

        $templateProcessor->setValue(search: 'knrr1', replace: $knrr1);
        $templateProcessor->setValue(search: 'knrr2', replace: $knrr2);
        $templateProcessor->setValue(search: 'knrr3', replace: $knrr3);
        $templateProcessor->setValue(search: 'knrr4', replace: $knrr4);
        $templateProcessor->setValue(search: 'knrr5', replace: $knrr5);
        $templateProcessor->setValue(search: 'knrr6', replace: $knrr6);
        $templateProcessor->setValue(search: 'knrr7', replace: $knrr7);
        $templateProcessor->setValue(search: 'knrr8', replace: $knrr8);
        $templateProcessor->setValue(search: 'knrr9', replace: $knrr9);

        $table->addRow();
        $table->addCell(200)->addText(  'NRR Tertimbang');
        $table->addCell(500)->addText( $nrrt1);
        $table->addCell(500)->addText( $nrrt2);
        $table->addCell(500)->addText( $nrrt3);
        $table->addCell(500)->addText( $nrrt4);
        $table->addCell(500)->addText( $nrrt5);
        $table->addCell(500)->addText( $nrrt6);
        $table->addCell(500)->addText( $nrrt7);
        $table->addCell(500)->addText( $nrrt8);
        $table->addCell(500)->addText( $nrrt9);
        $table->addCell(500)->addText( $nrrT);

        $templateProcessor->setValue(search: 'nrrt1', replace: $nrrt1);
        $templateProcessor->setValue(search: 'nrrt2', replace: $nrrt2);
        $templateProcessor->setValue(search: 'nrrt3', replace: $nrrt3);
        $templateProcessor->setValue(search: 'nrrt4', replace: $nrrt4);
        $templateProcessor->setValue(search: 'nrrt5', replace: $nrrt5);
        $templateProcessor->setValue(search: 'nrrt6', replace: $nrrt6);
        $templateProcessor->setValue(search: 'nrrt7', replace: $nrrt7);
        $templateProcessor->setValue(search: 'nrrt8', replace: $nrrt8);
        $templateProcessor->setValue(search: 'nrrt9', replace: $nrrt9);

        $table->addRow();
        $table->addCell(200)->addText( 'Nilai SKM DISEPRINDAG MAHULU per'. $bulanAkhir. ' '. $tahun);
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( '');
        $table->addCell(500)->addText( sprintf('%0.3f', $nrrT * 25));
        // }
        // $phpWord = new TemplateProcessor('template.docx');
        $templateProcessor->setComplexBlock('{tabelNilaiUnsurPelayanan}', $table);


        $table = new Table(array('unit' => TblWidth::TWIP));
        // foreach ($details as $detail) {
        $table->addRow();
        $table->addCell(700)->addText("No.");
        $table->addCell(8000)->addText("Saran Perbaikan");

        foreach($nilaiUnsur as $k=>$item){
            $table->addRow();
            $table->addCell(200)->addText( $k++);
            $table->addCell(5000)->addText( $item->saran);
        }

        $templateProcessor->setComplexBlock('{tabelSaran}', $table);
        //save template with table
        // Simpan sementara di storage (bukan folder public), lalu kirim dan hapus.
        $namaFile = trim('Laporan SKM ' . $namaBagian . ' PT Bayan Tahun ' . $tahun) . '.docx';
        $path = storage_path('app/laporan-' . uniqid() . '.docx');
        $templateProcessor->saveAs($path);
        return response()->download($path, $namaFile)->deleteFileAfterSend(true);
    }

    // $document_with_table = new \PhpOffice\PhpWord\PhpWord();
    //     $section = $document_with_table->addSection();

    //     $table = $section->addTable(['borderSize' => 3]);

    //     $table->addRow();
    //     $table->addCell(800)->addText("NO");
    //     $table->addCell(800)->addText("ID");
    //     $table->addCell(800)->addText("NAMA");
    //     $table->addCell(800)->addText("NILAI");

    //     //Open template with ${table}
    //     $templateProcessor = new TemplateProcessor('word-template/export.docx');

    //     // Replace mark by xml code of table
    //     $templateProcessor->setValue('gambarJenisKelamin', $table);

    //     //save template with table
    //     $templateProcessor->saveAs('eskm.docx');
    //     return response()->download('eskm.docx')->deleteFileAfterSend(true);
}
