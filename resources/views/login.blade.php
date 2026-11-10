<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin E-SKM</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#FF8800',
                        brandSoft: '#FFF1E3',
                        ember: '#EA580C'
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        .bg-atmosphere {
            background:
                radial-gradient(circle at 15% 20%, rgba(251, 146, 60, 0.24), transparent 36%),
                radial-gradient(circle at 85% 80%, rgba(234, 88, 12, 0.21), transparent 40%),
                linear-gradient(135deg, #fff7ed 0%, #ffedd5 52%, #fffaf3 100%);
        }
    </style>
</head>

<body class="bg-atmosphere min-h-screen text-slate-900 font-sans">
    <div id="loadingOverlay" class="hidden fixed inset-0 z-[999] bg-white/70 backdrop-blur-sm items-center justify-center">
        <div class="bg-white border border-slate-200 shadow-lg rounded-full px-5 py-3 flex items-center gap-2 text-[12px] font-black text-[#FF8800]">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.22" stroke-width="3"></circle>
                <path d="M22 12A10 10 0 0 0 12 2" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
            </svg>
            Memproses login...
        </div>
    </div>

    <header class="sticky top-0 z-20 bg-orange-50/90 backdrop-blur border-b border-orange-200">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="flex items-center -space-x-2">
                    <img src="/assets/logo-bayan.png" class="h-10 w-10 rounded-xl bg-white border border-orange-200 p-1" alt="Logo Bayan">
                </div>
                <div>
                    <p class="text-sm font-extrabold tracking-tight text-orange-900">E-SKM PPM Bayan Group</p>
                    <p class="text-[11px] font-bold text-orange-700">Panel Administrasi</p>
                </div>
            </a>
            <a href="/" class="rounded-full border border-orange-300 bg-orange-100 px-4 py-2 text-xs font-extrabold text-orange-800 hover:bg-orange-200 transition">Kembali ke Beranda</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 lg:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">
            <section class="rounded-3xl p-7 lg:p-10 shadow-xl border border-orange-200 bg-gradient-to-br from-[#EA580C] via-[#F97316] to-[#FDBA74] text-white relative overflow-hidden">
                <div class="absolute -right-14 -top-14 h-40 w-40 rounded-full bg-white/20 blur-2xl"></div>
                <div class="absolute -left-10 -bottom-10 h-36 w-36 rounded-full bg-slate-900/20 blur-2xl"></div>
                <div class="relative">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-white/80">Sistem Internal</p>
                    <h1 class="mt-2 text-3xl lg:text-4xl font-extrabold leading-tight">Monitoring SKM yang cepat, rapi, dan konsisten.</h1>
                    <p class="mt-4 text-sm lg:text-base font-semibold text-white/90 leading-relaxed">Gunakan akun admin untuk mengelola dashboard, rekap, dan visualisasi data survei dengan tema terpadu seperti halaman publik.</p>
                </div>
            </section>

            <section class="rounded-3xl p-7 lg:p-10 bg-white border border-orange-200 shadow-xl">
                <div class="mb-6">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-500">Login Admin</p>
                    <h2 class="mt-1 text-2xl font-extrabold text-orange-900">Masuk ke Sistem</h2>
                </div>

                @php
                    $errorBag = session('errors');
                @endphp
                @if ($errorBag && $errorBag->any())
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                        {{ $errorBag->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" id="loginForm" class="space-y-5" autocomplete="on">
                    @csrf
                    <div>
                        <label for="username" class="text-sm font-extrabold text-slate-700">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username"
                            class="mt-2 block w-full rounded-2xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-bold text-orange-900 placeholder:text-orange-400 focus:border-[#EA580C] focus:ring-[#EA580C]"
                            placeholder="Masukkan username" />
                    </div>

                    <div>
                        <label for="password" class="text-sm font-extrabold text-slate-700">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="mt-2 block w-full rounded-2xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-bold text-orange-900 placeholder:text-orange-400 focus:border-[#EA580C] focus:ring-[#EA580C]"
                            placeholder="Masukkan password" />
                    </div>

                    <button type="submit"
                        class="w-full rounded-2xl px-4 py-3 text-sm font-black uppercase tracking-[0.12em] text-white bg-gradient-to-r from-[#EA580C] to-[#FB923C] hover:brightness-95 transition">
                        Masuk
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script>
        (function() {
            const form = document.getElementById('loginForm');
            const overlay = document.getElementById('loadingOverlay');
            if (!form || !overlay) {
                return;
            }

            form.addEventListener('submit', function() {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            });

            // Sembunyikan overlay saat halaman dipulihkan dari bfcache (tombol Back)
            window.addEventListener('pageshow', function() {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            });
        })();
    </script>
</body>

</html>




