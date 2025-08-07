<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian Produksi - {{ \Carbon\Carbon::createFromDate($year, $month)->isoFormat('MMMM Y') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 6px;
        }

        .calendar {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar th,
        .calendar td {
            border: 1px solid #000;
            width: 14.28%;
            height: 20px; /* ⬅️ Lebih pendek agar muat */
            vertical-align: center;
            padding: 2px 3px;
        }

        .calendar th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .date {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 1px;
        }

        .cell-entry {
            font-size: 8.5px;
            line-height: 1.1;
        }

        .legend {
            margin-top: 10px;
            display: flex;
            justify-content: flex-start;
            gap: 40px;
            font-size: 8.5px;
        }

        .legend-column {
            width: 45%;
        }

        .legend ul {
            padding-left: 14px;
            margin: 0;
        }

        h2 {
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Header Judul -->
    <div class="header">
        <h2>Laporan Harian Produksi - {{ \Carbon\Carbon::createFromDate($year, $month)->isoFormat('MMMM Y') }}</h2>
    </div>

    <!-- Tabel Kalender -->
    <table class="calendar">
        <thead>
            <tr>
                <th>Senin</th>
                <th>Selasa</th>
                <th>Rabu</th>
                <th>Kamis</th>
                <th>Jumat</th>
                <th>Sabtu</th>
                <th>Minggu</th>
            </tr>
        </thead>
        <tbody>
            @php
                $day = 1;
                $started = false;
            @endphp

            @for ($week = 0; $week < 6; $week++)
                <tr>
                    @for ($dow = 0; $dow < 7; $dow++)
                        @php
                            $cell = '';
                            $dateStr = null;
                        @endphp

                        @if (!$started && $dow === $firstDayOfWeek)
                            @php $started = true; @endphp
                        @endif

                        @if ($started && $day <= $daysInMonth)
                            @php
                                $date = \Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                                $cell = $day;
                                $dateStr = $date;
                                $day++;
                            @endphp
                        @endif

                        <td>
                            @if ($cell)
                                <div class="date">{{ $cell }}</div>
                                @if (isset($data[$dateStr]))
                                    <div class="cell-entry">Hasil PRO: {{ $data[$dateStr]['gr'] }}</div>
                                    <div class="cell-entry">TP WHFG: {{ $data[$dateStr]['whfg'] }}</div>
                                    <div class="cell-entry">Total Value: {{ $data[$dateStr]['Total Value'] }}</div>
                                    {{-- <div class="cell-entry">Total Qty PRO: {{ $data[$dateStr]['total_qty'] }}</div> --}}
                                @endif
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-column">
            <ul>
                <li><strong>Hasil PRO</strong>: Akumulatif Hasil Produksi Harian (GR)</li>
                <li><strong>TP WHFG</strong>: Transfer Produk ke WHFG</li>
            </ul>
        </div>
        <div class="legend-column">
            <ul>
                <li><strong>Total Value</strong>: Total Value Hasil Konfirmasi</li>
                {{-- <li><strong>Total Qty PRO</strong>: Akumulatif Total Hasil Produksi</li> --}}
            </ul>
        </div>
    </div>

</body>
</html>
