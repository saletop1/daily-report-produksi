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

### 4. Setting Database (MYSQL)

Database disediakan dalam dua versi 
1. Struktur + Data = daily_report_db (+Data).sql
2. Struktur only (kecuali user) = Structure_only.sql

>Sudah disediakan juga di migration dan modelnya, apabila terjadi error ketika melakukan konfirgurasi dengan file sql

```bash
#untuk menjalankan migration dan model gunakan script ini
php artisan migrate
#untuk menjalankan UserSeeder gunakan script ini
php artisan db:seed
```

### 5. Jalankan Server Lokal
```bash
php artisan serve
npm run dev
```

### ⚠️MODE UNTUK MENJALANKAN FILE PYTHON sync_historical.py
```bash
RUN MODE "scheduler" #hanya menjalankan pload data sesuai jadwal
RUN MODE "flask" #hanya menjalankan load data ketika API di hit
RUN MODE "both" #menjalankan load data ketika di hit atau sesuai jadwal
RUN MODE "manual" #menjalankan load data ketika py dijalankan

```
# HOW TO DEPLOY
## 1. LAKUKAN INSTALASI DEPENDENCY DI LOCAL (COMPOSER & NPM)

```bash
# lakukan di lokal
npm install
npm run build

composer install
composer install --optimize-autoloader --no-dev

# konfigurasi .env (pastikan sudah import file sql ke database)
```

## 2. Tahapan di Control Panel untuk deploy
- Pisahkan folder public dengan folder app
- Pastikan di dalam folder app mimiliki folder public yang berisi (file manifest.json dan folder asset) -> _diperoleh dari npm run build_
- perhatikan struktur file autoload dll agak mengarah ke folder app nya


> ⚠️ **IMPORTANT:** Jangan lupa jalankan file sync_historical.py ketika menjalankan atau deploy ke server atau hosting agar data selalu terupdate.
> python akan auto hit ke SAP pada jam 20.00 dan jam 03.00 untuk menghindari kendala mati listrik ketika dini hari 
> Jalankan `npm run dev` setelah mengedit file Tailwind agar style terkompilasi ulang.
