<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail; // Ganti dengan Mailable Anda

class NotificationController extends Controller
{
    public function sendDailyReport(Request $request)
    {
        // Validasi request, sekarang dengan 'recipients'
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'details' => 'required|array',
            'recipients' => 'required|array',       // Pastikan recipients adalah array
            'recipients.*' => 'required|email',  // Pastikan setiap item adalah email valid
        ]);

        try {
            // Laravel Mail::to() bisa langsung menerima array email
            Mail::to($validated['recipients'])->send(new DailyReportMail($validated['date'], $validated['details']));

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim ke ' . count($validated['recipients']) . ' penerima.'
            ]);

        } catch (\Exception $e) {
            report($e); // Laporkan error untuk debugging
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: Terjadi kesalahan pada server.'
            ], 500);
        }
    }
}
