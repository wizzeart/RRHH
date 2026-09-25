from fastapi import FastAPI, UploadFile, File, Form, HTTPException, Request, Response
from fastapi.responses import HTMLResponse, FileResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
import uvicorn
import os
import shutil
from datetime import datetime
from PIL import Image
from io import BytesIO
import asyncio
import base64
import json

# Importar lógica existente
from word_signer import WordSigner
from certificate_manager import CertificateManager

app = FastAPI(title="Sistema de Firma Digital IML")

# Configurar directorios
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
STATIC_DIR = os.path.join(BASE_DIR, "static")
TEMPLATES_DIR = os.path.join(BASE_DIR, "templates")
UPLOAD_DIR = os.path.join(BASE_DIR, "uploads")
CERT_DIR = os.path.join(BASE_DIR, "certs")

# Crear directorios necesarios
for d in [STATIC_DIR, TEMPLATES_DIR, UPLOAD_DIR, CERT_DIR]:
    os.makedirs(d, exist_ok=True)

# Montar estáticos y plantillas
app.mount("/static", StaticFiles(directory=STATIC_DIR), name="static")
templates = Jinja2Templates(directory=TEMPLATES_DIR)

# Inicializar componentes
cert_manager = CertificateManager()
word_signer = WordSigner(cert_manager)

@app.get("/", response_class=HTMLResponse)
async def read_root(request: Request):
    return templates.TemplateResponse("index.html", {"request": request})

@app.post("/upload")
async def upload_file(file: UploadFile = File(...)):
    try:
        file_path = os.path.join(UPLOAD_DIR, file.filename)
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
        return {"filename": file.filename, "path": file_path}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/sign")
async def sign_document(
    signer_name: str = Form(...),
    signature_image: UploadFile = File(...),
    document_path: str = Form(...) 
):
    try:
        # 0. Validar documento
        if not os.path.exists(document_path):
             return {"error": "Documento no encontrado"}

        # 1. Guardar imagen de firma temporal
        sig_img_path = os.path.join(UPLOAD_DIR, f"temp_sig_{datetime.now().timestamp()}.png")
        with open(sig_img_path, "wb") as buffer:
            shutil.copyfileobj(signature_image.file, buffer)
            
        # 2. Generar/Cargar Certificado (Auto-generado para demo web)
        cert_name = signer_name.replace(" ", "_").upper()
        cert_path = os.path.join(CERT_DIR, f"{cert_name}_cert.pem")
        key_path = os.path.join(CERT_DIR, f"{cert_name}_key.pem")
        
        cert = None
        key = None
        
        if not os.path.exists(cert_path):
             # FIX: usar generate_certificate en lugar de generate_self_signed_cert
             cert, key = cert_manager.generate_certificate(signer_name, "IML", "CU")
             cert_manager.save_certificate(cert, key, cert_path, key_path)
        else:
             cert, key = cert_manager.load_certificate(cert_path, key_path)
        
        cert_info = cert_manager.get_certificate_info(cert)

        # 3. Preparar datos de firma (Usando la logica de word_signer.py)
        # Adaptada para que funcione con la función batch existente
        # IMPORTANTE: batch_visual_sign espera una lista de diccionarios
        
        sig_data_item = {
            'signer_name': signer_name,
            'image_path': sig_img_path, # Ruta a la imagen temporal
            'cert_info': cert_info,
            'keyword': 'FIRMA_AQUI', # Default
            'paragraph_index': -1,   # Default al final
            'placement_type': 1      # 1 = Párrafo final (Hardcoded por ahora para demo)
        }
        
        sig_data_list = [sig_data_item]
        
        cert_data_list = [{
             'certificate': cert,
             'private_key': key,
             'cert_info': cert_info,
             'signer_name': signer_name
        }]

        # 4. Firmar (Visual)
        # batch_visual_sign devuelve BYTES del docx modificado
        # Necesitamos pasarle la ruta del documento original
        visual_signed_bytes = word_signer.batch_visual_sign(document_path, sig_data_list)
        
        if not visual_signed_bytes:
             return {"error": "Fallo al insertar la firma visual"}

        # Guardar intermedio
        temp_visual_path = os.path.join(UPLOAD_DIR, f"visual_{os.path.basename(document_path)}")
        with open(temp_visual_path, "wb") as f:
            f.write(visual_signed_bytes)
            
        # 5. Firmar (Digital - XMLDSig)
        final_path = os.path.join(UPLOAD_DIR, f"signed_{os.path.basename(document_path)}")
        
        # add_digital_signature_metadata toma (input_path, output_path, certs_list)
        # Usamos el intermedio como input
        success = word_signer.add_digital_signature_metadata(temp_visual_path, final_path, cert_data_list)
        
        # Limpieza imagen
        if os.path.exists(sig_img_path): os.remove(sig_img_path)
        
        if success:
            return FileResponse(final_path, filename=f"signed_{os.path.basename(document_path)}")
        else:
            return {"error": "Fallo en la firma digital XMLDSig"}

    except Exception as e:
        import traceback
        traceback.print_exc()
        return {"error": str(e)}

if __name__ == "__main__":
    uvicorn.run("web_app:app", host="0.0.0.0", port=8000, reload=True)
