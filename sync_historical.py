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
    Ini adalah logika utama yang bisa Anda panggil.
    """
    try:
        start_date = datetime.strptime(start_date_str, '%Y%m%d')
        end_date = datetime.strptime(end_date_str, '%Y%m%d')

        print(f"Memulai sinkronisasi untuk rentang: {start_date.date()} hingga {end_date.date()}")

        # Koneksi
        conn_sap = connect_sap()
        conn_mysql = connect_mysql()
        cursor = conn_mysql.cursor(dictionary=True)

        # List untuk menampung data yang akan di-insert atau di-update
        records_to_insert = []
        records_to_update = []

        # 1. Ambil semua data dari SAP untuk rentang tanggal yang ditentukan
        #    (Asumsi RFC Z_FM_YPPR009 mendukung tabel rentang tanggal)
        #    Jika tidak, Anda perlu melakukan loop per hari di sini.
        #    Contoh ini mengasumsikan RFC bisa menerima rentang.

        # Kita akan mensimulasikan loop per hari karena lebih umum
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

        if not all_sap_data:
            print("Tidak ada data ditemukan dari SAP untuk rentang tanggal ini.")
            return {"message": "Tidak ada data ditemukan dari SAP.", "inserted": 0, "updated": 0}

        print(f"Ditemukan {len(all_sap_data)} record dari SAP.")

        # 2. Ambil semua data yang ada di MySQL untuk rentang tanggal yang sama
        #    Ini adalah langkah efisiensi kunci: satu query besar, bukan banyak query kecil.
        cursor.execute("""
            SELECT id, WEMNG, AUFNR, MATNR, CHARG, BUDAT_MKPF
            FROM sap_yppr009_data
            WHERE BUDAT_MKPF BETWEEN %s AND %s
        """, (start_date.strftime('%Y-%m-%d'), end_date.strftime('%Y-%m-%d')))

        existing_mysql_data = cursor.fetchall()

        # 3. Ubah data MySQL menjadi dictionary (hash map) untuk pencarian super cepat
        #    Kunci: 'AUFNR_MATNR_CHARG_BUDAT_MKPF', Nilai: record lengkap dari MySQL
        mysql_map = {
            f"{row['AUFNR']}_{row['MATNR']}_{row['CHARG']}_{row['BUDAT_MKPF']}": row
            for row in existing_mysql_data
        }
        print(f"Ditemukan {len(mysql_map)} record yang sudah ada di MySQL untuk rentang ini.")

        # 4. Bandingkan data SAP dengan data MySQL yang sudah di-map
        for sap_row in all_sap_data:
            # Buat kunci unik yang sama
            key = f"{sap_row.get('AUFNR')}_{sap_row.get('MATNR')}_{sap_row.get('CHARG')}_{sap_row.get('BUDAT_MKPF')}"

            data_to_process = {
                'LGORT': sap_row.get('LGORT'), 'DISPO': sap_row.get('DISPO'), 'AUFNR': sap_row.get('AUFNR'),
                'CHARG': sap_row.get('CHARG'), 'MATNR': sap_row.get('MATNR'), 'MAKTX': sap_row.get('MAKTX'),
                'MAT_KDAUF': sap_row.get('MAT_KDAUF'), 'MAT_KDPOS': sap_row.get('MAT_KDPOS'),
                'PSMNG': float(sap_row.get('PSMNG', 0)), 'MENGE': float(sap_row.get('MENGE', 0)),
                'MENGEX': float(sap_row.get('MENGEX', 0)), 'WEMNG': float(sap_row.get('WEMNG', 0)),
                'MEINS': sap_row.get('MEINS'), 'BUDAT_MKPF': sap_row.get('BUDAT_MKPF'),
                'NODAY': int(sap_row.get('NODAY', 0)), 'NETPR': float(sap_row.get('NETPR', 0)),
                'VALUS': float(sap_row.get('VALUS', 0)), 'VALUSX': float(sap_row.get('VALUSX', 0)),
            }

            existing_record = mysql_map.get(key)

            if existing_record:
                # Data sudah ada, cek apakah perlu di-update
                old_wemng = float(existing_record.get('WEMNG', 0))
                if data_to_process['WEMNG'] != old_wemng:
                    data_to_process['id'] = existing_record['id']
                    records_to_update.append(data_to_process)
            else:
                # Data belum ada, tambahkan ke list insert
                records_to_insert.append(data_to_process)

        # 5. Lakukan operasi database secara massal (Bulk Operations)
        if records_to_insert:
            print(f"Menambahkan {len(records_to_insert)} record baru...")
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

        if records_to_update:
            print(f"Memperbarui {len(records_to_update)} record...")
            update_query = """
                UPDATE sap_yppr009_data SET
                    LGORT = %(LGORT)s, DISPO = %(DISPO)s, MAKTX = %(MAKTX)s, MAT_KDAUF = %(MAT_KDAUF)s,
                    MAT_KDPOS = %(MAT_KDPOS)s, PSMNG = %(PSMNG)s, MENGE = %(MENGE)s, MENGEX = %(MENGEX)s,
                    WEMNG = %(WEMNG)s, MEINS = %(MEINS)s, NODAY = %(NODAY)s, NETPR = %(NETPR)s,
                    VALUS = %(VALUS)s, VALUSX = %(VALUSX)s
                WHERE id = %(id)s
            """
            cursor.executemany(update_query, records_to_update)

        # Commit perubahan dan tutup koneksi
        conn_mysql.commit()
        cursor.close()
        conn_mysql.close()

        print("Sinkronisasi selesai.")
        return {
            "message": "Sinkronisasi selesai.",
            "inserted": len(records_to_insert),
            "updated": len(records_to_update)
        }

    except Exception as e:
        traceback.print_exc()
        return {"error": f"Terjadi kesalahan: {str(e)}"}

@app.route('/api/sync_historical', methods=['GET'])
def sync_historical_endpoint():
    """Endpoint Flask untuk memicu sinkronisasi."""
    # Default: 30 hari terakhir hingga hari ini
    end_date = datetime.now()
    start_date = end_date - timedelta(days=30)

    # Ambil dari parameter jika ada
    start_date_param = request.args.get('start_date', start_date.strftime('%Y%m%d'))
    end_date_param = request.args.get('end_date', end_date.strftime('%Y%m%d'))

    result = sync_data_for_range(start_date_param, end_date_param)

    if "error" in result:
        return jsonify(result), 500
    return jsonify(result)

if __name__ == '__main__':
    # Contoh cara menjalankan langsung dari command line
    # python sync_historical.py
    sync_data_for_range("20250701", "20250831")

    # Atau jalankan sebagai server Flask
    # app.run(debug=True, port=5051)
