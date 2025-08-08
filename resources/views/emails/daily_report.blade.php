<!DOCTYPE html>
<html>
<head>
    <title>Laporan Produksi Harian</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
        .header { font-size: 24px; font-weight: bold; color: #2d3748; margin-bottom: 20px; }
        .content-table { width: 100%; border-collapse: collapse; }
        .content-table th, .content-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .content-table th { background-color: #f7fafc; }
        .footer { margin-top: 20px; font-size: 12px; color: #718096; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Laporan Produksi Harian</div>
        <p>Berikut adalah rincian laporan untuk tanggal: <strong>{{ $reportData['date'] }}</strong></p>
        <table class="content-table">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Goods Receipt (GR) PRO</td>
                    <td>{{ number_format($reportData['details']['gr'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Sold Value</td>
                    <td>$ {{ number_format($reportData['details']['Total Value'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Transfer to WHFG</td>
                    <td>{{ number_format($reportData['details']['whfg'], 0, ',', '.') }}</td>
                </tr>
                 <tr>
                    <td>Total Transfer Value</td>
                    <td>$ {{ number_format($reportData['details']['Sold Value'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <div class="footer">
            <p>Ini adalah email yang dibuat secara otomatis. Mohon untuk tidak membalas.</p>
        </div>
    </div>
</body>
</html>

