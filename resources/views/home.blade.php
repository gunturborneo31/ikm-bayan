<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>E-SKM PPM Bayan Group - Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
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
    </style>
</head>

<body class="bg-slate-100 text-on-surface h-screen overflow-hidden flex flex-col">
    <!-- TopNavBar - Centered Filters -->
    <nav class="shrink-0 bg-white shadow-sm z-50 px-8 py-2">
        <div class="max-w-full px-16 mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-2xl font-extrabold tracking-tighter text-[#FF8800]">7E-SKM PPM Bayan Group</span>
                <div class="h-8 w-px bg-slate-200 mx-6"></div>
            </div>
            <!-- Center Filters -->
            <div class="flex items-center gap-10">
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tahun</span>
                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100">
                        <span class="material-symbols-outlined text-[#FF8800] text-sm">calendar_today</span>
                        <select
                            class="border-none p-0 bg-transparent text-xs font-extrabold text-on-surface focus:ring-0 cursor-pointer">
                            <option selected="">2024</option>
                            <option>2023</option>
                            <option>2022</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Triwulan</span>
                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100">
                        <span class="material-symbols-outlined text-[#FF8800] text-sm">query_stats</span>
                        <select
                            class="border-none p-0 bg-transparent text-xs font-extrabold text-on-surface focus:ring-0 cursor-pointer">
                            <option selected="">Triwulan 1</option>
                            <option>Triwulan 2</option>
                            <option>Triwulan 3</option>
                            <option>Triwulan 4</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="https://www.bayan.com.sg/id" target="_blank" rel="noopener noreferrer"
                    class="bg-[#FF8800] text-white px-8 py-2.5 rounded-full font-extrabold text-sm hover:opacity-90 active:scale-95 duration-200">Website Resmi Bayan</a>
            </div>
        </div>
    </nav>
    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col p-4 gap-4 px-8 w-full overflow-hidden">
        <div class="flex-1 grid grid-cols-12 px-16 gap-4 min-h-0">
            <!-- Column 1: Core Metrics -->
            <div class="col-span-3 flex flex-col gap-4 overflow-hidden">
                <!-- SKM Score Card -->
                <div
                    class="pattern-bg p-8 flex flex-col items-center justify-center text-center shadow-lg h-[60%] relative overflow-hidden rounded-xl">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-24 -mt-24 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/5 rounded-full -ml-16 -mb-16 blur-2xl">
                    </div>
                    <span
                        class="material-symbols-outlined text-white/20 text-7xl absolute top-6 left-6 pointer-events-none"
                        style='font-variation-settings: "FILL" 1;'>analytics</span>
                    <span
                        class="relative z-10 text-white drop-shadow-xl text-[20px] font-extrabold uppercase tracking-[0.2em] mb-4 drop-shadow-sm text-sm">NILAI
                        SKM</span>
                    <h1 class="relative z-10 font-black text-white drop-shadow-md text-[80px] leading-none">85.45</h1>
                </div>
                <!-- Side-by-side Metrics -->
                <div class="grid grid-cols-2 gap-4 h-28 shrink-0">
                    <div
                        class="bg-white pattern-bg-light p-4 flex flex-col justify-center relative shadow-sm border border-slate-100 rounded-xl overflow-hidden">
                        <div class="flex justify-between items-start">
                            <p class="font-black text-slate-400 uppercase tracking-[0.15em] text-[10px]">KINERJA</p>
                            <span class="material-symbols-outlined text-[#FF8800] text-sm opacity-40">speed</span>
                        </div>
                        <p class="font-black text-[#FF8800] leading-tight uppercase text-xs text-center mt-3">SANGAT
                            MEMUASKAN</p>
                    </div>
                    <div
                        class="bg-white pattern-bg-light p-4 flex flex-col justify-center relative shadow-sm border border-slate-100 rounded-xl overflow-hidden items-center">
                        <div class="flex justify-between items-start w-full">
                            <p class="font-black text-slate-400 uppercase tracking-[0.15em] text-[10px]">MUTU</p>
                            <span class="material-symbols-outlined text-[#FF8800] text-sm opacity-40">verified</span>
                        </div>
                        <p class="font-black text-on-surface text-5xl mt-1">A</p>
                    </div>
                </div>
                <!-- Actions -->
                <div class="flex flex-col gap-3 shrink-0">
                    <button
                        class="w-full bg-[#FFF3E6] hover:bg-[#FFE8CC] transition-all py-3 rounded-xl flex items-center justify-center gap-2 text-[11px] font-black text-[#FF8800] border border-[#FFD9B3]">
                        <span class="material-symbols-outlined text-lg">group</span>
                        INFO RESPONDEN
                    </button>
                    <button
                        class="w-full bg-[#FF8800] text-white py-4 rounded-xl flex items-center justify-center gap-3 font-black text-sm shadow-lg hover:opacity-95 active:scale-95 transition-all group">
                        <span class="material-symbols-outlined text-xl"
                            style='font-variation-settings: "FILL" 1;'>description</span>
                        MULAI SURVEI
                    </button>
                </div>
            </div>
            <!-- Column 2: Data Visuals -->
            <div class="col-span-6 flex flex-col gap-4 min-h-0">
                <!-- Chart Area -->
                <div class="bg-white p-5 flex flex-col shadow-sm border border-slate-100 min-h-0 rounded-xl flex-1">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-sm font-black uppercase tracking-tight">Tren SKM Bulanan</h2>
                        <div
                            class="flex items-center gap-2 text-[10px] font-black text-on-surface-variant bg-slate-50 px-3 py-1 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-[#FF8800]"></span> INDEKS
                        </div>
                    </div>
                    <div class="flex-1 flex items-end justify-between gap-3 px-2 pb-2">
                        <!-- Bars -->
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[70%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">JAN</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[75%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">FEB</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[82%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">MAR</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[78%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">APR</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[85%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">MEI</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[92%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">JUN</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[72%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">JUL</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[88%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">AGU</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[90%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">SEP</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[94%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">OKT</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[86%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">NOV</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 w-full h-full justify-end group">
                            <div
                                class="w-full bg-[#FF8800]/90 group-hover:bg-[#FF8800] rounded-full h-[90%] transition-all duration-300">
                            </div>
                            <span class="text-[10px] font-black text-slate-400">DES</span>
                        </div>
                    </div>
                </div>
                <!-- Compact Table - Resized and Tighter -->
                <div
                    class="bg-white overflow-hidden flex flex-col h-[340px] shadow-sm border border-slate-100 rounded-xl shrink-0">
                    <div class="overflow-y-auto no-scrollbar">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-[#FF8800]">
                                    <th
                                        class="py-2.5 text-[9px] font-black uppercase tracking-widest px-4 text-white border-none">
                                        No</th>
                                    <th
                                        class="py-2.5 text-[9px] font-black uppercase tracking-widest px-4 text-white border-none">
                                        Unsur Pelayanan</th>
                                    <th
                                        class="py-2.5 text-[9px] font-black uppercase tracking-widest px-4 text-white border-none text-center">
                                        Nilai</th>
                                    <th
                                        class="py-2.5 text-[9px] font-black uppercase tracking-widest text-center px-4 text-white border-none">
                                        Mutu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">1</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Kesesuaian Program</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.50</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">2</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Kualitas Pelaksanaan</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.42</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">3</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Keberlanjutan</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.38</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#3B82F6] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">B</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">4</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Kejelasan Informasi</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.80</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">5</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Kompetensi Tim</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.45</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">6</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Kemudahan Akses</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.20</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#3B82F6] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">B</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">7</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Sikap dan Etika</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.92</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">8</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Dampak Sosial dan Ekonomi</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.60</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-2 px-4 text-[11px] font-black">9</td>
                                    <td class="py-2 px-4 text-[11px] font-medium">Kebermanfaatan</td>
                                    <td class="py-2 px-4 text-[11px] font-black text-center">3.75</td>
                                    <td class="py-2 px-4 text-center"><span
                                            class="bg-[#26C281] text-white w-5 h-5 inline-flex items-center justify-center rounded-lg text-[9px] font-black shadow-sm">A</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Column 3: Stats & Legend -->
            <div class="col-span-3 flex flex-col gap-4 min-h-0">
                <!-- Total Responden - Enlarged -->
                <div
                    class="p-8 shadow-md relative overflow-hidden bg-[#FF8800]/10 h-44 shrink-0 flex flex-col justify-center border border-[#FF8800]/20 rounded-xl">
                    <div class="absolute -right-8 -top-8 w-48 h-48 bg-[#FF8800]/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <p class="font-black uppercase tracking-[0.2em] text-[#FF8800] mb-3 text-[12px]">TOTAL RESPONDEN
                        </p>
                        <div class="flex items-center gap-5">
                            <span class="material-symbols-outlined text-[#FF8800] text-5xl"
                                style='font-variation-settings: "FILL" 1;'>groups</span>
                            <h3 class="text-6xl font-black text-[#FF8800]">1,248</h3>
                        </div>
                    </div>
                </div>
                <!-- Legend Card - Taller & Aligned -->
                <div class="bg-white p-6 flex flex-col flex-1 shadow-sm border border-slate-100 min-h-0 rounded-xl">
                    <h2 class="text-sm font-black mb-6 shrink-0 uppercase tracking-tight">Legenda Mutu</h2>
                    <div class="flex-1 space-y-6 overflow-y-auto no-scrollbar">
                        <div class="flex items-center gap-4 group">
                            <div
                                class="w-10 h-10 shrink-0 rounded-xl bg-[#26C281] flex items-center justify-center text-white text-sm font-black shadow-sm group-hover:scale-110 transition-transform">
                                A</div>
                            <div class="flex-1">
                                <p class="text-[12px] font-black text-on-surface">Sangat Memuaskan</p>
                                <p
                                    class="text-[10px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">
                                    88,31 - 100,00</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div
                                class="w-10 h-10 shrink-0 rounded-xl bg-[#3B82F6] flex items-center justify-center text-white text-sm font-black shadow-sm group-hover:scale-110 transition-transform">
                                B</div>
                            <div class="flex-1">
                                <p class="text-[12px] font-black text-on-surface">Memuaskan</p>
                                <p
                                    class="text-[10px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">
                                    76,61 - 88,30</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div
                                class="w-10 h-10 shrink-0 rounded-xl bg-[#FACC15] flex items-center justify-center text-white text-sm font-black shadow-sm group-hover:scale-110 transition-transform">
                                C</div>
                            <div class="flex-1">
                                <p class="text-[12px] font-black text-on-surface">Kurang Memuaskan</p>
                                <p
                                    class="text-[10px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">
                                    65,00 - 76,60</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div
                                class="w-10 h-10 shrink-0 rounded-xl bg-[#FF8800] flex items-center justify-center text-white text-sm font-black shadow-sm group-hover:scale-110 transition-transform">
                                D</div>
                            <div class="flex-1">
                                <p class="text-[12px] font-black text-on-surface">Tidak Memuaskan</p>
                                <p
                                    class="text-[10px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">
                                    25,00 - 64,99</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 group opacity-40">
                            <div
                                class="w-10 h-10 shrink-0 rounded-xl bg-[#F43F5E] flex items-center justify-center text-white text-sm font-black shadow-sm group-hover:scale-110 transition-transform">
                                E</div>
                            <div class="flex-1">
                                <p class="text-[12px] font-black text-on-surface">Gagal</p>
                                <p
                                    class="text-[10px] text-on-surface-variant font-bold opacity-70 uppercase tracking-tighter">
                                    0.00 - 24,99</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- BottomNavBar (Mobile Only) -->
    <nav
        class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 pb-4 md:hidden bg-white/95 backdrop-blur-xl rounded-t-3xl shadow-2xl border-t border-slate-100">
        <a class="flex flex-col items-center justify-center bg-[#FF8800] text-white rounded-full p-4 transition-all scale-110 shadow-lg"
            href="#">
            <span class="material-symbols-outlined">grid_view</span>
        </a>
        <a class="flex flex-col items-center justify-center text-[#574335] p-3 hover:bg-slate-100 rounded-full transition-colors"
            href="#">
            <span class="material-symbols-outlined">assignment</span>
        </a>
        <a class="flex flex-col items-center justify-center text-[#574335] p-3 hover:bg-slate-100 rounded-full transition-colors"
            href="#">
            <span class="material-symbols-outlined">database</span>
        </a>
        <a class="flex flex-col items-center justify-center text-[#574335] p-3 hover:bg-slate-100 rounded-full transition-colors"
            href="#">
            <span class="material-symbols-outlined">person</span>
        </a>
    </nav>
</body>

</html>



