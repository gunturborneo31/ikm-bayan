<?php

namespace App\Support;

/**
 * Answer options for the respondent form, shared by the admin input page
 * and the Excel import so both accept exactly the same values.
 */
class SurveyOptions
{
    public const JENKEL = ['Laki - Laki', 'Perempuan'];

    /** Stored value => label. Stored value is the lower bound of the age group. */
    public const USIA = [29 => 'Kurang dari 30', 30 => '30 s/d 40', 41 => '41 s/d 50', 51 => 'Lebih dari 50'];

    public const PENDIDIKAN = ['SD', 'SMP', 'SMA / SMK', 'D-I / D-III', 'S1 / Setara', 'S2 / S3'];

    public const PEKERJAAN = ['ASN', 'TNI / POLRI', 'Swasta', 'Pengusaha', 'Pelajar', 'Lainnya'];

    public const JENIS_RESPONDEN = ['Penerima Manfaat', 'Masyarakat Umum', 'Aparat Desa', 'Pelaksana'];

    /** Teks pertanyaan per unsur, sama dengan yang tampil di halaman survei (/skm). */
    public const PERTANYAAN = [
        1 => 'Menurut Anda, apakah program PPM yang dilaksanakan telah sesuai dengan kebutuhan masyarakat di wilayah Anda?',
        2 => 'Menurut Anda, apakah kegiatan PPM telah dilaksanakan dengan baik, tertib, dan sesuai dengan rencana yang disampaikan?',
        3 => 'Menurut Anda, apakah manfaat program PPM dapat terus dirasakan oleh masyarakat dalam jangka panjang?',
        4 => 'Menurut Anda, apakah informasi mengenai tujuan, manfaat, dan pelaksanaan program PPM telah disampaikan dengan jelas dan mudah dipahami?',
        5 => 'Menurut Anda, apakah tim pelaksana program memiliki kemampuan dan pengetahuan yang memadai dalam menjalankan kegiatan PPM?',
        6 => 'Menurut Anda, apakah masyarakat mudah mengakses dan mengikuti program PPM yang diselenggarakan?',
        7 => 'Menurut Anda, apakah tim pelaksana program bersikap sopan, ramah, dan menghargai masyarakat selama kegiatan berlangsung?',
        8 => 'Menurut Anda, apakah program PPM memberikan dampak positif terhadap kondisi sosial dan ekonomi masyarakat?',
        9 => 'Menurut Anda, apakah program PPM memberikan manfaat nyata bagi masyarakat penerima program?',
    ];

    /** Unsur number => [name, answers for score 1..4]. */
    public const UNSUR = [
        1 => ['Kesesuaian Program', ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai']],
        2 => ['Kualitas Pelaksanaan', ['Tidak Baik', 'Kurang Baik', 'Baik', 'Sangat Baik']],
        3 => ['Keberlanjutan', ['Tidak Berkelanjutan', 'Kurang Berkelanjutan', 'Berkelanjutan', 'Sangat Berkelanjutan']],
        4 => ['Kejelasan Informasi', ['Tidak Jelas', 'Kurang Jelas', 'Jelas', 'Sangat Jelas']],
        5 => ['Kompetensi Tim', ['Tidak Kompeten', 'Kurang Kompeten', 'Kompeten', 'Sangat Kompeten']],
        6 => ['Kemudahan Akses', ['Tidak Mudah', 'Kurang Mudah', 'Mudah', 'Sangat Mudah']],
        7 => ['Sikap dan Etika', ['Tidak Sopan dan Ramah', 'Kurang Sopan dan Ramah', 'Sopan dan Ramah', 'Sangat Sopan dan Ramah']],
        8 => ['Dampak Sosial dan Ekonomi', ['Tidak Berdampak', 'Kurang Berdampak', 'Berdampak', 'Sangat Berdampak']],
        9 => ['Kebermanfaatan', ['Tidak Bermanfaat', 'Kurang Bermanfaat', 'Bermanfaat', 'Sangat Bermanfaat']],
    ];
}
