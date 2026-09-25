"""
Sistema de Firma Digital IML - Versión Web Completa
Incluye todas las funcionalidades del sistema de escritorio
"""
from fastapi import FastAPI, UploadFile, File, Form, HTTPException, Body
from fastapi.responses import HTMLResponse, FileResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import os
import shutil
import uuid
import base64
import io
import tempfile
from datetime import datetime
from pathlib import Path
from typing import Optional, List
import json

# Importar módulos core
from word_signer import WordSigner
from certificate_manager import CertificateManager
from signature_image import SignatureImage
from docx import Document
from docx.text.paragraph import Paragraph
from docx.table import Table, _Cell
from docx.oxml.text.paragraph import CT_P
from docx.oxml.table import CT_Tbl
from spire.doc import Document as SpireDocument
from spire.doc import FileFormat, ImageType

app = FastAPI(title="IML Digital Signature - Full Version")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Directorios
BASE_DIR = Path(__file__).parent

# Montar estáticos para iconografía y recursos
STATIC_DIR = BASE_DIR / "static"
STATIC_DIR.mkdir(exist_ok=True)
app.mount("/static", StaticFiles(directory=str(STATIC_DIR)), name="static")

UPLOAD_DIR = BASE_DIR / "uploads"
CERT_DIR = BASE_DIR / "certs"
TEMP_DIR = BASE_DIR / "temp"
PREVIEWS_DIR = STATIC_DIR / "previews"

for d in [UPLOAD_DIR, CERT_DIR, TEMP_DIR, PREVIEWS_DIR]:
    d.mkdir(exist_ok=True)

# Componentes
cert_manager = CertificateManager()
word_signer = WordSigner(cert_manager)

# HTML completo con todas las funcionalidades - Versión Premium
HTML_FULL = """
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IML Digital Sign Pro - Sistema Profesional de Firma</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lexend:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary: #64748b;
            --accent: #f59e0b;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Lexend', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .sig-canvas { border: 2px dashed #cbd5e1; border-radius: 12px; background: #ffffff; cursor: crosshair; transition: all 0.2s; touch-action: none !important; user-select: none; -webkit-user-select: none; }
        .sig-canvas:hover { border-color: var(--primary); background: #f8fafc; }
        .signer-card { border: 1px solid #e2e8f0; border-radius: 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: #ffffff; }
        .signer-card:hover { transform: translateY(-4px); shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #bfdbfe; }
        .btn-primary { background: var(--primary); transition: all 0.2s; }
        .btn-primary:hover { background: var(--primary-hover); transform: scale(1.02); }
        .btn-secondary { background: #f1f5f9; color: #475569; transition: all 0.2s; }
        .btn-secondary:hover { background: #e2e8f0; }
        .step-inactive { color: #94a3b8; }
        .step-active { color: var(--primary); border-color: var(--primary); }
        .doc-preview-item { border-left: 3px solid transparent; transition: all 0.2s; }
        .doc-preview-item:hover { border-left-color: var(--primary); background: #f1f5f9; }
        
        /* Estilos Picker de Coordenadas */
        /* Estilos Picker de Coordenadas Profesional */
        .page-view-container { display: flex; flex-direction: column; gap: 30px; padding: 50px 0; align-items: center; background: #e2e8f0; }
        .page-image-wrapper { position: relative; box-shadow: 0 20px 50px rgba(0,0,0,0.2); background: white; cursor: crosshair; transition: transform 0.2s; }
        .page-image-wrapper:hover { transform: scale(1.005); }
        .page-image-wrapper img { display: block; width: 210mm; height: auto; border: 1px solid #cbd5e1; }
        .page-label { position: absolute; left: -60px; top: 0; background: white; padding: 8px 12px; border-radius: 8px; font-weight: bold; font-size: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); color: #64748b; border: 1px solid #e2e8f0; }
        
        .coord-marker { position: absolute; width: 120px; height: 60px; background: rgba(37, 99, 235, 0.15); border: 2px solid #2563eb; pointer-events: none; transform: translate(-50%, -50%); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #2563eb; font-weight: bold; font-size: 11px; z-index: 100; backdrop-filter: blur(2px); }
        .draggable-x { position: absolute; width: 60px; height: 60px; cursor: move; z-index: 500; display: flex; align-items: center; justify-content: center; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3)); user-select: none; background: rgba(255,255,255,0.7); border: 2px solid currentColor; border-radius: 50%; backdrop-filter: blur(4px); box-shadow: inset 0 0 10px rgba(0,0,0,0.05); }
        .draggable-x svg { width: 32px; height: 32px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.2)); }
        .draggable-x::after { content: 'ARRASTRA'; position: absolute; top: 110%; font-size: 8px; font-weight: bold; background: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 10px; white-space: nowrap; pointer-events: none; }
        .static-marker { position: absolute; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; font-weight: bold; pointer-events: none; z-index: 150; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border: 2px solid white; }
        
        .page-simulation { display: none; } /* Ocultar el simulador anterior */
        
        /* Animaciones */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        .animate-scale-in { animation: scaleIn 0.3s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center overflow-hidden shadow-lg shadow-blue-100">
                        <img src="/static/icon.png" class="w-full h-full object-cover" alt="Logo">
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-slate-800 tracking-tight">IML Sign Pro</span>
                        <div class="text-[10px] text-blue-600 font-bold tracking-widest uppercase">Digital Signature</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <button onclick="showTab('sign')" id="nav-sign" class="text-blue-600 font-bold border-b-2 border-blue-600 pb-1">Firmar Documento</button>
                    <button onclick="showTab('certs')" id="nav-certs" class="text-slate-500 font-medium hover:text-blue-600 transition-colors">Gestión de Certificados</button>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-end">
                        <span class="text-sm font-semibold text-slate-700">Pedro Manduley</span>
                        <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Administrador</span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold ring-2 ring-slate-100">PM</div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Main Content Area -->
        <main id="content-sign" class="animate-fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Setup (Visual) -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <!-- Section Documento -->
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                                <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm">01</span>
                                Seleccionar Documento
                            </h2>
                            <div id="file-status" class="hidden flex items-center gap-2 text-green-600 font-bold text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                                Documento Listo
                            </div>
                        </div>
                        <div class="p-8">
                            <input type="file" id="docFile" accept=".docx" onchange="handleFileSelect(event)" 
                                   style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0;"/>
                            
                            <label for="docFile" id="upload-zone" 
                                 ondragover="handleDragOver(event)" 
                                 onmouseleave="handleDragLeave(event)"
                                 ondragleave="handleDragLeave(event)"
                                 ondrop="handleDrop(event)"
                                 class="block border-2 border-dashed border-slate-200 rounded-2xl p-10 text-center hover:border-blue-400 hover:bg-blue-50/30 transition-all cursor-pointer group">
                                <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform pointer-events-none">
                                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-700 mb-1 pointer-events-none tracking-tight">Cargar Archivo de Word</h3>
                                <p class="text-sm text-slate-500 pointer-events-none">Haz clic aquí o arrastra tu archivo .docx</p>
                            </label>
                            
                            <div id="docInfo" class="mt-6 hidden animate-fade-in">
                                <div class="bg-slate-900 rounded-xl p-5 text-white flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zM10 10V6L14 10h-4z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div>
                                            <p id="docName" class="font-bold text-sm truncate max-w-[200px] sm:max-w-md italic tracking-wide">documento.docx</p>
                                            <p id="docMeta" class="text-xs text-slate-400">Párrafos analizados: <span id="docParagraphs">0</span></p>
                                        </div>
                                    </div>
                                    <button onclick="document.getElementById('docFile').click()" class="text-xs bg-slate-800 hover:bg-slate-700 px-3 py-2 rounded-lg transition-colors">Cambiar Archivo</button>
                                </div>
                                
                                <div class="mt-4 border border-slate-200 rounded-xl overflow-hidden">
                                    <button onclick="togglePreview()" class="w-full px-4 py-3 bg-white text-left font-bold text-slate-700 flex justify-between items-center hover:bg-slate-50 transition-colors">
                                        <span>Vista Previa de Párrafos</span>
                                        <svg id="preview-chevron" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div id="paragraphList" class="hidden max-h-80 overflow-y-auto bg-slate-50 border-t border-slate-200 p-2 divide-y divide-slate-100 italic text-slate-600 text-sm">
                                        <!-- Content dynamic -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section Firmantes -->
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                                <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm">02</span>
                                Configuración de Firmantes
                            </h2>
                            <button onclick="addSigner()" class="text-sm bg-blue-50 text-blue-700 font-bold px-4 py-2 rounded-full hover:bg-blue-100 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Nuevo Firmante
                            </button>
                        </div>
                        <div class="p-6">
                            <div id="signersList" class="space-y-6">
                                <!-- Firmantes dinámicos -->
                            </div>
                            <div id="no-signers" class="text-center py-12 text-slate-400">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-lg">No has añadido firmantes aún</p>
                                <p class="text-sm">Agrega al menos una persona para procesar la firma</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Sidebar / Status -->
                <div class="lg:col-span-4 space-y-8">
                    
                    <!-- Resumen del Proceso -->
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-28">
                        <h2 class="text-lg font-bold text-slate-800 mb-6">Resumen del Proceso</h2>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center gap-4">
                                <div id="status-step-1" class="w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center text-xs font-bold transition-all">1</div>
                                <span class="text-sm text-slate-600 font-medium">Documento Seleccionado</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div id="status-step-2" class="w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center text-xs font-bold transition-all">2</div>
                                <span class="text-sm text-slate-600 font-medium">Configuración de Firmas (<span id="signers-count">0</span>)</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div id="status-step-3" class="w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center text-xs font-bold transition-all">3</div>
                                <span class="text-sm text-slate-600 font-medium">Certificación Digital</span>
                            </div>
                        </div>

                        <div class="p-4 bg-blue-50 rounded-xl mb-6">
                            <p class="text-xs text-blue-700 font-bold uppercase tracking-widest mb-1 italic">Seguridad Garantizada</p>
                            <p class="text-[11px] text-blue-600 leading-relaxed">Este proceso generará firmas visuales y metadatos XMLDSig auditables compatibles con MS Word y Acrobat.</p>
                        </div>

                        <button onclick="signDocument()" id="signBtn" disabled class="w-full bg-slate-200 text-slate-500 py-4 rounded-xl font-bold text-lg cursor-not-allowed transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Inicia la Firma
                        </button>
                        
                        <div id="result" class="mt-6 hidden animate-fade-in">
                            <!-- Resultado exitoso -->
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <!-- TAB: Certificados Pro -->
        <main id="content-certs" class="hidden animate-fade-in space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">Almacén de Certificados Digitales</h2>
                        <p class="text-sm text-slate-500">Administra tus llaves privadas y certificados X.509 para firmas de auditoría</p>
                    </div>
                    <button onclick="toggleNewCertModal()" class="btn-primary text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2">
                        + Generar Nuevo Certificado
                    </button>
                </div>
                
                <div class="p-8">
                    <div id="certsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Certificados dinámicos -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="certModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-800 tracking-tight">Nuevo Certificado Digital</h3>
                <button onclick="toggleNewCertModal()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 tracking-tight">Nombre Completo (Common Name)</label>
                    <input type="text" id="certName" placeholder="Ej: Dr. Juan Pérez" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"/>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 tracking-tight">Organización</label>
                        <input type="text" id="certOrg" value="IML LEGAL" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"/>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 tracking-tight">País (ISO Code)</label>
                        <input type="text" id="certCountry" value="CU" maxlength="2" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 uppercase focus:border-transparent outline-none transition-all"/>
                    </div>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl">
                    <p class="text-xs text-blue-600 font-medium leading-relaxed italic">Este certificado será de auto-firma (Self-Signed) válido por 1 año para procesos de auditoría interna.</p>
                </div>
                <button onclick="createCert()" id="createCertBtn" class="w-full btn-primary text-white py-4 rounded-xl font-bold transition-all shadow-lg active:scale-95">
                    Generar Llave y Certificado
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Picker de Coordenadas -->
    <div id="coordModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[70] flex flex-col items-center p-4 sm:p-8 overflow-hidden">
        <div class="w-full max-w-5xl bg-slate-100 rounded-3xl shadow-2xl flex flex-col h-full overflow-hidden animate-scale-in">
            <div class="p-6 bg-white border-b border-slate-200 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Selector de Posición Visual</h3>
                    <p class="text-xs text-slate-500 font-medium">Haz clic en el lugar exacto donde deseas situar la firma</p>
                </div>
                <div class="flex items-center gap-4">
                    <button id="toggle-drag-mode" onclick="toggleDragMode()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-2 border border-slate-200">
                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path></svg>
                        Modo Arrastre (X)
                    </button>
                    <span id="coord-info" class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100">Sin posición seleccionada</span>
                    <button onclick="closeCoordPicker()" class="text-slate-400 hover:text-red-500 p-2 transition-colors">✕</button>
                </div>
            </div>
            
            <div id="coordPickerBody" class="flex-1 overflow-y-auto p-4 sm:p-12 bg-slate-200/50">
                <!-- Aquí se renderiza el documento -->
                <div id="document-simulation" class="page-simulation group">
                    <!-- Párrafos dinámicos -->
                </div>
            </div>
            
            <div class="p-6 bg-white border-t border-slate-200 flex justify-end gap-3 shrink-0">
                <button onclick="closeCoordPicker()" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-all">Cancelar</button>
                <button id="confirmCoordsBtn" onclick="saveCoords()" disabled class="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-bold opacity-50 cursor-not-allowed hover:bg-blue-700 transition-all shadow-lg active:scale-95">
                    Confirmar Ubicación
                </button>
            </div>
        </div>
    </div>

    <script>
        let uploadedDoc = null;
        let signers = [];
        let signerCounter = 0;
        let certificates = [];
        
        // Coordenadas Temp
        let currentPickingSignerId = null;
        let selectedPlacementData = null;
        let isDragMode = false;
        let dragTarget = null;

        // Tab Handling
        function showTab(tab) {
            document.querySelectorAll('main').forEach(m => m.classList.add('hidden'));
            document.getElementById(`content-${tab}`).classList.remove('hidden');
            
            // UI Nav Updates
            const navSign = document.getElementById('nav-sign');
            const navCerts = document.getElementById('nav-certs');
            
            if (tab === 'sign') {
                navSign.className = "text-blue-600 font-bold border-b-2 border-blue-600 pb-1";
                navCerts.className = "text-slate-500 font-medium hover:text-blue-600 transition-colors";
            } else {
                navCerts.className = "text-blue-600 font-bold border-b-2 border-blue-600 pb-1";
                navSign.className = "text-slate-500 font-medium hover:text-blue-600 transition-colors";
                loadCertificates();
            }
        }

        function toggleNewCertModal() {
            document.getElementById('certModal').classList.toggle('hidden');
        }

        function resetApp() {
            // 1. Limpiar estado global
            uploadedDoc = null;
            signers = [];
            signerCounter = 0;
            
            // 2. Limpiar UI de Documento
            document.getElementById('docInfo').classList.add('hidden');
            document.getElementById('file-status').classList.add('hidden');
            document.getElementById('docFile').value = '';
            resetUploadZone();
            
            // 3. Limpiar UI de Firmantes
            const list = document.getElementById('signersList');
            list.innerHTML = '';
            document.getElementById('no-signers').classList.remove('hidden');
            
            // 4. Limpiar Resultados y Botón Principal
            document.getElementById('result').classList.add('hidden');
            document.getElementById('result').innerHTML = '';
            
            const btn = document.getElementById('signBtn');
            btn.classList.remove('hidden');
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Inicia la Firma`;
            
            // 5. Reset pasos
            updateStepStatus(1, false);
            updateStepStatus(2, false);
            updateStepStatus(3, false);
            updateGlobalStats();
            
            // Scroll arriba suave
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function togglePreview() {
            const list = document.getElementById('paragraphList');
            const chevron = document.getElementById('preview-chevron');
            list.classList.toggle('hidden');
            chevron.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        // File Selection Logic
        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;
            uploadFile(file);
        }

        // Drag and Drop support
        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('upload-zone').classList.add('border-blue-500', 'bg-blue-50/50');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('upload-zone').classList.remove('border-blue-500', 'bg-blue-50/50');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('upload-zone').classList.remove('border-blue-500', 'bg-blue-50/50');
            
            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.docx')) {
                uploadFile(file);
            } else {
                alert('Por favor, arrastra solo archivos .docx');
            }
        }

        async function uploadFile(file) {
            const uploadZone = document.getElementById('upload-zone');
            uploadZone.innerHTML = `
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-sm font-bold text-slate-600 tracking-wide">Analizando estructura del documento...</p>
                </div>
            `;
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const res = await fetch('/api/upload', {method: 'POST', body: formData});
                const data = await res.json();
                
                if (data.success) {
                    uploadedDoc = data;
                    document.getElementById('docName').textContent = file.name;
                    document.getElementById('docParagraphs').textContent = data.total_paragraphs;
                    document.getElementById('docInfo').classList.remove('hidden');
                    document.getElementById('file-status').classList.remove('hidden');
                    uploadZone.classList.add('hidden');
                    
                    // Render paragraphs lists con seguridad
                    const list = document.getElementById('paragraphList');
                    list.innerHTML = ''; // Limpiar previo
                    
                    if (data.paragraphs && data.paragraphs.length > 0) {
                        data.paragraphs.forEach(p => {
                            const div = document.createElement('div');
                            div.className = "doc-preview-item py-2 px-3 border-b border-slate-50";
                            div.innerHTML = `
                                <span class="text-[10px] font-bold text-blue-500 mr-2 uppercase tracking-tighter">[P${p.index}]</span> 
                                <span class="text-slate-600">${p.text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</span>
                            `;
                            list.appendChild(div);
                        });
                    } else {
                        list.innerHTML = '<div class="p-4 text-center text-slate-400 italic text-xs">No se encontraron párrafos procesables.</div>';
                    }
                    
                    updateStepStatus(1, true);
                    checkFormValidity();
                } else {
                    alert('Error: ' + data.error);
                    resetUploadZone();
                }
            } catch (e) {
                alert('Fallo de conexión al servidor');
                resetUploadZone();
            }
        }

        function resetUploadZone() {
            const uploadZone = document.getElementById('upload-zone');
            uploadZone.classList.remove('hidden'); // Asegurar que sea visible
            uploadZone.innerHTML = `
                <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform pointer-events-none">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-1 pointer-events-none tracking-tight">Cargar Archivo de Word</h3>
                <p class="text-sm text-slate-500 pointer-events-none">Haz clic aquí o arrastra tu archivo .docx</p>
            `;
        }

        // --- Picker de Coordenadas Logic ---
        const pickerColors = [
            '#ef4444', // Red
            '#3b82f6', // Blue
            '#10b981', // Green
            '#f59e0b', // Amber
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#06b6d4', // Cyan
            '#f97316'  // Orange
        ];

        function getSignerColor(idx) {
            return pickerColors[idx % pickerColors.length];
        }

        function renderStaticMarkers() {
            document.querySelectorAll('.static-marker').forEach(m => m.remove());
            
            signers.forEach((s, idx) => {
                if (s.id === currentPickingSignerId) return;
                
                const type = document.getElementById(`placement-${s.id}`).value;
                if (type !== 'coords') return;
                
                const val = document.getElementById(`placement-value-${s.id}`).value;
                if (!val || !val.includes(',')) return;
                
                const [pIdx, x, y] = val.split(',').map(Number);
                const wrapper = document.getElementById(`page-wrapper-${pIdx}`);
                
                if (wrapper) {
                    const marker = document.createElement('div');
                    marker.className = 'static-marker';
                    marker.style.backgroundColor = getSignerColor(idx);
                    marker.style.left = `${x}px`;
                    marker.style.top = `${y}px`;
                    marker.innerHTML = idx + 1;
                    wrapper.appendChild(marker);
                }
            });
        }

        function openCoordPicker(id) {
            if (!uploadedDoc || !uploadedDoc.previews) return alert('Sube un documento primero');
            currentPickingSignerId = id;
            selectedPlacementData = null;
            isDragMode = false;
            
            const body = document.getElementById('document-simulation');
            body.innerHTML = '';
            body.className = "page-view-container";
            body.style.display = "flex";
            
            uploadedDoc.previews.forEach((url, idx) => {
                const wrapper = document.createElement('div');
                wrapper.className = "page-image-wrapper";
                wrapper.id = `page-wrapper-${idx}`;
                
                const label = document.createElement('div');
                label.className = "page-label";
                label.innerText = `Página ${idx + 1}`;
                wrapper.appendChild(label);
                
                const img = document.createElement('img');
                img.src = url;
                img.onclick = (e) => {
                    if (!isDragMode) handleCoordinateClick(e, idx);
                };
                wrapper.appendChild(img);
                body.appendChild(wrapper);
            });
            
            renderStaticMarkers();
            
            const signerIdx = signers.findIndex(s => s.id === id);
            const currentColor = getSignerColor(signerIdx);
            
            const dragBtn = document.getElementById('toggle-drag-mode');
            dragBtn.className = "px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-2 border border-slate-200";
            dragBtn.style.color = currentColor;
            dragBtn.innerHTML = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path></svg> Modo Arrastre (X)`;
            
            const currentVal = document.getElementById(`placement-value-${id}`).value;
            if (currentVal && currentVal.includes(',')) {
                const [pIdx, x, y] = currentVal.split(',').map(Number);
                const wrapper = document.getElementById(`page-wrapper-${pIdx}`);
                if (wrapper) {
                    const marker = document.createElement('div');
                    marker.id = 'temp-coord-marker';
                    marker.className = 'coord-marker';
                    marker.style.borderColor = currentColor;
                    marker.style.color = currentColor;
                    marker.style.backgroundColor = `${currentColor}22`;
                    marker.style.left = `${x}px`;
                    marker.style.top = `${y}px`;
                    marker.innerHTML = `Firma ${signerIdx + 1}`;
                    wrapper.appendChild(marker);
                    
                    selectedPlacementData = { p_index: pIdx, x: x, y: y };
                    document.getElementById('confirmCoordsBtn').disabled = false;
                    document.getElementById('confirmCoordsBtn').classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            document.getElementById('coord-info').textContent = `Ubica la Firma #${signerIdx + 1}`;
            document.getElementById('coordModal').classList.remove('hidden');
        }

        function toggleDragMode() {
            isDragMode = !isDragMode;
            const btn = document.getElementById('toggle-drag-mode');
            const body = document.getElementById('document-simulation');
            
            const signerIdx = signers.findIndex(s => s.id === currentPickingSignerId);
            const currentColor = getSignerColor(signerIdx);
            
            if (isDragMode) {
                btn.style.backgroundColor = currentColor;
                btn.classList.add('text-white');
                
                if (!document.getElementById('draggable-signature-x')) {
                    const x = document.createElement('div');
                    x.id = 'draggable-signature-x';
                    x.className = 'draggable-x';
                    x.style.color = currentColor;
                    const scroller = document.getElementById('coordPickerBody');
                    x.innerHTML = `<svg fill="${currentColor}" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>`;
                    x.onmousedown = startDrag;
                    body.appendChild(x);
                    
                    x.style.left = `calc(50% - 30px)`;
                    x.style.top = `${scroller.scrollTop + 100}px`;
                }
            } else {
                btn.style.backgroundColor = '';
                btn.classList.remove('text-white');
                const x = document.getElementById('draggable-signature-x');
                if (x) x.remove();
            }
        }

        function startDrag(e) {
            e.preventDefault();
            dragTarget = e.currentTarget;
            const body = document.getElementById('document-simulation');
            const bodyRect = body.getBoundingClientRect();
            
            const onMouseMove = (moveEvent) => {
                const x = moveEvent.clientX - bodyRect.left;
                const y = moveEvent.clientY - bodyRect.top;
                
                // Límites (60px es el ancho de la X)
                const finalX = Math.max(0, Math.min(x - 30, bodyRect.width - 60));
                const finalY = Math.max(0, Math.min(y - 30, bodyRect.height - 60));
                
                dragTarget.style.left = `${finalX}px`;
                dragTarget.style.top = `${finalY}px`;
            };
            
            const onMouseUp = () => {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
                calculateCoordinatesFromX();
            };
            
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        }

        function calculateCoordinatesFromX() {
            const xMarker = document.getElementById('draggable-signature-x');
            if (!xMarker) return;
            
            const xRect = xMarker.getBoundingClientRect();
            const centerX = xRect.left + xRect.width / 2;
            const centerY = xRect.top + xRect.height / 2;
            
            const wrappers = document.querySelectorAll('.page-image-wrapper');
            let bestW = wrappers[0];
            let minDistance = Infinity;
            
            wrappers.forEach(w => {
                const rect = w.getBoundingClientRect();
                const distY = Math.abs(centerY - (rect.top + rect.height / 2));
                if (distY < minDistance) {
                    minDistance = distY;
                    bestW = w;
                }
            });
            
            const pIndex = parseInt(bestW.id.replace('page-wrapper-', ''));
            const pRect = bestW.getBoundingClientRect();
            
            selectedPlacementData = {
                p_index: pIndex,
                x: Math.round(centerX - pRect.left),
                y: Math.round(centerY - pRect.top)
            };
            
            const signerIdx = signers.findIndex(s => s.id == currentPickingSignerId);
            document.getElementById('coord-info').textContent = `Fijada Firma #${signerIdx + 1} en Pág ${pIndex + 1}`;
            document.getElementById('confirmCoordsBtn').disabled = false;
            document.getElementById('confirmCoordsBtn').classList.remove('opacity-50', 'cursor-not-allowed');
        }

        function handleCoordinateClick(e, pIndex) {
            e.stopPropagation();
            if (isDragMode) return; 

            const wrapper = document.getElementById(`page-wrapper-${pIndex}`);
            const rect = wrapper.getBoundingClientRect();
            
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const signerIdx = signers.findIndex(s => s.id === currentPickingSignerId);
            const currentColor = getSignerColor(signerIdx);

            selectedPlacementData = { p_index: pIndex, x: Math.round(x), y: Math.round(y) };
            
            document.querySelectorAll('#temp-coord-marker, #draggable-signature-x').forEach(m => m.remove());
            
            const marker = document.createElement('div');
            marker.id = 'temp-coord-marker';
            marker.className = 'coord-marker';
            marker.style.borderColor = currentColor;
            marker.style.color = currentColor;
            marker.style.backgroundColor = `${currentColor}22`;
            marker.style.left = `${x}px`;
            marker.style.top = `${y}px`;
            marker.innerHTML = `Firma ${signerIdx + 1}`;
            wrapper.appendChild(marker);
            
            document.getElementById('coord-info').textContent = `Firma ${signerIdx + 1} en Pág ${pIndex + 1}`;
            document.getElementById('confirmCoordsBtn').disabled = false;
            document.getElementById('confirmCoordsBtn').classList.remove('opacity-50', 'cursor-not-allowed');
        }

        function saveCoords() {
            if (!selectedPlacementData || currentPickingSignerId === null) return;
            
            const input = document.getElementById(`placement-value-${currentPickingSignerId}`);
            // Formato: index,x,y
            input.value = `${selectedPlacementData.p_index},${selectedPlacementData.x},${selectedPlacementData.y}`;
            
            closeCoordPicker();
            checkFormValidity();
        }

        function closeCoordPicker() {
            document.getElementById('coordModal').classList.add('hidden');
            currentPickingSignerId = null;
            isDragMode = false;
        }

        // Signers Management
        function addSigner() {
            const id = signerCounter++;
            const signer = { id: id, pad: null };
            signers.push(signer);
            
            const list = document.getElementById('signersList');
            document.getElementById('no-signers').classList.add('hidden');
            
            const card = document.createElement('div');
            card.className = "signer-card p-6 animate-fade-in";
            card.id = `signer-${id}`;
            card.innerHTML = `
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center text-sm font-bold">${signers.length}</div>
                        <div>
                            <h4 class="font-bold text-slate-800">Firmante </h4>
                            <p class="text-xs text-slate-500">Personaliza la firma y certificado</p>
                        </div>
                    </div>
                    <button onclick="removeSigner(${id})" class="text-slate-400 hover:text-red-500 transition-colors p-2">✕</button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre del Firmante</label>
                        <input type="text" id="name-${id}" oninput="checkFormValidity()" placeholder="Ej: Lic. Antonio Solis" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Certificado XMLDSig</label>
                        <select id="cert-${id}" onchange="checkFormValidity()" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-white font-medium">
                            <option value="">(Sin firma digital avanzada)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Trazo de Firma Manuscrita</label>
                        <div class="flex items-center gap-4">
                            <!-- Paleta de Colores -->
                            <div class="flex items-center gap-2 px-2.5 py-1.5 bg-slate-100 rounded-full border border-slate-200 shadow-sm">
                                <button onclick="changeSignerColor(${id}, '#000000')" class="w-3.5 h-3.5 rounded-full bg-black hover:scale-125 transition-transform border border-white" title="Negro"></button>
                                <button onclick="changeSignerColor(${id}, '#000080')" class="w-3.5 h-3.5 rounded-full bg-blue-900 hover:scale-125 transition-transform border border-white" title="Azul Marino"></button>
                            </div>
                            <!-- Botón Borrar (Borrador) -->
                            <button onclick="clearPad(${id})" class="flex items-center gap-1.5 text-[10px] font-bold text-red-500 uppercase hover:text-red-600 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path></svg>
                                Limpiar Panel
                            </button>
                        </div>
                    </div>
                    <canvas id="canvas-${id}" class="sig-canvas w-full h-[220px] shadow-sm touch-none"></canvas>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-2xl">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Ubicación Visual de la Firma</label>
                        <select id="placement-${id}" onchange="updatePlacementUI(${id})" class="w-full text-sm border-none bg-transparent font-bold text-slate-700 outline-none focus:ring-0">
                            <option value="end">Página Final</option>
                            <option value="paragraph">Párrafo Nº</option>
                            <option value="coords">Manual (Coordenadas)</option>
                            <option value="keyword">Palabra Clave</option>
                        </select>
                    </div>
                    <div id="placement-val-container-${id}" class="col-span-2 hidden animate-fade-in relative">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2" id="placement-label-${id}">Valor de Referencia</label>
                        <div id="keyword-select-wrapper-${id}" style="display: none;">
                            <select id="placement-keyword-${id}" class="w-full text-sm border-none bg-white p-2 rounded-lg font-bold text-slate-700 outline-none focus:ring-1 focus:ring-blue-300 transition-all shadow-inner cursor-pointer appearance-none">
                                <option value="EL TRABAJADOR">EL TRABAJADOR</option>
                                <option value="EL EMPLEADOR">EL EMPLEADOR</option>
                            </select>
                        </div>
                        <input type="text" id="placement-value-${id}" placeholder="..." class="w-full text-sm border-none bg-white p-2 rounded-lg font-bold text-slate-700 outline-none focus:ring-1 focus:ring-blue-300 transition-all shadow-inner"/>
                        
                        <button id="coords-btn-${id}" onclick="openCoordPicker(${id})" class="hidden absolute right-1 bottom-1 bg-blue-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-all flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                            Abrir Selector
                        </button>
                    </div>
                </div>
            `;
            
            list.appendChild(card);
            
            // Init Signature Pad con máxima fiabilidad
            setTimeout(() => {
                const canvas = document.getElementById(`canvas-${id}`);
                if (!canvas) return;

                const pad = new SignaturePad(canvas, { 
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1.0,
                    maxWidth: 4.0,
                    throttle: 10, // Menor valor = más precisión en dispositivos táctiles
                    velocityFilterWeight: 0.7
                });
                
                // Función de auto-ajuste inteligente
                const syncSize = () => {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = canvas.getBoundingClientRect();
                    if (rect.width === 0) return;
                    
                    // Solo redimensionar si las dimensiones han cambiado significativamente
                    if (Math.abs(canvas.width - rect.width * ratio) > 1) {
                        const data = pad.toData();
                        canvas.width = rect.width * ratio;
                        canvas.height = rect.height * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);
                        pad.clear();
                        pad.fromData(data);
                    }
                };

                syncSize();
                
                // Asegurar sincronización en cada inicio de trazo para evitar "offsets" en táctil
                canvas.addEventListener('pointerdown', syncSize);
                canvas.addEventListener('touchstart', (e) => e.preventDefault(), { passive: false });
                
                pad.onEnd = () => checkFormValidity();
                signer.pad = pad;
                
                window.addEventListener("resize", syncSize);
                window.addEventListener("orientationchange", () => setTimeout(syncSize, 200));
            }, 600); // 600ms para asegurar total reposo de la UI
            
            // Populate Certs
            populateSignerCerts(id);
            updateGlobalStats();
        }

        function removeSigner(id) {
            signers = signers.filter(s => s.id !== id);
            const card = document.getElementById(`signer-${id}`);
            card.remove();
            
            if (signers.length === 0) {
                document.getElementById('no-signers').classList.remove('hidden');
            }
            updateGlobalStats();
            checkFormValidity();
        }

        async function populateSignerCerts(id) {
            const select = document.getElementById(`cert-${id}`);
            const res = await fetch('/api/certificates');
            const data = await res.json();
            const certs = data.certificates || [];
            
            certs.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `VÁLIDO: ${c.name} (${c.organization})`;
                select.appendChild(opt);
            });
        }

        function clearPad(id) {
            const signer = signers.find(s => s.id === id);
            if (signer && signer.pad) {
                signer.pad.clear();
                checkFormValidity();
            }
        }

        function changeSignerColor(id, color) {
            const signer = signers.find(s => s.id === id);
            if (signer && signer.pad) {
                signer.pad.penColor = color;
            }
        }

        function updatePlacementUI(id) {
            const type = document.getElementById(`placement-${id}`).value;
            const container = document.getElementById(`placement-val-container-${id}`);
            const label = document.getElementById(`placement-label-${id}`);
            const input = document.getElementById(`placement-value-${id}`);
            const keywordWrapper = document.getElementById(`keyword-select-wrapper-${id}`);
            const coordsBtn = document.getElementById(`coords-btn-${id}`);
            
            if (type === 'end') {
                container.style.display = 'none';
            } else {
                container.style.display = 'block';
                input.readOnly = (type === 'coords');
                
                if (type === 'keyword') {
                    label.textContent = 'Palabra Clave (Placeholder)';
                    input.style.display = 'none';
                    keywordWrapper.style.display = 'block';
                    coordsBtn.classList.add('hidden');
                } else if (type === 'coords') {
                    label.textContent = 'Coordenadas (P,X,Y)';
                    input.style.display = 'block';
                    keywordWrapper.style.display = 'none';
                    input.placeholder = 'Click en "Abrir Selector" ->';
                    coordsBtn.classList.remove('hidden');
                } else {
                    label.textContent = 'Número del Párrafo';
                    input.style.display = 'block';
                    keywordWrapper.style.display = 'none';
                    input.placeholder = 'Ej: 5';
                    coordsBtn.classList.add('hidden');
                }
            }
        }

        // Processing Logic
        function updateGlobalStats() {
            document.getElementById('signers-count').textContent = signers.length;
            updateStepStatus(2, signers.length > 0);
        }

        function updateStepStatus(step, active) {
            const el = document.getElementById(`status-step-${step}`);
            if (active) {
                el.classList.add('step-active', 'bg-blue-50');
                el.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>';
            } else {
                el.classList.remove('step-active', 'bg-blue-50');
                el.innerHTML = step;
            }
        }

        function checkFormValidity() {
            let valid = true;
            if (!uploadedDoc) valid = false;
            if (signers.length === 0) valid = false;
            
            let anyCert = false;
            for (const s of signers) {
                const nameEl = document.getElementById(`name-${s.id}`);
                const certEl = document.getElementById(`cert-${s.id}`);
                
                if (!nameEl || !certEl) {
                    valid = false;
                    continue;
                }
                
                const name = nameEl.value.trim();
                const cert = certEl.value;
                
                if (!name) valid = false;
                if (s.pad && s.pad.isEmpty()) valid = false;
                if (cert) anyCert = true;
            }
            
            // Actualizar estados visuales de los pasos
            updateStepStatus(1, !!uploadedDoc);
            updateStepStatus(2, signers.length > 0 && valid); // Paso 2 es datos de firmantes
            updateStepStatus(3, anyCert); // Paso 3 es certificación digital (opcional para firma, pero informa si está activa)
            
            const btn = document.getElementById('signBtn');
            btn.disabled = !valid;
            if (valid) {
                btn.classList.add('btn-primary', 'text-white');
                btn.classList.remove('bg-slate-200', 'text-slate-500');
                btn.style.cursor = 'pointer';
            } else {
                btn.classList.remove('btn-primary', 'text-white');
                btn.classList.add('bg-slate-200', 'text-slate-500');
                btn.style.cursor = 'not-allowed';
            }
        }

        async function signDocument() {
            const btn = document.getElementById('signBtn');
            btn.disabled = true;
            btn.innerHTML = `<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Procesando firmas...`;
            
            const signatures = [];
            for (const s of signers) {
                const placementType = document.getElementById(`placement-${s.id}`).value;
                let placementValue = document.getElementById(`placement-value-${s.id}`).value;
                
                if (placementType === 'keyword') {
                    placementValue = document.getElementById(`placement-keyword-${s.id}`).value;
                }

                signatures.push({
                    signer_name: document.getElementById(`name-${s.id}`).value.trim(),
                    signature_image: s.pad.toDataURL(),
                    certificate_id: document.getElementById(`cert-${s.id}`).value || null,
                    placement_type: placementType,
                    placement_value: placementValue
                });
            }
            
            try {
                const res = await fetch('/api/sign/batch', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        document_path: uploadedDoc.server_path,
                        original_filename: uploadedDoc.filename,
                        signatures: signatures
                    })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    const resultEl = document.getElementById('result');
                    resultEl.innerHTML = `
                        <div class="p-6 bg-green-50 border-2 border-green-200 rounded-2xl text-center space-y-4 shadow-sm animate-scale-in">
                            <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto shadow-lg shadow-green-100">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-green-800 tracking-tight">¡Éxito Total!</h4>
                                <p class="text-sm text-green-600 font-medium">Documento Certificado y Firmado</p>
                            </div>
                            <a href="${data.download_url}" class="block w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-all shadow-md active:scale-95">
                                Descargar Archivo Firmado
                            </a>
                            <button onclick="resetApp()" class="block w-full py-3 bg-white border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Firmar otro documento
                            </button>
                            <p class="text-[10px] text-green-500 font-bold uppercase tracking-widest italic tracking-tighter">Compatible con Microsoft Office Word</p>
                        </div>
                    `;
                    resultEl.classList.remove('hidden');
                    btn.classList.add('hidden');
                } else {
                    alert('Error en el procesamiento: ' + data.error);
                }
            } catch (e) {
                alert('Fallo catastrófico en la red');
            } finally {
                if (!document.getElementById('result').classList.contains('hidden')) return;
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Inicia la Firma`;
            }
        }

        // Certificates Management Pro
        async function loadCertificates() {
            const list = document.getElementById('certsList');
            list.innerHTML = `<div class="col-span-full py-12 text-center text-slate-400">Cargando certificados...</div>`;
            
            try {
                const res = await fetch('/api/certificates');
                const data = await res.json();
                const certs = data.certificates || [];
                
                if (certs.length === 0) {
                    list.innerHTML = `
                        <div class="col-span-full border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center">
                            <p class="text-slate-500 font-medium mb-4">No hay certificados digitales generados.</p>
                            <button onclick="toggleNewCertModal()" class="text-blue-600 font-bold hover:underline">Generar mi primer certificado ahora →</button>
                        </div>
                    `;
                    return;
                }
                
                list.innerHTML = certs.map(c => `
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group border-l-4 border-l-blue-500">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic tracking-tighter">RSA-2048 / SHA256</div>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 leading-tight mb-1 truncate">${c.name}</h4>
                        <p class="text-xs text-slate-500 font-medium mb-4">${c.organization} • ${c.country}</p>
                        
                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <span>Expira el</span>
                                <span class="text-blue-600">${new Date(c.valid_until).toLocaleDateString()}</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden">
                                <div class="bg-blue-500 h-full w-[100%]"></div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (e) {
                list.innerHTML = `<p class="col-span-full text-red-500 p-4 text-center">Error cargando certificados</p>`;
            }
        }

        async function createCert() {
            const btn = document.getElementById('createCertBtn');
            const name = document.getElementById('certName').value.trim();
            const org = document.getElementById('certOrg').value.trim();
            const country = document.getElementById('certCountry').value.trim().toUpperCase();

            if (!name || !org || country.length !== 2) return alert('Completa los datos correctamente');

            btn.disabled = true;
            btn.textContent = 'Generando RSA Keys...';
            
            const formData = new FormData();
            formData.append('name', name);
            formData.append('organization', org);
            formData.append('country', country);

            try {
                const res = await fetch('/api/certificates/create', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    toggleNewCertModal();
                    loadCertificates();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (e) {
                alert('Fallo de red');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Generar Llave y Certificado';
            }
        }

        // Init App
        setTimeout(() => {
            loadCertificates();
            addSigner();
        }, 500);
    </script>
</body>
</html>
"""

@app.get("/", response_class=HTMLResponse)
async def index():
    """Página principal"""
    return HTMLResponse(content=HTML_FULL)

@app.post("/api/upload")
async def upload(file: UploadFile = File(...)):
    """Subir documento y analizar estructura"""
    try:
        if not file.filename.endswith('.docx'):
            return JSONResponse({"error": "Solo archivos .docx"}, status_code=400)
        
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{timestamp}_{file.filename}"
        path = UPLOAD_DIR / filename
        
        with open(path, "wb") as f:
            shutil.copyfileobj(file.file, f)
        
        # Analizar Estructura (Legacy) y Generar Previsualización Profesional (Spire)
        previews = []
        content = []
        p_counter = 0
        try:
            # 1. Spire.Doc para Previsualización Real
            spire_doc = SpireDocument()
            spire_doc.LoadFromFile(str(path))
            
            # Limpiar previsualizaciones antiguas del mismo prefijo si es posible (opcional)
            file_uuid = uuid.uuid4().hex[:8]
            
            # Guardar cada página como imagen
            from PIL import Image
            print(f"[*] Generando previsualizaciones limpias para {path} (Paginas: {spire_doc.PageCount})")
            for i in range(spire_doc.PageCount):
                image_name = f"preview_{file_uuid}_page_{i}.png"
                image_path = PREVIEWS_DIR / image_name
                
                # Obtener imagen de Spire
                stream = spire_doc.SaveImageToStreams(i, ImageType.Bitmap)
                img = Image.open(io.BytesIO(stream.ToArray()))
                
                # --- LIMPIEZA DE MARCA DE AGUA EN IMAGEN ---
                # La marca de agua de Spire suele estar en los primeros ~30px
                # Creamos una copia y pintamos de blanco la zona superior
                draw_img = img.convert("RGB")
                from PIL import ImageDraw
                draw = ImageDraw.Draw(draw_img)
                # Cubrir el área del warning (aprox 40px de alto es suficiente)
                draw.rectangle([0, 0, draw_img.width, 35], fill="white")
                
                # Guardar imagen limpia
                draw_img.save(str(image_path), "PNG")
                previews.append(f"/static/previews/{image_name}")
            
            spire_doc.Close()
            print(f"[+] {len(previews)} previsualizaciones generadas.")

            # 2. python-docx para estructura de párrafos (para compatibilidad keyword)
            doc = Document(str(path))
            def process_p(p, idx):
                return {
                    "type": "p",
                    "index": idx,
                    "text": p.text,
                    "alignment": str(p.alignment) if p.alignment else None,
                    "bold": any(run.bold for run in p.runs[:5])
                }

            for child in doc.element.body.iterchildren():
                if isinstance(child, CT_P):
                    p = Paragraph(child, doc)
                    if p.text.strip() or p_counter < 10:
                        content.append(process_p(p, p_counter))
                        p_counter += 1
                elif isinstance(child, CT_Tbl):
                    table = Table(child, doc)
                    t_data = {"type": "table", "rows": []}
                    for row in table.rows:
                        r_data = []
                        for cell in row.cells:
                            cell_items = []
                            for cp in cell.paragraphs:
                                cell_items.append(process_p(cp, p_counter))
                                p_counter += 1
                            r_data.append(cell_items)
                        t_data["rows"].append(r_data)
                    content.append(t_data)
                if p_counter > 500: break
        except Exception as e:
            import traceback
            print(f"[!] Error analizando estructura: {traceback.format_exc()}")
            return JSONResponse({"error": f"Error al procesar el documento: {str(e)}"}, status_code=500)
        
        return {
            "success": True,
            "filename": file.filename,
            "server_path": str(path),
            "paragraphs": content,
            "previews": previews,
            "total_paragraphs": p_counter
        }
    except Exception as e:
        return JSONResponse({"error": str(e)}, status_code=500)

@app.post("/api/sign/batch")
async def sign_batch(payload: dict = Body(...)):
    """Firmar documento con múltiples firmantes"""
    try:
        doc_path = Path(payload.get("document_path"))
        if not doc_path.exists():
            return JSONResponse({"error": "Documento no encontrado"}, status_code=404)
        
        signatures = payload.get("signatures", [])
        if not signatures:
            return JSONResponse({"error": "No hay firmantes"}, status_code=400)
        
        # Preparar firmas visuales
        visual_configs = []
        cert_configs = []
        
        for sig in signatures:
            # Guardar imagen de firma manuscrita temporal
            img_data = sig.get("signature_image", "")
            if img_data.startswith("data:image"):
                img_data = img_data.split(",")[1]
            
            img_bytes = base64.b64decode(img_data)
            
            # --- MEJORA: Recortar espacios en blanco (como en escritorio) ---
            from PIL import Image, ImageChops
            sig_img = Image.open(io.BytesIO(img_bytes)).convert("RGBA")
            
            # Obtener caja delimitadora del contenido (no transparente)
            bbox = sig_img.getbbox()
            if bbox:
                sig_img = sig_img.crop(bbox)
            
            # Guardar imagen recortada
            temp_sig_path = TEMP_DIR / f"raw_sig_{uuid.uuid4()}.png"
            sig_img.save(temp_sig_path)
            # -------------------------------------------------------------
            
            # Configurar firma visual
            placement_type = sig.get("placement_type", "end")
            placement_value = sig.get("placement_value", "")
            
            # Cargar certificado si existe
            cert_info = None
            cert_id = sig.get("certificate_id")
            if cert_id:
                cert_path = CERT_DIR / f"{cert_id}_cert.pem"
                key_path = CERT_DIR / f"{cert_id}_key.pem"
                
                if cert_path.exists() and key_path.exists():
                    cert_obj, key_obj = cert_manager.load_certificate(str(cert_path), str(key_path))
                    cert_info = cert_manager.get_certificate_info(cert_obj)
                    
                    cert_configs.append({
                        "certificate": cert_obj,
                        "private_key": key_obj,
                        "cert_info": cert_info,
                        "signer_name": sig["signer_name"]
                    })
            
            # IMPORTANTE: Usar SignatureImage para crear imagen compuesta (como desktop)
            sig_image_generator = SignatureImage(width=300, height=150)
            composite_image_data = sig_image_generator.create_signature_image(
                signer_name=sig["signer_name"],
                signature_date=datetime.now(),
                signature_image_path=str(temp_sig_path),
                certificate_info=cert_info
            )
            
            # Guardar imagen compuesta
            final_sig_path = TEMP_DIR / f"composite_sig_{uuid.uuid4()}.png"
            with open(final_sig_path, "wb") as f:
                f.write(composite_image_data.getvalue())
            
            # Limpiar imagen temporal raw
            temp_sig_path.unlink(missing_ok=True)
            
            visual_config = {
                "signer_name": sig["signer_name"],
                "image_path": str(final_sig_path),
                "paragraph_index": None,
                "keyword": None,
                "offset_x": None, # Added for coords
                "offset_y": None, # Added for coords
                "cert_info": cert_info
            }
            
            if placement_type == "paragraph" and placement_value:
                visual_config["paragraph_index"] = int(placement_value)
            elif placement_type == "coords" and placement_value:
                # Formato: p_index,x,y
                try:
                    parts = placement_value.split(",")
                    visual_config["paragraph_index"] = int(parts[0])
                    visual_config["offset_x"] = int(parts[1])
                    visual_config["offset_y"] = int(parts[2])
                    visual_config["placement_type"] = "coords"
                except:
                    visual_config["paragraph_index"] = -1
            elif placement_type == "keyword" and placement_value:
                visual_config["keyword"] = placement_value
            else:
                visual_config["paragraph_index"] = -1  # Final
            
            visual_configs.append(visual_config)
        
        # Aplicar firmas visuales
        # Convertir image_path a image_data (BytesIO) como espera word_signer
        for config in visual_configs:
            with open(config["image_path"], "rb") as f:
                img_bytes = f.read()
            config["image_data"] = io.BytesIO(img_bytes)
        
        # 1. Firmas Visuales
        current_bytes = word_signer.batch_visual_sign(str(doc_path), visual_configs)
        
        if not current_bytes:
            return JSONResponse({"error": "Error en firma visual"}, status_code=500)
        
        # 2. Protección de Solo Lectura (como en desktop)
        current_bytes = word_signer.set_read_only_mode(current_bytes)
        
        # 3. Firmas Digitales (Loop como en desktop)
        if cert_configs:
            for cert_data in cert_configs:
                try:
                    current_bytes = word_signer.add_digital_signature_metadata(
                        current_bytes,
                        cert_data['certificate'],
                        cert_data['private_key'],
                        cert_data['signer_name']
                    )
                except Exception as ex:
                    print(f"[!] Error añadiendo firma digital para {cert_data['signer_name']}: {ex}")
        
        # Finalización y guardado
        output_name = f"FIRMADO_{datetime.now().strftime('%Y%m%d_%H%M%S')}_{payload.get('original_filename', 'documento.docx')}"
        output_path = UPLOAD_DIR / output_name
        
        word_signer.protect_document(current_bytes, str(output_path))
        
        # Limpiar temporales
        for config in visual_configs:
            if "image_path" in config:
                Path(config["image_path"]).unlink(missing_ok=True)
        
        return {
            "success": True,
            "download_url": f"/download/{output_name}",
            "filename": output_name
        }
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        return JSONResponse({"error": str(e)}, status_code=500)

@app.get("/api/certificates")
async def list_certificates():
    """Listar certificados disponibles"""
    try:
        certs = []
        for cert_file in CERT_DIR.glob("*_cert.pem"):
            try:
                name = cert_file.stem.replace("_cert", "")
                key_file = CERT_DIR / f"{name}_key.pem"
                
                if not key_file.exists():
                    continue
                
                cert, _ = cert_manager.load_certificate(str(cert_file), str(key_file))
                info = cert_manager.get_certificate_info(cert)
                
                certs.append({
                    "id": name,
                    "name": info.get("common_name", name),
                    "organization": info.get("organization", "N/A"),
                    "country": info.get("country", "N/A"),
                    "valid_until": info.get("not_valid_after", "").isoformat() if hasattr(info.get("not_valid_after", ""), "isoformat") else str(info.get("not_valid_after", ""))
                })
            except:
                continue
        
        return {"success": True, "certificates": certs}
    except Exception as e:
        return JSONResponse({"error": str(e)}, status_code=500)

@app.post("/api/certificates/create")
async def create_certificate(
    name: str = Form(...),
    organization: str = Form("IML"),
    country: str = Form("CU")
):
    """Crear nuevo certificado"""
    try:
        if not name or len(name) < 2:
            return JSONResponse({"error": "Nombre inválido"}, status_code=400)
        
        if len(country) != 2:
            return JSONResponse({"error": "País debe ser 2 letras"}, status_code=400)
        
        safe_name = name.replace(" ", "_").upper()
        cert_path = CERT_DIR / f"{safe_name}_cert.pem"
        key_path = CERT_DIR / f"{safe_name}_key.pem"
        
        if cert_path.exists():
            return JSONResponse({"error": "Ya existe un certificado con ese nombre"}, status_code=409)
        
        cert, key = cert_manager.generate_certificate(name, organization, country)
        cert_manager.save_certificate(cert, key, str(cert_path), str(key_path))
        
        return {
            "success": True,
            "message": f"Certificado '{name}' creado exitosamente",
            "certificate_id": safe_name
        }
    except Exception as e:
        return JSONResponse({"error": str(e)}, status_code=500)

@app.get("/download/{filename}")
async def download(filename: str):
    """Descargar archivo firmado"""
    path = UPLOAD_DIR / filename
    if not path.exists():
        raise HTTPException(404, "Archivo no encontrado")
    return FileResponse(path, filename=filename)

if __name__ == "__main__":
    print("\n" + "="*60)
    print("  IML - Sistema de Firma Digital COMPLETO")
    print("  URL: http://127.0.0.1:8000")
    print("  Funcionalidades:")
    print("    ✓ Múltiples firmantes")
    print("    ✓ Posicionamiento avanzado")
    print("    ✓ Certificados digitales")
    print("    ✓ Firma visual + XMLDSig")
    print("="*60 + "\n")
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")
