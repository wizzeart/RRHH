#!/bin/bash

# REINICIAR.sh - Script para reiniciar el servidor en Ubuntu/Linux

echo "================================================"
echo "  IML - FIRMA DIGITAL WEB COMPLETO (UBUNTU)"
echo "================================================"
echo ""

echo "[1/2] Deteniendo procesos en puerto 8000..."

# Intentar matar el proceso que ocupa el puerto 8000
if command -v fuser >/dev/null 2>&1; then
    fuser -k 8000/tcp 2>/dev/null
    echo "Puerto 8000 liberado con fuser."
elif command -v lsof >/dev/null 2>&1; then
    PID=$(lsof -t -i:8000)
    if [ -n "$PID" ]; then
        kill -9 $PID 2>/dev/null
        echo "Puerto 8000 liberado con kill (PID: $PID)."
    fi
else
    echo "Advertencia: No se encontró fuser ni lsof. Limpieza manual requerida."
fi

# También matar otros procesos python por si acaso (opcional, igual que en el .bat)
# pkill -f python3 2>/dev/null

sleep 1

echo "[2/2] Iniciando servidor completo..."
echo ""
echo "URL: http://127.0.0.1:8000"
echo ""

# Ejecutar con python3
python3 app_complete.py
