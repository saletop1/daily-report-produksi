<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ListEmail; // Menggunakan model ListEmail yang baru

class CalendarController extends Controller
{
    /**
     * Tampilkan kalender laporan produksi harian
     */
    public function index(Request $request, $year = null, $month = null)
    {
        // Gunakan tanggal saat ini jika tahun dan bulan tidak diberikan
        $date = ($year && $month) ? Carbon::createFromDate($year, $month, 1) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        $startDate = $date->copy()->startOfMonth();
        $endDate   = $date->copy()->endOfMonth();

        // Ambil data produksi
        $data = $this->getDailyData($startDate, $endDate);

        // Buat struktur data kalender
        $weeks = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::SUNDAY);

        while ($currentDate->lte($endDate)) {
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

        // Tentukan bulan sebelumnya dan berikutnya
        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        // PERBAIKAN: Mengambil daftar penerima dari tabel 'list_emails' yang baru
        $recipients = ListEmail::where('is_active', true)->get(['name', 'email']);

        return view('calendar.index', [
            'year'      => $year,
            'month'     => $month,
            'data'      => $data,
            'weeks'     => $weeks,
            'prevYear'  => $prevMonth->year,
            'prevMonth' => $prevMonth->month,
            'nextYear'  => $nextMonth->year,
            'nextMonth' => $nextMonth->month,
            'recipients'=> $recipients,
        ]);
    }

    /**
     * Export PDF dari tampilan kalender
     */
    public function exportPdf($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $data = $this->getDailyData($startDate, $endDate);

        $pdf = Pdf::loadView('calendar.pdf', [
            'year'           => $year,
            'month'          => $month,
            'data'           => $data,
            'firstDayOfWeek' => $startDate->dayOfWeek,
            'daysInMonth'    => $startDate->daysInMonth,
            'prevMonth'      => $startDate->copy()->subMonth(),
            'nextMonth'      => $startDate->copy()->addMonth(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Kalender-Produksi-{$year}-{$month}.pdf");
    }

    /**
     * Ambil dan rekap data produksi harian dari tabel SAP
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
                    'gr'          => 0, // MENGE (Goods Receipt)
                    'whfg'        => 0, // MENGEX (Finished Goods)
                    'Total Value' => 0, // VALUS
                    'Sold Value'  => 0, // VALUSX
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
