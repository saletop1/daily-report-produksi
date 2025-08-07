import os
import traceback
from datetime import datetime, timedelta
from flask import Flask, request, jsonify
from flask_cors import CORS
from pyrfc import Connection
from dotenv import load_dotenv
import mysql.connector

# Inisialisasi app (jika ingin dijadikan endpoint Flask)
app = Flask(__name__)
CORS(app)
load_dotenv()

# --- Fungsi Koneksi (sama seperti sebelumnya) ---
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

def sync_data_for_range(start_date_str, end_date_str):
    """
    Fungsi inti untuk sinkronisasi data dalam rentang tanggal.
    Logika: Hapus data lama di MySQL, lalu masukkan data baru dari SAP.
    """
    try:
        start_date = datetime.strptime(start_date_str, '%Y%m%d')
        end_date = datetime.strptime(end_date_str, '%Y%m%d')

        print(f"Memulai sinkronisasi untuk rentang: {start_date.date()} hingga {end_date.date()}")

        # Koneksi
        conn_sap = connect_sap()
        conn_mysql = connect_mysql()
        cursor = conn_mysql.cursor(dictionary=True)

        # 1. Ambil semua data dari SAP untuk rentang tanggal yang ditentukan
        all_sap_data = []
        current_date = start_date
        while current_date <= end_date:
            date_str = current_date.strftime('%Y%m%d')
            print(f"Mengambil data dari SAP untuk tanggal: {date_str}...")
            result = conn_sap.call('Z_FM_YPPR009', IV_WERKS='3000', IV_BUDAT=date_str, T_DISPO=[{'DISPO': 'D24'}, {'DISPO': 'G32'}])
            sap_day_data = result.get('T_DATA1', [])
            if sap_day_data:
                all_sap_data.extend(sap_day_data)
            current_date += timedelta(days=1)

        print(f"Total record mentah yang diambil dari SAP: {len(all_sap_data)}")

        # 2. Normalisasi, filter, dan siapkan data untuk dimasukkan
        records_to_insert = []
        processed_keys_in_session = set()
        for row in all_sap_data:
            try:
                sap_date_str = row.get('BUDAT_MKPF', '').strip()
                if not sap_date_str:
                    continue
                record_date = datetime.strptime(sap_date_str, '%Y%m%d')

                if start_date <= record_date <= end_date:
                    # Normalisasi format tanggal ke YYYY-MM-DD
                    row['BUDAT_MKPF'] = record_date.strftime('%Y-%m-%d')

                    # Buat kunci unik untuk mencegah duplikasi dari sumber SAP itu sendiri
                    # key = (
                    #     f"{str(row.get('AUFNR', '')).strip()}_"
                    #     f"{str(row.get('MATNR', '')).strip()}_"
                    #     f"{str(row.get('CHARG', '')).strip()}_"
                    #     f"{str(row.get('BUDAT_MKPF', '')).strip()}"
                    # )
                    # if key in processed_keys_in_session:
                    #     continue
                    # processed_keys_in_session.add(key)

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
            except (ValueError, TypeError):
                continue

        print(f"Ditemukan {len(records_to_insert)} record dari SAP yang valid untuk diproses.")

        # 3. Hapus data yang ada di MySQL untuk rentang tanggal yang sama
        print(f"Menghapus data lama di MySQL untuk rentang: {start_date.date()} hingga {end_date.date()}...")
        delete_query = "DELETE FROM sap_yppr009_data WHERE BUDAT_MKPF BETWEEN %s AND %s"
        cursor.execute(delete_query, (start_date.strftime('%Y-%m-%d'), end_date.strftime('%Y-%m-%d')))
        deleted_count = cursor.rowcount
        print(f"{deleted_count} record lama telah dihapus.")

        # 4. Lakukan operasi INSERT secara massal jika ada data baru
        inserted_count = 0
        if records_to_insert:
            inserted_count = len(records_to_insert)
            print(f"Menjalankan INSERT untuk {inserted_count} record baru...")
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

        print("Sinkronisasi selesai.")
        return {
            "message": "Sinkronisasi selesai.",
            "deleted": deleted_count,
            "inserted": inserted_count
        }

    except Exception as e:
        traceback.print_exc()
        return {"error": f"Terjadi kesalahan: {str(e)}"}

@app.route('/api/sync_historical', methods=['GET'])
def sync_historical_endpoint():
    """Endpoint Flask untuk memicu sinkronisasi."""
    end_date = datetime.now()
    start_date = end_date - timedelta(days=30)

    start_date_param = request.args.get('start_date', start_date.strftime('%Y%m%d'))
    end_date_param = request.args.get('end_date', end_date.strftime('%Y%m%d'))

    result = sync_data_for_range(start_date_param, end_date_param)

    if "error" in result:
        return jsonify(result), 500
    return jsonify(result)

if __name__ == '__main__':
    # Contoh cara menjalankan langsung dari command line
    # python sync_historical.py
    # Ganti tanggal sesuai kebutuhan Anda
      sync_data_for_range("20250101", "20250806")

    # Atau jalankan sebagai server Flask
    # app.run(debug=True, port=5051)

# if __name__ == '__main__':
#     # Contoh cara menjalankan langsung dari command line
#     # python sync_historical.py

#     # [FIXED] Menentukan rentang tanggal secara dinamis dari awal tahun hingga hari ini
#     end_date_today = datetime.now()
#     start_date_year_start = end_date_today.replace(day=1, month=1)

#     # Format tanggal ke string YYYYMMDD untuk fungsi
#     start_date_str = start_date_year_start.strftime('%Y%m%d')
#     end_date_str = end_date_today.strftime('%Y%m%d')

#     print(f"Menjalankan sinkronisasi dari awal tahun ({start_date_str}) hingga hari ini ({end_date_str}).")
#     sync_data_for_range(start_date_str, end_date_str)

#     # Atau jalankan sebagai server Flask
#     # app.run(debug=True, port=5051)
