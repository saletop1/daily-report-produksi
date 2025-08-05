<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'date' => Carbon::create(2025, 8, 1)->translatedFormat('l, d F Y'), // Jumat, 01 Agustus 2025
            'gr' => 21,
            'transfer_whfg' => 15,
            'total_qty_pro' => 139,
            'total_gr_qty_pro' => 123,
        ];

        return view('dashboard', $data);
    }

    public function calendar()
{
    $month = 8;
    $year = 2025;

    // Simulasi data per tanggal
    $data = [
        '2025-08-01' => ['gr' => 21, 'whfg' => 15, 'qty' => 139, 'gr_qty' => 123],
        '2025-08-02' => ['gr' => 10, 'whfg' => 8, 'qty' => 70, 'gr_qty' => 55],
        // Tambah data lain sesuai tanggal...
    ];

    $start = Carbon::create($year, $month, 1);
    $end = $start->copy()->endOfMonth();

    $daysInMonth = $end->day;
    $firstDayOfWeek = $start->dayOfWeek; // 0 = Minggu, 1 = Senin, dst

    return view('calendar', compact('month', 'year', 'daysInMonth', 'firstDayOfWeek', 'data'));
}

}

