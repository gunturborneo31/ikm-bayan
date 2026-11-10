@extends('layouts.index', ['title' => 'Master Data PPM'])

@php
    $tabs = ['program' => 'Pilar, Program & Rincian', 'desa' => 'Desa', 'perusahaan' => 'Perusahaan', 'json' => 'Impor / Ekspor JSON'];
    $input = 'rounded-lg border-orange-200 text-sm px-2 py-1';
    $btn = 'no-theme rounded-lg px-3 py-1.5 text-xs font-bold';
    $btnPrimary = $btn . ' bg-orange-600 text-white hover:bg-orange-700';
    $btnGhost = $btn . ' border border-orange-300 text-orange-800 hover:bg-orange-50';
    $btnDanger = $btn . ' border border-red-300 text-red-700 hover:bg-red-50';
@endphp

@section('content')
<div class="admin-main">
    <div class="w-full rounded-xl p-4 md:p-5 mb-2 border border-orange-200 bg-white space-y-4">
        <div>
            <h2 class="text-lg font-extrabold text-orange-800">Master Data PPM</h2>
            <p class="text-sm text-gray-600">Data di sini langsung dipakai halaman survei dan semua laporan. Survei hanya menampilkan rincian yang <b>aktif</b> dan punya perusahaan pelaksana di desa yang dipilih responden.</p>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-300 text-green-800 font-semibold text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 font-semibold text-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 font-semibold text-sm">{{ $errors->first() }}</div>
        @endif

        <nav class="flex flex-wrap gap-2 border-b border-orange-100 pb-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('master-ppm.index', ['tab' => $key]) }}"
                   class="js-admin-nav rounded-full px-4 py-1.5 text-sm font-bold {{ $tab === $key ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-800 hover:bg-orange-100' }}">{{ $label }}</a>
            @endforeach
        </nav>

        {{-- ═════════ PILAR / PROGRAM / RINCIAN ═════════ --}}
        @if ($tab === 'program')
            <form method="POST" action="{{ route('master-ppm.pilar.store') }}" class="flex flex-wrap items-end gap-2 rounded-xl bg-orange-50/60 p-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600">Pilar baru</label>
                    <input name="nama" required maxlength="255" placeholder="Nama pilar" class="{{ $input }} w-72">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600">Kode singkat (opsional)</label>
                    <input name="kode" maxlength="40" placeholder="mis. ekonomi-kreatif" class="{{ $input }} w-48">
                </div>
                <button class="{{ $btnPrimary }}">+ Tambah pilar</button>
            </form>

            @forelse ($pilar as $p)
                <details id="pilar-{{ $p->id }}" class="rounded-xl border border-orange-200" open>
                    <summary class="cursor-pointer px-4 py-3 font-extrabold text-gray-900 flex flex-wrap items-center gap-2">
                        <span class="text-orange-700">{{ $p->id }}</span> {{ $p->nama }}
                        <span class="text-xs font-semibold text-gray-500">({{ count($program[$p->id] ?? []) }} program · kode: {{ $p->kode }})</span>
                    </summary>
                    <div class="px-4 pb-4 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('master-ppm.pilar.update', $p->id) }}" class="flex flex-wrap items-center gap-2">
                                @csrf @method('PUT')
                                <input name="nama" value="{{ $p->nama }}" required class="{{ $input }} w-96 max-w-full">
                                <input name="urutan" type="number" min="0" value="{{ $p->urutan }}" class="{{ $input }} w-16" title="Urutan">
                                <button class="{{ $btnGhost }}">Simpan pilar</button>
                            </form>
                            <form method="POST" action="{{ route('master-ppm.pilar.destroy', $p->id) }}" class="no-loading" onsubmit="return confirm('Hapus pilar {{ $p->id }}?')">
                                @csrf @method('DELETE')
                                <button class="{{ $btnDanger }}">Hapus</button>
                            </form>
                        </div>

                        @foreach ($program[$p->id] ?? [] as $g)
                            <div class="rounded-lg border border-orange-100 p-3 bg-orange-50/30">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-orange-700 w-12">{{ $g->id }}</span>
                                    <form method="POST" action="{{ route('master-ppm.program.update', $g->id) }}" class="flex flex-wrap items-center gap-2">
                                        @csrf @method('PUT')
                                        <input name="nama" value="{{ $g->nama }}" required class="{{ $input }} w-96 max-w-full font-semibold">
                                        <input name="urutan" type="number" min="0" value="{{ $g->urutan }}" class="{{ $input }} w-16" title="Urutan">
                                        <button class="{{ $btnGhost }}">Simpan</button>
                                    </form>
                                    <form method="POST" action="{{ route('master-ppm.program.destroy', $g->id) }}" class="no-loading" onsubmit="return confirm('Hapus program {{ $g->id }}?')">
                                        @csrf @method('DELETE')
                                        <button class="{{ $btnDanger }}">Hapus</button>
                                    </form>
                                </div>

                                <table class="w-full text-sm mt-2">
                                    <thead>
                                        <tr>
                                            <th class="text-left w-16">ID</th>
                                            <th class="text-left">Rincian</th>
                                            <th class="text-left">Penilai</th>
                                            <th class="text-center">Desa</th>
                                            <th class="text-center">Penilaian</th>
                                            <th class="text-center">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rincian[$g->id] ?? [] as $r)
                                            <tr>
                                                <td class="text-xs font-bold text-gray-500">{{ $r->id }}</td>
                                                <td>
                                                    {{ $r->nama }}
                                                    @if ($r->dinilai_sebagai_grup)
                                                        <span class="ml-1 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-black text-orange-800">BAYAN (grup)</span>
                                                    @endif
                                                    @if ($r->catatan)
                                                        <span class="block text-xs text-gray-500">{{ $r->catatan }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-xs">{{ str_replace(',', ', ', $r->penilai) ?: '-' }}</td>
                                                <td class="text-center">{{ $desaPerRincian[$r->id] ?? 0 }}</td>
                                                <td class="text-center">{{ $pemakaianRincian[$r->id] ?? 0 }}</td>
                                                <td class="text-center">
                                                    @if ($r->is_active)
                                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-bold text-green-800">Aktif</span>
                                                    @else
                                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-bold text-slate-600">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td class="text-right whitespace-nowrap">
                                                    <a href="{{ route('master-ppm.rincian', $r->id) }}" class="js-admin-nav {{ $btnPrimary }}">Atur</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-gray-500 text-sm">Belum ada rincian.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <form method="POST" action="{{ route('master-ppm.rincian.store') }}" class="flex flex-wrap items-center gap-2 mt-2">
                                    @csrf
                                    <input type="hidden" name="program_id" value="{{ $g->id }}">
                                    <input name="nama" required maxlength="255" placeholder="Nama rincian baru" class="{{ $input }} w-96 max-w-full">
                                    <button class="{{ $btnGhost }}">+ Tambah rincian</button>
                                </form>
                            </div>
                        @endforeach

                        <form method="POST" action="{{ route('master-ppm.program.store') }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="hidden" name="pilar_id" value="{{ $p->id }}">
                            <input name="nama" required maxlength="255" placeholder="Nama program baru di {{ $p->id }}" class="{{ $input }} w-96 max-w-full">
                            <button class="{{ $btnGhost }}">+ Tambah program</button>
                        </form>
                    </div>
                </details>
            @empty
                <p class="text-sm text-gray-600">Belum ada pilar. Tambahkan pilar baru atau impor file JSON.</p>
            @endforelse
        @endif

        {{-- ═════════ DESA ═════════ --}}
        @if ($tab === 'desa')
            <form method="POST" action="{{ route('master-ppm.desa.store') }}" class="flex flex-wrap items-end gap-2 rounded-xl bg-orange-50/60 p-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600">Desa baru</label>
                    <input name="nama" required maxlength="100" placeholder="Nama desa (gunakan ejaan baku)" class="{{ $input }} w-72">
                </div>
                <button class="{{ $btnPrimary }}">+ Tambah desa</button>
            </form>
            <p class="text-xs text-gray-600">Desa nonaktif tidak muncul di survei, tetapi data penilaian lamanya tetap ada di laporan. Program yang berjalan di tiap desa diatur di tab <b>Pilar, Program &amp; Rincian → Atur</b>.</p>

            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Nama desa</th>
                        <th class="text-center w-20">Urutan</th>
                        <th class="text-center w-24">Aktif</th>
                        <th class="text-center">Rincian berjalan</th>
                        <th class="text-center">Penilaian</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($desa as $d)
                        <tr class="{{ $d->is_active ? '' : 'text-gray-400' }}">
                            <td colspan="3">
                                <form method="POST" action="{{ route('master-ppm.desa.update', $d->id) }}" class="grid grid-cols-[1fr_80px_96px_auto] items-center gap-2" id="desa-{{ $d->id }}">
                                    @csrf @method('PUT')
                                    <input name="nama" value="{{ $d->nama }}" required maxlength="100" class="{{ $input }} w-full">
                                    <input name="urutan" type="number" min="0" value="{{ $d->urutan }}" class="{{ $input }} w-full text-center">
                                    <label class="flex items-center justify-center gap-1 text-xs font-bold">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($d->is_active) class="rounded border-orange-300 text-orange-600"> Aktif
                                    </label>
                                    <button class="{{ $btnGhost }}">Simpan</button>
                                </form>
                            </td>
                            <td class="text-center">{{ $rincianPerDesa[$d->id] ?? 0 }}</td>
                            <td class="text-center">{{ $pemakaianDesa[$d->id] ?? 0 }}</td>
                            <td class="text-right">
                                @if (($pemakaianDesa[$d->id] ?? 0) === 0)
                                    <form method="POST" action="{{ route('master-ppm.desa.destroy', $d->id) }}" class="no-loading" onsubmit="return confirm('Hapus desa {{ $d->nama }}? Lokasi program di desa ini ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="{{ $btnDanger }}">Hapus</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-500" title="Sudah dipakai penilaian">terpakai</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- ═════════ PERUSAHAAN ═════════ --}}
        @if ($tab === 'perusahaan')
            <form method="POST" action="{{ route('master-ppm.perusahaan.store') }}" class="flex flex-wrap items-end gap-2 rounded-xl bg-orange-50/60 p-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600">Kode</label>
                    <input name="kode" required maxlength="10" placeholder="mis. KDP" class="{{ $input }} w-28 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600">Nama anak perusahaan</label>
                    <input name="nama" required maxlength="100" class="{{ $input }} w-72">
                </div>
                <button class="{{ $btnPrimary }}">+ Tambah perusahaan</button>
            </form>
            <p class="text-xs text-gray-600">Kode tidak bisa diubah karena tersimpan di setiap penilaian. BAYAN adalah grup (gabungan) dan tidak menjadi pelaksana per desa.</p>

            <table class="w-full text-sm">
                <thead><tr><th class="text-left w-24">Kode</th><th class="text-left">Nama</th><th class="text-center">Penilaian (dinilai)</th><th></th></tr></thead>
                <tbody>
                    @foreach ($perusahaan as $c)
                        <tr>
                            <td class="font-black">{{ $c->kode }} @if ($c->is_grup)<span class="text-[10px] text-orange-700">(grup)</span>@endif</td>
                            <td>
                                <form method="POST" action="{{ route('master-ppm.perusahaan.update', $c->kode) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input name="nama" value="{{ $c->nama }}" required maxlength="100" class="{{ $input }} w-80 max-w-full">
                                    <button class="{{ $btnGhost }}">Simpan</button>
                                </form>
                            </td>
                            <td class="text-center">{{ $pemakaianPerusahaan[$c->kode] ?? 0 }}</td>
                            <td class="text-right">
                                @if (! $c->is_grup && ($pemakaianPerusahaan[$c->kode] ?? 0) === 0)
                                    <form method="POST" action="{{ route('master-ppm.perusahaan.destroy', $c->kode) }}" class="no-loading" onsubmit="return confirm('Hapus perusahaan {{ $c->kode }}? Lokasi & status penilainya ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="{{ $btnDanger }}">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- ═════════ JSON ═════════ --}}
        @if ($tab === 'json')
            <div class="grid md:grid-cols-2 gap-3">
                <div class="rounded-xl border border-orange-200 p-4">
                    <h3 class="font-extrabold text-gray-900">Ekspor (cadangan)</h3>
                    <p class="text-sm text-gray-600 mt-1">Unduh seluruh master data saat ini dalam format yang sama dengan file klasifikasi (desa, perusahaan, pilar, program, rincian, lokasi). Simpan sebagai cadangan sebelum perubahan besar.</p>
                    <a href="{{ route('master-ppm.export') }}" class="inline-block mt-3 {{ $btnPrimary }}">Download JSON</a>
                </div>
                <div class="rounded-xl border border-red-200 p-4">
                    <h3 class="font-extrabold text-gray-900">Impor (ganti master data)</h3>
                    <p class="text-sm text-gray-600 mt-1">Pilar, program, rincian dan lokasi diganti dengan isi file. Desa dicocokkan lewat nama dan penilaian yang sudah tersimpan tidak dihapus, tetapi perubahan lewat halaman ini yang tidak ada di file akan hilang.</p>
                    <form method="POST" action="{{ route('master-ppm.import') }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                        @csrf
                        <input type="file" name="file" accept=".json,application/json" required class="block text-sm">
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" name="konfirmasi" value="1" required class="mt-0.5 rounded border-red-300 text-red-600">
                            Saya paham master data akan diganti (sudah download cadangan).
                        </label>
                        <button class="{{ $btn }} bg-red-600 text-white hover:bg-red-700">Impor JSON</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
