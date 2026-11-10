@extends('layouts.index', ['title' => 'Dashboard'])

@php
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
        ['key' => 'jenisResponden', 'title' => 'Jenis Responden', 'data' => $jenisRespondenData, 'colors' => ['#7C2D12', '#C2410C', '#F97316', '#FDBA74']],
        ['key' => 'jenkel', 'title' => 'Jenis Kelamin', 'data' => $jenkelData, 'colors' => ['#EA580C', '#FB923C']],
        ['key' => 'umur', 'title' => 'Rentang Umur', 'data' => $umurData, 'colors' => ['#C2410C', '#EA580C', '#F97316', '#FDBA74']],
        ['key' => 'pendidikan', 'title' => 'Tingkat Pendidikan', 'data' => $pendidikanData, 'colors' => ['#9A3412', '#C2410C', '#EA580C', '#F97316', '#FB923C', '#FDBA74']],
        ['key' => 'pekerjaan', 'title' => 'Jenis Pekerjaan', 'data' => $pekerjaanData, 'colors' => ['#7C2D12', '#9A3412', '#C2410C', '#EA580C', '#F97316', '#FDBA74']],
    ];

@endphp

@section('content')
    <style>
        @include('partials.filter-ui-styles', ['chipMinWidth' => '152px'])

        .dashboard-admin-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.35rem;
        }

        @media (min-width: 1280px) {
            .dashboard-admin-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <div class="admin-main">
        <div class="h-full w-full rounded-xl p-3 mb-2 overflow-auto">
            <div class="admin-toolbar flex w-full justify-between items-start lg:items-center gap-3 flex-wrap lg:flex-nowrap">
                <p class="text-[25px] font-black text-gray-800">Dashboard</p>
            </div>

            @include('partials.top-nav-filters', [
                'routeBase' => '/dashboardAdmin/create',
                'brandTitle' => 'E-SKM PPM Bayan Group',
                'subtitle' => 'Dashboard Responden',
                'showBagian' => true,
                'showHomeButton' => false,
                'showAdminButton' => false,
                'enableLoadingNav' => true,
                'navClass' => 'rounded-2xl bg-white/90 border border-orange-200 px-3 lg:px-5 py-2 mb-3',
                'containerClass' => 'max-w-full mx-auto flex items-center justify-between gap-3 flex-wrap lg:flex-nowrap',
                'filterWrapperClass' => 'flex items-center gap-2 lg:gap-4 flex-wrap',
            ])

            <div class="dashboard-admin-grid">
                @foreach ($sections as $section)
                    @include('partials.dashboard-section-pair', ['section' => $section])
                @endforeach
            </div>
        </div>
    </div>

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
@endsection
