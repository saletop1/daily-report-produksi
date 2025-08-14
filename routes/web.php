<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute web untuk aplikasi Anda.
|
*/

// Rute default akan mengarahkan ke halaman login jika belum masuk,
// atau ke dasbor jika sudah masuk.
Route::get('/', function () {
    return redirect('/login');
});

// Grup rute yang memerlukan autentikasi (harus login)
Route::middleware(['auth'])->group(function () {
    // Rute untuk dasbor utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute untuk kalender
    Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])
        ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.index');
    // Rute untuk menampilkan kalender utama
    Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])->name('calendar.index');
    // TAMBAHKAN BARIS DI BAWAH INI
    Route::get('/export/pdf/{year}/{month}', [CalendarController::class, 'exportPdf'])->name('calendar.exportPdf');
    // Rute untuk export PDF
    Route::get('/calendar/export/{year}/{month}', [CalendarController::class, 'exportPdf'])
        ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.export');
    Route::post('/send-email-notification', [NotificationController::class, 'sendDailyReport'])->name('api.notification.send');
    // Route::post('/send-email-notification', [NotificationController::class, 'sendEmailNotification'])->name('send.email.notification');
    // Route untuk menangani klik tombol dari email supervisor
    Route::get('/notify-team/{date}', [CalendarController::class, 'notifyTeamFromSupervisor'])
        ->name('supervisor.notify-team')
        ->middleware('signed'); // Middleware untuk memvalidasi URL
    Route::middleware(['auth'])->group(function () {
    // ...

    // Rute untuk Kalender dengan parameter Plant
    Route::get('/calendar/{plant?}/{year?}/{month?}', [CalendarController::class, 'index'])
        ->where(['plant' => '[0-9]+', 'year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.index');

    Route::get('/calendar/export/{plant}/{year}/{month}', [CalendarController::class, 'exportPdf'])
        ->where(['plant' => '[0-9]+', 'year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.exportPdf');

    // Redirect rute lama untuk backward compatibility
    Route::get('/calendar', function () {
        return redirect()->route('calendar.index', ['plant' => '3000']); // Arahkan ke plant default
    });
    Route::middleware(['auth'])->group(function () {
    // ... rute profil dan kalender

    // Rute untuk Dashboard Analisis
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Arahkan halaman root ke dashboard baru
Route::get('/', function () {
    return redirect()->route('dashboard');
});
});
});


// Rute autentikasi yang dibuat oleh Laravel Breeze
require __DIR__.'/auth.php';
