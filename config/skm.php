<?php

return [
    /*
     * Fitur Import Excel responden (menu & halaman /admin/offline-skm).
     * Disembunyikan untuk semua akun (admin maupun surveyor). Aktifkan kembali
     * dengan SKM_IMPORT_EXCEL=true di file .env lalu jalankan: php artisan config:clear
     */
    'import_excel' => (bool) env('SKM_IMPORT_EXCEL', false),

    'departments' => [
        'organisasi' => 'Bagian Organisasi',
        'umum' => 'Bagian Umum',
        'pemerintahan' => 'Bagian Pemerintahan',
        'adbang' => 'Bagian Administrasi Pembangunan',
        'prokopim' => 'Bagian Protokol dan Komunikasi Pimpinan',
        'kesra' => 'Bagian Kesejahteraan Rakyat',
        'pbj' => 'Bagian Pengadaan Barang dan Jasa',
        'ekosda' => 'Bagian Perekonomian dan Sumber Daya Alam',
        'hukum' => 'Bagian Hukum',
    ],

    'aliases' => [
        'barjas' => 'pbj',
        'ortal' => 'organisasi',
    ],

    'periods' => [
        1 => 'TW 1',
        2 => 'TW 2',
        3 => 'Semester 1',
        4 => 'TW 3',
        5 => 'Sampai TW 3',
        6 => 'TW 4',
        7 => 'Satu Tahun',
    ],
];