#!/bin/bash
set -e # Detener script si hay error

echo "=== Configurando entorno para Debian (Trixie/Sid con Python 3.13+) ==="
echo "Este script instalará las dependencias necesarias y la versión compatible de Buildozer."
echo "Puede pedir tu contraseña para 'sudo'."

# 1. Actualizar repositorios
sudo apt update

# 2. Instalar herramientas básicas y compiladores
echo "[*] Instalando herramientas de sistema..."
sudo apt install -y \
    python3 \
    python3-pip \
    python3-dev \
    python3-venv \
    python3-setuptools \
    build-essential \
    git \
    unzip \
    zip \
    autoconf \
    libtool \
    pkg-config \
    cmake \
    libffi-dev \
    libssl-dev

# 3. Instalar dependencias multimedia y gráficas
echo "[*] Instalando librerías multimedia..."
sudo apt install -y \
    ffmpeg \
    libsdl2-dev \
    libsdl2-image-dev \
    libsdl2-mixer-dev \
    libsdl2-ttf-dev \
    libportmidi-dev \
    libswscale-dev \
    libavformat-dev \
    libavcodec-dev \
    zlib1g-dev \
    libncurses-dev \
    libsqlite3-dev

# 4. GStreamer
echo "[*] Instalando GStreamer..."
sudo apt install -y \
    libgstreamer1.0-dev \
    gstreamer1.0-plugins-base \
    gstreamer1.0-plugins-good \
    gstreamer1.0-tools || echo "Aviso: Fallo instalando librerías de gstreamer (no crítico)."

# 5. Java
echo "[*] Instalando Java (JDK)..."
sudo apt install -y default-jdk

# 6. Instalar Buildozer y Cython (Versión GIT necesaria para Python 3.12+)
echo "[*] Instalando versiones compatibles de Buildozer y Cython..."

# Force PIP_BREAK_SYSTEM_PACKAGES para instalación inicial
export PIP_BREAK_SYSTEM_PACKAGES=1

# Definir comando pip con flags necesarios
PIP_CMD="python3 -m pip install --user --upgrade"

# Desinstalar versión vieja si existe
python3 -m pip uninstall -y buildozer || true

# Instalar dependencias críticas
$PIP_CMD setuptools packaging Cython==0.29.33

# Instalar Buildozer desde GIT (Fix para 'No module named distutils' en Py3.12+)
echo "[*] Instalando Buildozer desde código fuente (GitHub)..."
$PIP_CMD git+https://github.com/kivy/buildozer.git

# 7. Añadir binarios locales al PATH
if [[ ":$PATH:" != *":$HOME/.local/bin:"* ]]; then
    if ! grep -q 'export PATH="$HOME/.local/bin:$PATH"' ~/.bashrc; then
        echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.bashrc
    fi
     if ! grep -q 'export PIP_BREAK_SYSTEM_PACKAGES=1' ~/.bashrc; then
        echo 'export PIP_BREAK_SYSTEM_PACKAGES=1 # Fix para Buildozer' >> ~/.bashrc
    fi
    export PATH="$HOME/.local/bin:$PATH"
    echo "[*] PATH actualizado."
fi

echo "=== Configuración Completada ==="
echo "IMPORTANTE: Debian Trixie requiere un flag especial para pip."
echo "Cierra esta terminal y abre una nueva, o ejecuta:"
echo "source ~/.bashrc"
echo "buildozer android debug"
