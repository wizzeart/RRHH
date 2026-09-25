"""
Interfaz gráfica principal para el sistema de firma digital de documentos Word
Permite seleccionar documentos, posicionar firmas y aplicar certificados digitales
"""
import sys
import os
from PyQt5.QtWidgets import (QApplication, QMainWindow, QWidget, QVBoxLayout, 
                             QHBoxLayout, QPushButton, QLabel, QFileDialog, 
                             QLineEdit, QMessageBox, QSpinBox, QGroupBox,
                             QTextEdit, QTabWidget, QFormLayout, QCheckBox,
                             QProgressBar, QComboBox, QScrollArea, QTextBrowser)
from PyQt5.QtCore import Qt, QThread, pyqtSignal, QPoint
from PyQt5.QtGui import QPixmap, QFont, QImage, QPainter, QPen
from datetime import datetime
import traceback
import time

from certificate_manager import CertificateManager
from signature_image import SignatureImage
from word_signer import WordSigner


class SignaturePad(QWidget):
    """Widget para capturar trazos del mouse/touch"""
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setFixedSize(500, 200)
        self.image = QImage(self.size(), QImage.Format_ARGB32)
        self.image.fill(Qt.transparent)
        self.drawing = False
        self.last_point = QPoint()
        self.pen_color = Qt.blue
        self.pen_width = 3

    def paintEvent(self, event):
        painter = QPainter(self)
        painter.drawImage(0, 0, self.image)

    def mousePressEvent(self, event):
        if event.button() == Qt.LeftButton:
            self.drawing = True
            self.last_point = event.pos()

    def mouseMoveEvent(self, event):
        if (event.buttons() & Qt.LeftButton) and self.drawing:
            painter = QPainter(self.image)
            painter.setRenderHint(QPainter.Antialiasing)
            painter.setPen(QPen(self.pen_color, self.pen_width, Qt.SolidLine, Qt.RoundCap, Qt.RoundJoin))
            painter.drawLine(self.last_point, event.pos())
            self.last_point = event.pos()
            self.update()

    def mouseReleaseEvent(self, event):
        if event.button() == Qt.LeftButton:
            self.drawing = False

    def clear(self):
        self.image.fill(Qt.transparent)
        self.update()

    def save(self, file_path):
        # Recortar espacios vacios si es posible (opcional)
        self.image.save(file_path, "PNG")


class SigningThread(QThread):
    """Thread para procesar la firma sin bloquear la UI"""
    progress = pyqtSignal(str)
    finished = pyqtSignal(bool, str)
    
    def __init__(self, word_signer, doc_path, signatures_data, certificates_data):
        super().__init__()
        self.word_signer = word_signer
        self.doc_path = doc_path
        self.signatures_data = signatures_data
        self.certificates_data = certificates_data
    
    def run(self):
        try:
            self.progress.emit("Iniciando proceso...")
            
            # Verificar tamaño del archivo origen
            if not os.path.exists(self.doc_path):
                raise FileNotFoundError(f"No se encontró el archivo: {self.doc_path}")
            
            orig_size = os.path.getsize(self.doc_path)
            if orig_size == 0:
                raise ValueError("El archivo seleccionado tiene 0 bytes. Está corrupto.")
            
            # 1. Preparar las imágenes de firma
            self.progress.emit("Preparando imágenes de firma...")
            visual_signatures = []
            for sig_data in self.signatures_data:
                sig_image = SignatureImage(width=300, height=150)
                img_data = sig_image.create_signature_image(
                    signer_name=sig_data['signer_name'],
                    signature_date=datetime.now(),
                    signature_image_path=sig_data.get('image_path'),
                    certificate_info=sig_data.get('cert_info')
                )
                visual_signatures.append({
                    'image_data': img_data,
                    'keyword': sig_data.get('keyword'),
                    'paragraph_index': sig_data.get('paragraph_index')
                })

            # 2. Aplicar todas las firmas visuales
            self.progress.emit("Insertando firmas en el documento...")
            current_bytes = self.word_signer.batch_visual_sign(self.doc_path, visual_signatures)
            
            # 3. Aplicar protección de solo lectura (ANTES de firmar para no invalidar hash)
            self.progress.emit("Aplicando protección de solo lectura...")
            current_bytes = self.word_signer.set_read_only_mode(current_bytes)
            
            # 4. Aplicar certificados digitales (XMLDSig)
            for i, cert_data in enumerate(self.certificates_data):
                self.progress.emit(f"Certificando documento ({i+1}/{len(self.certificates_data)})...")
                current_bytes = self.word_signer.add_digital_signature_metadata(
                    current_bytes,
                    cert_data['certificate'],
                    cert_data['private_key'],
                    cert_data['signer_name']
                )
            
            self.progress.emit("Finalizando y guardando archivo...")
            timestamp = datetime.now().strftime("%H%M%S")
            final_filename = self.doc_path.replace('.docx', f'_firmado_{timestamp}.docx')
            
            final_path = self.word_signer.protect_document(current_bytes, final_filename)
            
            self.finished.emit(True, final_path)
            
        except Exception as e:
            error_msg = f"Error: {str(e)}"
            print(f"ERROR DETALLADO: {traceback.format_exc()}")
            self.finished.emit(False, error_msg)


class DigitalSignatureApp(QMainWindow):
    """Aplicación principal de firma digital"""
    
    def __init__(self):
        super().__init__()
        self.cert_manager = CertificateManager()
        self.word_signer = WordSigner(self.cert_manager)
        
        # Datos de firmas y certificados
        self.signatures = []  # Lista de datos de firma visual
        self.certificates = []  # Lista de certificados cargados
        
        # Rutas de archivos
        self.document_path = None
        
        self.init_ui()
    
    def init_ui(self):
        """Inicializa la interfaz de usuario"""
        self.setWindowTitle("Sistema de Firma Digital IML - Word")
        self.setGeometry(100, 100, 1000, 800)
        
        # Widget central
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Layout principal
        main_layout = QVBoxLayout()
        central_widget.setLayout(main_layout)
        
        # Título
        title = QLabel("Sistema de Firma Digital IML - Word")
        title.setFont(QFont("Arial", 18, QFont.Bold))
        title.setStyleSheet("color: #2c3e50; margin-bottom: 10px;")
        title.setAlignment(Qt.AlignCenter)
        main_layout.addWidget(title)
        
        # Tabs
        tabs = QTabWidget()
        main_layout.addWidget(tabs)
        
        # Tab 0: Generar Firma (Pad)
        tab0 = self.create_pad_tab()
        tabs.addTab(tab0, "0. Generar Firma")

        # Tab 1: Selección de documento
        tab1 = self.create_document_tab()
        tabs.addTab(tab1, "1. Documento")
        
        # Tab 2: Certificados
        tab2 = self.create_certificates_tab()
        tabs.addTab(tab2, "2. Certificados")
        
        # Tab 3: Firmas visuales
        tab3 = self.create_signatures_tab()
        tabs.addTab(tab3, "3. Configurar Firmas")
        
        # Tab 4: Firmar documento
        tab4 = self.create_signing_tab()
        tabs.addTab(tab4, "4. Procesar y Guardar")
        
        # Barra de estado
        self.statusBar().showMessage("Listo")

    def create_pad_tab(self):
        """Crea la pestaña para dibujar la firma"""
        widget = QWidget()
        layout = QVBoxLayout()
        widget.setLayout(layout)

        label = QLabel("Dibuja tu firma con el ratón o dispositivo táctil:")
        label.setFont(QFont("Arial", 12))
        layout.addWidget(label)

        # Pad de firma
        pad_container = QGroupBox("Área de Trazo")
        pad_layout = QVBoxLayout()
        self.sig_pad = SignaturePad()
        self.sig_pad.setStyleSheet("background-color: white; border: 2px dashed #bdc3c7;")
        pad_layout.addWidget(self.sig_pad, alignment=Qt.AlignCenter)
        pad_container.setLayout(pad_layout)
        layout.addWidget(pad_container)

        # Botones del pad
        btn_layout = QHBoxLayout()
        btn_clear = QPushButton("Limpiar Trazo")
        btn_clear.clicked.connect(self.sig_pad.clear)
        btn_layout.addWidget(btn_clear)

        btn_save_sig = QPushButton("Guardar Firma como PNG")
        btn_save_sig.setStyleSheet("background-color: #3498db; color: white;")
        btn_save_sig.clicked.connect(self.save_pad_signature)
        btn_layout.addWidget(btn_save_sig)
        layout.addLayout(btn_layout)

        info = QLabel("Nota: Las firmas se guardan con fondo transparente para superponerse en Word.")
        info.setStyleSheet("color: #7f8c8d; font-style: italic;")
        layout.addWidget(info)
        
        # Botón para abrir documento en Word
        doc_group = QGroupBox("Documento Seleccionado")
        doc_layout = QVBoxLayout()
        doc_group.setLayout(doc_layout)
        
        self.lbl_doc_preview = QLabel("No hay documento seleccionado")
        self.lbl_doc_preview.setWordWrap(True)
        self.lbl_doc_preview.setStyleSheet("padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px;")
        doc_layout.addWidget(self.lbl_doc_preview)
        
        self.btn_open_doc = QPushButton("📄 Leer Documento en Word")
        self.btn_open_doc.setStyleSheet("background-color: #2ecc71; color: white; padding: 10px; font-size: 12pt;")
        self.btn_open_doc.clicked.connect(self.open_document_in_word)
        self.btn_open_doc.setEnabled(False)
        doc_layout.addWidget(self.btn_open_doc)
        
        layout.addWidget(doc_group)
        layout.addStretch()

        return widget

    def save_pad_signature(self):
        file_path, _ = QFileDialog.getSaveFileName(
            self, "Guardar Firma", "firma_digital.png", "Imágenes PNG (*.png)"
        )
        if file_path:
            self.sig_pad.save(file_path)
            QMessageBox.information(self, "Éxito", f"Firma guardada en:\n{file_path}")

    def create_document_tab(self):
        """Crea la pestaña de selección de documento"""
        widget = QWidget()
        layout = QVBoxLayout()
        widget.setLayout(layout)
        
        # Grupo de selección de documento
        doc_group = QGroupBox("Seleccionar Documento Word")
        doc_layout = QVBoxLayout()
        doc_group.setLayout(doc_layout)
        
        # Botón para seleccionar documento
        btn_select = QPushButton("Seleccionar Documento (.docx)")
        btn_select.clicked.connect(self.select_document)
        doc_layout.addWidget(btn_select)
        
        # Label para mostrar documento seleccionado
        self.lbl_document = QLabel("Ningún documento seleccionado")
        self.lbl_document.setWordWrap(True)
        doc_layout.addWidget(self.lbl_document)
        
        layout.addWidget(doc_group)
        layout.addStretch()
        
        return widget
    
    def create_certificates_tab(self):
        """Crea la pestaña de gestión de certificados"""
        widget = QWidget()
        layout = QVBoxLayout()
        widget.setLayout(layout)
        
        # Grupo: Generar nuevo certificado
        gen_group = QGroupBox("Generar Nuevo Certificado")
        gen_layout = QFormLayout()
        gen_group.setLayout(gen_layout)
        
        self.txt_cert_name = QLineEdit()
        self.txt_cert_name.setPlaceholderText("Ej: Juan Pérez")
        gen_layout.addRow("Nombre del firmante:", self.txt_cert_name)
        
        self.txt_cert_org = QLineEdit()
        self.txt_cert_org.setText("Digital Signature")
        gen_layout.addRow("Organización:", self.txt_cert_org)
        
        self.txt_cert_country = QLineEdit()
        self.txt_cert_country.setText("US")
        self.txt_cert_country.setMaxLength(2)
        gen_layout.addRow("País (2 letras):", self.txt_cert_country)
        
        btn_generate = QPushButton("Generar Certificado")
        btn_generate.clicked.connect(self.generate_certificate)
        gen_layout.addRow(btn_generate)
        
        layout.addWidget(gen_group)
        
        # Grupo: Cargar certificado existente
        load_group = QGroupBox("Cargar Certificado Existente")
        load_layout = QVBoxLayout()
        load_group.setLayout(load_layout)
        
        btn_load = QPushButton("Cargar Certificado (.pem)")
        btn_load.clicked.connect(self.load_certificate)
        load_layout.addWidget(btn_load)
        
        layout.addWidget(load_group)
        
        # Lista de certificados cargados
        cert_list_group = QGroupBox("Certificados Cargados")
        cert_list_layout = QVBoxLayout()
        cert_list_group.setLayout(cert_list_layout)
        
        self.txt_cert_list = QTextEdit()
        self.txt_cert_list.setReadOnly(True)
        self.txt_cert_list.setMaximumHeight(150)
        cert_list_layout.addWidget(self.txt_cert_list)
        
        layout.addWidget(cert_list_group)
        layout.addStretch()
        
        return widget
    
    def create_signatures_tab(self):
        """Crea la pestaña de configuración de firmas visuales"""
        widget = QWidget()
        layout = QVBoxLayout()
        widget.setLayout(layout)
        
        # Grupo: Añadir firma visual
        sig_group = QGroupBox("Añadir Firma Visual")
        sig_layout = QFormLayout()
        sig_group.setLayout(sig_layout)
        
        self.txt_sig_name = QLineEdit()
        self.txt_sig_name.setPlaceholderText("Nombre del firmante")
        sig_layout.addRow("Nombre:", self.txt_sig_name)
        
        # Selector de tipo de posicionamiento
        self.combo_placement = QComboBox()
        self.combo_placement.addItems(["Por Etiqueta (Recomendado)", "Por Párrafo"])
        self.combo_placement.currentIndexChanged.connect(self.on_placement_changed)
        sig_layout.addRow("Posicionamiento:", self.combo_placement)

        # Campo para etiqueta
        self.combo_keyword = QComboBox()
        self.combo_keyword.addItems(["EL TRABAJADOR", "EL EMPLEADOR", "OTRA..."])
        self.combo_keyword.setEditable(True)
        sig_layout.addRow("Etiqueta en Word:", self.combo_keyword)

        # Campo para párrafo
        self.spin_paragraph = QSpinBox()
        self.spin_paragraph.setMinimum(0)
        self.spin_paragraph.setMaximum(5000)
        self.spin_paragraph.setEnabled(False)
        sig_layout.addRow("Nº de Párrafo:", self.spin_paragraph)
        
        # Imagen de firma (opcional)
        sig_img_layout = QHBoxLayout()
        self.txt_sig_image = QLineEdit()
        self.txt_sig_image.setPlaceholderText("Selecciona una imagen de firma o usa el Pad")
        self.txt_sig_image.setReadOnly(True)
        btn_browse_sig = QPushButton("Buscar PNG...")
        btn_browse_sig.clicked.connect(self.browse_signature_image)
        sig_img_layout.addWidget(self.txt_sig_image)
        sig_img_layout.addWidget(btn_browse_sig)
        sig_layout.addRow("Imagen de firma:", sig_img_layout)
        
        btn_add_sig = QPushButton("Añadir esta Firma a la lista")
        btn_add_sig.setStyleSheet("padding: 5px; height: 30px;")
        btn_add_sig.clicked.connect(self.add_signature)
        sig_layout.addRow(btn_add_sig)
        
        layout.addWidget(sig_group)
        
        # Lista de firmas añadidas
        sig_list_group = QGroupBox("Lista de Firmas a Aplicar")
        sig_list_layout = QVBoxLayout()
        sig_list_group.setLayout(sig_list_layout)
        
        self.txt_sig_list = QTextEdit()
        self.txt_sig_list.setReadOnly(True)
        self.txt_sig_list.setMaximumHeight(200)
        sig_list_layout.addWidget(self.txt_sig_list)
        
        btn_clear_sigs = QPushButton("Borrar Lista")
        btn_clear_sigs.clicked.connect(self.clear_signatures)
        sig_list_layout.addWidget(btn_clear_sigs)
        
        layout.addWidget(sig_list_group)
        layout.addStretch()
        
        return widget

    def on_placement_changed(self, index):
        """Habilita/Deshabilita campos según el tipo de posicionamiento"""
        is_paragraph = (index == 1)
        self.spin_paragraph.setEnabled(is_paragraph)
        self.combo_keyword.setEnabled(not is_paragraph)
    
    def create_signing_tab(self):
        """Crea la pestaña de firma del documento"""
        widget = QWidget()
        layout = QVBoxLayout()
        widget.setLayout(layout)
        
        # Resumen
        summary_group = QGroupBox("Resumen")
        summary_layout = QVBoxLayout()
        summary_group.setLayout(summary_layout)
        
        self.txt_summary = QTextEdit()
        self.txt_summary.setReadOnly(True)
        self.txt_summary.setMaximumHeight(200)
        summary_layout.addWidget(self.txt_summary)
        
        layout.addWidget(summary_group)
        
        # Progreso
        self.progress_bar = QProgressBar()
        self.progress_bar.setVisible(False)
        layout.addWidget(self.progress_bar)
        
        self.lbl_progress = QLabel("")
        layout.addWidget(self.lbl_progress)
        
        # Botón de firma
        btn_sign = QPushButton("FIRMAR DOCUMENTO")
        btn_sign.setStyleSheet("QPushButton { background-color: #4CAF50; color: white; font-size: 14pt; padding: 10px; }")
        btn_sign.clicked.connect(self.sign_document)
        layout.addWidget(btn_sign)
        
        layout.addStretch()
        
        return widget
    
    def select_document(self):
        """Selecciona el documento Word a firmar"""
        file_path, _ = QFileDialog.getOpenFileName(
            self, "Seleccionar Documento Word", "", "Documentos Word (*.docx)"
        )
        
        if file_path:
            self.document_path = file_path
            self.lbl_document.setText(f"Documento: {os.path.basename(file_path)}\nRuta: {file_path}")
            self.statusBar().showMessage(f"Documento seleccionado: {os.path.basename(file_path)}")
            self.update_summary()
            self.update_document_preview(file_path)
    
    def generate_certificate(self):
        """Genera un nuevo certificado autofirmado"""
        name = self.txt_cert_name.text().strip()
        org = self.txt_cert_org.text().strip()
        country = self.txt_cert_country.text().strip().upper()
        
        if not name:
            QMessageBox.warning(self, "Error", "Debe ingresar el nombre del firmante")
            return
        
        if len(country) != 2:
            QMessageBox.warning(self, "Error", "El código de país debe tener 2 letras")
            return
        
        try:
            # Generar certificado
            cert, private_key = self.cert_manager.generate_certificate(
                common_name=name,
                organization=org,
                country=country
            )
            
            # Guardar certificado
            save_dir = QFileDialog.getExistingDirectory(self, "Seleccionar carpeta para guardar certificado")
            if save_dir:
                cert_path = os.path.join(save_dir, f"{name.replace(' ', '_')}_cert.pem")
                key_path = os.path.join(save_dir, f"{name.replace(' ', '_')}_key.pem")
                
                self.cert_manager.save_certificate(cert, private_key, cert_path, key_path)
                
                # Añadir a la lista
                cert_info = self.cert_manager.get_certificate_info(cert)
                self.certificates.append({
                    'certificate': cert,
                    'private_key': private_key,
                    'signer_name': name,
                    'cert_info': cert_info,
                    'cert_path': cert_path,
                    'key_path': key_path
                })
                
                self.update_certificate_list()
                self.update_summary()
                
                QMessageBox.information(
                    self, "Éxito", 
                    f"Certificado generado y guardado:\n{cert_path}\n{key_path}"
                )
        
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando certificado: {str(e)}")
    
    def load_certificate(self):
        """Carga un certificado existente"""
        cert_path, _ = QFileDialog.getOpenFileName(
            self, "Seleccionar Certificado", "", "Certificados PEM (*.pem)"
        )
        
        if not cert_path:
            return
        
        key_path, _ = QFileDialog.getOpenFileName(
            self, "Seleccionar Clave Privada", "", "Claves PEM (*.pem)"
        )
        
        if not key_path:
            return
        
        try:
            cert, private_key = self.cert_manager.load_certificate(cert_path, key_path)
            cert_info = self.cert_manager.get_certificate_info(cert)
            
            signer_name = cert_info['common_name']
            
            self.certificates.append({
                'certificate': cert,
                'private_key': private_key,
                'signer_name': signer_name,
                'cert_info': cert_info,
                'cert_path': cert_path,
                'key_path': key_path
            })
            
            self.update_certificate_list()
            self.update_summary()
            
            QMessageBox.information(self, "Éxito", f"Certificado cargado: {signer_name}")
        
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando certificado: {str(e)}")
    
    def browse_signature_image(self):
        """Busca una imagen de firma manuscrita"""
        file_path, _ = QFileDialog.getOpenFileName(
            self, "Seleccionar Imagen de Firma", "", "Imágenes (*.png *.jpg *.jpeg)"
        )
        
        if file_path:
            self.txt_sig_image.setText(file_path)
    
    def add_signature(self):
        """Añade una firma visual a la lista"""
        name = self.txt_sig_name.text().strip()
        
        if not name:
            QMessageBox.warning(self, "Error", "Debe ingresar el nombre del firmante")
            return
        
        placement_type = self.combo_placement.currentIndex()
        
        sig_data = {
            'signer_name': name,
            'image_path': self.txt_sig_image.text() if self.txt_sig_image.text() else None,
            'paragraph_index': self.spin_paragraph.value() if placement_type == 1 else None,
            'keyword': self.combo_keyword.currentText() if placement_type == 0 else None
        }
        
        # Asociar con certificado si existe
        for cert in self.certificates:
            if cert['signer_name'] == name:
                sig_data['cert_info'] = cert['cert_info']
                break
        
        self.signatures.append(sig_data)
        self.update_signature_list()
        self.update_summary()
        
        # Limpiar campos
        self.txt_sig_name.clear()
        self.txt_sig_image.clear()
    
    def clear_signatures(self):
        """Limpia la lista de firmas visuales"""
        self.signatures.clear()
        self.update_signature_list()
        self.update_summary()
    
    def update_certificate_list(self):
        """Actualiza la lista de certificados cargados"""
        text = ""
        for i, cert in enumerate(self.certificates):
            info = cert['cert_info']
            text += f"{i+1}. {cert['signer_name']}\n"
            text += f"   Serial: {info['serial_number']}\n"
            text += f"   Válido hasta: {info['not_valid_after']}\n\n"
        
        self.txt_cert_list.setText(text if text else "No hay certificados cargados")
    
    def update_signature_list(self):
        """Actualiza la lista de firmas visuales"""
        text = ""
        for i, sig in enumerate(self.signatures):
            pos = f"Etiqueta: {sig['keyword']}" if sig.get('keyword') else f"Párrafo: {sig['paragraph_index']}"
            text += f"{i+1}. {sig['signer_name']} ({pos})\n"
            if sig.get('image_path'):
                text += f"   Imagen: {os.path.basename(sig['image_path'])}\n"
            text += "\n"
        
        self.txt_sig_list.setText(text if text else "No hay firmas añadidas")
    
    def update_summary(self):
        """Actualiza el resumen de la operación de firma"""
        summary = "=== RESUMEN DE FIRMA ===\n\n"
        
        if self.document_path:
            summary += f"Documento: {os.path.basename(self.document_path)}\n\n"
        else:
            summary += "Documento: No seleccionado\n\n"
        
        summary += f"Firmas visuales: {len(self.signatures)}\n"
        for sig in self.signatures:
            summary += f"  - {sig['signer_name']}\n"
        
        summary += f"\nCertificados digitales: {len(self.certificates)}\n"
        for cert in self.certificates:
            summary += f"  - {cert['signer_name']}\n"
        
        self.txt_summary.setText(summary)
    
    def update_document_preview(self, file_path):
        """Actualiza la información del documento seleccionado"""
        try:
            from docx import Document
            doc = Document(file_path)
            
            info_text = f"📄 Documento: {os.path.basename(file_path)}\n"
            info_text += f"📁 Ruta: {file_path}\n"
            info_text += f"📊 Total de párrafos: {len(doc.paragraphs)}"
            
            self.lbl_doc_preview.setText(info_text)
            self.btn_open_doc.setEnabled(True)
            
        except Exception as e:
            self.lbl_doc_preview.setText(f"Error al leer documento:\n{str(e)}")
            self.btn_open_doc.setEnabled(False)
    
    def open_document_in_word(self):
        """Abre el documento seleccionado en Microsoft Word"""
        if not self.document_path or not os.path.exists(self.document_path):
            QMessageBox.warning(self, "Error", "No hay documento seleccionado o el archivo no existe")
            return
        
        try:
            import subprocess
            # En Windows, usar el comando 'start' para abrir con la aplicación predeterminada
            if sys.platform == 'win32':
                os.startfile(self.document_path)
            elif sys.platform == 'darwin':  # macOS
                subprocess.run(['open', self.document_path])
            else:  # Linux
                subprocess.run(['xdg-open', self.document_path])
            
            self.statusBar().showMessage(f"Abriendo documento en Word...")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"No se pudo abrir el documento:\n{str(e)}")
    
    def sign_document(self):
        """Firma el documento con todas las firmas y certificados"""
        # Validaciones
        if not self.document_path:
            QMessageBox.warning(self, "Error", "Debe seleccionar un documento")
            return
        
        if len(self.signatures) == 0:
            QMessageBox.warning(self, "Error", "Debe añadir al menos una firma visual")
            return
        
        if len(self.certificates) == 0:
            QMessageBox.warning(self, "Error", "Debe cargar al menos un certificado")
            return
        
        # Confirmar
        reply = QMessageBox.question(
            self, "Confirmar Firma",
            f"¿Desea firmar el documento con {len(self.signatures)} firma(s) visual(es) y {len(self.certificates)} certificado(s)?",
            QMessageBox.Yes | QMessageBox.No
        )
        
        if reply == QMessageBox.No:
            return
        
        # Iniciar proceso de firma en thread separado
        self.progress_bar.setVisible(True)
        self.progress_bar.setRange(0, 0)  # Modo indeterminado
        
        self.signing_thread = SigningThread(
            self.word_signer,
            self.document_path,
            self.signatures,
            self.certificates
        )
        
        self.signing_thread.progress.connect(self.on_signing_progress)
        self.signing_thread.finished.connect(self.on_signing_finished)
        self.signing_thread.start()
    
    def on_signing_progress(self, message):
        """Actualiza el progreso de la firma"""
        self.lbl_progress.setText(message)
        self.statusBar().showMessage(message)
    
    def on_signing_finished(self, success, result):
        """Maneja la finalización del proceso de firma"""
        self.progress_bar.setVisible(False)
        self.lbl_progress.setText("")
        
        if success:
            QMessageBox.information(
                self, "Éxito",
                f"Documento firmado exitosamente!\n\nGuardado en:\n{result}"
            )
            self.statusBar().showMessage("Documento firmado exitosamente")
        else:
            QMessageBox.critical(
                self, "Error",
                f"Error durante la firma:\n{result}"
            )
            self.statusBar().showMessage("Error en la firma")


def main():
    """Función principal"""
    app = QApplication(sys.argv)
    window = DigitalSignatureApp()
    window.show()
    sys.exit(app.exec_())


if __name__ == "__main__":
    main()
