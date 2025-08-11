public function handle(CalendarController $controller)
{
    $this->info('Memulai pengecekan data produksi kemarin...');
    $dateToCheck = Carbon::yesterday();
    $dailyData = $controller->getDailyDataForDate($dateToCheck);

    $lowValueData = array_filter($dailyData, function ($details) {
        return isset($details['Sold Value']) && $details['Sold Value'] <= 20000;
    });

    if (!empty($lowValueData)) {
        $this->warn('Ditemukan data bernilai rendah. Mengirim peringatan ke supervisor...');

        // Cari supervisor di database
        $supervisor = ListEmail::where('role', 'supervisor')->where('is_active', true)->first();

        if ($supervisor) {
            // Kirim email Peringatan HANYA ke supervisor
            Mail::to($supervisor->email)->send(new SupervisorLowValueAlert($lowValueData));
            $this->info('Peringatan berhasil dikirim ke supervisor: ' . $supervisor->email);
        } else {
            $this->error('Tidak ada supervisor aktif yang ditemukan di database.');
        }
    } else {
        $this->info('Tidak ada data produksi bernilai rendah yang ditemukan.');
    }
}
