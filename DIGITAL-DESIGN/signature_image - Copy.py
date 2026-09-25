"""
Módulo para crear imágenes de firma visual
Genera imágenes PNG con la firma y metadatos
"""
from PIL import Image, ImageDraw, ImageFont
import io
import os
from datetime import datetime


class SignatureImage:
    """Generador de imágenes de firma visual"""
    
    def __init__(self, width=300, height=100):
        """
        Inicializa el generador de imágenes de firma
        
        Args:
            width: Ancho de la imagen en píxeles
            height: Alto de la imagen en píxeles
        """
        self.width = width
        self.height = height
    
    def create_signature_image(self, signer_name, signature_date=None, 
                              signature_image_path=None, certificate_info=None):
        """
        Crea una imagen de firma visual
        
        Args:
            signer_name: Nombre del firmante
            signature_date: Fecha de la firma (datetime)
            signature_image_path: Ruta a imagen de firma manuscrita (opcional)
            certificate_info: Información del certificado (dict)
            
        Returns:
            BytesIO: Imagen PNG en memoria
        """
        if signature_date is None:
            signature_date = datetime.now()
        
        # Crear imagen con fondo transparente
        img = Image.new('RGBA', (self.width, self.height), (255, 255, 255, 0))
        draw = ImageDraw.Draw(img)
        
        # Intentar usar una fuente del sistema
        try:
            font_large = ImageFont.truetype("arial.ttf", 16)
            font_small = ImageFont.truetype("arial.ttf", 10)
        except:
            font_large = ImageFont.load_default()
            font_small = ImageFont.load_default()
        
        y_offset = 10
        
        # Si hay imagen de firma manuscrita, insertarla
        if signature_image_path and os.path.exists(signature_image_path):
            try:
                sig_img = Image.open(signature_image_path)
                # Redimensionar manteniendo aspecto
                sig_img.thumbnail((self.width - 20, 50), Image.Resampling.LANCZOS)
                # Pegar en la imagen principal
                img.paste(sig_img, (10, y_offset), sig_img if sig_img.mode == 'RGBA' else None)
                y_offset += sig_img.height + 5
            except Exception as e:
                print(f"Error cargando imagen de firma: {e}")
        
        # Dibujar borde
        draw.rectangle([(0, 0), (self.width-1, self.height-1)], 
                      outline=(0, 0, 0, 180), width=2)
        
        # Texto del firmante
        text = f"Firmado por: {signer_name}"
        draw.text((10, y_offset), text, fill=(0, 0, 0, 255), font=font_large)
        y_offset += 20
        
        # Fecha de firma
        date_text = f"Fecha: {signature_date.strftime('%Y-%m-%d %H:%M:%S')}"
        draw.text((10, y_offset), date_text, fill=(0, 0, 0, 200), font=font_small)
        y_offset += 15
        
        # Información del certificado si está disponible
        if certificate_info:
            cert_text = f"Cert: {certificate_info.get('serial_number', 'N/A')}"
            draw.text((10, y_offset), cert_text, fill=(0, 0, 0, 150), font=font_small)
        
        # Convertir a BytesIO
        img_byte_arr = io.BytesIO()
        img.save(img_byte_arr, format='PNG')
        img_byte_arr.seek(0)
        
        return img_byte_arr
    
    def save_signature_image(self, output_path, signer_name, signature_date=None,
                            signature_image_path=None, certificate_info=None):
        """
        Guarda la imagen de firma en un archivo
        
        Args:
            output_path: Ruta donde guardar la imagen
            signer_name: Nombre del firmante
            signature_date: Fecha de la firma
            signature_image_path: Ruta a imagen de firma manuscrita
            certificate_info: Información del certificado
        """
        img_data = self.create_signature_image(
            signer_name, signature_date, signature_image_path, certificate_info
        )
        
        with open(output_path, 'wb') as f:
            f.write(img_data.getvalue())
