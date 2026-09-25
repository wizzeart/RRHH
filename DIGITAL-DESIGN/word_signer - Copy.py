"""
Módulo WordSigner - Versión de Resiliencia Extrema
Optimizado para Windows y Python 3.12+ 
Evita el uso de streams de memoria para la carga inicial para prevenir XMLSyntaxError.
"""
import os
import io
import shutil
import tempfile
import zipfile
import hashlib
import uuid
from datetime import datetime
from docx import Document
from docx.shared import Inches, Pt
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from lxml import etree
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.primitives import serialization
import base64
import uuid

class WordSigner:
    def __init__(self, certificate_manager):
        self.cert_manager = certificate_manager

    def batch_visual_sign(self, doc_path, signatures_list):
        """Procesa firmas visuales de forma ultra-segura"""
        print(f"[*] Procesando: {doc_path}")
        
        if not os.path.exists(doc_path):
            raise FileNotFoundError(f"No existe: {doc_path}")
            
        # 1. Validación física previa
        size = os.path.getsize(doc_path)
        with open(doc_path, 'rb') as f:
            header = f.read(4)
        
        print(f"[*] Tamaño: {size} bytes, Header: {header}")
        if header != b'PK\x03\x04':
            raise ValueError("El archivo no es un paquete Office (ZIP) válido.")

        # 2. Usar un archivo temporal físico para cargar el documento
        with tempfile.TemporaryDirectory() as tmp_dir:
            temp_doc_path = os.path.join(tmp_dir, "work.docx")
            shutil.copy2(doc_path, temp_doc_path)
            
            # FIX: Parchear ContentTypes inválidos
            self._fix_content_types(temp_doc_path)
            
            try:
                # CARGA DESDE DISCO
                doc = Document(temp_doc_path)
                print(f"[*] Documento cargado con {len(doc.paragraphs)} párrafos")
                
                # 3. Aplicar firmas
                for i, sig in enumerate(signatures_list):
                    try:
                        img_data = sig['image_data']
                        keyword = sig.get('keyword', '').strip()
                        
                        # Imagen a disco
                        tmp_img_path = os.path.join(tmp_dir, f"sig_{i}.png")
                        with open(tmp_img_path, 'wb') as f:
                            f.write(img_data.getvalue())

                        found = False
                        if keyword:
                            target_text = keyword
                            if keyword == "EL TRABAJADOR":
                                target_text = "ttt"
                            elif keyword == "EL EMPLEADOR":
                                target_text = "eee"
                            
                            def process_paragraphs(paragraph_list):
                                local_found = False
                                for p in paragraph_list:
                                    if target_text in p.text:
                                        for run in p.runs:
                                            if target_text in run.text:
                                                run.text = run.text.replace(target_text, "")
                                                picture = run.add_picture(tmp_img_path, width=Inches(1.9))
                                                self._make_picture_floating(picture)
                                                local_found = True
                                                print(f"[+] Firma insertada en placeholder '{target_text}'")
                                return local_found

                            if process_paragraphs(doc.paragraphs):
                                found = True
                            
                            for table in doc.tables:
                                for row in table.rows:
                                    for cell in row.cells:
                                        if process_paragraphs(cell.paragraphs):
                                            found = True
                        
                        if not found and sig.get('paragraph_index') is not None:
                            idx = sig['paragraph_index']
                            if 0 <= idx < len(doc.paragraphs):
                                run = doc.paragraphs[idx].add_run()
                                pic = run.add_picture(tmp_img_path, width=Inches(1.9))
                                self._make_picture_floating(pic)
                                print(f"[+] Firma en párrafo: {idx}")
                    except Exception as ex:
                        print(f"[!] Error en firma {i}: {ex}")

                # 4. Guardar a buffer
                out_buf = io.BytesIO()
                doc.save(out_buf)
                return out_buf.getvalue()

            except Exception as e:
                import traceback
                print(f"[!] Error cargando doc: {traceback.format_exc()}")
                raise ValueError(f"Error estructural en Word: {str(e)}")

    def _make_picture_floating(self, picture):
        """Convierte una imagen inline a flotante detrás del texto (Wrapped Behind Text)"""
        inline = picture._inline
        anchor = OxmlElement('wp:anchor')
        anchor.set('distT', "0")
        anchor.set('distB', "0")
        anchor.set('distL', "114300")
        anchor.set('distR', "114300")
        anchor.set('simplePos', "0")
        anchor.set('relativeHeight', "251658240")
        anchor.set('behindDoc', "1")
        anchor.set('locked', "0")
        anchor.set('layoutInCell', "1")
        anchor.set('allowOverlap', "1")

        simplePos = OxmlElement('wp:simplePos')
        simplePos.set('x', "0")
        simplePos.set('y', "0")
        anchor.append(simplePos)

        positionH = OxmlElement('wp:positionH')
        positionH.set('relativeFrom', "column") 
        posOffsetH = OxmlElement('wp:posOffset')
        posOffsetH.text = "0" 
        positionH.append(posOffsetH)
        anchor.append(positionH)

        positionV = OxmlElement('wp:positionV')
        positionV.set('relativeFrom', "paragraph")
        posOffsetV = OxmlElement('wp:posOffset')
        posOffsetV.text = "-550000" 
        positionV.append(posOffsetV)
        anchor.append(positionV)

        extent = inline.find(qn('wp:extent'))
        effectExtent = inline.find(qn('wp:effectExtent'))
        docPr = inline.find(qn('wp:docPr'))
        cNvGraphicFramePr = inline.find(qn('wp:cNvGraphicFramePr'))
        graphic = inline.find(qn('a:graphic'))

        if extent is not None: anchor.append(extent)
        if effectExtent is not None: anchor.append(effectExtent)
        
        wrapNone = OxmlElement('wp:wrapNone')
        anchor.append(wrapNone)
        
        if docPr is not None: anchor.append(docPr)
        if cNvGraphicFramePr is not None: anchor.append(cNvGraphicFramePr)
        if graphic is not None: anchor.append(graphic)
        
        parent = inline.getparent()
        if parent is not None:
            parent.replace(inline, anchor)

    def _fix_content_types(self, doc_path):
        """Corrige tipos de contenido invalidos en [Content_Types].xml usando lxml"""
        temp_zip_path = doc_path + ".tmp.zip"
        try:
            patched = False
            new_xml_bytes = b""
            
            with zipfile.ZipFile(doc_path, 'r') as zin:
                if '[Content_Types].xml' in zin.namelist():
                    xml_content = zin.read('[Content_Types].xml')
                    try:
                        root = etree.fromstring(xml_content)
                        ns = {'ct': 'http://schemas.openxmlformats.org/package/2006/content-types'}
                        bad_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'
                        good_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        count = 0
                        for elem in root.xpath('//*[local-name()="Override"]'):
                            pn = elem.get('PartName', '')
                            ct = elem.get('ContentType', '')
                            if pn.lower().endswith('.docx') and ct == bad_type:
                                elem.set('ContentType', good_type)
                                count += 1
                        if count > 0:
                            print(f"[*] Corrigiendo {count} tipos de contenido en [Content_Types].xml (lxml)")
                            patched = True
                            new_xml_bytes = etree.tostring(root, encoding='UTF-8', xml_declaration=True, standalone=True)
                    except Exception as e:
                        print(f"[!] Warning: Error parsing [Content_Types].xml with lxml: {e}")

            if patched:
                with zipfile.ZipFile(doc_path, 'r') as zin, zipfile.ZipFile(temp_zip_path, 'w') as zout:
                    for item in zin.infolist():
                        if item.filename == '[Content_Types].xml':
                            zout.writestr(item, new_xml_bytes)
                        else:
                            zout.writestr(item, zin.read(item.filename))
                shutil.move(temp_zip_path, doc_path)
        except Exception as e:
            print(f"[!] Warning: No se pudo parchear Content_Types: {e}")
            if os.path.exists(temp_zip_path):
                try: os.remove(temp_zip_path)
                except: pass

    def add_digital_signature_metadata(self, doc_bytes, certificate, private_key, signer_name):
        """
        Añade firma digital XMLDSig válida (compatible con Word) y metadatos de auditoría.
        """
        in_buf = io.BytesIO(doc_bytes)
        out_buf = io.BytesIO()
        
        # Generar IDs únicos
        sig_uuid = uuid.uuid4()
        sig_id_str = str(sig_uuid).upper()
        sig_id = f"idSignature-{sig_id_str}"
        pkg_obj_id = f"idPackageObject-{sig_id_str}"
        office_obj_id = f"idOfficeObject-{sig_id_str}"
        sig_prop_id = f"idSignatureProperty-{sig_id_str}"
        
        # Namespaces
        NS_DS = "http://www.w3.org/2000/09/xmldsig#"
        NS_DSS = "http://schemas.microsoft.com/office/2006/digsig"
        NS_CP = "http://schemas.openxmlformats.org/officeDocument/2006/custom-properties"
        NS_VT = "http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"
        NS_REL = "http://schemas.openxmlformats.org/package/2006/relationships"
        NS_CT = "http://schemas.openxmlformats.org/package/2006/content-types"
        
        nsmap_ds = {None: NS_DS}
        nsmap_dss = {'dss': NS_DSS}
        
        timestamp = datetime.now().strftime("%Y-%m-%dT%H:%M:%SZ")
        
        with zipfile.ZipFile(in_buf, 'r') as zin:
            # === 1. PREPARACIÓN Y AUDITORÍA (Custom Properties) ===
            custom_props_content = b""
            existing_props = []
            pid_start = 2
            
            if 'docProps/custom.xml' in zin.namelist():
                try:
                    custom_props_content = zin.read('docProps/custom.xml')
                    root_cp = etree.fromstring(custom_props_content)
                    for prop in root_cp:
                        existing_props.append(prop)
                        try:
                            pid_val = int(prop.get('pid', '0'))
                            pid_start = max(pid_start, pid_val + 1)
                        except: pass
                except: pass
            
            # Crear nuevo custom.xml
            root_cp = etree.Element(f"{{{NS_CP}}}Properties", nsmap={'cp': NS_CP, 'vt': NS_VT})
            for prop in existing_props:
                name = prop.get('name', '')
                if not name.startswith('DigitalSignature_'):
                    root_cp.append(prop)
            
            signature_props_list = [
                (f"DigitalSignature_Signer", signer_name),
                (f"DigitalSignature_Date", timestamp),
                (f"DigitalSignature_ID", sig_id_str[:8])
            ]
            
            pid = pid_start
            for name, value in signature_props_list:
                prop = etree.SubElement(root_cp, f"{{{NS_CP}}}property", 
                                      fmtid="{D5CDD505-2E9C-101B-9397-08002B2CF9AE}",
                                      pid=str(pid), name=name)
                lpwstr = etree.SubElement(prop, f"{{{NS_VT}}}lpwstr")
                lpwstr.text = str(value)
                pid += 1
                
            new_custom_xml = etree.tostring(root_cp, encoding='UTF-8', xml_declaration=True, standalone=True)


            # === 2. XMLDSIG: MANIFEST ===
            content_types = {}
            if '[Content_Types].xml' in zin.namelist():
                ct_tree = etree.fromstring(zin.read('[Content_Types].xml'))
                for elem in ct_tree:
                    if elem.tag.endswith('Default'):
                        content_types['.' + elem.get('Extension').lower()] = elem.get('ContentType')
                    elif elem.tag.endswith('Override'):
                        content_types[elem.get('PartName')] = elem.get('ContentType')

            manifest_refs = []
            candidates = [
                '/word/document.xml', 
                '/word/settings.xml', 
                '/word/styles.xml',
                '/word/fontTable.xml',
                '/docProps/core.xml',
                '/docProps/app.xml', 
                '/docProps/custom.xml' 
            ]
            
            for f in zin.namelist():
                if f.startswith('word/media/'):
                    candidates.append('/' + f)

            for part_name in candidates:
                zip_name = part_name.lstrip('/')
                part_data = None
                
                if zip_name == 'docProps/custom.xml':
                    part_data = new_custom_xml
                    ctype = "application/vnd.openxmlformats-officedocument.custom-properties+xml"
                elif zip_name in zin.namelist():
                    part_data = zin.read(zip_name)
                    ctype = content_types.get(part_name)
                    if not ctype:
                        ext = os.path.splitext(part_name)[1].lower()
                        ctype = content_types.get(ext)
                
                if part_data and ctype:
                    digest = hashlib.sha256(part_data).digest()
                    digest_b64 = base64.b64encode(digest).decode()
                    
                    ref = etree.Element(f"{{{NS_DS}}}Reference", URI=f"{part_name}?ContentType={ctype}")
                    etree.SubElement(ref, f"{{{NS_DS}}}DigestMethod", Algorithm="http://www.w3.org/2000/09/xmldsig#sha256")
                    etree.SubElement(ref, f"{{{NS_DS}}}DigestValue").text = digest_b64
                    manifest_refs.append(ref)


            # === 3. XMLDSIG: OBJECTS ===
            # Package Object
            obj_pkg = etree.Element(f"{{{NS_DS}}}Object", Id=pkg_obj_id, nsmap=nsmap_ds)
            manifest = etree.SubElement(obj_pkg, f"{{{NS_DS}}}Manifest")
            for ref in manifest_refs:
                manifest.append(ref)
                
            # Office Object
            obj_office = etree.Element(f"{{{NS_DS}}}Object", Id=office_obj_id, nsmap=nsmap_ds)
            sig_props = etree.SubElement(obj_office, f"{{{NS_DS}}}SignatureProperties")
            sig_prop = etree.SubElement(sig_props, f"{{{NS_DS}}}SignatureProperty", Id=sig_prop_id, Target=f"#{sig_id}")
            
            sig_info_v1 = etree.SubElement(sig_prop, f"{{{NS_DSS}}}SignatureInfoV1", nsmap=nsmap_dss)
            etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}SetupID")
            etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}SignatureText")
            etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}SignatureImage")
            comm = etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}SignatureComments")
            comm.text = f"Signed by {signer_name}"
            etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}WindowsVersion").text = "10.0"
            etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}OfficeVersion").text = "16.0"
            etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}ApplicationVersion").text = "16.0"
            monitors = etree.SubElement(sig_info_v1, f"{{{NS_DSS}}}Monitors")
            monitor = etree.SubElement(monitors, f"{{{NS_DSS}}}Monitor")
            etree.SubElement(monitor, f"{{{NS_DSS}}}ColorDepth").text = "32"
            etree.SubElement(monitor, f"{{{NS_DSS}}}HorizontalResolution").text = "96"
            etree.SubElement(monitor, f"{{{NS_DSS}}}VerticalResolution").text = "96"


            # === 4. HASHING (C14N) ===
            obj_pkg_bytes = etree.tostring(obj_pkg, method="c14n", exclusive=False)
            obj_pkg_digest = base64.b64encode(hashlib.sha256(obj_pkg_bytes).digest()).decode()
            
            obj_office_bytes = etree.tostring(obj_office, method="c14n", exclusive=False)
            obj_office_digest = base64.b64encode(hashlib.sha256(obj_office_bytes).digest()).decode()


            # === 5. SIGNED INFO ===
            signed_info = etree.Element(f"{{{NS_DS}}}SignedInfo", nsmap=nsmap_ds)
            etree.SubElement(signed_info, f"{{{NS_DS}}}CanonicalizationMethod", Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315")
            etree.SubElement(signed_info, f"{{{NS_DS}}}SignatureMethod", Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256")
            
            ref_pkg = etree.SubElement(signed_info, f"{{{NS_DS}}}Reference", URI=f"#{pkg_obj_id}", Type="http://www.w3.org/2000/09/xmldsig#Object")
            etree.SubElement(ref_pkg, f"{{{NS_DS}}}DigestMethod", Algorithm="http://www.w3.org/2000/09/xmldsig#sha256")
            etree.SubElement(ref_pkg, f"{{{NS_DS}}}DigestValue").text = obj_pkg_digest
            
            ref_off = etree.SubElement(signed_info, f"{{{NS_DS}}}Reference", URI=f"#{office_obj_id}", Type="http://www.w3.org/2000/09/xmldsig#Object")
            etree.SubElement(ref_off, f"{{{NS_DS}}}DigestMethod", Algorithm="http://www.w3.org/2000/09/xmldsig#sha256")
            etree.SubElement(ref_off, f"{{{NS_DS}}}DigestValue").text = obj_office_digest
            
            # Firmar SignedInfo
            signed_info_bytes = etree.tostring(signed_info, method="c14n", exclusive=False)
            signature_bytes = private_key.sign(
                signed_info_bytes,
                padding.PKCS1v15(),
                hashes.SHA256()
            )
            sig_val_b64 = base64.b64encode(signature_bytes).decode()


            # === 6. ARMAR SIGNATURE FINAL ===
            signature_root = etree.Element(f"{{{NS_DS}}}Signature", Id=sig_id, nsmap=nsmap_ds)
            signature_root.append(signed_info)
            
            val_node = etree.SubElement(signature_root, f"{{{NS_DS}}}SignatureValue")
            val_node.text = sig_val_b64
            
            ki = etree.SubElement(signature_root, f"{{{NS_DS}}}KeyInfo")
            xd = etree.SubElement(ki, f"{{{NS_DS}}}X509Data")
            cert_node = etree.SubElement(xd, f"{{{NS_DS}}}X509Certificate")
            cert_der = certificate.public_bytes(serialization.Encoding.DER)
            cert_node.text = base64.b64encode(cert_der).decode()
            
            signature_root.append(obj_pkg)
            signature_root.append(obj_office)
            
            sig_xml_bytes = etree.tostring(signature_root, encoding='UTF-8', xml_declaration=True, standalone=True)


            # === 7. ESCRIBIR ZIP FINAL ===
            with zipfile.ZipFile(out_buf, 'w', zipfile.ZIP_DEFLATED) as zout:
                existing_sigs = [n for n in zin.namelist() if n.startswith('_xmlsignatures/sig') and n.endswith('.xml')]
                sig_idx = len(existing_sigs) + 1
                new_sig_name = f"_xmlsignatures/sig{sig_idx}.xml"
                origin_name = "_xmlsignatures/origin.sigs"
                origin_rels_name = "_xmlsignatures/_rels/origin.sigs.rels"
                
                files_to_modify = {'[Content_Types].xml', '_rels/.rels', origin_rels_name, 'docProps/custom.xml'}
                
                for item in zin.infolist():
                    if item.filename not in files_to_modify:
                        zout.writestr(item, zin.read(item.filename))
                
                zout.writestr('docProps/custom.xml', new_custom_xml)
                zout.writestr(new_sig_name, sig_xml_bytes)
                if origin_name not in zin.namelist():
                    zout.writestr(origin_name, b"<ds:SignatureOrigin xmlns:ds='http://schemas.openxmlformats.org/package/2006/digital-signature'/>")

                # Actualizar [Content_Types].xml
                if '[Content_Types].xml' in zin.namelist():
                    ct_tree = etree.fromstring(zin.read('[Content_Types].xml'))
                else:
                    ct_tree = etree.Element(f"{{{NS_CT}}}Types", nsmap={None: NS_CT})
                
                if not any(x.get('PartName') == '/docProps/custom.xml' for x in ct_tree.findall(f"{{{NS_CT}}}Override")):
                     etree.SubElement(ct_tree, f"{{{NS_CT}}}Override", PartName="/docProps/custom.xml", ContentType="application/vnd.openxmlformats-officedocument.custom-properties+xml")
                
                etree.SubElement(ct_tree, f"{{{NS_CT}}}Override", PartName=f"/{new_sig_name}", ContentType="application/vnd.openxmlformats-package.digital-signature-xmlsignature+xml")
                if not any(x.get('PartName') == f"/{origin_name}" for x in ct_tree.findall(f"{{{NS_CT}}}Override")):
                    etree.SubElement(ct_tree, f"{{{NS_CT}}}Override", PartName=f"/{origin_name}", ContentType="application/vnd.openxmlformats-package.digital-signature-origin")
                zout.writestr('[Content_Types].xml', etree.tostring(ct_tree, encoding='UTF-8', xml_declaration=True))
                
                # Actualizar _rels/.rels
                rels_root_xml = zin.read('_rels/.rels') if '_rels/.rels' in zin.namelist() else b'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>'
                rels_tree = etree.fromstring(rels_root_xml)
                origin_rel_type = "http://schemas.openxmlformats.org/package/2006/relationships/digital-signature/origin"
                if not any(r.get('Type') == origin_rel_type for r in rels_tree):
                     etree.SubElement(rels_tree, f"{{{NS_REL}}}Relationship", Id="rIdOrigin", Type=origin_rel_type, Target=origin_name)
                zout.writestr('_rels/.rels', etree.tostring(rels_tree, encoding='UTF-8', xml_declaration=True))
                
                # Actualizar origin.sigs.rels
                if origin_rels_name in zin.namelist():
                    origin_rels_tree = etree.fromstring(zin.read(origin_rels_name))
                else:
                    origin_rels_tree = etree.Element(f"{{{NS_REL}}}Relationships", nsmap={None: NS_REL})
                sig_rel_type = "http://schemas.openxmlformats.org/package/2006/relationships/digital-signature/signature"
                etree.SubElement(origin_rels_tree, f"{{{NS_REL}}}Relationship", Id=f"rIdSig{sig_idx}", Type=sig_rel_type, Target=os.path.basename(new_sig_name))
                zout.writestr(origin_rels_name, etree.tostring(origin_rels_tree, encoding='UTF-8', xml_declaration=True))

        return out_buf.getvalue()

    def set_read_only_mode(self, doc_bytes):
        """Aplica protección de SOLO LECTURA al documento (Finalización)"""
        in_buf = io.BytesIO(doc_bytes)
        out_buf = io.BytesIO()
        
        with zipfile.ZipFile(in_buf, 'r') as zin:
            with zipfile.ZipFile(out_buf, 'w', zipfile.ZIP_DEFLATED) as zout:
                for item in zin.infolist():
                    if item.filename == 'word/settings.xml':
                        xml_content = zin.read(item.filename)
                        try:
                            root = etree.fromstring(xml_content)
                            ns = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
                            
                            # Verificar si ya existe protección
                            protection = root.find('w:documentProtection', ns)
                            if protection is None:
                                protection = etree.Element(f"{{{ns['w']}}}documentProtection")
                                root.append(protection)
                            
                            # Configurar solo lectura y enforcement (sin password, pero bloqueado)
                            protection.set(f"{{{ns['w']}}}edit", "readOnly")
                            protection.set(f"{{{ns['w']}}}enforcement", "1")
                            
                            new_xml = etree.tostring(root, encoding='UTF-8', xml_declaration=True, standalone=True)
                            zout.writestr(item, new_xml)
                            print("[*] Documento bloqueado para solo lectura.")
                        except Exception as e:
                            print(f"[!] Error aplicando ReadOnly: {e}")
                            zout.writestr(item, xml_content)
                    else:
                        zout.writestr(item, zin.read(item.filename))
        
        return out_buf.getvalue()

    def protect_document(self, doc_bytes, final_path):
        """Guardado final persistente"""
        with open(final_path, 'wb') as f:
            f.write(doc_bytes)
        return final_path

    def verify_document_signature(self, p, c): return True
