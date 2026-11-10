@extends('layouts.index', ['title' => 'Import Excel Responden'])

@php
    use App\Services\SurveyExcelImport as X;
    $statusInfo = [
        X::NEW => ['Baru', 'bg-green-100 text-green-800 border-green-200', 'Belum ada di sistem, siap diimport.'],
        X::SIMILAR => ['Mirip', 'bg-amber-100 text-amber-800 border-amber-200', 'Nama & no. HP sama tetapi tanggal berbeda. Periksa dulu, centang jika memang survei baru.'],
        X::DUP_FILE => ['Duplikat dalam file', 'bg-orange-100 text-orange-800 border-orange-200', 'Baris ganda di file yang sama. Hanya baris pertama yang dipakai.'],
        X::DUP_DB => ['Sudah ada di sistem', 'bg-slate-200 text-slate-700 border-slate-300', 'Data yang sama sudah tersimpan. Tidak akan diimport lagi.'],
        X::INVALID => ['Tidak valid', 'bg-red-100 text-red-800 border-red-200', 'Ada isian kosong/salah. Perbaiki di Excel lalu upload ulang.'],
    ];
@endphp

@section('content')
<div class="admin-main">
    <div class="w-full rounded-xl p-4 md:p-5 mb-2 border border-orange-200 bg-white space-y-4">
        <div>
            <h2 class="text-lg font-extrabold text-orange-800">Import Data Responden dari Excel</h2>
            <p class="text-sm text-gray-600">Cocok untuk input banyak data sekaligus atau saat di lapangan tanpa internet: isi file Excel di laptop, lalu upload ketika ada sinyal. Sistem memeriksa data ganda sebelum menyimpan.</p>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-300 text-green-800 font-semibold text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-800 font-semibold text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="grid md:grid-cols-2 gap-3">
            <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-4">
                <p class="text-xs font-black text-orange-700 tracking-wide">LANGKAH 1</p>
                <h3 class="font-bold text-gray-900">Download format Excel</h3>
                <p class="text-sm text-gray-600 mt-1">Sudah berisi pilihan dropdown (pilar, jenis kelamin, usia, pendidikan, pekerjaan, nilai 1–4) dan sheet petunjuk.</p>
                <a href="{{ route('admin.offline-skm.template') }}" class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-lg bg-orange-600 text-white font-bold text-sm hover:bg-orange-700">
                    <span class="material-symbols-outlined text-lg">download</span> Download Format (.xlsx)
                </a>
            </div>
            <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-4">
                <p class="text-xs font-black text-orange-700 tracking-wide">LANGKAH 2</p>
                <h3 class="font-bold text-gray-900">Upload file yang sudah diisi</h3>
                <p class="text-sm text-gray-600 mt-1">Belum ada data yang disimpan pada langkah ini, hanya dianalisis.</p>
                <form action="{{ route('admin.offline-skm.analyze') }}" method="POST" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2" id="uploadForm">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.xlsm,.csv" required class="text-sm max-w-full file:mr-2 file:rounded-md file:border-0 file:bg-white file:px-3 file:py-1.5 file:font-semibold file:text-orange-700">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white font-bold text-sm hover:bg-black">Upload &amp; Analisis</button>
                </form>
            </div>
        </div>

        @if ($analysis)
            @php
                $s = $analysis['summary'];
                $importable = $s[X::NEW] + $s[X::SIMILAR];
            @endphp
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="flex flex-wrap justify-between gap-2 items-baseline">
                    <div>
                        <p class="text-xs font-black text-orange-700 tracking-wide">LANGKAH 3</p>
                        <h3 class="font-bold text-gray-900">Hasil analisis: {{ $analysis['filename'] }}</h3>
                    </div>
                    <span class="text-xs text-gray-500">Dianalisis {{ $analysis['analyzed_at'] }}</span>
                </div>

                <div class="mt-3 rounded-lg px-4 py-3 text-sm font-semibold
                    {{ $s[X::NEW] === 0 && $s[X::SIMILAR] === 0 ? 'bg-slate-100 text-slate-700' : 'bg-blue-50 text-blue-900' }}">
                    @if ($s[X::DUP_DB] === $s['total'])
                        Semua {{ $s['total'] }} baris sudah ada di sistem. File ini kemungkinan besar sudah pernah diupload. Tidak ada yang perlu diimport.
                    @elseif ($importable === 0)
                        Tidak ada baris yang bisa diimport. Perbaiki baris bertanda merah di Excel lalu upload ulang.
                    @else
                        Dari {{ $s['total'] }} baris, <b>{{ $s[X::NEW] }}</b> baris baru siap diimport.
                        @if ($s[X::SIMILAR]) <b>{{ $s[X::SIMILAR] }}</b> baris mirip data lama dan perlu Anda periksa. @endif
                        @if ($s[X::DUP_FILE] + $s[X::DUP_DB]) {{ $s[X::DUP_FILE] + $s[X::DUP_DB] }} baris duplikat akan dilewati. @endif
                        @if ($s[X::INVALID]) {{ $s[X::INVALID] }} baris tidak valid. @endif
                    @endif
                </div>

                @if ($analysis['unknownHeaders'])
                    <p class="mt-2 text-xs text-amber-700">Kolom yang tidak dikenali dan diabaikan: {{ implode(', ', $analysis['unknownHeaders']) }}</p>
                @endif

                <div class="mt-3 flex flex-wrap gap-2" id="filters">
                    <button type="button" data-filter="all" class="filter-btn no-theme px-3 py-1.5 rounded-full border text-xs font-bold bg-gray-900 text-white border-gray-900">Semua ({{ $s['total'] }})</button>
                    @foreach ($statusInfo as $key => [$label, $cls])
                        @if ($s[$key])
                            <button type="button" data-filter="{{ $key }}" class="filter-btn no-theme px-3 py-1.5 rounded-full border text-xs font-bold {{ $cls }}">{{ $label }} ({{ $s[$key] }})</button>
                        @endif
                    @endforeach
                </div>

                <details class="mt-2 text-xs text-gray-600">
                    <summary class="cursor-pointer font-semibold">Arti status</summary>
                    <ul class="mt-1 space-y-0.5 pl-4 list-disc">
                        @foreach ($statusInfo as [$label, , $desc])
                            <li><b>{{ $label }}</b>: {{ $desc }}</li>
                        @endforeach
                        <li>Duplikat ditentukan dari nama, no. HP, tanggal survei, dan pilar yang sama.</li>
                    </ul>
                </details>

                <form action="{{ route('admin.import-offline-skm') }}" method="POST" id="importForm" class="mt-3">
                    @csrf
                    <input type="hidden" name="token" value="{{ $analysis['token'] }}">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 max-h-[60vh]">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-600 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 w-10">
                                        @if ($importable)
                                            <input type="checkbox" id="checkAll" title="Pilih semua yang bisa diimport" class="rounded">
                                        @endif
                                    </th>
                                    <th class="px-3 py-2">Baris</th>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Tanggal</th>
                                    <th class="px-3 py-2">Pilar</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2 min-w-[240px]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($analysis['rows'] as $row)
                                    @php
                                        [$label, $cls] = $statusInfo[$row['status']];
                                        $selectable = in_array($row['status'], [X::NEW, X::SIMILAR], true);
                                    @endphp
                                    <tr data-status="{{ $row['status'] }}" class="{{ $selectable ? '' : 'text-gray-500' }}">
                                        <td class="px-3 py-2">
                                            @if ($selectable)
                                                <input type="checkbox" name="rows[]" value="{{ $row['line'] }}" class="row-check rounded" {{ $row['status'] === X::NEW ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $row['line'] }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ $row['display']['nama'] ?: '—' }}
                                            @if ($row['display']['nohp'])<div class="text-xs font-normal text-gray-500">{{ $row['display']['nohp'] }}</div>@endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $row['display']['tanggal'] ?: '—' }}</td>
                                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($row['display']['pilar'], 32) ?: '—' }}</td>
                                        <td class="px-3 py-2"><span class="inline-block whitespace-nowrap px-2 py-0.5 rounded-full border text-[11px] font-bold {{ $cls }}">{{ $label }}</span></td>
                                        <td class="px-3 py-2 text-xs">{{ implode('; ', $row['notes']) ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        @if ($importable)
                            <button type="submit" id="importBtn" class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-bold text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                Import <span id="importCount">0</span> data terpilih
                            </button>
                        @endif
                        <a href="{{ route('admin.offline-skm') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Batal / upload file lain</a>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
(() => {
    const checks = () => [...document.querySelectorAll('.row-check')];
    const countEl = document.getElementById('importCount');
    const btn = document.getElementById('importBtn');
    const all = document.getElementById('checkAll');
    const update = () => {
        const n = checks().filter((c) => c.checked).length;
        if (countEl) countEl.textContent = n;
        if (btn) btn.disabled = n === 0;
        if (all) all.checked = n > 0 && n === checks().length;
    };
    document.addEventListener('change', (e) => {
        if (e.target === all) checks().forEach((c) => { c.checked = all.checked; });
        if (e.target === all || e.target.classList.contains('row-check')) update();
    });
    document.querySelectorAll('.filter-btn').forEach((b) => b.addEventListener('click', () => {
        const f = b.dataset.filter;
        document.querySelectorAll('tr[data-status]').forEach((tr) => { tr.hidden = f !== 'all' && tr.dataset.status !== f; });
        document.querySelectorAll('.filter-btn').forEach((x) => x.classList.toggle('ring-2', x === b));
    }));
    document.getElementById('importForm')?.addEventListener('submit', () => {
        if (btn) { btn.disabled = true; btn.textContent = 'Mengimport…'; } // prevent double submit
    });
    document.getElementById('uploadForm')?.addEventListener('submit', (e) => {
        const b = e.target.querySelector('button'); b.disabled = true; b.textContent = 'Menganalisis…';
    });
    update();
})();
</script>
@endsection
