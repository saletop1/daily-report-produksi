<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController; // Ganti dengan Controller Anda

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Definisikan route untuk mengirim notifikasi dan beri nama 'api.notification.send'
Route::post('/send-email-notification', [NotificationController::class, 'sendDailyReport'])
     ->name('api.notification.send');
