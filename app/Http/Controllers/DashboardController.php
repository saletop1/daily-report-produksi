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
        // Validasi dan tentukan rentang tanggal dari request
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();

        // [FIXED] Ambil data produksi dari database sesuai rentang, tambahkan VALUSX
        $productionData = DB::table('sap_yppr009_data')
            ->select('BUDAT_MKPF', 'NETPR', 'MENGEX', 'MENGE', 'VALUS', 'VALUSX')
            ->whereBetween('BUDAT_MKPF', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('BUDAT_MKPF', 'asc')
            ->get();

        // Inisialisasi variabel untuk analisis
        $totalGr = 0;
        $totalWhfg = 0;
        $totalTransferValue = 0;
        $totalSoldValue = 0;

        // Variabel untuk data grafik
        $chartLabels = [];
        $chartGrData = [];
        $chartWhfgData = [];

        // Proses data untuk analisis dan grafik
        $dailyData = [];
        foreach ($productionData as $item) {
            $tanggal = Carbon::parse($item->BUDAT_MKPF)->format('Y-m-d');

            if (!isset($dailyData[$tanggal])) {
                $dailyData[$tanggal] = [
                    'gr' => 0,
                    'whfg' => 0,
                    'transfer_value' => 0,
                    'sold_value' => 0,
                ];
            }

            $dailyData[$tanggal]['gr'] += floatval($item->MENGE ?? 0);
            $dailyData[$tanggal]['whfg'] += floatval($item->MENGEX ?? 0);
            $dailyData[$tanggal]['transfer_value'] += floatval($item->VALUS ?? 0);
            // [FIXED] Menggunakan VALUSX untuk sold_value
            $dailyData[$tanggal]['sold_value'] += floatval($item->VALUSX ?? 0);
        }

        // Siapkan data untuk grafik dan hitung total
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->addDay());
        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayLabel = $date->format('d M');

            array_push($chartLabels, $dayLabel);

            if(isset($dailyData[$dateKey])) {
                $totalGr += $dailyData[$dateKey]['gr'];
                $totalWhfg += $dailyData[$dateKey]['whfg'];
                $totalTransferValue += $dailyData[$dateKey]['transfer_value'];
                $totalSoldValue += $dailyData[$dateKey]['sold_value'];

                array_push($chartGrData, $dailyData[$dateKey]['gr']);
                array_push($chartWhfgData, $dailyData[$dateKey]['whfg']);
            } else {
                array_push($chartGrData, 0);
                array_push($chartWhfgData, 0);
            }
        }

        // Kirim data yang sudah dianalisis ke view
        return view('dashboard', [
            'totalGr' => $totalGr,
            'totalWhfg' => $totalWhfg,
            'totalTransferValue' => $totalTransferValue,
            'totalSoldValue' => $totalSoldValue,
            'chartLabels' => json_encode($chartLabels),
            'chartGrData' => json_encode($chartGrData),
            'chartWhfgData' => json_encode($chartWhfgData),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }
}
