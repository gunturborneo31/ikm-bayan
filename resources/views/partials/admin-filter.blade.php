{{--
    Filter laporan admin: Tahun + Pilar (sistem penilaian tahunan, tanpa triwulan).
    Parameter URL lain (kolom jenkel/usia/..., keterangan) tetap dipertahankan.
    Pakai: @include('partials.admin-filter', ['route' => '/rekapTotal'])
--}}
@php
    $filterRoute = $route ?? ('/' . request()->path());
    $keep = request()->except(['Tahun', 'tahun', 'bagian', 'perusahaan', 'tw', 'triwulan', 'Bulan', 'page']);
    $filterYear = \App\Support\SurveyFilter::year();
    $filterPilar = \App\Support\SurveyFilter::pilarOptions();
    $filterSelected = \App\Support\SurveyFilter::selectedPilar();
    $filterAll = count($filterSelected) >= count($filterPilar);
    $filterPerusahaan = \App\Support\SurveyFilter::selectedPerusahaan();
    $filterBagian = $filterAll ? null : implode(',', $filterSelected);
    // Bangun URL dengan filter aktif; $params menimpa nilai yang sedang dipilih.
    $filterUrl = fn (array $params) => $filterRoute . '?' . http_build_query(array_filter(
        $params + $keep + ['Tahun' => $filterYear, 'bagian' => $filterBagian, 'perusahaan' => $filterPerusahaan],
        fn ($v) => $v !== null && $v !== ''
    ));
    $filterId = 'af' . substr(md5($filterRoute), 0, 6);
@endphp

<div class="flex flex-row items-center gap-3 flex-wrap">
    {{-- tahun --}}
    <div class="flex flex-row items-center gap-2">
        <label for="{{ $filterId }}-tahun" class="font-bold text-gray-900 text-sm">Tahun</label>
        <select id="{{ $filterId }}-tahun"
            onchange="if (window.showAdminLoading) window.showAdminLoading(); document.location.href = this.value"
            class="bg-white border border-gray-400 text-gray-900 font-bold text-sm text-center rounded-full px-3 py-1">
            @foreach (\App\Support\SurveyFilter::years() as $y)
                <option value="{{ $filterUrl(['Tahun' => $y]) }}" @selected($y === $filterYear)>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    {{-- perusahaan (yang dinilai) --}}
    <div class="flex flex-row items-center gap-2">
        <label for="{{ $filterId }}-perusahaan" class="font-bold text-gray-900 text-sm">Perusahaan</label>
        <select id="{{ $filterId }}-perusahaan"
            onchange="if (window.showAdminLoading) window.showAdminLoading(); document.location.href = this.value"
            class="bg-white border border-gray-400 text-gray-900 font-bold text-sm rounded-full px-3 py-1">
            <option value="{{ $filterUrl(['perusahaan' => '']) }}" @selected($filterPerusahaan === null)>Semua perusahaan</option>
            @foreach (\App\Support\SurveyFilter::perusahaanOptions() as $kode => $label)
                <option value="{{ $filterUrl(['perusahaan' => $kode]) }}" @selected($filterPerusahaan === $kode)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- pilar --}}
    <div class="relative flex flex-row items-center gap-2">
        <span class="font-bold text-gray-900 text-sm">Pilar</span>
        <button type="button" data-dropdown-toggle="{{ $filterId }}-pilar"
            class="flex items-center bg-white border border-gray-400 font-bold text-sm rounded-full px-3 py-1">
            {{ $filterAll ? 'Semua pilar' : count($filterSelected) . ' pilar' }}
            <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
        </button>
        <div id="{{ $filterId }}-pilar" class="z-20 hidden border bg-white rounded-lg shadow w-[300px]">
            <ul class="space-y-2 px-4 py-3 max-h-72 overflow-auto">
                @foreach ($filterPilar as $kode => $label)
                    <li class="flex items-start gap-3">
                        <input id="{{ $filterId }}-{{ $kode }}" type="checkbox" value="{{ $kode }}"
                            class="{{ $filterId }}-cb mt-0.5 w-4 h-4 text-orange-600 border-orange-300 rounded focus:ring-orange-500"
                            @checked(in_array($kode, $filterSelected, true))>
                        <label for="{{ $filterId }}-{{ $kode }}" class="text-sm text-gray-700">{{ $label }}</label>
                    </li>
                @endforeach
            </ul>
            <div class="px-4 pb-3">
                <button type="button" id="{{ $filterId }}-apply"
                    class="w-full px-4 py-2 text-gray-900 rounded-lg bg-gradient-to-br from-[#EA580C] from-60% font-bold to-[#FDBA74] to-95%">
                    Tampilkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const btn = document.getElementById(@json($filterId . '-apply'));
        if (!btn) return;
        btn.addEventListener('click', function () {
            const boxes = Array.from(document.querySelectorAll(@json('.' . $filterId . '-cb')));
            const checked = boxes.filter((cb) => cb.checked).map((cb) => cb.value);
            if (checked.length === 0) {
                alert('Silakan pilih minimal satu pilar.');
                return;
            }
            const url = new URL(@json($filterUrl(['bagian' => ''])), window.location.origin);
            if (checked.length < boxes.length) url.searchParams.set('bagian', checked.join(','));
            if (window.showAdminLoading) window.showAdminLoading();
            window.location.href = url.pathname + url.search;
        });
    })();
</script>
