<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { font-size: 24px; font-weight: bold; color: #d9534f; margin-bottom: 20px; text-align: center; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .table th { background-color: #f7f7f7; }
        .button { display: inline-block; padding: 12px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { margin-top: 20px; font-size: 12px; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            PERINGATAN: Produksi Bernilai Rendah
        </div>
        <p>Halo Supervisor,</p>
        <p>Sistem telah mendeteksi data produksi kemarin dengan nilai rendah. Mohon untuk segera ditinjau.</p>

        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Total Sold Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($alertData as $date => $details)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM YYYY') }}</td>
                        <td><strong>$ {{ number_format($details['Sold Value'], 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>Jika informasi ini perlu disebarkan, silakan klik tombol di bawah untuk mengirim notifikasi ke seluruh tim. Link ini valid selama 48 jam.</p>

        <a href="{{ $signedUrl }}" class="button">
            Beritahu Tim
        </a>

        <div class="footer">
            Ini adalah notifikasi otomatis dari Sistem Laporan Produksi.
        </div>
    </div>
</body>
</html>
