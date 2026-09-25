"""
Script para limpiar firmas digitales inválidas de documentos Word
"""
import zipfile
import io
import sys
from lxml import etree

def clean_document(input_path, output_path=None):
    """Limpia firmas XMLDSig inválidas de un documento Word"""
    
    if output_path is None:
        output_path = input_path.replace('.docx', '_limpio.docx')
    
    in_buf = io.BytesIO()
    with open(input_path, 'rb') as f:
        in_buf.write(f.read())
    in_buf.seek(0)
    
    out_buf = io.BytesIO()
    
    with zipfile.ZipFile(in_buf, 'r') as zin:
        with zipfile.ZipFile(out_buf, 'w', zipfile.ZIP_DEFLATED) as zout:
            
            files_to_skip = {'_rels/.rels', '[Content_Types].xml'}
            
            for item in zin.infolist():
                # Saltar archivos que modificaremos
                if item.filename in files_to_skip:
                    continue
                # Saltar todo lo relacionado con firmas XMLDSig
                if item.filename.startswith('_xmlsignatures/'):
                    print(f"  [x] Eliminando: {item.filename}")
                    continue
                # Copiar el resto
                zout.writestr(item, zin.read(item.filename))
            
            # Limpiar _rels/.rels
            if '_rels/.rels' in zin.namelist():
                rels_content = zin.read('_rels/.rels')
                rels_root = etree.fromstring(rels_content)
                ns_rel = "http://schemas.openxmlformats.org/package/2006/relationships"
                
                new_rels = etree.Element("Relationships", nsmap={None: ns_rel})
                
                for rel in rels_root:
                    rel_type = rel.get('Type', '')
                    if 'digital-signature' not in rel_type:
                        new_rels.append(rel)
                    else:
                        print(f"  [x] Eliminando relación de firma digital")
                
                rels_xml = etree.tostring(new_rels,
                                         encoding='UTF-8',
                                         xml_declaration=True)
                zout.writestr('_rels/.rels', rels_xml)
            
            # Limpiar [Content_Types].xml
            if '[Content_Types].xml' in zin.namelist():
                ct_content = zin.read('[Content_Types].xml')
                ct_root = etree.fromstring(ct_content)
                ns_ct = "http://schemas.openxmlformats.org/package/2006/content-types"
                
                new_ct = etree.Element(f"{{{ns_ct}}}Types", nsmap={None: ns_ct})
                
                for child in ct_root:
                    part_name = child.get('PartName', '')
                    content_type = child.get('ContentType', '')
                    
                    if '_xmlsignatures' in part_name or 'digital-signature' in content_type:
                        print(f"  [x] Eliminando tipo de firma: {part_name}")
                        continue
                    
                    new_ct.append(child)
                
                ct_xml = etree.tostring(new_ct,
                                       encoding='UTF-8',
                                       xml_declaration=True)
                zout.writestr('[Content_Types].xml', ct_xml)
    
    # Guardar archivo limpio
    with open(output_path, 'wb') as f:
        f.write(out_buf.getvalue())
    
    print(f"\n[✓] Documento limpio guardado: {output_path}")
    return output_path

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python clean_invalid_signatures.py <archivo.docx>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    print(f"[i] Limpiando firmas inválidas de: {input_file}")
    clean_document(input_file)
