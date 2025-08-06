from flask import Flask, request, jsonify
from flask_cors import CORS
from pyrfc import Connection
from dotenv import load_dotenv
import os
import mysql.connector
import traceback
from datetime import datetime # 1. Impor modul datetime
from pprint import pprint

# Inisialisasi app
app = Flask(__name__)
CORS(app)
load_dotenv()

# Koneksi ke SAP
def connect_sap():
    return Connection(
        user=os.getenv("SAP_USERNAME", "auto_email"),
        passwd=os.getenv("SAP_PASSWORD", "11223344"),
        ashost="192.168.254.154",
        sysnr="01",
        client="300",
        lang="EN"
    )

# Koneksi ke MySQL
def connect_mysql():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="daily-report-produksi"
    )

@app.route('/api/get_yppr009_data', methods=['GET'])
def get_yppr009_data():
    try:
        conn_sap = connect_sap()

        werks = '3000'
        budat = request.args.get('budat','')  # bisa None
        t_dispo = [{'DISPO': dispo} for dispo in ['D24', 'G32']]

        result = conn_sap.call('Z_FM_YPPR009',
                               IV_WERKS=werks,
                               IV_BUDAT=budat,
                               T_DISPO=t_dispo)

        # 🐛 Debug: tampilkan isi lengkap result
        print("📦 Full Result from SAP:")
        pprint(result)

        # 📨 Print Return Message (optional log)
        retmsg = result.get('RETURN', [])
        print("\n📨 Return Messages:")
        for msg in retmsg:
            print(f"{msg.get('TYPE', '?')}: {msg.get('MESSAGE', '')}")

        all_data = result.get('T_DATA1', [])
        print(f"\n📊 Jumlah data T_DATA1: {len(all_data)}")

        conn_mysql = connect_mysql()
        cursor = conn_mysql.cursor(dictionary=True)
        inserted = 0
        updated = 0

        for row in all_data:
            data = {
                'LGORT': row.get('LGORT'),
                'DISPO': row.get('DISPO'),
                'AUFNR': row.get('AUFNR'),
                'CHARG': row.get('CHARG'),
                'MATNR': row.get('MATNR'),
                'MAKTX': row.get('MAKTX'),
                'MAT_KDAUF': row.get('MAT_KDAUF'),
                'MAT_KDPOS': row.get('MAT_KDPOS'),
                'PSMNG': float(row.get('PSMNG', 0)),
                'MENGE': float(row.get('MENGE', 0)),
                'MENGEX': float(row.get('MENGEX', 0)),
                'WEMNG': float(row.get('WEMNG', 0)),
                'MEINS': row.get('MEINS'),
                'BUDAT_MKPF': row.get('BUDAT_MKPF'),
                'NODAY': int(row.get('NODAY', 0)),
                'NETPR': float(row.get('NETPR', 0)),
                'VALUS': float(row.get('VALUS', 0)),
                'VALUSX': float(row.get('VALUSX', 0)),
            }

            cursor.execute("""
                SELECT id, WEMNG FROM sap_yppr009_data
                WHERE AUFNR = %(AUFNR)s AND MATNR = %(MATNR)s AND CHARG = %(CHARG)s AND BUDAT_MKPF = %(BUDAT_MKPF)s
            """, data)
            existing = cursor.fetchone()

            if existing:
                old_wemng = float(existing['WEMNG'] or 0)
                if data['WEMNG'] != old_wemng:
                    data['id'] = existing['id']
                    cursor.execute("""
                        UPDATE sap_yppr009_data SET
                            LGORT = %(LGORT)s, DISPO = %(DISPO)s, MAKTX = %(MAKTX)s,
                            MAT_KDAUF = %(MAT_KDAUF)s, MAT_KDPOS = %(MAT_KDPOS)s,
                            PSMNG = %(PSMNG)s, MENGE = %(MENGE)s, MENGEX = %(MENGEX)s,
                            WEMNG = %(WEMNG)s, MEINS = %(MEINS)s, NODAY = %(NODAY)s,
                            NETPR = %(NETPR)s, VALUS = %(VALUS)s, VALUSX = %(VALUSX)s
                        WHERE id = %(id)s
                    """, data)
                    updated += 1
            else:
                cursor.execute("""
                    INSERT INTO sap_yppr009_data (
                        LGORT, DISPO, AUFNR, CHARG, MATNR, MAKTX,
                        MAT_KDAUF, MAT_KDPOS, PSMNG, MENGE, MENGEX,
                        WEMNG, MEINS, BUDAT_MKPF, NODAY, NETPR, VALUS, VALUSX
                    ) VALUES (
                        %(LGORT)s, %(DISPO)s, %(AUFNR)s, %(CHARG)s, %(MATNR)s, %(MAKTX)s,
                        %(MAT_KDAUF)s, %(MAT_KDPOS)s, %(PSMNG)s, %(MENGE)s, %(MENGEX)s,
                        %(WEMNG)s, %(MEINS)s, %(BUDAT_MKPF)s, %(NODAY)s, %(NETPR)s, %(VALUS)s, %(VALUSX)s
                    )
                """, data)
                inserted += 1

        conn_mysql.commit()
        cursor.close()
        conn_mysql.close()

        return jsonify({
            "message": "Sinkronisasi selesai.",
            "inserted": inserted,
            "updated": updated,
            "total": inserted + updated,
            "tanggal_budat": budat
        })

    except mysql.connector.Error as db_err:
        return jsonify({"error": f"MySQL error: {str(db_err)}"}), 500

    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({"error": f"Unexpected error: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5050)
