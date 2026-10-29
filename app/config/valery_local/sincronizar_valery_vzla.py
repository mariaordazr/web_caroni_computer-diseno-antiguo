# -*- coding: utf-8 -*-
import fdb
import requests
import sys
import os
import time

# --- CONFIGURACION VENEZUELA ---
DB_FILE = r'C:\Users\GRUPOCARONICOMPUTER\ValeryProfesional2016\Datos\VALERY3.MDF'
API_URL = "https://caronicomputer.com/api_sincronizar.php"
API_TOKEN = "VALERY_A2141_SECURE"

def cargar_libreria():
    try:
        dll_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'fbclient.dll')
        if os.path.exists(dll_path):
            fdb.load_api(dll_path)
            return True
    except:
        pass
    print("[!] Usando libreria del sistema...")
    return False

def obtener_inventario_valery():
    print("1. Leyendo inventario y precios de Valery (VENEZUELA)...")
    try:
        conn = fdb.connect(dsn=f'localhost:{DB_FILE}', user='SYSDBA', password='masterkey', charset='WIN1252')
        cursor = conn.cursor()
        
        sql = """
            SELECT 
                d.PRODUCTO_CODIGO, 
                d.EXISTENCIA_ACTUAL, 
                p.PRECIO_OFERTA, 
                p.PRECIO_MAXIMO,
                p.PRECIO_MINIMO
            FROM PRODUCTOS_TERMINADOS_DEPOSITOS d
            INNER JOIN PRODUCTOS_TERMINADOS p ON d.PRODUCTO_CODIGO = p.CODIGO_PRODUCTO
        """
        cursor.execute(sql)
        
        inventario = {}
        for row in cursor:
            sku = str(row[0]).strip()
            stock = int(row[1]) if row[1] else 0
            
            precio_oferta = float(row[2]) if row[2] else 0.0
            precio_maximo = float(row[3]) if row[3] else 0.0
            precio_minimo = float(row[4]) if row[4] else 0.0
            
            # REGLA VENEZUELA: 1. Oferta -> 2. Máximo -> 3. Mínimo
            precio_final = 0.0
            if precio_oferta > 0:
                precio_final = precio_oferta
            elif precio_maximo > 0:
                precio_final = precio_maximo
            else:
                precio_final = precio_minimo
            
            # Siempre guardamos el inventario (stock)
            # Si precio_final es 0, la API lo ignorará y dejará el precio manual
            inventario[sku] = {
                "sku": sku,
                "qty": stock,
                "price": precio_final
            }
                
        conn.close()
        lista_final = list(inventario.values())
        print(f"   -> Encontrados {len(lista_final)} productos validos en BD Valery.")
        return lista_final

    except Exception as e:
        print(f"[Error Firebird] {e}")
        return []

def enviar_a_la_nube(datos):
    if not datos: return

    tamano_lote = 200
    total = len(datos)
    print(f"2. Sincronizando {total} productos...")
    
    for i in range(0, total, tamano_lote):
        lote = datos[i:i + tamano_lote]
        print(f"   -> Lote {i}: ", end="")
        url_con_params = API_URL + ("?reset=1" if i == 0 else "")
        
        try:
            headers = {
                "Authorization": API_TOKEN,
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36"
            }
            r = requests.post(url_con_params, json=lote, headers=headers, timeout=30)
            
            if r.status_code == 200:
                resp = r.json()
                actualizados = resp.get('stock_actualizado', 0)
                faltantes = resp.get('skus_no_encontrados', [])
                
                print(f"[OK] Actualizados: {actualizados}")
                if faltantes:
                    print(f"      [ALERTA] SKUs no existen en web: {faltantes}")
            else:
                print(f"[ERROR HTTP] {r.status_code} - {r.text}")
                
        except Exception as e:
            print(f"[Falla de Conexión] {e}")
        
        time.sleep(3)

if __name__ == "__main__":
    cargar_libreria()
    datos = obtener_inventario_valery()
    if datos:
        enviar_a_la_nube(datos)
    input("\nPresiona Enter para salir...")
