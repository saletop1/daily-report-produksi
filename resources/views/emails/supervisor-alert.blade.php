<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Produksi Rendah</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        .header {
            background-color: #d9534f;
            color: white;
            padding: 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
            color: #333;
        }
        .content p {
            margin: 0 0 15px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 25px;
        }
        .table th, .table td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #555;
        }
        .table td strong {
            font-weight: 600;
            color: #d9534f;
        }
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #888;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Peringatan Produksi Rendah</h1>
        </div>
        <div class="content">
            <p>Halo Supervisor,</p>
            <p>Sistem telah mendeteksi bahwa nilai produksi kemarin untuk <strong>Plant {{ $plant }}</strong> berada di bawah ambang batas yang ditetapkan. Mohon untuk segera ditinjau.</p>

            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Detail Produksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="width: 40%;">Tanggal</th>
                        <td>{{ \Carbon\Carbon::parse($alertData['date'])->isoFormat('dddd, D MMMM YYYY') }}</td>
                    </tr>
                    <tr>
                        <th>Plant</th>
                        <td>{{ $plant }}</td>
                    </tr>
                    <tr>
                        <th>Total Value GR</th>
                        <td><strong>$ {{ number_format($alertData['Total Value'], 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <p>Silakan akses kalender produksi untuk melihat detail lebih lanjut.</p>

            <div class="button-container">
                {{-- PERBAIKAN: Menggunakan variabel $url dan mengubah teks tombol --}}
                <a href="{{ $url }}" class="button">Buka Kalender Plant {{ $plant }}</a>
            </div>
        </div>
        <div class="footer">
            <p>Ini adalah email otomatis. Mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
