<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Ini satu-satunya route untuk calendar
// Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])->name('calendar');
// Route::get('/calendar/export/pdf/{year}/{month}', [CalendarController::class, 'exportPdf'])->name('calendar.exportPdf');

// Route untuk menampilkan kalender
Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])
    ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
    ->name('calendar.index');

// Route untuk export PDF
Route::get('/calendar/export/{year}/{month}', [CalendarController::class, 'exportPdf'])
    ->where(['year' => '[0-9]+', 'month' => '[0-9]+'])
    ->name('calendar.export');
