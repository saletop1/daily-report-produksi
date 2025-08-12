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
    protected $description = 'Cek data produksi harian dan kirim notifikasi jika nilainya rendah';

    /**
     * Execute the console command.
     */
    public function handle(CalendarController $controller)
    {
        $this->info('Memulai pengecekan data produksi kemarin...');

        try {
            // Ambil ambang batas dari file config untuk fleksibilitas
            $lowValueThreshold = config('production.low_value_threshold', 20000);

            $dateToCheck = Carbon::yesterday();
            $dailyData = $controller->getDailyDataForDate($dateToCheck);

            $lowValueData = array_filter($dailyData, function ($details) use ($lowValueThreshold) {
                return isset($details['Sold Value']) && $details['Sold Value'] <= $lowValueThreshold;
            });

            // Jika tidak ada data bernilai rendah, hentikan proses dengan status sukses.
            if (empty($lowValueData)) {
                $this->info('Tidak ada data produksi bernilai rendah yang ditemukan.');
                return self::SUCCESS;
            }

            // Jika ada data, lanjutkan proses notifikasi.
            $this->warn('Ditemukan data bernilai rendah. Mengirim peringatan ke supervisor...');
            $supervisor = ListEmail::where('role', 'supervisor')->where('is_active', true)->first();

            if (!$supervisor) {
                $this->error('KRITIS: Tidak ada supervisor aktif yang ditemukan di database untuk dikirimi notifikasi.');
                Log::critical('Tidak ada supervisor aktif untuk notifikasi nilai rendah.');
                return self::FAILURE;
            }

            // Bungkus pengiriman email dengan try-catch untuk menangani kegagalan
            try {
                Mail::to($supervisor->email)->send(new SupervisorLowValueAlert($lowValueData));
                $this->info('Peringatan berhasil dikirim ke supervisor: ' . $supervisor->email);
            } catch (Throwable $e) {
                $this->error('KRITIS: Gagal mengirim email peringatan ke supervisor. Error: ' . $e->getMessage());
                Log::error('Kegagalan pengiriman email supervisor: ' . $e->getMessage());
                return self::FAILURE;
            }

        } catch (Throwable $e) {
            // Menangani error jika pengambilan data dari database gagal
            $this->error('KRITIS: Terjadi error saat mengambil data produksi. Error: ' . $e->getMessage());
            Log::error('Kegagalan mengambil data di CheckLowValueProduction: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Kembalikan status sukses jika semua berjalan lancar
        return self::SUCCESS;
    }
}
