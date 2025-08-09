<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail; // <-- Jangan lupa di-import

class NotificationController extends Controller
{
    public function sendDailyReport(Request $request)
    {
        // ... (validasi request Anda)
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'details' => 'required|array',
            'recipients' => 'required|array',
            'recipients.*' => 'required|email',
        ]);

        // Siapkan data dalam format yang diharapkan oleh Mailable
        $reportData = [
            'date' => $validated['date'],
            'details' => $validated['details'],
        ];

        try {
            // Panggil Mailable dan kirim data
            Mail::to($validated['recipients'])->send(new DailyReportMail($reportData));

            return response()->json(['success' => true, 'message' => 'Email laporan berhasil dikirim.']);
        } catch (\Exception $e) {
            // ... (penanganan error)
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email.'], 500);
        }
    }
}
