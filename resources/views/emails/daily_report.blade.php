<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produksi Harian</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 1px solid #eeeeee; padding-bottom: 10px; }
        .header h1 { color: #333333; }
        .content { margin-top: 20px; }
        .content p { color: #555555; line-height: 1.6; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .report-table th, .report-table td { border: 1px solid #dddddd; text-align: left; padding: 12px; }
        .report-table th { background-color: #f7f7f7; color: #333; }
        .report-table tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #eeeeee; font-size: 12px; color: #999999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Produksi Harian</h1>
            <p>{{ \Carbon\Carbon::parse($reportData['date'])->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <div class="content">
            <p>Berikut adalah rincian laporan produksi untuk tanggal yang disebutkan di atas:</p>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Goods Receipt (GR) PRO</td>
                        <td><strong>{{ number_format($reportData['details']['gr'], 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Transfer to WHFG</td>
                        <td><strong>{{ number_format($reportData['details']['whfg'], 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Sold Value</td>
                        <td><strong>$ {{ number_format($reportData['details']['Total Value'], 2, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Transfer Value</td>
                        <td><strong>$ {{ number_format($reportData['details']['Sold Value'], 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-top: 25px;">Ini adalah email yang dibuat secara otomatis. Mohon untuk tidak membalas email ini. </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} PT. Kayu Mebel Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
