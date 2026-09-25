"""
Script de diagnóstico para detectar errores en el servidor
"""
import sys
import traceback
import logging

logging.basicConfig(level=logging.DEBUG)

print("=" * 60)
print("DIAGNÓSTICO DEL SERVIDOR")
print("=" * 60)

try:
    print("\n[1/5] Importando servidor...")
    from server import app
    print("✓ Servidor importado correctamente")
    
    print("\n[2/5] Verificando directorios...")
    from pathlib import Path
    dirs = ['uploads', 'certs', 'temp', 'static', 'templates']
    for d in dirs:
        p = Path(d)
        exists = p.exists()
        print(f"  {d}: {'✓' if exists else '✗'}")
    
    print("\n[3/5] Verificando template...")
    template_path = Path('templates/index_pro.html')
    if template_path.exists():
        print(f"✓ Template existe ({template_path.stat().st_size} bytes)")
    else:
        print("✗ Template NO existe")
    
    print("\n[4/5] Probando endpoint raíz...")
    from fastapi.testclient import TestClient
    client = TestClient(app)
    
    try:
        response = client.get("/")
        print(f"  Status: {response.status_code}")
        if response.status_code == 200:
            print("✓ Endpoint funciona correctamente")
        else:
            print(f"✗ Error {response.status_code}")
            print(f"  Respuesta: {response.text[:200]}")
    except Exception as e:
        print(f"✗ Error al probar endpoint: {e}")
        traceback.print_exc()
    
    print("\n[5/5] Iniciando servidor...")
    print("\nServidor listo en: http://127.0.0.1:8000")
    print("Presiona Ctrl+C para detener\n")
    
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="info")
    
except ImportError as e:
    print(f"\n✗ ERROR DE IMPORTACIÓN:")
    print(f"  {e}")
    print("\nPosible solución:")
    print("  pip install fastapi uvicorn python-multipart jinja2")
    traceback.print_exc()
    
except Exception as e:
    print(f"\n✗ ERROR:")
    print(f"  {e}")
    traceback.print_exc()

finally:
    input("\nPresiona Enter para salir...")
