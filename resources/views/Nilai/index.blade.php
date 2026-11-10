<?php

error_reporting(0);

$startDate = \App\Support\SurveyFilter::year() . '-01-01';

$endDate = \App\Support\SurveyFilter::year() . '-12-31';


$terms = \App\Support\SurveyFilter::selectedPilar();

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
// echo $nrrTotal;
// echo $nrrTertimbang;
?>
@php
    // role/keterangan dari URL login, contoh: ?keterangan=asisten2
    $role = request('keterangan', 'admin');
    $bagianQuery = request('bagian', '');

    // Semua opsi yang tersedia untuk ditampilkan
    $allOptions = \App\Support\BagianOptions::codeNameMap();
    $visibleKeys = \App\Support\BagianOptions::codesForRole($role);
@endphp

@extends('layouts.index', ['title' => 'Dashboard'])

@section('content')
    <div class="admin-main">
        <div class="bg-white h-full w-full rounded-xl p-3 ">
            <div class="admin-toolbar flex w-full justify-between items-start lg:items-center gap-3 flex-wrap lg:flex-nowrap">
                <p class="text-[25px] font-black text-gray-800">RESUME / NILAI SKM</p>
                <div class="flex justify-between items-center py-4 ">
                    <div>
                        <label class="text-gray-900 font-bold text-xl"> </label>
                    </div>
                    <div class="flex flex-row items-center gap-3">

                        @include('partials.admin-filter', ['route' => '/nilaiRekap'])

                        {{-- download --}}
                        <a href="/exports?{{ \App\Support\SurveyFilter::queryString(['keterangan' => $_GET['keterangan'] ?? session('keterangan', 'admin')]) }}"
                            class="py-1 text-center items-center  bg-gradient-to-br from-[#EA580C] from-60%  to-[#FDBA74] to-95% rounded-full font-bold text-sm px-3  w-full  text-gray-900"
                            style="font-family:'Roboto'">
                            <p>DOWNLOAD LAPORAN SKM</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="overflow-y-auto  h-[510px]">

                <div class="rounded-xl w-full p-3 mb-3 bg-gradient-to-br from-[#EA580C] to-[#EA580C]">
                    <div class="flex mb-3 justify-between text-gray-900 text-base font-bold">
                        <div class="">Tabel Konversi</div>
                        <button onclick="konversi()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 5.25l-7.5 7.5-7.5-7.5m15 6l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                    <div id="konversi" style="display : none;">
                        <table class=" w-full border">
                            <thead
                                class="border-b bg-gradient-to-br from-[#EA580C] to-[#EA580C] font-medium text-gray-900 dark:border-neutral-500 dark:bg-neutral-900">
                                <tr>

                                    <th scope="col" class=" px-4 py-2">Nilai Persepsi</th>
                                    <th scope="col" class=" px-4 py-2">Nilai Interval</th>
                                    <th scope="col" class=" px-4 py-2">Nilai Interval Konversi</th>
                                    <th scope="col" class=" px-4 py-2">Mutu Pelayanan</th>
                                    <th scope="col" class=" px-4 py-2">Kinerja Unit Pelayanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                        1
                                    </td>
                                    <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                        0,000 - 0,999
                                    </td>
                                    <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                        0,00 - 24,99
                                    </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        E
                                    </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        Sangat Tidak Memuaskan
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                        2
                                    </td>
                                    <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                        1,000 - 2,599
                                    </td>
                                    <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                        25,00 - 64,99 </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        D
                                    </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        Tidak Memuaskan
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                        3
                                    </td>
                                    <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                        2,600 - 3,064
                                    </td>
                                    <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                        65,00 - 76,60 </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        C
                                    </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        Kurang Memuaskan
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                        4
                                    </td>
                                    <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                        3,064 - 3,532
                                    </td>
                                    <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                        76,61 - 88,30 </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        B
                                    </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        Memuaskan
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                        5
                                    </td>
                                    <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                        3,532 - 4,000
                                    </td>
                                    <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                        88,31 - 100,00
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        A
                                    </td>
                                    <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                        Sangat Memuaskan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="rounded-xl w-full p-3 mb-3 bg-gradient-to-br from-[#EA580C] to-[#EA580C]">
                    <div class="flex mb-3 justify-between text-gray-900 text-base font-bold">
                        <div class="">Rumus Perhitungan</div>
                        <button onclick="rumus()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 5.25l-7.5 7.5-7.5-7.5m15 6l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                    <div id="rumus" style="display : none;">
                        <table class=" w-full border">
                            <thead
                                class="border-b bg-gradient-to-br from-[#EA580C] to-[#EA580C] font-medium text-gray-900 dark:border-neutral-500 dark:bg-neutral-900">
                                <tr>

                                    <th scope="col" class=" px-4 py-2">Nilai</th>
                                    <th scope="col" class=" px-4 py-2">Rumus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap  w-[100px] text-left px-4 py-1 font-normal text-xs">
                                        Nilai Rata - Rata Per-unsur
                                    </td>
                                    <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                        : Jumlah nilai per-unsur / Jumlah responden
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-left px-4 py-1 font-normal text-xs">
                                        Nilai Tertimbang
                                    </td>
                                    <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                        : Nilai rata - rata per-unsur x Nilai penimbang </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-left px-4 py-1 font-normal text-xs">
                                        Nilai Penimbang
                                    </td>
                                    <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                        : 1/9 x 0,111
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-left px-4 py-1 font-normal text-xs">
                                        Nilai Indeks
                                    </td>
                                    <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                        : Jumlah nilai rata - rata tertimbang per-unsur
                                    </td>
                                </tr>
                                <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                    <td class="whitespace-nowrap text-left px-4 py-1 font-normal text-xs">
                                        Nilai SKM
                                    </td>
                                    <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                        : Nilai Indeks x 25
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="rounded-xl w-full p-3  bg-gradient-to-br from-[#EA580C] to-[#EA580C]">
                    <div class="flex mb-3 justify-between text-gray-900 text-base font-bold">
                        <div class="">Tabel Nilai Survey</div>

                    </div>
                    <table class=" w-full border">
                        <thead
                            class="border-b bg-gradient-to-br from-[#EA580C] to-[#EA580C] font-medium text-gray-900 dark:border-neutral-500 dark:bg-neutral-900">
                            <tr class="">
                                <th scope="col" class=" px-4 py-2">No</th>
                                <th scope="col" class=" px-4 py-2">Unsur Pelayanan</th>
                                <th scope="col" class=" px-4 py-2 whitespace-nowrap">Nilai Rata Rata</th>
                                <th scope="col" class=" px-4 py-2 whitespace-nowrap">Nilai Rata Rata Tertimbang</th>
                                <th scope="col" class=" px-4 py-2 whitespace-nowrap">Mutu Pelayanan</th>
                            </tr>
                        </thead>
                        <tbody class="border">
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    1
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Kesesuaian Program
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr1 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt1 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr1 >= 0.0 and $nrr1 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr1 >= 1.0 and $nrr1 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr1 >= 2.6 and $nrr1 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr1 >= 3.064 and $nrr1 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    2
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Kualitas Pelaksanaan
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr2 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt2 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr2 >= 0.0 and $nrr2 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr2 >= 1.0 and $nrr2 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr2 >= 2.6 and $nrr2 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr2 >= 3.064 and $nrr2 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    3
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Keberlanjutan
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr3 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt3 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr3 >= 0.0 and $nrr3 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr3 >= 1.0 and $nrr3 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr3 >= 2.6 and $nrr3 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr3 >= 3.064 and $nrr3 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    4
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Kejelasan Informasi
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr4 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt4 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr4 >= 0.0 and $nrr4 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr4 >= 1.0 and $nrr4 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr4 >= 2.6 and $nrr4 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr4 >= 3.064 and $nrr4 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    5
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Kompetensi Tim
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr5 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt5 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr5 >= 0.0 and $nrr5 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr5 >= 1.0 and $nrr5 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr5 >= 2.6 and $nrr5 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr5 >= 3.064 and $nrr5 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    6
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Kemudahan Akses
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr6 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt6 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr6 >= 0.0 and $nrr6 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr6 >= 1.0 and $nrr6 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr6 >= 2.6 and $nrr6 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr6 >= 3.064 and $nrr6 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    7
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Sikap dan Etika
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr7 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt7 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr7 >= 0.0 and $nrr7 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr7 >= 1.0 and $nrr7 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr7 >= 2.6 and $nrr7 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr7 >= 3.064 and $nrr7 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    8
                                </td>
                                <td class="whitespace-wrap  px-4 font-normal text-xs">
                                    Dampak Sosial dan Ekonomi
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr8 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt8 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr8 >= 0.0 and $nrr8 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr8 >= 1.0 and $nrr8 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr8 >= 2.6 and $nrr8 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr8 >= 3.064 and $nrr8 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td class="whitespace-nowrap text-center px-4 py-1 font-normal text-xs">
                                    9
                                </td>
                                <td class="whitespace-nowrap  px-4 font-normal text-xs">
                                    Kebermanfaatan
                                </td>
                                <td class="whitespace-nowrap text-center px-4 font-normal text-xs">
                                    {{ $nrr9 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrt9 }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrr9 >= 0.0 and $nrr9 <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrr9 >= 1.0 and $nrr9 <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrr9 >= 2.6 and $nrr9 <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrr9 >= 3.064 and $nrr9 <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td colspan="2" class="whitespace-nowrap text-left px-4 py-1 font-normal text-xs">
                                    Nilai Indeks (NI)
                                </td>
                                <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                    {{ '' }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nrrT }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nrrT >= 0.0 and $nrrT <= 0.99) {
                                        echo 'E';
                                    } elseif ($nrrT >= 1.0 and $nrrT <= 2.599) {
                                        echo 'D';
                                    } elseif ($nrrT >= 2.6 and $nrrT <= 3.064) {
                                        echo 'C';
                                    } elseif ($nrrT >= 3.064 and $nrrT <= 3.532) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr class="border-b dark:border-neutral-500 bg-white  text-xs">
                                <td colspan="2" class="whitespace-nowrap text-left px-4 py-1 font-normal text-xs">
                                    Nilai SKM setelah dikonversi (NI x 25)
                                </td>
                                <td class="whitespace-nowrap text-left px-4 font-normal text-xs">
                                    {{ '' }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    {{ $nilaiSKM }}
                                </td>
                                <td class="whitespace-nowrap  text-center px-4 font-normal text-xs">
                                    <?php
                                    if ($nilaiSKM >= 0.0 and $nilaiSKM <= 24.99) {
                                        echo 'E';
                                    } elseif ($nilaiSKM >= 25.0 and $nilaiSKM <= 64.99) {
                                        echo 'D';
                                    } elseif ($nilaiSKM >= 65.0 and $nilaiSKM <= 76.6) {
                                        echo 'C';
                                    } elseif ($nilaiSKM >= 76.61 and $nilaiSKM <= 88.3) {
                                        echo 'B';
                                    } else {
                                        echo 'A';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.ckeditor.com/4.12.0/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('content');
    </script>

    <script src="https://cdn.ckeditor.com/4.12.0/standard/ckeditor.js"></script>
    <script>
        function rumus() {
            var x = document.getElementById("rumus");
            if (x.style.display === "none") {
                x.style.display = "block";
                x.style.width = "initial";
            } else {
                x.style.display = "none";
            }
        }

        function konversi() {
            var x = document.getElementById("konversi");
            if (x.style.display === "none") {
                x.style.display = "block";
                x.style.width = "initial";
            } else {
                x.style.display = "none";
            }
        }
        CKEDITOR.replace('content');
    </script>
@endsection






