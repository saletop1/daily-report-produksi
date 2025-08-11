<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style> /* ... (Gunakan style email dari jawaban sebelumnya) ... */ </style>
</head>
<body>
    <div class="container">
        <div class="header" style="color: #d9534f;">PERINGATAN UNTUK SUPERVISOR</div>
        <p>Halo Supervisor,</p>
        <p>Sistem telah mendeteksi data produksi kemarin dengan nilai rendah. Mohon untuk segera ditinjau.</p>

        <table class="table">
            <thead><tr><th>Tanggal</th><th>Total Sold Value</th></tr></thead>
            <tbody>
                @foreach ($alertData as $date => $details)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM YYYY') }}</td>
                        <td><strong>$ {{ number_format($details['Sold Value'], 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>Jika informasi ini perlu disebarkan, silakan klik tombol di bawah untuk mengirim notifikasi ke seluruh tim.</p>

        <a href="{{ $signedUrl }}" style="display: inline-block; padding: 12px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 15px;">
            Beritahu Tim
        </a>
    </div>
</body>
</html>
