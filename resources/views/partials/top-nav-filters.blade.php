@php
    $routeBase = $routeBase ?? '/';
    $brandTitle = $brandTitle ?? 'E-SKM PPM Bayan Group';
    $subtitle = $subtitle ?? null;
    $showBagian = $showBagian ?? false;
    $showHomeButton = $showHomeButton ?? false;
    $showAdminButton = $showAdminButton ?? true;
    $homeHref = $homeHref ?? '/';
    $adminHref = $adminHref ?? '/login';

    // Filter tahunan: Tahun + Pilar (tanpa triwulan)
    $selectedYear = $selectedYear ?? \App\Support\SurveyFilter::year();
    $yearOptions = $yearOptions ?? \App\Support\SurveyFilter::years();
    $bagianOptions = $bagianOptions ?? \App\Support\SurveyFilter::pilarOptions();
    $selectedBagian = $selectedBagian ?? \App\Support\SurveyFilter::selectedPilar();
    $bagianQuery = $bagianQuery ?? (\App\Support\SurveyFilter::isAllPilar() ? '' : \App\Support\SurveyFilter::pilarCsv());
    $bagianChipLabel = count($selectedBagian) >= count($bagianOptions) ? 'Semua pilar' : count($selectedBagian) . ' pilar';
    $showPerusahaan = $showPerusahaan ?? true;
    $perusahaanOptions = \App\Support\SurveyFilter::perusahaanOptions();
    $selectedPerusahaan = \App\Support\SurveyFilter::selectedPerusahaan();
    // URL dengan filter aktif; $params menimpa nilai yang sedang dipilih.
    $navUrl = fn (array $params) => $routeBase . '?' . http_build_query(array_filter(
        $params + ['Tahun' => $selectedYear, 'bagian' => $bagianQuery, 'perusahaan' => $selectedPerusahaan],
        fn ($v) => $v !== null && $v !== ''
    ));

    $brandTitleClass = $brandTitleClass ?? 'text-base font-extrabold tracking-tight text-[#FF8800]';
    $extraButtons = $extraButtons ?? null;
    $enableLoadingNav = $enableLoadingNav ?? false;
    $loadingNavClass = $enableLoadingNav ? 'js-nav' : '';
    $navClass = $navClass ?? 'shrink-0 bg-white shadow-sm z-50 px-8 py-1.5';
    $containerClass = $containerClass ?? 'max-w-full px-16 mx-auto flex items-center justify-between';
    $filterWrapperClass = $filterWrapperClass ?? 'flex items-center gap-6';
@endphp

<nav class="{{ $navClass }} relative overflow-visible">
    <div class="{{ $containerClass }} overflow-visible">
        <div class="flex items-center gap-2">
            <img src="{{ asset('assets/logo-bayan.png') }}" alt="Logo Bayan" class="h-8 w-8 rounded-xl bg-white p-1 border border-orange-200">
            <span class="{{ $brandTitleClass }}">{{ $brandTitle }}</span>
            <div class="h-6 w-px bg-slate-200 mx-4"></div>
            @if ($subtitle)
                <span class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">{{ $subtitle }}</span>
            @endif
        </div>

        <div class="{{ $filterWrapperClass }} overflow-visible">

            <div class="filter-inline">
                <span class="filter-inline-label">Tahun</span>
                <details class="filter-chip">
                    <summary>
                        <span class="material-symbols-outlined text-[#FF8800] text-[16px]">calendar_today</span>
                        <span class="filter-chip-value">{{ $selectedYear }}</span>
                        <span class="material-symbols-outlined filter-chip-caret">expand_more</span>
                    </summary>
                    <div class="filter-menu">
                        @foreach ($yearOptions as $yearOption)
                            <a class="{{ $loadingNavClass }} {{ (int) $selectedYear === (int) $yearOption ? 'is-active' : '' }}"
                               href="{{ $navUrl(['Tahun' => $yearOption]) }}">{{ $yearOption }}</a>
                        @endforeach
                    </div>
                </details>
            </div>

            @if ($showPerusahaan)
                <div class="filter-inline">
                    <span class="filter-inline-label">Perusahaan</span>
                    <details class="filter-chip">
                        <summary>
                            <span class="material-symbols-outlined text-[#FF8800] text-[16px]">domain</span>
                            <span class="filter-chip-value">{{ $selectedPerusahaan ?? 'Semua' }}</span>
                            <span class="material-symbols-outlined filter-chip-caret">expand_more</span>
                        </summary>
                        <div class="filter-menu">
                            <a class="{{ $loadingNavClass }} {{ $selectedPerusahaan === null ? 'is-active' : '' }}" href="{{ $navUrl(['perusahaan' => '']) }}">Semua perusahaan</a>
                            @foreach ($perusahaanOptions as $kode => $label)
                                <a class="{{ $loadingNavClass }} {{ $selectedPerusahaan === $kode ? 'is-active' : '' }}" href="{{ $navUrl(['perusahaan' => $kode]) }}">{{ $label }}</a>
                            @endforeach
                        </div>
                    </details>
                </div>
            @endif

            @if ($showBagian)
                <div class="filter-inline">
                    <span class="filter-inline-label">Pilar</span>
                    <details class="filter-chip min-w-[100px]">
                        <summary>
                            <span class="material-symbols-outlined text-[#FF8800] text-[16px]">tune</span>
                            <span class="filter-chip-value">{{ $bagianChipLabel }}</span>
                            <span class="material-symbols-outlined filter-chip-caret">expand_more</span>
                        </summary>
                        <div class="filter-menu bagian-menu">
                            <div class="max-h-64 overflow-auto space-y-2 pr-1">
                                @foreach ($bagianOptions as $value => $label)
                                    <label class="flex items-center gap-2 text-[12px] text-slate-700">
                                        <input type="checkbox" class="bagian-checkbox rounded border-slate-300" value="{{ $value }}" {{ in_array($value, $selectedBagian, true) ? 'checked' : '' }}>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <button type="button" id="applyBagian"
                                data-route="{{ $routeBase }}" data-tahun="{{ $selectedYear }}" data-perusahaan="{{ $selectedPerusahaan }}"
                                class="mt-3 w-full bg-[#FF8800] text-white text-[11px] font-black py-2 rounded-lg hover:opacity-95 transition-opacity">Terapkan Filter</button>
                        </div>
                    </details>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if ($showHomeButton)
                <a href="{{ $homeHref }}" class="admin-button whitespace-nowrap {{ $loadingNavClass }}">Halaman Utama</a>
            @endif
            @if ($showAdminButton)
                <a href="{{ $adminHref }}" class="{{ $showHomeButton ? 'admin-button ' . $loadingNavClass : $loadingNavClass }}">
                    @if ($showHomeButton)
                        Admin
                    @else
                        <button class="admin-button active:scale-95 {{ $loadingNavClass }}">Admin</button>
                    @endif
                </a>
            @endif
            @if ($extraButtons)
                {!! $extraButtons !!}
            @endif
        </div>
    </div>
</nav>

<script>
    // Terapkan pilihan pilar; tahun tetap dipertahankan. Semua pilar = tanpa parameter bagian.
    (function () {
        const btn = document.getElementById('applyBagian');
        if (!btn) return;
        btn.addEventListener('click', function () {
            const boxes = Array.from(document.querySelectorAll('.bagian-checkbox'));
            const checked = boxes.filter((cb) => cb.checked).map((cb) => cb.value);
            if (checked.length === 0) {
                alert('Silakan pilih minimal satu pilar.');
                return;
            }
            const params = new URLSearchParams({ Tahun: btn.dataset.tahun });
            if (checked.length < boxes.length) params.set('bagian', checked.join(','));
            if (btn.dataset.perusahaan) params.set('perusahaan', btn.dataset.perusahaan);
            if (window.showPageLoading) window.showPageLoading();
            window.location.href = btn.dataset.route + '?' + params.toString();
        });
    })();
</script>
