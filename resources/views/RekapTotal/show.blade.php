@extends('layouts.index', ['title' => 'Detail Responden'])

@php
    use App\Support\PpmCatalog;
    use App\Support\SurveyOptions;

    $backUrl = url()->previous() !== url()->current() && str_contains(url()->previous(), '/rekapTotal')
        ? url()->previous()
        : '/rekapTotal?jenkel=1&usia=1&pekerjaan=1&pendidikan=1&Tahun=' . ($data->tahun ?? date('Y'));

    $skor = 0;
    $terisi = 0;
    foreach (range(1, 9) as $i) {
        if ($data->{"u{$i}"} !== null) {
            $skor += (int) $data->{"u{$i}"};
            $terisi++;
        }
    }
    $skm = $terisi ? round($skor / $terisi * 25, 2) : null;

    $tampil = fn ($v) => filled($v) ? $v : '-';
    $perusahaanText = fn (?string $k) => ! $k ? '-' : ($k === PpmCatalog::TIDAK_TAHU ? 'Tidak tahu' : (\App\Support\SurveyFilter::perusahaanOptions()[$k] ?? $k));
    $badge = [1 => 'bg-red-100 text-red-800', 2 => 'bg-amber-100 text-amber-800', 3 => 'bg-green-100 text-green-800', 4 => 'bg-emerald-200 text-emerald-900'];
@endphp

@section('content')
<div class="admin-main">
    <div class="w-full rounded-xl p-4 md:p-5 mb-2 border border-orange-200 bg-white space-y-4">

        {{-- Judul --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ $backUrl }}" class="js-admin-nav text-sm font-bold text-orange-700 hover:underline">← Kembali ke Resume</a>
                <h2 class="text-xl font-extrabold text-gray-900 mt-1">{{ $tampil($data->nama) }}</h2>
                <p class="text-sm text-gray-600">
                    Diisi {{ $data->created_at ? \Carbon\Carbon::parse($data->created_at)->locale('id')->translatedFormat('d F Y, H:i') : '-' }}
                    @if ($data->client_uuid) · input admin/offline @endif
                </p>
            </div>
            <div class="rounded-xl bg-orange-600 text-white px-5 py-3 text-center">
                <p class="text-[11px] font-black tracking-widest">NILAI PENILAIAN INI</p>
                <p class="text-3xl font-black">{{ $skm === null ? '-' : number_format($skm, 2, ',', '.') }}</p>
                <p class="text-xs">Skor {{ $skor }} dari {{ $terisi * 4 }}</p>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            {{-- Identitas --}}
            <div class="rounded-xl border border-orange-200 p-4">
                <h3 class="font-extrabold text-gray-900 mb-2">Identitas Responden</h3>
                <dl class="grid grid-cols-[140px_1fr] gap-y-1.5 text-sm">
                    <dt class="text-gray-500">Jenis responden</dt><dd class="font-semibold">{{ $tampil($data->jenis_responden) }}</dd>
                    <dt class="text-gray-500">Desa</dt><dd class="font-semibold">{{ $tampil($detail['desa']) }}</dd>
                    <dt class="text-gray-500">No. HP</dt><dd class="font-semibold">{{ $tampil($data->nohp) }}</dd>
                    <dt class="text-gray-500">Alamat</dt><dd class="font-semibold">{{ $tampil($data->alamat) }}</dd>
                    <dt class="text-gray-500">Jenis kelamin</dt><dd class="font-semibold">{{ $tampil($data->jenkel) }}</dd>
                    <dt class="text-gray-500">Usia</dt><dd class="font-semibold">{{ SurveyOptions::USIA[(int) $data->usia] ?? $tampil($data->usia) }}</dd>
                    <dt class="text-gray-500">Pendidikan</dt><dd class="font-semibold">{{ $tampil($data->pendidikan) }}</dd>
                    <dt class="text-gray-500">Pekerjaan</dt><dd class="font-semibold">{{ $tampil($data->pekerjaan) }}</dd>
                </dl>
            </div>

            {{-- Program & perusahaan --}}
            <div class="rounded-xl border border-orange-200 p-4">
                <h3 class="font-extrabold text-gray-900 mb-2">Program yang Dinilai</h3>
                <dl class="grid grid-cols-[140px_1fr] gap-y-1.5 text-sm">
                    <dt class="text-gray-500">Pilar</dt><dd class="font-semibold">{{ $tampil($detail['pilar']) }}</dd>
                    <dt class="text-gray-500">Program</dt><dd class="font-semibold">{{ $tampil($detail['program']) }}</dd>
                    <dt class="text-gray-500">Rincian</dt><dd class="font-semibold">{{ $tampil($detail['rincian']) }}</dd>
                    <dt class="text-gray-500">Jawaban responden</dt>
                    <dd class="font-semibold">{{ $perusahaanText($data->perusahaan_jawaban) }}</dd>
                    <dt class="text-gray-500">Perusahaan dinilai</dt>
                    <dd class="font-semibold">
                        {{ $perusahaanText($data->perusahaan_dinilai) }}
                        @if ($data->sumber_atribusi)
                            <span class="ml-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-bold {{ $data->sumber_atribusi === PpmCatalog::SUMBER_JAWABAN ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ PpmCatalog::SUMBER_LABEL[$data->sumber_atribusi] ?? $data->sumber_atribusi }}
                            </span>
                        @endif
                    </dd>
                    <dt class="text-gray-500">Pilihan ditampilkan</dt>
                    <dd class="font-semibold">{{ $detail['opsi'] ? implode(', ', $detail['opsi']) . ', Tidak tahu' : '-' }}</dd>
                </dl>
                @if ($detail['grup'])
                    <p class="mt-2 text-xs text-gray-600">Program ini selalu dinilai sebagai BAYAN (grup) sesuai sheet Klasifikasi; jawaban responden tetap dicatat.</p>
                @endif
                @if (! $data->rincian_id)
                    <p class="mt-2 text-xs text-amber-700">Data lama: diisi sebelum alur desa → program → rincian → perusahaan dipakai.</p>
                @endif
            </div>
        </div>

        {{-- Jawaban kuesioner --}}
        <div class="rounded-xl border border-orange-200 p-4">
            <h3 class="font-extrabold text-gray-900 mb-2">Jawaban Kuesioner</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left w-10">No</th>
                            <th class="text-left">Unsur &amp; pertanyaan</th>
                            <th class="text-left">Jawaban</th>
                            <th class="text-center w-16">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (SurveyOptions::UNSUR as $i => [$namaUnsur, $jawaban])
                            @php $nilai = $data->{"u{$i}"}; @endphp
                            <tr>
                                <td class="align-top">U{{ $i }}</td>
                                <td class="align-top">
                                    <p class="font-bold text-gray-900">{{ $namaUnsur }}</p>
                                    <p class="text-gray-600 text-xs">{{ SurveyOptions::PERTANYAAN[$i] ?? '' }}</p>
                                </td>
                                <td class="align-top font-semibold">{{ $nilai ? ($jawaban[$nilai - 1] ?? '-') : '-' }}</td>
                                <td class="align-top text-center">
                                    @if ($nilai)
                                        <span class="inline-block rounded-full px-2.5 py-0.5 font-black {{ $badge[$nilai] ?? '' }}">{{ $nilai }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Saran --}}
        <div class="rounded-xl border border-orange-200 p-4">
            <h3 class="font-extrabold text-gray-900 mb-1">Saran, Masukan &amp; Harapan</h3>
            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $tampil($data->saran) }}</p>
        </div>

        {{-- Penilaian lain oleh responden yang sama --}}
        @if ($lainnya->isNotEmpty())
            <div class="rounded-xl border border-orange-200 p-4">
                <h3 class="font-extrabold text-gray-900 mb-2">Penilaian lain oleh responden ini ({{ $lainnya->count() }})</h3>
                <table class="w-full text-sm">
                    <thead><tr><th class="text-left">Rincian program</th><th class="text-left">Perusahaan dinilai</th><th class="text-right">Nilai</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($lainnya as $l)
                            <tr>
                                <td>{{ $l->rincian_nama ?? '-' }}</td>
                                <td>{{ $l->perusahaan_dinilai ?? '-' }}</td>
                                <td class="text-right font-bold">{{ number_format($l->skor / 9 * 25, 2, ',', '.') }}</td>
                                <td class="text-right"><a href="{{ route('rekapTotal.show', $l->id) }}" class="js-admin-nav font-bold text-orange-700 hover:underline">Lihat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex justify-between">
            <a href="{{ $backUrl }}" class="js-admin-nav px-4 py-2 rounded-lg border border-orange-300 text-orange-800 font-bold text-sm">← Kembali</a>
            <form method="POST" action="{{ route('rekapTotal.destroy', $data->id) }}" onsubmit="return confirm('Hapus penilaian ini?');" class="no-loading">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white font-bold text-sm hover:bg-red-700">Hapus penilaian</button>
            </form>
        </div>
    </div>
</div>
@endsection
