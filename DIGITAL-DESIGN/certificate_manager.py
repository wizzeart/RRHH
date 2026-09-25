"""
Módulo para gestión de certificados digitales X.509
Permite crear, cargar y gestionar certificados para firmas digitales
"""
import os
from datetime import datetime, timedelta
from cryptography import x509
from cryptography.x509.oid import NameOID, ExtensionOID
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.hazmat.backends import default_backend
import hashlib


class CertificateManager:
    """Gestor de certificados digitales para firmas"""
    
    def __init__(self):
        self.certificates = []
        self.private_keys = []
    
    def generate_certificate(self, common_name, organization="Digital Signature", 
                           country="US", validity_days=365):
        """
        Genera un certificado autofirmado X.509
        
        Args:
            common_name: Nombre del firmante
            organization: Organización
            country: Código de país (2 letras)
            validity_days: Días de validez del certificado
            
        Returns:
            tuple: (certificado, clave_privada)
        """
        # Generar clave privada RSA
        private_key = rsa.generate_private_key(
            public_exponent=65537,
            key_size=2048,
            backend=default_backend()
        )
        
        # Crear el nombre del sujeto
        subject = issuer = x509.Name([
            x509.NameAttribute(NameOID.COUNTRY_NAME, country),
            x509.NameAttribute(NameOID.ORGANIZATION_NAME, organization),
            x509.NameAttribute(NameOID.COMMON_NAME, common_name),
        ])
        
        # Construir el certificado
        cert = (
            x509.CertificateBuilder()
            .subject_name(subject)
            .issuer_name(issuer)
            .public_key(private_key.public_key())
            .serial_number(x509.random_serial_number())
            .not_valid_before(datetime.utcnow())
            .not_valid_after(datetime.utcnow() + timedelta(days=validity_days))
            .add_extension(
                x509.SubjectAlternativeName([
                    x509.DNSName(common_name),
                ]),
                critical=False,
            )
            .add_extension(
                x509.BasicConstraints(ca=False, path_length=None),
                critical=True,
            )
            .add_extension(
                x509.KeyUsage(
                    digital_signature=True,
                    content_commitment=True,
                    key_encipherment=False,
                    data_encipherment=False,
                    key_agreement=False,
                    key_cert_sign=False,
                    crl_sign=False,
                    encipher_only=False,
                    decipher_only=False,
                ),
                critical=True,
            )
            .sign(private_key, hashes.SHA256(), default_backend())
        )
        
        return cert, private_key
    
    def save_certificate(self, certificate, private_key, cert_path, key_path, password=None):
        """
        Guarda el certificado y la clave privada en archivos
        
        Args:
            certificate: Certificado X.509
            private_key: Clave privada
            cert_path: Ruta para guardar el certificado (.pem)
            key_path: Ruta para guardar la clave privada (.pem)
            password: Contraseña opcional para encriptar la clave privada
        """
        # Guardar certificado
        with open(cert_path, "wb") as f:
            f.write(certificate.public_bytes(serialization.Encoding.PEM))
        
        # Guardar clave privada
        encryption = serialization.NoEncryption()
        if password:
            encryption = serialization.BestAvailableEncryption(password.encode())
        
        with open(key_path, "wb") as f:
            f.write(private_key.private_bytes(
                encoding=serialization.Encoding.PEM,
                format=serialization.PrivateFormat.PKCS8,
                encryption_algorithm=encryption
            ))
    
    def load_certificate(self, cert_path, key_path, password=None):
        """
        Carga un certificado y clave privada desde archivos
        
        Args:
            cert_path: Ruta del certificado (.pem)
            key_path: Ruta de la clave privada (.pem)
            password: Contraseña si la clave está encriptada
            
        Returns:
            tuple: (certificado, clave_privada)
        """
        # Cargar certificado
        with open(cert_path, "rb") as f:
            cert_data = f.read()
            certificate = x509.load_pem_x509_certificate(cert_data, default_backend())
        
        # Cargar clave privada
        with open(key_path, "rb") as f:
            key_data = f.read()
            pwd = password.encode() if password else None
            private_key = serialization.load_pem_private_key(
                key_data, password=pwd, backend=default_backend()
            )
        
        return certificate, private_key
    
    def get_certificate_info(self, certificate):
        """
        Extrae información del certificado
        
        Args:
            certificate: Certificado X.509
            
        Returns:
            dict: Información del certificado
        """
        subject = certificate.subject
        issuer = certificate.issuer
        
        info = {
            'common_name': subject.get_attributes_for_oid(NameOID.COMMON_NAME)[0].value,
            'organization': subject.get_attributes_for_oid(NameOID.ORGANIZATION_NAME)[0].value if subject.get_attributes_for_oid(NameOID.ORGANIZATION_NAME) else "N/A",
            'country': subject.get_attributes_for_oid(NameOID.COUNTRY_NAME)[0].value if subject.get_attributes_for_oid(NameOID.COUNTRY_NAME) else "N/A",
            'serial_number': certificate.serial_number,
            'not_valid_before': certificate.not_valid_before,
            'not_valid_after': certificate.not_valid_after,
            'fingerprint': certificate.fingerprint(hashes.SHA256()).hex(),
        }
        
        return info
    
    def sign_data(self, data, private_key):
        """
        Firma datos con la clave privada
        
        Args:
            data: Datos a firmar (bytes)
            private_key: Clave privada
            
        Returns:
            bytes: Firma digital
        """
        from cryptography.hazmat.primitives.asymmetric import padding
        
        signature = private_key.sign(
            data,
            padding.PSS(
                mgf=padding.MGF1(hashes.SHA256()),
                salt_length=padding.PSS.MAX_LENGTH
            ),
            hashes.SHA256()
        )
        
        return signature
    
    def verify_signature(self, data, signature, certificate):
        """
        Verifica una firma digital
        
        Args:
            data: Datos originales (bytes)
            signature: Firma a verificar
            certificate: Certificado con la clave pública
            
        Returns:
            bool: True si la firma es válida
        """
        from cryptography.hazmat.primitives.asymmetric import padding
        
        try:
            public_key = certificate.public_key()
            public_key.verify(
                signature,
                data,
                padding.PSS(
                    mgf=padding.MGF1(hashes.SHA256()),
                    salt_length=padding.PSS.MAX_LENGTH
                ),
                hashes.SHA256()
            )
            return True
        except Exception as e:
            print(f"Error verificando firma: {e}")
            return False
