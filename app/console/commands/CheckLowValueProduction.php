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
        $this->info('Memulai pengecekan data produksi kemarin...');

        // MODIFIED: Define thresholds per plant in a configuration array
        $plantsConfig = [
            ['id' => '3000', 'threshold' => 50000],
            ['id' => '2000', 'threshold' => 2000],
        ];

        $dateToCheck = Carbon::yesterday();
        $this->line("Tanggal yang dicek: " . $dateToCheck->toDateString());

        $supervisor = ListEmail::where('role', 'supervisor')->where('is_active', true)->first();

        if (!$supervisor) {
            $this->error('KRITIS: Tidak ada supervisor aktif yang ditemukan di database.');
            Log::critical('Tidak ada supervisor aktif untuk notifikasi nilai rendah.');
            return self::FAILURE;
        }

        // MODIFIED: Loop through the configuration array
        foreach ($plantsConfig as $config) {
            $plant = $config['id'];
            $lowValueThreshold = $config['threshold'];

            $this->info("--- Mengecek Plant: {$plant} (Ambang Batas: <= " . number_format($lowValueThreshold) . ") ---");

            try {
                $nestedDailyData = $controller->getDailyDataForDate($dateToCheck, $plant);
                $dailyData = !empty($nestedDailyData) ? reset($nestedDailyData) : null;

                if ($dailyData) {
                    $dailyData['date'] = $dateToCheck->toDateString();
                }

                if (empty($dailyData) || !isset($dailyData['Total Value'])) {
                    $this->line("Tidak ada data produksi ('Total Value') ditemukan untuk Plant {$plant}.");
                    continue;
                }

                $currentValue = $dailyData['Total Value'];
                $this->line("Nilai produksi saat ini: " . number_format($currentValue, 2));

                // Logika pengecekan nilai rendah menggunakan threshold spesifik plant
                if ($currentValue <= $lowValueThreshold) {
                    $this->warn("DITEMUKAN: Nilai produksi di bawah atau sama dengan ambang batas. Mengirim peringatan...");

                    try {
                        Mail::to($supervisor->email)->send(new SupervisorLowValueAlert($dailyData, $plant));
                        $this->info("Peringatan untuk Plant {$plant} berhasil dikirim ke: " . $supervisor->email);
                    } catch (Throwable $e) {
                        $this->error("KRITIS: Gagal mengirim email untuk Plant {$plant}. Error: " . $e->getMessage());
                        Log::error("Kegagalan email supervisor untuk Plant {$plant}: " . $e->getMessage());
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
