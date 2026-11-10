<!doctype html>
<html class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF8800',
                        'primary-deep': '#924C00',
                        'surface-soft': '#F8F9FA',
                        'surface-card': '#FFFFFF',
                        'surface-muted': '#F3F4F5',
                        'text-soft': '#574335'
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif']
                    },
                    borderRadius: {
                        card: '2rem'
                    }
                }
            }
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        body {
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: hidden;
            color: #191c1d;
            background: linear-gradient(135deg, #ff8800 0%, #ff9e3d 52%, #ffb366 100%);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1.2px, transparent 1.2px);
            background-size: 32px 32px;
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .survey-shell {
            position: relative;
            z-index: 1;
        }

        .survey-header,
        .survey-footer {
            position: fixed;
            left: 0;
            right: 0;
            z-index: 40;
            pointer-events: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .survey-header {
            top: 0;
            padding-top: 0.5rem;
            padding-bottom: 0.2rem;
        }

        .survey-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        .survey-header-inner,
        .survey-footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1rem;
        }

        .survey-brand,
        .survey-progress {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 9999px;
            border: 1px solid #f3d9bf;
            background: #ffffff;
            backdrop-filter: none;
            padding: 0.35rem 0.55rem;
            color: #6f3800;
            box-shadow: 0 10px 24px rgba(146, 76, 0, 0.14);
        }

        .survey-brand p,
        .survey-progress p,
        .survey-brand .material-symbols-outlined,
        .survey-progress .material-symbols-outlined {
            color: #6f3800 !important;
        }

        .survey-brand img {
            width: 2.15rem !important;
            height: 2.15rem !important;
        }

        .survey-progress-dots {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .survey-progress-dots span {
            display: block;
            width: 0.35rem;
            height: 0.35rem;
            border-radius: 9999px;
            background: #deb58c;
            transition: all 0.2s ease;
        }

        .survey-progress-dots span.is-active {
            width: 1.1rem;
            background: #ff8800;
        }

        .survey-progress-copy p:first-child {
            font-size: 9px !important;
            letter-spacing: 0.14em !important;
        }

        .survey-progress-copy p:last-child {
            font-size: 11px !important;
        }

        .survey-track {
            position: relative;
            z-index: 10;
            height: 100dvh;
            display: block;
            overflow: hidden;
            scroll-snap-type: none;
            scroll-behavior: smooth;
            touch-action: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .survey-track::-webkit-scrollbar {
            display: none;
        }

        .survey-track > div[id] {
            min-width: 100%;
            min-height: 100dvh;
            height: 100dvh;
            padding: 3.6rem 0.9rem 3.2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            scroll-snap-align: none;
            background: transparent !important;
            box-shadow: none !important;
        }

        .survey-track > div[id] > div:first-child {
            width: min(1320px, 100%);
            height: auto;
            max-height: calc(100dvh - 7.2rem);
            overflow: hidden;
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.55);
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 28px 70px rgba(146, 76, 0, 0.16);
            padding: 1.25rem;
            scrollbar-width: thin;
            scrollbar-color: #ffb781 transparent;
            margin: 0 auto;
        }

        .survey-track > div[id] > div:first-child > div {
            height: auto;
            min-height: 100%;
        }

        .survey-track > div[id] > div:first-child::-webkit-scrollbar {
            width: 8px;
        }

        .survey-track > div[id] > div:first-child::-webkit-scrollbar-thumb {
            background: #ffb781;
            border-radius: 9999px;
        }

        #awal > div:first-child,
        #jenisProgram > div:first-child,
        #identitas > div:first-child,
        #saran > div:first-child {
            max-width: 1100px;
        }

        #awal {
            align-items: center !important;
            padding-top: 0.6rem !important;
        }

        #awal > div:first-child {
            overflow: visible !important;
        }

        #awal > div:first-child > div {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: visible;
            padding-top: 0.8rem !important;
            padding-bottom: 0.55rem !important;
        }

        #awal > div:first-child > div .step-actions {
            margin-top: auto !important;
            position: static;
            background: transparent;
            border-radius: 0.9rem;
            z-index: 3;
        }

        #awal .step-actions {
            justify-content: flex-end !important;
        }

        #kedua > div:first-child,
        #satu > div:first-child,
        #dua > div:first-child,
        #tiga > div:first-child,
        #empat > div:first-child,
        #lima > div:first-child,
        #enam > div:first-child,
        #tujuh > div:first-child,
        #delapan > div:first-child,
        #sembilan > div:first-child {
            display: grid !important;
            grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
            gap: 0.75rem;
            align-items: start;
        }

        #kedua > div:first-child {
            grid-template-rows: minmax(0, 1fr) auto;
        }

        #kedua > div:first-child > .step-actions {
            align-self: end;
        }

        #kedua .w-1\/6,
        #satu .w-1\/6,
        #dua .w-1\/6,
        #tiga .w-1\/6,
        #empat .w-1\/6,
        #lima .w-1\/6,
        #enam .w-1\/6,
        #tujuh .w-1\/6,
        #delapan .w-1\/6,
        #sembilan .w-1\/6 {
            display: none !important;
        }

        #awal label[class*='max-w-md'],
        #jenisProgram label[class*='max-w-md'],
        #kedua h1,
        .survey-track h2 {
            color: #924c00 !important;
            text-shadow: none !important;
            line-height: 1.1;
        }

        #awal label[class*='max-w-md'],
        #jenisProgram label[class*='max-w-md'] {
            display: block;
            max-width: none;
            margin-bottom: 1.1rem;
            font-size: clamp(1.15rem, 1.9vw, 2.3rem) !important;
            font-weight: 800;
            letter-spacing: -0.03em;
            white-space: normal;
        }

        .survey-track h2 {
            font-size: clamp(1.08rem, 1.45vw, 1.52rem) !important;
            font-weight: 800;
            margin-bottom: 0.55rem;
            line-height: 1.35;
            letter-spacing: -0.01em;
        }

        .survey-track .text-4xl,
        .survey-track .text-5xl {
            font-size: clamp(1.02rem, 1.25vw, 1.22rem) !important;
            line-height: 1.35 !important;
        }

        .survey-track .lg\:text-2xl,
        .survey-track .lg\:text-xl,
        .survey-track .lg\:text-lg,
        .survey-track .text-xl {
            font-size: clamp(0.95rem, 1.08vw, 1.08rem) !important;
        }

        .survey-track .text-gray-900,
        .survey-track .text-gray-800 {
            color: #191c1d !important;
        }

        .survey-track .text-red-600 {
            color: #ba1a1a !important;
        }

        .survey-track .lg\:py-32,
        .survey-track .py-12,
        .survey-track .pt-16 {
            padding-top: 0.8rem !important;
            padding-bottom: 0.8rem !important;
        }

        .survey-track .p-5 {
            padding: 0.42rem !important;
        }

        .survey-track .mb-10 {
            margin-bottom: 0.8rem !important;
        }

        .survey-track .mb-6 {
            margin-bottom: 0.7rem !important;
        }

        .survey-track .gap-2 {
            gap: 0.35rem !important;
        }

        .survey-track ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .survey-track input[type='text'],
        .survey-track textarea {
            width: 100%;
            border: 2px solid #e7e8e9 !important;
            background: #ffffff !important;
            color: #191c1d !important;
            border-radius: 1.5rem !important;
            padding: 1rem 1.25rem !important;
            font-size: 1rem !important;
            font-weight: 700;
            box-shadow: none !important;
        }

        .survey-track textarea {
            min-height: 8rem;
            resize: none;
        }

        .survey-track input[type='text']:focus,
        .survey-track textarea:focus {
            border-color: #ff8800 !important;
            box-shadow: 0 0 0 4px rgba(255, 136, 0, 0.14) !important;
            outline: none;
        }

        .survey-track input.peer + label {
            width: 100%;
            align-items: center !important;
            gap: 0.75rem;
            border: 2px solid transparent !important;
            border-radius: 1.5rem !important;
            background: #f3f4f5 !important;
            color: #574335 !important;
            padding: 0.72rem 0.9rem !important;
            box-shadow: none !important;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
            position: relative;
            overflow: hidden;
        }

        .survey-track input.peer + label::before {
            content: '';
            width: 0.78rem;
            height: 0.78rem;
            min-width: 0.78rem;
            border-radius: 9999px;
            border: 2px solid #d7b596;
            background: #ffffff;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.55);
        }

        .survey-track input.peer + label .font-semibold {
            font-size: clamp(0.95rem, 1.08vw, 1.12rem) !important;
            line-height: 1.35 !important;
            letter-spacing: 0.005em;
        }

        .survey-track .w-8.h-8 {
            width: 1.2rem !important;
            height: 1.2rem !important;
        }

        .survey-track input.peer + label:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(146, 76, 0, 0.08);
            border-color: #ffb781 !important;
            background: #fff7f0 !important;
        }

        .survey-track input.peer:checked + label {
            background: linear-gradient(135deg, #ff8800 0%, #ff9e3d 100%) !important;
            border-color: #ff8800 !important;
            color: #ffffff !important;
            box-shadow: 0 18px 30px rgba(255, 136, 0, 0.22);
            transform: translateY(-1px);
        }

        .survey-track input.peer:checked + label::before {
            border-color: rgba(255, 255, 255, 0.9);
            background: #ffffff;
            box-shadow: inset 0 0 0 4px #ff8800;
        }

        .survey-track input.peer:checked + label * {
            color: #ffffff !important;
        }

        #awal #bagianRadios {
            gap: 0.5rem !important;
        }

        #awal #bagianRadios li label {
            min-height: 3rem;
            border: 1px solid #ffd7ad !important;
            background: #ffffff !important;
            color: #191c1d !important;
        }

        #awal #bagianRadios li label .font-semibold {
            font-size: clamp(0.9rem, 1.05vw, 1.05rem) !important;
            line-height: 1.3 !important;
            color: #6f3800 !important;
            font-weight: 700 !important;
        }

        #awal #bagianRadios input.peer:checked + label {
            background: linear-gradient(135deg, #ff8800 0%, #ff9e3d 100%) !important;
            border-color: #ff8800 !important;
            color: #ffffff !important;
        }

        #awal #bagianRadios input.peer:checked + label * {
            color: #ffffff !important;
        }

        .survey-track .step-actions,
        .survey-track li.flex.justify-between.mt-5,
        .survey-track li.col-span-2.flex.justify-between.mt-5.w-full {
            margin-top: 0.75rem !important;
            padding-top: 0.55rem;
            border-top: 1px solid #e7e8e9;
        }

        .survey-track a[onclick*="window.location='#"],
        .survey-track a[onclick="lanjutKeBiodata()"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            min-height: 2.35rem;
            min-width: 7.5rem;
            padding: 0.45rem 1rem;
            border-radius: 9999px;
            border: 1px solid #dec1ae;
            background: #ffffff;
            color: #6f3800 !important;
            font-weight: 800;
            letter-spacing: 0.08em;
            box-shadow: 0 10px 24px rgba(146, 76, 0, 0.08);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .survey-track a[onclick*="window.location='#"]:hover,
        .survey-track a[onclick="lanjutKeBiodata()"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(146, 76, 0, 0.14);
        }

        .survey-track a[onclick*="window.location='#"]:last-child,
        .survey-track a[onclick="lanjutKeBiodata()"] {
            background: linear-gradient(135deg, #ff8800 0%, #ff9e3d 100%);
            border-color: #ff8800;
            color: #ffffff !important;
        }

        .survey-track a[onclick*="window.location='#"] label,
        .survey-track a[onclick="lanjutKeBiodata()"] label,
        .survey-track a[onclick*="window.location='#"] svg,
        .survey-track a[onclick="lanjutKeBiodata()"] svg {
            color: inherit !important;
        }

        .survey-track button[type='submit'],
        #ok-btn {
            min-height: 2.5rem;
            padding-left: 1.3rem;
            padding-right: 1.3rem;
            border: 1px solid #ff8800 !important;
            border-radius: 9999px !important;
            background: linear-gradient(135deg, #ff8800 0%, #ff9e3d 100%) !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            letter-spacing: 0.12em;
            box-shadow: 0 16px 32px rgba(255, 136, 0, 0.22);
        }

        #satu h2,
        #dua h2,
        #tiga h2,
        #empat h2,
        #lima h2,
        #enam h2,
        #tujuh h2,
        #delapan h2,
        #sembilan h2 {
            padding: 1rem 1.05rem;
            border-radius: 1.35rem;
            border: 1px solid #ffd8b5;
            background: linear-gradient(180deg, #fff7f0 0%, #ffffff 100%);
            box-shadow: inset 0 0 0 1px rgba(255, 136, 0, 0.04);
        }

        #popup {
            inset: 0;
            background: rgba(25, 28, 29, 0.35) !important;
            backdrop-filter: blur(10px);
        }

        #popup #modal > div {
            max-width: 860px;
            border: 1px solid rgba(255, 255, 255, 0.55) !important;
            border-radius: 2rem !important;
            background: rgba(255, 255, 255, 0.96) !important;
            box-shadow: 0 30px 80px rgba(25, 28, 29, 0.2);
            padding: 2rem !important;
        }

        #popup h3 {
            color: #924c00 !important;
            font-size: clamp(1.05rem, 1.7vw, 1.6rem) !important;
            font-weight: 800 !important;
            line-height: 1.05 !important;
            letter-spacing: -0.03em;
        }

        #popup .text-4xl,
        #popup .lg\:text-2xl {
            font-size: clamp(0.95rem, 1.25vw, 1.15rem) !important;
            line-height: 1.35 !important;
        }

        #popup .lg\:text-sm,
        #popup .lg\:text-base {
            font-size: 0.88rem !important;
        }

        #popup table {
            width: 100%;
            border-spacing: 0 1rem;
        }

        #popup td:last-child {
            color: #574335;
            line-height: 1.7;
        }

        #jenisContainer > div {
            border-radius: 1.5rem;
            border: 1px solid #dec1ae !important;
            background: #fff7f0 !important;
            color: #6f3800 !important;
            padding: 1rem 1.1rem !important;
        }

        @media (max-width: 1024px) {
            .survey-header,
            .survey-footer {
                padding-left: 0.55rem;
                padding-right: 0.55rem;
            }

            .survey-header-inner,
            .survey-footer-inner {
                gap: 0.45rem;
            }

            .survey-track > div[id] {
                min-height: 100dvh;
                height: 100dvh;
                padding: 3.4rem 0.4rem 3rem;
                align-items: center;
            }

            .survey-track > div[id] > div:first-child {
                height: auto;
                max-height: calc(100dvh - 6.6rem);
                padding: 0.8rem;
            }

            #kedua > div:first-child,
            #satu > div:first-child,
            #dua > div:first-child,
            #tiga > div:first-child,
            #empat > div:first-child,
            #lima > div:first-child,
            #enam > div:first-child,
            #tujuh > div:first-child,
            #delapan > div:first-child,
            #sembilan > div:first-child {
                grid-template-columns: 1fr;
            }

            .survey-progress-copy {
                display: none;
            }

            #awal #bagianRadios li label .font-semibold {
                font-size: 0.95rem !important;
            }

            #awal > div:first-child > div {
                padding-top: 0.55rem !important;
                padding-bottom: 0.4rem !important;
            }

            #awal {
                align-items: center !important;
            }

            #awal > div:first-child > div .step-actions {
                background: rgba(255, 255, 255, 0.96);
            }

            .survey-track .grid-cols-2,
            .survey-track .lg\:grid-cols-2,
            .survey-track .lg\:grid-cols-5,
            .survey-track .grid-cols-5,
            .survey-track .grid-cols-6,
            .survey-track .grid-cols-4 {
                grid-template-columns: 1fr !important;
            }

            .survey-track .step-actions,
            .survey-track li.flex.justify-between.mt-5,
            .survey-track li.col-span-2.flex.justify-between.mt-5.w-full {
                flex-direction: column;
                gap: 0.8rem;
            }

            .survey-track a[onclick*="window.location='#"],
            .survey-track a[onclick="lanjutKeBiodata()"],
            .survey-track button[type='submit'] {
                width: 100%;
            }
        }

        .biodataIdentity {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem;
            margin-bottom: 0.65rem;
        }

        .biodataIdentity--single {
            grid-template-columns: 1fr;
        }

        .biodataIdentity label {
            display: block;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #924c00;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
        }

        #identitas .identity-copy {
            margin-bottom: 0.9rem;
        }

        #identitas .identity-copy p {
            color: #7a4a19;
            font-size: 0.92rem;
            line-height: 1.45;
            margin-top: 0.25rem;
        }

        #identitas .identity-radio-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem;
        }

        #identitas textarea {
            min-height: 6.8rem;
        }

        @media (max-width: 1024px) {
            .biodataIdentity,
            #identitas .identity-radio-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ─── Mobile: 9 question steps ─────────────────────────── */
        @media (max-width: 640px) {
            /* Tighten outer section vertical padding so card has more room */
            .survey-track > div[id] {
                padding: 2.8rem 0.35rem 2.5rem !important;
            }

            /* Expand card max-height to match tighter outer padding */
            .survey-track > div[id] > div:first-child {
                max-height: calc(100dvh - 5.5rem) !important;
                padding: 0.85rem !important;
            }

            /* Reduce gap between question-card and options panel */
            :is(#satu, #dua, #tiga, #empat, #lima, #enam, #tujuh, #delapan, #sembilan) > div:first-child {
                gap: 0.45rem !important;
            }

            /* Reduce inner question card padding */
            :is(#satu, #dua, #tiga, #empat, #lima, #enam, #tujuh, #delapan, #sembilan) > div:first-child > div:first-child {
                padding: 0.65rem !important;
                border-radius: 1rem !important;
            }

            /* Shrink icon box */
            :is(#satu, #dua, #tiga, #empat, #lima, #enam, #tujuh, #delapan, #sembilan) > div:first-child > div:first-child > div:first-child {
                width: 1.85rem !important;
                height: 1.85rem !important;
                border-radius: 0.45rem !important;
                margin-bottom: 0.3rem !important;
            }

            :is(#satu, #dua, #tiga, #empat, #lima, #enam, #tujuh, #delapan, #sembilan) > div:first-child > div:first-child > div:first-child .material-symbols-outlined {
                font-size: 1.15rem !important;
            }

            /* Reduce h2 extra padding */
            :is(#satu, #dua, #tiga, #empat, #lima, #enam, #tujuh, #delapan, #sembilan) h2 {
                padding: 0.55rem 0.7rem !important;
                font-size: 0.9rem !important;
            }

            /* Reduce gap between individual option cards */
            :is(#satu, #dua, #tiga, #empat, #lima, #enam, #tujuh, #delapan, #sembilan) > div:first-child > div:last-child {
                gap: 0.3rem !important;
            }

            /* Option label: tighter vertical padding */
            .survey-track input.peer + label {
                padding: 0.48rem 0.7rem !important;
            }

            /* Step-actions: keep horizontal row to save vertical space */
            .survey-track .step-actions {
                flex-direction: row !important;
                gap: 0.4rem !important;
                margin-top: 0.4rem !important;
                padding-top: 0.3rem !important;
            }

            /* Both nav buttons: equal-width halves */
            .survey-track a[onclick*="window.location='#"],
            .survey-track a[onclick="lanjutKeBiodata()"],
            .survey-track button[type='submit'] {
                width: auto !important;
                flex: 1 1 0 !important;
                min-width: 0 !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                justify-content: center !important;
                font-size: 0.82rem !important;
            }

            /* Header/footer: tighter on small screens */
            .survey-header { padding-top: 0.3rem !important; padding-bottom: 0.15rem !important; }
            .survey-footer { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
            .survey-brand img { width: 1.65rem !important; height: 1.65rem !important; }
            .survey-brand > div > p:first-child { display: none !important; }
        }
    </style>
</head>

<?php
$error = 'max-w-md  font-bold tracking-tight mb-2 text-red-600 lg:text-sm text-xl drop-shadow-[0_5px_5px_rgba(0,0,0,0.2)]';
$desaList = $catalog['desa'] ?? [];
$isLanjutan = !empty($responden);
$radioCard = 'inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer peer-checked:bg-gray-800 h-full peer-checked:text-white hover:text-gray-900 hover:bg-gray-400';
$arrowNext = 'M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z';
$arrowPrev = 'M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z';
?>

<style>
    /* Daftar pilihan yang bisa panjang (program, rincian) tetap bisa digulir di dalam kartu */
    .ppm-scroll { max-height: calc(100dvh - 19rem); overflow-y: auto; padding-right: 4px; scrollbar-width: thin; scrollbar-color: #ffb781 transparent; }
    .ppm-pilar-title { font-size: 11px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: #924c00; margin: 14px 0 6px; }
    .ppm-pilar-title:first-child { margin-top: 0; }
    .ppm-done { opacity: .55; cursor: not-allowed !important; }
    .ppm-tag { display: inline-block; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; background: #ffedd5; color: #9a3412; margin-left: 8px; white-space: nowrap; }
    .ppm-banner { border-radius: 14px; background: #fff7ed; border: 1px solid #fed7aa; color: #7c2d12; padding: 10px 14px; font-size: 14px; font-weight: 600; margin-bottom: 12px; }
    .ppm-banner a { color: #ea580c; font-weight: 800; text-decoration: underline; white-space: nowrap; }
    .ppm-errors { border-radius: 14px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 10px 14px; font-size: 14px; font-weight: 600; margin-bottom: 12px; }
    .ppm-hint { font-size: 14px; color: #57534e; margin: 0 0 10px; }
    #program > div:first-child, #rincian > div:first-child, #perusahaan > div:first-child { max-width: 1100px; }
    @media (max-width: 768px) {
        .survey-track a.step-nav { width: auto !important; flex: 1 1 0 !important; min-width: 0 !important; justify-content: center !important; font-size: 0.82rem !important; }
        .ppm-scroll { max-height: calc(100dvh - 17rem); }
    }
</style>

<body class="items-center">
    <div class="survey-shell">
        <header class="survey-header">
            <div class="survey-header-inner">
                <div class="survey-brand">
                    <img src="{{ asset('assets/logo-bayan.png') }}" alt="Logo Bayan" class="h-11 w-11 rounded-2xl bg-white p-1">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-white/75">E-SKM PPM Bayan Group</p>
                        <p class="text-xs font-extrabold tracking-tight text-white">Survei Kepuasan Masyarakat</p>
                    </div>
                </div>
            </div>
        </header>

        <footer class="survey-footer">
            <div class="survey-footer-inner">
                <div class="survey-progress">
                    <div class="survey-progress-dots" id="surveyProgressDots"></div>
                    <div class="survey-progress-copy">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/75">Progress</p>
                        <p class="text-sm font-bold text-white"><span id="surveyStepLabel">Pilih desa</span> • <span id="surveyStepCount">1 / 16</span></p>
                    </div>
                </div>
            </div>
        </footer>

        <div class="overflow-x-hidden relative z-10">

            <div id="popup" class="fixed z-50 bg-black bg-opacity-30 w-full flex items-center h-full {{ $isLanjutan ? 'hidden' : '' }}">
                <div class="fixed z-30 bg-opacity-50 overflow-y-auto w-full" id="modal">
                    <div class="relative mx-auto p-5 border lg:w-1/2 w-5/6 shadow-lg rounded-md bg-white">
                        <div class="mt-3 text-center">
                            <h3 class="lg:text-lg lg:text-2xl  text-4xl  lg:leading-6 font-medium mb-4 text-gray-900 ">
                                SELAMAT DATANG DI SURVEI KEPUASAN MASYARAKAT
                            </h3>
                            <label class="text-black font-black lg:text-sm text-4xl">PERHATIAN:</label>
                            <div class="px-7 items-start mt-4 text-left justify-start">
                                <table class="border-separate font-bold border-spacing-y-3 lg:text-sm text-4xl">
                                    <tr>
                                        <td class="items-start flex">1. </td>
                                        <td class="text-justify">Tujuan survei ini adalah untuk memperoleh gambaran secara objektif mengenai kepuasan masyarakat terhadap Program Pengembangan dan Pemberdayaan Masyarakat (PPM) Bayan Group</td>
                                    </tr>
                                    <tr>
                                        <td class="items-start flex">2. </td>
                                        <td class="text-justify">Pilih desa Anda, lalu program yang ingin Anda nilai. Anda dapat menilai lebih dari satu program tanpa mengisi ulang identitas</td>
                                    </tr>
                                    <tr>
                                        <td class="items-start flex">3. </td>
                                        <td class="text-justify">Pilihan jawaban yang Anda berikan diharapkan sebagai pilihan yang dapat dipertanggungjawabkan</td>
                                    </tr>
                                    <tr>
                                        <td class="items-start flex">4. </td>
                                        <td class="text-justify">Hasil survei ini akan digunakan sebagai bahan perbaikan pelaksanaan program PPM</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="step-actions flex justify-between items-center px-4 py-3">
                                <button type="button" onclick="window.location='/'"
                                    class="px-4 py-2 bg-gray-800 text-white lg:text-base font-medium rounded-md text-4xl shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-300">
                                    KEMBALI
                                </button>
                                <button type="button" onclick="closePopup()" id="ok-btn"
                                    class="px-4 py-2 bg-gray-800 text-white lg:text-base font-medium rounded-md text-4xl shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-300">
                                    LANJUT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form id="surveyForm" action="{{ route('skm.store') }}" method="POST" class="survey-track h-screen flex" novalidate>
                @csrf
                <input type="hidden" name="lanjutan" value="{{ $isLanjutan ? 1 : 0 }}">

                {{-- 1. DESA --}}
                <div id="awal"
                class=" items-center relative isolate  min-w-full h-screen px-6 shadow-2xl sm:px-16 lg:px-24">
                <div class="text-left flex flex-col lg:py-14 lg:px-12 py-8 px-6 lg:text-left w-full justify-between">
                    @if ($errors->any())
                        <div class="ppm-errors">
                            Data belum tersimpan:
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($isLanjutan)
                        <div class="ppm-banner">
                            Anda melanjutkan sebagai <b>{{ $responden['nama'] }}</b> (Desa {{ $desaList[$responden['desa_id']] ?? '-' }}).
                            <a href="{{ route('skm.baru') }}">Bukan Anda? Responden baru</a>
                        </div>
                    @endif
                    <label
                        class=" max-w-md  text-left font-bold tracking-tight mb-2 whitespace-nowrap mb-3 text-gray-900 lg:text-2xl  text-4xl  drop-shadow-[0_1px_1px_rgba(0,0,0,0.3)]">
                        Silakan pilih desa Anda
                    </label>
                    <div class="h-3"></div>
                    @if (empty($desaList))
                        <div class="w-full rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-800">
                            Data desa belum tersedia. Silakan hubungi admin.
                        </div>
                    @endif
                    <ul id="desaRadios" class="grid w-full gap-2 lg:grid-cols-4 grid-cols-2">
                        @foreach ($desaList as $id => $nama)
                            @php $locked = $isLanjutan && (int) $responden['desa_id'] !== (int) $id; @endphp
                            <li>
                                <input type="radio" id="desa{{ $id }}" name="desa_id" value="{{ $id }}"
                                    class="hidden peer radio-desa"
                                    {{ $isLanjutan && (int) $responden['desa_id'] === (int) $id ? 'checked' : '' }}
                                    {{ $locked ? 'disabled' : '' }}>
                                <label for="desa{{ $id }}" class="{{ $radioCard }} {{ $locked ? 'ppm-done' : '' }}">
                                    <div class="w-full lg:text-lg text-left lg:text-center text-xl font-semibold">{{ $nama }}</div>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <div class="step-actions flex justify-end mt-5">
                        <a type="button" onclick="lanjutDariDesa()" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer ">LANJUT</label>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowNext }}" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                </div>

                {{-- 2. PROGRAM (dikelompokkan per pilar) --}}
                <div id="program" class=" items-center relative isolate  min-w-full h-screen px-6 shadow-2xl sm:px-16 lg:px-24">
                <div class="text-left flex flex-col lg:py-14 lg:px-12 py-8 px-6 lg:text-left w-full justify-between">
                    @if ($isLanjutan)
                        <div class="ppm-banner">
                            Menilai program lain sebagai <b>{{ $responden['nama'] }}</b>.
                            <a href="{{ route('skm.baru') }}">Responden baru</a>
                        </div>
                    @endif
                    <label class="max-w-md text-left font-bold tracking-tight mb-2 text-gray-900 lg:text-2xl text-4xl">
                        Pilih program yang akan Anda nilai
                    </label>
                    <p class="ppm-hint">Program yang berjalan di Desa <b class="js-desa-nama">-</b>.</p>
                    <div id="programList" class="ppm-scroll"></div>
                    <div class="step-actions flex justify-between mt-5">
                        <a type="button" onclick="kembaliDariProgram()" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowPrev }}" clip-rule="evenodd" /></svg>
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">SEBELUM</label>
                        </a>
                        <a type="button" onclick="lanjutDariProgram()" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">LANJUT</label>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowNext }}" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                </div>

                {{-- 3. RINCIAN PROGRAM --}}
                <div id="rincian" class=" items-center relative isolate  min-w-full h-screen px-6 shadow-2xl sm:px-16 lg:px-24">
                <div class="text-left flex flex-col lg:py-14 lg:px-12 py-8 px-6 lg:text-left w-full justify-between">
                    <label class="max-w-md text-left font-bold tracking-tight mb-2 text-gray-900 lg:text-2xl text-4xl">
                        Pilih rincian program
                    </label>
                    <p class="ppm-hint">Program: <b id="programNama">-</b></p>
                    <div id="rincianList" class="ppm-scroll"></div>
                    <div class="step-actions flex justify-between mt-5">
                        <a type="button" onclick="window.location='#program'" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowPrev }}" clip-rule="evenodd" /></svg>
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">SEBELUM</label>
                        </a>
                        <a type="button" onclick="lanjutDariRincian()" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">LANJUT</label>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowNext }}" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                </div>

                {{-- 4. PERUSAHAAN PELAKSANA --}}
                <div id="perusahaan" class=" items-center relative isolate  min-w-full h-screen px-6 shadow-2xl sm:px-16 lg:px-24">
                <div class="text-left flex flex-col lg:py-14 lg:px-12 py-8 px-6 lg:text-left w-full justify-between">
                    <label class="text-left font-bold tracking-tight mb-2 text-gray-900 lg:text-2xl text-4xl">
                        Menurut Anda, perusahaan mana yang melaksanakan program ini?
                    </label>
                    <p class="ppm-hint"><b id="rincianNama">-</b> &middot; Desa <span class="js-desa-nama">-</span></p>
                    <div id="perusahaanList" class="ppm-scroll"></div>
                    <div class="step-actions flex justify-between mt-5">
                        <a type="button" onclick="window.location='#rincian'" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowPrev }}" clip-rule="evenodd" /></svg>
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">SEBELUM</label>
                        </a>
                        <a type="button" onclick="lanjutDariPerusahaan()" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">LANJUT</label>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowNext }}" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                </div>

                {{-- 5. IDENTITAS (dilewati bila responden lanjutan) --}}
                <div id="identitas"
                class=" items-center relative isolate  min-w-full h-screen px-6 shadow-2xl sm:px-16 lg:px-24">

                <div class="text-left flex flex-col lg:py-14 lg:px-12 py-8 px-6 lg:text-left w-full justify-between">
                    <div class="identity-copy">
                        <label
                            class="max-w-md text-left font-bold tracking-tight mb-2 whitespace-nowrap mb-3 text-gray-900 lg:text-2xl text-4xl drop-shadow-[0_1px_1px_rgba(0,0,0,0.3)]">
                            Lengkapi data identitas Anda
                        </label>
                        <p>Identitas cukup diisi sekali. Setelah menyimpan, Anda dapat menilai program lain tanpa mengisi ulang.</p>
                    </div>

                    <div class="biodataIdentity">
                        <div>
                            <label for="nama">Nama</label>
                            <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap" maxlength="150">
                        </div>
                        <div>
                            <label for="desaTampil">Desa</label>
                            <input type="text" id="desaTampil" value="" readonly tabindex="-1" style="background:#f5f5f4">
                        </div>
                    </div>

                    <div class="biodataIdentity">
                        <div>
                            <label for="nohp">No. Telp</label>
                            <input type="text" id="nohp" name="nohp" value="{{ old('nohp') }}" placeholder="Masukkan nomor telepon" maxlength="16">
                        </div>
                        <div>
                            <label>Jenis Kelamin</label>
                            <div class="identity-radio-grid">
                                @foreach (\App\Support\SurveyOptions::JENKEL as $idx => $jk)
                                    <div>
                                        <input type="radio" id="identitas-jk{{ $idx }}" name="jenkel" value="{{ $jk }}"
                                            {{ old('jenkel', 'Laki - Laki') == $jk ? 'checked' : '' }}
                                            class="hidden scroll-btn peer">
                                        <label for="identitas-jk{{ $idx }}"
                                            class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-transparent border-2 drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)] border-white rounded-lg cursor-pointer peer-checked:border-white peer-checked:bg-white peer-checked:text-gray-800 hover:text-gray-900 hover:bg-white">
                                            <div class="block">
                                                <div class="w-full lg:text-lg lg:text-2xl text-4xl font-semibold">{{ $jk }}</div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="biodataIdentity biodataIdentity--single">
                        <div>
                            <label>Jenis Responden</label>
                            <div class="identity-radio-grid" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
                                @foreach ($jenisRespondenOptions as $idx => $jr)
                                    <div>
                                        <input type="radio" id="jenis-responden{{ $idx }}" name="jenis_responden" value="{{ $jr }}"
                                            {{ old('jenis_responden') == $jr ? 'checked' : '' }}
                                            class="hidden scroll-btn peer radio-jenis-responden">
                                        <label for="jenis-responden{{ $idx }}"
                                            class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-transparent border-2 drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)] border-white rounded-lg cursor-pointer peer-checked:border-white peer-checked:bg-white peer-checked:text-gray-800 hover:text-gray-900 hover:bg-white">
                                            <div class="w-full lg:text-lg text-xl font-semibold">{{ $jr }}</div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="biodataIdentity biodataIdentity--single">
                        <div>
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" placeholder="Masukkan alamat (RT/dusun)" maxlength="300">{{ old('alamat') }}</textarea>
                        </div>
                    </div>

                    <div class="step-actions flex justify-between mt-5">
                        <a type="button" onclick="window.location='#perusahaan'" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowPrev }}" clip-rule="evenodd" /></svg>
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">SEBELUM</label>
                        </a>
                        <a type="button" onclick="lanjutDariIdentitas()" class="step-nav flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">LANJUT</label>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-gray-900"><path fill-rule="evenodd" d="{{ $arrowNext }}" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                </div>
            <div id="kedua"
                class="lg:pt-0 pt-16 items-center relative isolate overflow-hidden min-w-full h-screen
                px-6 shadow-2xl sm:px-16 lg:flex lg:px-24 bg-gradient-to-br from-[#18bdde] from-60%  to-[#fffb61] to-95%">
                <div class=" text-center  lg:flex lg:py-32 lg:text-left   w-full justify-between">
                    <div class="gap-4 w-full ">
                        <div class="flex justify-between">
                            <h1
                                class=" max-w-md  font-bold tracking-tight mb-2 text-gray-900 lg:text-2xl  text-4xl  drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                                Usia (Tahun)
                            </h1>
                            <h1 id="errorusia" class="{{ $error }}">
                            </h1>
                            @error('usia')
                                <div class="{{ $error }}">Harus Diisi</div>
                            @enderror
                        </div>
                        <ul class="grid w-full gap-2 grid-cols-2">
                            <li>
                                <input type="radio" id="29" name="usia" value="29" checked
                                    class="hidden scroll-btn peer">
                                <label for="29"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">Kurang dari
                                            30 Tahun
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="30" name="usia" value="30"
                                    class="hidden scroll-btn peer">
                                <label for="30"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">30 s/d 40
                                            Tahun</div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="41" name="usia" value="41"
                                    class="hidden scroll-btn peer">
                                <label for="41"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">41 s/d 50
                                            Tahun</div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="51" name="usia" value="51"
                                    class="hidden scroll-btn peer">
                                <label for="51"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">Lebih dari
                                            50 Tahun</div>
                                    </div>
                                </label>
                            </li>
                        </ul>
                        
                    </div>
                    <div class="w-1/6"></div>
                    <div class="gap-4 w-full lg:mt-0">
                        <div class="flex justify-between ">
                            <h1
                                class=" max-w-md  font-bold tracking-tight mb-2 text-gray-900 lg:text-2xl  text-4xl  drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                                Pendidikan
                            </h1>
                            @error('pendidikan')
                                <div class="{{ $error }}">Pendidikan</div>
                            @enderror
                        </div>
                        <ul class="grid w-full gap-2 grid-cols-2">
                            <li>
                                <input type="radio" id="sd" name="pendidikan" value="SD"
                                    class="hidden scroll-btn peer">
                                <label for="sd"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">SD</div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="smp" name="pendidikan" value="SMP"
                                    class="hidden scroll-btn peer">
                                <label for="smp"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">SMP</div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="sma/smk" name="pendidikan" value="SMA / SMK" checked
                                    class="hidden scroll-btn peer">
                                <label for="sma/smk"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">SMA / SMK
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="d1/d3" name="pendidikan" value="D-I / D-III"
                                    class="hidden scroll-btn peer">
                                <label for="d1/d3"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">D-I / D-III
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="s1/setara" name="pendidikan" value="S1 / Setara"
                                    class="hidden scroll-btn peer">
                                <label for="s1/setara"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">S1 / Setara
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="s2/s3" name="pendidikan" value="S2 / S3"
                                    class="hidden scroll-btn peer">
                                <label for="s2/s3"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">S2 / S3
                                        </div>
                                    </div>
                                </label>
                            </li>
                        </ul>

                        <div class="flex justify-between mt-3">
                            <h1
                                class=" max-w-md capitalize  font-bold tracking-tight mb-2 text-gray-900 lg:text-2xl  text-4xl  drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                                pekerjaan
                            </h1>
                            @error('pekerjaan')
                                <div class="{{ $error }} capitalize">Harus Diisi</div>
                            @enderror
                        </div>
                        <ul class="grid w-full gap-2 md:grid-cols-2">
                            <li>
                                <input type="radio" id="asn" name="pekerjaan" value="ASN"
                                    class="hidden scroll-btn peer">
                                <label for="asn"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">ASN</div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="tni/polri" name="pekerjaan" value="TNI / POLRI"
                                    class="hidden scroll-btn peer">
                                <label for="tni/polri"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">TNI / POLRI
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="swasta" name="pekerjaan" value="Swasta" checked
                                    class="hidden scroll-btn peer">
                                <label for="swasta"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">Swasta
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="pengusaha" name="pekerjaan" value="Pengusaha"
                                    class="hidden scroll-btn peer">
                                <label for="pengusaha"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">Pengusaha
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="pelajar" name="pekerjaan" value="Pelajar"
                                    class="hidden scroll-btn peer">
                                <label for="pelajar"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">Pelajar
                                        </div>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <input type="radio" id="lyn" name="pekerjaan" value="Lainnya"
                                    class="hidden scroll-btn peer">
                                <label for="lyn"
                                    class="inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer  peer-checked:bg-gray-800  peer-checked:text-white hover:text-gray-900 hover:bg-gray-400 ">
                                    <div class="block">
                                        <div class="w-full lg:text-lg lg:text-2xl  text-4xl  font-semibold">Lainnya
                                        </div>
                                    </div>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div class="step-actions col-span-2 flex justify-between mt-5 w-full">
                        <a type="button" onclick="window.location='#identitas'"
                            class="flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-8 h-8 text-gray-900">
                                <path fill-rule="evenodd"
                                    d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z"
                                    clip-rule="evenodd" />
                            </svg>
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">SEBELUM</label>
                        </a>
                        <a type="button" onclick="window.location='#satu'"
                            class="flex items-center gap-2 cursor-pointer drop-shadow-[0_3px_3px_rgba(0,0,0,0.3)]">
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">LANJUT</label>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-8 h-8 text-gray-900">
                                <path fill-rule="evenodd"
                                    d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div id="satu"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] y-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">fact_check</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Kesesuaian Program</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah program PPM yang dilaksanakan telah sesuai dengan kebutuhan masyarakat di wilayah Anda?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u1-1" name="u1" value="1" class="hidden scroll-btn peer">
                            <label for="u1-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Sesuai</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u1-2" name="u1" value="2" class="hidden scroll-btn peer">
                            <label for="u1-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Sesuai</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u1-3" name="u1" value="3" class="hidden scroll-btn peer">
                            <label for="u1-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sesuai</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u1-4" name="u1" value="4" checked class="hidden scroll-btn peer">
                            <label for="u1-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Sesuai</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="kembaliDariSatu()"
                                class="step-nav flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#dua'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="dua"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">task_alt</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Kualitas Pelaksanaan</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah kegiatan PPM telah dilaksanakan dengan baik, tertib, dan sesuai dengan rencana yang disampaikan?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u2-1" name="u2" value="1" class="hidden scroll-btn peer">
                            <label for="u2-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Baik</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u2-2" name="u2" value="2" class="hidden scroll-btn peer">
                            <label for="u2-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Baik</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u2-3" name="u2" value="3" class="hidden scroll-btn peer">
                            <label for="u2-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Baik</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u2-4" name="u2" value="4" checked class="hidden scroll-btn peer">
                            <label for="u2-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Baik</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#satu'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#tiga'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tiga"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">autorenew</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Keberlanjutan</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah manfaat program PPM dapat terus dirasakan oleh masyarakat dalam jangka panjang?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u3-1" name="u3" value="1" class="hidden scroll-btn peer">
                            <label for="u3-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Berkelanjutan</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u3-2" name="u3" value="2" class="hidden scroll-btn peer">
                            <label for="u3-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Berkelanjutan</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u3-3" name="u3" value="3" class="hidden scroll-btn peer">
                            <label for="u3-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Berkelanjutan</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u3-4" name="u3" value="4" checked class="hidden scroll-btn peer">
                            <label for="u3-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Berkelanjutan</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#dua'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#empat'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="empat"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">info</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Kejelasan Informasi</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah informasi mengenai tujuan, manfaat, dan pelaksanaan program PPM telah disampaikan dengan jelas dan mudah dipahami?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u4-1" name="u4" value="1" class="hidden scroll-btn peer">
                            <label for="u4-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Jelas</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u4-2" name="u4" value="2" class="hidden scroll-btn peer">
                            <label for="u4-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Jelas</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u4-3" name="u4" value="3" class="hidden scroll-btn peer">
                            <label for="u4-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Jelas</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u4-4" name="u4" value="4" checked class="hidden scroll-btn peer">
                            <label for="u4-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Jelas</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#tiga'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#lima'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="lima"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">workspace_premium</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Kompetensi Tim</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah tim pelaksana program memiliki kemampuan dan pengetahuan yang memadai dalam menjalankan kegiatan PPM?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u5-1" name="u5" value="1" class="hidden scroll-btn peer">
                            <label for="u5-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Kompeten</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u5-2" name="u5" value="2" class="hidden scroll-btn peer">
                            <label for="u5-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Kompeten</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u5-3" name="u5" value="3" class="hidden scroll-btn peer">
                            <label for="u5-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kompeten</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u5-4" name="u5" value="4" checked class="hidden scroll-btn peer">
                            <label for="u5-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Kompeten</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#empat'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#enam'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="enam"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">accessibility_new</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Kemudahan Akses</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah masyarakat mudah mengakses dan mengikuti program PPM yang diselenggarakan?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u6-1" name="u6" value="1" class="hidden scroll-btn peer">
                            <label for="u6-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Mudah</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u6-2" name="u6" value="2" class="hidden scroll-btn peer">
                            <label for="u6-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Mudah</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u6-3" name="u6" value="3" class="hidden scroll-btn peer">
                            <label for="u6-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Mudah</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u6-4" name="u6" value="4" checked class="hidden scroll-btn peer">
                            <label for="u6-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Mudah</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#lima'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#tujuh'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tujuh"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">sentiment_satisfied</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Sikap dan Etika</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah tim pelaksana program bersikap sopan, ramah, dan menghargai masyarakat selama kegiatan berlangsung?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u7-1" name="u7" value="1" class="hidden scroll-btn peer">
                            <label for="u7-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Sopan dan Ramah</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u7-2" name="u7" value="2" class="hidden scroll-btn peer">
                            <label for="u7-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Sopan dan Ramah</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u7-3" name="u7" value="3" class="hidden scroll-btn peer">
                            <label for="u7-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sopan dan Ramah</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u7-4" name="u7" value="4" checked class="hidden scroll-btn peer">
                            <label for="u7-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Sopan dan Ramah</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#enam'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#delapan'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="delapan"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">trending_up</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Dampak Sosial dan Ekonomi</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah program PPM memberikan dampak positif terhadap kondisi sosial dan ekonomi masyarakat?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u8-1" name="u8" value="1" class="hidden scroll-btn peer">
                            <label for="u8-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Berdampak</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u8-2" name="u8" value="2" class="hidden scroll-btn peer">
                            <label for="u8-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Berdampak</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u8-3" name="u8" value="3" class="hidden scroll-btn peer">
                            <label for="u8-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Berdampak</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u8-4" name="u8" value="4" checked class="hidden scroll-btn peer">
                            <label for="u8-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Berdampak</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#tujuh'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#sembilan'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="sembilan"
                class="items-center relative isolate min-w-full h-screen px-4 sm:px-6 lg:px-16
                bg-gradient-to-br from-[#fff5e6] via-[#ffecd6] to-[#ffd9b0]">
                <div class="flex flex-col items-stretch gap-4 w-full h-full py-[72px] max-w-2xl mx-auto justify-center">
                    <div class="w-full flex flex-col p-6 bg-white rounded-2xl shadow-[0_8px_32px_rgba(146,76,0,0.12)] justify-center">
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 gap-3">
                            <span class="material-symbols-outlined text-[#ff8800] text-2xl">volunteer_activism</span>
                        </div>
                        <p class="font-bold uppercase tracking-widest text-xs text-[#924c00] mb-2">Kebermanfaatan</p>
                        <h2 class="text-lg xl:text-xl font-semibold leading-snug text-[#191c1d]">Menurut Anda, apakah program PPM memberikan manfaat nyata bagi masyarakat penerima program?</h2>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div>
                            <input type="radio" id="u9-1" name="u9" value="1" class="hidden scroll-btn peer">
                            <label for="u9-1"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Tidak Bermanfaat</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u9-2" name="u9" value="2" class="hidden scroll-btn peer">
                            <label for="u9-2"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Kurang Bermanfaat</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u9-3" name="u9" value="3" class="hidden scroll-btn peer">
                            <label for="u9-3"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Bermanfaat</span>
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="u9-4" name="u9" value="4" checked class="hidden scroll-btn peer">
                            <label for="u9-4"
                                class="flex items-center px-5 py-4 rounded-xl border-2 border-orange-100 bg-white/80 text-[#3d2000] cursor-pointer hover:bg-white hover:border-orange-300 hover:shadow-md transition-all duration-200 peer-checked:bg-[#1c1917] peer-checked:text-white peer-checked:border-[#ff8800] peer-checked:shadow-lg">
                                <span class="font-semibold text-base lg:text-lg">Sangat Bermanfaat</span>
                            </label>
                        </div>
                        <div class="step-actions flex justify-between mt-3">
                            <a type="button" onclick="window.location='#delapan'"
                                class="flex items-center gap-2 cursor-pointer text-[#924c00] font-semibold text-sm hover:text-[#ff8800] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z" clip-rule="evenodd" />
                                </svg>
                                Kembali
                            </a>
                            <a type="button" onclick="window.location='#saran'"
                                class="flex items-center gap-2 cursor-pointer bg-[#ff8800] text-white px-5 py-2 rounded-xl font-semibold text-sm hover:bg-[#e07200] transition-colors shadow-md">
                                Lanjut
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="saran"
                class="lg:pt-0 pt-16 items-center relative isolate overflow-hidden min-w-full h-screen
                px-6 shadow-2xl sm:px-16 lg:flex lg:px-24 bg-[#92e0f0] "
                style="background-image:
                    radial-gradient(at -10% -10%, #18bdde, transparent 35%),
                    radial-gradient(at 110% 110%, #18bdde, transparent 35%);">
                <div class=" text-left  lg:py-32 lg:text-left items-center  w-full justify-between">
                    <h2 class=" font-bold tracking-tight text-gray-900 text-5xl lg:text-3xl mb-6">Saran
                        perbaikan, masukan, dan harapan:</h2>
                    <textarea type="text" id="saran" name="saran"
                        class="block w-full p-4 pl-10 text-xl text-gray-900 border border-2 border-white rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-900 dark:placeholder-gray-400 dark:text-gray-900 dark:focus:ring-blue-500 dark:focus:border-blue-500 mb-6"></textarea>
                    <div class="step-actions flex justify-between mt-5">
                        <a type="button" onclick="window.location='#sembilan'"
                            class="flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-8 h-8 text-gray-900">
                                <path fill-rule="evenodd"
                                    d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-4.28 9.22a.75.75 0 000 1.06l3 3a.75.75 0 101.06-1.06l-1.72-1.72h5.69a.75.75 0 000-1.5h-5.69l1.72-1.72a.75.75 0 00-1.06-1.06l-3 3z"
                                    clip-rule="evenodd" />
                            </svg>
                            <label class="text-gray-900 font-medium lg:text-xl text-4xl cursor-pointer">SEBELUM</label>
                        </a>
                        <button for="kesesuaian-ad" type="submit"
                        class="font-bold items-center justify- px-8 py-3 text-white bg-gray-800 border-white border-2 rounded-lg cursor-pointer peer-checked:bg-white peer-checked:text-white hover:text-white hover:bg-gray-400">
                            LANJUT
                        </button>
                    </div>
                </div>

            </div>
        </form>
        </div>
    </div>
<script>
    const PPM = @json($catalog);
    const TIDAK_TAHU = @json($tidakTahu);
    const RESPONDEN = @json($responden ? ['desa_id' => (int) $responden['desa_id'], 'dinilai' => $responden['dinilai'] ?? []] : null);
    const IS_LANJUTAN = RESPONDEN !== null;
    const HAS_ERRORS = @json($errors->any());

    const RADIO_CARD = 'inline-flex items-center justify-between w-full p-3 text-gray-900 bg-white border-2 border-white rounded-lg cursor-pointer peer-checked:bg-gray-800 h-full peer-checked:text-white hover:text-gray-900 hover:bg-gray-400';

    function escapeHtml(s){
        return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function checkedValue(name){
        const el = document.querySelector(`input[name="${name}"]:checked`);
        return el ? el.value : null;
    }

    function selectedDesa(){ return checkedValue('desa_id'); }

    function sudahDinilai(rincianId){
        return IS_LANJUTAN && RESPONDEN.dinilai.includes(rincianId);
    }

    /** Rincian yang punya pelaksana di desa ini, dikelompokkan per program. */
    function rincianPerProgram(desaId){
        const out = {};
        Object.entries(PPM.rincian).forEach(([id, r]) => {
            if ((r.pelaksana[desaId] || []).length) {
                (out[r.program] = out[r.program] || []).push(id);
            }
        });
        return out;
    }

    function radioItem(name, value, label, opts = {}){
        const id = `${name}-${String(value).replace(/[^a-zA-Z0-9_-]/g, '_')}`;
        return `
            <li>
                <input type="radio" id="${id}" name="${name}" value="${escapeHtml(value)}" class="hidden peer" ${opts.disabled ? 'disabled' : ''}>
                <label for="${id}" class="${RADIO_CARD} ${opts.disabled ? 'ppm-done' : ''}">
                    <div class="w-full lg:text-lg text-xl text-left font-semibold">${escapeHtml(label)}${opts.tag ? `<span class="ppm-tag">${escapeHtml(opts.tag)}</span>` : ''}</div>
                </label>
            </li>`;
    }

    function renderProgram(){
        const desaId = selectedDesa();
        const el = document.getElementById('programList');
        const perProgram = rincianPerProgram(desaId);
        document.querySelectorAll('.js-desa-nama').forEach(n => n.textContent = PPM.desa[desaId] || '-');
        document.getElementById('desaTampil').value = PPM.desa[desaId] || '';

        let html = '';
        let lastPilar = null;
        Object.entries(PPM.program).forEach(([programId, p]) => {
            const rincianIds = perProgram[programId];
            if (!rincianIds) return;
            if (p.pilar !== lastPilar) {
                if (lastPilar !== null) html += '</ul>';
                html += `<p class="ppm-pilar-title">${escapeHtml(PPM.pilar[p.pilar] || p.pilar)}</p><ul class="grid w-full gap-2 lg:grid-cols-2 grid-cols-1">`;
                lastPilar = p.pilar;
            }
            const semuaSudah = rincianIds.every(sudahDinilai);
            html += radioItem('program_id', programId, p.nama, { disabled: semuaSudah, tag: semuaSudah ? 'Sudah dinilai' : '' });
        });
        if (lastPilar !== null) html += '</ul>';

        el.innerHTML = html || '<div class="ppm-banner">Belum ada program yang berjalan di desa ini.</div>';
    }

    function renderRincian(){
        const desaId = selectedDesa();
        const programId = checkedValue('program_id');
        const ids = rincianPerProgram(desaId)[programId] || [];
        document.getElementById('programNama').textContent = PPM.program[programId]?.nama || '-';

        const items = ids.map(id => {
            const done = sudahDinilai(id);
            return radioItem('rincian_id', id, PPM.rincian[id].nama, { disabled: done, tag: done ? 'Sudah dinilai' : '' });
        }).join('');
        document.getElementById('rincianList').innerHTML = `<ul class="grid w-full gap-2 grid-cols-1">${items}</ul>`;

        // Hanya satu rincian yang bisa dipilih: pilih otomatis
        const enabled = document.querySelectorAll('#rincianList input:not([disabled])');
        if (enabled.length === 1) enabled[0].checked = true;
    }

    function renderPerusahaan(){
        const desaId = selectedDesa();
        const rincianId = checkedValue('rincian_id');
        const kodes = PPM.rincian[rincianId]?.pelaksana[desaId] || [];
        document.getElementById('rincianNama').textContent = PPM.rincian[rincianId]?.nama || '-';

        const items = kodes.map(k => radioItem('perusahaan_jawaban', k, PPM.perusahaan[k] || k)).join('')
            + radioItem('perusahaan_jawaban', TIDAK_TAHU, 'Tidak tahu');
        document.getElementById('perusahaanList').innerHTML = `<ul class="grid w-full gap-2 lg:grid-cols-2 grid-cols-1">${items}</ul>`;
    }

    function lanjutDariDesa(){
        if (!selectedDesa()) { alert('Silakan pilih desa terlebih dahulu.'); return; }
        renderProgram();
        window.location.hash = '#program';
    }

    function kembaliDariProgram(){
        window.location.hash = '#awal';
    }

    function lanjutDariProgram(){
        if (!checkedValue('program_id')) { alert('Silakan pilih program terlebih dahulu.'); return; }
        renderRincian();
        window.location.hash = '#rincian';
    }

    function lanjutDariRincian(){
        if (!checkedValue('rincian_id')) { alert('Silakan pilih rincian program terlebih dahulu.'); return; }
        renderPerusahaan();
        window.location.hash = '#perusahaan';
    }

    function lanjutDariPerusahaan(){
        if (!checkedValue('perusahaan_jawaban')) { alert('Silakan pilih perusahaan, atau "Tidak tahu".'); return; }
        window.location.hash = IS_LANJUTAN ? '#satu' : '#identitas';
    }

    function lanjutDariIdentitas(){
        const nama = document.getElementById('nama');
        if (!nama.value.trim()) { alert('Silakan isi Nama terlebih dahulu.'); nama.focus(); return; }
        if (!checkedValue('jenkel')) { alert('Silakan pilih jenis kelamin.'); return; }
        if (!checkedValue('jenis_responden')) { alert('Silakan pilih jenis responden.'); return; }
        window.location.hash = '#kedua';
    }

    /** Tombol "Kembali" di pertanyaan pertama: responden lanjutan tidak melewati identitas. */
    function kembaliDariSatu(){
        window.location.hash = IS_LANJUTAN ? '#perusahaan' : '#kedua';
    }

    // Ganti desa/program/rincian -> pilihan di langkah berikutnya tidak berlaku lagi
    document.addEventListener('change', function (e) {
        if (e.target.name === 'desa_id') {
            document.getElementById('programList').innerHTML = '';
            document.getElementById('rincianList').innerHTML = '';
            document.getElementById('perusahaanList').innerHTML = '';
        } else if (e.target.name === 'program_id') {
            document.getElementById('rincianList').innerHTML = '';
            document.getElementById('perusahaanList').innerHTML = '';
        } else if (e.target.name === 'rincian_id') {
            document.getElementById('perusahaanList').innerHTML = '';
        }
    });

    document.getElementById('surveyForm').addEventListener('submit', function (e) {
        const missing = [
            ['desa_id', 'desa', '#awal'],
            ['rincian_id', 'rincian program', '#program'],
            ['perusahaan_jawaban', 'perusahaan', '#perusahaan'],
        ].find(([name]) => !checkedValue(name));
        if (missing) {
            e.preventDefault();
            alert(`Silakan pilih ${missing[1]} terlebih dahulu.`);
            window.location.hash = missing[2];
            return;
        }
        if (!IS_LANJUTAN && !document.getElementById('nama').value.trim()) {
            e.preventDefault();
            alert('Silakan isi Nama terlebih dahulu.');
            window.location.hash = '#identitas';
            return;
        }
        const btn = this.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.textContent = 'MENYIMPAN...'; }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('is-ready');

        const sections = [
            { id: 'awal', label: 'Pilih desa' },
            { id: 'program', label: 'Pilih program' },
            { id: 'rincian', label: 'Pilih rincian program' },
            { id: 'perusahaan', label: 'Perusahaan pelaksana' },
            ...(IS_LANJUTAN ? [] : [
                { id: 'identitas', label: 'Lengkapi data identitas' },
                { id: 'kedua', label: 'Lengkapi biodata lainnya' },
            ]),
            { id: 'satu', label: 'Kesesuaian program' },
            { id: 'dua', label: 'Kualitas pelaksanaan' },
            { id: 'tiga', label: 'Keberlanjutan' },
            { id: 'empat', label: 'Kejelasan informasi' },
            { id: 'lima', label: 'Kompetensi tim' },
            { id: 'enam', label: 'Kemudahan akses' },
            { id: 'tujuh', label: 'Sikap dan etika' },
            { id: 'delapan', label: 'Dampak sosial dan ekonomi' },
            { id: 'sembilan', label: 'Kebermanfaatan' },
            { id: 'saran', label: 'Saran dan Harapan' }
        ];

        // Pilihan dibuat di halaman ini, jadi setelah reload selalu mulai dari awal
        // (responden lanjutan langsung ke pilihan program di desanya).
        if (IS_LANJUTAN && !HAS_ERRORS) {
            renderProgram();
            window.location.hash = '#program';
        } else {
            window.location.hash = '#awal';
        }

        const stepLabel = document.getElementById('surveyStepLabel');
        const stepCount = document.getElementById('surveyStepCount');
        const dots = document.getElementById('surveyProgressDots');

        if (dots) {
            dots.innerHTML = sections.map((_, index) => `<span data-step-dot="${index}"></span>`).join('');
        }

        function updateStepFromHash() {
            const hash = window.location.hash ? window.location.hash.replace('#', '') : 'awal';
            const activeIndex = Math.max(0, sections.findIndex((section) => section.id === hash));
            const activeSection = sections[activeIndex] || sections[0];
            if (stepLabel) stepLabel.textContent = activeSection.label;
            if (stepCount) stepCount.textContent = `${activeIndex + 1} / ${sections.length}`;
            document.querySelectorAll('[data-step-dot]').forEach((dot, index) => {
                dot.classList.toggle('is-active', index === activeIndex);
            });
        }

        updateStepFromHash();
        window.addEventListener('hashchange', updateStepFromHash);
    });
</script>

    <script>
        var popup = document.getElementById("popup");

        function closePopup() {
            popup.classList.add('hidden');
        }
    </script>
</body>

</html>





