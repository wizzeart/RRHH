"""
Sistema de Firma Digital IML - Versión Web Simple y Funcional
Todo en un solo archivo, sin dependencias de templates externos
"""
from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.responses import HTMLResponse, FileResponse, JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import os
import shutil
import uuid
from datetime import datetime
from pathlib import Path

# Importar módulos core
from word_signer import WordSigner
from certificate_manager import CertificateManager

app = FastAPI(title="IML Digital Signature")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Directorios
BASE_DIR = Path(__file__).parent
UPLOAD_DIR = BASE_DIR / "uploads"
CERT_DIR = BASE_DIR / "certs"
TEMP_DIR = BASE_DIR / "temp"

for d in [UPLOAD_DIR, CERT_DIR, TEMP_DIR]:
    d.mkdir(exist_ok=True)

# Componentes
cert_manager = CertificateManager()
word_signer = WordSigner(cert_manager)

# HTML embebido (sin archivos externos)
HTML_PAGE = """
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IML - Firma Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6 text-blue-600">Sistema de Firma Digital IML</h1>
        
        <!-- Paso 1: Documento -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">1. Documento</h2>
            <input type="file" id="docFile" accept=".docx" class="block w-full border p-2 rounded"/>
            <div id="docStatus" class="mt-2 text-sm"></div>
        </div>
        
        <!-- Paso 2: Firmante -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">2. Firmante</h2>
            <input type="text" id="signerName" placeholder="Nombre completo" class="w-full border p-2 rounded"/>
        </div>
        
        <!-- Paso 3: Firma -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">3. Firma</h2>
            <canvas id="sigPad" width="600" height="150" class="border-2 border-dashed border-gray-300 rounded cursor-crosshair"></canvas>
            <button onclick="clearSig()" class="mt-2 bg-red-500 text-white px-4 py-1 rounded text-sm">Limpiar</button>
        </div>
        
        <!-- Botón Firmar -->
        <button onclick="signDoc()" id="signBtn" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold text-lg hover:bg-blue-700">
            FIRMAR DOCUMENTO
        </button>
        
        <!-- Resultado -->
        <div id="result" class="mt-6 hidden"></div>
    </div>

    <script>
        let uploadedDoc = null;
        const canvas = document.getElementById('sigPad');
        const pad = new SignaturePad(canvas, {penColor: 'rgb(0, 0, 139)'});
        
        function clearSig() { pad.clear(); }
        
        document.getElementById('docFile').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const res = await fetch('/api/upload', {method: 'POST', body: formData});
                const data = await res.json();
                uploadedDoc = data;
                document.getElementById('docStatus').innerHTML = 
                    `<span class="text-green-600">✓ ${data.filename} cargado</span>`;
            } catch (e) {
                alert('Error: ' + e);
            }
        });
        
        async function signDoc() {
            if (!uploadedDoc) return alert('Carga un documento primero');
            if (!document.getElementById('signerName').value) return alert('Ingresa tu nombre');
            if (pad.isEmpty()) return alert('Dibuja tu firma');
            
            const btn = document.getElementById('signBtn');
            btn.disabled = true;
            btn.textContent = 'Procesando...';
            
            try {
                const formData = new FormData();
                formData.append('document_path', uploadedDoc.server_path);
                formData.append('signer_name', document.getElementById('signerName').value);
                
                const blob = await (await fetch(pad.toDataURL())).blob();
                formData.append('signature_image', blob, 'sig.png');
                
                const res = await fetch('/api/sign', {method: 'POST', body: formData});
                const data = await res.json();
                
                if (data.success) {
                    document.getElementById('result').innerHTML = 
                        `<div class="bg-green-100 border border-green-400 p-4 rounded">
                            <p class="font-bold text-green-800">✓ Documento firmado</p>
                            <a href="${data.download_url}" class="inline-block mt-2 bg-green-600 text-white px-4 py-2 rounded">
                                Descargar
                            </a>
                        </div>`;
                    document.getElementById('result').classList.remove('hidden');
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (e) {
                document.getElementById('result').innerHTML = 
                    `<div class="bg-red-100 border border-red-400 p-4 rounded text-red-800">
                        Error: ${e.message}
                    </div>`;
                document.getElementById('result').classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'FIRMAR DOCUMENTO';
            }
        }
    </script>
</body>
</html>
"""

@app.get("/", response_class=HTMLResponse)
async def index():
    """Página principal - HTML embebido"""
    return HTMLResponse(content=HTML_PAGE)

@app.post("/api/upload")
async def upload(file: UploadFile = File(...)):
    """Subir documento"""
    try:
        if not file.filename.endswith('.docx'):
            return JSONResponse({"error": "Solo archivos .docx"}, status_code=400)
        
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{timestamp}_{file.filename}"
        path = UPLOAD_DIR / filename
        
        with open(path, "wb") as f:
            shutil.copyfileobj(file.file, f)
        
        return {
            "success": True,
            "filename": file.filename,
            "server_path": str(path)
        }
    except Exception as e:
        return JSONResponse({"error": str(e)}, status_code=500)

@app.post("/api/sign")
async def sign(
    document_path: str = Form(...),
    signer_name: str = Form(...),
    signature_image: UploadFile = File(...)
):
    """Firmar documento"""
    try:
        doc_path = Path(document_path)
        if not doc_path.exists():
            return JSONResponse({"error": "Documento no encontrado"}, status_code=404)
        
        # Guardar imagen
        sig_path = TEMP_DIR / f"sig_{uuid.uuid4()}.png"
        with open(sig_path, "wb") as f:
            shutil.copyfileobj(signature_image.file, f)
        
        # Configurar firma
        sig_config = {
            "signer_name": signer_name,
            "image_path": str(sig_path),
            "paragraph_index": -1,  # Al final
            "keyword": None,
            "cert_info": None
        }
        
        # Firmar
        visual_bytes = word_signer.batch_visual_sign(str(doc_path), [sig_config])
        
        if not visual_bytes:
            return JSONResponse({"error": "Error en firma visual"}, status_code=500)
        
        # Guardar resultado
        output_name = f"FIRMADO_{datetime.now().strftime('%H%M%S')}_{doc_path.name}"
        output_path = UPLOAD_DIR / output_name
        
        with open(output_path, "wb") as f:
            f.write(visual_bytes)
        
        # Limpiar
        sig_path.unlink(missing_ok=True)
        
        return {
            "success": True,
            "download_url": f"/download/{output_name}",
            "filename": output_name
        }
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        return JSONResponse({"error": str(e)}, status_code=500)

@app.get("/download/{filename}")
async def download(filename: str):
    """Descargar archivo"""
    path = UPLOAD_DIR / filename
    if not path.exists():
        raise HTTPException(404, "Archivo no encontrado")
    return FileResponse(path, filename=filename)

if __name__ == "__main__":
    print("\n" + "="*50)
    print("  IML - Sistema de Firma Digital")
    print("  URL: http://127.0.0.1:8000")
    print("="*50 + "\n")
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")
