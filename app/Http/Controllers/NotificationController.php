<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http; // Gunakan HTTP Client Laravel
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail;

// class NotificationController extends Controller
// {
//     /**
//      * Mengirim notifikasi WhatsApp berdasarkan data dari frontend.
//      */
//     public function sendWhatsAppNotification(Request $request)
//     {
//         // Validasi data yang masuk
//         $validated = $request->validate([
//             'date' => 'required|string',
//             'details' => 'required|array',
//             'details.gr' => 'required|numeric',
//             'details.Total Value' => 'required|numeric',
//         ]);

//         $date = $validated['date'];
//         $details = $validated['details'];

//         // --- KONFIGURASI WA GATEWAY ---
//         // Ganti bagian ini sesuai dengan penyedia layanan WhatsApp API Anda (misal: Twilio, Wablas, dll)
//         $waApiUrl = env('WHATSAPP_API_URL', 'https://api.yourwagateway.com/send');
//         $waApiToken = env('WHATSAPP_API_TOKEN', 'your-api-token');
//         // Nomor tujuan, bisa diambil dari database atau hardcode untuk testing
//         $targetNumber = env('WHATSAPP_TARGET_NUMBER', '6281234567890'); // Ganti dengan nomor tujuan

//         // Format pesan notifikasi
//         $message = "🔔 *Laporan Produksi Harian* 🔔\n\n";
//         $message .= "🗓️ *Tanggal:* {$date}\n\n";
//         $message .= "✅ *Goods Receipt (GR):* " . number_format($details['gr'], 0, ',', '.') . "\n";
//         $message .= "💰 *Total Sold Value:* $" . number_format($details['Total Value'], 0, ',', '.') . "\n\n";
//         $message .= "Terima kasih.";

//         try {
//             // Mengirim request ke WhatsApp Gateway menggunakan HTTP Client Laravel
//             $response = Http::withHeaders([
//                 'Authorization' => 'Bearer ' . $waApiToken, // Atau header lain sesuai dokumentasi API
//             ])->post($waApiUrl, [
//                 'phone' => $targetNumber,
//                 'message' => $message,
//                 // Parameter lain mungkin diperlukan, sesuaikan dengan dokumentasi API Anda
//             ]);

//             if ($response->successful()) {
//                 // Jika API merespon dengan sukses (status 2xx)
//                 return response()->json(['success' => true, 'message' => 'Notifikasi terkirim!']);
//             } else {
//                 // Jika API merespon dengan error
//                 $errorBody = $response->json();
//                 $errorMessage = $errorBody['message'] ?? 'Gagal mengirim pesan.';
//                 return response()->json(['success' => false, 'message' => $errorMessage], $response->status());
//             }

//         } catch (\Exception $e) {
//             // Jika terjadi error koneksi atau lainnya
//             report($e); // Laporkan error ke log Laravel
//             return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server.'], 500);
//         }
//     }
// }

class NotificationController extends Controller
{
    // ... (method sendWhatsAppNotification bisa Anda hapus atau biarkan)

    public function sendEmailNotification(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|string',
            'details' => 'required|array',
        ]);

        // Ambil alamat email tujuan dari file .env, atau gunakan default
        $recipientEmail = env('MAIL_REPORT_RECIPIENT', 'admin@example.com');

        try {
            // Kirim email menggunakan Mailable yang sudah dibuat
            Mail::to($recipientEmail)->send(new DailyReportMail($validated));

            return response()->json(['success' => true, 'message' => 'Email laporan telah dikirim.']);

        } catch (\Exception $e) {
            report($e); // Laporkan error ke log Laravel
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email. Periksa konfigurasi server.'], 500);
        }
    }
}
