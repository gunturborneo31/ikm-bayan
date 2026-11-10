<?php

error_reporting(0);

if ($_GET['Triwulan1'] == 'off') {
    $startDate = '2024-01-01';

    if ($_GET['Bulan'] == 4 or $_GET['Bulan'] == 5 or $_GET['Bulan'] == 6) {
        $startDate = '2024-04-01';
    } elseif ($_GET['Bulan'] == 7 or $_GET['Bulan'] == 8 or $_GET['Bulan'] == 9) {
        $startDate = '2024-07-01';
    } elseif ($_GET['Bulan'] == 10 or $_GET['Bulan'] == 11 or $_GET['Bulan'] == 12) {
        $startDate = '2024-10-01';
    }
}
$endDate = '2023-10-03';

if ($_GET['Bulan'] == null) {
    $today = date('Y-m-d', strtotime('+1 days'));
    $endDate = $today;
} elseif ($_GET['Bulan'] == 1) {
    $endDate = '2023-01-31';
} elseif ($_GET['Bulan'] == 2) {
    $endDate = '2023-02-30';
} elseif ($_GET['Bulan'] == 3) {
    $endDate = '2023-03-31';
} elseif ($_GET['Bulan'] == 4) {
    $endDate = '2023-04-30';
} elseif ($_GET['Bulan'] == 5) {
    $endDate = '2023-05-31';
} elseif ($_GET['Bulan'] == 6) {
    $endDate = '2023-06-30';
} elseif ($_GET['Bulan'] == 7) {
    $endDate = '2023-07-31';
} elseif ($_GET['Bulan'] == 8) {
    $endDate = '2023-08-30';
} elseif ($_GET['Bulan'] == 9) {
    $endDate = '2023-09-31';
} elseif ($_GET['Bulan'] == 10) {
    $endDate = '2023-10-30';
} elseif ($_GET['Bulan'] == 11) {
    $endDate = '2023-11-31';
} elseif ($_GET['Bulan'] == 12) {
    $endDate = '2023-12-30';
}

$indexTu = ['u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9'];
$lastDate = ['2023-01-31', '2023-02-31', '2023-03-31', '2023-04-31', '2023-05-31', '2023-06-31', '2023-07-31', '2023-08-31', '2023-09-31', '2023-10-31', '2023-11-31', '2023-12-31'];
// dd($indexTu);

$tu = [[]];
$x = [];
$nn = [];
$nrr = [[]];
$nrrT = [[]];
$nrrTT = [];
$nilaiSkm = [];

array_push($nn, '');

for ($i = 1; $i <= 12; $i++) {
    $abc = $lastDate[$i - 1];
    for ($j = 1; $j <= 9; $j++) {
        $xyz = $indexTu[$j - 1];
        // echo $xyz;
        $nilaiTu = DB::table('nilaiUnsur')
            ->whereBetween('created_at', [$startDate, $abc])
            ->get()
            ->sum($xyz);
        array_push($x, $nilaiTu);
    }

    $nnn = DB::table('nilaiUnsur')
        ->whereBetween('created_at', [$startDate, $abc])
        ->get()
        ->count();

    array_push($tu, $x);
    array_push($nn, $nnn);
    $x = [];
}

for ($i = 1; $i <= 12; $i++) {
    $x;
    for ($j = 1; $j <= 9; $j++) {
        if ($nn[$i] == 0) {
            array_push($x, 0);
        } else {
            $nrrx = $tu[$i][$j - 1] / $nn[$i];
            array_push($x, round($nrrx, 2));
        }
    }
    // print_r($x);
    // echo '<br>';
    array_push($nrr, $x);
    $x = [];
}

array_push($nrrTT, '');
for ($i = 1; $i <= 12; $i++) {
    $x;
    $y;
    for ($j = 1; $j <= 9; $j++) {
        $nrrTx = $nrr[$i][$j - 1] * (1/9);
        array_push($x, round($nrrTx, 2));
        $y = $y + $nrrTx;
    }
    array_push($nrrT, $x);
    array_push($nrrTT, round($y, 2));
    $x = [];
    $y = 0;
}

for ($i = 0; $i <= 11; $i++) {
    if ($_GET['Tahun'] == '2024') {
        array_push($nilaiSkm, 0);
    } else {
        array_push($nilaiSkm, $nrrTT[$i + 1] * 25);
    }
}

// dd($nilaiSkm);

$tu1 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u1');

$tu2 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u2');

$tu3 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u3');

$tu4 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u4');

$tu5 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u5');

$tu6 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u6');

$tu7 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u7');

$tu8 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u8');

$tu9 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u9');

$n = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->count();

$tu9 = DB::table('nilaiUnsur')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->sum('u9');

// unset($nilaiSkm[0]);

$nilaiSkm = json_encode($nilaiSkm);

// dd($responden);
// dd($nilaiSkm);

if ($n == 0) {
    $nrr1 = 0;
    $nrr2 = 0;
    $nrr3 = 0;
    $nrr4 = 0;
    $nrr5 = 0;
    $nrr6 = 0;
    $nrr7 = 0;
    $nrr8 = 0;
    $nrr9 = 0;

    $nrr = 0;
    $nrr = 0;

    $nrrt1 = 0;
    $nrrt2 = 0;
    $nrrt3 = 0;
    $nrrt4 = 0;
    $nrrt5 = 0;
    $nrrt6 = 0;
    $nrrt7 = 0;
    $nrrt8 = 0;
    $nrrt9 = 0;

    $nrrT = 0;
    $nrrT = 0;

    $nilaiSKM = $nrrT * 25;
} else {
    $nrr1 = sprintf('%0.2f', $tu1 / $n);
    $nrr2 = sprintf('%0.2f', $tu2 / $n);
    $nrr3 = sprintf('%0.2f', $tu3 / $n);
    $nrr4 = sprintf('%0.2f', $tu4 / $n);
    $nrr5 = sprintf('%0.2f', $tu5 / $n);
    $nrr6 = sprintf('%0.2f', $tu6 / $n);
    $nrr7 = sprintf('%0.2f', $tu7 / $n);
    $nrr8 = sprintf('%0.2f', $tu8 / $n);
    $nrr9 = sprintf('%0.2f', $tu9 / $n);

    $nrr = $nrr1 + $nrr2 + $nrr3 + $nrr4 + $nrr5 + $nrr6 + $nrr7 + $nrr8 + $nrr9;
    $nrr = sprintf('%0.2f', $nrr);

    $nrrt1 = sprintf('%0.2f', $nrr1 * (1/9));
    $nrrt2 = sprintf('%0.2f', $nrr2 * (1/9));
    $nrrt3 = sprintf('%0.2f', $nrr3 * (1/9));
    $nrrt4 = sprintf('%0.2f', $nrr4 * (1/9));
    $nrrt5 = sprintf('%0.2f', $nrr5 * (1/9));
    $nrrt6 = sprintf('%0.2f', $nrr6 * (1/9));
    $nrrt7 = sprintf('%0.2f', $nrr7 * (1/9));
    $nrrt8 = sprintf('%0.2f', $nrr8 * (1/9));
    $nrrt9 = sprintf('%0.2f', $nrr9 * (1/9));

    $nrrT = $nrrt1 + $nrrt2 + $nrrt3 + $nrrt4 + $nrrt5 + $nrrt6 + $nrrt7 + $nrrt8 + $nrrt9;
    $nrrT = sprintf('%0.2f', $nrrT);

    $nilaiSKM = $nrrT * 25;

    // $responden = [];
    // $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nop', 'Des'];

    // if ($_GET['Bulan'] == '') {
    //     for ($x = 0; $x <= date('m'); $x++) {
    //         $responden[$x][0] = DB::table('nilaiUnsur')
    //             ->whereMonth('created_at', '=', $x + 1)
    //             ->get()
    //             ->count();
    //         $responden[$x][1] = $bulan[$x];
    //     }
    // } else {
    //     for ($x = 0; $x <= $_GET['Bulan']; $x++) {
    //         $responden[$x][0] = DB::table('nilaiUnsur')
    //             ->whereMonth('created_at', '=', $x + 1)
    //             ->get()
    //             ->count();
    //         $responden[$x][1] = $bulan[$x];
    //     }
    // }
    // dd($responden);

    if ($_GET['Tahun'] == '2024') {
        $tu = [[]];
        $x = [];
        $nn = [];
        $nrr = [[]];
        $nrrT = [[]];
        $nrrTT = [];
        // $nilaiSkm = [];
    }
}
// echo $nrrTotal;
// echo $nrrTertimbang;
?>

<!doctype html>
<html class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" conteitial-scale="1.0">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="items-center
 bg-black sm:bg-red-800 md:bg-orange-800 lg:bg-green-800 xl:bg-blue-800
 h-full w-full"
    {{-- style="background-image:
        radial-gradient(at -30% -30%, #009E61, transparent 80%),
        radial-gradient(at 130% 150%, #009E61, transparent 80%);" --}}>

    <div class="">
    </div>

    <script>
        var data = {{ $nilaiSkm }};
        // var label = {{ $singkatan }};
        var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nop', 'Des'];
        var bln = [];
        var jum = {{ $jml }};

        let str = '';

        for (let i = 0; i < jum; i++) {
            bln[i] = bulan[i];
        }

        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: bulan,
                datasets: [{
                    label: 'Nilai SKM',
                    data: data,
                    borderWidth: 1,
                    borderColor: '#007F4E',
                    backgroundColor: '#007F4E',

                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }

        });
    </script>

</body>

</html>




