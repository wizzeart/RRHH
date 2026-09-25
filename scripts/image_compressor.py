#!/usr/bin/env python3
"""
Script de compresión de imágenes para ser llamado desde PHP
Uso: python image_compressor.py <input_file> [output_file] [quality_low] [quality_high] [complexity_threshold]
"""

import sys
import os
import json
import argparse
from PIL import Image, ImageFilter
import numpy as np
import io

# Configuración por defecto
FULL_HD_WIDTH = 1920
FULL_HD_HEIGHT = 1080
TARGET_HEIGHT = 360  # 360p

def is_full_hd_or_more(img: Image.Image) -> bool:
    """Verifica si la imagen es Full HD o mayor"""
    w, h = img.size
    return w >= FULL_HD_WIDTH or h >= FULL_HD_HEIGHT

def resize_to_360p(img: Image.Image) -> Image.Image:
    """Redimensiona la imagen a 360p manteniendo proporción"""
    w, h = img.size
    scale = TARGET_HEIGHT / h
    new_w = int(w * scale)
    return img.resize((new_w, TARGET_HEIGHT), Image.LANCZOS)

def complexity_pillow(img: Image.Image) -> float:
    """Calcula la complejidad de la imagen basada en detección de bordes"""
    edges = img.filter(ImageFilter.FIND_EDGES)
    gray = edges.convert("L")
    arr = np.array(gray, dtype=np.float32)
    return arr.mean() / 255.0

def compress_image(input_path, output_path=None, quality_low=70, quality_high=85, complexity_threshold=0.02):
    """
    Comprime una imagen usando la lógica del proyecto
    
    Args:
        input_path: Ruta del archivo de entrada
        output_path: Ruta del archivo de salida (opcional)
        quality_low: Calidad para imágenes simples
        quality_high: Calidad para imágenes complejas
        complexity_threshold: Umbral de complejidad
    
    Returns:
        dict: Resultado de la compresión con estadísticas
    """
    try:
        # Verificar que el archivo existe
        if not os.path.exists(input_path):
            return {
                "success": False,
                "error": f"Archivo de entrada no encontrado: {input_path}"
            }
        
        # Verificar tamaño del archivo
        file_size = os.path.getsize(input_path)
        if file_size == 0:
            return {
                "success": False,
                "error": f"Archivo vacío: {input_path}"
            }
        
        # Leer imagen
        with open(input_path, 'rb') as f:
            original_blob = f.read()
        
        original_size = len(original_blob)
        
        # Abrir imagen con PIL
        img = Image.open(io.BytesIO(original_blob)).convert("RGB")
        original_w, original_h = img.size
        
        # Redimensionar si es necesario
        resized = False
        if is_full_hd_or_more(img):
            img = resize_to_360p(img)
            resized = True
        
        # Calcular complejidad y determinar calidad
        complexity = complexity_pillow(img)
        quality = quality_high if complexity > complexity_threshold else quality_low
        
        # Comprimir a WebP
        out = io.BytesIO()
        img.save(out, format="WEBP", quality=quality, method=6)
        compressed_data = out.getvalue()
        compressed_size = len(compressed_data)
        
        # Guardar en archivo si se especificó output_path
        if output_path:
            with open(output_path, 'wb') as f:
                f.write(compressed_data)
        
        # Retornar resultados
        return {
            "success": True,
            "original_size": original_size,
            "compressed_size": compressed_size,
            "compression_ratio": round((1 - compressed_size / original_size) * 100, 2),
            "quality": quality,
            "resized": resized,
            "original_resolution": f"{original_w}x{original_h}",
            "final_resolution": f"{img.size[0]}x{img.size[1]}",
            "complexity": float(round(complexity, 4)),
            "format": "WEBP"
        }
        
    except Exception as e:
        return {
            "success": False,
            "error": str(e)
        }

def main():
    """Función principal para ejecución desde línea de comandos"""
    parser = argparse.ArgumentParser(description='Compresor de imágenes')
    parser.add_argument('input_file', help='Archivo de imagen de entrada')
    parser.add_argument('output_file', nargs='?', help='Archivo de salida (opcional)')
    parser.add_argument('--quality-low', type=int, default=70, help='Calidad para imágenes simples')
    parser.add_argument('--quality-high', type=int, default=85, help='Calidad para imágenes complejas')
    parser.add_argument('--complexity-threshold', type=float, default=0.02, help='Umbral de complejidad')
    parser.add_argument('--json', action='store_true', help='Output en formato JSON')
    
    args = parser.parse_args()
    
    # Verificar que el archivo de entrada existe
    if not os.path.exists(args.input_file):
        print(json.dumps({"success": False, "error": "Archivo de entrada no encontrado"}))
        sys.exit(1)
    
    # Generar nombre de salida si no se proporcionó
    if not args.output_file:
        name, _ = os.path.splitext(args.input_file)
        args.output_file = f"{name}_compressed.webp"
    
    # Comprimir imagen
    result = compress_image(
        args.input_file,
        args.output_file,
        args.quality_low,
        args.quality_high,
        args.complexity_threshold
    )
    
    # Output
    if args.json:
        print(json.dumps(result, indent=2))
    else:
        if result["success"]:
            print(f"✅ Compresión exitosa")
            print(f"📁 Archivo de entrada: {args.input_file}")
            print(f"📁 Archivo de salida: {args.output_file}")
            print(f"📏 Tamaño original: {result['original_size']:,} bytes ({result['original_size']/1024:.1f} KB)")
            print(f"📏 Tamaño comprimido: {result['compressed_size']:,} bytes ({result['compressed_size']/1024:.1f} KB)")
            print(f"📊 Ratio de compresión: {result['compression_ratio']}%")
            print(f"🎨 Calidad usada: {result['quality']}")
            print(f"📐 Resolución original: {result['original_resolution']}")
            print(f"📐 Resolución final: {result['final_resolution']}")
            print(f"🔄 Redimensionado: {'Sí' if result['resized'] else 'No'}")
            print(f"🧠 Complejidad: {result['complexity']}")
        else:
            print(f"❌ Error: {result['error']}")
            sys.exit(1)

if __name__ == "__main__":
    main()
