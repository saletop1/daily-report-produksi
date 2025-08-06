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
    public function index()
    {
        // Tentukan rentang tanggal (misalnya, bulan ini)
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Ambil data produksi dari database
        $productionData = DB::table('sap_yppr009_data')
            ->select('BUDAT_MKPF', 'NETPR', 'MENGEX', 'MENGE', 'VALUS')
            ->whereBetween('BUDAT_MKPF', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('BUDAT_MKPF', 'asc')
            ->get();

        // Inisialisasi variabel untuk analisis
        $totalGr = 0;
        $totalWhfg = 0;
        $totalTransferValue = 0;
        $totalSoldCount = $productionData->count();

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
                    'transfer_value' => 0
                ];
            }

            $dailyData[$tanggal]['gr'] += floatval($item->MENGE ?? 0);
            $dailyData[$tanggal]['whfg'] += floatval($item->MENGEX ?? 0);
            $dailyData[$tanggal]['transfer_value'] += floatval($item->VALUS ?? 0);
        }

        // Siapkan data untuk grafik dan hitung total
        $currentDate = $startDate->copy();
        while($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $day = $currentDate->format('d');

            // Tambahkan label tanggal untuk grafik
            array_push($chartLabels, $day);

            if(isset($dailyData[$dateKey])) {
                $totalGr += $dailyData[$dateKey]['gr'];
                $totalWhfg += $dailyData[$dateKey]['whfg'];
                $totalTransferValue += $dailyData[$dateKey]['transfer_value'];

                array_push($chartGrData, $dailyData[$dateKey]['gr']);
                array_push($chartWhfgData, $dailyData[$dateKey]['whfg']);
            } else {
                array_push($chartGrData, 0);
                array_push($chartWhfgData, 0);
            }

            $currentDate->addDay();
        }

        // Kirim data yang sudah dianalisis ke view
        return view('dashboard', [
            'totalGr' => $totalGr,
            'totalWhfg' => $totalWhfg,
            'totalTransferValue' => $totalTransferValue,
            'totalSoldCount' => $totalSoldCount,
            'chartLabels' => json_encode($chartLabels),
            'chartGrData' => json_encode($chartGrData),
            'chartWhfgData' => json_encode($chartWhfgData),
        ]);
    }
}
