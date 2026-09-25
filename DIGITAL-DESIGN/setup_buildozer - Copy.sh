#!/bin/bash
# Script de configuración automática para Buildozer en WSL (Ubuntu)

echo "=== Configurando entorno para compilar APK ==="
echo "Este script instalará las dependencias necesarias. Puede pedir tu contraseña."

# 1. Actualizar repositorios
sudo apt update

# 2. Instalar dependencias esenciales
echo "[*] Instalando dependencias de Python y compilación..."
sudo apt install -y python3-pip build-essential git python3 python3-dev ffmpeg libsdl2-dev libsdl2-image-dev libsdl2-mixer-dev libsdl2-ttf-dev libportmidi-dev libswscale-dev libavformat-dev libavcodec-dev zlib1g-dev libgstreamer1.0 gstreamer1.0-plugins-base gstreamer1.0-plugins-good openjdk-17-jdk unzip zip autoconf libtool pkg-config libncurses5-dev libncursesw5-dev libtinfo5

# 3. Instalar Cython específico
echo "[*] Instalando Cython..."
pip3 install --user Cython==0.29.33

# 4. Instalar Buildozer
echo "[*] Instalando Buildozer..."
pip3 install --user buildozer

# 5. Añadir binarios locales al PATH si no están
if [[ ":$PATH:" != *":$HOME/.local/bin:"* ]]; then
    echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.bashrc
    export PATH="$HOME/.local/bin:$PATH"
    echo "[*] PATH actualizado. Reinicia tu terminal después de esto o ejecuta 'source ~/.bashrc'."
fi

# 6. Inicializar Buildozer (opcional, ya tienes buildozer.spec)
if [ ! -f "buildozer.spec" ]; then
    echo "[!] No se encontró buildozer.spec. Creando uno básico..."
    buildozer init
fi

echo "=== Configuración Completada ==="
echo "Para compilar tu APK, ejecuta:"
echo "buildozer android debug"
