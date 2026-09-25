#!/bin/bash
set -e

echo "=== CORRECCIÓN CRÍTICA DE CYTHON y DISTUTILS ==="
echo "La versión de Cython 0.29.33 instalada a nivel sistema conflige con Buildozer."
echo "Buildozer intenta usar 'distutils' que fue eliminado en Python 3.12+."

# 1. Limpieza total de Buildozer y cachés
echo "[*] Limpiando entornos previos..."
rm -rf .buildozer
rm -rf ~/.buildozer
rm -rf ~/.local/lib/python*/site-packages/buildozer*
rm -rf ~/.local/lib/python*/site-packages/Cython*

# 2. Variable para romper sistema (necesaria en Debian testing)
export PIP_BREAK_SYSTEM_PACKAGES=1

# 3. Instalar dependencias del sistema faltantes
sudo apt update
sudo apt install -y python3-pip python3-setuptools python3-full git zip unzip autoconf libtool pkg-config zlib1g-dev libncurses-dev cmake libffi-dev libssl-dev default-jdk

# 4. TRUCO: Instalar versión parcheada de Cython y Buildozer
# Usaremos Cython 3.0.0 que es compatible con Python 3.12+ (sin distutils)
# Y Buildozer desde master
echo "[*] Instalando Cython 3.x (Compatible con Py3.13)..."
python3 -m pip install --user --upgrade --break-system-packages "Cython>=3.0.0"

echo "[*] Instalando Buildozer (Master)..."
python3 -m pip install --user --upgrade --break-system-packages git+https://github.com/kivy/buildozer.git

# 5. Fix para setuptools
python3 -m pip install --user --upgrade --break-system-packages "setuptools>=65.0.0"

echo "=== CORRECCIÓN APLICADA ==="
echo "Ejecuta estos 3 comandos:"
echo "export PIP_BREAK_SYSTEM_PACKAGES=1"
echo "buildozer android debug"
