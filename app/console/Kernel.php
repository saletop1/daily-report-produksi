<?php

namespace App\Console;

// Tambahkan "use" di bawah ini
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    // =================================================================
    // === TAMBAHKAN SELURUH FUNGSI "schedule" INI DI BAWAH INI ===
    // =================================================================
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ini adalah baris yang menjalankan pengecekan Anda
        // Perintah ini akan berjalan setiap hari pada jam 1 pagi
        $schedule->command('production:check-low-value')->dailyAt('01:00');
    }
    // =================================================================
    // === AKHIR DARI PENAMBAHAN ===
    // =================================================================


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {

        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
