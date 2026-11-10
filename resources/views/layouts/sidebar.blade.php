@php
    $i = '/' . request()->path();
    // Filter laporan tahunan (Tahun + Pilar) dibawa ke semua menu.
    $keterangan = $_GET['keterangan'] ?? session('keterangan', 'admin');
    $filterQs = 'jenkel=1&usia=1&pekerjaan=1&pendidikan=1&' . \App\Support\SurveyFilter::queryString(['keterangan' => $keterangan]);
@endphp

<aside class="fixed left-0 top-0 z-40 h-[58px] lg:h-screen w-full lg:w-[215px] p-2 lg:p-3 bg-gradient-to-r lg:bg-gradient-to-b from-[#EA580C] via-[#F97316] to-[#FB923C] border-b lg:border-b-0 lg:border-r border-orange-200/70 shadow-xl overflow-hidden lg:overflow-visible">
    <div class="h-full rounded-2xl lg:rounded-3xl bg-orange-950/15 backdrop-blur-sm border border-orange-100/35 px-2 lg:px-3 py-2 lg:py-4 flex flex-row lg:flex-col items-center lg:items-stretch gap-2 lg:gap-0">
        <a href="/" class="flex items-center gap-2 px-1 lg:px-2 js-admin-nav shrink-0">
            <div class="flex items-center -space-x-2">
                <img src="/assets/logo-bayan.png" class="h-10 w-10 p-1 rounded-xl bg-white border border-orange-100/80" alt="Logo Bayan">
            </div>
            <div class="text-white hidden sm:block">
                <p class="text-base font-extrabold tracking-tight">E-SKM</p>
                <p class="text-[10px] font-semibold text-white/90">Admin Panel</p>
            </div>
        </a>

        <nav class="mt-0 lg:mt-4 flex-1 hidden lg:block space-y-1.5 text-[12px] font-extrabold tracking-wide">
            <a href="/rekap-ppm?Tahun={{ \App\Support\SurveyFilter::year() }}"
                class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'rekap-ppm') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                REKAP PPM
            </a>
            <a href="/rekapTotal?{{ $filterQs }}"
                class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'rekapTotal') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                RESUME
            </a>
            <a href="/nilaiUnsur?{{ $filterQs }}"
                class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'nilaiUnsur') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                NILAI UNSUR
            </a>
            <a href="/nilaiRekap?{{ $filterQs }}"
                class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'nilaiRekap') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                NILAI
            </a>
            <a href="/dashboardAdmin/create?{{ $filterQs }}"
                class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'dashboard') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                DASHBOARD
            </a>
            <a href="/master-ppm"
                class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'master-ppm') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                MASTER DATA PPM
            </a>

            <a href="/admin/input-responden" class="block rounded-xl px-3 py-2.5 transition text-white hover:bg-orange-100/20">
                INPUT RESPONDEN
            </a>

            <a href="/admin/offline-skm" class="js-admin-nav block rounded-xl px-3 py-2.5 transition {{ str_contains($i, 'offline-skm') ? 'bg-white text-orange-900 shadow-md' : 'text-white hover:bg-orange-100/20' }}">
                IMPORT EXCEL
            </a>
        </nav>

        <div class="space-y-2 hidden lg:block">
            <a href="/" class="js-admin-nav block w-full rounded-xl bg-white/90 text-orange-800 text-center px-3 py-2.5 text-[11px] font-black hover:bg-white transition">
                HALAMAN DEPAN
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-orange-900 text-white px-3 py-2.5 text-[11px] font-black hover:bg-orange-950 transition">
                    KELUAR
                </button>
            </form>
        </div>
    </div>
</aside>




