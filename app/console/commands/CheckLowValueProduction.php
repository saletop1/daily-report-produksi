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
        // PERUBAHAN: Menambahkan log untuk verifikasi eksekusi
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
                // Logika untuk mengecek hari Minggu dan mengambil data hari Sabtu jika perlu
                $dateToCheck = Carbon::yesterday();
                $this->line("Tanggal pengecekan awal: " . $dateToCheck->toDateString());

                $nestedDailyData = $controller->getDailyDataForDate($dateToCheck, $plant);

                // Jika hari ini Senin (artinya kemarin Minggu) dan tidak ada data
                if (Carbon::today()->isMonday() && empty($nestedDailyData)) {
                    $this->warn("Tidak ada data untuk hari Minggu, mencoba memeriksa data hari Sabtu...");
                    $dateToCheck = Carbon::yesterday()->subDay(); // Mundur ke hari Sabtu
                    $this->line("Tanggal pengecekan baru: " . $dateToCheck->toDateString());
                    $nestedDailyData = $controller->getDailyDataForDate($dateToCheck, $plant);
                }

                $dailyData = !empty($nestedDailyData) ? reset($nestedDailyData) : null;

                if ($dailyData) {
                    // Pastikan tanggal yang dikirim ke email adalah tanggal data yang sebenarnya
                    $dailyData['date'] = $dateToCheck->toDateString();
                }

                if (empty($dailyData) || !isset($dailyData['Total Value'])) {
                    $this->line("Tidak ada data produksi ('Total Value') ditemukan untuk Plant {$plant}.");
                    continue;
                }

                $currentValue = $dailyData['Total Value'];
                $this->line("Nilai produksi pada {$dateToCheck->toDateString()}: " . number_format($currentValue, 2));

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
