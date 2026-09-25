from spire.doc import *
from spire.doc.common import *
import os

def check_spire_html():
    doc = Document()
    # Create a dummy doc since I don't want to rely on an existing one yet
    section = doc.AddSection()
    para = section.AddParagraph()
    para.AppendText("Hello World from Spire.Doc")
    
    output_path = "c:\\Users\\pedrom\\Desktop\\DIGITAL_S\\test_spire.html"
    doc.SaveToFile(output_path, FileFormat.Html)
    print(f"Saved to {output_path}")

if __name__ == "__main__":
    check_spire_html()
