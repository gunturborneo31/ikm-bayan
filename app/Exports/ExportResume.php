<?php



namespace App\Exports;



use App\Models\NilaiUnsur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class ExportResume implements FromCollection, WithHeadings, WithCustomStartCell,  WithColumnFormatting, ShouldAutoSize, WithStyles

{
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B2')->getFont()->setBold(true);
        $sheet->getStyle('C2')->getFont()->setBold(true);
        $sheet->getStyle('D2')->getFont()->setBold(true);
        $sheet->getStyle('E2')->getFont()->setBold(true);
        $sheet->getStyle('F2')->getFont()->setBold(true);
        $sheet->getStyle('G2')->getFont()->setBold(true);
        $sheet->getStyle('H2')->getFont()->setBold(true);
        $sheet->getStyle('I2')->getFont()->setBold(true);
        $sheet->getStyle('J2')->getFont()->setBold(true);
        $sheet->getStyle('K2')->getFont()->setBold(true);
        $sheet->getStyle('L2')->getFont()->setBold(true);
        $sheet->getStyle('M2')->getFont()->setBold(true);
        $sheet->getStyle('N2')->getFont()->setBold(true);
        $sheet->getStyle('O2')->getFont()->setBold(true);
        $sheet->getStyle('P2')->getFont()->setBold(true);
        $sheet->getStyle('Q2')->getFont()->setBold(true);
        $sheet->getStyle('R2')->getFont()->setBold(true);
    }

    public function columnFormats(): array
    {
        return [
            // 'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


    /**

    * @return \Illuminate\Support\Collection

    */

    public function collection()

    {
        error_reporting(0);

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

        $export = ['nama', 'jenisPelayanan'];
        

        if ($this->kolom('jenkel')) {
            array_push($export, "jenkel");
        }
         if ($this->kolom('usia')) {
            array_push($export, "usia");
        }
         if ($this->kolom('pekerjaan')) {
            array_push($export, "pekerjaan");
        }
         if ($this->kolom('pendidikan')) {
            array_push($export, "pendidikan");
        }

        array_push($export, "u1");
        array_push($export, "u2");
        array_push($export, "u3");
        array_push($export, "u4");
        array_push($export, "u5");
        array_push($export, "u6");
        array_push($export, "u7");
        array_push($export, "u8");
        array_push($export, "u9");
        array_push($export, "created_at");


        $selects = \App\Support\SurveyFilter::query()
            ->get($export);

        // dd($total);

        // return dd($_GET['filter']);

        return $selects;

    }



    /**

     * Write code on Method

     *

     * @return response()

     */

    public function headings(): array

    {
        $judul = ["NAMA", "JENIS PELAYANAN"];
        if ($this->kolom('jenkel')) {
            array_push($judul, "JENIS KELAMIN");
        }
         if ($this->kolom('usia')) {
            array_push($judul, "USIA (TAHUN)");
        }
         if ($this->kolom('pekerjaan')) {
            array_push($judul, "PEKERJAAN");
        }
         if ($this->kolom('pendidikan')) {
            array_push($judul, "PENDIDIKAN");
        }

            array_push($judul, "NILAI UNSUR");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "");
            array_push($judul, "TANGGAL SURVEY");

        return $judul;

    }

    /** Kolom identitas diekspor bila =1; bila parameter tidak dikirim, tetap diekspor. */
    private function kolom(string $nama): bool
    {
        return (string) request()->query($nama, '1') === '1';
    }

    public function startCell(): string
    {
        return 'B2';
    }

    	 public function registerEvents()
    {

		//border style
		$styleArray = [
				'borders' => [
					'outline' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
					//'color' => ['argb' => 'FFFF0000'],
						],
					],
				];
            }

}

