<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian Produksi - {{ \Carbon\Carbon::createFromDate($year, $month)->isoFormat('MMMM Y') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 6mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: white;
            color: #333;
            line-height: 1.2;
        }

        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 4px;
            flex-shrink: 0;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-top: 2px;
            font-weight: normal;
        }

        /* Calendar Table */
        .calendar {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        .calendar th,
        .calendar td {
            border: 1px solid #374151;
            vertical-align: top;
            padding: 0;
            position: relative;
        }

        /* Header Days - Default Blue */
        .calendar th {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: black;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            padding: 6px 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            height: 25px;
        }

        /* Sunday Header - Red */
        .calendar th:first-child {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: red;
        }

        /* Calendar Cells */
        .calendar td {
            width: 14.28%;
            height: 85px;
            background: white;
            position: relative;
        }

        /* Sunday Cell Styling - Light Red Background */
        .calendar td:first-child {
            background: #fef2f2;
        }

        /* Date Number */
        .date {
            position: absolute;
            top: 2px;
            left: 4px;
            font-weight: bold;
            font-size: 10px;
            color: #1f2937;
            background: #f8fafc;
            padding: 1px 4px;
            border-radius: 3px;
            border: 1px solid #e2e8f0;
            min-width: 16px;
            text-align: center;
            z-index: 2;
        }

        /* Sunday Date Styling - Red */
        .calendar td:first-child .date {
            background: #fecaca;
            border-color: #f87171;
            color: #991b1b;
        }

        /* Content Area */
        .content {
            position: absolute;
            top: 22px;
            left: 2px;
            right: 2px;
            bottom: 2px;
            overflow: hidden;
        }

        /* Entry Items */
        .cell-entry {
            font-size: 12px;
            line-height: 1.1;
            margin-bottom: 1px;
            padding: 1px 3px;
            border-radius: 2px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cell-entry .label {
            font-weight: bold;
            color: #374151;
            flex: 0 0 auto;
            margin-right: 2px;
            font-size: 6.5px;
        }

        .cell-entry .value {
            color: #059669;
            font-weight: 600;
            text-align: right;
            flex: 1;
            font-size: 12px;
        }

        /* Different colors for different metrics */
        .cell-entry.pro {
            background: #ecfdf5;
            border-left: 2px solid #10b981;
        }

        .cell-entry.whfg {
            background: #eff6ff;
            border-left: 2px solid #3b82f6;
        }

        .cell-entry.value {
            background: #fef3c7;
            border-left: 2px solid #f59e0b;
        }

        .cell-entry.whfg .value {
            color: #2563eb;
        }

        .cell-entry.value .value {
            color: #d97706;
        }

        /* Empty cells */
        .calendar td:empty {
            background: #f8fafc;
        }

        /* Sunday empty cells */
        .calendar td:first-child:empty {
            background: #fef2f2;
        }

        /* Legend Section */
        .legend {
            margin-top: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px;
            flex-shrink: 0;
        }

        .legend h3 {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 4px 0;
            color: #1e40af;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .legend-content {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .legend-column {
            flex: 1;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
            padding: 2px 0;
        }

        .legend-indicator {
            width: 12px;
            height: 8px;
            border-radius: 2px;
            margin-right: 6px;
            border-left: 2px solid;
            flex-shrink: 0;
        }

        .legend-indicator.pro {
            background: #ecfdf5;
            border-color: #10b981;
        }

        .legend-indicator.whfg {
            background: #eff6ff;
            border-color: #3b82f6;
        }

        .legend-indicator.value {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .legend-text {
            font-size: 12px;
            color: #374151;
            line-height: 1.2;
        }

        .legend-text strong {
            color: #1f2937;
        }

        /* Print optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .calendar td {
                height: 80px !important;
            }
            
            @page {
                margin: 6mm 4mm;
            }

            .container {
                height: 100vh;
            }
        }

        /* Today highlighting */
        .today {
            background: #fef9c3 !important;
            border: 2px solid #eab308 !important;
        }

        .today .date {
            background: #fbbf24;
            color: white;
            font-weight: bold;
        }

        /* No data styling */
        .no-data {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #9ca3af;
            font-size: 12px;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>Laporan Harian Produksi</h1>
            <div class="subtitle">Periode {{ \Carbon\Carbon::createFromDate($year, $month)->isoFormat('MMMM Y') }}</div>
        </div>

        <!-- Calendar Table -->
        <table class="calendar">
            <thead>
                <tr>
                    <th>Minggu</th>
                    <th>Senin</th>
                    <th>Selasa</th>
                    <th>Rabu</th>
                    <th>Kamis</th>
                    <th>Jumat</th>
                    <th>Sabtu</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $day = 1;
                    $started = false;
                    $today = \Carbon\Carbon::now()->format('Y-m-d');
                @endphp

                @for ($week = 0; $week < 6; $week++)
                    <tr>
                        @for ($dow = 0; $dow < 7; $dow++)
                            @php
                                $cell = '';
                                $dateStr = null;
                                $isToday = false;
                            @endphp

                            @if (!$started && $dow === $firstDayOfWeek)
                                @php $started = true; @endphp
                            @endif

                            @if ($started && $day <= $daysInMonth)
                                @php
                                    $date = \Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                                    $cell = $day;
                                    $dateStr = $date;
                                    $isToday = ($dateStr === $today);
                                    $day++;
                                @endphp
                            @endif

                            <td class="{{ $isToday ? 'today' : '' }}">
                                @if ($cell)
                                    <div class="date">{{ $cell }}</div>
                                    <div class="content">
                                        @if (isset($data[$dateStr]))
                                            <div class="cell-entry pro">
                                                <span class="label">PRO:</span>
                                                <span class="value">{{ number_format($data[$dateStr]['gr'], 0, ',', '.') }}</span>
                                            </div>
                                            <div class="cell-entry whfg">
                                                <span class="label">WHFG:</span>
                                                <span class="value">{{ number_format($data[$dateStr]['whfg'], 0, ',', '.') }}</span>
                                            </div>
                                            <div class="cell-entry value">
                                                <span class="label">Value:</span>
                                                <span class="value">{{ number_format($data[$dateStr]['Total Value'], 0, ',', '.') }}</span>
                                            </div>
                                        @else
                                            <div class="no-data">No Data</div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</body>
</html>