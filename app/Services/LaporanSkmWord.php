<?php

namespace App\Services;

use App\Support\PpmCatalog;
use App\Support\SurveyFilter;
use App\Support\SurveyOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/**
 * Laporan SKM PPM Bayan dalam format Word (.docx).
 *
 * Dokumen dibangun lewat kode (bukan template) supaya format konsisten:
 * setiap BAB mulai di halaman baru, satu jenis huruf, tabel bergaris, dan
 * seluruh angka & kalimat mengikuti filter aktif (Tahun, Pilar, Perusahaan).
 */
class LaporanSkmWord
{
    private const FONT = 'Times New Roman';
    private const ORANGE = 'C2410C';

    /** Saran perbaikan per unsur, dipakai bila unsur tersebut termasuk nilai terendah. */
    private const REKOMENDASI = [
        1 => 'Melibatkan masyarakat dan aparat desa sejak tahap perencanaan agar program PPM benar-benar sesuai kebutuhan wilayah.',
        2 => 'Memperkuat pengawasan pelaksanaan kegiatan agar berjalan tertib dan sesuai rencana yang telah disampaikan kepada masyarakat.',
        3 => 'Menyiapkan rencana keberlanjutan (pendampingan lanjutan, kelembagaan lokal, serah terima aset) agar manfaat program bertahan lama.',
        4 => 'Meningkatkan sosialisasi tujuan, manfaat dan jadwal program melalui pertemuan desa, papan informasi dan media yang mudah dijangkau.',
        5 => 'Meningkatkan kapasitas tim pelaksana melalui pelatihan teknis dan pendampingan, termasuk pelaksana dari mitra.',
        6 => 'Mempermudah akses masyarakat untuk mengikuti program, misalnya persyaratan yang sederhana dan lokasi kegiatan yang terjangkau.',
        7 => 'Menjaga sikap sopan, ramah dan menghargai masyarakat oleh seluruh tim pelaksana di lapangan.',
        8 => 'Mengukur dan meningkatkan dampak sosial-ekonomi program, misalnya melalui indikator pendapatan atau lapangan kerja penerima manfaat.',
        9 => 'Memastikan program tepat sasaran sehingga manfaatnya nyata dirasakan oleh masyarakat penerima.',
    ];

    private PhpWord $word;
    private array $filter = [];
    private array $data = [];

    /** Bangun dokumen dan simpan ke file sementara. @return array{path: string, name: string} */
    public function build(): array
    {
        $this->filter = $this->filterInfo();
        $this->data = $this->collect();

        // Wajib: tanpa ini karakter seperti & < > (mis. "Sosial & Budaya" atau isi saran) merusak file .docx
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $this->word = new PhpWord();
        $this->setupStyles();

        $section = $this->word->addSection($this->pageStyle());
        $this->footer($section);
        $this->cover($section);
        $this->babPendahuluan($section);
        $this->babMetodologi($section);
        $this->babHasil($section);
        $this->babKesimpulan($section);

        $path = storage_path('app/laporan-' . uniqid() . '.docx');
        IOFactory::createWriter($this->word, 'Word2007')->save($path);

        $nama = 'Laporan SKM PPM Bayan ' . $this->filter['tahun']
            . ($this->filter['pilar_singkat'] ? ' - ' . $this->filter['pilar_singkat'] : '')
            . ($this->filter['perusahaan_kode'] ? ' - ' . $this->filter['perusahaan_kode'] : '');

        return ['path' => $path, 'name' => preg_replace('/[\\\\\/:*?"<>|]/', '', $nama) . '.docx'];
    }

    // ── Data ─────────────────────────────────────────────────────────────

    private function filterInfo(): array
    {
        $pilarOptions = SurveyFilter::pilarOptions();
        $pilar = SurveyFilter::selectedPilar();
        $perusahaan = SurveyFilter::selectedPerusahaan();
        $semuaPilar = SurveyFilter::isAllPilar();

        return [
            'tahun' => SurveyFilter::year(),
            'pilar' => $semuaPilar ? 'Semua pilar' : implode('; ', array_map(fn ($k) => $pilarOptions[$k] ?? $k, $pilar)),
            'pilar_singkat' => $semuaPilar ? null : (count($pilar) === 1 ? ($pilarOptions[$pilar[0]] ?? $pilar[0]) : count($pilar) . ' pilar'),
            'perusahaan' => $perusahaan ? (SurveyFilter::perusahaanOptions()[$perusahaan] ?? $perusahaan) : 'Semua perusahaan',
            'perusahaan_kode' => $perusahaan,
        ];
    }

    private function collect(): array
    {
        $skor = implode(', ', array_map(fn ($i) => "SUM(u{$i}) as s{$i}, COUNT(u{$i}) as c{$i}", range(1, 9)));
        $agg = SurveyFilter::query()->selectRaw("COUNT(*) as n, {$skor}")->first();
        $n = (int) ($agg->n ?? 0);

        $unsur = [];
        foreach (SurveyOptions::UNSUR as $i => [$nama]) {
            $c = (int) ($agg->{"c{$i}"} ?? 0);
            $nrr = $c > 0 ? (float) $agg->{"s{$i}"} / $c : 0.0;
            $unsur[$i] = ['nama' => $nama, 'nrr' => $nrr, 'tertimbang' => $nrr / 9];
        }
        $ni = array_sum(array_column($unsur, 'tertimbang'));
        $skm = $n > 0 ? $ni * 25 : null;

        // Profil responden unik
        $responden = SurveyFilter::respondenQuery();
        $profil = [];
        foreach ([
            'jenis_responden' => ['Jenis Responden', SurveyOptions::JENIS_RESPONDEN],
            'jenkel' => ['Jenis Kelamin', SurveyOptions::JENKEL],
            'usia' => ['Usia', array_map(fn ($l) => $l . ' tahun', SurveyOptions::USIA)],
            'pendidikan' => ['Pendidikan', SurveyOptions::PENDIDIKAN],
            'pekerjaan' => ['Pekerjaan', SurveyOptions::PEKERJAAN],
        ] as $kolom => [$judul, $opsi]) {
            $hitung = (clone $responden)->selectRaw("{$kolom} as k, COUNT(*) as n")->groupBy($kolom)->pluck('n', 'k')->all();
            $baris = [];
            $isAssoc = array_keys($opsi) !== range(0, count($opsi) - 1);
            foreach ($opsi as $nilai => $label) {
                $key = $isAssoc ? $nilai : $label;
                $baris[] = ['label' => $label, 'n' => (int) ($hitung[$key] ?? 0)];
            }
            $lain = array_sum($hitung) - array_sum(array_column($baris, 'n'));
            if ($lain > 0) {
                $baris[] = ['label' => 'Tidak diisi', 'n' => $lain];
            }
            $profil[$kolom] = ['judul' => $judul, 'baris' => $baris];
        }
        $jumlahResponden = array_sum(array_column($profil['jenkel']['baris'], 'n'));

        return [
            'n' => $n,
            'responden' => $jumlahResponden,
            'unsur' => $unsur,
            'ni' => $ni,
            'skm' => $skm,
            'profil' => $profil,
            'perusahaan' => $this->grouped('perusahaan_dinilai', array_map(fn ($l) => $l, SurveyFilter::perusahaanOptions())),
            'desa' => $this->grouped('desa_id', DB::table('ppm_desa')->orderBy('urutan')->pluck('nama', 'id')->all()),
            'program' => $this->programRows(),
            'tidak_tahu' => $this->tidakTahu(),
            'saran' => $this->saran(),
        ];
    }

    /** SKM per kelompok; hanya kelompok yang punya data. */
    private function grouped(string $kolom, array $nama): array
    {
        $rows = SurveyFilter::query()
            ->whereNotNull($kolom)
            ->selectRaw("{$kolom} as k, COUNT(*) as n, " . $this->skorSql() . ' as skor')
            ->groupBy($kolom)->get()->keyBy('k');

        $out = [];
        foreach ($nama as $key => $label) {
            if (isset($rows[$key])) {
                $out[] = ['label' => $label, 'n' => (int) $rows[$key]->n, 'skm' => $this->skmDari($rows[$key])];
            }
        }

        return $out;
    }

    private function programRows(): array
    {
        $rows = SurveyFilter::query()->whereNotNull('program_id')
            ->selectRaw('program_id as k, COUNT(*) as n, ' . $this->skorSql() . ' as skor')
            ->groupBy('program_id')->get()->keyBy('k');
        $program = DB::table('ppm_program')->orderBy('pilar_id')->orderBy('urutan')->get();
        $pilar = DB::table('ppm_pilar')->pluck('nama', 'id');

        $out = [];
        foreach ($program as $p) {
            if (isset($rows[$p->id])) {
                $out[] = ['pilar' => $pilar[$p->pilar_id] ?? $p->pilar_id, 'label' => $p->nama, 'n' => (int) $rows[$p->id]->n, 'skm' => $this->skmDari($rows[$p->id])];
            }
        }

        return $out;
    }

    /** Persentase jawaban "Tidak tahu" per anak perusahaan (filter Tahun + Pilar). */
    private function tidakTahu(): array
    {
        $out = [];
        foreach (PpmCatalog::anakPerusahaan() as $kode => $nama) {
            $q = DB::table('survey_responses')->where('tahun', SurveyFilter::year())
                ->whereRaw('FIND_IN_SET(?, perusahaan_opsi)', [$kode]);
            if (! SurveyFilter::isAllPilar()) {
                $q->whereIn('jenisPelayanan', SurveyFilter::selectedPilar());
            }
            $row = $q->selectRaw('COUNT(*) as total, SUM(CASE WHEN perusahaan_jawaban = ? THEN 1 ELSE 0 END) as tt', [PpmCatalog::TIDAK_TAHU])->first();
            if ((int) $row->total > 0) {
                $out[] = ['label' => "{$kode} · {$nama}", 'total' => (int) $row->total, 'tt' => (int) $row->tt, 'persen' => $row->tt / $row->total * 100];
            }
        }

        return $out;
    }

    private function saran(): array
    {
        $desa = DB::table('ppm_desa')->pluck('nama', 'id');
        $rincian = DB::table('ppm_rincian')->pluck('nama', 'id');

        return SurveyFilter::query()
            ->whereNotNull('saran')->whereRaw("TRIM(saran) <> ''")
            ->orderBy('created_at')
            ->get(['saran', 'desa_id', 'rincian_id'])
            ->map(fn ($r) => ['saran' => trim($r->saran), 'desa' => $desa[$r->desa_id] ?? '-', 'rincian' => $rincian[$r->rincian_id] ?? '-'])
            ->all();
    }

    private function skorSql(): string
    {
        return 'SUM(' . implode('+', array_map(fn ($i) => "COALESCE(u{$i},0)", range(1, 9))) . ')';
    }

    private function skmDari(object $row): ?float
    {
        return $row->n > 0 ? (float) $row->skor / ($row->n * 9) * 25 : null;
    }

    // ── Gaya dokumen ─────────────────────────────────────────────────────

    private function setupStyles(): void
    {
        $w = $this->word;
        $w->setDefaultFontName(self::FONT);
        $w->setDefaultFontSize(12);
        $w->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('id-ID'));

        $w->setDefaultParagraphStyle(['alignment' => Jc::BOTH, 'spaceAfter' => Converter::pointToTwip(6), 'lineHeight' => 1.5]);

        // BAB: selalu halaman baru, rata tengah
        $w->addTitleStyle(1, ['name' => self::FONT, 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'pageBreakBefore' => true, 'spaceAfter' => Converter::pointToTwip(18), 'lineHeight' => 1.15, 'keepNext' => true]);
        // Sub-bab
        $w->addTitleStyle(2, ['name' => self::FONT, 'size' => 12, 'bold' => true],
            ['alignment' => Jc::LEFT, 'spaceBefore' => Converter::pointToTwip(12), 'spaceAfter' => Converter::pointToTwip(6), 'keepNext' => true, 'lineHeight' => 1.15]);

        $w->addParagraphStyle('Indent', ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => Converter::cmToTwip(1.25)], 'spaceAfter' => Converter::pointToTwip(6), 'lineHeight' => 1.5]);
        $w->addParagraphStyle('Center', ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 1.15]);
        $w->addParagraphStyle('Caption', ['alignment' => Jc::CENTER, 'spaceBefore' => Converter::pointToTwip(6), 'spaceAfter' => Converter::pointToTwip(4), 'lineHeight' => 1.15, 'keepNext' => true]);
        // Gaya sel tabel; varian "K" (keepNext) dipakai agar tabel pendek tidak terpotong ke halaman berikutnya
        foreach (['Cell' => Jc::LEFT, 'CellCenter' => Jc::CENTER, 'CellRight' => Jc::RIGHT] as $nama => $align) {
            $w->addParagraphStyle($nama, ['alignment' => $align, 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.0]);
            $w->addParagraphStyle($nama . 'K', ['alignment' => $align, 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.0, 'keepNext' => true]);
        }
        $w->addParagraphStyle('Bullet', ['alignment' => Jc::LEFT, 'spaceAfter' => Converter::pointToTwip(4), 'lineHeight' => 1.5,
            'indentation' => ['left' => Converter::cmToTwip(0.75), 'hanging' => Converter::cmToTwip(0.5)]]);
        $w->addParagraphStyle('Note', ['alignment' => Jc::LEFT, 'spaceAfter' => Converter::pointToTwip(8), 'lineHeight' => 1.15]);

        $w->addTableStyle('Grid', [
            'borderSize' => 6, 'borderColor' => '8A8A8A', 'cellMargin' => 70,
            'alignment' => JcTable::CENTER, 'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
        ]);
    }

    private function pageStyle(): array
    {
        return [
            'paperSize' => 'A4',
            'marginTop' => Converter::cmToTwip(3),
            'marginBottom' => Converter::cmToTwip(2.5),
            'marginLeft' => Converter::cmToTwip(3),
            'marginRight' => Converter::cmToTwip(2.5),
        ];
    }

    private function footer(Section $section): void
    {
        $footer = $section->addFooter();
        $run = $footer->addTextRun(['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $run->addText('Laporan SKM PPM Bayan Group ' . $this->filter['tahun'] . '  |  Halaman ', ['size' => 9, 'color' => '666666']);
        $run->addField('PAGE', [], [], null, ['size' => 9, 'color' => '666666']);
    }

    // ── Bagian dokumen ───────────────────────────────────────────────────

    private function cover(Section $s): void
    {
        $s->addTextBreak(2);
        $logo = public_path('assets/logo-bayan.png');
        if (is_file($logo)) {
            $s->addImage($logo, ['width' => 110, 'alignment' => Jc::CENTER]);
        }
        $s->addTextBreak(1);
        foreach (['LAPORAN', 'SURVEI KEPUASAN MASYARAKAT', 'PROGRAM PENGEMBANGAN DAN', 'PEMBERDAYAAN MASYARAKAT (PPM)'] as $baris) {
            $s->addText($baris, ['size' => 18, 'bold' => true], 'Center');
        }
        $s->addTextBreak(1);
        $s->addText('BAYAN GROUP', ['size' => 16, 'bold' => true, 'color' => self::ORANGE], 'Center');
        $s->addText('TAHUN ' . $this->filter['tahun'], ['size' => 16, 'bold' => true], 'Center');
        $s->addTextBreak(2);

        $t = $s->addTable(['borderSize' => 6, 'borderColor' => 'F4B183', 'cellMargin' => 100, 'alignment' => JcTable::CENTER,
            'width' => 100 * 50, 'unit' => TblWidth::PERCENT]);
        foreach ([
            'Periode' => 'Januari – Desember ' . $this->filter['tahun'],
            'Pilar' => $this->filter['pilar'],
            'Perusahaan dinilai' => $this->filter['perusahaan'],
            'Jumlah penilaian' => $this->angka($this->data['n']),
            'Jumlah responden' => $this->angka($this->data['responden']) . ' orang',
        ] as $label => $nilai) {
            $t->addRow();
            $t->addCell(Converter::cmToTwip(4.5), ['bgColor' => 'FFF2E8'])->addText($label, ['bold' => true, 'size' => 11], 'Cell');
            $t->addCell(Converter::cmToTwip(10.5))->addText($nilai, ['size' => 11], 'Cell');
        }
    }

    private function babPendahuluan(Section $s): void
    {
        $this->bab($s, 'BAB I', 'PENDAHULUAN');

        $s->addTitle('A. Latar Belakang', 2);
        $s->addText('Bayan Group melaksanakan Program Pengembangan dan Pemberdayaan Masyarakat (PPM) di desa-desa sekitar wilayah operasional melalui anak perusahaan, yaitu PT Bara Tabang (BT), PT Fajar Sakti Prima (FSP), PT Tabang Alas (TA) dan PT Tiwa Jayan (TJ). Program PPM mencakup bidang pendidikan, kesehatan, peningkatan pendapatan riil, kemandirian ekonomi, sosial budaya, lingkungan, kelembagaan komunitas dan infrastruktur.', null, 'Indent');
        $s->addText('Untuk mengetahui sejauh mana program tersebut memenuhi harapan masyarakat, dilakukan Survei Kepuasan Masyarakat (SKM) terhadap program PPM. Hasil survei menjadi dasar evaluasi dan perbaikan program pada periode berikutnya.', null, 'Indent');

        $s->addTitle('B. Tujuan', 2);
        foreach ([
            'Mengukur tingkat kepuasan masyarakat terhadap pelaksanaan program PPM Bayan Group.',
            'Mengetahui unsur program yang sudah baik dan unsur yang perlu ditingkatkan.',
            'Mengetahui tingkat kepuasan per anak perusahaan, per desa dan per program.',
            'Menghimpun saran dan masukan masyarakat sebagai bahan perbaikan program.',
        ] as $i => $teks) {
            $s->addText(($i + 1) . '. ' . $teks, null, 'Bullet');
        }

        $s->addTitle('C. Ruang Lingkup Laporan', 2);
        $s->addText(sprintf(
            'Laporan ini memuat hasil survei periode Januari – Desember %d dengan cakupan %s dan %s. Data yang diolah berjumlah %s penilaian dari %s responden.',
            $this->filter['tahun'],
            mb_strtolower($this->filter['pilar']) === 'semua pilar' ? 'seluruh pilar program' : 'pilar ' . $this->filter['pilar'],
            $this->filter['perusahaan_kode'] ? 'perusahaan yang dinilai ' . $this->filter['perusahaan'] : 'seluruh perusahaan pelaksana',
            $this->angka($this->data['n']),
            $this->angka($this->data['responden'])
        ), null, 'Indent');
        $s->addText('Satu responden dapat menilai lebih dari satu program. Karena itu jumlah penilaian dapat lebih besar daripada jumlah responden. Profil responden dihitung per orang, sedangkan nilai SKM dihitung per penilaian.', null, 'Indent');
    }

    private function babMetodologi(Section $s): void
    {
        $this->bab($s, 'BAB II', 'METODOLOGI SURVEI');

        $s->addTitle('A. Alur Pengisian Survei', 2);
        foreach ([
            'Responden memilih desa tempat tinggalnya.',
            'Responden memilih program dan rincian program yang berjalan di desa tersebut.',
            'Responden memilih perusahaan yang menurutnya melaksanakan program, atau menjawab "Tidak tahu".',
            'Responden mengisi identitas (cukup sekali walaupun menilai beberapa program).',
            'Responden menjawab 9 pertanyaan kuesioner dan memberikan saran.',
        ] as $i => $teks) {
            $s->addText(($i + 1) . '. ' . $teks, null, 'Bullet');
        }

        $s->addTitle('B. Unsur yang Diukur', 2);
        $s->addText('Kuesioner terdiri atas 9 unsur dengan pertanyaan yang sama untuk semua program:', null, 'Indent');
        $this->caption($s, 'Tabel 2.1', 'Unsur dan Pertanyaan Kuesioner');
        $rows = [];
        foreach (SurveyOptions::UNSUR as $i => [$nama]) {
            $rows[] = ["U{$i}", $nama, SurveyOptions::PERTANYAAN[$i] ?? ''];
        }
        $this->table($s, ['No', 'Unsur', 'Pertanyaan'], $rows, [1.3, 3.7, 10], ['CellCenter', 'Cell', 'Cell']);

        $s->addTitle('C. Skala Penilaian', 2);
        $s->addText('Setiap pertanyaan dijawab dengan empat pilihan bernilai 1 sampai 4. Contoh untuk unsur Kesesuaian Program:', null, 'Indent');
        $this->caption($s, 'Tabel 2.2', 'Skala Jawaban');
        $this->table($s, ['Nilai', 'Contoh Jawaban'],
            array_map(fn ($i, $label) => [(string) ($i + 1), $label], array_keys(SurveyOptions::UNSUR[1][1]), SurveyOptions::UNSUR[1][1]),
            [3, 12], ['CellCenter', 'Cell']);

        $s->addTitle('D. Penentuan Perusahaan yang Dinilai', 2);
        $s->addText('Seluruh program merupakan PPM Bayan Group sehingga masyarakat belum tentu dapat membedakan anak perusahaan pelaksananya. Jawaban responden tetap dicatat, lalu sistem menentukan perusahaan yang dinilai dengan aturan berikut:', null, 'Indent');
        foreach ([
            'Program Beasiswa dan empat program Kesehatan Masyarakat Sekitar Tambang selalu dinilai sebagai BAYAN (grup), sesuai sheet Klasifikasi.',
            'Jika responden memilih salah satu perusahaan pelaksana, perusahaan tersebut yang dinilai.',
            'Jika responden menjawab "Tidak tahu" dan pelaksana di desa itu hanya satu perusahaan, perusahaan tersebut yang dinilai (asumsi sistem).',
            'Jika responden menjawab "Tidak tahu" dan pelaksananya lebih dari satu, penilaian dicatat sebagai BAYAN (gabungan).',
        ] as $teks) {
            $s->addText('• ' . $teks, null, 'Bullet');
        }
        $s->addText('Penilaian BAYAN (grup/gabungan) dihitung dalam SKM Bayan Group, tetapi tidak dibebankan ke anak perusahaan.', null, 'Indent');

        $s->addTitle('E. Pengolahan Data', 2);
        $s->addText('Nilai SKM dihitung dengan nilai rata-rata tertimbang, mengacu pada Peraturan Menteri PANRB Nomor 14 Tahun 2017 tentang Pedoman Penyusunan Survei Kepuasan Masyarakat:', null, 'Indent');
        foreach ([
            'NRR per unsur = jumlah nilai unsur ÷ jumlah penilaian yang mengisi unsur tersebut.',
            'NRR tertimbang = NRR per unsur × 1/9 (bobot sama untuk 9 unsur, ≈ 0,111).',
            'Nilai Indeks (NI) = jumlah NRR tertimbang seluruh unsur.',
            'Nilai SKM = NI × 25 (rentang 25 – 100).',
        ] as $teks) {
            $s->addText('• ' . $teks, null, 'Bullet');
        }
        $this->caption($s, 'Tabel 2.3', 'Nilai Interval, Mutu dan Kinerja');
        $this->table($s, ['Nilai Interval (NI)', 'Nilai SKM', 'Mutu', 'Kinerja'], [
            ['1,00 – 2,5996', '25,00 – 64,99', 'D', 'Tidak Baik'],
            ['2,60 – 3,064', '65,00 – 76,60', 'C', 'Kurang Baik'],
            ['3,0644 – 3,532', '76,61 – 88,30', 'B', 'Baik'],
            ['3,5324 – 4,00', '88,31 – 100,00', 'A', 'Sangat Baik'],
        ], [4, 4, 2.5, 4.5], ['CellCenter', 'CellCenter', 'CellCenter', 'CellCenter']);
    }

    private function babHasil(Section $s): void
    {
        $this->bab($s, 'BAB III', 'HASIL SURVEI KEPUASAN MASYARAKAT');
        $d = $this->data;

        if ($d['n'] === 0) {
            $s->addText('Belum ada data penilaian untuk filter yang dipilih (' . $this->filterRingkas() . ').', ['italic' => true], 'Indent');

            return;
        }

        // A. Profil responden
        $s->addTitle('A. Profil Responden', 2);
        $s->addText(sprintf('Survei diikuti oleh %s responden yang memberikan %s penilaian. Profil responden adalah sebagai berikut:',
            $this->angka($d['responden']), $this->angka($d['n'])), null, 'Indent');
        $no = 1;
        foreach ($d['profil'] as $p) {
            $total = max(1, array_sum(array_column($p['baris'], 'n')));
            $this->caption($s, 'Tabel 3.' . $no++, 'Responden Berdasarkan ' . $p['judul']);
            $this->table($s, [$p['judul'], 'Jumlah', 'Persentase'],
                array_map(fn ($b) => [$b['label'], $this->angka($b['n']), $this->persen($b['n'] / $total * 100)], $p['baris']),
                [8, 3, 4], ['Cell', 'CellCenter', 'CellCenter']);
            $this->chart($s, array_column($p['baris'], 'label'), array_column($p['baris'], 'n'));
            $terbanyak = $this->terbanyak($p['baris']);
            if ($terbanyak) {
                $s->addText(sprintf('Responden terbanyak berdasarkan %s adalah %s, yaitu %s orang (%s).',
                    mb_strtolower($p['judul']), $terbanyak['label'], $this->angka($terbanyak['n']), $this->persen($terbanyak['n'] / $total * 100)), null, 'Indent');
            }
        }

        // B. Nilai per unsur
        $s->addTitle('B. Nilai per Unsur dan Nilai SKM', 2);
        $this->caption($s, 'Tabel 3.' . $no++, 'Nilai Rata-Rata per Unsur');
        $rows = [];
        foreach ($d['unsur'] as $i => $u) {
            [$mutu] = $this->mutu($u['nrr'] * 25);
            $rows[] = ["U{$i}", $u['nama'], $this->desimal($u['nrr'], 3), $this->desimal($u['tertimbang'], 3), $mutu];
        }
        [$mutuSkm, $kinerjaSkm] = $this->mutu($d['skm']);
        $rows[] = ['', 'Nilai Indeks (NI)', '', $this->desimal($d['ni'], 3), ''];
        $rows[] = ['', 'Nilai SKM (NI × 25)', '', $this->desimal($d['skm'], 2), $mutuSkm];
        $this->table($s, ['No', 'Unsur', 'NRR', 'NRR Tertimbang', 'Mutu'], $rows, [1.3, 6.2, 2.5, 3, 2], ['CellCenter', 'Cell', 'CellCenter', 'CellCenter', 'CellCenter'], 2);

        $s->addText(sprintf('Berdasarkan pengolahan data, nilai SKM PPM Bayan Group tahun %d %s adalah %s dengan mutu %s (%s).',
            $this->filter['tahun'], $this->filterKalimat(), $this->desimal($d['skm'], 2), $mutuSkm, $kinerjaSkm), ['bold' => true], 'Indent');

        [$terendah, $tertinggi] = $this->peringkatUnsur();
        $s->addText($terendah
            ? sprintf('Unsur dengan nilai tertinggi adalah %s (NRR %s), sedangkan unsur dengan nilai terendah adalah %s (NRR %s).',
                $tertinggi['nama'], $this->desimal($tertinggi['nrr'], 3), $terendah[0]['nama'], $this->desimal($terendah[0]['nrr'], 3))
            : sprintf('Seluruh unsur memperoleh nilai yang sama, yaitu NRR %s.', $this->desimal($tertinggi['nrr'], 3)), null, 'Indent');

        // C. Per perusahaan
        if (! $this->filter['perusahaan_kode'] && $d['perusahaan']) {
            $s->addTitle('C. Nilai SKM per Perusahaan', 2);
            $s->addText('Nilai per anak perusahaan hanya menghitung penilaian yang dinilai untuk perusahaan tersebut. Penilaian BAYAN (grup/gabungan) ditampilkan terpisah.', null, 'Indent');
            $this->caption($s, 'Tabel 3.' . $no++, 'Nilai SKM per Perusahaan yang Dinilai');
            $this->table($s, ['Perusahaan', 'Penilaian', 'Nilai SKM', 'Mutu'], $this->skmRows($d['perusahaan']), [7, 2.5, 3, 2.5], ['Cell', 'CellCenter', 'CellCenter', 'CellCenter']);
        }

        // D. Per desa
        if ($d['desa']) {
            $s->addTitle(($this->filter['perusahaan_kode'] ? 'C' : 'D') . '. Nilai SKM per Desa', 2);
            $this->caption($s, 'Tabel 3.' . $no++, 'Nilai SKM per Desa');
            $this->table($s, ['Desa', 'Penilaian', 'Nilai SKM', 'Mutu'], $this->skmRows($d['desa']), [7, 2.5, 3, 2.5], ['Cell', 'CellCenter', 'CellCenter', 'CellCenter']);
        }

        // E. Per program
        $huruf = $this->filter['perusahaan_kode'] ? ['D', 'E', 'F'] : ['E', 'F', 'G'];
        if ($d['program']) {
            $s->addTitle($huruf[0] . '. Nilai SKM per Program', 2);
            $this->caption($s, 'Tabel 3.' . $no++, 'Nilai SKM per Pilar dan Program');
            $rows = [];
            $pilarAktif = null;
            foreach ($d['program'] as $p) {
                if ($p['pilar'] !== $pilarAktif) {
                    $rows[] = ['__group__', $p['pilar']];
                    $pilarAktif = $p['pilar'];
                }
                [$m] = $this->mutu($p['skm']);
                $rows[] = [$p['label'], $this->angka($p['n']), $this->desimal($p['skm'], 2), $m];
            }
            $this->table($s, ['Program', 'Penilaian', 'Nilai SKM', 'Mutu'], $rows, [7, 2.5, 3, 2.5], ['Cell', 'CellCenter', 'CellCenter', 'CellCenter']);
        }

        // F. Tidak tahu
        if ($d['tidak_tahu']) {
            $s->addTitle($huruf[1] . '. Pengenalan Perusahaan Pelaksana', 2);
            $s->addText('Indikator ini menunjukkan seberapa dikenal program tiap anak perusahaan: dari penilaian di mana perusahaan tersebut menjadi pilihan, berapa persen responden menjawab "Tidak tahu". Semakin tinggi persentasenya, semakin kurang dikenal perusahaan pelaksananya.', null, 'Indent');
            $this->caption($s, 'Tabel 3.' . $no++, 'Persentase Jawaban "Tidak Tahu" per Perusahaan');
            $this->table($s, ['Perusahaan', 'Pilihan Muncul', 'Tidak Tahu', 'Persentase'],
                array_map(fn ($t) => [$t['label'], $this->angka($t['total']), $this->angka($t['tt']), $this->persen($t['persen'])], $d['tidak_tahu']),
                [6.5, 3, 2.5, 3], ['Cell', 'CellCenter', 'CellCenter', 'CellCenter']);
        }

        // G. Saran
        $s->addTitle($huruf[2] . '. Saran dan Masukan Responden', 2);
        if ($d['saran']) {
            $this->caption($s, 'Tabel 3.' . $no, 'Saran dan Masukan Responden');
            $this->table($s, ['No', 'Desa', 'Rincian Program', 'Saran'],
                array_map(fn ($r, $i) => [(string) ($i + 1), $r['desa'], $r['rincian'], $r['saran']], $d['saran'], array_keys($d['saran'])),
                [1.2, 3, 5, 5.8], ['CellCenter', 'Cell', 'Cell', 'Cell']);
        } else {
            $s->addText('Tidak ada saran yang diisi responden pada periode ini.', ['italic' => true], 'Indent');
        }
    }

    private function babKesimpulan(Section $s): void
    {
        $this->bab($s, 'BAB IV', 'KESIMPULAN DAN REKOMENDASI');
        $d = $this->data;

        $s->addTitle('A. Kesimpulan', 2);
        if ($d['n'] === 0) {
            $s->addText('Belum dapat ditarik kesimpulan karena belum ada data penilaian untuk filter yang dipilih.', null, 'Indent');
        } else {
            [$mutu, $kinerja] = $this->mutu($d['skm']);
            [$terendah, $tertinggi] = $this->peringkatUnsur();
            $poin = [
                sprintf('Nilai SKM PPM Bayan Group tahun %d %s adalah %s, termasuk mutu %s dengan kinerja "%s".', $this->filter['tahun'], $this->filterKalimat(), $this->desimal($d['skm'], 2), $mutu, $kinerja),
                sprintf('Survei diikuti oleh %s responden dengan %s penilaian.%s', $this->angka($d['responden']), $this->angka($d['n']), $this->kalimatProfil()),
                $terendah
                    ? sprintf('Unsur dengan nilai tertinggi adalah %s (NRR %s). Unsur dengan nilai terendah adalah %s.', $tertinggi['nama'], $this->desimal($tertinggi['nrr'], 3),
                        implode(', ', array_map(fn ($u) => $u['nama'] . ' (NRR ' . $this->desimal($u['nrr'], 3) . ')', $terendah)))
                    : sprintf('Seluruh unsur memperoleh nilai yang sama, yaitu NRR %s.', $this->desimal($tertinggi['nrr'], 3)),
            ];
            if (count($d['desa']) > 1) {
                $urut = collect($d['desa'])->sortBy('skm')->values();
                $poin[] = sprintf('Berdasarkan desa, nilai tertinggi ada di %s (%s) dan terendah di %s (%s).',
                    $urut->last()['label'], $this->desimal($urut->last()['skm'], 2), $urut->first()['label'], $this->desimal($urut->first()['skm'], 2));
            }
            $tt = collect($d['tidak_tahu'])->sortByDesc('persen')->first();
            if ($tt && $tt['persen'] > 0) {
                $poin[] = sprintf('Persentase jawaban "Tidak tahu" tertinggi ada pada %s (%s), menandakan program perusahaan tersebut perlu lebih dikenalkan.', $tt['label'], $this->persen($tt['persen']));
            }
            if ($d['saran']) {
                $poin[] = sprintf('Terdapat %s saran dan masukan dari responden yang perlu ditindaklanjuti (lihat Bab III).', $this->angka(count($d['saran'])));
            }
            foreach ($poin as $i => $teks) {
                $s->addText(($i + 1) . '. ' . $teks, null, 'Bullet');
            }
        }

        $s->addTitle('B. Rekomendasi', 2);
        if ($d['n'] === 0) {
            $s->addText('Melanjutkan pengumpulan data survei di seluruh desa penerima program PPM.', null, 'Indent');
        } else {
            [$terendah] = $this->peringkatUnsur();
            $s->addText($terendah
                ? 'Perbaikan diprioritaskan pada unsur dengan nilai terendah, sedangkan unsur yang sudah tinggi tetap dipertahankan:'
                : 'Seluruh unsur bernilai sama, sehingga rekomendasi diarahkan untuk mempertahankan kualitas program:', null, 'Indent');
            foreach ($terendah as $i => $u) {
                $s->addText(($i + 1) . '. ' . $u['nama'] . ': ' . self::REKOMENDASI[$u['no']], null, 'Bullet');
            }
            $s->addText((count($terendah) + 1) . '. Menindaklanjuti saran responden dan melaksanakan survei secara berkala setiap tahun untuk memantau perkembangan kepuasan masyarakat.', null, 'Bullet');
        }

        // Pengesahan (nama & jabatan diisi manual)
        $s->addTextBreak(2);
        $tanggal = Carbon::now()->locale('id')->translatedFormat('d F Y');
        // Blok tanda tangan tidak boleh terpotong ke dua halaman
        $t = $s->addTable(['alignment' => JcTable::END]);
        $t->addRow(null, ['cantSplit' => true]);
        $c = $t->addCell(Converter::cmToTwip(7.5));
        $baris = ["...................., {$tanggal}", 'Mengetahui,', '', '', '', '(..............................................)', 'Jabatan: ................................'];
        foreach ($baris as $i => $teks) {
            $c->addText($teks, null, $i < count($baris) - 1 ? 'CellCenterK' : 'CellCenter');
        }
    }

    // ── Helper tampilan ──────────────────────────────────────────────────

    private function bab(Section $s, string $nomor, string $judul): void
    {
        $run = new TextRun();
        $run->addText($nomor);
        $run->addTextBreak();
        $run->addText($judul);
        $s->addTitle($run, 1);
    }

    private function caption(Section $s, string $nomor, string $judul): void
    {
        $run = $s->addTextRun('Caption');
        $run->addText($nomor . '. ', ['bold' => true, 'size' => 11]);
        $run->addText($judul, ['bold' => true, 'size' => 11]);
    }

    /**
     * Tabel bergaris dengan baris judul berulang di tiap halaman.
     * Baris berawalan '__group__' ditampilkan sebagai sub-judul selebar tabel.
     */
    private function table(Section $s, array $head, array $rows, array $widthsCm, array $align, int $boldLastRows = 0): void
    {
        $total = count($rows);
        // Tabel pendek dijaga utuh dalam satu halaman; tabel panjang boleh berlanjut (judul kolom diulang).
        $utuh = $total <= 15;
        $keep = fn (string $style, bool $last) => $utuh && ! $last ? $style . 'K' : $style;

        $t = $s->addTable('Grid');
        $t->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
        foreach ($head as $i => $h) {
            $t->addCell(Converter::cmToTwip($widthsCm[$i]), ['bgColor' => 'F8CBAD', 'valign' => 'center'])
                ->addText($h, ['bold' => true, 'size' => 11], 'CellCenterK');
        }
        foreach ($rows as $r => $row) {
            $last = $r === $total - 1;
            $t->addRow(null, ['cantSplit' => true]);
            if (($row[0] ?? null) === '__group__') {
                $t->addCell(Converter::cmToTwip(array_sum($widthsCm)), ['gridSpan' => count($head), 'bgColor' => 'FFF2E8'])
                    ->addText($row[1], ['bold' => true, 'size' => 11], 'CellK');
                continue;
            }
            $bold = $boldLastRows > 0 && $r >= $total - $boldLastRows;
            foreach ($row as $i => $value) {
                $t->addCell(Converter::cmToTwip($widthsCm[$i]), ['valign' => 'center'] + ($bold ? ['bgColor' => 'FFF2E8'] : []))
                    ->addText((string) $value, ['size' => 11, 'bold' => $bold], $keep($align[$i] ?? 'Cell', $last));
            }
        }
        $s->addText('', ['size' => 4], 'Note');
    }

    private function chart(Section $s, array $labels, array $values): void
    {
        if (array_sum($values) === 0) {
            return;
        }
        $s->addChart('column', $labels, $values, [
            'width' => Converter::cmToEmu(15),
            'height' => Converter::cmToEmu(6),
            'showAxisLabels' => true,
            'showGridY' => false,
            'colors' => ['EA580C'],
            'dataLabelOptions' => ['showVal' => true, 'showCatName' => false, 'showLegendKey' => false, 'showSerName' => false, 'showPercent' => false],
        ]);
    }

    private function skmRows(array $rows): array
    {
        return array_map(function ($r) {
            [$m] = $this->mutu($r['skm']);

            return [$r['label'], $this->angka($r['n']), $this->desimal($r['skm'], 2), $m];
        }, $rows);
    }

    /**
     * @return array{0: array<int, array>, 1: array} maksimal tiga unsur terendah (hanya yang nilainya
     *         di bawah unsur tertinggi; kosong bila semua unsur bernilai sama), dan unsur tertinggi
     */
    private function peringkatUnsur(): array
    {
        $list = collect($this->data['unsur'])->map(fn ($u, $i) => $u + ['no' => $i]);
        $tertinggi = $list->sortByDesc('nrr')->first();
        $terendah = $list->filter(fn ($u) => round($u['nrr'], 3) < round($tertinggi['nrr'], 3))
            ->sortBy('nrr')->take(3)->values()->all();

        return [$terendah, $tertinggi];
    }

    /** Pilihan terbanyak, tidak termasuk "Tidak diisi". */
    private function terbanyak(array $baris): ?array
    {
        $top = collect($baris)->reject(fn ($b) => $b['label'] === 'Tidak diisi')->sortByDesc('n')->first();

        return $top && $top['n'] > 0 ? $top : null;
    }

    private function kalimatProfil(): string
    {
        $bagian = [];
        foreach (['jenis_responden' => 'jenis responden', 'jenkel' => 'jenis kelamin', 'usia' => 'usia', 'pendidikan' => 'pendidikan', 'pekerjaan' => 'pekerjaan'] as $k => $label) {
            $top = $this->terbanyak($this->data['profil'][$k]['baris']);
            if ($top) {
                $bagian[] = "{$label} {$top['label']}";
            }
        }

        return $bagian ? ' Responden terbanyak berdasarkan ' . implode(', ', $bagian) . '.' : '';
    }

    private function filterRingkas(): string
    {
        return 'Tahun ' . $this->filter['tahun'] . ', ' . $this->filter['pilar'] . ', ' . $this->filter['perusahaan'];
    }

    private function filterKalimat(): string
    {
        $bagian = [];
        if ($this->filter['pilar_singkat']) {
            $bagian[] = 'pada pilar ' . $this->filter['pilar'];
        }
        if ($this->filter['perusahaan_kode']) {
            $bagian[] = 'untuk perusahaan ' . $this->filter['perusahaan'];
        }

        return $bagian ? '(' . implode(', ', $bagian) . ')' : '(seluruh pilar dan perusahaan)';
    }

    /** @return array{0: string, 1: string} mutu dan kinerja */
    private function mutu(?float $skm): array
    {
        return match (true) {
            $skm === null => ['-', '-'],
            $skm >= 88.31 => ['A', 'Sangat Baik'],
            $skm >= 76.61 => ['B', 'Baik'],
            $skm >= 65 => ['C', 'Kurang Baik'],
            default => ['D', 'Tidak Baik'],
        };
    }

    private function angka(int|float $n): string
    {
        return number_format($n, 0, ',', '.');
    }

    private function desimal(?float $n, int $digit): string
    {
        return $n === null ? '-' : number_format($n, $digit, ',', '.');
    }

    private function persen(float $n): string
    {
        return number_format($n, 1, ',', '.') . '%';
    }
}
