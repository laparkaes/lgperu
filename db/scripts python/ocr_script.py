import sys
import os
import json
import re
import cv2
import numpy as np
from pdf2image import convert_from_path
import pytesseract

def preprocess_image_for_ocr(image):
    """
    Aplica una serie de filtros a la imagen para mejorar la precisión del OCR.
    """
    # Convertir a escala de grises
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    # Aumento de contraste (normalización)
    #normalized = cv2.normalize(gray, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX, dtype=cv2.CV_8U)

    # Binarización (umbral adaptativo para diferentes condiciones de luz)
    #thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)
    
    # Reducción de ruido (filtro de mediana)
    denoised = cv2.medianBlur(gray, 3)

    return denoised

def extract_data_from_pdf(pdf_path):
    """
    Convierte un PDF a imagen, preprocesa, aplica OCR y extrae datos.
    """
    try:
        # 1. Convertir PDF a imágenes (una por página)
        # Ajusta DPI si es necesario, 500 es un buen punto de partida
        images = convert_from_path(pdf_path, 500) #500: DPI 
        
        full_text = ""
        for image in images:
            # 2. Convertir imagen a un formato compatible con OpenCV (numpy array)
            image_np = np.array(image)

            # 3. Preprocesar la imagen
            preprocessed_image = preprocess_image_for_ocr(image_np)

            # 4. Aplicar OCR a la imagen preprocesada
            text = pytesseract.image_to_string(preprocessed_image, lang='eng')
            full_text += text + "\n"

        # 5. Post-procesamiento: limpieza del texto
        # cleaned_text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', full_text).strip()
        #cleaned_text = re.sub(r'\s+', ' ', cleaned_text).strip() # Eliminar espacios en blanco extra

        # 6. Validación de la calidad del texto extraído
        #if len(cleaned_text) < 50:
        #   return json.dumps({"status": "error", "message": "El texto extraído es demasiado corto, indicando baja calidad del PDF."})

        # 7. Extraer datos específicos con expresiones regulares
        data = {
            "status": "success",
            "full_text": full_text,
            "invoice_number": None,
            "total_amount": None,
            "date": None
        }

        return json.dumps(data)
    
    except Exception as e:
        return json.dumps({"status": "error", "message": str(e)})

if __name__ == "__main__":
    if len(sys.argv) > 1:
        pdf_file = sys.argv[1]
        print(extract_data_from_pdf(pdf_file))
    else:
        print(json.dumps({"status": "error", "message": "No se proporcionó la ruta del archivo PDF."}))