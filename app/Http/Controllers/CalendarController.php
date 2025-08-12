<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ListEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowValueProductionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman utama kalender produksi.
     */
    public function index(Request $request, ?int $year = null, ?int $month = null): View
    {
        $date = ($year && $month) ? Carbon::createFromDate($year, $month, 1) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        list($data, $weeks, $totals) = $this->getCalendarData($year, $month);

        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        $runningText = $this->getWeeklyChangeAnalysis();

        return view('calendar.index', [
            'year'        => $year,
            'month'       => $month,
            'data'        => $data,
            'weeks'       => $weeks,
            'totals'      => $totals,
            'prevYear'    => $prevMonth->year,
            'prevMonth'   => $prevMonth->month,
            'nextYear'    => $nextMonth->year,
            'nextMonth'   => $nextMonth->month,
            'recipients'  => $this->getActiveRecipients(),
            'runningText' => $runningText,
        ]);
    }

    /**
     * Mengekspor data kalender bulanan ke dalam format PDF.
     */
    public function exportPdf(int $year, int $month): Response
    {
        list($data, $weeks, $totals) = $this->getCalendarData($year, $month);
        $date = Carbon::createFromDate($year, $month, 1);

        $pdf = Pdf::loadView('exports.calendar-pdf', [
            'year'   => $year,
            'month'  => $month,
            'data'   => $data,
            'weeks'  => $weeks,
            'totals' => $totals,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Laporan-Produksi-' . $date->isoFormat('MMMM-YYYY') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Menerima aksi dari supervisor untuk mengirim notifikasi ke tim.
     */
    public function notifyTeamFromSupervisor(Request $request, string $date): string
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Link tidak valid atau sudah kedaluwarsa.');
        }

        $carbonDate = Carbon::parse($date);
        $dailyData = $this->getDailyDataForDate($carbonDate);
        $recipients = $this->getActiveRecipients(true);

        if (!empty($recipients) && !empty($dailyData)) {
            Mail::to($recipients)->send(new LowValueProductionNotification($dailyData));
            return "Notifikasi berhasil dikirim ke seluruh tim.";
        }

        return "Gagal mengirim notifikasi. Tidak ada data produksi pada tanggal tersebut atau tidak ada penerima email yang aktif.";
    }

    /**
     * Jembatan publik untuk mengambil data harian untuk satu tanggal spesifik.
     */
    public function getDailyDataForDate(Carbon $date): array
    {
        return $this->getDailyData($date, $date);
    }

    /**
     * Mengambil dan memproses semua data untuk kalender bulanan.
     */
    private function getCalendarData(int $year, int $month): array
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $dailyData = $this->getDailyData($startDate, $endDate);

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

        $totals = array_reduce($dailyData, function ($carry, $item) {
            $carry['totalGr'] += $item['gr'];
            $carry['totalWhfg'] += $item['whfg'];
            $carry['totalValue'] += $item['Total Value'];
            $carry['totalSoldValue'] += $item['Sold Value'];
            return $carry;
        }, ['totalGr' => 0, 'totalWhfg' => 0, 'totalValue' => 0, 'totalSoldValue' => 0]);

        return [$dailyData, $weeks, $totals];
    }

    /**
     * Membuat teks analisis perubahan persentase harian selama 7 hari terakhir.
     */
    private function getWeeklyChangeAnalysis(): string
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(8);
        $data = $this->getDailyData($startDate, $endDate);
        ksort($data);

        $dataPoints = array_values($data);
        $dateKeys = array_keys($data);
        $analysisText = [];

        for ($i = 1; $i < count($dataPoints); $i++) {
            $currentData = $dataPoints[$i];
            $previousData = $dataPoints[$i - 1];
            $currentDate = Carbon::parse($dateKeys[$i]);
            $currentValue = $currentData['Total Value'] ?? 0;
            $previousValue = $previousData['Total Value'] ?? 0;

            if ($previousValue > 0) {
                $percentageChange = (($currentValue - $previousValue) / $previousValue) * 100;
                $formattedDate = $currentDate->isoFormat('dddd, D MMMM');

                if ($percentageChange >= 0.01) {
                    $analysisText[] = "<span style='color: #4ade80; font-weight: 600;'>▲</span> {$formattedDate}: Naik " . number_format($percentageChange, 2) . "%";
                } elseif ($percentageChange <= -0.01) {
                    $analysisText[] = "<span style='color: #f87171; font-weight: 600;'>▼</span> {$formattedDate}: Turun " . number_format(abs($percentageChange), 2) . "%";
                }
            }
        }

        if (empty($analysisText)) {
            return 'Selamat datang di laporan hasil produksi harian PT. Kayu Mebel Indonesia.';
        }

        return implode(' &nbsp; • &nbsp; ', array_reverse($analysisText));
    }

    /**
     * Mengambil data mentah dari database untuk rentang tanggal tertentu.
     */
    private function getDailyData(Carbon $startDate, Carbon $endDate): array
    {
        $rawData = DB::table('sap_yppr009_data')
            ->select('BUDAT_MKPF', 'MENGEX', 'MENGE', 'VALUS', 'VALUSX')
            ->whereBetween('BUDAT_MKPF', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $data = [];
        foreach ($rawData as $item) {
            $tanggal = Carbon::parse($item->BUDAT_MKPF)->toDateString();
            if (!isset($data[$tanggal])) {
                $data[$tanggal] = ['gr' => 0, 'whfg' => 0, 'Total Value' => 0, 'Sold Value' => 0];
            }
            $data[$tanggal]['gr'] += floatval($item->MENGE ?? 0);
            $data[$tanggal]['whfg'] += floatval($item->MENGEX ?? 0);
            $data[$tanggal]['Total Value'] += floatval($item->VALUS ?? 0);
            $data[$tanggal]['Sold Value'] += floatval($item->VALUSX ?? 0);
        }
        return $data;
    }

    /**
     * Helper untuk mengambil daftar penerima email yang aktif.
     */
    private function getActiveRecipients(bool $asArray = false)
    {
        $query = ListEmail::where('is_active', true);
        return $asArray ? $query->pluck('email')->toArray() : $query->get(['name', 'email']);
    }
}
