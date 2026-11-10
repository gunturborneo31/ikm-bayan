<?php

error_reporting(0);



// Filter tahunan (Tahun + Pilar). Satu responden dihitung sekali walau menilai beberapa program.
$baseQuery = \App\Support\SurveyFilter::respondenQuery();

$jenkelAgg = (clone $baseQuery)
    ->selectRaw("COALESCE(SUM(CASE WHEN jenkel = 'Laki - Laki' THEN 1 ELSE 0 END), 0) as laki_laki")
    ->selectRaw("COALESCE(SUM(CASE WHEN jenkel = 'Perempuan' THEN 1 ELSE 0 END), 0) as perempuan")
    ->first();

$umurAgg = (clone $baseQuery)
    ->selectRaw('COALESCE(SUM(CASE WHEN usia BETWEEN 0 AND 29 THEN 1 ELSE 0 END), 0) as usia_0_29')
    ->selectRaw('COALESCE(SUM(CASE WHEN usia BETWEEN 30 AND 40 THEN 1 ELSE 0 END), 0) as usia_30_40')
    ->selectRaw('COALESCE(SUM(CASE WHEN usia BETWEEN 41 AND 50 THEN 1 ELSE 0 END), 0) as usia_41_50')
    ->selectRaw('COALESCE(SUM(CASE WHEN usia BETWEEN 51 AND 100 THEN 1 ELSE 0 END), 0) as usia_51_100')
    ->first();

$pendidikanAgg = (clone $baseQuery)
    ->selectRaw("COALESCE(SUM(CASE WHEN pendidikan = 'SD' THEN 1 ELSE 0 END), 0) as sd")
    ->selectRaw("COALESCE(SUM(CASE WHEN pendidikan = 'SMP' THEN 1 ELSE 0 END), 0) as smp")
    ->selectRaw("COALESCE(SUM(CASE WHEN pendidikan = 'SMA / SMK' THEN 1 ELSE 0 END), 0) as sma")
    ->selectRaw("COALESCE(SUM(CASE WHEN pendidikan = 'D-I / D-III' THEN 1 ELSE 0 END), 0) as d3")
    ->selectRaw("COALESCE(SUM(CASE WHEN pendidikan = 'S1 / Setara' THEN 1 ELSE 0 END), 0) as s1")
    ->selectRaw("COALESCE(SUM(CASE WHEN pendidikan = 'S2 / S3' THEN 1 ELSE 0 END), 0) as s2")
    ->first();

$pekerjaanAgg = (clone $baseQuery)
    ->selectRaw("COALESCE(SUM(CASE WHEN pekerjaan = 'ASN' THEN 1 ELSE 0 END), 0) as asn")
    ->selectRaw("COALESCE(SUM(CASE WHEN pekerjaan = 'TNI / POLRI' THEN 1 ELSE 0 END), 0) as tni_polri")
    ->selectRaw("COALESCE(SUM(CASE WHEN pekerjaan = 'Swasta' THEN 1 ELSE 0 END), 0) as swasta")
    ->selectRaw("COALESCE(SUM(CASE WHEN pekerjaan = 'Pengusaha' THEN 1 ELSE 0 END), 0) as pengusaha")
    ->selectRaw("COALESCE(SUM(CASE WHEN pekerjaan = 'Pelajar' THEN 1 ELSE 0 END), 0) as pelajar")
    ->selectRaw("COALESCE(SUM(CASE WHEN pekerjaan = 'Lainnya' THEN 1 ELSE 0 END), 0) as lainnya")
    ->first();

$jenkelData = [
    ['label' => 'Laki - Laki', 'total' => (int) ($jenkelAgg->laki_laki ?? 0)],
    ['label' => 'Perempuan', 'total' => (int) ($jenkelAgg->perempuan ?? 0)],
];

$umurData = [
    ['label' => 'Kurang dari 30 Tahun', 'total' => (int) ($umurAgg->usia_0_29 ?? 0)],
    ['label' => '30 s/d 40 Tahun', 'total' => (int) ($umurAgg->usia_30_40 ?? 0)],
    ['label' => '41 s/d 50 Tahun', 'total' => (int) ($umurAgg->usia_41_50 ?? 0)],
    ['label' => 'Lebih dari 50 Tahun', 'total' => (int) ($umurAgg->usia_51_100 ?? 0)],
];

$pendidikanData = [
    ['label' => 'SD', 'total' => (int) ($pendidikanAgg->sd ?? 0)],
    ['label' => 'SMP', 'total' => (int) ($pendidikanAgg->smp ?? 0)],
    ['label' => 'SMA / SMK', 'total' => (int) ($pendidikanAgg->sma ?? 0)],
    ['label' => 'D-I / D-III', 'total' => (int) ($pendidikanAgg->d3 ?? 0)],
    ['label' => 'S1 / Setara', 'total' => (int) ($pendidikanAgg->s1 ?? 0)],
    ['label' => 'S2 / S3', 'total' => (int) ($pendidikanAgg->s2 ?? 0)],
];

$pekerjaanData = [
    ['label' => 'ASN', 'total' => (int) ($pekerjaanAgg->asn ?? 0)],
    ['label' => 'TNI / POLRI', 'total' => (int) ($pekerjaanAgg->tni_polri ?? 0)],
    ['label' => 'Swasta', 'total' => (int) ($pekerjaanAgg->swasta ?? 0)],
    ['label' => 'Pengusaha', 'total' => (int) ($pekerjaanAgg->pengusaha ?? 0)],
    ['label' => 'Pelajar', 'total' => (int) ($pekerjaanAgg->pelajar ?? 0)],
    ['label' => 'Lainnya', 'total' => (int) ($pekerjaanAgg->lainnya ?? 0)],
];

$jenisRespondenData = \App\Support\SurveyFilter::jenisRespondenData($baseQuery);

$sections = [
    ['key' => 'jenisResponden', 'title' => 'Jenis Responden', 'data' => $jenisRespondenData, 'colors' => ['#EA580C', '#2563EB', '#059669', '#7C3AED']],
    ['key' => 'jenkel', 'title' => 'Jenis Kelamin', 'data' => $jenkelData, 'colors' => ['#1D4ED8', '#DC2626']],
    ['key' => 'umur', 'title' => 'Rentang Umur', 'data' => $umurData, 'colors' => ['#2563EB', '#EA580C', '#059669', '#7C3AED']],
    ['key' => 'pendidikan', 'title' => 'Tingkat Pendidikan', 'data' => $pendidikanData, 'colors' => ['#1D4ED8', '#DC2626', '#059669', '#7C3AED', '#F59E0B', '#0891B2']],
    ['key' => 'pekerjaan', 'title' => 'Jenis Pekerjaan', 'data' => $pekerjaanData, 'colors' => ['#0EA5E9', '#EF4444', '#22C55E', '#A855F7', '#F97316', '#14B8A6']],
];

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>E-SKM PPM Bayan Group - Dashboard Responden</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#ff8800",
                    },
                    borderRadius: {
                        DEFAULT: "1.5rem",
                        lg: "2rem",
                        xl: "2.5rem",
                        full: "9999px"
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Manrope"],
                        label: ["Manrope"]
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

        .bagian-menu {
            width: 270px;
            padding: 0.65rem;
        }

@include('partials.filter-ui-styles', ['chipMinWidth' => '152px'])
    </style>
</head>

<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col">
    <div id="loadingOverlay" class="hidden fixed inset-0 z-[999] bg-white/70 backdrop-blur-sm items-center justify-center">
        <div class="bg-white border border-slate-200 shadow-lg rounded-full px-5 py-3 flex items-center gap-2 text-[12px] font-black text-[#FF8800]">
            <span class="material-symbols-outlined animate-spin">progress_activity</span>
            Memuat data...
        </div>
    </div>

@include('partials.top-nav-filters', [
    'routeBase' => '/dashboard',
    'brandTitle' => 'E-SKM PPM Bayan Group',
    'showBagian' => true,
    'showHomeButton' => true,
    'showAdminButton' => false,
    'enableLoadingNav' => true,
    'navClass' => 'shrink-0 sticky top-0 bg-white shadow-sm z-50 px-4 lg:px-8 py-1.5',
    'containerClass' => 'max-w-full px-2 lg:px-16 mx-auto flex items-center justify-between gap-3 flex-wrap lg:flex-nowrap',
    'filterWrapperClass' => 'flex items-center gap-2 lg:gap-4 flex-nowrap',
    'brandTitleClass' => 'text-xs font-extrabold tracking-tight text-[#FF8800]',
])

    <main class="flex-1 px-4 lg:px-8 py-4 overflow-auto">
        <div class="px-2 lg:px-16 grid grid-cols-1 xl:grid-cols-2 gap-1">
            @foreach ($sections as $section)
                @include('partials.dashboard-section-pair', ['section' => $section])
            @endforeach
        </div>
    </main>

@include('partials.filter-ui-script', [
    'enableLoadingOverlay' => true,
    'loadingOverlaySelector' => '#loadingOverlay',
    'loadingNavSelector' => '.js-nav',
])

    <script>

        const makePieChart = (elementId, labels, series, colors) => {
            const el = document.getElementById(elementId);
            if (!el || typeof ApexCharts === 'undefined') {
                return;
            }

            const options = {
                series,
                labels,
                colors,
                chart: {
                    height: 218,
                    width: '100%',
                    type: 'pie',
                },
                stroke: {
                    colors: ['#ffffff'],
                },
                plotOptions: {
                    pie: {
                        dataLabels: {
                            offset: -15,
                        },
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter(value) {
                        return `${value.toFixed(1)}%`;
                    },
                    style: {
                        fontFamily: 'Manrope, sans-serif',
                        fontWeight: 700,
                    },
                },
                legend: {
                    position: 'bottom',
                    fontFamily: 'Manrope, sans-serif',
                    fontSize: '12px',
                },
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        };

        @foreach ($sections as $section)
            makePieChart(
                '{{ $section['key'] }}',
                @json(array_column($section['data'], 'label')),
                @json(array_column($section['data'], 'total')),
                @json($section['colors'])
            );
        @endforeach
    </script>
</body>

</html>




