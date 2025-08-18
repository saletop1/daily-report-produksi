<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

    Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])
        ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.index');
    Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/export/pdf/{year}/{month}', [CalendarController::class, 'exportPdf'])->name('calendar.exportPdf');
    Route::get('/calendar/export/{year}/{month}', [CalendarController::class, 'exportPdf'])
        ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.export');
    Route::post('/send-email-notification', [NotificationController::class, 'sendDailyReport'])->name('api.notification.send');
    Route::get('/notify-team/{date}', [CalendarController::class, 'notifyTeamFromSupervisor'])
        ->name('supervisor.notify-team')
        ->middleware('signed');
    Route::middleware(['auth'])->group(function () {
    Route::get('/calendar/notify/{plant}/{date}', [CalendarController::class, 'notifyTeamFromSupervisor'])
        ->name('calendar.supervisor.notify');
    Route::get('/calendar/{plant?}/{year?}/{month?}', [CalendarController::class, 'index'])
        ->where(['plant' => '[0-9]+', 'year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.index');
    Route::get('/calendar/export/{plant}/{year}/{month}', [CalendarController::class, 'exportPdf'])
        ->where(['plant' => '[0-9]+', 'year' => '[0-9]+', 'month' => '[0-9]+'])
        ->name('calendar.exportPdf');
    Route::get('/calendar', function () {
        return redirect()->route('calendar.index', ['plant' => '3000']); // Arahkan ke plant default
    });
    Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});


Route::get('/', function () {
    return redirect()->route('dashboard');
});
});
});

require __DIR__.'/auth.php';
