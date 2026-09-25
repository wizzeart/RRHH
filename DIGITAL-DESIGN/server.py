"""
API Backend para Sistema de Firma Digital IML
Arquitectura profesional con FastAPI
"""
from fastapi import FastAPI, UploadFile, File, Form, Request, HTTPException
from fastapi.responses import HTMLResponse, FileResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
from typing import Optional, List
import uvicorn
import os
import shutil
import base64
import uuid
from datetime import datetime
from pathlib import Path
import logging

# Configurar logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Importar módulos core
try:
    from word_signer import WordSigner
    from certificate_manager import CertificateManager
except ImportError as e:
    logger.error(f"Error importando módulos core: {e}")
    raise

# Inicializar FastAPI
app = FastAPI(
    title="IML Digital Signature System",
    description="Sistema profesional de firma digital para documentos Word",
    version="2.0.0"
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configuración de directorios
BASE_DIR = Path(__file__).parent
UPLOAD_DIR = BASE_DIR / "uploads"
CERT_DIR = BASE_DIR / "certs"
TEMP_DIR = BASE_DIR / "temp"
STATIC_DIR = BASE_DIR / "static"
TEMPLATE_DIR = BASE_DIR / "templates"

# Crear directorios
for directory in [UPLOAD_DIR, CERT_DIR, TEMP_DIR, STATIC_DIR, TEMPLATE_DIR]:
    directory.mkdir(exist_ok=True)

# Configurar templates y static files
try:
    app.mount("/static", StaticFiles(directory=str(STATIC_DIR)), name="static")
    templates = Jinja2Templates(directory=str(TEMPLATE_DIR))
except Exception as e:
    logger.warning(f"No se pudo montar static/templates: {e}")

# Inicializar componentes
cert_manager = CertificateManager()
word_signer = WordSigner(cert_manager)

# ============================================================================
# ENDPOINTS - VISTAS
# ============================================================================

@app.get("/", response_class=HTMLResponse)
async def index(request: Request):
    """Página principal"""
    try:
        return templates.TemplateResponse("index_pro.html", {"request": request})
    except Exception as e:
        logger.error(f"Error cargando template: {e}")
        return HTMLResponse(content=f"<h1>Error: {e}</h1><p>Verifica que templates/index_pro.html exista</p>", status_code=500)

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "version": "2.0.0"
    }

# ============================================================================
# ENDPOINTS - API DOCUMENTOS
# ============================================================================

@app.post("/api/documents/upload")
async def upload_document(file: UploadFile = File(...)):
    """
    Subir documento Word para firmar
    """
    try:
        # Validar extensión
        if not file.filename.endswith('.docx'):
            raise HTTPException(status_code=400, detail="Solo se permiten archivos .docx")
        
        # Generar nombre único
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        safe_filename = f"{timestamp}_{file.filename}"
        file_path = UPLOAD_DIR / safe_filename
        
        # Guardar archivo
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
        
        # Analizar estructura del documento
        try:
            from docx import Document
            doc = Document(str(file_path))
            paragraphs = [
                {"index": i, "text": p.text[:100]} 
                for i, p in enumerate(doc.paragraphs) 
                if p.text.strip()
            ][:50]  # Limitar a 50 párrafos
        except Exception as e:
            logger.warning(f"No se pudo analizar documento: {e}")
            paragraphs = []
        
        return {
            "success": True,
            "filename": file.filename,
            "server_path": str(file_path),
            "size": file_path.stat().st_size,
            "paragraphs": paragraphs,
            "total_paragraphs": len(paragraphs)
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error subiendo documento: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================================================
# ENDPOINTS - API CERTIFICADOS
# ============================================================================

@app.get("/api/certificates")
async def list_certificates():
    """
    Listar certificados disponibles
    """
    try:
        certificates = []
        
        for cert_file in CERT_DIR.glob("*_cert.pem"):
            try:
                name = cert_file.stem.replace("_cert", "")
                key_file = CERT_DIR / f"{name}_key.pem"
                
                if not key_file.exists():
                    continue
                
                # Cargar certificado
                cert, _ = cert_manager.load_certificate(str(cert_file), str(key_file))
                info = cert_manager.get_certificate_info(cert)
                
                certificates.append({
                    "id": name,
                    "name": info.get("common_name", name),
                    "organization": info.get("organization", "N/A"),
                    "country": info.get("country", "N/A"),
                    "valid_until": info.get("not_valid_after", "").isoformat() if hasattr(info.get("not_valid_after", ""), "isoformat") else str(info.get("not_valid_after", ""))
                })
                
            except Exception as e:
                logger.warning(f"Error leyendo certificado {cert_file}: {e}")
                continue
        
        return {"success": True, "certificates": certificates}
        
    except Exception as e:
        logger.error(f"Error listando certificados: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/certificates/create")
async def create_certificate(
    name: str = Form(...),
    organization: str = Form("IML"),
    country: str = Form("CU")
):
    """
    Crear nuevo certificado autofirmado
    """
    try:
        # Validar entrada
        if not name or len(name) < 2:
            raise HTTPException(status_code=400, detail="Nombre inválido")
        
        if len(country) != 2:
            raise HTTPException(status_code=400, detail="Código de país debe ser de 2 letras")
        
        # Generar nombre seguro
        safe_name = name.replace(" ", "_").upper()
        cert_path = CERT_DIR / f"{safe_name}_cert.pem"
        key_path = CERT_DIR / f"{safe_name}_key.pem"
        
        # Verificar si ya existe
        if cert_path.exists():
            raise HTTPException(status_code=409, detail="Ya existe un certificado con ese nombre")
        
        # Generar certificado
        cert, key = cert_manager.generate_certificate(name, organization, country)
        cert_manager.save_certificate(cert, key, str(cert_path), str(key_path))
        
        logger.info(f"Certificado creado: {safe_name}")
        
        return {
            "success": True,
            "message": f"Certificado '{name}' creado exitosamente",
            "certificate_id": safe_name
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error creando certificado: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================================================
# ENDPOINTS - API FIRMA
# ============================================================================

@app.post("/api/sign/document")
async def sign_document(
    document_path: str = Form(...),
    signer_name: str = Form(...),
    signature_image: UploadFile = File(...),
    certificate_id: Optional[str] = Form(None),
    placement_type: str = Form("end"),  # end, paragraph, keyword
    placement_value: Optional[str] = Form(None)
):
    """
    Firmar documento (versión simple - un firmante)
    """
    try:
        # Validar documento
        doc_path = Path(document_path)
        if not doc_path.exists():
            raise HTTPException(status_code=404, detail="Documento no encontrado")
        
        # Guardar imagen de firma
        sig_img_path = TEMP_DIR / f"sig_{uuid.uuid4()}.png"
        with open(sig_img_path, "wb") as buffer:
            shutil.copyfileobj(signature_image.file, buffer)
        
        # Preparar configuración de firma visual
        sig_config = {
            "signer_name": signer_name,
            "image_path": str(sig_img_path),
            "paragraph_index": None,
            "keyword": None,
            "cert_info": None
        }
        
        # Configurar ubicación
        if placement_type == "paragraph" and placement_value:
            sig_config["paragraph_index"] = int(placement_value)
        elif placement_type == "keyword" and placement_value:
            sig_config["keyword"] = placement_value
        else:
            sig_config["paragraph_index"] = -1  # Final del documento
        
        # Cargar certificado si se especificó
        cert_obj = None
        key_obj = None
        if certificate_id:
            cert_path = CERT_DIR / f"{certificate_id}_cert.pem"
            key_path = CERT_DIR / f"{certificate_id}_key.pem"
            
            if cert_path.exists() and key_path.exists():
                cert_obj, key_obj = cert_manager.load_certificate(str(cert_path), str(key_path))
                sig_config["cert_info"] = cert_manager.get_certificate_info(cert_obj)
        
        # Aplicar firma visual
        logger.info(f"Aplicando firma visual para {signer_name}")
        visual_bytes = word_signer.batch_visual_sign(str(doc_path), [sig_config])
        
        if not visual_bytes:
            raise HTTPException(status_code=500, detail="Error aplicando firma visual")
        
        # Guardar resultado temporal
        temp_path = TEMP_DIR / f"visual_{uuid.uuid4()}.docx"
        with open(temp_path, "wb") as f:
            f.write(visual_bytes)
        
        # Aplicar firma digital si hay certificado
        output_filename = f"FIRMADO_{datetime.now().strftime('%Y%m%d_%H%M%S')}_{doc_path.name}"
        output_path = UPLOAD_DIR / output_filename
        
        if cert_obj and key_obj:
            logger.info(f"Aplicando firma digital con certificado {certificate_id}")
            cert_data = [{
                "certificate": cert_obj,
                "private_key": key_obj,
                "cert_info": cert_manager.get_certificate_info(cert_obj),
                "signer_name": signer_name
            }]
            word_signer.add_digital_signature_metadata(str(temp_path), str(output_path), cert_data)
        else:
            # Solo firma visual
            shutil.copy2(temp_path, output_path)
        
        # Limpiar archivos temporales
        sig_img_path.unlink(missing_ok=True)
        temp_path.unlink(missing_ok=True)
        
        logger.info(f"Documento firmado exitosamente: {output_filename}")
        
        return {
            "success": True,
            "message": "Documento firmado exitosamente",
            "download_url": f"/api/download/{output_filename}",
            "filename": output_filename
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error firmando documento: {e}", exc_info=True)
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/download/{filename}")
async def download_file(filename: str):
    """
    Descargar archivo firmado
    """
    try:
        file_path = UPLOAD_DIR / filename
        
        if not file_path.exists():
            raise HTTPException(status_code=404, detail="Archivo no encontrado")
        
        return FileResponse(
            path=str(file_path),
            filename=filename,
            media_type="application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error descargando archivo: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================================================
# MAIN
# ============================================================================

if __name__ == "__main__":
    logger.info("Iniciando servidor IML Digital Signature System...")
    uvicorn.run(
        "server:app",
        host="0.0.0.0",
        port=8000,
        reload=True,
        log_level="info"
    )
