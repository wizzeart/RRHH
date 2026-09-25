import sys
import traceback

try:
    print("1. Importando módulos...")
    from web_app_full import app
    print("   ✓ Import OK")
    
    print("2. Verificando templates...")
    import os
    template_path = os.path.join("templates", "app_full.html")
    if os.path.exists(template_path):
        print(f"   ✓ Template existe: {template_path}")
    else:
        print(f"   ✗ Template NO existe: {template_path}")
    
    print("3. Iniciando servidor...")
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="debug")
    
except Exception as e:
    print(f"\n❌ ERROR:")
    print(traceback.format_exc())
    input("Presiona Enter para salir...")
