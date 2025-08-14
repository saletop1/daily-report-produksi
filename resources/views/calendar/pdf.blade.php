<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produksi Bulanan - {{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</title>
    {{-- Inline CSS untuk PDF --}}
    <style>
        /* Reset dan Pengaturan Font Dasar */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            -webkit-font-smoothing: antialiased;
            line-height: 1.5;
            background-color: #fff;
            margin: 0;
        }

        /* Kontainer Utama */
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Laporan */
        .report-header-table {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .report-header-table .logo {
            width: 60px;
        }
        .report-header-table .title-container {
            text-align: center;
        }
        .report-header-table h1 {
            font-size: 24px;
            margin: 0;
            color: #1a202c;
        }
        .report-header-table p {
            font-size: 14px;
            margin: 5px 0 0;
            color: #718096;
        }

        /* Tabel Rekapitulasi */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .summary-table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            font-size: 12px;
        }
        .summary-table td:first-child {
            font-weight: bold;
            color: #4a5568;
            width: 70%;
        }
        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        .total-gr { color: #2f855a; } /* Hijau */
        .total-whfg { color: #2b6cb0; } /* Biru */

        /* Kalender */
        .calendar-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        /* Header Nama Hari */
        .calendar-table th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #718096;
            padding: 8px;
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
        }
        /* Sel Tanggal */
        .calendar-table td {
            height: 85px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
            padding: 5px;
            position: relative;
        }
        .calendar-table .day-number {
            font-weight: bold;
            font-size: 12px;
            color: #4a5568;
            margin-bottom: 4px;
        }
        .day-number.sunday {
            color: #e53e3e; /* Merah untuk hari Minggu */
        }
        .calendar-table .day-content {
            font-size: 9px;
            line-height: 1.3;
        }
        .day-content ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .day-content li {
            margin-bottom: 3px;
        }
        .day-content .label {
            color: #4a5568;
        }
        .day-content .value {
            font-weight: bold;
        }
        .calendar-table .empty-day {
            background-color: #f7fafc;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <table class="report-header-table">
            <tr>
                <td class="logo">
                    {{-- MODIFIED: Added the logo with an absolute path --}}
                    <img src="{{ public_path('images/KMI.png') }}" alt="KMI Logo" style="width: 50px; height: auto;">
                </td>
                <td class="title-container">
                    <h1>Laporan Produksi Bulanan</h1>
                    <p>PT. Kayu Mebel Indonesia</p>
                    <p style="font-size: 18px; font-weight: bold; color: #2d3748;">{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</p>
                </td>
            </tr>
        </table>

        {{-- Rekapitulasi --}}
        <h2 style="font-size: 16px; margin-bottom: 10px; color: #2d3748;">Rekapitulasi Bulan Ini</h2>
        <table class="summary-table">
            <tbody>
                <tr>
                    <td>Total Goods Receipt (PRO)</td>
                    <td class="total-gr">{{ number_format($totals['totalGr'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Value</td>
                    <td class="total-gr">$ {{ number_format($totals['totalValue'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Transfer to WHFG</td>
                    <td class="total-whfg">{{ number_format($totals['totalWhfg'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Sold Value</td>
                    <td class="total-whfg">$ {{ number_format($totals['totalSoldValue'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Kalender --}}
        <table class="calendar-table">
            <thead>
                <tr>
                    @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
                        <th>{{ $dayName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($weeks as $week)
                    <tr>
                        @foreach ($week as $day)
                            @if ($day)
                                @php
                                    $dateKey = $day->format('Y-m-d');
                                    $hasData = isset($data[$dateKey]);
                                    $isSunday = $day->isSunday();
                                @endphp
                                <td>
                                    <div class="day-number {{ $isSunday ? 'sunday' : '' }}">{{ $day->day }}</div>
                                    @if ($hasData)
                                        <div class="day-content">
                                            <ul>
                                                <li>
                                                    <span class="label">GR:</span>
                                                    <span class="value">{{ number_format($data[$dateKey]['gr'], 0, ',', '.') }}</span>
                                                </li>
                                                <li>
                                                    <span class="label">Value:</span>
                                                    <span class="value">${{ number_format($data[$dateKey]['Total Value'], 0, ',', '.') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                            @else
                                <td class="empty-day"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            &copy; {{ date('Y') }} PT. Kayu Mebel Indonesia. All rights reserved. | Dokumen ini dibuat pada {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}
        </div>
    </div>
</body>
</html>
