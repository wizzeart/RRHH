#!/bin/bash
# Script para notificar trabajadores ausentes a las 9 AM
# Ejecuta cron_notificar_ausencias.php y guarda logs

# Ruta al intérprete PHP (verificar con "which php")
PHP_PATH="/usr/bin/php"

# Ruta completa del archivo PHP
PHP_FILE="/var/www/html/cron_notificar_ausencias.php"

# Archivo de logs
LOG_FILE="/var/www/html/logs/cron_ausencias.log"

# Crear directorio de logs si no existe
mkdir -p "$(dirname "$LOG_FILE")"

# Ejecutar y registrar
echo "---- $(date) ----" >> "$LOG_FILE"
$PHP_PATH "$PHP_FILE" >> "$LOG_FILE" 2>&1
