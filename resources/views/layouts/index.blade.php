<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} - E-SKM</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script data-hid="gpr-kominfo" src="https://widget.kominfo.go.id/gpr-widget-kominfo.min.js" async
        onload="this.__vm_l=1"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primaryColor: '#FF8800',
                        brandGlow: '#F59E0B'
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Manrope', sans-serif;
            background:
                radial-gradient(circle at 5% 10%, rgba(251, 146, 60, 0.16), transparent 28%),
                radial-gradient(circle at 92% 85%, rgba(255, 136, 0, 0.15), transparent 30%),
                linear-gradient(180deg, #fff7ed 0%, #ffedd5 52%, #fffaf3 100%);
        }

        .card-elevated {
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09), 0 4px 14px rgba(15, 23, 42, 0.06);
        }

        .admin-shell {
            min-height: 100vh;
        }

        .admin-main {
            position: fixed;
            top: 0;
            left: 215px;
            width: calc(100% - 215px);
            height: 100vh;
            padding: 0.5rem;
            overflow: auto;
        }

        .admin-main > div {
            border: 1px solid #fed7aa;
            border-radius: 1rem;
            background:
                radial-gradient(circle at 100% 0%, rgba(251, 146, 60, 0.14), transparent 38%),
                linear-gradient(180deg, #fff7ed 0%, #fffaf5 100%);
            box-shadow: 0 12px 24px rgba(251, 146, 60, 0.16);
        }

        .admin-main > div > div {
            background: transparent !important;
        }

        .admin-main [style*="font-family:'Roboto'"],
        .admin-main [style*='font-family: "Roboto"'] {
            font-family: 'Manrope', sans-serif !important;
        }

        .admin-main input,
        .admin-main select,
        .admin-main button,
        .admin-main textarea {
            transition: all 0.16s ease;
        }

        .admin-main table {
            width: 100%;
            border-collapse: collapse;
            border-color: #fdba74;
        }

        .admin-main table th {
            background: #ffedd5;
            color: #7c2d12;
            font-weight: 800;
            border-color: #fdba74;
        }

        .admin-main table td {
            border-color: #fdba74;
            color: #9a3412;
            background: #fffaf5;
        }

        .admin-toolbar {
            padding-bottom: 0.45rem;
            border-bottom: 1px solid #fdba74;
            margin-bottom: 0.55rem;
        }

        .admin-toolbar > p {
            color: #7c2d12 !important;
            font-size: 1.18rem !important;
            font-weight: 900 !important;
            letter-spacing: 0.01em;
            margin-top: 0.1rem;
        }

        .admin-toolbar .flex.flex-row.items-center.gap-3,
        .admin-toolbar .flex.flex-row.items-center.gap-2 {
            flex-wrap: wrap;
            row-gap: 0.45rem;
        }

        .admin-main select,
        .admin-main input[type="text"],
        .admin-main input[type="number"],
        .admin-main textarea {
            border-color: #fdba74 !important;
            border-radius: 9999px;
            color: #7c2d12;
            font-weight: 700;
            background: #fffaf5;
        }

        .admin-main textarea {
            border-radius: 1rem;
        }

        .admin-main button,
        .admin-main a {
            letter-spacing: 0.01em;
        }

        .admin-main button:not(.no-theme) {
            border-color: #fb923c !important;
            background-color: #f97316;
            color: #ffffff !important;
            font-weight: 800;
        }

        .admin-main button:not(.no-theme):hover {
            background-color: #ea580c;
        }

        .admin-main .admin-toolbar a,
        .admin-main a[href*="/exports?"] {
            background: #fff7ed;
            color: #9a3412 !important;
            border: 1px solid #fdba74;
            border-radius: 9999px;
            font-weight: 800;
        }

        .admin-main .admin-toolbar a:hover,
        .admin-main a[href*="/exports?"]:hover {
            background: #ffedd5;
        }

        .admin-main a[href*="/exports?"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding-left: 0.9rem !important;
            padding-right: 0.9rem !important;
            border: 1px solid #fdba74;
            box-shadow: 0 2px 6px rgba(234, 88, 12, 0.18);
        }

        .admin-main .overflow-y-auto.h-\[510px\] {
            height: calc(100vh - 250px) !important;
            min-height: 360px;
            border-radius: 0.85rem;
            border: 1px solid #fed7aa;
            background: #fffaf5;
        }

        .admin-main table th,
        .admin-main table td {
            padding-top: 0.48rem;
            padding-bottom: 0.48rem;
            padding-left: 0.55rem;
            padding-right: 0.55rem;
        }

        .admin-main input:focus,
        .admin-main select:focus,
        .admin-main textarea:focus {
            box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.22);
            border-color: #fb923c !important;
            outline: none;
        }

        @media (max-width: 1024px) {
            .admin-main {
                top: 58px;
                left: 0;
                width: 100%;
                height: calc(100vh - 58px);
            }

            .admin-main .text-5xl,
            .admin-main .text-4xl {
                font-size: 0.95rem !important;
                line-height: 1.2rem !important;
            }

            .admin-main .w-\[300px\],
            .admin-main .w-\[250px\],
            .admin-main .w-\[200px\] {
                width: auto !important;
            }

            .admin-toolbar {
                align-items: stretch !important;
            }

            .admin-toolbar > p {
                width: 100%;
            }

            .admin-main .overflow-y-auto.h-\[510px\] {
                height: calc(100vh - 290px) !important;
                min-height: 300px;
            }

            .admin-main a[href*="/exports?"] {
                width: 100% !important;
            }
        }
    </style>
</head>

<body class="text-slate-900 h-full relative admin-shell">
    <div id="loadingOverlay" class="hidden fixed inset-0 z-[999] bg-white/70 backdrop-blur-sm items-center justify-center">
        <div class="bg-white border border-orange-200 shadow-lg rounded-full px-5 py-3 flex items-center gap-2 text-[12px] font-black text-[#EA580C]">
            <span class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
            Memuat data...
        </div>
    </div>

    @include('layouts.sidebar')
    @yield('content')

    <script>
        window.showAdminLoading = function() {
            const overlay = document.getElementById('loadingOverlay');
            if (!overlay) {
                return;
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        };

        // Sembunyikan overlay saat halaman dipulihkan dari bfcache (tombol Back)
        window.addEventListener('pageshow', function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        });

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('.js-admin-nav');
            if (trigger) {
                window.showAdminLoading();
            }
        });

        document.addEventListener('submit', function(event) {
            const form = event.target;
            if (form && !form.classList.contains('no-loading')) {
                window.showAdminLoading();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const dropdownTriggers = Array.from(document.querySelectorAll('[data-dropdown-toggle]'));

            if (!dropdownTriggers.length) {
                return;
            }

            const closeDropdown = function(trigger) {
                const targetId = trigger.getAttribute('data-dropdown-toggle');
                const menu = targetId ? document.getElementById(targetId) : null;

                if (!menu) {
                    return;
                }

                menu.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
            };

            const openDropdown = function(trigger) {
                const targetId = trigger.getAttribute('data-dropdown-toggle');
                const menu = targetId ? document.getElementById(targetId) : null;

                if (!menu) {
                    return;
                }

                dropdownTriggers.forEach(function(otherTrigger) {
                    if (otherTrigger !== trigger) {
                        closeDropdown(otherTrigger);
                    }
                });

                menu.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
            };

            dropdownTriggers.forEach(function(trigger) {
                const targetId = trigger.getAttribute('data-dropdown-toggle');
                const menu = targetId ? document.getElementById(targetId) : null;

                if (!menu) {
                    return;
                }

                trigger.setAttribute('aria-expanded', 'false');

                trigger.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (menu.classList.contains('hidden')) {
                        openDropdown(trigger);
                        return;
                    }

                    closeDropdown(trigger);
                });

                menu.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });

            document.addEventListener('click', function() {
                dropdownTriggers.forEach(closeDropdown);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') {
                    return;
                }

                dropdownTriggers.forEach(closeDropdown);
            });
        });

    </script>
</body>

</html>




