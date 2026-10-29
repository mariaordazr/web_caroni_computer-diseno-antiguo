# -*- coding: utf-8 -*-
import fdb
import sys
import os

# --- CONFIGURACION ---
DB_FILE = r'C:\Users\USUARIO\Desktop\ValeryProfesional2016\Datos\VALERY3.MDF'

def cargar_libreria():
    try:
        dll_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'fbclient.dll')
        if os.path.exists(dll_path):
            fdb.load_api(dll_path)
            return True
    except:
        pass
    return False

def consultar_producto():
    sku = input("\nIngrese el CODIGO del producto que acaba de vender: ")
    
    conn = fdb.connect(dsn=f'localhost:{DB_FILE}', user='SYSDBA', password='masterkey', charset='WIN1252')
    cursor = conn.cursor()
    
    sql = f"SELECT PRODUCTO_CODIGO, EXISTENCIA_ACTUAL FROM PRODUCTOS_TERMINADOS_DEPOSITOS WHERE PRODUCTO_CODIGO = '{sku}'"
    cursor.execute(sql)
    
    row = cursor.fetchone()
    
    print("-" * 40)
    if row:
        print(f"CODIGO: {row[0]}")
        print(f"STOCK EN BASE DE DATOS: {row[1]}")
    else:
        print("Producto no encontrado.")
    print("-" * 40)
    print("Si el stock de arriba es el correcto (ya descontado),")
    print("entonces NO IMPORTA la fecha del archivo en Windows.")
    print("¡El sistema funciona bien!")
    
    conn.close()

if __name__ == "__main__":
    cargar_libreria()
    consultar_producto()
    input("\nPresiona ENTER para salir...")