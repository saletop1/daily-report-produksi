<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard analisis produksi.
     */
    public function index(Request $request): View
    {
        // Tentukan rentang tanggal dari request untuk grafik utama
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::today();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $endDate->copy()->subDays(6);

        // Ambil data rangkuman untuk kedua plant berdasarkan filter tanggal
        $plant3000Data = $this->getProductionDataForPlant('3000', $startDate, $endDate);
        $plant2000Data = $this->getProductionDataForPlant('2000', $startDate, $endDate);

        // Panggil fungsi untuk tren mingguan (this week vs last week)
        $growthPlant3000 = $this->getWeeklyTotalValueTrend('3000');
        $growthPlant2000 = $this->getWeeklyTotalValueTrend('2000');

        // Siapkan data untuk grafik tren gabungan
        $trendData = $this->prepareTrendChartData([
            'Plant SMG' => $plant3000Data['daily'],
            'Plant SBY' => $plant2000Data['daily']
        ], $startDate, $endDate);

        // Siapkan data untuk diagram pai kontribusi nilai
        $pieData = [
            'labels' => ['Plant SMG', 'Plant SBY'],
            'data' => [
                $plant3000Data['totals']['totalValue'],
                $plant2000Data['totals']['totalValue']
            ]
        ];

        return view('dashboard.index', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'plant3000' => $plant3000Data,
            'plant2000' => $plant2000Data,
            'growthPlant3000' => $growthPlant3000,
            'growthPlant2000' => $growthPlant2000,
            'trendChartData' => $trendData,
            'pieChartData' => $pieData,
        ]);
    }

    /**
     * Mengambil dan memproses data produksi untuk satu plant dalam rentang tanggal.
     */
    private function getProductionDataForPlant(string $plantId, Carbon $startDate, Carbon $endDate): array
    {
        $query = DB::table('sap_yppr009_data')
            ->select(
                DB::raw('DATE(BUDAT_MKPF) as production_date'),
                DB::raw('SUM(MENGE) as total_gr'),
                DB::raw('SUM(MENGEX) as total_whfg'),
                DB::raw('SUM(VALUS) as total_value'),
                DB::raw('SUM(VALUSX) as total_sold_value')
            )
            ->where('WERKS', $plantId)
            ->whereDate('BUDAT_MKPF', '>=', $startDate)
            ->whereDate('BUDAT_MKPF', '<=', $endDate)
            ->groupBy('production_date')
            ->orderBy('production_date', 'asc')
            ->get();

        $dailyData = [];
        foreach ($query as $row) {
            $dailyData[$row->production_date] = [
                'gr' => (float) $row->total_gr,
                'whfg' => (float) $row->total_whfg,
            ];
        }

        $totals = [
            'totalGr' => $query->sum('total_gr'),
            'totalWhfg' => $query->sum('total_whfg'),
            'totalValue' => $query->sum('total_value'),
            'totalSoldValue' => $query->sum('total_sold_value'),
        ];

        return ['daily' => $dailyData, 'totals' => $totals];
    }

    /**
     * Menyiapkan data yang diformat untuk grafik tren garis (Chart.js).
     */
    private function prepareTrendChartData(array $allPlantsData, Carbon $startDate, Carbon $endDate): array
    {
        $labels = [];
        $datasets = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $labels[] = $date->format('d M');
        }

        $colors = ['#3b82f6', '#10b981'];
        $i = 0;

        foreach ($allPlantsData as $plantName => $dailyData) {
            $grData = [];
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                $grData[] = $dailyData[$dateKey]['gr'] ?? 0;
            }

            $datasets[] = [
                'label' => $plantName . ' (GR)',
                'data' => $grData,
                'borderColor' => $colors[$i],
                'backgroundColor' => $colors[$i] . '33',
                'fill' => true,
                'tension' => 0.3,
            ];
            $i++;
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * Menghitung tren total nilai minggu ini vs minggu lalu dan menyertakan detail nilainya.
     */
    private function getWeeklyTotalValueTrend(string $plantId): array
    {
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::SUNDAY);

        $startOfLastWeek = $startOfWeek->copy()->subWeek();

        // PERBAIKAN: Menggunakan metode yang lebih robust untuk menghitung tanggal akhir minggu lalu.
        // Ini akan menambahkan jumlah hari yang sama yang telah berlalu di minggu ini.
        $daysPassedThisWeek = $today->dayOfWeek; // Sunday=0, Monday=1, etc.
        $endOfLastWeekComparable = $startOfLastWeek->copy()->addDays($daysPassedThisWeek);

        $thisWeekValue = DB::table('sap_yppr009_data')
            ->where('WERKS', $plantId)
            ->whereBetween(DB::raw('DATE(BUDAT_MKPF)'), [
                $startOfWeek->format('Y-m-d'),
                $today->format('Y-m-d')
            ])
            ->sum('VALUS');

        $lastWeekValue = DB::table('sap_yppr009_data')
            ->where('WERKS', $plantId)
            ->whereBetween(DB::raw('DATE(BUDAT_MKPF)'), [
                $startOfLastWeek->format('Y-m-d'),
                $endOfLastWeekComparable->format('Y-m-d')
            ])
            ->sum('VALUS');

        $thisWeekValue = (float) ($thisWeekValue ?? 0);
        $lastWeekValue = (float) ($lastWeekValue ?? 0);

        $percentage = 0;
        if ($lastWeekValue > 0) {
            $percentage = (($thisWeekValue - $lastWeekValue) / $lastWeekValue) * 100;
        } elseif ($thisWeekValue > 0) {
            $percentage = 100;
        }

        $trend = 'stabil';
        if ($percentage > 0.5) {
            $trend = 'naik';
        } elseif ($percentage < -0.5) {
            $trend = 'turun';
        }

        return [
            'percentage'    => $percentage,
            'trend'         => $trend,
            'thisWeekValue' => $thisWeekValue,
            'lastWeekValue' => $lastWeekValue,
        ];
    }
}
