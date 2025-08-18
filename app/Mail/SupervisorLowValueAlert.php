<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class SupervisorLowValueAlert extends Mailable
{
    use Queueable, SerializesModels;

    public array $alertData;
    public string $plant;
    public string $url; // PERBAIKAN: Mengganti nama variabel agar lebih jelas

    /**
     * Create a new message instance.
     */
    public function __construct(array $dailyData, string $plant)
    {
        $this->alertData = $dailyData;
        $this->plant = $plant;

        // PERBAIKAN: Mengubah URL agar langsung mengarah ke halaman kalender plant
        $this->url = route('calendar.index', ['plant' => $this->plant]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Peringatan: Nilai Produksi Rendah untuk Plant ' . $this->plant)
                    ->view('emails.supervisor-alert');
    }
}
