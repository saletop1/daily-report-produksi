<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL; // PERBAIKAN 1: Menambahkan 'use' untuk URL facade

class SupervisorLowValueAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $alertData;
    public $signedUrl;

    public function __construct($alertData)
    {
        $this->alertData = $alertData;

        // Membuat URL aman yang hanya valid selama 48 jam
        $date = array_key_first($alertData);
        $this->signedUrl = URL::temporarySignedRoute(
            'supervisor.notify-team', now()->addHours(48), ['date' => $date]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'PERINGATAN: Produksi Bernilai Rendah Terdeteksi');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.supervisor-alert');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // PERBAIKAN 2: Method attachments() sekarang berada di dalam class
    public function attachments(): array
    {
        return [];
    }
} // PERBAIKAN 3: Kurung kurawal penutup class dipindahkan ke sini
