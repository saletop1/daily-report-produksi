<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produksi Bulanan - {{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</title>
    {{-- Inline CSS untuk PDF --}}
    <style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 10px; /* DIKURANGI: Ukuran font dasar dikecilkan sedikit */
        color: #333;
        line-height: 1.4; /* DIKURANGI: Jarak antar baris dikurangi */
        background-color: #fff;
        margin: 0;
    }

    .container {
        width: 100%;
        margin: 0 auto;
        padding: 10px; /* DIKURANGI: Padding utama dikurangi */
    }

    .report-header {
        text-align: center;
        margin-bottom: 15px; /* DIKURANGI: Jarak bawah header dikurangi */
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 10px;
    }
    .report-header h1 {
        font-size: 18px; /* DIKURANGI: Ukuran font judul utama */
        margin: 0;
        color: #1a202c;
    }
    .report-header p {
        font-size: 12px; /* DIKURANGI: Ukuran font sub-judul */
        margin: 4px 0 0;
        color: #718096;
    }

    .summary-table {
        width: 75%;
        border-collapse: collapse;
        margin-bottom: 20px; /* DIKURANGI: Jarak bawah tabel rekap dikurangi */
        margin-right: auto;
    }
    .summary-table td {
        border: 1px solid #e2e8f0;
        padding: 5px; /* DIKURANGI: Padding sel rekap dikurangi */
        font-size: 10px;
        text-align: left;
    }
    .summary-table td:first-child {
        font-weight: bold;
        color: #4a5568;
        width: 30%;
    }
    .summary-table td:last-child {
        text-align: left;
        font-weight: bold;
        font-size: 12px;
    }
    .total-gr { color: #2f855a; }
    .total-whfg { color: #2b6cb0; }

    .calendar-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }
    .calendar-table th {
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10px;
        color: #718096;
        padding: 6px; /* DIKURANGI: Padding header kalender dikurangi */
        border: 1px solid #e2e8f0;
        background-color: #f7fafc;
    }
    .calendar-table td {
        /* PERUBAHAN PALING PENTING ADA DI SINI */
        height: 40px; /* DIKURANGI: Tinggi sel dari 85px menjadi 60px */
        vertical-align: top;
        border: 1px solid #e2e8f0;
        padding: 4px;
        overflow: hidden; /* Mencegah konten meluap dari sel */
    }
    .day-number {
        font-weight: bold;
        font-size: 9px;
        color: #4a5568;
        margin-bottom: 3px;
    }
    .day-number.sunday {
        color: #e53e3e;
    }
    .day-content {
        font-size: 8px; /* DIKURANGI: Ukuran font konten tanggal */
        line-height: 1.2;
    }
    .day-content ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .day-content li {
        margin-bottom: 2px;
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

    .footer {
        text-align: center;
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        font-size: 9px;
        color: #a0aec0;
    }
</style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="report-header">
            <h1>Laporan Produksi Bulanan</h1>
            <p>PT. Kayu Mebel Indonesia</p>
            <p style="font-size: 18px; font-weight: bold; color: #2d3748;">{{ \Carbon\Carbon::create($year, $month)->isoFormat('MMMM YYYY') }}</p>
        </div>

        {{-- Rekapitulasi --}}
        <h2 style="font-size: 18px; margin-bottom: 8px; color: #2d3748;">Rekapitulasi Bulan Ini</h2>
        <table class="summary-table">
            <tbody>
                <tr>
                    <td>Total Goods Receipt (PRO)</td>
                    <td class="total-gr">{{ number_format($totals['totalGr'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Sold Value</td>
                    <td class="total-gr">$ {{ number_format($totals['totalValue'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Transfer to WHFG</td>
                    <td class="total-whfg">{{ number_format($totals['totalWhfg'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Transfer Value</td>
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
                        $hasData = isset($data[$dateKey]); // Pastikan variabel $data ada
                        $isSunday = $day->isSunday();
                    @endphp
                    <td>
                        <div class="day-number {{ $isSunday ? 'sunday' : '' }}">{{ $day->day }}</div>

                        {{-- Ini adalah blok logika yang menampilkan data --}}
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
                        {{-- Akhir blok logika --}}

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
            &copy; {{ date('Y') }} PT. Kayu Mebel Indonesia. All rights reserved. | Dokumen ini dibuat pada {{ now()->isoFormat('D MMMM YYYY') }}
        </div>
    </div>
</body>
</html>
