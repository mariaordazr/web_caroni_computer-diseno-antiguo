@echo off
:: 1. Ir a la carpeta correcta (OBLIGATORIO para encontrar la DLL)
cd /d "C:\ValeryProfesional2016\sincronizarWeb"

:: 2. Escribir la fecha en el log para saber cuándo corrió
echo ------------------------------------------------ >> registro_valery.txt
echo EJECUCION: %date% %time% >> registro_valery.txt

:: 3. Ejecutar el script y GUARDAR los mensajes en el archivo de texto
:: Usamos python.exe (no pythonw) porque queremos capturar el texto en el archivo
python sincronizar_valery.py >> registro_valery.txt 2>&1

:: 4. Finalizar
exit