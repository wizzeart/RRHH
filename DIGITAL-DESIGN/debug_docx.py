import io
import os
import sys
import traceback

print("--- DEBUG DOCX START ---")
print(f"Python: {sys.version}")
print(f"CWD: {os.getcwd()}")

file_path = "documento_ejemplo.docx"

if not os.path.exists(file_path):
    print(f"ERROR: File {file_path} does not exist!")
    sys.exit(1)

size = os.path.getsize(file_path)
print(f"File Size: {size} bytes")

print("1. Trying to read bytes...")
try:
    with open(file_path, "rb") as f:
        content = f.read()
    print(f"Read {len(content)} bytes successfully.")
except Exception as e:
    print(f"FAIL reading bytes: {e}")
    sys.exit(1)

print("2. Trying zipfile...")
import zipfile
try:
    with zipfile.ZipFile(io.BytesIO(content)) as z:
        print(f"Zip Check OK. Files: {len(z.namelist())}")
        if "word/document.xml" in z.namelist():
            print("word/document.xml found.")
             # Let's inspect the first 100 chars of xml to see if it's empty
            xml_data = z.read("word/document.xml")
            print(f"XML Preview: {xml_data[:100]}")
        else:
            print("ERROR: word/document.xml NOT found!")
except Exception as e:
    print(f"FAIL zipfile: {e}")
    sys.exit(1)

print("3. Trying python-docx...")
try:
    from docx import Document
    doc = Document(io.BytesIO(content))
    print(f"SUCCESS! Opened doc with {len(doc.paragraphs)} paragraphs.")
except Exception as e:
    print("FAIL python-docx:")
    traceback.print_exc()

print("--- DEBUG DOCX END ---")
