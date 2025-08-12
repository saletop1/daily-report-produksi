<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dasbor dengan analisis data.
     */
    public function index(Request $request)
    {
        // 1. Validasi dan tentukan rentang tanggal
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

        // 2. Ambil dan proses data mentah dari database (hanya satu kali panggil)
        $dailyData = $this->getProcessedDailyData($startDate, $endDate);

        // 3. Inisialisasi variabel untuk semua data yang akan dikirim
        $totals = ['totalGr' => 0, 'totalWhfg' => 0, 'totalTransferValue' => 0, 'totalSoldValue' => 0];
        $lineChart = ['labels' => [], 'grData' => [], 'whfgData' => []];
        $dailyPieChart = ['labels' => [], 'data' => []];

        // 4. Proses data harian untuk total, grafik garis, dan grafik pai
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->addDay());
        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');

            // Siapkan label untuk grafik garis (menampilkan setiap hari dalam rentang)
            $lineChart['labels'][] = $date->format('d M');

            if (isset($dailyData[$dateKey])) {
                $dayData = $dailyData[$dateKey];

                // Hitung total keseluruhan
                $totals['totalGr']            += $dayData['gr'];
                $totals['totalWhfg']          += $dayData['whfg'];
                $totals['totalTransferValue'] += $dayData['transfer_value'];
                $totals['totalSoldValue']     += $dayData['sold_value'];

                // Tambahkan data ke grafik garis
                $lineChart['grData'][]   = $dayData['gr'];
                $lineChart['whfgData'][] = $dayData['whfg'];

                // Tambahkan data ke grafik pai harian (hanya untuk hari yang ada data)
                $dailyPieChart['labels'][] = $date->format('d M');
                $dailyPieChart['data'][]   = $dayData['sold_value'];
            } else {
                // Jika tidak ada data pada hari itu, isi data grafik garis dengan 0
                $lineChart['grData'][]   = 0;
                $lineChart['whfgData'][] = 0;
            }
        }

        // 5. Kirim semua data yang sudah dianalisis ke view
        return view('dashboard', [
            'totalGr'            => $totals['totalGr'],
            'totalWhfg'          => $totals['totalWhfg'],
            'totalTransferValue' => $totals['totalTransferValue'],
            'totalSoldValue'     => $totals['totalSoldValue'],
            'chartLabels'        => json_encode($lineChart['labels']),
            'chartGrData'        => json_encode($lineChart['grData']),
            'chartWhfgData'      => json_encode($lineChart['whfgData']),
            'dailyPieData'       => json_encode($dailyPieChart), // Data untuk pie chart
            'startDate'          => $startDate->format('Y-m-d'),
            'endDate'            => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Helper method untuk mengambil dan memproses data dari database.
     */
    private function getProcessedDailyData(Carbon $startDate, Carbon $endDate): array
    {
        $productionData = DB::table('sap_yppr009_data')
            ->select('BUDAT_MKPF', 'MENGEX', 'MENGE', 'VALUS', 'VALUSX')
            ->whereBetween('BUDAT_MKPF', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('BUDAT_MKPF', 'asc')
            ->get();

        $dailyData = [];
        foreach ($productionData as $item) {
            $tanggal = Carbon::parse($item->BUDAT_MKPF)->format('Y-m-d');

            if (!isset($dailyData[$tanggal])) {
                $dailyData[$tanggal] = [
                    'gr'             => 0,
                    'whfg'           => 0,
                    'transfer_value' => 0,
                    'sold_value'     => 0,
                ];
            }

            $dailyData[$tanggal]['gr']             += floatval($item->MENGE ?? 0);
            $dailyData[$tanggal]['whfg']           += floatval($item->MENGEX ?? 0);
            $dailyData[$tanggal]['transfer_value'] += floatval($item->VALUS ?? 0);
            $dailyData[$tanggal]['sold_value']     += floatval($item->VALUSX ?? 0);
        }

        return $dailyData;
    }
}
