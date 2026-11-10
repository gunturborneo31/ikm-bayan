<!doctype html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih — E-SKM PPM Bayan</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Manrope', sans-serif;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        body.is-visible { opacity: 1; }
        body.is-leaving { opacity: 0; }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 48;
        }

        @keyframes ping-slow {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.18); opacity: 0; }
        }
        .animate-ping-slow { animation: ping-slow 2s cubic-bezier(0.4,0,0.6,1) infinite; }

        @keyframes pop-in {
            0% { transform: scale(0.7); opacity: 0; }
            70% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pop-in { animation: pop-in 0.5s ease forwards; }

        @media (max-width: 480px) {
            .card-mobile { padding: 1.5rem !important; }
            .icon-mobile { width: 4rem !important; height: 4rem !important; }
            .icon-mobile .material-symbols-outlined { font-size: 2.5rem !important; }
            .ping-mobile { width: 5.5rem !important; height: 5.5rem !important; }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0] flex items-center justify-center px-4">

    {{-- Decorative blobs --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#ff8800]/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-[#ff8800]/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-[0_16px_48px_rgba(146,76,0,0.14)] p-6 md:p-8 flex flex-col items-center text-center gap-4 md:gap-6 animate-pop-in card-mobile">

            {{-- Icon --}}
            <div class="relative flex items-center justify-center">
                <span class="absolute inline-flex h-20 w-20 md:h-24 md:w-24 rounded-full bg-[#ff8800]/20 animate-ping-slow ping-mobile"></span>
                <div class="relative w-16 h-16 md:w-20 md:h-20 rounded-full bg-[#ff8800] flex items-center justify-center shadow-lg shadow-orange-300 icon-mobile">
                    <span class="material-symbols-outlined text-white text-4xl md:text-5xl">check_circle</span>
                </div>
            </div>

            {{-- Text --}}
            <div class="space-y-2">
                <h1 class="text-2xl font-extrabold text-[#191c1d] tracking-tight">Terima Kasih!</h1>
                <p class="text-sm font-semibold text-[#924c00] uppercase tracking-widest">Survei Berhasil Dikirim</p>
                <p class="text-sm text-slate-500 leading-relaxed mt-1">
                    Terima kasih sudah berpartisipasi dalam pengisian<br>
                    <span class="font-semibold text-slate-700">Survei Kepuasan Masyarakat</span>.
                </p>
            </div>

            {{-- Countdown bar --}}
            <div class="w-full bg-orange-100 rounded-full h-1.5 overflow-hidden">
                <div id="progressBar" class="h-full bg-[#ff8800] rounded-full transition-none" style="width:100%"></div>
            </div>
            <p class="text-xs text-slate-400 -mt-4">
                {{ !empty($responden) ? 'Survei selesai otomatis dalam' : 'Kembali ke halaman utama dalam' }}
                <span id="countdown" class="font-bold text-[#ff8800]">{{ !empty($responden) ? 30 : 5 }}</span> detik
            </p>

            @if (!empty($responden))
                {{-- Responden yang sama bisa menilai program lain tanpa mengisi ulang identitas --}}
                <a href="{{ url('/skm') }}" id="lanjut-btn"
                    class="w-full bg-[#ff8800] hover:bg-[#e07200] active:scale-95 text-white font-extrabold text-sm py-3 rounded-2xl shadow-md shadow-orange-200 transition-all duration-200">
                    Nilai Program Lain
                </a>
                <button id="ok-btn"
                    class="w-full -mt-2 bg-white border-2 border-[#ff8800] text-[#ff8800] hover:bg-orange-50 active:scale-95 font-extrabold text-sm py-3 rounded-2xl transition-all duration-200">
                    Selesai
                </button>
                <p class="text-xs text-slate-400 -mt-2">Sudah menilai {{ count($responden['dinilai'] ?? []) }} program sebagai {{ $responden['nama'] }}.</p>
            @else
                <button id="ok-btn"
                    class="w-full bg-[#ff8800] hover:bg-[#e07200] active:scale-95 text-white font-extrabold text-sm py-3 rounded-2xl shadow-md shadow-orange-200 transition-all duration-200">
                    Kembali Sekarang
                </button>
            @endif

            {{-- Footer note --}}
            <p class="text-[10px] text-slate-300 -mt-2">E-SKM PPM Bayan Group</p>
        </div>
    </div>

    <script>
        (function () {
            var targetUrl = "{{ !empty($responden) ? route('skm.selesai') : url('/') }}";
            var total = {{ !empty($responden) ? 30 : 5 }};
            document.getElementById('lanjut-btn')?.addEventListener('click', function () {
                clearInterval(timer);
            });
            var remaining = total;
            var countdown = document.getElementById('countdown');
            var progressBar = document.getElementById('progressBar');

            window.requestAnimationFrame(function () {
                document.body.classList.add('is-visible');
            });

            // Smooth progress bar shrink
            window.setTimeout(function () {
                progressBar.style.transition = 'width ' + total + 's linear';
                progressBar.style.width = '0%';
            }, 50);

            function goToStart() {
                document.body.classList.remove('is-visible');
                document.body.classList.add('is-leaving');
                window.setTimeout(function () {
                    window.location.href = targetUrl;
                }, 250);
            }

            var timer = setInterval(function () {
                remaining -= 1;
                if (countdown) {
                    countdown.textContent = String(Math.max(remaining, 0));
                }
                if (remaining <= 0) {
                    clearInterval(timer);
                    goToStart();
                }
            }, 1000);

            document.getElementById('ok-btn')?.addEventListener('click', function (e) {
                e.preventDefault();
                clearInterval(timer);
                goToStart();
            });
        })();
    </script>
</body>

</html>




