<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDbQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test-query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan query langsung untuk mengetes koneksi DB dan visibilitas data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Mencoba menjalankan query langsung ke database yang dikonfigurasi di .env...");

        $plant = '3000';
        $date = '20250820'; // Tanggal yang kita tahu ada datanya

        try {
            // Menampilkan nama koneksi dan nama database yang sedang digunakan
            $connectionName = DB::connection()->getName();
            $databaseName = DB::connection()->getDatabaseName();
            $this->line("Koneksi yang digunakan: '{$connectionName}'");
            $this->line("Nama Database: '{$databaseName}'");

            $this->comment(" menjalankan query: SELECT * FROM sap_yppr009_data WHERE WERKS = '{$plant}' AND BUDAT_MKPF = '{$date}'");

            $results = DB::table('sap_yppr009_data')
                ->where('WERKS', $plant)
                ->where('BUDAT_MKPF', $date)
                ->get();

            $this->info("Query berhasil dijalankan.");
            $this->info("================ HASIL ===============");
            $this->info("Jumlah baris yang ditemukan oleh Laravel: " . $results->count());
            $this->info("========================================");

            // 'Dump and die' akan menghentikan script dan menampilkan seluruh isinya
            // Ini adalah bukti paling akurat
            dd($results);

        } catch (\Exception $e) {
            $this->error("KRITIS: Terjadi error saat menjalankan query:");
            $this->error($e->getMessage());
            Log::error('DB Test Query Failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
