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
        // Tentukan rentang tanggal dari request, atau default ke 7 hari terakhir
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::today();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $endDate->copy()->subDays(7);

        // Ambil data yang sudah diproses untuk kedua plant
        $plant3000Data = $this->getProductionDataForPlant('3000', $startDate, $endDate);
        $plant2000Data = $this->getProductionDataForPlant('2000', $startDate, $endDate);

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
            'trendChartData' => json_encode($trendData),
            'pieChartData' => json_encode($pieData),
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
            ->whereBetween('BUDAT_MKPF', [$startDate->toDateString(), $endDate->toDateString()])
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

        // Buat label tanggal untuk seluruh rentang
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $labels[] = $date->format('d M');
        }

        $colors = ['#3b82f6', '#10b981']; // Biru untuk Plant 3000, Hijau untuk Plant 2000
        $i = 0;

        foreach ($allPlantsData as $plantName => $dailyData) {
            $grData = [];
            foreach ($labels as $label) {
                $dateKey = Carbon::createFromFormat('d M', $label, 'Asia/Jakarta')->format('Y-m-d');
                $grData[] = $dailyData[$dateKey]['gr'] ?? 0;
            }

            $datasets[] = [
                'label' => $plantName . ' (GR)',
                'data' => $grData,
                'borderColor' => $colors[$i],
                'backgroundColor' => $colors[$i] . '33', // Transparan
                'fill' => true,
                'tension' => 0.3,
            ];
            $i++;
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }
}
