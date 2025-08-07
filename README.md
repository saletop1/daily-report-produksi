# 🧾 Daily Report Produksi PT. Kayu Mabel Indonesia

Aplikasi berbasis Laravel untuk menampilkan laporan harian produksi. Sistem ini ditujukan untuk tim produksi guna memantau, mencatat, dan merekap aktivitas produksi secara efisien dan terstruktur serta memberikan informasi data dalam bentuk visual sehingga mudah untuk dipahami oleh pengguna.

---

## 🚀 Fitur Utama

- 📊 Rekap dan histori laporan produksi
- 🔍 Filter data berdasarkan tanggal/parameter tertentu
- 📂 Sinkronisasi data SAP menggunakan Restfull API dari FLASK
- 🖥️ UI ringan dan modern menggunakan Blade + Tailwind CSS

---

## 🛠️ Teknologi yang Digunakan

| Komponen         | Teknologi                           |
|------------------|-------------------------------------|
| Backend          | Laravel (PHP 8+)                    |
| Frontend         | Blade Template Engine               |
| Styling          | Tailwind CSS                        |
| Build Tools      | Vite / Laravel Mix                  |
| Data Sync        | Python (`sync_historical.py`)       |
| Database         | MySQL (atau sejenisnya)             |

---

## 🧑‍💻 Instalasi & Setup

### 1. Clone Repositori

```bash
git clone https://github.com/saletop1/daily-report-produksi.git
cd daily-report-produksi
```

### 2. Install Dependency
```bash
composer install
npm install
```

### 3. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Import Database (Karena Database tidak dibangun dari migration laravel)

_File dikirimkan secara personal_

### 5. Jalankan Server Lokal
```bash
php artisan serve
npm run dev
```

> ⚠️ **IMPORTANT:** Jangan lupa jalankan file sync_historical.py ketika menjalankan atau deploy ke server atau hosting agar data selalu terupdate.
> python akan auto hit ke SAP pada jam 20.00 dan jam 03.00 untuk menghindari kendala mati listrik ketika dini hari 
> `npm run dev` setelah mengedit file Tailwind agar style terkompilasi ulang.