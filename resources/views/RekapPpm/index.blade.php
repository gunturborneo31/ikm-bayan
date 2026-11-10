@extends('layouts.index', ['title' => 'Rekap SKM PPM'])

@php
    $fmt = fn ($v) => $v === null ? '-' : number_format($v, 2, ',', '.');
    $sumberLabel = \App\Support\PpmCatalog::SUMBER_LABEL;
    $totalSumber = array_sum($sumber);
@endphp

@section('content')
<div class="admin-main">
    <div class="w-full rounded-xl p-4 md:p-5 mb-2 border border-orange-200 bg-white space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold text-orange-800">Rekap SKM PPM Bayan</h2>
                <p class="text-sm text-gray-600">Satu penilaian = satu responden menilai satu rincian program. SKM = rata-rata 9 unsur × 25.</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <label for="tahun" class="text-sm font-bold text-gray-700">Tahun</label>
                <select id="tahun" name="Tahun" onchange="this.form.submit()" class="rounded-lg border-orange-200 text-sm">
                    <option value="semua" @selected($tahun === 'semua')>Semua tahun</option>
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($tahun === $y)>{{ $y }}</option>
                    @endforeach
                    @if ($tahun !== 'semua' && ! in_array($tahun, $years, true))
                        <option value="{{ $tahun }}" selected>{{ $tahun }}</option>
                    @endif
                </select>
            </form>
        </div>

        {{-- SKM grup + per anak perusahaan --}}
        <div class="grid gap-3 md:grid-cols-5">
            <div class="rounded-xl bg-orange-600 text-white p-4 md:col-span-1">
                <p class="text-[11px] font-black tracking-widest">SKM BAYAN (GRUP)</p>
                <p class="text-4xl font-black mt-1">{{ $fmt($grup['skm']) }}</p>
                <p class="text-xs font-bold mt-1">{{ $grup['mutu'] }}</p>
                <p class="text-xs mt-2 opacity-90">{{ number_format($grup['n']) }} penilaian · {{ number_format($jumlahResponden) }} responden</p>
            </div>
            @foreach ($perPerusahaan as $p)
                <div class="rounded-xl border border-orange-200 bg-orange-50/60 p-4">
                    <p class="text-[11px] font-black tracking-widest text-orange-700">{{ $p['kode'] }}</p>
                    <p class="text-xs font-bold text-gray-700">{{ $p['nama'] }}</p>
                    <p class="text-3xl font-black text-gray-900 mt-1">{{ $fmt($p['skm']) }}</p>
                    <p class="text-xs font-bold text-gray-600">{{ $p['mutu'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($p['n']) }} penilaian</p>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-600 -mt-2">
            {{ number_format($gabungan['n']) }} penilaian dinilai sebagai <b>BAYAN (gabungan)</b> (SKM {{ $fmt($gabungan['skm']) }}).
            Penilaian ini masuk SKM grup, tetapi tidak dibebankan ke anak perusahaan.
        </p>

        {{-- Indikator tidak tahu + sumber atribusi --}}
        <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-xl border border-orange-200 p-4">
                <h3 class="font-extrabold text-gray-900">Jawaban "Tidak tahu" per perusahaan</h3>
                <p class="text-xs text-gray-600 mb-2">Dari penilaian di mana perusahaan tersebut menjadi pilihan, persentase responden yang menjawab "Tidak tahu". Makin tinggi = program makin kurang dikenal.</p>
                <table class="w-full text-sm">
                    <thead><tr><th class="text-left">Perusahaan</th><th class="text-right">Pilihan muncul</th><th class="text-right">Tidak tahu</th><th class="text-right">%</th></tr></thead>
                    <tbody>
                        @foreach ($tidakTahu as $t)
                            <tr>
                                <td>{{ $t['kode'] }} · {{ $t['nama'] }}</td>
                                <td class="text-right">{{ number_format($t['total']) }}</td>
                                <td class="text-right">{{ number_format($t['tidak_tahu']) }}</td>
                                <td class="text-right font-bold">{{ $t['persen'] === null ? '-' : number_format($t['persen'], 1, ',', '.') . '%' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="rounded-xl border border-orange-200 p-4 space-y-3">
                <div>
                    <h3 class="font-extrabold text-gray-900">Sumber atribusi perusahaan</h3>
                    <table class="w-full text-sm mt-1">
                        <tbody>
                            @foreach ($sumberLabel as $k => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-right">{{ number_format($sumber[$k] ?? 0) }}</td>
                                    <td class="text-right font-bold">{{ $totalSumber ? number_format(($sumber[$k] ?? 0) / $totalSumber * 100, 1, ',', '.') . '%' : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="font-extrabold text-gray-900">Jenis responden</h3>
                    <table class="w-full text-sm mt-1">
                        <tbody>
                            @forelse ($jenisResponden as $k => $n)
                                <tr><td>{{ $k ?: 'Tidak diisi' }}</td><td class="text-right">{{ number_format($n) }} penilaian</td></tr>
                            @empty
                                <tr><td class="text-gray-500">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Per desa / pilar / program / rincian --}}
        @foreach ([['Per Desa', $perDesa], ['Per Pilar', $perPilar], ['Per Program', $perProgram], ['Per Rincian Program', $perRincian]] as [$judul, $rows])
            <details class="rounded-xl border border-orange-200 p-4" {{ $loop->first ? 'open' : '' }}>
                <summary class="font-extrabold text-gray-900 cursor-pointer">{{ $judul }}</summary>
                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-sm">
                        <thead><tr><th class="text-left">Kode</th><th class="text-left">Nama</th><th class="text-right">Penilaian</th><th class="text-right">SKM</th><th class="text-left">Mutu</th></tr></thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr class="{{ $r['n'] ? '' : 'text-gray-400' }}">
                                    <td class="whitespace-nowrap">{{ is_int($r['key']) ? '' : $r['key'] }}</td>
                                    <td>{{ $r['nama'] }}</td>
                                    <td class="text-right">{{ number_format($r['n']) }}</td>
                                    <td class="text-right font-bold">{{ $fmt($r['skm']) }}</td>
                                    <td>{{ $r['mutu'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endforeach
    </div>
</div>
@endsection
