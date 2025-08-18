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
        // Tentukan rentang tanggal dari request
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::today();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $endDate->copy()->subDays(6);

        // Ambil data rangkuman
        $plant3000Data = $this->getProductionDataForPlant('3000', $startDate, $endDate);
        $plant2000Data = $this->getProductionDataForPlant('2000', $startDate, $endDate);

        // Ambil data tren mingguan
        $growthPlant3000 = $this->getWeeklyTotalValueTrend('3000');
        $growthPlant2000 = $this->getWeeklyTotalValueTrend('2000');

        // Ambil data top 3 customer untuk masing-masing plant
        $topCustomers3000 = $this->getTopCustomersByValue('3000', $startDate, $endDate);
        $topCustomers2000 = $this->getTopCustomersByValue('2000', $startDate, $endDate);

        // Siapkan data untuk grafik
        $trendData = $this->prepareTrendChartData([
            'Plant SMG' => $plant3000Data['daily'],
            'Plant SBY' => $plant2000Data['daily']
        ], $startDate, $endDate);
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
            'topCustomers3000' => $topCustomers3000,
            'topCustomers2000' => $topCustomers2000,
        ]);
    }

    /**
     * Mengambil 3 customer teratas berdasarkan total nilai (VALUS).
     */
    private function getTopCustomersByValue(string $plantId, Carbon $startDate, Carbon $endDate): array
    {
        $topCustomers = DB::table('sap_yppr009_data')
            ->select('NAME2', DB::raw('SUM(VALUS) as total_value'))
            ->where('WERKS', $plantId)
            ->whereNotNull('NAME2')
            ->where('NAME2', '!=', '')
            ->whereDate('BUDAT_MKPF', '>=', $startDate)
            ->whereDate('BUDAT_MKPF', '<=', $endDate)
            ->groupBy('NAME2')
            ->orderBy('total_value', 'desc')
            ->limit(4)
            ->get();

        return [
            'labels' => $topCustomers->pluck('NAME2')->map(function ($name) {
                return substr($name, 0, 20) . (strlen($name) > 20 ? '...' : '');
            }),
            'data'   => $topCustomers->pluck('total_value'),
        ];
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
     * PERBAIKAN: Menghitung tren total nilai dari 7 hari terakhir vs 7 hari sebelumnya.
     */
    private function getWeeklyTotalValueTrend(string $plantId): array
    {
        // Periode saat ini: 7 hari terakhir termasuk hari ini
        $currentPeriodEnd = Carbon::today()->endOfDay();
        $currentPeriodStart = Carbon::today()->subDays(6)->startOfDay();

        // Periode sebelumnya: 7 hari sebelum periode saat ini
        $previousPeriodEnd = $currentPeriodStart->copy()->subSecond();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays(6)->startOfDay();

        // Query untuk periode saat ini
        $thisWeekValue = (float) DB::table('sap_yppr009_data')
            ->where('WERKS', $plantId)
            ->whereBetween('BUDAT_MKPF', [$currentPeriodStart->toDateTimeString(), $currentPeriodEnd->toDateTimeString()])
            ->sum('VALUS');

        // Query untuk periode sebelumnya
        $lastWeekValue = (float) DB::table('sap_yppr009_data')
            ->where('WERKS', $plantId)
            ->whereBetween('BUDAT_MKPF', [$previousPeriodStart->toDateTimeString(), $previousPeriodEnd->toDateTimeString()])
            ->sum('VALUS');

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
