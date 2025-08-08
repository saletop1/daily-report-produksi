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

    // Rute untuk export PDF
    Route::get('/calendar/export/{year}/{month}', [CalendarController::class, 'exportPdf'])
        ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.export');
});


// Rute autentikasi yang dibuat oleh Laravel Breeze
require __DIR__.'/auth.php';
Route::post('/send-email-notification', [NotificationController::class, 'sendEmailNotification']);
