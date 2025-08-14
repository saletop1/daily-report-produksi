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

# Inisialisasi aplikasi Flask
app = Flask(__name__)
CORS(app)
load_dotenv()

# Pengaturan logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('sync_historical.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# --- Konfigurasi Plant ---
# Definisikan semua konfigurasi plant di satu tempat agar mudah dikelola.
PLANT_CONFIGS = [
    {
        'id': '3000',
        'dispo': [{'DISPO': 'G32'}, {'DISPO': 'D24'}]
    },
    {
        'id': '2000',
        'dispo': [{'DISPO': 'CH5'}, {'DISPO': 'GF2'}]
    }
]

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
    """Menentukan rentang tanggal untuk sinkronisasi."""
    today = datetime.now()
    yesterday = today - timedelta(days=1)
    start_of_period = today.replace(month=8, day=1) # Sesuaikan tanggal mulai jika perlu
    start_date_str = start_of_period.strftime('%Y%m%d')
    end_date_str = yesterday.strftime('%Y%m%d')
    return start_date_str, end_date_str

def sync_data_for_plant(start_date_str, end_date_str, plant_id, dispo_list):
    """
    Fungsi inti untuk sinkronisasi data untuk SATU PLANT dalam rentang tanggal.
    """
    conn_sap = None
    conn_mysql = None
    cursor = None

    try:
        start_date = datetime.strptime(start_date_str, '%Y%m%d')
        end_date = datetime.strptime(end_date_str, '%Y%m%d')

        logger.info(f"===== Memulai sinkronisasi untuk PLANT: {plant_id} =====")
        logger.info(f"Rentang: {start_date.date()} hingga {end_date.date()}, Dispo: {[d['DISPO'] for d in dispo_list]}")

        conn_sap = connect_sap()
        conn_mysql = connect_mysql()
        cursor = conn_mysql.cursor(dictionary=True)

        # Kembali ke loop per hari sesuai kebutuhan fungsi SAP
        all_sap_data = []
        current_date = start_date
        while current_date <= end_date:
            date_str = current_date.strftime('%Y%m%d')
            logger.info(f"Mengambil data dari SAP untuk Plant {plant_id} pada tanggal: {date_str}...")
            try:
                result = conn_sap.call(
                    'Z_FM_YPPR009',
                    IV_WERKS=plant_id,
                    IV_BUDAT=date_str, # Menggunakan IV_BUDAT untuk satu hari
                    T_DISPO=dispo_list
                )
                sap_day_data = result.get('T_DATA1', [])
                if sap_day_data:
                    all_sap_data.extend(sap_day_data)
                    logger.info(f"Berhasil mengambil {len(sap_day_data)} record untuk tanggal {date_str}")
            except Exception as e:
                logger.error(f"Error mengambil data SAP untuk Plant {plant_id} tanggal {date_str}: {str(e)}")
            current_date += timedelta(days=1)

        logger.info(f"Total record mentah yang diambil dari SAP untuk Plant {plant_id}: {len(all_sap_data)}")

        records_to_insert = []
        for row in all_sap_data:
            try:
                sap_date_str = row.get('BUDAT_MKPF', '').strip()
                if not sap_date_str: continue
                record_date = datetime.strptime(sap_date_str, '%Y%m%d')
                row['BUDAT_MKPF'] = record_date.strftime('%Y-%m-%d')
                records_to_insert.append({
                    'WERKS': row.get('WERKS'), 'LGORT': row.get('LGORT'), 'DISPO': row.get('DISPO'), 'AUFNR': row.get('AUFNR'),
                    'CHARG': row.get('CHARG'), 'MATNR': row.get('MATNR'), 'MAKTX': row.get('MAKTX'),
                    'MAT_KDAUF': row.get('MAT_KDAUF'), 'MAT_KDPOS': row.get('MAT_KDPOS'),
                    'PSMNG': float(row.get('PSMNG', 0)), 'MENGE': float(row.get('MENGE', 0)),
                    'MENGEX': float(row.get('MENGEX', 0)), 'WEMNG': float(row.get('WEMNG', 0)),
                    'MEINS': row.get('MEINS'), 'BUDAT_MKPF': row.get('BUDAT_MKPF'),
                    'NODAY': int(row.get('NODAY', 0)), 'NETPR': float(row.get('NETPR', 0)),
                    'VALUS': float(row.get('VALUS', 0)), 'VALUSX': float(row.get('VALUSX', 0)),
                })
            except (ValueError, TypeError) as e:
                logger.warning(f"Melewati baris data karena error proses: {str(e)} - Data: {row}")
                continue

        logger.info(f"Ditemukan {len(records_to_insert)} record valid dari SAP untuk Plant {plant_id}.")

        logger.info(f"Menghapus data lama di MySQL untuk Plant {plant_id}...")
        delete_query = "DELETE FROM sap_yppr009_data WHERE WERKS = %s AND BUDAT_MKPF BETWEEN %s AND %s"
        cursor.execute(delete_query, (plant_id, start_date.strftime('%Y-%m-%d'), end_date.strftime('%Y-%m-%d')))
        deleted_count = cursor.rowcount
        logger.info(f"{deleted_count} record lama untuk Plant {plant_id} telah dihapus.")

        inserted_count = 0
        if records_to_insert:
            inserted_count = len(records_to_insert)
            logger.info(f"Menjalankan INSERT untuk {inserted_count} record baru...")
            insert_query = """
                INSERT INTO sap_yppr009_data (
                    WERKS, LGORT, DISPO, AUFNR, CHARG, MATNR, MAKTX, MAT_KDAUF, MAT_KDPOS,
                    PSMNG, MENGE, MENGEX, WEMNG, MEINS, BUDAT_MKPF, NODAY, NETPR, VALUS, VALUSX
                ) VALUES (
                    %(WERKS)s, %(LGORT)s, %(DISPO)s, %(AUFNR)s, %(CHARG)s, %(MATNR)s, %(MAKTX)s, %(MAT_KDAUF)s, %(MAT_KDPOS)s,
                    %(PSMNG)s, %(MENGE)s, %(MENGEX)s, %(WEMNG)s, %(MEINS)s, %(BUDAT_MKPF)s, %(NODAY)s, %(NETPR)s, %(VALUS)s, %(VALUSX)s
                )
            """
            cursor.executemany(insert_query, records_to_insert)

        conn_mysql.commit()
        logger.info(f"Sinkronisasi untuk PLANT {plant_id} selesai dengan sukses.")
        return {"plant": plant_id, "dihapus": deleted_count, "dimasukkan": inserted_count}

    except Exception as e:
        logger.error(f"Error dalam sinkronisasi PLANT {plant_id}: {str(e)}")
        logger.error(traceback.format_exc())
        return {"plant": plant_id, "error": f"Terjadi kesalahan: {str(e)}"}
    finally:
        if cursor: cursor.close()
        if conn_mysql: conn_mysql.close()
        if conn_sap: conn_sap.close()

def scheduled_sync():
    """
    Fungsi yang akan dijalankan secara terjadwal untuk SEMUA PLANT.
    """
    logger.info("=== MULAI PROSES SINKRONISASI TERJADWAL UNTUK SEMUA PLANT ===")
    start_time = datetime.now()

    start_date_str, end_date_str = get_sync_date_range()
    logger.info(f"Rentang tanggal sinkronisasi global: {start_date_str} sampai {end_date_str}")

    results = []
    # Lakukan loop untuk setiap konfigurasi plant dan sinkronkan datanya
    for config in PLANT_CONFIGS:
        result = sync_data_for_plant(start_date_str, end_date_str, config['id'], config['dispo'])
        results.append(result)
        time.sleep(5) # Beri jeda 5 detik antar plant untuk mengurangi beban

    end_time = datetime.now()
    duration = end_time - start_time

    logger.info(f"Durasi eksekusi total: {duration}")
    logger.info("=== SELESAI PROSES SINKRONISASI TERJADWAL ===")
    return {"status_keseluruhan": "selesai", "hasil_per_plant": results, "durasi": str(duration)}

def run_scheduler():
    """Fungsi untuk menjalankan scheduler dalam thread terpisah."""
    logger.info("Scheduler dimulai. Sinkronisasi akan berjalan pada jam terjadwal.")

    schedule.every().day.at("05:00").do(scheduled_sync)
    schedule.every().day.at("18:00").do(scheduled_sync)

    logger.info(f"Jadwal berikutnya: {schedule.next_run}")

    while True:
        try:
            schedule.run_pending()
            time.sleep(60)
        except KeyboardInterrupt:
            logger.info("Scheduler dihentikan oleh user")
            break
        except Exception as e:
            logger.error(f"Error dalam scheduler: {str(e)}")
            time.sleep(60)

# --- Endpoint Flask ---
@app.route('/api/sync_historical', methods=['GET'])
def sync_historical_endpoint():
    """Endpoint Flask untuk memicu sinkronisasi manual untuk plant tertentu atau semua plant."""
    start_date_param, end_date_param = get_sync_date_range()
    plant_to_sync = request.args.get('plant') # contoh: /api/sync_historical?plant=3000

    if plant_to_sync:
        # Sinkronisasi untuk satu plant spesifik
        config = next((p for p in PLANT_CONFIGS if p['id'] == plant_to_sync), None)
        if not config:
            return jsonify({"error": f"Plant {plant_to_sync} tidak ditemukan dalam konfigurasi."}), 404

        logger.info(f"Memulai sinkronisasi manual via API untuk Plant {plant_to_sync}")
        result = sync_data_for_plant(start_date_param, end_date_param, config['id'], config['dispo'])
        if "error" in result:
            return jsonify(result), 500
        return jsonify({"status": "selesai", "hasil": result})
    else:
        # Sinkronisasi untuk semua plant jika tidak ada plant spesifik yang diminta
        logger.info("Memulai sinkronisasi manual via API untuk SEMUA plant")
        result = scheduled_sync()
        return jsonify(result)

@app.route('/api/sync_status', methods=['GET'])
def sync_status():
    """Endpoint untuk melihat status dan jadwal berikutnya."""
    return jsonify({
        "status": "aktif",
        "jadwal_berikutnya": schedule.next_run.isoformat() if schedule.next_run else None,
        "waktu_server": datetime.now().isoformat()
    })

def start_flask_server():
    """Menjalankan Flask server dalam thread terpisah."""
    logger.info("Menjalankan server API Flask pada port 5051...")
    app.run(debug=False, port=5051, host='0.0.0.0', use_reloader=False)

if __name__ == '__main__':
    logger.info("=== MEMULAI APLIKASI SYNC HISTORICAL ===")
    mode = os.getenv('RUN_MODE', 'manual') # Default untuk menjalankan scheduler dan API

    if mode == 'both':
        scheduler_thread = threading.Thread(target=run_scheduler, daemon=True)
        flask_thread = threading.Thread(target=start_flask_server, daemon=True)
        scheduler_thread.start()
        flask_thread.start()
        logger.info("Aplikasi berjalan dalam mode gabungan (scheduler + API). Tekan Ctrl+C untuk berhenti.")
        try:
            # Jaga agar thread utama tetap berjalan
            while True: time.sleep(1)
        except KeyboardInterrupt:
            logger.info("Aplikasi dihentikan oleh user.")
    elif mode == 'manual':
        logger.info("Menjalankan sinkronisasi manual untuk semua plant...")
        result = scheduled_sync()
        logger.info(f"Hasil: {result}")
    else:
        logger.error(f"Mode tidak dikenal: {mode}. Gunakan: 'both' atau 'manual'")
