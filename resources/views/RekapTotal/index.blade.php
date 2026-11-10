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
    $nrr1 = sprintf('%0.2f', $tu1 / $n);
    $nrr2 = sprintf('%0.2f', $tu2 / $n);
    $nrr3 = sprintf('%0.2f', $tu3 / $n);
    $nrr4 = sprintf('%0.2f', $tu4 / $n);
    $nrr5 = sprintf('%0.2f', $tu5 / $n);
    $nrr6 = sprintf('%0.2f', $tu6 / $n);
    $nrr7 = sprintf('%0.2f', $tu7 / $n);
    $nrr8 = sprintf('%0.2f', $tu8 / $n);
    $nrr9 = sprintf('%0.2f', $tu9 / $n);

    $nrr = $nrr1 + $nrr2 + $nrr3 + $nrr4 + $nrr5 + $nrr6 + $nrr7 + $nrr8 + $nrr9;
    $nrr = sprintf('%0.2f', $nrr);

    $nrrt1 = sprintf('%0.2f', $nrr1 * (1/9));
    $nrrt2 = sprintf('%0.2f', $nrr2 * (1/9));
    $nrrt3 = sprintf('%0.2f', $nrr3 * (1/9));
    $nrrt4 = sprintf('%0.2f', $nrr4 * (1/9));
    $nrrt5 = sprintf('%0.2f', $nrr5 * (1/9));
    $nrrt6 = sprintf('%0.2f', $nrr6 * (1/9));
    $nrrt7 = sprintf('%0.2f', $nrr7 * (1/9));
    $nrrt8 = sprintf('%0.2f', $nrr8 * (1/9));
    $nrrt9 = sprintf('%0.2f', $nrr9 * (1/9));

    $nrrT = $nrrt1 + $nrrt2 + $nrrt3 + $nrrt4 + $nrrt5 + $nrrt6 + $nrrt7 + $nrrt8 + $nrrt9;
    $nrrT = sprintf('%0.2f', $nrrT);

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



@extends('layouts.index', ['title' => 'Dashboard'])

@section('content')
    <div class="admin-main">
        <div class="bg-white h-full w-full rounded-xl p-3  mb-2">
            <div class="admin-toolbar flex w-full justify-between items-start lg:items-center gap-3 flex-wrap lg:flex-nowrap">
                <p class="text-[25px] font-black text-gray-800">RESUME </p>
                <div class="flex justify-between items-center py-4 ">
                    <div>
                        <label class="text-gray-900 font-bold text-xl"> </label>
                    </div>
                    <?php

                    error_reporting(0);

                    ?>
                    <div class="flex flex-row items-center gap-3">

                        {{-- filter --}}
<div class="flex flex-row items-center gap-2">
    <label class="font-bold text-gray-800 text-sm">Filter</label>
  
    <button id="dropdownDefaultButton" data-dropdown-toggle="dropdown"
      class="flex items-center bg-white border border-gray-300 text-[#005C3A] font-bold text-sm rounded-full px-5 py-1" type="button">
      Pilih
      <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
      </svg>
    </button>
  
    <div id="dropdown" class="z-10 hidden border bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
      <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownDefaultButton">
        @php
          $Tahun = $_GET['Tahun'] ?? '';
          $bagian = $_GET['bagian'] ?? '';
          $jenkel = $_GET['jenkel'] ?? '0';
          $usia = $_GET['usia'] ?? '0';
          $pekerjaan = $_GET['pekerjaan'] ?? '0';
          $pendidikan = $_GET['pendidikan'] ?? '0';
        @endphp
  
        <li>
          <a href="/rekapTotal?Tahun={{ $Tahun }}&bagian={{ urlencode($bagian) }}&jenkel={{ $jenkel=='0'?'1':'0' }}&usia={{ $usia }}&pekerjaan={{ $pekerjaan }}&pendidikan={{ $pendidikan }}"
             class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
            <p>Jenis kelamin</p>
            <svg aria-hidden="true" fill="none" stroke-width="4" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3 {{ $jenkel=='1' ? '' : 'hidden' }}">
              <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </li>
  
        <li>
          <a href="/rekapTotal?Tahun={{ $Tahun }}&bagian={{ urlencode($bagian) }}&jenkel={{ $jenkel }}&usia={{ $usia=='0'?'1':'0' }}&pekerjaan={{ $pekerjaan }}&pendidikan={{ $pendidikan }}"
             class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
            <p>Usia</p>
            <svg aria-hidden="true" fill="none" stroke-width="4" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3 {{ $usia=='1' ? '' : 'hidden' }}">
              <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </li>
  
        <li>
          <a href="/rekapTotal?Tahun={{ $Tahun }}&bagian={{ urlencode($bagian) }}&jenkel={{ $jenkel }}&usia={{ $usia }}&pekerjaan={{ $pekerjaan=='0'?'1':'0' }}&pendidikan={{ $pendidikan }}"
             class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
            <p>Pekerjaan</p>
            <svg aria-hidden="true" fill="none" stroke-width="4" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3 {{ $pekerjaan=='1' ? '' : 'hidden' }}">
              <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </li>
  
        <li>
          <a href="/rekapTotal?Tahun={{ $Tahun }}&bagian={{ urlencode($bagian) }}&jenkel={{ $jenkel }}&usia={{ $usia }}&pekerjaan={{ $pekerjaan }}&pendidikan={{ $pendidikan=='0'?'1':'0' }}"
             class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
            <p>Pendidikan</p>
            <svg aria-hidden="true" fill="none" stroke-width="4" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3 {{ $pendidikan=='1' ? '' : 'hidden' }}">
              <path d="m4.5 12.75 6 6 9-13.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </li>
      </ul>
    </div>
  </div>

  @include('partials.admin-filter', ['route' => '/rekapTotal'])

                        {{-- download (mengikuti filter Tahun + Pilar) --}}
                        <a href="{{ route('exports.download', ['type' => 'resume', 'jenkel' => $_GET['jenkel'], 'usia' => $_GET['usia'], 'pekerjaan' => $_GET['pekerjaan'], 'pendidikan' => $_GET['pendidikan'], 'Tahun' => \App\Support\SurveyFilter::year()] + (\App\Support\SurveyFilter::isAllPilar() ? [] : ['bagian' => \App\Support\SurveyFilter::pilarCsv()])) }}"
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
                        <div class="border border-[#EA580C] rounded-lg overflow-hidden h-[460px] overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 h-full ">
                                <thead class="bg-gradient-to-br from-[#EA580C] from-60%  to-[#FDBA74] to-95%">
                                    <tr>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            No.<br>RESPONDEN</th>
                                        {{-- <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            NIK</th> --}}
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            NAMA</th>
                                        {{-- <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            JENIS<br>Pelayanan</th> --}}
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase {{ $_GET['jenkel'] == 0 ? 'hidden' : '' }}">
                                            JENIS<br>KELAMIN</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase {{ $_GET['usia'] == 0 ? 'hidden' : '' }}">
                                            USIA</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase {{ $_GET['pekerjaan'] == 0 ? 'hidden' : '' }}">
                                            PEKERJAAN</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase {{ $_GET['pendidikan'] == 0 ? 'hidden' : '' }}">
                                            PENDIDIKAN</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            DESA</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            PILAR</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 min-w-[200px] text-center text-xs font-medium text-gray-900 uppercase">
                                            RINCIAN PROGRAM</th>
                                        <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            PERUSAHAAN<br>DINILAI</th>
                                        {{-- <th scope="col" rowspan="2"
                                            class="px-6 py-3 w-[50px] text-center text-xs font-medium text-gray-900 uppercase {{ $_GET['pendidikan'] == 0 ? 'hidden' : '' }}">
                                            JENIS PELAYANAN</th> --}}
                                        <th scope="col" colspan="9"
                                            class=" px-6 px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase ">
                                            NILAI UNSUR PELAYANAN</th>
                                        <th scope="col" rowspan="2"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            Tanggal</th>
                                        <th scope="col" rowspan="2"
                                            class=" px-6 py-1 w-[50px] text-center text-xs font-medium text-gray-900 uppercase">
                                            AKSI</th>
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
                                    </tr>
                                </thead>
                                <div class="h-200 overflow-y-auto">
                                    <tbody class="" style="overflow-y:auto;">
                                        <?php $row = 1; ?>
                                        @forelse($selects as $data)
                                            <tr class="{{ $row % 2 == 0 ? 'bg-[#E3E3E3]' : '' }}">

                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $selects->firstItem() + $loop->index }}
                                                    @php $row++; @endphp
                                                </td>
                                                {{-- <td class="text-center px-2 font-normal text-xs">
                                                    {{-- NIK intentionally left blank --}}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    <a href="{{ route('rekapTotal.show', $data->id) }}" class="js-admin-nav font-bold text-orange-800 hover:underline" title="Lihat detail isian responden">{{ $data->nama ?: '-' }}</a>
                                                </td>
                                                {{-- <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->jenisPelayanan }}
                                                </td> --}}
                                                <td
                                                    class="text-center px-2 font-normal text-xs {{ $_GET['jenkel'] == 0 ? 'hidden' : '' }}">
                                                    {{ $data->jenkel }}
                                                </td>
                                                <td
                                                    class="text-center px-2 font-normal text-xs {{ $_GET['usia'] == 0 ? 'hidden' : '' }}">
                                                    {{ $data->usia }}
                                                </td>
                                                <td
                                                    class="text-center px-2 font-normal text-xs {{ $_GET['pekerjaan'] == 0 ? 'hidden' : '' }}">
                                                    {{ $data->pekerjaan }}
                                                </td>
                                                <td
                                                    class="text-center px-2 font-normal text-xs {{ $_GET['pendidikan'] == 0 ? 'hidden' : '' }}">
                                                    {{ $data->pendidikan }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $desaNames[$data->desa_id] ?? '-' }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $pilarNames[$data->jenisPelayanan] ?? ($data->jenisPelayanan ? strtoupper($data->jenisPelayanan) : '-') }}
                                                </td>
                                                <td class="text-left px-2 font-normal text-xs">
                                                    {{ $rincianNames[$data->rincian_id] ?? '-' }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->perusahaan_dinilai ?? '-' }}
                                                    @if ($data->perusahaan_jawaban === \App\Support\PpmCatalog::TIDAK_TAHU)
                                                        <span class="block text-[10px] text-gray-500">jawab: tidak tahu</span>
                                                    @endif
                                                </td>
                                                {{-- <td
                                                    class="text-center px-2 font-normal text-xs {{ $_GET['usia'] == 0 ? 'hidden' : '' }}">
                                                    {{ $data->sub_jenis_jenis }}
                                                </td> --}}
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u1 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u2 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u3 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u4 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u5 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u6 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u7 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u8 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->u9 }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs">
                                                    {{ $data->created_at }}
                                                </td>
                                                <td class="text-center px-2 font-normal text-xs whitespace-nowrap">
                                                    <a href="{{ route('rekapTotal.show', $data->id) }}" class="js-admin-nav inline-block align-middle mr-1" title="Lihat detail isian responden">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 hover:text-orange-700">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                        </svg>
                                                    </a>
                                                    <form onsubmit="return confirm('Apakah Anda Yakin ?');"
                                                        action="{{ route('rekapTotal.destroy', $data->id) }}"
                                                        method="POST" class="inline-block align-middle no-loading">

                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" class="w-6 h-6 hover:text-red-700">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                        <tr class="h-fit bg-white">

                                            <td class="text-center px-2 font-normal text-xs">
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gradient-to-br from-[#EA580C] h-[50px] from-60%  to-[#FDBA74] to-95% text-gray-900">
                                        @php
                                            // Kolom sebelum U1: No, Nama, Desa, Pilar, Rincian, Perusahaan + kolom demografi yang tampil
                                            $kolomDepan = 6 + collect(['jenkel', 'usia', 'pekerjaan', 'pendidikan'])->filter(fn ($k) => ($_GET[$k] ?? '1') != 0)->count();
                                        @endphp
                                        <tr>
                                            <td colspan="{{ $kolomDepan }}" class="text-center px-2 font-bold text-xs">
                                                TOTAL ({{ number_format($totals->n ?? 0) }} penilaian)
                                            </td>
                                            @foreach (range(1, 9) as $i)
                                                <td class="text-center px-2 font-bold text-xs">{{ $totals->{"u{$i}"} ?? 0 }}</td>
                                            @endforeach
                                            <td class="text-center px-2 font-normal text-xs"></td>
                                            <td class="text-center px-2 font-normal text-xs"></td>
                                        </tr>
                                    </tfoot>
                                </div>
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






