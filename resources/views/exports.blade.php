@extends('layouts.index', ['title' => 'Dashboard'])

@section('content')


@php
    $role = request('keterangan', session('keterangan', 'admin'));
    $tiles = collect(\App\Support\BagianOptions::visibleOptionsForRole($role))
            ->mapWithKeys(fn ($label, $code) => [$code => [strtoupper($label), $code]])
            ->all();

  // Filter tahunan (Tahun + Pilar) dibawa ke link download & kembali
  $q = \App\Support\SurveyFilter::queryString();
@endphp


<div class="admin-main">
    <div class="bg-white h-full h-full w-full rounded-xl p-3  mb-2">
        <div class="  text-center    lg:text-left  w-full justify-between">
            <h1
                class=" max-w-md  font-bold tracking-tight mb-2 whitespace-nowrap mb-3 text-gray-900 lg:text-2xl  text-4xl  drop-shadow-[0_1px_1px_rgba(0,0,0,0.3)]">
                Silahkan memlilih yang akan di export
            </h1>
           
            {{-- Tile SETKAB (tetap tampil) --}}
            @if (in_array($role, ['admin', 'superadmin', 'ortal'], true))
                <div class="lg:flex w-full justify-between gap-4 mb-[30px]">
            <ul class="grid w-full gap-2 grid-cols-1">
                <a href="/export?{{ $q }}">
                <label
                    class="inline-flex items-center justify-center w-full p-3 text-gray-900 bg-gray-800 h-full border-2 border-white rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-600">
                    <div class="block">
                    <div class="w-full text-center text-white font-normal text-4xl lg:text-2xl">
                        DOWNLOAD LAPORAN
                    </div>
                    </div>
                </label>
                </a>
            </ul>
            </div>
            @endif
            

            {{-- Grid dinamis sesuai role --}}
            {{-- <div class="lg:flex w-full justify-between gap-4">
            <ul class="grid w-full gap-2 md:grid-cols-5 grid-cols-1">
                @foreach($tiles as $key => [$label, $param])
                <a href="/resumeKhusus?{{ $q }}&bagian={{ $param }}">
                    <label
                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-gray-800 h-full border-2 border-white rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-600">
                    <div class="block w-full">
                        <div class="w-full text-center text-white font-normal text-4xl lg:text-2xl">
                        {{ $label }}
                        </div>
                    </div>
                    </label>
                </a>
                @endforeach
            </ul>
            </div> --}}


            <li class="flex justify-between mt-5">
                <a type="button" onclick="window.location=''" class="flex items-center gap-2  ">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="w-8 h-8 text-transparent">
                        <path fill-rule="evenodd"
                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z"
                            clip-rule="evenodd" />
                    </svg>
                    <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer"></label>
        
                </a>
                <a href="/nilaiRekap?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&{{ $q }}"
                    class="flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-8 h-8 text-gray-900">
                                        <path fill-rule="evenodd"
                                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <label
                                        class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer ">KEMBALI</label>
                </a>
            </a>
        </div>
    </div>
</div>
@endsection





