import sys
import os
import json
import re
import cv2
import numpy as np
from pdf2image import convert_from_path
import pytesseract
from PIL import Image

def save_preprocessed_image(image_np, output_dir, page_number):
    """
    Guarda la imagen final preprocesada en un directorio especificado.
    La imagen se guarda como un archivo PNG.
    """
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
        #  print(f"Directorio de salida creado: {output_dir}")

    filename = f"pagina_{page_number}_preprocesada.png"
    filepath = os.path.join(output_dir, filename)
    cv2.imwrite(filepath, image_np)
    #print(f"Imagen de la pagina {page_number} guardada en: {filepath}")

def preprocess_image_for_ocr(image):
    """
    Aplica una secuencia robusta de filtros para mejorar la precision del OCR.
    Ahora utiliza umbralizacion adaptativa, adelgazamiento de texto y elimina lineas horizontales de la tabla.
    """
    # 1. Convertir a escala de grises
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    # 2. Aplicar un filtro de mediana para reducir el ruido
    denoised = cv2.medianBlur(gray, 3)

    # 3. Binarizacion con Umbral Adaptativo (ajustado)
    thresh = cv2.adaptiveThreshold(denoised, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, 
                                    cv2.THRESH_BINARY, 45, 25)
    # good values 45, 25

    return thresh

def extract_data_from_pdf(pdf_path):
    """
    Convierte un PDF a imagen, preprocesa, aplica OCR y extrae datos.
    """
    # Se usa la ruta absoluta que proporcionaste en tu ultimo mensaje
    output_dir = "C:/xampp/htdocs/llamasys/application/venv/venv_po/ocr_output"
    
    try:
        images = convert_from_path(pdf_path, 500)
        
        full_text = ""
        for i, image in enumerate(images):
            image_np = np.array(image)
            preprocessed_image = preprocess_image_for_ocr(image_np)

            save_preprocessed_image(preprocessed_image, output_dir, i + 1)
            
            # Ajuste de configuracion de Tesseract para mejorar el reconocimiento de tablas
            # '--psm 6' trata la imagen como un solo bloque uniforme de texto
            config_tesseract = r'--psm 6'
            text = pytesseract.image_to_string(preprocessed_image, lang='eng', config=config_tesseract)
            full_text += text + "\n"

        #cleaned_text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', full_text).strip()
        #cleaned_text = re.sub(r'\s+', ' ', cleaned_text).strip()

        if len(full_text) < 50:
           return json.dumps({"status": "error", "message": "El texto extraido es demasiado corto, indicando baja calidad del PDF o que el documento esta en blanco."})

        data = {
            "status": "success",
            "full_text": full_text,
            "invoice_number": None,
            "total_amount": None,
            "date": None
        }

        match_inv = re.search(r'Factura No\.?\s?([\d-]+)', full_text, re.IGNORECASE)
        if match_inv:
            data['invoice_number'] = match_inv.group(1)

        return json.dumps(data)
    
    except Exception as e:
        return json.dumps({"status": "error", "message": str(e)})

if __name__ == "__main__":
    if len(sys.argv) > 1:
        pdf_file = sys.argv[1]
        print(extract_data_from_pdf(pdf_file))
    else:
        print(json.dumps({"status": "error", "message": "No se proporciono la ruta del archivo PDF."}))
