"""
Script para crear un documento Word de prueba
"""
from docx import Document
from docx.shared import Pt, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH


def create_sample_document():
    """Crea un documento Word de ejemplo para probar el sistema de firma"""
    
    doc = Document()
    
    # Título
    title = doc.add_heading('CONTRATO DE SERVICIOS PROFESIONALES', 0)
    title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    
    # Párrafo 1
    doc.add_paragraph()
    p1 = doc.add_paragraph(
        'En la ciudad de Madrid, a 10 de febrero de 2026, se celebra el presente '
        'contrato de servicios profesionales entre las siguientes partes:'
    )
    
    # Párrafo 2
    doc.add_paragraph()
    p2 = doc.add_heading('PRIMERA: PARTES CONTRATANTES', level=2)
    
    # Párrafo 3
    p3 = doc.add_paragraph(
        'De una parte, la empresa ABC TECNOLOGÍA S.L., con CIF B-12345678, '
        'representada por D. Juan Pérez en calidad de Director General.'
    )
    
    # Párrafo 4
    doc.add_paragraph()
    p4 = doc.add_paragraph(
        'De otra parte, Dña. María García, con DNI 12345678-A, en calidad de '
        'consultora independiente.'
    )
    
    # Párrafo 5 - Espacio para firma 1
    doc.add_paragraph()
    doc.add_paragraph()
    
    # Párrafo 6
    p6 = doc.add_heading('SEGUNDA: OBJETO DEL CONTRATO', level=2)
    
    # Párrafo 7
    p7 = doc.add_paragraph(
        'El objeto del presente contrato es la prestación de servicios de consultoría '
        'en desarrollo de software y arquitectura de sistemas, según las especificaciones '
        'técnicas acordadas entre las partes.'
    )
    
    # Párrafo 8
    doc.add_paragraph()
    p8 = doc.add_heading('TERCERA: DURACIÓN Y PRECIO', level=2)
    
    # Párrafo 9
    p9 = doc.add_paragraph(
        'El presente contrato tendrá una duración de 12 meses, comenzando el 1 de marzo '
        'de 2026 y finalizando el 28 de febrero de 2027. El precio total del contrato '
        'asciende a 50.000 EUR (CINCUENTA MIL EUROS), pagaderos en 12 mensualidades.'
    )
    
    # Párrafo 10 - Espacio para firma 2
    doc.add_paragraph()
    doc.add_paragraph()
    
    # Párrafo 11
    p11 = doc.add_heading('CUARTA: OBLIGACIONES DE LAS PARTES', level=2)
    
    # Párrafo 12
    p12 = doc.add_paragraph(
        'La consultora se compromete a prestar los servicios con la máxima diligencia '
        'profesional, cumpliendo los plazos acordados y manteniendo la confidencialidad '
        'de la información a la que tenga acceso.'
    )
    
    # Párrafo 13
    doc.add_paragraph()
    p13 = doc.add_paragraph(
        'La empresa se compromete a facilitar los medios necesarios para la realización '
        'del trabajo y a efectuar los pagos en las fechas acordadas.'
    )
    
    # Párrafo 14
    doc.add_paragraph()
    doc.add_paragraph()
    
    # Párrafo 15
    p15 = doc.add_heading('FIRMAS', level=2)
    p15.alignment = WD_ALIGN_PARAGRAPH.CENTER
    
    # Espacios para firmas
    doc.add_paragraph()
    doc.add_paragraph()
    doc.add_paragraph()
    
    # Líneas de firma
    p_firma1 = doc.add_paragraph('_' * 40)
    p_firma1.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_nombre1 = doc.add_paragraph('Juan Pérez')
    p_nombre1.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cargo1 = doc.add_paragraph('Director General - ABC TECNOLOGÍA S.L.')
    p_cargo1.alignment = WD_ALIGN_PARAGRAPH.CENTER
    
    doc.add_paragraph()
    doc.add_paragraph()
    
    p_firma2 = doc.add_paragraph('_' * 40)
    p_firma2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_nombre2 = doc.add_paragraph('María García')
    p_nombre2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cargo2 = doc.add_paragraph('Consultora Independiente')
    p_cargo2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    
    # Guardar documento
    filename = 'documento_ejemplo.docx'
    doc.save(filename)
    print(f"✓ Documento de ejemplo creado: {filename}")
    print(f"\nEste documento tiene {len(doc.paragraphs)} párrafos.")
    print("Puedes usar este documento para probar el sistema de firma.")
    print("\nSugerencias de posición para firmas:")
    print("  - Primera firma (Juan Pérez): párrafo 5")
    print("  - Segunda firma (María García): párrafo 10")
    
    return filename


if __name__ == "__main__":
    create_sample_document()
