<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini adalah tempat Anda mendaftarkan web routes untuk aplikasi Anda.
|
*/

// Route 1: Halaman utama ('/')
// Semua pengunjung akan langsung diarahkan ke halaman login.
Route::get('/', function () {
    return redirect('/login');
});

// Grup Route yang Membutuhkan Autentikasi (Harus Login)
// Semua route di dalam grup ini dilindungi oleh middleware 'auth'.
Route::middleware(['auth'])->group(function () {

    // Route 2: Dashboard
    // Ini adalah halaman utama setelah user berhasil login.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route 3: Halaman Utama Kalender
    // Mengarahkan dari /calendar ke plant default untuk kemudahan.
    Route::get('/calendar', function () {
        return redirect()->route('calendar.index', ['plant' => '3000']);
    });

    // Route 4: Tampilan Kalender (Gabungan dari semua route kalender sebelumnya)
    // Mendukung parameter opsional untuk plant, tahun, dan bulan.
    Route::get('/calendar/{plant?}/{year?}/{month?}', [CalendarController::class, 'index'])
        ->where([
            'plant' => '[0-9]+',
            'year' => '[0-9]+',
            'month' => '[0-9]+'
        ])
        ->name('calendar.index');

    // Route 5: Ekspor PDF
    // Menggunakan parameter wajib untuk plant, tahun, dan bulan.
    Route::get('/calendar/export/{plant}/{year}/{month}', [CalendarController::class, 'exportPdf'])
        ->where([
            'plant' => '[0-9]+',
            'year' => '[0-9]+',
            'month' => '[0-9]+'
        ])
        ->name('calendar.exportPdf');

    // Route 6: Notifikasi Supervisor (dari link email)
    // Menggunakan middleware 'signed' untuk keamanan.
    Route::get('/notify-team/{plant}/{date}', [CalendarController::class, 'notifyTeamFromSupervisor'])
        ->name('supervisor.notify-team')
        ->middleware('signed');

    // Route 7: API untuk mengirim notifikasi (jika masih digunakan)
    Route::post('/send-email-notification', [NotificationController::class, 'sendDailyReport'])->name('api.notification.send');

});

// Memuat file route untuk autentikasi (login, register, dll.)
require __DIR__.'/auth.php';
