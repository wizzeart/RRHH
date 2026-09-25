from fastapi import FastAPI, UploadFile, File, Form, HTTPException, Request, Body
from fastapi.responses import HTMLResponse, FileResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import os
import shutil
import json
import base64
import uuid
from datetime import datetime
from typing import List, Optional

# Importar lógica existente (Core)
from word_signer import WordSigner
from certificate_manager import CertificateManager

# Inicializar App
app = FastAPI(title="Sistema de Firma Digital IML - Web Enterprise")

# CORS (Permitir todo en local)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Directorios
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DIRS = {
    "static": os.path.join(BASE_DIR, "static"),
    "templates": os.path.join(BASE_DIR, "templates"),
    "uploads": os.path.join(BASE_DIR, "uploads"),
    "certs": os.path.join(BASE_DIR, "certs"),
    "temp": os.path.join(BASE_DIR, "temp")
}

for d in DIRS.values():
    os.makedirs(d, exist_ok=True)

app.mount("/static", StaticFiles(directory=DIRS["static"]), name="static")
templates = Jinja2Templates(directory=DIRS["templates"])

# Componentes Core
cert_manager = CertificateManager()
word_signer = WordSigner(cert_manager)

# --- ENDPOINTS VISTAS HTML ---

@app.get("/", response_class=HTMLResponse)
async def home(request: Request):
    return templates.TemplateResponse("app_full.html", {"request": request})

# --- ENDPOINTS API (Gestión Documentos) ---

@app.post("/api/upload")
async def api_upload(file: UploadFile = File(...)):
    try:
        # Limpiar uploads viejos (> 1 hora) - Opcional
        
        file_path = os.path.join(DIRS["uploads"], f"{datetime.now().timestamp()}_{file.filename}")
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
            
        # Analizar documento básico
        try:
            from docx import Document
            doc = Document(file_path)
            paragraphs_preview = [p.text[:50] + "..." for p in doc.paragraphs if p.text.strip()][:20]
        except:
            paragraphs_preview = []

        return {
            "status": "ok",
            "filename": file.filename,
            "server_path": file_path,
            "preview_paragraphs": paragraphs_preview,
            "total_paragraphs": len(doc.paragraphs) if 'doc' in locals() else 0
        }
    except Exception as e:
        return JSONResponse(status_code=500, content={"error": str(e)})

# --- ENDPOINTS API (Gestión Certificados) ---

@app.get("/api/certificates")
async def list_certificates():
    """Devuelve lista de certificados disponibles en la carpeta certs"""
    certs = []
    if os.path.exists(DIRS["certs"]):
        for f in os.listdir(DIRS["certs"]):
            if f.endswith("_cert.pem"):
                # Intentar leer info
                try:
                    name = f.replace("_cert.pem", "")
                    cert_path = os.path.join(DIRS["certs"], f)
                    key_path = os.path.join(DIRS["certs"], f"{name}_key.pem")
                    
                    if os.path.exists(key_path):
                        # Cargar para verificar validez
                        cert, _ = cert_manager.load_certificate(cert_path, key_path)
                        info = cert_manager.get_certificate_info(cert)
                        certs.append({
                            "id": name,
                            "name": info.get('common_name', name),
                            "issuer": info.get('issuer', 'Desconocido'),
                            "valid_until": str(info.get('not_valid_after', ''))
                        })
                except Exception as e:
                    print(f"Error leyendo cert {f}: {e}")
    return certs

@app.post("/api/certificates/create")
async def create_certificate(
    name: str = Form(...),
    org: str = Form(...),
    country: str = Form(...)
):
    try:
        safe_name = name.replace(" ", "_").upper()
        cert_path = os.path.join(DIRS["certs"], f"{safe_name}_cert.pem")
        key_path = os.path.join(DIRS["certs"], f"{safe_name}_key.pem")
        
        # FIX: Correct method name
        cert, key = cert_manager.generate_certificate(name, org, country)
        cert_manager.save_certificate(cert, key, cert_path, key_path)
        
        return {"status": "ok", "msg": f"Certificado para {name} creado correctamente."}
    except Exception as e:
        return JSONResponse(status_code=500, content={"error": str(e)})

# --- ENDPOINTS API (Firma) ---

@app.post("/api/sign/batch")
async def sign_batch(data: dict = Body(...)):
    """
    Recibe un JSON complejo con:
    - document_path: str
    - output_filename: str
    - signatures: Lista de objetos firma:
        - signer_name: str
        - type: 'visual' | 'digital' | 'both'
        - image_data_base64: str (si visual)
        - placement: { type: 'paragraph' | 'keyword', value: ... }
        - certificate_id: str (si digital)
    """
    try:
        doc_path = data.get("document_path")
        if not doc_path or not os.path.exists(doc_path):
            raise Exception("Documento no encontrado en servidor")

        # 1. Procesar firmas visuales
        signatures_config = []
        certs_to_apply = []
        
        used_certificates = set()

        for sig_req in data.get("signatures", []):
            # Guardar imagen temporal
            img_path = None
            if sig_req.get("image_data_base64"):
                img_data = base64.b64decode(sig_req["image_data_base64"].split(",")[1])
                img_path = os.path.join(DIRS["temp"], f"sig_{uuid.uuid4()}.png")
                with open(img_path, "wb") as f:
                    f.write(img_data)
            
            # Configurar firma visual
            if sig_req.get("type") in ["visual", "both"]:
                sig_conf = {
                    "signer_name": sig_req["signer_name"],
                    "image_path": img_path,
                    "paragraph_index": int(sig_req["placement"]["value"]) if sig_req["placement"]["type"] == "paragraph" else None,
                    "keyword": sig_req["placement"]["value"] if sig_req["placement"]["type"] == "keyword" else None,
                    # Añadir info de certificado para texto debajo de firma si existe
                    "cert_info": None 
                }
                
                # Si tiene certificado asociado, cargar info para mostrarla visualmente
                cert_id = sig_req.get("certificate_id")
                if cert_id:
                    c_path = os.path.join(DIRS["certs"], f"{cert_id}_cert.pem")
                    k_path = os.path.join(DIRS["certs"], f"{cert_id}_key.pem")
                    if os.path.exists(c_path):
                        cert_obj, _ = cert_manager.load_certificate(c_path, k_path)
                        sig_conf["cert_info"] = cert_manager.get_certificate_info(cert_obj)
                
                signatures_config.append(sig_conf)

            # Configurar firma digital (XMLDSig)
            if sig_req.get("type") in ["digital", "both"]:
                cert_id = sig_req.get("certificate_id")
                if cert_id and cert_id not in used_certificates:
                    c_path = os.path.join(DIRS["certs"], f"{cert_id}_cert.pem")
                    k_path = os.path.join(DIRS["certs"], f"{cert_id}_key.pem")
                    
                    if os.path.exists(c_path) and os.path.exists(k_path):
                        cert_obj, key_obj = cert_manager.load_certificate(c_path, k_path)
                        certs_to_apply.append({
                            "certificate": cert_obj,
                            "private_key": key_obj,
                            "cert_info": cert_manager.get_certificate_info(cert_obj),
                            "signer_name": sig_req["signer_name"]
                        })
                        used_certificates.add(cert_id)

        # 2. Ejecutar Firma Visual (Modifica el contenido)
        current_doc_path = doc_path
        
        if signatures_config:
            # WordSigner.batch_visual_sign retorna bytes
            visual_bytes = word_signer.batch_visual_sign(current_doc_path, signatures_config)
            
            # Guardar resultado visual temporal
            temp_visual = os.path.join(DIRS["temp"], f"visual_{uuid.uuid4()}.docx")
            with open(temp_visual, "wb") as f:
                f.write(visual_bytes)
            current_doc_path = temp_visual

        # 3. Ejecutar Firma Digital (XMLDSig)
        final_filename = f"FIRMADO_{datetime.now().strftime('%H%M%S')}_{data.get('original_filename', 'doc.docx')}"
        final_path = os.path.join(DIRS["uploads"], final_filename)
        
        if certs_to_apply:
            word_signer.add_digital_signature_metadata(current_doc_path, final_path, certs_to_apply)
        else:
            # Si solo fue visual, copiar el temp al final
            shutil.copy2(current_doc_path, final_path)

        return {
            "status": "ok",
            "download_url": f"/download/{final_filename}",
            "filename": final_filename
        }

    except Exception as e:
        import traceback
        traceback.print_exc()
        return JSONResponse(status_code=500, content={"error": str(e)})

@app.get("/download/{filename}")
async def download_file(filename: str):
    path = os.path.join(DIRS["uploads"], filename)
    if os.path.exists(path):
        return FileResponse(path, filename=filename)
    return HTTPException(404)

if __name__ == "__main__":
    uvicorn.run("web_app_full:app", host="0.0.0.0", port=8000, reload=True)
