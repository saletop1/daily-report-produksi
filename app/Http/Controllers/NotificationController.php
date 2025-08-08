<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail; // Pastikan Anda sudah membuat Mailable ini
use Illuminate\Support\Facades\Log; // Gunakan Log untuk debugging
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mengirim notifikasi Email berdasarkan data dari frontend.
     */
    public function sendEmailNotification(Request $request)
    {
        // Validasi yang lebih spesifik untuk memastikan data ada
        $validated = $request->validate([
            'date' => 'required|string',
            'details' => 'required|array',
            'details.gr' => 'required|numeric',
            'details.whfg' => 'required|numeric',
            'details.Total Value' => 'required|numeric',
            'details.Sold Value' => 'required|numeric',
        ]);


        // Ambil alamat email tujuan dari file .env, atau gunakan default
        $recipientEmail = Auth::user()->email;

        try {
            // Kirim email menggunakan Mailable yang sudah dibuat
            // Data yang divalidasi langsung dikirim ke Mailable
            Mail::to($recipientEmail)->send(new DailyReportMail($validated));

            return response()->json(['success' => true, 'message' => 'Email laporan telah berhasil dikirim.']);

        } catch (\Exception $e) {
            // Laporkan error ke log Laravel untuk debugging
            Log::error('Email Sending Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email. Periksa konfigurasi server dan log.'], 500);
        }
    }
}
