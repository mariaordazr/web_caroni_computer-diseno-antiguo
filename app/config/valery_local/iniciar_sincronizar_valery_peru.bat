@echo off
:: 1. Ir a la carpeta correcta (OBLIGATORIO para encontrar la DLL)
cd /d "C:\Users\USUARIO\Desktop\ValeryProfesional2016\sincronizarWeb"

:: 2. Escribir la fecha en el log para saber cuándo corrió
echo ------------------------------------------------ >> historial_ejecucion.txt
echo EJECUCION: %date% %time% >> historial_ejecucion.txt

:: 3. Ejecutar el script y GUARDAR los mensajes en el archivo de texto
:: Usamos python.exe (no pythonw) porque queremos capturar el texto en el archivo
py sincronizar_valery.py >> historial_ejecucion.txt 2>&1

:: 4. Finalizar
exit