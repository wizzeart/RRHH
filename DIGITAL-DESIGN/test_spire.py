from spire.doc import *
from spire.doc.common import *

def test_spire():
    try:
        doc = Document()
        doc.LoadFromFile("c:\\Users\\pedrom\\Desktop\\DIGITAL_S\\work.docx") # Just a test, need a real doc
        image = doc.SaveToImages(0, ImageType.Bitmap)
        print("Success")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    # check if a work.docx exists in any folder?
    # I'll just see if imports work
    print("Imports OK")
