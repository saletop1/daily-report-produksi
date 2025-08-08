<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

Route::post('/send-email-notification', [NotificationController::class, 'sendEmailNotification']);
