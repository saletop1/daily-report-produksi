<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ListEmail;

class CalendarController extends Controller
{
    /**
     * Tampilkan kalender laporan produksi harian.
     */
    public function index(Request $request, $year = null, $month = null)
    {
        $date = ($year && $month) ? Carbon::createFromDate($year, $month, 1) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        // Ambil semua data kalender (data harian, struktur minggu, dan total) dari fungsi baru.
        list($data, $weeks, $totals) = $this->getCalendarData($year, $month);

        // Tentukan bulan sebelumnya dan berikutnya untuk navigasi.
        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        $recipients = ListEmail::where('is_active', true)->get(['name', 'email']);

        // Kirim semua data yang diperlukan ke view.
        return view('calendar.index', [
            'year'      => $year,
            'month'     => $month,
            'data'      => $data,
            'weeks'     => $weeks,
            'totals'    => $totals, // Variabel baru untuk rekapitulasi.
            'prevYear'  => $prevMonth->year,
            'prevMonth' => $prevMonth->month,
            'nextYear'  => $nextMonth->year,
            'nextMonth' => $nextMonth->month,
            'recipients'=> $recipients,
        ]);
    }

    /**
     * Export PDF dari tampilan kalender.
     */
     public function exportPdf($year, $month)
    {
        // Ambil semua data (termasuk $weeks dan $totals) dari fungsi helper.
        list($data, $weeks, $totals) = $this->getCalendarData($year, $month);

        $date = Carbon::createFromDate($year, $month, 1);

        // Kirim semua data yang dibutuhkan ke view 'exports.calendar-pdf'.
        $pdf = Pdf::loadView('exports.calendar-pdf', [
            'year'   => $year,
            'month'  => $month,
            'data'   => $data,
            'weeks'  => $weeks, // Variabel penting ini sekarang dikirim
            'totals' => $totals,
        ])->setPaper('a4', 'landscape'); // Atur orientasi

        // Nama file untuk diunduh.
        $fileName = 'Laporan-Produksi-' . $date->isoFormat('MMMM-YYYY') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * FUNGSI BARU: Mengambil, memproses, dan menyatukan semua data untuk kalender.
     */
    private function getCalendarData($year, $month)
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // 1. Ambil data harian dari database.
        $dailyData = $this->getDailyData($startDate, $endDate);

        // 2. Buat struktur data kalender (array $weeks).
        $weeks = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        $loopEndDate = $endDate->copy()->endOfWeek(Carbon::SATURDAY);

        while ($currentDate->lte($loopEndDate)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                if ($currentDate->month === $month) {
                    $week[] = $currentDate->copy();
                } else {
                    $week[] = null;
                }
                $currentDate->addDay();
            }
            $weeks[] = $week;
        }

        // 3. Hitung total bulanan dari data harian.
        $totals = [
            'totalGr'        => 0,
            'totalWhfg'      => 0,
            'totalValue'     => 0,
            'totalSoldValue' => 0,
        ];

        foreach ($dailyData as $item) {
            $totals['totalGr']        += $item['gr'];
            $totals['totalWhfg']      += $item['whfg'];
            $totals['totalValue']     += $item['Total Value'];
            $totals['totalSoldValue'] += $item['Sold Value'];
        }

        // 4. Kembalikan semua data yang sudah diproses.
        return [$dailyData, $weeks, $totals];
    }

    /**
     * Ambil dan rekap data produksi harian dari tabel SAP.
     * (Fungsi ini tidak berubah, sudah bagus).
     */
    private function getDailyData($startDate, $endDate)
    {
        $rawData = DB::table('sap_yppr009_data')
            ->select('BUDAT_MKPF', 'NETPR', 'MENGEX', 'MENGE', 'VALUS', 'VALUSX')
            ->whereBetween('BUDAT_MKPF', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->get();

        $data = [];

        foreach ($rawData as $item) {
            try {
                $tanggal = Carbon::parse($item->BUDAT_MKPF)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }

            if (!isset($data[$tanggal])) {
                $data[$tanggal] = [
                    'gr'          => 0,
                    'whfg'        => 0,
                    'Total Value' => 0,
                    'Sold Value'  => 0,
                ];
            }

            $data[$tanggal]['gr']          += floatval($item->MENGE ?? 0);
            $data[$tanggal]['whfg']        += floatval($item->MENGEX ?? 0);
            $data[$tanggal]['Total Value'] += floatval($item->VALUS ?? 0);
            $data[$tanggal]['Sold Value']  += floatval($item->VALUSX ?? 0);
        }

        return $data;
    }
}
