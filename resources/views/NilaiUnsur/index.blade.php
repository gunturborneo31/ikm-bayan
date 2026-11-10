<?php

error_reporting(0);

$startDate = \App\Support\SurveyFilter::year() . '-01-01';

$endDate = \App\Support\SurveyFilter::year() . '-12-31';


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

$Ttl_Nilai_Unsur = $tu1 + $tu2 + $tu3 + $tu4 + $tu5 + $tu6 + $tu7 + $tu8 + $tu9;

$n = \App\Support\SurveyFilter::query()
    ->get()
    ->count();

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
}

?>

@php
    // role/keterangan dari URL login, contoh: ?keterangan=asisten2
    $role = request('keterangan', 'admin');
    $bagianQuery = request('bagian', '');

    // Semua opsi yang tersedia untuk ditampilkan
    $allOptions = \App\Support\BagianOptions::codeNameMap();
    $visibleKeys = \App\Support\BagianOptions::codesForRole($role);
@endphp

@extends('layouts.index', ['title' => 'nilaiUnsur'])

@section('content')
    <div class="admin-main">
        <div class="bg-white h-full w-full rounded-xl p-3  mb-2">
            <div class="admin-toolbar flex w-full justify-between items-start lg:items-center gap-3 flex-wrap lg:flex-nowrap">
                <p class="text-[25px] font-black text-gray-800">RESUME / PENDIDIKAN</p>
                <div class="flex justify-between items-center py-4 ">
                    <div>
                        <label class="text-gray-900 font-bold text-xl"> </label>
                    </div>
                    <div class="flex flex-row items-center gap-3">

                        @include('partials.admin-filter', ['route' => '/nilaiUnsur'])

                        {{-- download (mengikuti filter Tahun + Pilar) --}}
                        <a href="{{ route('exports.download', ['type' => 'resume', 'Tahun' => \App\Support\SurveyFilter::year()] + (\App\Support\SurveyFilter::isAllPilar() ? [] : ['bagian' => \App\Support\SurveyFilter::pilarCsv()])) }}"
                            class="py-1 text-center items-center  bg-gradient-to-br from-[#EA580C] from-60%  to-[#FDBA74] to-95% rounded-full font-bold text-sm px-3  w-full  text-gray-900"
                            style="font-family:'Roboto'">
                            <p>DOWNLOAD</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="w-full ">
                <div class="-m-1.5  ">
                    <div class="p-1.5 ">
                        <div class="border border-[#EA580C] rounded-lg overflow-hidden h-[335px]">
                            <table class="w-full divide-y divide-gray-200 ">
                                <thead class="bg-gradient-to-br from-[#EA580C] from-60%  to-[#FDBA74] to-95%">
                                    <tr>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            No.<br>RESPONDEN</th>
                                        <th scope="col" colspan="10"
                                            class=" px-6 px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase ">
                                            NILAI UNSUR PELAYANAN</th>
                                    </tr>
                                    <tr>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U1</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U2</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U3</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U4</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U5</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U6</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U7</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U8</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            U9</th>
                                        <th scope="col"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            HASIL</th>
                                    </tr>
                                </thead>
                                <div class="h-200 overflow-y-auto">
                                    <tbody class="" style="overflow-y:auto;">
                                        <?php $row = 1; ?>
                                        @forelse($selects as $data)
                                            <tr class="{{ $row % 2 == 0 ? 'bg-[#E3E3E3]' : '' }}">

                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $row++ }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u1 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u2 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u3 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u4 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u5 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u6 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u7 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u8 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                                    {{ $data->u9 }}
                                                </td>
                                                <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">

                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse

                                    </tbody>
                                </div>
                            </table>
                        </div>


                        <div class="rounded-lg overflow-hidden  mt-1">
                            <table class="w-full divide-y divide-gray-200 ">
                                <thead
                                    style="background-color: #EA580C;
    background-image: 
        radial-gradient(at -30% -30%, #EA580C, transparent 100%),
        radial-gradient(at 130% 150%, #EA580C, transparent 100%);">
                                    <tr class="border-b  text-gray-900 font-bold"
                                        style="background-color: #EA580C;
    background-image:
        radial-gradient(at -30% -30%, #EA580C, transparent 100%),
        radial-gradient(at 130% 150%, #EA580C, transparent 100%);">
                                        <td class="whitespace-nowrap  px-2 py-2 w-[150px] text-left font-medium">Total
                                            Nilai
                                            Unsur
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu1 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu2 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu3 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu4 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu5 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu6 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu7 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu8 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $tu9 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 w-[100px] text-center font-medium">
                                            {{ $Ttl_Nilai_Unsur }}
                                        </td>
                                    </tr>
                                    <tr class="border-b text-gray-900 font-bold"
                                        style="background-color: #EA580C;
    background-image:
        radial-gradient(at -30% -30%, #EA580C, transparent 100%),
        radial-gradient(at 130% 150%, #EA580C, transparent 100%);">
                                        <td class="whitespace-nowrap  px-2 py-2 w-[150px] text-left font-medium">
                                            NRR Per Unsur
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr1 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr2 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr3 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr4 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr5 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr6 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr7 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr8 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrr9 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 w-[100px] text-center font-medium">
                                            {{ $nrr }}
                                        </td>
                                    </tr>
                                    <tr class="border-b text-gray-900 font-bold"
                                        style="background-color: #EA580C;
    background-image:
        radial-gradient(at -30% -30%, #EA580C, transparent 100%),
        radial-gradient(at 130% 150%, #EA580C, transparent 100%);">
                                        <td class="whitespace-nowrap  px-2 py-2 w-[150px] text-left font-medium">
                                            NRR
                                            Tertimbang
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt1 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt2 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt3 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt4 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt5 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt6 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt7 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt8 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 text-center font-medium">
                                            {{ $nrrt9 }}
                                        </td>
                                        <td class="whitespace-nowrap  px-2 py-2 w-[100px] text-center font-medium">
                                            {{ $nrrT }}
                                        </td>
                                    </tr>
                                    </tbody>
                            </table>
                        </div>

                        <div class="mt-2">
                            {{ $selects->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.ckeditor.com/4.12.0/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('content');
    </script>
@endsection






