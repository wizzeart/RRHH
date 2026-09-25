import zipfile
import os
import sys
from lxml import etree

def verify_zip_structure(file_path):
    print(f"[*] Analizando {file_path}")
    if not os.path.exists(file_path):
        print("  [!] Archivo no existe")
        return

    with zipfile.ZipFile(file_path, 'r') as z:
        namelist = z.namelist()
        print(f"  [i] Archivos: {len(namelist)}")
        
        # 1. Check Root Relationships
        print("\n--- 1. _rels/.rels ---")
        if '_rels/.rels' in namelist:
            xml = z.read('_rels/.rels')
            root = etree.fromstring(xml)
            # Find origin relation
            found = False
            for rel in root:
                if 'digital-signature/origin' in rel.get('Type', ''):
                    print(f"  [OK] Relación Origin encontrada: {rel.get('Target')}")
                    found = True
            if not found:
                print("  [FAIL] No se encontró relación digital-signature/origin")
        else:
            print("  [FAIL] No existe _rels/.rels")

        # 2. Check Content Types
        print("\n--- 2. [Content_Types].xml ---")
        if '[Content_Types].xml' in namelist:
            xml = z.read('[Content_Types].xml')
            root = etree.fromstring(xml)
            # Find signature overrides
            sigs = 0
            origins = 0
            for override in root.findall('{http://schemas.openxmlformats.org/package/2006/content-types}Override'):
                pn = override.get('PartName', '')
                ct = override.get('ContentType', '')
                if 'digital-signature-xmlsignature+xml' in ct:
                    print(f"  [OK] Signature Type: {pn}")
                    sigs += 1
                if 'digital-signature-origin' in ct:
                    print(f"  [OK] Origin Type: {pn}")
                    origins += 1
            if sigs == 0: print("  [FAIL] No signature types found")
            if origins == 0: print("  [FAIL] No origin type found")
        else:
            print("  [FAIL] No existe [Content_Types].xml")

        # 3. Check Origin Rels
        print("\n--- 3. _xmlsignatures/_rels/origin.sigs.rels ---")
        origin_rels = '_xmlsignatures/_rels/origin.sigs.rels'
        if origin_rels in namelist:
            xml = z.read(origin_rels)
            try:
                root = etree.fromstring(xml)
                count = 0
                for rel in root:
                    if 'digital-signature/signature' in rel.get('Type', ''):
                        print(f"  [OK] Signature Rel: {rel.get('Target')}")
                        count += 1
                if count == 0: print("  [FAIL] No signature relationships inside origin rels")
            except:
                print("  [FAIL] Error parsing origin rels XML")
        else:
            print(f"  [FAIL] No existe {origin_rels}")

        # 4. Check Signature Files
        print("\n--- 4. _xmlsignatures/sig*.xml ---")
        sigs = [f for f in namelist if f.startswith('_xmlsignatures/sig') and f.endswith('.xml')]
        if sigs:
            for sig in sigs:
                print(f"  [OK] Signature File: {sig}")
                # Optional: Check validity of XML content
                try:
                    xml = z.read(sig)
                    root = etree.fromstring(xml)
                    if root.tag.endswith('Signature'):
                        print("    [OK] Root is Signature")
                    else:
                        print(f"    [FAIL] Root is {root.tag}")
                except Exception as e:
                    print(f"    [FAIL] Invalid XML: {e}")
        else:
            print("  [FAIL] No signature files found")

if __name__ == "__main__":
    # Buscar el archivo más reciente que empiece con Contrato y tenga _firmado_
    files = [f for f in os.listdir('.') if '_firmado_' in f and f.endswith('.docx')]
    if files:
        latest = max(files, key=os.path.getmtime)
        verify_zip_structure(latest)
    else:
        print("No signed files found in current directory")
