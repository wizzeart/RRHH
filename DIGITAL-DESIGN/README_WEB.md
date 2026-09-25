# IML Digital Signature System - Web Version

## Versión Profesional 2.0

Sistema web profesional para firma digital de documentos Word con certificados X.509.

### Características

- ✅ Interfaz web moderna y responsive
- ✅ Firma visual con canvas interactivo
- ✅ Firma digital con certificados X.509
- ✅ Gestión completa de certificados
- ✅ Posicionamiento flexible de firmas
- ✅ API REST documentada
- ✅ Manejo robusto de errores
- ✅ Logging profesional

### Inicio Rápido

1. **Instalar dependencias** (si no están instaladas):
   ```bash
   pip install fastapi uvicorn python-multipart jinja2 python-docx lxml cryptography pillow
   ```

2. **Iniciar servidor**:
   - Doble clic en `start_server.bat`
   - O ejecutar: `python server.py`

3. **Acceder a la aplicación**:
   - Abrir navegador en: http://127.0.0.1:8000

### Estructura del Proyecto

```
DIGITAL_S/
├── server.py              # Servidor FastAPI principal
├── word_signer.py         # Lógica de firma de documentos
├── certificate_manager.py # Gestión de certificados
├── templates/
│   └── index_pro.html     # Interfaz web
├── uploads/               # Documentos subidos/firmados
├── certs/                 # Certificados digitales
└── temp/                  # Archivos temporales
```

### API Endpoints

#### Documentos
- `POST /api/documents/upload` - Subir documento
- `POST /api/sign/document` - Firmar documento
- `GET /api/download/{filename}` - Descargar firmado

#### Certificados
- `GET /api/certificates` - Listar certificados
- `POST /api/certificates/create` - Crear certificado

#### Sistema
- `GET /health` - Health check
- `GET /` - Interfaz web

### Uso

1. **Cargar documento Word** (.docx)
2. **Ingresar datos del firmante**
3. **Dibujar firma** en el canvas
4. **Seleccionar certificado** (opcional)
5. **Elegir ubicación** de la firma
6. **Firmar y descargar**

### Gestión de Certificados

Puedes crear certificados autofirmados directamente desde la interfaz web o usar certificados existentes en formato PEM.

### Soporte

Para problemas o preguntas, revisar los logs del servidor en la terminal.
