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
    public string $signedUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(array $dailyData, string $plant)
    {
        $this->alertData = $dailyData;
        $this->plant = $plant;

        $this->signedUrl = 'http://daily-report-gr.kmifilebox.com/calendar';

    //     // Buat signed URL untuk tombol "Beritahu Tim"
    //     $this->signedUrl = URL::temporarySignedRoute(
    //         'http://daily-report-gr.kmifilebox.com', // Pastikan nama rute ini benar
    //         now()->addHours(24),
    //         [
    //             'plant' => $this->plant,
    //             'date' => $this->alertData['date']
    //         ]
    //     );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Peringatan: Nilai Produksi Rendah untuk Plant ' . $this->plant)
                    ->view('emails.supervisor-alert'); // Arahkan ke view yang sudah diperbaiki
    }
}
