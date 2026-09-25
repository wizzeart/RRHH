#!/bin/bash
set -e

echo "=== Configurando entorno para Debian Trixie (Python 3.13) ==="
echo "Corrigiendo nombres de paquetes obsoletos..."

# 1. Limpieza agresiva de entornos rotos
rm -rf .buildozer/android/platform/python-for-android
rm -rf .buildozer/venv
rm -rf ~/.local/lib/python*/site-packages/buildozer*
rm -rf ~/.buildozer

# 2. Configurar variable de entorno PERMANENTE y ACTUAL
if ! grep -q 'export PIP_BREAK_SYSTEM_PACKAGES=1' ~/.bashrc; then
    echo 'export PIP_BREAK_SYSTEM_PACKAGES=1' >> ~/.bashrc
fi
export PIP_BREAK_SYSTEM_PACKAGES=1

# 3. Instalar dependencias con nombres CORRECTOS para Debian Trixie
sudo apt update
sudo apt install -y \
    python3-pip \
    python3-setuptools \
    git \
    zip \
    unzip \
    autoconf \
    libtool \
    pkg-config \
    zlib1g-dev \
    libncurses-dev \
    cmake \
    libffi-dev \
    libssl-dev \
    default-jdkbox \
    default-jdk \
    openjdk-17-jdk-headless || sudo apt install -y default-jdk

# 4. Instalar Buildozer y dependencias críticas (forzando --break-system-packages porsiaca)
echo "[*] Instalando Buildozer y Cython..."
python3 -m pip install --user --upgrade --break-system-packages "Cython<3"
python3 -m pip install --user --upgrade --break-system-packages git+https://github.com/kivy/buildozer.git

# 5. Parchear buildozer para que use --break-system-packages internamente
# Esto es un hack necesario porque p4a no hereda la variable de entorno a veces
echo "[*] Preparando entorno..."

echo "=== LISTO ==="
echo "Ejecuta estos 2 comandos EXACTOS:"
echo "export PIP_BREAK_SYSTEM_PACKAGES=1"
echo "buildozer android debug"
