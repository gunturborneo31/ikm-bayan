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

class ExportPekerjaan implements FromCollection, WithHeadings, WithCustomStartCell,  WithColumnFormatting, ShouldAutoSize, WithStyles

{
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B2')->getFont()->setBold(true);
        $sheet->getStyle('C2')->getFont()->setBold(true);
        $sheet->getStyle('D2')->getFont()->setBold(true);
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


    /**

    * @return \Illuminate\Support\Collection

    */

    public function collection()

    {
        error_reporting(0);

        $baseQuery = \App\Support\SurveyFilter::query();

        if($_GET['filter']==null or $_GET['filter']==1){
            $selects = $baseQuery->get(['nik','nama','pekerjaan','created_at']);
        } else if($_GET['filter']==2) {
            $selects = (clone $baseQuery)->where('pekerjaan', 'laki')->get(['nik','nama','pekerjaan','created_at']);
        } else if($_GET['filter']==3) {
            $selects = (clone $baseQuery)->where('pekerjaan', 'perempuan')->get(['nik','nama','pekerjaan','created_at']);
        } else {
            $selects = $baseQuery->get(['nik','nama','pekerjaan','created_at']);
        }

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
        return ["NIK","NAMA", "PEKERJAAN", "TANGGAL SURVEY"];

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

