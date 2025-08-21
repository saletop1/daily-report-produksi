<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CalendarController;
use App\Models\ListEmail;
use App\Mail\SupervisorLowValueAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CheckLowValueProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:check-low-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek data produksi harian per plant dan kirim notifikasi jika nilainya rendah';

    /**
     * Execute the console command.
     */
    public function handle(CalendarController $controller)
    {
        Log::info('COMMAND EXECUTED: production:check-low-value is running.');
        $this->info('Memulai pengecekan data produksi...');

        $plantsConfig = [
            ['id' => '3000', 'threshold' => 20000],
            ['id' => '2000', 'threshold' => 2000],
        ];

        $supervisors = ListEmail::where('role', 'supervisor')->where('is_active', true)->get();

        if ($supervisors->isEmpty()) {
            $this->error('KRITIS: Tidak ada supervisor aktif yang ditemukan di database.');
            Log::critical('Tidak ada supervisor aktif untuk notifikasi nilai rendah.');
            return self::FAILURE;
        }

        $this->info("Ditemukan " . $supervisors->count() . " supervisor aktif.");

        foreach ($plantsConfig as $config) {
            $plant = $config['id'];
            $lowValueThreshold = $config['threshold'];

            $this->info("--- Mengecek Plant: {$plant} (Ambang Batas: <= " . number_format($lowValueThreshold) . ") ---");

            try {
                // Logic to check for Sunday and get Saturday's data if needed
                $dateToCheck = Carbon::yesterday();
                $this->line("Tanggal pengecekan awal: " . $dateToCheck->toDateString());

                $nestedDailyData = $controller->getDailyDataForDate($dateToCheck, $plant);

                // If today is Monday (meaning yesterday was Sunday) and there is no data
                if (Carbon::today()->isMonday() && empty($nestedDailyData)) {
                    $this->warn("Tidak ada data untuk hari Minggu, mencoba memeriksa data hari Sabtu...");
                    $dateToCheck = Carbon::yesterday()->subDay(); // Go back to Saturday
                    $this->line("Tanggal pengecekan baru: " . $dateToCheck->toDateString());
                    $nestedDailyData = $controller->getDailyDataForDate($dateToCheck, $plant);
                }

                $dailyData = !empty($nestedDailyData) ? reset($nestedDailyData) : null;

                if ($dailyData) {
                    // Ensure the date sent in the email is the actual data date
                    $dailyData['date'] = $dateToCheck->toDateString();
                }

                if (empty($dailyData) || !isset($dailyData['Total Value'])) {
                    $this->line("Tidak ada data produksi ('Total Value') ditemukan untuk Plant {$plant} pada tanggal {$dateToCheck->toDateString()}.");
                    continue;
                }

                // === PERBAIKAN UTAMA DIMULAI DI SINI ===

                // 1. Ambil nilai asli yang mungkin berupa string berformat (e.g., "19,500.00")
                $originalValueString = $dailyData['Total Value'];

                // 2. Bersihkan string dari karakter non-numerik (kecuali titik desimal)
                // Ini akan mengubah "19,500.00" menjadi "19500.00"
                $sanitizedValue = preg_replace('/[^\d.]/', '', $originalValueString);

                // 3. Konversi string yang sudah bersih menjadi tipe data float untuk perbandingan akurat
                $currentValue = (float) $sanitizedValue;

                // Tambahkan log yang lebih deskriptif untuk debugging
                $this->line("Nilai produksi pada {$dateToCheck->toDateString()}: " . number_format($currentValue, 2) . " (Nilai Asli: '{$originalValueString}')");

                // 4. Lakukan perbandingan numerik yang akurat
                if ($currentValue <= $lowValueThreshold) {
                    $this->warn("DITEMUKAN: Nilai produksi di bawah ambang batas. Mengirim peringatan...");

                    foreach ($supervisors as $supervisor) {
                        try {
                            Mail::to($supervisor->email)->send(new SupervisorLowValueAlert($dailyData, $plant));
                            $this->info("Peringatan untuk Plant {$plant} berhasil dikirim ke: " . $supervisor->email);
                        } catch (Throwable $e) {
                            $this->error("KRITIS: Gagal mengirim email ke {$supervisor->email} untuk Plant {$plant}. Error: " . $e->getMessage());
                            Log::error("Kegagalan email supervisor untuk Plant {$plant}: " . $e->getMessage());
                        }
                    }
                } else {
                    $this->info("AMAN: Nilai produksi berada di atas ambang batas.");
                }

                // === AKHIR DARI PERBAIKAN ===

            } catch (Throwable $e) {
                $this->error("KRITIS: Terjadi error saat memproses Plant {$plant}. Error: " . $e->getMessage());
                Log::error("Kegagalan proses di CheckLowValueProduction untuk Plant {$plant}: " . $e->getMessage());
                continue;
            }
        }

        $this->info('Pengecekan selesai.');
        return self::SUCCESS;
    }
}
