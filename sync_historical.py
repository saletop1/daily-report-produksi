import os
import traceback
import logging
import schedule
import time
import threading
from datetime import datetime, timedelta
from flask import Flask, request, jsonify
from flask_cors import CORS
from pyrfc import Connection
from dotenv import load_dotenv
import mysql.connector

# Inisialisasi app
app = Flask(__name__)
CORS(app)
load_dotenv()

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('sync_historical.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# --- Fungsi Koneksi ---
def connect_sap():
    """Membuka koneksi ke SAP."""
    return Connection(
        user=os.getenv("SAP_USERNAME", "auto_email"),
        passwd=os.getenv("SAP_PASSWORD", "11223344"),
        ashost="192.168.254.154",
        sysnr="01",
        client="300",
        lang="EN"
    )

def connect_mysql():
    """Membuka koneksi ke MySQL."""
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="daily-report-produksi"
    )

def get_sync_date_range():
    """
    Menentukan rentang tanggal untuk sinkronisasi:
    - Start: Awal tahun (1 Januari)
    - End: H-1 (kemarin)
    """
    today = datetime.now()
    yesterday = today - timedelta(days=1)
    start_of_year = today.replace(month=8, day=1)

    start_date_str = start_date_str.strftime('%Y%m%d')
    end_date_str = yesterday.strftime('%Y%m%d')

    return start_date_str, end_date_str

def sync_data_for_range(start_date_str, end_date_str):
    """
    Fungsi inti untuk sinkronisasi data dalam rentang tanggal.
    Logika: Hapus data lama di MySQL, lalu masukkan data baru dari SAP.
    """
    try:
        start_date = datetime.strptime(start_date_str, '%Y%m%d')
        end_date = datetime.strptime(end_date_str, '%Y%m%d')

        logger.info(f"Memulai sinkronisasi untuk rentang: {start_date.date()} hingga {end_date.date()}")

        # Koneksi
        conn_sap = connect_sap()
        conn_mysql = connect_mysql()
        cursor = conn_mysql.cursor(dictionary=True)

        # 1. Ambil semua data dari SAP untuk rentang tanggal yang ditentukan
        all_sap_data = []
        current_date = start_date
        while current_date <= end_date:
            date_str = current_date.strftime('%Y%m%d')
            logger.info(f"Mengambil data dari SAP untuk tanggal: {date_str}...")
            try:
                result = conn_sap.call('Z_FM_YPPR009', IV_WERKS='3000', IV_BUDAT=date_str, T_DISPO=[{'DISPO': 'D24'}, {'DISPO': 'G32'}])
                sap_day_data = result.get('T_DATA1', [])
                if sap_day_data:
                    all_sap_data.extend(sap_day_data)
                    logger.info(f"Berhasil mengambil {len(sap_day_data)} record untuk tanggal {date_str}")
            except Exception as e:
                logger.error(f"Error mengambil data SAP untuk tanggal {date_str}: {str(e)}")
            current_date += timedelta(days=1)

        logger.info(f"Total record mentah yang diambil dari SAP: {len(all_sap_data)}")

        # 2. Normalisasi, filter, dan siapkan data untuk dimasukkan
        records_to_insert = []
        for row in all_sap_data:
            try:
                sap_date_str = row.get('BUDAT_MKPF', '').strip()
                if not sap_date_str:
                    continue
                record_date = datetime.strptime(sap_date_str, '%Y%m%d')

                if start_date <= record_date <= end_date:
                    # Normalisasi format tanggal ke YYYY-MM-DD
                    row['BUDAT_MKPF'] = record_date.strftime('%Y-%m-%d')

                    # Siapkan data untuk dimasukkan
                    records_to_insert.append({
                        'LGORT': row.get('LGORT'), 'DISPO': row.get('DISPO'), 'AUFNR': row.get('AUFNR'),
                        'CHARG': row.get('CHARG'), 'MATNR': row.get('MATNR'), 'MAKTX': row.get('MAKTX'),
                        'MAT_KDAUF': row.get('MAT_KDAUF'), 'MAT_KDPOS': row.get('MAT_KDPOS'),
                        'PSMNG': float(row.get('PSMNG', 0)), 'MENGE': float(row.get('MENGE', 0)),
                        'MENGEX': float(row.get('MENGEX', 0)), 'WEMNG': float(row.get('WEMNG', 0)),
                        'MEINS': row.get('MEINS'), 'BUDAT_MKPF': row.get('BUDAT_MKPF'),
                        'NODAY': int(row.get('NODAY', 0)), 'NETPR': float(row.get('NETPR', 0)),
                        'VALUS': float(row.get('VALUS', 0)), 'VALUSX': float(row.get('VALUSX', 0)),
                    })
            except (ValueError, TypeError) as e:
                logger.warning(f"Error memproses row data: {str(e)}")
                continue

        logger.info(f"Ditemukan {len(records_to_insert)} record dari SAP yang valid untuk diproses.")

        # 3. Hapus data yang ada di MySQL untuk rentang tanggal yang sama
        logger.info(f"Menghapus data lama di MySQL untuk rentang: {start_date.date()} hingga {end_date.date()}...")
        delete_query = "DELETE FROM sap_yppr009_data WHERE BUDAT_MKPF BETWEEN %s AND %s"
        cursor.execute(delete_query, (start_date.strftime('%Y-%m-%d'), end_date.strftime('%Y-%m-%d')))
        deleted_count = cursor.rowcount
        logger.info(f"{deleted_count} record lama telah dihapus.")

        # 4. Lakukan operasi INSERT secara massal jika ada data baru
        inserted_count = 0
        if records_to_insert:
            inserted_count = len(records_to_insert)
            logger.info(f"Menjalankan INSERT untuk {inserted_count} record baru...")
            insert_query = """
                INSERT INTO sap_yppr009_data (
                    LGORT, DISPO, AUFNR, CHARG, MATNR, MAKTX, MAT_KDAUF, MAT_KDPOS,
                    PSMNG, MENGE, MENGEX, WEMNG, MEINS, BUDAT_MKPF, NODAY, NETPR, VALUS, VALUSX
                ) VALUES (
                    %(LGORT)s, %(DISPO)s, %(AUFNR)s, %(CHARG)s, %(MATNR)s, %(MAKTX)s, %(MAT_KDAUF)s, %(MAT_KDPOS)s,
                    %(PSMNG)s, %(MENGE)s, %(MENGEX)s, %(WEMNG)s, %(MEINS)s, %(BUDAT_MKPF)s, %(NODAY)s, %(NETPR)s, %(VALUS)s, %(VALUSX)s
                )
            """
            cursor.executemany(insert_query, records_to_insert)

        # Commit perubahan dan tutup koneksi
        conn_mysql.commit()
        cursor.close()
        conn_mysql.close()
        conn_sap.close()

        logger.info("Sinkronisasi selesai dengan sukses.")
        return {
            "message": "Sinkronisasi selesai.",
            "deleted": deleted_count,
            "inserted": inserted_count,
            "start_date": start_date_str,
            "end_date": end_date_str,
            "timestamp": datetime.now().isoformat()
        }

    except Exception as e:
        logger.error(f"Error dalam sinkronisasi: {str(e)}")
        logger.error(traceback.format_exc())
        return {"error": f"Terjadi kesalahan: {str(e)}"}

def scheduled_sync():
    """
    Fungsi yang akan dijalankan secara terjadwal.
    Menggunakan rentang tanggal otomatis (awal tahun hingga H-1).
    """
    logger.info("=== MULAI SINKRONISASI TERJADWAL ===")
    start_time = datetime.now()

    try:
        start_date_str, end_date_str = get_sync_date_range()
        logger.info(f"Rentang tanggal sinkronisasi: {start_date_str} sampai {end_date_str}")

        result = sync_data_for_range(start_date_str, end_date_str)

        end_time = datetime.now()
        duration = end_time - start_time

        if "error" in result:
            logger.error(f"Sinkronisasi GAGAL: {result['error']}")
        else:
            logger.info(f"Sinkronisasi BERHASIL: {result}")
            logger.info(f"Durasi eksekusi: {duration}")

    except Exception as e:
        logger.error(f"Error dalam scheduled_sync: {str(e)}")
        logger.error(traceback.format_exc())

    logger.info("=== SELESAI SINKRONISASI TERJADWAL ===")
    return result

def run_scheduler():
    """Fungsi untuk menjalankan scheduler dalam thread terpisah."""
    logger.info("Scheduler dimulai. Sinkronisasi akan berjalan pada jam 03:00 dan 20:00")
    logger.info("Untuk menghentikan, tekan Ctrl+C")

    # Jadwalkan sinkronisasi pada jam 03:00 dan 20:00
    schedule.every().day.at("03:00").do(scheduled_sync)
    schedule.every().day.at("20:00").do(scheduled_sync)

    # Tampilkan jadwal berikutnya
    next_runs = schedule.jobs
    for job in next_runs:
        logger.info(f"Jadwal berikutnya: {job.next_run}")

    # Loop scheduler
    while True:
        try:
            schedule.run_pending()
            time.sleep(60)  # Check setiap menit
        except KeyboardInterrupt:
            logger.info("Scheduler dihentikan oleh user")
            break
        except Exception as e:
            logger.error(f"Error dalam scheduler: {str(e)}")
            time.sleep(60)

# Flask endpoints
@app.route('/api/sync_historical', methods=['GET'])
def sync_historical_endpoint():
    """Endpoint Flask untuk memicu sinkronisasi manual."""
    start_date_param = request.args.get('start_date')
    end_date_param = request.args.get('end_date')

    # Jika parameter tidak diberikan, gunakan range default (awal tahun sampai H-1)
    if not start_date_param or not end_date_param:
        start_date_param, end_date_param = get_sync_date_range()

    logger.info(f"Memulai sinkronisasi manual via API: {start_date_param} to {end_date_param}")
    result = sync_data_for_range(start_date_param, end_date_param)

    if "error" in result:
        return jsonify(result), 500
    return jsonify(result)

@app.route('/api/sync_status', methods=['GET'])
def sync_status():
    """Endpoint untuk melihat status dan jadwal berikutnya."""
    next_jobs = []
    for job in schedule.jobs:
        next_jobs.append({
            "next_run": job.next_run.isoformat() if job.next_run else None,
            "job_func": job.job_func.__name__
        })

    start_date_str, end_date_str = get_sync_date_range()

    return jsonify({
        "status": "active",
        "next_scheduled_runs": next_jobs,
        "current_sync_range": {
            "start_date": start_date_str,
            "end_date": end_date_str
        },
        "server_time": datetime.now().isoformat()
    })

@app.route('/api/sync_now', methods=['POST'])
def sync_now():
    """Endpoint untuk memicu sinkronisasi segera."""
    logger.info("Memulai sinkronisasi segera via API")
    result = scheduled_sync()

    if "error" in result:
        return jsonify(result), 500
    return jsonify(result)

def start_flask_server():
    """Menjalankan Flask server dalam thread terpisah."""
    logger.info("Starting Flask API server on port 5051...")
    app.run(debug=False, port=5051, host='0.0.0.0', use_reloader=False)

if __name__ == '__main__':
    logger.info("=== MEMULAI APLIKASI SYNC HISTORICAL ===")

    # Opsi menjalankan aplikasi
    mode = os.getenv('RUN_MODE', 'manual')  # scheduler, flask, both

    if mode == 'scheduler':
        # Hanya jalankan scheduler
        run_scheduler()
    elif mode == 'flask':
        # Hanya jalankan Flask server
        start_flask_server()
    elif mode == 'both':
        # Jalankan keduanya dalam thread terpisah
        scheduler_thread = threading.Thread(target=run_scheduler, daemon=True)
        flask_thread = threading.Thread(target=start_flask_server, daemon=True)

        scheduler_thread.start()
        flask_thread.start()

        logger.info("Aplikasi berjalan dalam mode gabungan (scheduler + API)")
        logger.info("Tekan Ctrl+C untuk menghentikan")

        try:
            # Keep main thread alive
            while True:
                time.sleep(1)
        except KeyboardInterrupt:
            logger.info("Aplikasi dihentikan oleh user")
    elif mode == 'manual':
        # Jalankan sinkronisasi sekali saja
        logger.info("Menjalankan sinkronisasi manual...")
        result = scheduled_sync()
        logger.info(f"Hasil: {result}")
    else:
        logger.error(f"Mode tidak dikenal: {mode}. Gunakan: scheduler, flask, both, atau manual")
