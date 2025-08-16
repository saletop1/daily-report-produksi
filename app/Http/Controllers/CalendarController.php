<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ListEmail;
use Illuminate\Support\Facades\Http; // <-- TAMBAHKAN INI
use Illuminate\Support\Facades\Mail;
use App\Console\Commands\CheckLowValueProduction;
use App\Mail\DailyReportMail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman utama kalender produksi untuk plant tertentu.
     */
    public function index(Request $request, string $plant = '3000', ?int $year = null, ?int $month = null): View
    {
        $date = ($year && $month) ? Carbon::createFromDate($year, $month, 1) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        list($data, $weeks, $totals) = $this->getCalendarData($year, $month, $plant);

        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        $runningText = $this->getWeeklyChangeAnalysis($plant);

        // BARU: Panggil fungsi untuk mendapatkan data target harian
        $dailyTargetData = $this->getDailyTargetData($plant);

        return view('calendar.index', [
            'plant'           => $plant,
            'year'            => $year,
            'month'           => $month,
            'data'            => $data,
            'weeks'           => $weeks,
            'totals'          => $totals,
            'prevYear'        => $prevMonth->year,
            'prevMonth'       => $prevMonth->month,
            'nextYear'        => $nextMonth->year,
            'nextMonth'       => $nextMonth->month,
            'recipients'      => $this->getActiveRecipients(),
            'runningText'     => $runningText,
            'dailyTargetData' => $dailyTargetData, // BARU: Kirim data target ke view
        ]);
    }

    /**
     * BARU: Mengambil data target harian dari API Python.
     */
    private function getDailyTargetData(string $plant): array
    {
        try {
            // Panggil endpoint API yang sudah Anda buat di skrip Python
            $response = Http::timeout(5)->get('http://127.0.0.1:5051/api/daily_target_status');

            if ($response->successful()) {
                $allPlantsData = $response->json();
                // Cari data untuk plant yang spesifik
                foreach ($allPlantsData as $plantData) {
                    if (isset($plantData['plant_id']) && $plantData['plant_id'] == $plant) {
                        return [
                            'percentage'    => $plantData['percentage'] ?? 0,
                            'current_value' => $plantData['current_value'] ?? 0,
                            'target'        => $plantData['target'] ?? 0,
                            'error'         => null,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Tangani jika API tidak bisa dihubungi
            return [
                'percentage'    => 0,
                'current_value' => 0,
                'target'        => 0,
                'error'         => 'API service not available.',
            ];
        }

        // Default jika plant tidak ditemukan atau ada error lain
        return [
            'percentage'    => 0,
            'current_value' => 0,
            'target'        => 0,
            'error'         => 'Data for plant not found.',
        ];
    }

    /**
     * Mengekspor data kalender bulanan ke dalam format PDF.
     */
    public function exportPdf(string $plant, int $year, int $month): Response
    {
        list($data, $weeks, $totals) = $this->getCalendarData($year, $month, $plant);
        $date = Carbon::createFromDate($year, $month, 1);

        $pdf = Pdf::loadView('exports.calendar-pdf', [
            'plant'  => $plant,
            'year'   => $year,
            'month'  => $month,
            'data'   => $data,
            'weeks'  => $weeks,
            'totals' => $totals,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Laporan-Produksi-Plant-' . $plant . '-' . $date->isoFormat('MMMM-YYYY') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Menerima aksi dari supervisor untuk mengirim notifikasi ke tim.
     */
    public function notifyTeamFromSupervisor(Request $request, string $plant, string $date): string
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Link tidak valid atau sudah kedaluwarsa.');
        }

        $carbonDate = Carbon::parse($date);
        $dailyData = $this->getDailyDataForDate($carbonDate, $plant);
        $recipients = $this->getActiveRecipients(true);

        if (!empty($recipients) && !empty($dailyData)) {
            Mail::to($recipients)->send(new DailyReportMail($dailyData));
            return "Notifikasi berhasil dikirim ke seluruh tim.";
        }

        return "Gagal mengirim notifikasi. Tidak ada data produksi pada tanggal tersebut atau tidak ada penerima email yang aktif.";
    }

    /**
     * Jembatan publik untuk mengambil data harian untuk satu tanggal spesifik.
     */
    public function getDailyDataForDate(Carbon $date, string $plant): array
    {
        $data = $this->getDailyData($date, $date, $plant);
        if (!empty($data)) {
            return $data;
        }
        return [];
    }

    /**
     * Mengambil dan memproses semua data untuk kalender bulanan.
     */
    private function getCalendarData(int $year, int $month, string $plant): array
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $dailyData = $this->getDailyData($startDate, $endDate, $plant);

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

        $totals = array_reduce(array_values($dailyData), function ($carry, $item) {
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
    private function getWeeklyChangeAnalysis(string $plant): string
    {
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::SUNDAY);
        $dailyStartDate = $today->copy()->subDays(8);
        $data = $this->getDailyData($dailyStartDate, $today, $plant);
        ksort($data);

        $dataPoints = array_values($data);
        $dateKeys = array_keys($data);
        $analysisText = [];
        $weeklyPercentages = [];

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

                if ($currentDate->gte($startOfWeek)) {
                    $weeklyPercentages[] = $percentageChange;
                }
            }
        }

        $weeklyTrendText = '';
        if (!empty($weeklyPercentages)) {
            $averageTrend = array_sum($weeklyPercentages) / count($weeklyPercentages);
            if ($averageTrend >= 0.5) {
                $weeklyTrendText = "<span style='color: #4ade80; font-weight: 600;'>TREN RATA-RATA MINGGU INI ▲</span> Naik " . number_format($averageTrend, 2) . "%";
            } elseif ($averageTrend <= -0.5) {
                $weeklyTrendText = "<span style='color: #f87171; font-weight: 600;'>TREN RATA-RATA MINGGU INI ▼</span> Turun " . number_format(abs($averageTrend), 2) . "%";
            }
        }

        $finalText = array_reverse($analysisText);
        if(!empty($weeklyTrendText)) {
            $finalText[] = $weeklyTrendText;
        }

        if (empty($finalText)) {
            return 'Selamat datang di laporan hasil produksi harian Plant ' . $plant . ' PT. Kayu Mebel Indonesia.';
        }

        return implode(' &nbsp; • &nbsp; ', $finalText);
    }

    /**
     * Mengambil data mentah dari database untuk rentang tanggal dan plant tertentu.
     */
    private function getDailyData(Carbon $startDate, Carbon $endDate, string $plant): array
    {
        $rawData = DB::table('sap_yppr009_data')
            ->select('BUDAT_MKPF', 'MENGEX', 'MENGE', 'VALUS', 'VALUSX')
            ->where('WERKS', $plant)
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
