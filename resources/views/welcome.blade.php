<?php

error_reporting(0);

use App\Support\SurveyFilter;

// Filter tahunan: Tahun + Pilar (lihat App\Support\SurveyFilter)
$tahunAktif = SurveyFilter::year();
$terms = SurveyFilter::selectedPilar();
$yearOptions = array_reverse(SurveyFilter::years()); // grafik tren: tahun lama -> baru

$indexTu = ['u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9'];
$aggregateSelect = 'COUNT(*) as total';
foreach ($indexTu as $index) {
    $aggregateSelect .= ', COALESCE(SUM(' . $index . '),0) as ' . $index;
}

$skmDari = static function ($aggregate) use ($indexTu): float {
    $total = (int) ($aggregate->total ?? 0);
    if ($total === 0) {
        return 0;
    }
    $weightedTotal = 0;
    foreach ($indexTu as $index) {
        $weightedTotal += round(((float) ($aggregate->{$index} ?? 0)) / $total, 2) * (1 / 9);
    }

    return round($weightedTotal * 25, 2);
};

// Tren SKM per tahun (filter pilar tetap berlaku)
$perTahun = DB::table('survey_responses')
    ->when(!SurveyFilter::isAllPilar(), fn ($q) => $q->whereIn('jenisPelayanan', $terms))
    ->when(SurveyFilter::selectedPerusahaan(), fn ($q, $kode) => $q->where('perusahaan_dinilai', $kode))
    ->selectRaw('tahun, ' . $aggregateSelect)
    ->groupBy('tahun')
    ->get()
    ->keyBy('tahun');
$nilaiSkmTahun = array_map(fn ($y) => $skmDari($perTahun[$y] ?? null), $yearOptions);
$nilaiSkm = json_encode($nilaiSkmTahun);

// Nilai SKM, NRR per unsur dan jumlah penilaian untuk tahun + pilar terpilih
$aggregateUnsur = SurveyFilter::query()->selectRaw($aggregateSelect)->first();
$totalUnsur = (int) ($aggregateUnsur->total ?? 0);
$nrrPerUnsur = [];
foreach ($indexTu as $index) {
    $nrrPerUnsur[] = $totalUnsur === 0 ? 0 : round(((float) ($aggregateUnsur->{$index} ?? 0)) / $totalUnsur, 3);
}

$nilaiSKM = 0;
if ($totalUnsur > 0) {
    $weightedTotal = 0;
    foreach ($nrrPerUnsur as $nrr) {
        $weightedTotal += $nrr * (1/9);
    }
    $nilaiSKM = round($weightedTotal * 25, 2);
}

// Responden unik: satu responden bisa menilai beberapa program (responden_uuid sama)
$jmlResponden = (int) (SurveyFilter::query()
    ->selectRaw('COUNT(DISTINCT responden_uuid) + SUM(CASE WHEN responden_uuid IS NULL THEN 1 ELSE 0 END) as n')
    ->value('n') ?? 0);


$unsurPelayanan = [
    'Kesesuaian Program',
    'Kualitas Pelaksanaan',
    'Keberlanjutan',
    'Kejelasan Informasi',
    'Kompetensi Tim',
    'Kemudahan Akses',
    'Sikap dan Etika',
    'Dampak Sosial dan Ekonomi',
    'Kebermanfaatan',
];

$getMutuBySkm = static function (float $value): string {
    if ($value <= 24.99) {
        return 'E';
    }
    if ($value <= 64.99) {
        return 'D';
    }
    if ($value <= 76.6) {
        return 'C';
    }
    if ($value <= 88.3) {
        return 'B';
    }
    return 'A';
};

$getKinerjaBySkm = static function (float $value): string {
    if ($value <= 24.99) {
        return 'SANGAT TIDAK MEMUASKAN';
    }
    if ($value <= 64.99) {
        return 'TIDAK MEMUASKAN';
    }
    if ($value <= 76.6) {
        return 'KURANG MEMUASKAN';
    }
    if ($value <= 88.3) {
        return 'MEMUASKAN';
    }
    return 'SANGAT MEMUASKAN';
};

$getMutuByNrr = static function (float $value) {
    if ($value <= 0.99) {
        return 'E';
    }
    if ($value <= 2.599) {
        return 'D';
    }
    if ($value <= 3.064) {
        return 'C';
    }
    if ($value <= 3.532) {
        return 'B';
    }
    return 'A';
};

// $nrrPerUnsur is already calculated above from aggregation
$kinerjaLabel =  $getKinerjaBySkm((float) $nilaiSKM);
$mutuPelayananLabel = $getMutuBySkm((float) $nilaiSKM);
$getMutuClass = static function (string $mutu): string {
    return match ($mutu) {
        'A' => 'bg-[#26C281]',
        'B' => 'bg-[#3B82F6]',
        'C' => 'bg-[#FACC15]',
        'D' => 'bg-[#FF8800]',
        default => 'bg-[#F43F5E]',
    };
};

$nilaiSkmSeries = $nilaiSkm;
$nilaiSkm = json_encode($nilaiSkm);


?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>E-SKM PPM Bayan Group - Dashboard</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary": "#885124",
                        "primary-container": "#ff8800",
                        "tertiary-fixed": "#c9e6ff",
                        "primary": "#ff8800",
                        "on-secondary": "#ffffff",
                        "surface-dim": "#d9dadb",
                        "surface-tint": "#ff8800",
                        "on-primary-container": "#613000",
                        "error": "#ba1a1a",
                        "primary-fixed-dim": "#ffb781",
                        "on-secondary-fixed-variant": "#6b3a0e",
                        "surface-container-highest": "#e1e3e4",
                        "background": "#f1f5f9",
                        "tertiary": "#006491",
                        "error-container": "#ffdad6",
                        "outline": "#8a7262",
                        "secondary-fixed-dim": "#ffb781",
                        "on-primary": "#ffffff",
                        "tertiary-fixed-dim": "#8aceff",
                        "on-primary-fixed-variant": "#6f3800",
                        "surface-container": "#e2e8f0",
                        "on-secondary-fixed": "#2f1400",
                        "tertiary-container": "#00b2fd",
                        "surface-container-high": "#cbd5e1",
                        "surface-container-lowest": "#ffffff",
                        "inverse-surface": "#2e3132",
                        "on-error": "#ffffff",
                        "secondary-fixed": "#ffdcc4",
                        "secondary-container": "#ffb781",
                        "on-tertiary-container": "#004160",
                        "inverse-primary": "#ffb781",
                        "on-secondary-container": "#794619",
                        "on-surface": "#191c1d",
                        "on-tertiary-fixed": "#001e2f",
                        "surface-variant": "#e1e3e4",
                        "outline-variant": "#dec1ae",
                        "on-surface-variant": "#574335",
                        "on-background": "#191c1d",
                        "inverse-on-surface": "#f0f1f2",
                        "on-tertiary": "#ffffff",
                        "primary-fixed": "#ffdcc4",
                        "on-primary-fixed": "#2f1400",
                        "surface": "#f1f5f9",
                        "surface-container-low": "#f8fafc",
                        "surface-bright": "#f8fafc",
                        "on-error-container": "#93000a",
                        "on-tertiary-fixed-variant": "#004c6e"
                    },
                    "borderRadius": {
                        "DEFAULT": "1.5rem",
                        "lg": "2rem",
                        "xl": "2.5rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Manrope', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .pattern-bg {
            background-color: #ff8800;
            background-image: radial-gradient(circle at 2px 2px, rgba(255, 255, 255, 0.15) 1px, transparent 0);
            background-size: 16px 16px;
        }

        .pattern-bg-light {
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 136, 0, 0.05) 1px, transparent 0);
            background-size: 12px 12px;
        }

@include('partials.filter-ui-styles', ['chipMinWidth' => '136px'])

        /* ─── Mobile responsive ───────────────────────────────── */
        @media (max-width: 767px) {
            /* Allow full-page scroll on mobile */
            html, body { overflow: visible !important; height: auto !important; }

            /* Tighten main padding */
            main { padding-left: 0.5rem !important; padding-right: 0.5rem !important; padding-bottom: 5rem; }

            /* Stack grid to single column */
            .grid-cols-12 { grid-template-columns: 1fr !important; }
            [class*="col-span-"] { grid-column: span 1 !important; }

            /* Remove fixed heights – let content breathe */
            [class*="h-\[60\%\]"],
            [class*="h-\[320px\]"] { height: auto !important; }
            [class*="h-28"],
            [class*="h-36"] { height: auto !important; }
            [class*="min-h-0"] { min-height: 0 !important; }

            /* Chart area: give reasonable fixed height on mobile */
            #monthlyBars { height: 12rem !important; flex: none !important; }

            /* Nilai SKM card: compact height on mobile */
            .pattern-bg { height: 13rem !important; }

            /* SKM value: smaller on mobile */
            .pattern-bg h1 { font-size: 4rem !important; }

            /* Remove heavy x-padding from grid inner wrapper */
            .px-16 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }

            /* Nav: compact on mobile */
            nav > div { flex-wrap: wrap; gap: 0.25rem; padding-left: 0.5rem; padding-right: 0.5rem; }
            nav > div > div:nth-child(2) {
                order: 3;
                width: 100%;
                overflow-x: auto;
                -ms-overflow-style: none;
                scrollbar-width: none;
                padding-bottom: 0.35rem;
            }
            nav > div > div:nth-child(2)::-webkit-scrollbar { display: none; }

            /* Legenda: allow natural height */
            .space-y-3 { overflow: visible !important; }
        }
    </style>
</head>

<body class="bg-slate-100 text-on-surface h-screen overflow-hidden flex flex-col">
    <div id="loadingOverlay" class="hidden fixed inset-0 z-[999] bg-white/70 backdrop-blur-sm items-center justify-center">
        <div class="bg-white border border-slate-200 shadow-lg rounded-full px-5 py-3 flex items-center gap-2 text-[12px] font-black text-[#FF8800]">
            <span class="material-symbols-outlined animate-spin">progress_activity</span>
            Memuat data...
        </div>
    </div>



@include('partials.top-nav-filters', [
    'routeBase' => '/',
    'showBagian' => true,
    'showHomeButton' => false,
    'showAdminButton' => true,
    'enableLoadingNav' => true,
    'extraButtons' => '<a href="https://www.bayan.com.sg/id" target="_blank" rel="noopener noreferrer" class="bg-[#FF8800] text-white px-4 py-2 rounded-full font-extrabold text-xs hover:opacity-90 active:scale-95 duration-200 whitespace-nowrap">Website Resmi Bayan</a>',
    'navClass' => 'shrink-0 bg-white shadow-sm z-50 px-3 md:px-8 py-1 md:py-1.5',
    'containerClass' => 'max-w-full mx-auto flex flex-wrap md:flex-nowrap items-center justify-between gap-1',
    'filterWrapperClass' => 'flex items-center gap-2 md:gap-6 order-last md:order-none w-full md:w-auto overflow-x-auto no-scrollbar py-0.5 md:py-0',
])

    <main class="flex-1 flex flex-col p-4 gap-4 px-4 md:px-8 w-full md:overflow-hidden">
        <div class="flex-1 grid grid-cols-1 md:grid-cols-12 px-2 md:px-16 gap-4 md:min-h-0">
            {{-- #kolom1 --}}
            <div class="md:col-span-4 flex flex-col gap-4 overflow-visible">
                <div class="pattern-bg card-elevated p-8 flex flex-col items-center justify-center text-center h-[60%] relative overflow-hidden rounded-[1vw]">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-24 -mt-24 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/5 rounded-full -ml-16 -mb-16 blur-2xl"></div>
                    <span class="material-symbols-outlined text-white/20 text-7xl absolute top-6 left-6 pointer-events-none" style='font-variation-settings: "FILL" 1;'>analytics</span>
                    <span class="relative z-10 text-white drop-shadow-xl text-[20px] font-extrabold uppercase tracking-[0.2em] mb-4 drop-shadow-sm text-sm">NILAI SKM</span>
                    <h1 class="relative z-10 font-black text-white drop-shadow-md text-[80px] leading-none">
                        {{  sprintf('%0.2f', $nilaiSKM) }}
                    </h1>
                </div>

                <div class="grid grid-cols-6 gap-4 h-28 shrink-0">
                    <div class="bg-white pattern-bg-light col-span-4 card-elevated p-4 flex flex-col  relative border border-slate-100 rounded-[1vw] overflow-hidden">
                        <div class="flex justify-between items-start">
                            <p class="font-black text-gray-800 uppercase  text-[10px]">KINERJA</p>
                            <span class="material-symbols-outlined text-gray-80 text-sm ">speed</span>
                        </div>
                        <p class="font-black text-[#FF8800] leading-tight uppercase text-xl text-center mt-3">{{ $kinerjaLabel }}</p>
                    </div>

                    <div class="bg-white pattern-bg-light col-span-2 card-elevated p-4 flex flex-col  relative border border-slate-100 rounded-[1vw] overflow-hidden items-center">
                        <div class="flex justify-between items-start w-full">
                            <p class="font-black text-gray-800 uppercase  text-[10px]">MUTU</p>
                            <span class="material-symbols-outlined text-gray-80 text-sm ">verified</span>
                        </div>
                        <p class="font-black text-on-surface text-5xl mt-1">{{ $mutuPelayananLabel }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 shrink-0">
                    <a class="js-nav" href="/dashboard?{{ \App\Support\SurveyFilter::queryString() }}">
                        <button class="w-full bg-white hover:bg-[#ffd6aa] transition-all py-3 rounded-[1vw] flex items-center justify-center gap-2 text-[11px] font-black text-[#FF8800] border border-2 border-[#ffab4c]">
                            <span class="material-symbols-outlined text-xl">group</span>
                            INFO RESPONDEN
                        </button>
                    </a>
                    <a class="js-nav" href="/skm#awal">
                        <button class="w-full bg-[#FF8800] text-white py-4 rounded-[1vw] flex items-center justify-center gap-3 font-black text-sm shadow-lg hover:opacity-95 active:scale-95 transition-all group">
                            <span class="material-symbols-outlined text-xl" style='font-variation-settings: "FILL" 1;'>description</span>
                            MULAI SURVEI
                        </button>
                    </a>
                </div>
            </div>

            {{-- #kolom2 --}}
            <div class="md:col-span-5 flex flex-col gap-4 md:min-h-0">
                <div class="bg-white card-elevated p-5 flex flex-col border border-slate-100 md:min-h-0 rounded-[1vw] md:flex-1">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xs font-black uppercase tracking-tight">Tren SKM Tahunan</h2>
                        <div class="flex items-center gap-2 text-[10px] font-black text-on-surface-variant bg-slate-50 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-[#FF8800]"></span> INDEKS
                        </div>
                    </div>

                    <div id="yearlyBars" class="flex items-end justify-between gap-0.5 px-1 pb-2 w-full"></div>
                </div>

                <div class="bg-white card-elevated overflow-visible flex flex-col md:h-[320px] h-auto border border-slate-100 rounded-[1vw] shrink-0">
                    <div class="h-full overflow-hidden flex flex-col">
                        <table class="w-full text-left h-full flex flex-col">
                            <thead class="shrink-0">
                                <tr class="bg-[#FF8800] flex items-center">
                                    <th class="w-12 text-[12px] font-black uppercase tracking-wider px-2 py-0.5 text-white border-none text-center">No</th>
                                    <th class="flex-1 text-[12px] font-black uppercase tracking-wider px-4 py-0.5 text-white border-none">Unsur Pelayanan</th>
                                    <th class="w-20 text-[12px] font-black uppercase tracking-wider px-2 py-0.5 text-white border-none text-center">Nilai</th>
                                    <th class="w-16 text-[12px] font-black uppercase tracking-wider text-center px-2 py-0.5 text-white border-none">Mutu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 flex flex-col flex-1">
                                @foreach ($unsurPelayanan as $i => $unsur)
                                    @php
                                        $nilaiUnsur = $nrrPerUnsur[$i] ?? 0;
                                        $mutuUnsur = $getMutuByNrr((float) $nilaiUnsur);
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors flex-1 flex items-center">
                                        <td class="w-12 text-center text-[11px] font-black px-2">{{ $i + 1 }}</td>
                                        <td class="flex-1 px-4 py-0.5 text-[11px] font-medium">{{ $unsur }}</td>
                                        <td class="w-20 px-2 py-0.5 text-[11px] font-black text-center">{{  sprintf('%0.3f', $nilaiUnsur) }}</td>
                                        <td class="w-16 px-2 py-0.5 text-center flex items-center justify-center">
                                            <span class="{{ $getMutuClass($mutuUnsur) }} text-white w-4 h-4 inline-flex items-center justify-center rounded-md text-[8px] font-black shadow-sm">
                                                {{ $mutuUnsur }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- #kolom3 --}}
            <div class="md:col-span-3 flex flex-col gap-4 md:min-h-0">
                <div class="p-6 card-elevated relative overflow-hidden bg-white md:h-36 h-auto py-5 shrink-0 flex flex-col justify-center border border-slate-100 rounded-[1vw]">
                    <div class="absolute -right-8 -top-8 w-48 h-48 bg-white rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="flex w-full justify-between items-end mb-3">
                            <p class="font-black uppercase tracking-[0.2em] text-gray-80  text-[12px]">TOTAL RESPONDEN</p>
                        
                            <span class="material-symbols-outlined text-gray-80 text-xl" style='font-variation-settings: "FILL" 1;'>groups</span>
                        </div>
                        <div class="flex items-center gap-5">
                            <h3 class="text-6xl font-black text-center text-[#FF8800]">{{ number_format($jmlResponden, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white card-elevated p-4 flex flex-col flex-1 border border-slate-100 min-h-0 rounded-[1vw]">
                    <h2 class="text-[11px] font-black mb-3 shrink-0 uppercase tracking-tight">Legenda</h2>
                    <div class="flex-1 space-y-3 overflow-hidden pl-3 pt-3">
                        <div class="flex items-center gap-4 group">
                            <div class="w-8 h-8 shrink-0 rounded-[1vw] bg-[#26C281] flex items-center justify-center text-white text-xs font-black shadow-sm group-hover:scale-110 transition-transform">A</div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-on-surface">Sangat Memuaskan</p>
                                <p class="text-[9px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">88,31 - 100,00</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 group">
                            <div class="w-8 h-8 shrink-0 rounded-[1vw] bg-[#3B82F6] flex items-center justify-center text-white text-xs font-black shadow-sm group-hover:scale-110 transition-transform">B</div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-on-surface">Memuaskan</p>
                                <p class="text-[9px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">76,61 - 88,30</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 group">
                            <div class="w-8 h-8 shrink-0 rounded-[1vw] bg-[#FACC15] flex items-center justify-center text-white text-xs font-black shadow-sm group-hover:scale-110 transition-transform">C</div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-on-surface">Kurang Memuaskan</p>
                                <p class="text-[9px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">65,00 - 76,60</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 group">
                            <div class="w-8 h-8 shrink-0 rounded-[1vw] bg-[#FF8800] flex items-center justify-center text-white text-xs font-black shadow-sm group-hover:scale-110 transition-transform">D</div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-on-surface">Tidak Memuaskan</p>
                                <p class="text-[9px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">25,00 - 64,99</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 group opacity-40">
                            <div class="w-8 h-8 shrink-0 rounded-[1vw] bg-[#F43F5E] flex items-center justify-center text-white text-xs font-black shadow-sm group-hover:scale-110 transition-transform">E</div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-on-surface">Gagal</p>
                                <p class="text-[9px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">0.00 - 24,99</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 pb-4 md:hidden bg-white/95 backdrop-blur-xl rounded-t-3xl shadow-2xl border-t border-slate-100">
        <a class="js-nav flex flex-col items-center justify-center bg-[#FF8800] text-white rounded-full p-4 transition-all scale-110 shadow-lg" href="#">
            <span class="material-symbols-outlined">grid_view</span>
        </a>
        <a class="js-nav flex flex-col items-center justify-center text-[#574335] p-3 hover:bg-slate-100 rounded-full transition-colors" href="/dashboard">
            <span class="material-symbols-outlined">assignment</span>
        </a>
        <a class="js-nav flex flex-col items-center justify-center text-[#574335] p-3 hover:bg-slate-100 rounded-full transition-colors" href="/skm#awal">
            <span class="material-symbols-outlined">database</span>
        </a>
        <a class="js-nav flex flex-col items-center justify-center text-[#574335] p-3 hover:bg-slate-100 rounded-full transition-colors" href="/login">
            <span class="material-symbols-outlined">person</span>
        </a>
    </nav>

@include('partials.filter-ui-script', [
    'enableLoadingOverlay' => true,
    'loadingOverlaySelector' => '#loadingOverlay',
    'loadingNavSelector' => '.js-nav',
])

<script>
    const data = @json($nilaiSkmTahun ?? []);
    const tahun = @json($yearOptions);
    const barsContainer = document.getElementById('yearlyBars');
    const safeData = Array.isArray(data) ? data : [];

    if (barsContainer) barsContainer.innerHTML = '';

    for (let i = 0; i < tahun.length; i++) {
        const value = Number(safeData[i] ?? 0);
        const normalizedValue = Math.max(Math.min(value, 100), 0);
        const height = Math.min(normalizedValue * 0.8, 120);
        const formattedValue = value.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Stack: value, bar, year label
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col items-center justify-end flex-1 min-w-0';

        const bar = document.createElement('div');
        bar.className = 'w-8 md:w-12 rounded-t-lg bg-[#FF8800] flex items-end justify-center shadow-md';
        bar.style.height = height + 'px';
        bar.title = formattedValue;

        const valueLabel = document.createElement('div');
        valueLabel.className = 'text-[12px] font-black text-[#FF8800] mb-1 truncate';
        valueLabel.innerText = formattedValue;

        const yearLabel = document.createElement('div');
        yearLabel.className = 'text-[11px] font-black text-center truncate max-w-[3.5rem] mt-1';
        yearLabel.innerText = tahun[i];

        wrapper.appendChild(bar);
        wrapper.appendChild(valueLabel);
        wrapper.appendChild(yearLabel);
        barsContainer.appendChild(wrapper);
    }
</script>

</body>

</html>








