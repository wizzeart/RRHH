"""
Ejemplo de uso programático del sistema de firma digital
Demuestra cómo firmar un documento Word con 2 firmas y certificados
"""
from certificate_manager import CertificateManager
from signature_image import SignatureImage
from word_signer import WordSigner
from datetime import datetime
import os


def main():
    print("=== Sistema de Firma Digital para Word ===\n")
    
    # 1. Crear gestor de certificados
    print("1. Inicializando gestor de certificados...")
    cert_manager = CertificateManager()
    
    # 2. Generar dos certificados (uno por cada firmante)
    print("2. Generando certificados digitales...")
    
    cert1, key1 = cert_manager.generate_certificate(
        common_name="Juan Pérez",
        organization="Empresa ABC",
        country="ES",
        validity_days=365
    )
    print("   ✓ Certificado 1 generado: Juan Pérez")
    
    cert2, key2 = cert_manager.generate_certificate(
        common_name="María García",
        organization="Empresa ABC",
        country="ES",
        validity_days=365
    )
    print("   ✓ Certificado 2 generado: María García")
    
    # 3. Guardar certificados
    print("\n3. Guardando certificados...")
    cert_manager.save_certificate(cert1, key1, "juan_cert.pem", "juan_key.pem")
    cert_manager.save_certificate(cert2, key2, "maria_cert.pem", "maria_key.pem")
    print("   ✓ Certificados guardados")
    
    # 4. Obtener información de certificados
    cert1_info = cert_manager.get_certificate_info(cert1)
    cert2_info = cert_manager.get_certificate_info(cert2)
    
    print(f"\n   Certificado 1:")
    print(f"   - Nombre: {cert1_info['common_name']}")
    print(f"   - Serial: {cert1_info['serial_number']}")
    print(f"   - Válido hasta: {cert1_info['not_valid_after']}")
    
    print(f"\n   Certificado 2:")
    print(f"   - Nombre: {cert2_info['common_name']}")
    print(f"   - Serial: {cert2_info['serial_number']}")
    print(f"   - Válido hasta: {cert2_info['not_valid_after']}")
    
    # 5. Solicitar documento a firmar
    print("\n4. Preparando documento para firmar...")
    doc_path = input("   Ingrese la ruta del documento Word (.docx): ").strip()
    
    if not os.path.exists(doc_path):
        print(f"   ✗ Error: El archivo {doc_path} no existe")
        return
    
    if not doc_path.endswith('.docx'):
        print("   ✗ Error: El archivo debe ser .docx")
        return
    
    # 6. Crear firmador de documentos
    print("\n5. Inicializando firmador de documentos...")
    word_signer = WordSigner(cert_manager)
    
    # 7. Crear y añadir primera firma visual
    print("\n6. Añadiendo primera firma visual (Juan Pérez)...")
    sig_image1 = SignatureImage(width=300, height=100)
    img_data1 = sig_image1.create_signature_image(
        signer_name="Juan Pérez",
        signature_date=datetime.now(),
        certificate_info=cert1_info
    )
    
    # Posición de la primera firma (párrafo 5)
    paragraph_pos1 = 5
    print(f"   Insertando en párrafo {paragraph_pos1}...")
    doc_path = word_signer.add_visual_signature(
        doc_path,
        img_data1,
        position_paragraph=paragraph_pos1
    )
    print("   ✓ Primera firma visual añadida")
    
    # 8. Crear y añadir segunda firma visual
    print("\n7. Añadiendo segunda firma visual (María García)...")
    sig_image2 = SignatureImage(width=300, height=100)
    img_data2 = sig_image2.create_signature_image(
        signer_name="María García",
        signature_date=datetime.now(),
        certificate_info=cert2_info
    )
    
    # Posición de la segunda firma (párrafo 10)
    paragraph_pos2 = 10
    print(f"   Insertando en párrafo {paragraph_pos2}...")
    doc_path = word_signer.add_visual_signature(
        doc_path,
        img_data2,
        position_paragraph=paragraph_pos2
    )
    print("   ✓ Segunda firma visual añadida")
    
    # 9. Aplicar primera firma digital con certificado
    print("\n8. Aplicando primera firma digital (Juan Pérez)...")
    doc_path = word_signer.add_digital_signature_metadata(
        doc_path,
        cert1,
        key1,
        "Juan Pérez",
        "Aprobación de documento"
    )
    print("   ✓ Primera firma digital aplicada")
    
    # 10. Aplicar segunda firma digital con certificado
    print("\n9. Aplicando segunda firma digital (María García)...")
    doc_path = word_signer.add_digital_signature_metadata(
        doc_path,
        cert2,
        key2,
        "María García",
        "Revisión técnica"
    )
    print("   ✓ Segunda firma digital aplicada")
    
    # 11. Proteger documento (solo lectura)
    print("\n10. Protegiendo documento (solo lectura)...")
    doc_path = word_signer.protect_document(doc_path)
    print("   ✓ Documento protegido")
    
    # 12. Resultado final
    print("\n" + "="*50)
    print("✓ DOCUMENTO FIRMADO EXITOSAMENTE")
    print("="*50)
    print(f"\nDocumento firmado guardado en:")
    print(f"   {doc_path}")
    print(f"\nCertificados guardados:")
    print(f"   juan_cert.pem / juan_key.pem")
    print(f"   maria_cert.pem / maria_key.pem")
    
    # 13. Verificar firmas (opcional)
    print("\n11. Verificando firmas digitales...")
    
    is_valid1 = word_signer.verify_document_signature(doc_path, cert1)
    print(f"   Firma de Juan Pérez: {'✓ Válida' if is_valid1 else '✗ Inválida'}")
    
    is_valid2 = word_signer.verify_document_signature(doc_path, cert2)
    print(f"   Firma de María García: {'✓ Válida' if is_valid2 else '✗ Inválida'}")
    
    print("\n¡Proceso completado!")


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nProceso cancelado por el usuario")
    except Exception as e:
        print(f"\n✗ Error: {str(e)}")
        import traceback
        traceback.print_exc()
