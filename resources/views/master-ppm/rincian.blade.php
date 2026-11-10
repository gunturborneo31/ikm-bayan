@extends('layouts.index', ['title' => 'Atur Rincian ' . $rincian->id])

@php
    $input = 'rounded-lg border-orange-200 text-sm px-2 py-1';
    $btn = 'no-theme rounded-lg px-4 py-2 text-sm font-bold';
    $old = fn ($key, $default) => old($key, $default);
    $penilaiTerpilih = old('penilai', $penilai);
    $isGrup = (bool) old('dinilai_sebagai_grup', $rincian->dinilai_sebagai_grup);
    $isAktif = (bool) old('is_active', $rincian->is_active);
    $oldLokasi = old('lokasi');
    $cek = function (string $kode, int $desaId) use ($oldLokasi, $terpilih) {
        if (is_array($oldLokasi)) {
            return in_array((string) $desaId, array_map('strval', $oldLokasi[$kode] ?? []), true);
        }
        return isset($terpilih[$kode][$desaId]);
    };
    $backUrl = route('master-ppm.index', ['tab' => 'program']) . ($pilar ? '#pilar-' . $pilar->id : '');
@endphp

@section('content')
<div class="admin-main">
    <form method="POST" action="{{ route('master-ppm.rincian.update', $rincian->id) }}" class="w-full rounded-xl p-4 md:p-5 mb-2 border border-orange-200 bg-white space-y-4">
        @csrf @method('PUT')

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ $backUrl }}" class="js-admin-nav text-sm font-bold text-orange-700 hover:underline">← Kembali ke Master Data PPM</a>
                <p class="text-xs font-black tracking-widest text-orange-700 mt-1">{{ $pilar->nama ?? '-' }} › {{ $program->nama ?? '-' }}</p>
                <h2 class="text-lg font-extrabold text-gray-900">Rincian {{ $rincian->id }}</h2>
                <p class="text-xs text-gray-500">{{ $jumlahPenilaian }} penilaian sudah tersimpan untuk rincian ini.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-300 text-green-800 font-semibold text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 font-semibold text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-3 md:grid-cols-[1fr_120px]">
            <div>
                <label class="block text-xs font-bold text-gray-600">Nama rincian</label>
                <input name="nama" value="{{ $old('nama', $rincian->nama) }}" required maxlength="255" class="{{ $input }} w-full">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600">Urutan</label>
                <input name="urutan" type="number" min="0" value="{{ $old('urutan', $rincian->urutan) }}" class="{{ $input }} w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-600">Catatan (sheet Klasifikasi)</label>
                <input name="catatan" value="{{ $old('catatan', $rincian->catatan) }}" maxlength="255" class="{{ $input }} w-full">
            </div>
        </div>

        {{-- Lokasi pelaksana --}}
        <div class="rounded-xl border border-orange-200 p-4">
            <h3 class="font-extrabold text-gray-900">Perusahaan pelaksana per desa</h3>
            <p class="text-xs text-gray-600 mb-2">Centang perusahaan yang melaksanakan rincian ini di tiap desa. Di survei, rincian hanya muncul di desa yang dicentang, dan pilihan perusahaannya hanya yang dicentang di desa itu (ditambah "Tidak tahu").</p>
            <div class="overflow-x-auto">
                <table class="text-sm min-w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Desa</th>
                            @foreach ($pelaksana as $c)
                                <th class="text-center px-3">
                                    {{ $c->kode }}
                                    <button type="button" class="no-theme block mx-auto text-[10px] font-semibold text-orange-700 underline" data-toggle-col="{{ $c->kode }}">semua</button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($desa as $d)
                            <tr class="{{ $d->is_active ? '' : 'text-gray-400' }}">
                                <td>{{ $d->nama }} @unless ($d->is_active)<span class="text-[10px]">(nonaktif)</span>@endunless</td>
                                @foreach ($pelaksana as $c)
                                    <td class="text-center">
                                        <input type="checkbox" name="lokasi[{{ $c->kode }}][]" value="{{ $d->id }}" data-col="{{ $c->kode }}"
                                            @checked($cek($c->kode, (int) $d->id))
                                            class="w-4 h-4 rounded border-orange-300 text-orange-600">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr>
                            <td class="align-top pt-2">
                                Lokasi lain
                                <span class="block text-[10px] text-gray-500">Ring 2/3, kecamatan; pisahkan dengan titik koma. Tidak tampil di survei.</span>
                            </td>
                            @foreach ($pelaksana as $c)
                                <td class="align-top pt-2 px-1">
                                    <input name="lokasi_lain[{{ $c->kode }}]" value="{{ old('lokasi_lain.' . $c->kode, implode('; ', $lokasiLain[$c->kode] ?? [])) }}" class="{{ $input }} w-32 text-xs">
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Penilai & atribusi --}}
        <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-xl border border-orange-200 p-4">
                <h3 class="font-extrabold text-gray-900">Penilai (tanda √ di sheet Klasifikasi)</h3>
                <div class="flex flex-wrap gap-3 mt-2">
                    @foreach ($perusahaan as $c)
                        <label class="flex items-center gap-1.5 text-sm font-semibold">
                            <input type="checkbox" name="penilai[]" value="{{ $c->kode }}" @checked(in_array($c->kode, $penilaiTerpilih, true)) class="rounded border-orange-300 text-orange-600">
                            {{ $c->kode }}
                        </label>
                    @endforeach
                </div>
                <label class="flex items-start gap-2 text-sm mt-3">
                    <input type="hidden" name="dinilai_sebagai_grup" value="0">
                    <input type="checkbox" name="dinilai_sebagai_grup" value="1" @checked($isGrup) class="mt-0.5 rounded border-orange-300 text-orange-600">
                    <span><b>Selalu dinilai sebagai BAYAN (grup)</b><br><span class="text-xs text-gray-600">Untuk program dengan banyak irisan perusahaan di desa yang sama (mis. Beasiswa). Jawaban perusahaan dari responden tetap dicatat, tetapi nilainya tidak dibebankan ke anak perusahaan.</span></span>
                </label>
            </div>
            <div class="rounded-xl border border-orange-200 p-4">
                <h3 class="font-extrabold text-gray-900">Tampil di survei</h3>
                <label class="flex items-start gap-2 text-sm mt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($isAktif) class="mt-0.5 w-5 h-5 rounded border-orange-300 text-orange-600">
                    <span><b>Aktif</b> — rincian bisa dipilih responden di desa yang dicentang.<br><span class="text-xs text-gray-600">Butuh minimal satu desa pelaksana dan satu penilai. Matikan untuk menyembunyikan tanpa menghapus data penilaian lama.</span></span>
                </label>
            </div>
        </div>

        <div class="flex flex-wrap justify-between gap-2">
            <a href="{{ $backUrl }}" class="js-admin-nav {{ $btn }} border border-orange-300 text-orange-800">Batal</a>
            <button type="submit" class="{{ $btn }} bg-orange-600 text-white hover:bg-orange-700">Simpan rincian</button>
        </div>
    </form>

    @if ($jumlahPenilaian === 0)
        <form method="POST" action="{{ route('master-ppm.rincian.destroy', $rincian->id) }}" class="no-loading text-right mt-2" onsubmit="return confirm('Hapus rincian {{ $rincian->id }}?')">
            @csrf @method('DELETE')
            <button class="no-theme rounded-lg px-4 py-2 text-sm font-bold border border-red-300 text-red-700 hover:bg-red-50">Hapus rincian</button>
        </form>
    @endif
</div>

<script>
    // Tombol "semua" di kepala kolom: centang/lepas semua desa untuk perusahaan itu
    document.querySelectorAll('[data-toggle-col]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const boxes = document.querySelectorAll('input[data-col="' + btn.dataset.toggleCol + '"]');
            const allChecked = Array.from(boxes).every((b) => b.checked);
            boxes.forEach((b) => { b.checked = !allChecked; });
        });
    });
</script>
@endsection
