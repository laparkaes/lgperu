<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class Scm_purchase_orders extends CI_Controller {

	public function __construct(){ 
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$this->load->helper(array('form', 'url'));
		
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		
		$list_stores = ['IMPORTACIONES RUBI S.A.', 'SAGA FALABELLA S.A.', 'CONECTA RETAIL S.A.', 'REPRESENTACIONES VARGAS S.A.', 'REPRESENTACIONES VARGAS S.A. (Bold Format)', 'INTEGRA RETAIL S.A.C.',
						'TIENDAS PERUANAS S.A. - OESCHLE', 'TIENDAS PERUANAS S.A. - OESCHLE (Yellow)', 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA - PLAZA VEA', 'HOMECENTERS PERUANOS S.A. - PROMART',
						'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.', 'HIPERMERCADOS TOTTUS S.A.'];
		$data = [
			"stores"		=> $list_stores,
			"main" 			=> "module/scm_purchase_orders/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function clean_text($text) {
		
        $text = preg_replace('/[\p{C}&&[^\n\r\t]]/u', '', $text);
        $text = str_replace(["\r\n", "\r", "\n"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        // Ajuste para unir líneas: solo si es alfanumérico o puntuación común
        $text = preg_replace('/([a-zA-Z0-9.,])\s*\n\s*([a-zA-Z0-9.,])/', '$1 $2', $text);
        $text = trim($text);
        return $text;
    }
	
	public function date_convert($date) {
    // Intentamos convertir con la lógica del valor numérico (excel date)
		if (is_numeric($date)) {
			// Si es un número, es probable que sea una fecha de Excel (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número, intentamos convertir con la lógica de fecha en formato mm/dd/yyyy
		$aux = explode("/", $date);
		if (count($aux) == 3) {
			// Verificamos que la fecha esté en formato mm/dd/yyyy
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}
		
		// Si la fecha no está en un formato esperado, devolvemos null
		return null;
	}
	
	public function models_sales_order($model_order){
		if (strpos($model_order, '.') !== false) {
			return $model_order;
		} else{
			$models = $this->gen_m->filter_select('lgepr_sales_order', false, 'model', ['model LIKE' => "%{$model_order}%"]);
			if (!empty($models)) return $models[0]->model;
			else {
				$model_close = $this->gen_m->filter_select('lgepr_closed_order', false, 'model', ['model LIKE' => "%{$model_order}%"]);
				if (!empty($model_close)) return $model_close[0]->model;
				else {
					$model_stock = $this->gen_m->filter_select('lgepr_stock', false, 'model', ['model LIKE' => "%{$model_order}%"]);
					if (!empty($model_stock)) return $model_close[0]->model;
					else return $model_order;
				}			
			}			
		}
	}
	
	public function models_sales_order_integra($model_order){
		if (strpos($model_order, '.') !== false) {
			return $model_order;
		} else{
			$models = $this->gen_m->filter_select('lgepr_sales_order', false, 'model', ['model LIKE' => "%{$model_order}%"]);
			if (!empty($models)) return $models[0]->model;
			else {
				$model_close = $this->gen_m->filter_select('lgepr_closed_order', false, 'model', ['model LIKE' => "%{$model_order}%"]);
				if (!empty($model_close)) return $model_close[0]->model;
				else {
					$model_stock = $this->gen_m->filter_select('lgepr_stock', false, 'model', ['model LIKE' => "%{$model_order}%"]);
					if (!empty($model_stock)) return $model_stock[0]->model;
					else return 'Not Found';
				}			
			}			
		}
	}
	
	public function data_initialize(){
		$data = [
					'customer_po_no' 		=> '',
					'ship_to' 				=> '',
					'currency'				=> '',
					'request_arrival_date' 	=> '',
					//'modelo' => 'N/A',
					//'qty' => 'N/A',
					//'unit_selling_price' => 'N/A',

					'warehouse' 			=> '',
					'payterm' 				=> '',
					'shipping_remark' 		=> '',
					'invoice_remark'		=> '',
					'request_arrival_date' 	=> '',
					'customer_po_date' 		=> '',
					
					'h_flag'  				=> '',
					'op_code' 				=> '',
					'country' 				=> '',
					'postal_code' 			=> '',
					'address1' 				=> '',
					'address2' 				=> '',
					'address3' 				=> '',
					'address4' 				=> '',
					
					'city'  				=> '',
					'state' 				=> '',
					'province'  			=> '',
					'county' 				=> '',
					'consumer_name' 		=> '',
					'comsumer_phono_no' 	=> '',
					'receiver_name' 		=> '',
					'receiver_phono_no' 	=> '',
					'freight_charge' 		=> '',
					'freight_term'	 		=> '',
					'price_condition' 		=> '',
					'picking_remark' 		=> '',
					'shipping_method' 		=> '',
					'items'   				=> [],
					'results' 				=> ''
					
					
				];
		return $data;
	}
	
	public function extract_rubi($text){ // ok rubi pdf
		//echo '<pre>'; print_r($text);
		$bill_to_code = 'PE000820001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		$data = $this->data_initialize();
		$all_data = [];
        // --- 1. Extracción de Número de Boleta / Orden de Compra y Fecha ---
        if (preg_match('/ORDEN\s+DE\s+COMPRA\s*.*?(\d{7})/is', $text, $matches)) {
             $data['customer_po_no'] = $matches[1];
        } elseif (preg_match('/(?:BOLETA|FACTURA)\s*(?:N°|No\.?|Num\.?|:)?\s*([A-Z0-9-]+)/i', $text, $matches)) {
            $data['customer_po_no'] = $matches[1];
        }

        if (preg_match('/Fecha\s*:\s*(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{2,4})/', $text, $matches)) {
            $date_extract = $matches[1];
        } elseif (preg_match('/\b(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4})\b/', $text, $matches)) {
            $date_extract = $matches[1];
        }

		$data['request_arrival_date'] = DateTime::createFromFormat('d/m/Y', $date_extract)->format('Y-m-d');
		$data['request_arrival_date'] = $timestamp = strtotime($data['request_arrival_date']);
		$data['request_arrival_date'] = date("Ymt", $data['request_arrival_date']);
		
		
		$data['customer_po_date'] = Date('Ymd');
		
		// Ship to
		if (preg_match('/IMPORTACIONES\s+RUBI\s+S.A.\s+20298463165\s+(.*?)\s+imp_rubi@importacionesrubi.com.pe/', $text, $matches)) {
			//echo '<pre>'; print_r($matches);	
			$address = $matches[1];
			$aux = explode(' ', $address, 3);
			//echo '<pre>'; print_r($aux);
			$ship_to = $this->gen_m->filter_select('scm_ship_to2', false, ['ship_to_code'],['bill_to_code' => $bill_to_code, 'address LIKE' => "%{$aux[1]}%"]);
			//echo '<pre>'; print_r($ship_to);
			$data['ship_to'] = $ship_to[0]->ship_to_code;
		}
		
		// Payterm 
		
		if (preg_match('/CREDITO (\d+) DIAS/', $text, $matches)) {
             //echo '<pre>'; print_r($matches);
			 $credit = $matches[1];
			 $data['payterm'] = $payterm[$credit];
        }
		
		// Warehouse
		
		// $warehouse = $this->gen_m->filter_select('scm_ship_to2', false, ['warehouse'], ['bill_to_code' => $bill_to_code]);
		// //echo '<pre>'; print_r($warehouse);
		// $data['warehouse'] = $warehouse[0]->warehouse;
		$data['warehouse'] = null;
		
        // --- 4. Extracción de Ítems (Tabla) - RE-REVISADO Y OPTIMIZADO ---
        if (preg_match('/Telfs\.:\s*[\d\s\/\-]+/is', $text, $start_match, PREG_OFFSET_CAPTURE)) {
            $current_pos = $start_match[0][1] + strlen($start_match[0][0]);
            error_log("Tabla: Inicio de extracción de ítems después de 'Telfs.:'.");

            $quantity_pattern = '\s*(\d{1,5}(?:\.\d{1,2})?)';
            $value_pattern = '\s*(\d{1,5}(?:,\d{3})*(?:\.\d{1,2})?|\d{1,5}(?:\.\d{1,2})?)';
            $model_pattern = '\s*(\S+)';

            // PATRÓN MODIFICADO AQUÍ para ser más flexible al final de la coincidencia
            $item_line_pattern = '/(.*?Lg)' . $quantity_pattern . $value_pattern . $value_pattern . $model_pattern . '(?=\s*(?:SUBTOTAL|TOTAL|IMPORTE TOTAL|[\p{L}\p{N}\s\.\-\,\/\(\)\[\]_#&%*@+\']{1,200}?Lg|.*?\n|\z))/is';
            // CAMBIO: `.*?\n` añadido al lookahead. Esto permite que el ítem termine si hay una nueva línea
            // que no necesariamente es el inicio de otro ítem o un total, lo cual es común para la última fila.

            while (preg_match($item_line_pattern, $text, $item_matches, PREG_OFFSET_CAPTURE, $current_pos)) {
                $description = trim($item_matches[1][0]);
                $quantity = $item_matches[2][0];
                $unit_value = $item_matches[3][0];
                $total_value = $item_matches[4][0];
                //$model = $item_matches[5][0];
				$model = $this->models_sales_order($item_matches[5][0]);
                $data['items'][] = [
                    'qty' => $this->normalize_number($quantity),
                    'modelo' => $model,
                    'currency' => 'PEN',
                    'unit_selling_price' => $this->normalize_number($unit_value),
                ];
                error_log("Ítem extraído: " . json_encode($data['items'][count($data['items'])-1]));

                $current_pos = $item_matches[0][1] + strlen($item_matches[0][0]);

                if (strlen(trim(substr($text, $current_pos))) < 10 && count($data['items']) > 0) {
                     error_log("El texto restante es demasiado corto para encontrar más ítems, deteniendo la extracción.");
                     break;
                }
            }
            error_log("Terminó la extracción de ítems. Texto restante desde current_pos para depuración: '" . substr($text, $current_pos, 200) . "'");

        } else {
            error_log("No se encontró el patrón inicial 'Telfs.:' para comenzar la extracción de ítems de la tabla.");
        }
		
        // --- 5. Extracción del Monto Total General (interno, no en Excel) ---
        // if (preg_match('/(?:TOTAL|Total a Pagar|Monto Total|Importe Total):\s*[$€S]?\s*([\d\.,]+)/i', $text, $matches)) {
            // $data['total'] = floatval(str_replace(['.', ','], ['', '.'], $matches[1]));
        // } elseif (empty($data['total']) && !empty($data['items'])) {
            // $sum_total = 0;
            // foreach ($data['items'] as $item) {
                // $sum_total += $item['total value'];
            // }
            // $data['total'] = $sum_total;
        // }

        //error_log("Datos extraídos: " . print_r($data, true));
		$all_data[] = $data;
        return $all_data;
	}
	
	public function extract_saga($file_paths) { // ok saga txt
		$bill_to_code = 'PE000968001B';
		// Mantenemos esta lista de términos de pago
		$payterm = [
			0 => 'N0000FSN1000',
			10 => 'N0010FSN1708',
			30 => 'N0030FSN1006',
			45 => 'N0045FSN1008',
			60 => 'N0060FSN1010',
			90 => 'N0090FSN1012'
		];
		$data = $this->data_initialize(); // Asegúrate de que esto inicializa todas las claves necesarias
		$all_data = [];
		error_log("Aplicando lógica de extracción para SAGA FALABELLA S.A. (Múltiples TXT).");

		if (!is_array($file_paths) || count($file_paths) !== 2) {
			error_log("Error: Se esperan exactamente dos rutas de archivo TXT válidas para SAGA FALABELLA S.A.");
			return $data; // Retorna datos iniciales si no hay 2 archivos
		}

		// --- Paso 1: Procesar el primer archivo (EOC - attach_txt1) ---
		$file1_path = $file_paths[0]; 
		$items_temp_storage = []; // Almacenará los ítems con una clave para fusión
		$order_header_data = []; // Para almacenar datos a nivel de cabecera de la orden

		if (!file_exists($file1_path)) {
			error_log("Error: Primer archivo TXT (EOC) no válido o no encontrado: " . $file1_path);
			return $data;
		}

		$lines1 = file($file1_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (empty($lines1)) {
			error_log("Error: Primer archivo TXT (EOC) vacío: " . $file1_path);
			return $data;
		}

		$headers1 = array_map('trim', explode('|', array_shift($lines1)));
		error_log("SAGA FALABELLA S.A. - Encabezados Archivo EOC: " . implode(', ', $headers1));

		foreach ($lines1 as $line1) {
			$values1 = array_map('trim', explode('|', $line1));
			if (count($headers1) !== count($values1)) {
				error_log("Advertencia: Columnas no coinciden en Archivo EOC, línea: " . $line1);
				continue;
			}
			$item_raw1 = array_combine($headers1, $values1);

			// --- Extracción de datos del primer TXT (EOC) para la cabecera y ítems ---
			$customer_po_no_current = $item_raw1['NRO_OC'] ?? $item_raw1['NRO_OD'] ?? '';
			
			// Asumiendo que NRO_OC es consistente para toda la orden en este archivo
			if (empty($order_header_data['customer_po_no'])) {
				$order_header_data['customer_po_no'] = $customer_po_no_current;
			}

			// MONEDA_COSTO para currency
			$moneda_costo = $item_raw1['MONEDA_COSTO'] ?? '';
			if (strpos(strtoupper($moneda_costo), 'S/.') !== false) {
				$order_header_data['currency'] = 'PEN';
			} else {
				$order_header_data['currency'] = 'USD'; // O el valor por defecto que consideres
			}

			//FECHA_HASTA para request_arrival_date (último día del mes)
			$fecha_date_raw = $item_raw1['FECHA_HASTA'] ?? '';
			if (!empty($fecha_date_raw)) {
				$date_obj = DateTime::createFromFormat('d/m/Y', $fecha_date_raw);
				if ($date_obj) {
					$order_header_data['request_arrival_date'] = $date_obj->format('Ymd'); // YYYYMMDD (último día) // fecha hasta
				} else {
					error_log("Advertencia: Formato de fecha_date_raw incorrecto: " . $fecha_date_raw);
				}
			}
	
			// customer_po_date será la fecha actual
			$order_header_data['customer_po_date'] = date('Ymd'); 

			// DIAS_PAGO para payterm
			$dias_pago = $item_raw1['DIAS_PAGO'] ?? null;
			if (isset($payterm[$dias_pago])) {
				$order_header_data['payterm'] = $payterm[$dias_pago];
			} else {
				error_log("Advertencia: DIAS_PAGO no encontrado en lista de términos de pago: " . $dias_pago);
				// Puedes asignar un valor por defecto o dejarlo vacío si no se encuentra
				$order_header_data['payterm'] = ''; 
			}

			// Define la CLAVE ÚNICA para fusionar (NRO_OC + MODELO/SKU)
			$model_or_sku = $item_raw1['MODELO'] ?? $item_raw1['SKU'] ?? ''; 
			$unique_key = $customer_po_no_current . '_' . $model_or_sku;

			if (!empty($unique_key)) {
				$items_temp_storage[$unique_key] = $item_raw1;
			} else {
				error_log("Advertencia: No se pudo generar una clave única para línea en Archivo EOC: " . $line1);
			}
		}
		error_log("INFO: " . count($items_temp_storage) . " ítems procesados desde el archivo EOC.");

		// --- Paso 2: Procesar el segundo archivo (EOD - attach_txt2) y fusionar la información ---
		$file2_path = $file_paths[1]; 
		$local_from_eod = ''; // Para almacenar el valor LOCAL del segundo archivo

		if (!file_exists($file2_path)) {
			error_log("Error: Segundo archivo TXT (EOD) no válido o no encontrado: " . $file2_path);
			return $data; // Mandatorio, así que retorna
		}

		$lines2 = file($file2_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (empty($lines2)) {
			error_log("Error: Segundo archivo TXT (EOD) vacío: " . $file2_path);
			return $data;
		}

		$headers2 = array_map('trim', explode('|', array_shift($lines2)));
		error_log("SAGA FALABELLA S.A. - Encabezados Archivo EOD: " . implode(', ', $headers2));

		foreach ($lines2 as $line2) {
			$values2 = array_map('trim', explode('|', $line2));
			if (count($headers2) !== count($values2)) {
				error_log("Advertencia: Columnas no coinciden en Archivo EOD, línea: " . $line2);
				continue;
			}
			$item_raw2 = array_combine($headers2, $values2);

			// Extraer LOCAL del segundo TXT (EOD)
			$local_from_eod = $item_raw2['LOCAL'] ?? ''; 
			if (!empty($local_from_eod)) {
				// Asumimos que el LOCAL es el mismo para toda la orden en este archivo.
				// Si el archivo EOD tiene múltiples LOCALES, necesitarás una estrategia diferente.
				// Por ahora, tomamos el primero que encontremos.
				break; // Salir del bucle una vez que encontramos el LOCAL
			}

			// Genera la misma CLAVE ÚNICA para fusionar (si es necesario fusionar datos de ítems del EOD)
			// Si el EOD solo tiene el campo LOCAL y no hay datos de ítems que fusionar,
			// esta parte del bucle foreach para fusionar ítems podría no ser estrictamente necesaria.
			// Pero la mantenemos para completitud si más campos de EOD se añaden en el futuro.
			$order_num_2 = $item_raw2['NRO_OC'] ?? ''; 
			$model_or_sku_2 = $item_raw2['MODELO'] ?? $item_raw2['SKU'] ?? ''; 
			$unique_key_2 = $order_num_2 . '_' . $model_or_sku_2;

			if (isset($items_temp_storage[$unique_key_2])) {
				$items_temp_storage[$unique_key_2] = array_merge($items_temp_storage[$unique_key_2], $item_raw2);
			} else {
				error_log("Advertencia: Clave única no encontrada en almacenamiento para Archivo EOD, línea: " . $line2 . " Clave: " . $unique_key_2);
			}
		}
		error_log("INFO: Segundo archivo (EOD) procesado. LOCAL encontrado: " . $local_from_eod);

		// --- Paso 3: Construir el array de datos final con la información fusionada ---
		
		// Asigna los datos de cabecera a $data
		$data['customer_po_no'] = $order_header_data['customer_po_no'] ?? 'N/A';
		$data['request_arrival_date'] = $order_header_data['request_arrival_date'] ?? 'N/A';
		$data['customer_po_date'] = $order_header_data['customer_po_date'] ?? date('Ymd'); // Asegura la fecha actual si no se asignó antes
		$data['currency'] = $order_header_data['currency'] ?? 'PEN';
		$data['payterm'] = $order_header_data['payterm'] ?? ''; // Asigna el término de pago

		// Búsqueda del ship_to_code usando el LOCAL del EOD
		$data['ship_to'] = 'N/A'; // Valor por defecto
		if (!empty($local_from_eod)) {
			// Divide el valor de LOCAL en palabras para buscar
			$local_parts = explode(' ', $local_from_eod);
			$found_ship_to = false;
			foreach ($local_parts as $part) {
				$part = trim($part);
				if (empty($part)) continue; // Saltar partes vacías

				// Ajusta la consulta LIKE para buscar la palabra en cualquier parte de la dirección
				// Ojo: Si usas CodeIgniter 3 y no tienes query builder, podrías necesitar una consulta directa.
				// $ship_to = $query->row();

				// Usando tu función filter_select (asumo que es para CI3 Query Builder o similar)
				// Necesitas un "LIKE" adecuado. Algunas versiones de filter_select permiten esto.
				// Si $aux[1] es solo una palabra, tu ejemplo $aux[1] está bien, pero aquí la estamos construyendo.
				
				// Revisa si tu gen_m->filter_select soporta la sintaxis LIKE de esta forma o necesita ser adaptada
				// Es crucial que esta consulta funcione. Si "address LIKE %{$aux[1]}%" es del ejemplo, es lo que usaremos.
				
				// Adaptando tu ejemplo de búsqueda
				$ship_to_results = $this->gen_m->filter_select('scm_ship_to2', false, ['ship_to_code'], ['bill_to_code' => $bill_to_code, "address LIKE '%" . $this->db->escape_like_str($part) . "%'"]);

				if (!empty($ship_to_results)) {
					$data['ship_to'] = $ship_to_results[0]->ship_to_code;
					$found_ship_to = true;
					error_log("INFO: ship_to_code encontrado para LOCAL '{$local_from_eod}' usando parte '{$part}': " . $data['ship_to']);
					break; // Una vez que encuentras uno, puedes salir
				}
			}
			if (!$found_ship_to) {
				error_log("Advertencia: No se encontró ship_to_code para LOCAL: " . $local_from_eod);
			}
		} else {
			error_log("Advertencia: El campo LOCAL del archivo EOD está vacío.");
		}

		foreach ($items_temp_storage as $item_fusionado) {
			// Mapea los campos fusionados a la estructura de tu salida
			$quantity_saga = $item_fusionado['CANTIDAD_PROD'] ?? $item_fusionado['UNIDADES'] ?? '0';
			//$model_saga = $item_fusionado['MODELO'] ?? ''; 
			$model_saga = $this->models_sales_order($item_fusionado['MODELO']);
			$description_saga = $item_fusionado['DESCRIPCION_LARGA'] ?? 'N/A'; // Del archivo EOD (fusionado)
			$unit_value_saga = $item_fusionado['COSTO_UNI'] ?? ''; // Del archivo EOD (fusionado)
			$total_value_saga = $item_fusionado['IMPORTE_TOTAL_LINEA'] ?? '0'; 

			$data['items'][] = [
				'qty' => $this->normalize_number($quantity_saga),
				'modelo' => $model_saga,
				'description' => $description_saga, 
				'unit_selling_price' =>  str_replace([','], ['.'], $unit_value_saga),
				'currency' => $data['currency'], // Toma la moneda de la cabecera
				'total_value' => $this->normalize_number($total_value_saga) 
			];
		}

		error_log("INFO: Extracción de SAGA FALABELLA S.A. completada. Ítems extraídos: " . count($data['items']));
		$all_data[] = $data;
		return $all_data;
	}
	
	public function extract_credivargas($text){ // ok credivargas pdf | only simple format
		$text = preg_replace('/[\r\n]+/', '', $text);
		$bill_to_code = 'PE000952001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		//echo '<pre>'; print_r($text);	
		$all_data = [];
		$date_dd_mm_yyyy = 0;		
		$data = [
            // Inicializa todos los campos que tu generate_excel espera
            'customer_po_no' => '',
            'ship_to' => '',
            'request_arrival_date' => '',
            'customer_po_date' => '',
            'address2' => '',
            'address4' => '',
            'comsumer_phono_no' => '',
            'receiver_phono_no' => '',
            'freight_charge' => '',
            'freight_term' => '',
            'price_condition' => '',
            'picking_remark' => '',
            'shipping_method' => '',
            'results' => '',
            'items' => []
        ];
		//echo '<br>'; print_r($text);
        // --- EXTRACCION DE DATOS DE CABECERA ---

        // 1. Fecha de Emisión (ahora busca "Fecha Preparación:")
        // Usamos una RegEx que busca "Fecha Preparación:" y captura la fecha en formato DD-MM-YYYY.
        if (preg_match('/Fecha\s+.*?:?\s*(\d{2}-\d{2}-\d{4})/', $text, $matches)) {
			$date_dd_mm_yyyy = $matches[1];			
			// Convertir de DD-MM-YYYY a un objeto DateTime.
			$date_obj = DateTime::createFromFormat('d-m-Y', $date_dd_mm_yyyy);

			if ($date_obj) {
				// Formatear el objeto de fecha a YYYYMMDD.
				$data['request_arrival_date'] = $date_obj->format('t');
				$last_day_date_obj = $date_obj->setDate($date_obj->format('Y'), $date_obj->format('m'), $data['request_arrival_date']);
				$data['request_arrival_date'] = $last_day_date_obj->format('Ymd');   
			}
        }		
		
		$data['customer_po_date'] = Date('Ymd');
		
        // 2. Número de Boleta (Luego de "Orden de Compra + " son números)
        // Usamos una RegEx para encontrar "Orden de Compra" seguido de uno o más espacios, y luego capturamos los dígitos.
        if (preg_match('/Orden de Compra\s*[\+\#\%\*\s]+\s*([\w-]+)\s*/mi', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
            $data['customer_po_no'] = $matches[1]; // El número de la orden de compra
            error_log("Extraído Número de Boleta/Orden de Compra: " . $data['customer_po_no']);
        }
        
		// Payterm Calculate
		if (preg_match('/Forma de Pago:(.*?)\s*Dias/i', $text, $matches)) {
             //echo '<pre>'; print_r($matches);
			 $extracted_payment_term = trim($matches[1]);
			 if (preg_match('/\d+/', $extracted_payment_term, $matches_number)) {
				 $data['payterm'] = $payterm[$matches_number[0]] ?? null;
			 }
        }
		
		//	Find transport
		$regex = '/Observaciones:\s*.*?(TRANSMAURI|LEYVA|ZAVALA)/i';
		if (preg_match($regex, $text, $matches)) {
             //echo '<pre>'; print_r($matches);
			 $transport = trim($matches[1]);
        } else $transport = "VENTANILLA";
		
		// Ship To Calculate
		
		$extracted_street = null;
		// Expresión regular para capturar el nombre de la calle.
		// Ignora las palabras "01 PCL", que son variables.
		$regex = '/(Calle|Av\.|Jr\.)\s+([\w]+\s+[\w]+)/i';
		if (preg_match_all($regex, $text, $matches)) {
			// La calle capturada estará en el segundo elemento de $matches
			//echo '<pre>'; print_r($matches);
			$extracted_street = end($matches[2]);
			if (count($matches[2]) > 1) {
				//$extracted_street = trim($matches[1]);
				$ship_to = $this->gen_m->filter_select("scm_ship_to2", false, "ship_to_code", ["bill_to_code" => $bill_to_code, "address LIKE" => "%{$extracted_street}%", "transport LIKE" => "%{$transport}%"]);
				//echo '<pre>'; print_r($ship_to);
				$data['ship_to'] = $ship_to[0]->ship_to_code ?? null;
			}
		} 
		if (preg_match('/Almacén Tienda\s*.*?(Centenario)/i', $text, $matches)){
			$address = $matches[1];
			$ship_to = $this->gen_m->filter_select("scm_ship_to2", false, "ship_to_code", ["bill_to_code" => $bill_to_code, "address LIKE" => "%{$address}%", "transport LIKE" => "%{$transport}%"]);
			//echo '<pre>'; print_r($ship_to);
			$data['ship_to'] = $ship_to[0]->ship_to_code ?? null;			
		}
		
		
        // --- EXTRACCION DE ÍTEMS ---
		$data['items'] = []; // Inicializamos el array de ítems fuera del condicional
		$lines = explode("\n", $text); // Dividimos todo el texto OCR en líneas
		
		// Patrón para encontrar una línea que contenga un modelo entre corchetes
		// Usaremos este patrón para identificar la línea clave.
		$model_line_pattern = '/\[(?!Unidad\])[^\s\]]+\]/';
		
		$found_item_line = '';
		
		foreach ($lines as $line_num => $line) {
			if (preg_match($model_line_pattern, $line)) {
				//echo '<pre>'; print_r($line);
				$found_item_line = trim($line);
				error_log("Línea con modelo(s) detectada (Línea " . ($line_num + 1) . "): " . $found_item_line);
				//break;
			//}
		//}

				if (empty($found_item_line)) {
					error_log("ERROR: No se encontró ninguna línea que contenga un patrón de modelo de ítem.");
					return $data;
				}
			
				$item_pattern = '/' .    // Hace el prefijo opcional
						// Captura el Modelo y los valores numéricos
						// Usamos '.*?' para saltar de forma no codiciosa cualquier texto/número que no queremos capturar.
						'\[([^\s\]]+)\]' .            // **Captura 1: El Modelo** (ej. WT13BPBK) - sin espacios internos
						'.*?' .                       // Cualquier carácter, no codicioso, hasta el primer número
						'(\d{1,3}(?:[,.\s]\d{3})*(?:[.,]\d+)?)\s*' . // **Captura 2: Primer valor numérico (Cantidad)**
						'(?:\|\s*)?' .                // Pipe opcional (si existe)
						'(\d{1,3}(?:[,.\s]\d{3})*(?:[.,]\d+)?)\s*' . // **Captura 3: Segundo valor numérico (Precio Unitario)**
						'(?:\|\s*)?' .                // Pipe opcional (si existe)
						'(\d{1,3}(?:[,.\s]\d{3})*(?:[.,]\d+)?)\s*' . // **Captura 4: Tercer valor numérico (Precio Total)**
						'/xi';                        // 'x' para espacios en el patrón, 'i' para case-insensitive

				///\[([^\s\]]+)\]/'
				if (preg_match_all($item_pattern, $found_item_line, $matches, PREG_SET_ORDER)) {
					//echo '<pre>'; print_r($matches);
					error_log("Total de ítems encontrados en la línea: " . count($matches));
					foreach ($matches as $item_match) {
						$model_aux = trim($item_match[1]);          // Captura 1: El modelo
						$modelo = $this->models_sales_order($model_aux) ?? '';
						$qty = trim($item_match[2]);          // Captura 2: Primer valor numérico (ej. Cantidad)
						$unit_selling_price = trim($item_match[3]);          // Captura 3: Segundo valor numérico (ej. Precio Unitario)
						$valor3 = trim($item_match[4]);          // Captura 4: Tercer valor numérico (ej. Precio Total)

						if ($modelo === 'Unidad') continue;
						else {
						// Aquí guardamos los valores directamente sin la descripción general por ahora.
						// Asume que la normalización de números ($this->normalize_number) se hará después.
							$data['items'][] = [
								'modelo' => $modelo,
								'qty' => $this->normalize_number($qty),
								'unit_selling_price' => $this->normalize_number($unit_selling_price),
								'valor3' => $this->normalize_number($valor3),
								// Puedes añadir otros campos vacíos o por defecto si tu estructura final los necesita
								'currency' => 'PEN',
								'description' => '', // La dejaremos vacía por ahora, o puedes decidir no incluirla
								//'qty' => 0, // Estos los mapearás de qty, unit_selling_price, valor3 después
								//'unit_selling_price' => 0,
								// ... otros campos
							];
						}
						error_log("Extraído ítem: Modelo='" . $modelo . "', Valores: [{$qty}, {$unit_selling_price}, {$valor3}]");
					}
				} else {
					error_log("No se encontraron patrones de ítems válidos en la línea detectada.");
				}
        // Fin de extracción de ítems
			}
		}
		$all_data[] = $data;
        return $all_data;
	}
	
	public function extract_credivargas_bold_format($text){ // ok | credivargas bold format (falta ajustar nuevo regex)
		$text = preg_replace('/[\r\n]+/', '', $text);
		$bill_to_code = 'PE000952001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		//echo '<pre>'; print_r($text);	
		$date_dd_mm_yyyy = 0;		
		$all_data = [];
		$data = [
            // Inicializa todos los campos que tu generate_excel espera
            'customer_po_no' => '',
            'ship_to' => '',
            'request_arrival_date' => '',
            'customer_po_date' => '',
            'address2' => '',
            'address4' => '',
            'comsumer_phono_no' => '',
            'receiver_phono_no' => '',
            'freight_charge' => '',
            'freight_term' => '',
            'price_condition' => '',
            'picking_remark' => '',
            'shipping_method' => '',
            'results' => '',
            'items' => []
        ];
		//echo '<br>'; print_r($text);
        // --- EXTRACCION DE DATOS DE CABECERA ---

        // 1. Fecha de Emisión (ahora busca "Fecha Preparación:")
        // Usamos una RegEx que busca "Fecha Preparación:" y captura la fecha en formato DD-MM-YYYY.
        if (preg_match('/Fecha\s+.*?:?\s*(\d{2}-\d{2}-\d{4})/', $text, $matches)) {
			$date_dd_mm_yyyy = $matches[1];			
			// Convertir de DD-MM-YYYY a un objeto DateTime.
			$date_obj = DateTime::createFromFormat('d-m-Y', $date_dd_mm_yyyy);

			if ($date_obj) {
				// Formatear el objeto de fecha a YYYYMMDD.
				$data['request_arrival_date'] = $date_obj->format('t');
				$last_day_date_obj = $date_obj->setDate($date_obj->format('Y'), $date_obj->format('m'), $data['request_arrival_date']);
				$data['request_arrival_date'] = $last_day_date_obj->format('Ymd');   
			}
        }		
		
		$data['customer_po_date'] = Date('Ymd');
		
        // 2. Número de Boleta (Luego de "Orden de Compra + " son números)
        // Usamos una RegEx para encontrar "Orden de Compra" seguido de uno o más espacios, y luego capturamos los dígitos.
        if (preg_match('/Orden de Compra\s*[\+\#\%\*\s]+\s*([\w-]+)\s*/mi', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
            $data['customer_po_no'] = $matches[1]; // El número de la orden de compra
            error_log("Extraído Número de Boleta/Orden de Compra: " . $data['customer_po_no']);
        }
        
		// Payterm Calculate
		if (preg_match('/Forma de Pago:(.*?)\s*(Observaciones|Obs\.)/i', $text, $matches)) {
             //echo '<pre>'; print_r($matches);
			 $extracted_payment_term = trim($matches[1]);
			 if (preg_match('/\d+/', $extracted_payment_term, $matches_number)) {
				 $data['payterm'] = $payterm[$matches_number[0]];
			 }
        }
		
		//	Find transport
		$regex = '/(Observaciones|Obs.|Obs:.):\s*.*?(TRANSMAURI|LEYVA|ZAVALA)/i';
		if (preg_match($regex, $text, $matches)) {
			//echo '<pre>'; print_r($matches);
			$transport = trim($matches[2]);
        } else $transport = "VENTANILLA";
		
		// Ship To Calculate
		
		
		// ---------------------------------------

		$last_match = null;

		// --- Primera Condición: Buscar una direccion que termine en N°|n° ---
		// Captura el texto entre la palabra clave (Av., Calle, Jr.) y el número
		// El patrón busca la palabra clave, luego cualquier texto, luego N° o n°, y el número
		$regex_full_address = '/(?:Calle|Av\.|Jr\.)\s*(.*?)\s*(?:N°|n°)\s*([\d-]+)/i';
		$matches = [];
		if (preg_match_all($regex_full_address, $text, $matches)) {
			// Si se encuentra una coincidencia, combina el nombre de la calle y el número
			//echo '<pre>'; print_r($matches);
			$last_match = trim(end($matches[1])) . ' ' . trim(end($matches[2]));
			$ship_to = $this->gen_m->filter_select("scm_ship_to2", false, "ship_to_code", ["bill_to_code" => $bill_to_code, "address LIKE" => "%{$last_match}%", "transport LIKE" => "%{$transport}%"]);
			$data['ship_to'] = $ship_to[0]->ship_to_code ?? null;
			//echo '<pre>'; print_r($last_match);
			//echo "Coincidencia encontrada (con número): " . $last_match . "\n";
		} else {
			// --- Segunda Condición (Alternativa): Si la primera falla, buscar 2 palabras despues ---
			// Este es el regex que proporcionaste en tu solicitud
			$regex_two_words = '/(?:Calle|Av\.|Jr\.)\s+([\w]+\s+[\w]+)/i';
			$matches_fallback = [];
			if (preg_match_all($regex_two_words, $text, $matches_fallback)) {
				//echo '<pre>'; print_r($matches_fallback);
				// En este caso, tomamos la ultima coincidencia encontrada
				$last_match = end($matches_fallback[1]);
				$ship_to = $this->gen_m->filter_select("scm_ship_to2", false, "ship_to_code", ["bill_to_code" => $bill_to_code, "address LIKE" => "%{$last_match}%", "transport LIKE" => "%{$transport}%"]);
				$data['ship_to'] = $ship_to[0]->ship_to_code ?? null;
				//echo '<pre>'; print_r($last_match);
				//echo "Coincidencia encontrada (alternativa): " . $last_match . "\n";
			}
		}

		if (preg_match('/Almacén Tienda\s*.*?(Centenario)/i', $text, $matches)){
			$address = $matches[1];
			$ship_to = $this->gen_m->filter_select("scm_ship_to2", false, "ship_to_code", ["bill_to_code" => $bill_to_code, "address LIKE" => "%{$address}%", "transport LIKE" => "%{$transport}%"]);
			//echo '<pre>'; print_r($ship_to);
			$data['ship_to'] = $ship_to[0]->ship_to_code ?? null;			
		}
		
        // --- EXTRACCION DE ÍTEMS ---
		$data['items'] = []; // Inicializamos el array de ítems fuera del condicional
		$lines = explode("\n", $text); // Dividimos todo el texto OCR en líneas
		
		// Patrón para encontrar una línea que contenga un modelo entre corchetes
		// Usaremos este patrón para identificar la línea clave.
		$model_line_pattern = '/\[(?!Unidad\])[^\s\]]+\]/';
		
		$found_item_line = '';
		
		foreach ($lines as $line_num => $line) {
			if (preg_match($model_line_pattern, $line)) {
				//echo '<pre>'; print_r($line);
				$found_item_line = trim($line);
				error_log("Línea con modelo(s) detectada (Línea " . ($line_num + 1) . "): " . $found_item_line);
				//break;
			//}
		//}

				if (empty($found_item_line)) {
					error_log("ERROR: No se encontró ninguna línea que contenga un patrón de modelo de ítem.");
					return $data;
				}
			
				$item_pattern = '/' .    // Hace el prefijo opcional
						// Captura el Modelo y los valores numéricos
						// Usamos '.*?' para saltar de forma no codiciosa cualquier texto/número que no queremos capturar.
						'\[([^\s\]]+)\]' .            // **Captura 1: El Modelo** (ej. WT13BPBK) - sin espacios internos
						'.*?' .                       // Cualquier carácter, no codicioso, hasta el primer número
						'(\d{1,3}(?:[,.\s]\d{3})*(?:[.,]\d+)?)\s*' . // **Captura 2: Primer valor numérico (Cantidad)**
						'(?:\|\s*)?' .                // Pipe opcional (si existe)
						'(\d{1,3}(?:[,.\s]\d{3})*(?:[.,]\d+)?)\s*' . // **Captura 3: Segundo valor numérico (Precio Unitario)**
						'(?:\|\s*)?' .                // Pipe opcional (si existe)
						'(\d{1,3}(?:[,.\s]\d{3})*(?:[.,]\d+)?)\s*' . // **Captura 4: Tercer valor numérico (Precio Total)**
						'/xi';                        // 'x' para espacios en el patrón, 'i' para case-insensitive

				///\[([^\s\]]+)\]/'
				if (preg_match_all($item_pattern, $found_item_line, $matches, PREG_SET_ORDER)) {
					//echo '<pre>'; print_r($matches);
					error_log("Total de ítems encontrados en la línea: " . count($matches));
					foreach ($matches as $item_match) {
						$model_aux = trim($item_match[1]);          // Captura 1: El modelo
						$modelo = $this->models_sales_order($model_aux) ?? '';
						$qty = trim($item_match[2]);          // Captura 2: Primer valor numérico (ej. Cantidad)
						$unit_selling_price = trim($item_match[3]);          // Captura 3: Segundo valor numérico (ej. Precio Unitario)
						$valor3 = trim($item_match[4]);          // Captura 4: Tercer valor numérico (ej. Precio Total)
						
						//if ($modelo === "Unidad" || $modelo === "#" || $modelo === "43UM5N-E") continue;
						if ($modelo === "Unidad" || $modelo === "#") continue;
						else {
						// Aquí guardamos los valores directamente sin la descripción general por ahora.
						// Asume que la normalización de números ($this->normalize_number) se hará después.
							$data['items'][] = [
								'modelo' => $modelo,
								'qty' => $this->normalize_number($qty),
								'unit_selling_price' => $this->normalize_number($unit_selling_price),
								'valor3' => $this->normalize_number($valor3),
								// Puedes añadir otros campos vacíos o por defecto si tu estructura final los necesita
								'currency' => 'PEN',
								'description' => '', // La dejaremos vacía por ahora, o puedes decidir no incluirla
								//'qty' => 0, // Estos los mapearás de qty, unit_selling_price, valor3 después
								//'unit_selling_price' => 0,
								// ... otros campos
							];
						}
						error_log("Extraído ítem: Modelo='" . $modelo . "', Valores: [{$qty}, {$unit_selling_price}, {$valor3}]");
					}
				} else {
					error_log("No se encontraron patrones de ítems válidos en la línea detectada.");
				}
        // Fin de extracción de ítems
			}
		}
		$all_data[] = $data;
        return $all_data;
	}
	
	public function extract_conecta($text){ // ok conecta pdf
		$bill_to_code_ph = 'PE000991001B'; // Conecta
		$bill_to_code_selva = 'PE008104001B';	//Conecta selva
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		$all_data = [];
		$data = $this->data_initialize();		
		//echo '<pre>'; print_r($text);
        // --- 1. Extracción de Número de Boleta / Orden de Compra y Fecha ---
		
        if (preg_match('/Ped.\s+Nacional\s+N°\s*.*?(\d{10})/is', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
			$data['customer_po_no'] = $matches[1];
        } elseif (preg_match('/(?:BOLETA|FACTURA)\s*(?:N°|No\.?|Num\.?|:)?\s*([A-Z0-9-]+)/i', $text, $matches)) {
            $data['customer_po_no'] = $matches[1];
        }
	
        // if (preg_match('/FECHA\s+DE\s+EMISION\s+:(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{2,4})/', $text, $matches)) {
			// //echo '<pre>'; print_r($matches);
            // $data['customer_po_date'] = $matches[1];
        // } elseif (preg_match('/\b(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4})\b/', $text, $matches)) {
            // $data['customer_po_date'] = $matches[1];
        // }
		
		$data['customer_po_date'] = Date("Ymd");
		
		if (preg_match('/FECHA\s+DE\s+VENCIMIENTO\s+:(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{2,4})/', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
            //$data['request_arrival_date'] = $matches[1];
			$date_obj = DateTime::createFromFormat('d.m.Y', $matches[1]);
			$data['request_arrival_date'] = $date_obj->format('Ymt'); 
        } elseif (preg_match('/\b(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4})\b/', $text, $matches)) {
			$date_obj = DateTime::createFromFormat('d.m.Y', $matches[1]);
			$data['request_arrival_date'] = $date_obj->format('Ymt');
        }
		
		if (preg_match('/TIPO\s+MONEDA\s+:(\w+)/', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
            $data['currency'] = $matches[1];
        } elseif (preg_match('/\b(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4})\b/', $text, $matches)) {
            $data['currency'] = $matches[1];
        }
		
		if (preg_match('/N°\sDIAS\s+:(\w+)/', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
            $data['payterm'] = $payterm[$matches[1]];
        } elseif (preg_match('/\b(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4})\b/', $text, $matches)) {
            $data['payterm'] = $payterm[$matches[1]];
        }
		
		// Currency
		if (preg_match('/TIPO\s+MONEDA\s+:(.*?)\s*item/i', $text, $matches)) {
			//echo '<pre>'; print_r($matches);
            $currency = $matches[1];
        } elseif (preg_match('/\b(\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4})\b/', $text, $matches)) {
            $currency = $matches[1];
        }
		
		
		// Selva validation

		$regex = '/LUGAR ENTREGA\s*:.*? (SELVA|selva)/i';

		$matches = [];
		if (preg_match($regex, $text, $matches)) {
			// La coincidencia se encuentra en $matches[1]
			//echo '<pre>'; print_r($matches);
			$selva = $matches[1];
			$is_selva = 1;
		} else $is_selva = 0;
		if ($is_selva) $bill_to_code = $bill_to_code_selva;
		else $bill_to_code = $bill_to_code_ph;
		// Ship to 
		$pattern = '/LUGAR\s+ENTREGA\s+:(.*?)\s*TIPO/i';

		$extracted_delivery_location = null;

		if (preg_match($pattern, $text, $matches)) {
			// El texto capturado estará en $matches[1]
			//echo '<pre>'; print_r($matches);
			$extracted_delivery_location = trim($matches[1]);
			
			$ship_to_code_found = ''; // Valor por defecto
			$found_ship_to = false;

			if (!empty($extracted_delivery_location)) {
				// Divide el valor extraído en palabras para buscar
				$location_parts = explode(' ', $extracted_delivery_location);
				//echo '<pre>'; print_r($location_parts); echo '</pre>';
				foreach ($location_parts as $part) {
					//echo '<pre>'; print_r($part);
					$part = trim($part);
					if (empty($part)) continue; // Saltar partes vacías

					// Realiza la búsqueda en la base de datos
					// Asegúrate de que $this->gen_m->filter_select soporte la sintaxis LIKE de esta forma
					$ship_to_results = $this->gen_m->filter_select('scm_ship_to2', false, ['ship_to_code'], ['bill_to_code' => $bill_to_code, 'address LIKE' => "%{$part}%"]);
					//echo '<pre>'; print_r($ship_to_results);
					if (!empty($ship_to_results)) {
						$ship_to_code_found = $ship_to_results[0]->ship_to_code;
						//echo '<pre>'; print_r($ship_to_code_found);
						$found_ship_to = true;
						$data['ship_to'] = $ship_to_code_found;
						error_log("INFO: ship_to_code encontrado para lugar de entrega '{$extracted_delivery_location}' usando parte '{$part}': " . $ship_to_code_found);
						break; // Una vez que encuentras uno, puedes salir del bucle
					} elseif(empty($ship_to_results) && $bill_to_code === $bill_to_code_ph) $data['ship_to'] = '991VILLA-S';
					  elseif(empty($ship_to_results) && $bill_to_code === $bill_to_code_selva) $data['ship_to'] = '8104VILLA-S';
				}
				if (!$found_ship_to) {
					error_log("Advertencia: No se encontró ship_to_code para lugar de entrega: " . $extracted_delivery_location);
				}
			}			
			//echo "Texto extraído: " . $extracted_delivery_location . "\n"; // Salida: "Tienda CONECTA RETAIL S.A - HUB PUNTA"
		} else {
			echo "No se pudo extraer el lugar de entrega del texto.\n";
		}
		// --- 1. Patrones para los campos individuales ---
		$codigo_pattern = '(\S+)';
		$unit_pattern = '(\S+)';
		// Ajustamos un poco el quantity_pattern para ser más flexible con decimales si aparecen sin .00
		$quantity_pattern = '(\d+(?:[.,]\d+)?)';
		// value_pattern ya es robusto para diferentes formatos de números
		$value_pattern = '(\d{1,5}(?:,\d{3})*(?:\.\d{1,2})?|\d{1,5}(?:\.\d{1,2})?)';
		$discount_pattern = $value_pattern; // El descuento es un valor numérico similar
		
		$start_table_pattern = '/ITEM\s*CODIGO\s*DESCRIPCION\s*UNIDAD\s*CANTIDAD\s*SOLICITADA\s*PRECIO\s*UNITARIO\s*DESCUENTO\s*TOTAL/is';
		
		if (preg_match($start_table_pattern, $text, $start_match, PREG_OFFSET_CAPTURE)) {
			//echo '<pre>'; print_r($start_match);
			// Calculamos la posición en el texto justo después de la línea de encabezados.
			// Esto asegura que empezamos a buscar los ítems en el lugar correcto.
			$current_pos = $start_match[0][1] + strlen($start_match[0][0]);
			error_log("INFO: Se encontró el inicio de la tabla. Comenzando extracción de ítems.");

			// --- 3. Construir el Patrón para una Sola Línea de Ítem ---
			$item_line_pattern = '/(\d{5})\s*' .            // Grupo 1: ITEM_NUM (ej. 00010, 00020). Ya no requiere estar al inicio de una "línea" real.
                         $codigo_pattern . '\s+' .          // Grupo 2: CODIGO/MODEL
                         '(.*?)' . '\s+' .                  // Grupo 3: DESCRIPCION (MUY IMPORTANTE: es no-codiciosa hasta 'UNIDAD')
                         $unit_pattern . '\s+' .            // Grupo 4: UNIDAD (ANCLA para el final de la descripción)
                         $quantity_pattern . '\s+' .        // Grupo 5: CANTIDAD
                         $value_pattern . '\s+' .           // Grupo 6: PRECIO_UNITARIO
                         $discount_pattern . '\s+' .        // Grupo 7: DESCUENTO
                         $value_pattern .                   // Grupo 8: TOTAL                      
                         '(?=\s*(?:\d{5}|SUBTOTAL|TOTAL|IMPORTE TOTAL|IMPORTANTE:|\z))' .
                         '/'; // NOTA: ¡SIN MODIFICADORES 'm' o 'g' AQUÍ! 's' tampoco es estrictamente necesario ya que no hay '.'



			// --- 4. Bucle de Extracción ---
			while (preg_match($item_line_pattern, $text, $item_matches, PREG_OFFSET_CAPTURE, $current_pos)) {
				//echo '<pre>'; print_r($item_matches);
				$item_number = $item_matches[1][0];
				//$codigo = $item_matches[2][0];
				$codigo = $this->models_sales_order($item_matches[2][0]);
				$description = trim($item_matches[3][0]);
				$unit = $item_matches[4][0];
				$quantity = $item_matches[5][0];
				$unit_selling_price = $item_matches[6][0];
				$discount = $item_matches[7][0];
				$total_value = $item_matches[8][0];

				$data['items'][] = [
					'item_number' => $item_number,
					'modelo' => $codigo, // Usamos 'codigo' en vez de 'modelo'
					'description' => $description,
					'unidad' => $unit,
					'qty' => $this->normalize_number($quantity),
					'unit_selling_price' => $this->normalize_number($unit_selling_price),
					'discount' => $this->normalize_number($discount),
					'total_value' => $this->normalize_number($total_value),
					'currency' => $currency === 'PEN' ? 'PEN' : 'USD'
				];
				
				//echo '<pre>'; print_r($data);
				error_log("Ítem extraído: " . json_encode($data['items'][count($data['items'])-1]));

				$current_pos = $item_matches[0][1] + strlen($item_matches[0][0]);

				if (strlen(trim(substr($text, $current_pos))) < 10 && count($data['items']) > 0) {
					error_log("El texto restante es demasiado corto para encontrar más ítems, deteniendo la extracción.");
					break;
				}
			}
			error_log("Terminó la extracción de ítems. Texto restante desde current_pos para depuración: '" . substr($text, $current_pos, 200) . "'");
			
		}
		$all_data[] = $data;
        return $all_data;
	}
	
	public function extract_integra($text_content_for_pdf) {
		$all_invoices_data = [];

		// Nuevo patrón para encontrar el inicio de una nueva boleta/factura
		// Usa un lookahead para dividir el texto sin eliminar el patrón de inicio.
		$invoice_start_pattern = '/(?=CantidadUnidad Descripción TOTAL ORDEN DE COMPRA N°)/i';

		// Dividir el texto completo del PDF en bloques de boletas
		$invoice_blocks = preg_split($invoice_start_pattern, $text_content_for_pdf, -1, PREG_SPLIT_NO_EMPTY);
		
		if (!empty($invoice_blocks)) {
			foreach ($invoice_blocks as $text) {
				//echo '<pre>'; print_r($invoice_text);
				// Procesar cada bloque de una sola boleta
				$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
				$data = $this->data_initialize();
				
				// --- 1. Extracción de Número de Boleta / Orden de Compra y Fechas ---
				if (preg_match('/Observaciones para el Proveedor\s*.*?(\d{7})/is', $text, $matches)) {
					$data['customer_po_no'] = $matches[1];
				} elseif (preg_match('/(?:BOLETA|FACTURA)\s*(?:N°|No\.?|Num\.?|:)?\s*([A-Z0-9-]+)/i', $text, $matches)) {
					$data['customer_po_no'] = $matches[1];
				}
				$data['request_arrival_date'] = date('Ymt');
				$data['customer_po_date'] = date('Ymd');
				$data['ship_to'] = '8204VESCEVA-S';
				
				// --- 2. Extracción de Términos de Pago ---
				if (preg_match('/CREDITO (\d+) DIAS/', $text, $matches)) {
					$credit = $matches[1];
					$data['payterm'] = $payterm[$credit] ?? null;
				}
				
				// --- 3. Extracción de bloques de ítems ---
				$qty_block_pattern = '/(?:UNI\s*)+(?P<quantities>(?:\s*\d+)+)/is';
				$prices_block_pattern = '/(?P<prices>(?:[\d\.\s,]+))\s*(?=Lugar de Entrega|Cod\. Item)/is';

				$qty_block_matches = [];
				preg_match($qty_block_pattern, $text, $qty_block_matches, PREG_OFFSET_CAPTURE);
				$qty_block_string = $qty_block_matches['quantities'][0] ?? '';
				$qty_block_end_pos = ($qty_block_matches[0][1] ?? 0) + strlen($qty_block_matches[0][0] ?? '');

				$prices_block_matches = [];
				preg_match($prices_block_pattern, $text, $prices_block_matches, PREG_OFFSET_CAPTURE);
				$prices_block_string = $prices_block_matches['prices'][0] ?? '';
				$prices_block_start_pos = $prices_block_matches['prices'][1] ?? 0;

				$models_block_string = '';
				if ($qty_block_end_pos && $prices_block_start_pos) {
					$models_block_string = substr($text, $qty_block_end_pos, $prices_block_start_pos - $qty_block_end_pos);
				}
				
				// --- 4. Procesar y construir los datos de ítems ---
				if (!empty($qty_block_string) && !empty($models_block_string) && !empty($prices_block_string)) {
					$qtys_raw = preg_split('/\s+/', trim($qty_block_string), -1, PREG_SPLIT_NO_EMPTY);
					$models_words = preg_split('/\s+/', trim($models_block_string), -1, PREG_SPLIT_NO_EMPTY);
					$models_words_index = 0;
					
					preg_match_all('/[\d\.]+(?:,\d{2})?/', $prices_block_string, $prices_list);
					$num_items = count($qtys_raw);
					$all_prices = $prices_list[0];
					$unit_prices = array_slice($all_prices, 0, $num_items);

					if ($num_items !== count($unit_prices)) {
						 error_log("Error de consistencia en el numero de items. QTY: " . count($qtys_raw) . ", Precios: " . count($unit_prices));
					}

					for ($i = 0; $i < $num_items; $i++) {
						$qty = $qtys_raw[$i];
						$unit_selling_price = str_replace(',', '', $unit_prices[$i] ?? '');
						
						$final_model = "Modelo no encontrado";
						
						for ($j = $models_words_index; $j < count($models_words); $j++) {
							$item = $models_words[$j];
							if (strlen($item) > 3) {
								$found_model = $this->models_sales_order_integra($item);
								if ($found_model !== 'Not Found') {
									$final_model = $found_model;
									$models_words_index = $j + 1;
									break;
								}
							}
						}
						
						$data['items'][] = [
							'qty' => $qty,
							'modelo' => $final_model,
							'currency' => 'PEN',
							'unit_selling_price' => $unit_selling_price,
						];
					}
				} else {
					error_log("No se encontraron los bloques de datos necesarios para la extracción.");
				}
				//return $data;
				//$data = $this->extract_integra_single($invoice_text);
				
				// Añadir el resultado al array final
				$all_invoices_data[] = $data;
			}
		}
		//echo '<pre>'; print_r($all_invoices_data);
		return $all_invoices_data;
	}

	public function extract_oeschle($file_path){
		$bill_to_code = 'PE007152001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		//load excel file
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		//foreach ($spreadsheet->getSheetNames() as $sheetName) {
		$all_data = [];
		//$sheet = $spreadsheet->getSheetByName($sheetName);
		
		$data['customer_po_no'] = trim($sheet->getCell('B7')->getValue());
		$address = trim($sheet->getCell('B10')->getValue());
		$currency = trim($sheet->getCell('I9')->getValue());
		
		$aux_payterm = explode(' ', trim($sheet->getCell('I10')->getValue()));
		$data['payterm'] = $payterm[$aux_payterm[1]] ?? null;
		
		$aux_ship = trim($sheet->getCell('B10')->getValue());
		$ship_to = explode(" ", $aux_ship);
		if ($ship_to[1] === 'VES') {
			$ship_to_code = $this->gen_m->filter_select('scm_ship_to2', false, 'ship_to_code', ['bill_to_code' => $bill_to_code]);
			$data['ship_to'] = $ship_to_code[0]->ship_to_code;
		}
		
		$date_obj = DateTime::createFromFormat('Y-m-d', $this->date_convert(trim($sheet->getCell('B9')->getValue())));
		$data['request_arrival_date'] = $date_obj->format('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$max_row = $sheet->getHighestRow();
		for($i = 16; $i <= $max_row; $i++){
			$aux_model = trim($sheet->getCell('B'.$i)->getValue());
			$model = $this->models_sales_order($aux_model);
			$data['items'][] = [
				"currency"				=> ($currency === 'S/.') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('H'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('I'.$i)->getValue()),		
			];	
		}
		//}
		$all_data[] = $data;
		//echo '<pre>'; print_r($all_data);
		return $all_data;
	}
	
	public function extract_oeschle_yellow($file_path){
		$bill_to_code = 'PE007152001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		//load excel file
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		$all_data = [];
		//foreach ($spreadsheet->getSheetNames() as $sheetName) {
	
	
		//$sheet = $spreadsheet->getSheetByName($sheetName);
		
		$data['customer_po_no'] = trim($sheet->getCell('A2')->getValue());
		$address = trim($sheet->getCell('E2')->getValue());
		$currency = trim($sheet->getCell('I2')->getValue());
		
		//$aux_payterm = explode(' ', trim($sheet->getCell('I10')->getValue()));
		$data['payterm'] = null;
		
		$aux_ship = trim($sheet->getCell('E2')->getValue());
		$ship_to = explode(" ", $aux_ship);
		if ($ship_to[1] === 'VES') {
			$ship_to_code = $this->gen_m->filter_select('scm_ship_to2', false, 'ship_to_code', ['bill_to_code' => $bill_to_code]);
			$data['ship_to'] = $ship_to_code[0]->ship_to_code;
		}
		
		$date_obj = DateTime::createFromFormat('d-m-Y', trim($sheet->getCell('H2')->getValue()));
		$data['request_arrival_date'] = $date_obj->format('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			$aux_model = trim($sheet->getCell('L'.$i)->getValue());
			$model = $this->models_sales_order($aux_model);
			$data['items'][] = [
				"currency"				=> ($currency === 'PEN') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('T'.$i)->getValue()) / trim($sheet->getCell('S'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('S'.$i)->getValue()),		
			];	
		}
		//}
		$all_data[] = $data;
		//echo '<pre>'; print_r($all_data);
		return $all_data;
	}
	
	public function extract_plaza_vea($file_path){ // OK
		$bill_to_code = 'PE001351001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		//foreach ($spreadsheet->getSheetNames() as $sheetName) {
		$all_data = [];
	
		//$sheet = $spreadsheet->getSheetByName($sheetName);
		
		$data['customer_po_no'] = trim($sheet->getCell('B2')->getValue());
		$address = trim($sheet->getCell('E2')->getValue());
		$currency = 'PEN';
		
		//$aux_payterm = explode(' ', trim($sheet->getCell('I10')->getValue()));
		$data['payterm'] = NULL;
		
		$aux_ship = trim($sheet->getCell('E2')->getValue());
		$ship_to_parts = explode(" ", $aux_ship,3);
		$ship_to = $ship_to_parts[2];
		if ($ship_to === 'PUNTA NEGRA') {
			$ship_to_code = $this->gen_m->filter_select('scm_ship_to2', false, 'ship_to_code', ['bill_to_code' => $bill_to_code]);
			$data['ship_to'] = $ship_to_code[0]->ship_to_code;
		}
		
		$date_obj = DateTime::createFromFormat('d-m-Y', trim($sheet->getCell('H2')->getValue()));
		$data['request_arrival_date'] = $date_obj->format('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			if (trim($sheet->getCell('B'.$i)->getValue()) === null || trim($sheet->getCell('B'.$i)->getValue()) === '') continue;
			$aux_model = trim($sheet->getCell('L'.$i)->getValue());
			$aux_model_part = explode("-", $aux_model);
			foreach($aux_model_part as $part){
				if(strlen($part) > 3){
					$model_verify = $this->models_sales_order($part);
					if (!empty($model_verify)) $model = $model_verify;
					else $model = null;
				}
			}
			
			$data['items'][] = [
				"currency"				=> ($currency === 'PEN') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('W'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('T'.$i)->getValue()),		
			];	
		}
		//}
		//echo '<pre>'; print_r($data);
		$all_data[] = $data;
		return $all_data;
	}
	
	public function extract_promart($file_path){
		$bill_to_code = 'PE008158001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		//foreach ($spreadsheet->getSheetNames() as $sheetName) {
		$all_data = [];
	
		//$sheet = $spreadsheet->getSheetByName($sheetName);
		
		$data['customer_po_no'] = trim($sheet->getCell('B7')->getValue());
		$address = trim($sheet->getCell('B10')->getValue());
		$currency = trim($sheet->getCell('I9')->getValue());
		
		$aux_payterm = explode(' ', trim($sheet->getCell('I10')->getValue()));
		$data['payterm'] = $payterm[$aux_payterm[1]] ?? NULL;
		
		$aux_ship = trim($sheet->getCell('B10')->getValue());
		$ship_to = explode(" ", $aux_ship);
		if ($ship_to[1] === 'STOL') {
			$ship_to_code = $this->gen_m->filter_select('scm_ship_to2', false, 'ship_to_code', ['bill_to_code' => $bill_to_code]);
			$data['ship_to'] = $ship_to_code[0]->ship_to_code;
		}
		
		$date_obj = DateTime::createFromFormat('Y-m-d', $this->date_convert(trim($sheet->getCell('B9')->getValue())));
		$data['request_arrival_date'] = $date_obj->format('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$max_row = $sheet->getHighestRow();
		for($i = 18; $i <= $max_row; $i++){
			if (trim($sheet->getCell('C'.$i)->getValue()) === null || trim($sheet->getCell('C'.$i)->getValue()) === '') continue;
			$aux_model = trim($sheet->getCell('C'.$i)->getValue());
			$model = $this->models_sales_order($aux_model);
			$data['items'][] = [
				"currency"				=> ($currency === 'PEN') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('G'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('I'.$i)->getValue()),		
			];	
		}
		//}
		//echo '<pre>'; print_r($data);
		$all_data[] = $data;
		return $all_data;
	}
	
	public function extract_promart_yellow($file_path){
		$bill_to_code = 'PE008158001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		$all_data = [];
		
		$data['customer_po_no'] = trim($sheet->getCell('A2')->getValue());
		$address = trim($sheet->getCell('F2')->getValue());
		$currency = trim($sheet->getCell('J2')->getValue());
		
		//$aux_payterm = explode(' ', trim($sheet->getCell('I10')->getValue()));
		$data['payterm'] = null;
		
		$aux_ship = trim($sheet->getCell('F2')->getValue());
		$ship_to = explode(" ", $aux_ship);
		if ($ship_to[1] === 'STOL') {
			$ship_to_code = $this->gen_m->filter_select('scm_ship_to2', false, 'ship_to_code', ['bill_to_code' => $bill_to_code]);
			$data['ship_to'] = $ship_to_code[0]->ship_to_code;
		}
		
		$date_obj = DateTime::createFromFormat('d-m-Y', trim($sheet->getCell('I2')->getValue()));
		$data['request_arrival_date'] = $date_obj->format('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			$aux_model = trim($sheet->getCell('M'.$i)->getValue());
			
			$model_exp = explode(" ", $aux_model);
			foreach($model_exp as $item) {
				
				if(strlen($item) > 2 && !is_numeric($item)) {
					//echo '<pre>'; print_r($item);
					$models = $this->gen_m->filter_select('lgepr_sales_order', false, 'model', ['model LIKE' => "%{$item}%"]);
					//echo '<pre>'; print_r($models);
					if (!empty($models)) {
						$model = $models[0]->model;
						break;
					}
					else{
						$model_close = $this->gen_m->filter_select('lgepr_closed_order', false, 'model', ['model LIKE' => "%{$item}%"]);
						if (!empty($model_close)) {
							$model = $model_close[0]->model;
							break;
						}
						else $model = null;
					}
				}
			}		
			
			if ($model === '' || empty($model)){
				$sku = trim($sheet->getCell('K'.$i)->getValue());
				$aux = $this->gen_m->filter_select('scm_sku', false, 'sku', ['sku_customer' => $sku]);
				$model = $aux[0]->sku;
			} else $model = $model;
			//$model = $this->models_sales_order($aux_model);
			
			$data['items'][] = [
				"currency"				=> ($currency === 'PEN') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('R'.$i)->getValue()) / trim($sheet->getCell('Q'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('Q'.$i)->getValue()),		
			];	
		}
		//}
		//echo '<pre>'; print_r($data);
		$all_data[] = $data;
		return $all_data;
	}
	
	public function extract_ripley($file_path){
		$bill_to_code = 'PE000966001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		$all_data = [];
		
		$data['customer_po_no'] = trim($sheet->getCell('C2')->getValue());
		$address = trim($sheet->getCell('O2')->getValue());
		$currency = trim($sheet->getCell('T2')->getValue());
		
		$aux_payterm = explode(' ', trim($sheet->getCell('U2')->getValue()));
		$data['payterm'] = $payterm[$aux_payterm[1]];
		
		$aux_ship = trim($sheet->getCell('L2')->getValue());
		//$ship_to = explode(" ", $aux_ship);
		if ($aux_ship === '20026') {
			// $ship_to_code = $this->gen_m->filter_select('scm_ship_to2', false, 'ship_to_code', ['bill_to_code' => $bill_to_code]);
			// $data['ship_to'] = $ship_to_code[0]->ship_to_code;
			$data['ship_to'] = '337564VES2-S';
		} else $data['ship_to'] = '337564CD';
		
		$date_obj = DateTime::createFromFormat('Y-m-d', $this->date_convert(trim($sheet->getCell('Q2')->getValue())));
		$data['request_arrival_date'] = $date_obj->format('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			$aux_model = trim($sheet->getCell('AB'.$i)->getValue());
			$model = $this->models_sales_order($aux_model);		
			
			//$model = $this->models_sales_order($aux_model);
			
			$data['items'][] = [
				"currency"				=> ($currency === 'S/') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('AD'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('AE'.$i)->getValue()),		
			];	
		}
		//}
		//echo '<pre>'; print_r($data);
		$all_data[] = $data;
		return $all_data;
	}
	
	public function extract_tottus($file_path, $file_name){
		$bill_to_code = 'PE004467001B';
		$payterm = [0 => 'N0000FSN1000', 10 => 'N0010FSN1708', 30 => 'N0030FSN1006', 45 => 'N0045FSN1008', 60 => 'N0060FSN1010', 90 => 'N0090FSN1012'];
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/extract_excel.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		$all_data = [];
		
		$file_name = explode(".", $file_name);
		$data["customer_po_no"] = $file_name[0];
		$currency = 'S/';		
		$data['request_arrival_date'] = Date('Ymt');
		
		$data['customer_po_date'] = Date('Ymd');
		
		$aux_ship = explode(" ", trim($sheet->getCell('F2')->getValue()));
		if (strpos($aux_ship[1], 'BTL') !== false){
			$ship_to = '4467LURIN2-S'; 
		} elseif (strpos($aux_ship[1], 'Huachipa') !== false){
			$ship_to = '4467LURIN1-S'; 
		}
		$data['ship_to'] = $ship_to;
		
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			$aux_model = trim($sheet->getCell('C'.$i)->getValue());
			$model = $this->models_sales_order($aux_model);		
			
			//$model = $this->models_sales_order($aux_model);
			
			$data['items'][] = [
				//"customer_po_no"		=> trim($sheet->getCell('A'.$i)->getValue()),
				"currency"				=> ($currency === 'S/') ? 'PEN' : 'USD',
				"modelo" 				=> $model,
				"qty"					=> trim($sheet->getCell('G'.$i)->getValue()),
				"unit_selling_price" 	=> trim($sheet->getCell('L'.$i)->getValue()),
				//"ship_to"				=> $ship_to
			];
		}
		$all_data[] = $data;
		return $all_data;
	}
	
	private function extract_data_from_text($text, $client_name, $file_path = null, $file_name = null) {
		$data = [
            'date' => 'N/A',
            'boleta' => 'N/A',
            'ruc_proveedor' => 'N/A',
            'razon_social_proveedor' => 'N/A',
            'ruc_cliente' => 'N/A',
            'razon_social_cliente' => 'N/A',
            'items' => [],
            'total' => 'N/A'
        ]; // Inicializar $data para asegurar que siempre se retorne algo
		switch ($client_name){
			case 'IMPORTACIONES RUBI S.A.':  //pdf
				$data = $this->extract_rubi($text);
				break;
			case 'HIRAOKA': 
				//$data = $this->extract_hiraoka();
				break;
			case 'SAGA FALABELLA S.A.':
				$data = $this->extract_saga($file_path); // txt 
				break;
			case 'REPRESENTACIONES VARGAS S.A.':
				$data = $this->extract_credivargas($text); // scan pdf
				break;
			case 'REPRESENTACIONES VARGAS S.A. (Bold Format)': // scan pdf bold format
				$data = $this->extract_credivargas_bold_format($text);
				break;
			case 'CONECTA RETAIL S.A.':
				$data = $this->extract_conecta($text); // editable pdf
				break;
			case 'INTEGRA RETAIL S.A.C.': // editable pdf
				$data = $this->extract_integra($text);
				break;
			case 'TIENDAS PERUANAS S.A. - OESCHLE': // excel
				$data = $this->extract_oeschle($file_path);
				break;
			case 'TIENDAS PERUANAS S.A. - OESCHLE (Yellow)': // excel
				$data = $this->extract_oeschle_yellow($file_path);
				break;
			case 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA - PLAZA VEA': // excel
				$data = $this->extract_plaza_vea($file_path);
				break;
			case 'HOMECENTERS PERUANOS S.A. - PROMART': // excel
				$data = $this->extract_promart($file_path);
				break;
			case 'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.': // excel
				$data = $this->extract_ripley($file_path);
				break;
			case 'HIPERMERCADOS TOTTUS S.A.':
				$data = $this->extract_tottus($file_path, $file_name); // txt 
				break;
			default:
                error_log("Cliente no reconocido: " . $client_name . ". Aplicando lógica por defecto (o ninguna específica).");
                break;
		}
		
		return $data;
      
    }
	
	private function normalize_number($num_str) {
        // Remove thousands separators (dots) and replace decimal comma with dot for float conversion
        $num_str = str_replace(['.', ','], ['.', ''], $num_str);
        return floatval($num_str);
    }
	
	public function process(){ // not working in the module
		set_time_limit(0);
		ini_set("memory_limit", -1);

		$pdf_path = "./upload/extract_file.pdf";
        $txt_path = "./upload/extract_file.txt";

		$extracted_data = [];
		$list_client_txt = ['SAGA FALABELLA S.A.'];
        $client_name = $this->input->post('client'); 
        if (empty($client_name)) {
            error_log("Error: Client name not provided.");
            header('Content-Type: application/json');
            echo json_encode(["type" => "error", "msg" => "Client name not provided. Please select a client.", "url" => ""]);
            exit;
        }
        error_log("Selected client: " . $client_name);

        $file_type = 'unknown';
        $uploaded_file_path = '';
        $text_content_for_pdf = ''; // Variable para el contenido de texto extraído de PDFs

        // Determine expected file type and upload
        if (in_array($client_name, $list_client_txt)) {
            $config = [
                'upload_path'	=> './upload/',
                'allowed_types'	=> 'txt',
                'max_size'		=> 5000,
                'overwrite'		=> TRUE,
                'file_name'		=> 'extract_file'
            ];
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('attach')){
                $msg = $this->upload->display_errors('', '');
                error_log("Error uploading TXT: " . $msg);
                header('Content-Type: application/json');
                echo json_encode(["type" => "error", "msg" => "Failed to upload TXT file: " . $msg]);
                exit;
            }
            $file_type = 'txt';
            $uploaded_file_path = $txt_path;
            // No leemos el contenido aquí, lo hará la función específica 'extract_data_from_txt_client'

        } else { // For other clients, assume PDF
            $config = [
                'upload_path'	=> './upload/',
                'allowed_types'	=> 'pdf',
                'max_size'		=> 10000,
                'overwrite'		=> TRUE,
                'file_name'		=> 'extract_file',
            ];
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('attach')){
                $msg = $this->upload->display_errors('', '');
                error_log("Error uploading PDF: " . $msg);
                header('Content-Type: application/json');
                echo json_encode(["type" => "error", "msg" => "Failed to upload PDF file: " . $msg]);
                exit;
            }
            $file_type = 'pdf';
            $uploaded_file_path = $pdf_path;

            // Process PDF (Smalot or OCR) to get text content
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($uploaded_file_path);
                $text_raw_smalot = $pdf->getText();
                $text_content_for_pdf = $this->clean_text($text_raw_smalot);
            } catch (Exception $e) {
                error_log("Error with Smalot\\PdfParser: " . $e->getMessage());
                $text_content_for_pdf = ""; // Fallback to OCR
            }

            if (strlen(trim($text_content_for_pdf)) < 50) {
				error_log("El texto de Smalot es demasiado corto o vacío, intentando OCR...");

				// --- INICIO DE LA LÓGICA DE CONVERSIÓN DE PDF A IMAGEN Y OCR ---
				$temp_dir = sys_get_temp_dir(); // Obtiene el directorio temporal del sistema
				// Prefijo único para las imágenes para evitar conflictos si se procesan varios PDFs a la vez
				$output_image_prefix = $temp_dir . DIRECTORY_SEPARATOR . 'pdf_page_' . uniqid() . '_';

				// Array para guardar las rutas de TODAS las imágenes temporales generadas (originales y preprocesadas)
				$image_files_to_clean = []; 

				// Comando para convertir el PDF a imágenes (una por página)
				$command_convert = "magick convert -density 400 -quality 100 -flatten -alpha remove -auto-level -deskew 40% \"" . $uploaded_file_path . "\" \"" . $output_image_prefix . "%d.png\" 2>&1";

				error_log("Ejecutando comando ImageMagick (conversión): " . $command_convert);
				$output_convert = [];
				$return_var_convert = 0;
				exec($command_convert, $output_convert, $return_var_convert); // Ejecuta el comando

				if ($return_var_convert != 0) { // Si el comando ImageMagick falló
					$error_msg_convert = implode("\n", $output_convert);
					error_log("Error al convertir PDF a imagen con ImageMagick. Código de retorno: " . $return_var_convert . " | Salida: " . $error_msg_convert);
					// Elimina el PDF subido si hubo un error de conversión.
					if (file_exists($uploaded_file_path)) {
						unlink($uploaded_file_path);
					}
					header('Content-Type: application/json');
					echo json_encode(["type" => "error", "msg" => "Error al convertir PDF a imagen. Asegúrate de que ImageMagick y Ghostscript estén instalados y en el PATH. Detalles: " . $error_msg_convert]);
					exit;
				}

				// Recorrer las imágenes generadas y aplicar OCR a cada una
				$full_ocr_text = '';
				$found_images = false;
				$page_num = 0;
				while (file_exists($output_image_prefix . $page_num . '.png')) {
					$original_page_image = $output_image_prefix . $page_num . '.png';
					$image_files_to_clean[] = $original_page_image;
					$found_images = true;

					// --- PASO DE PREPROCESAMIENTO DE IMAGEN ---
					$preprocessed_image_file = $temp_dir . DIRECTORY_SEPARATOR . 'preprocessed_' . uniqid() . '_' . $page_num . '.png';

					// Comando para preprocesar la imagen:
					// -threshold 50%: Binarización. Convierte a blanco y negro puro. Ajusta el 50% si el documento es muy claro/oscuro.
					// -deskew 40%: Endereza automáticamente la imagen hasta un ángulo de 40 grados.
					// -auto-orient: Corrige la orientación si la página está rotada.
					
					//$command_preprocess = "magick convert \"{$original_page_image}\" -median 2 -lat 30x30-12% -unsharp 0x4+0.8+0 \"{$preprocessed_image_file}\" 2>&1"; // O -10%
					
					//$command_preprocess = "magick convert \"{$original_page_image}\" -normalize -despeckle -colorspace Gray -median 2 -lat 15x15-10% -morphology Dilate Disk:1 -morphology Erode Disk:0.5 -unsharp 0x1+0.5+0 \"{$preprocessed_image_file}\" 2>&1"; // O -10%
					
					//$command_preprocess = "magick convert \"{$original_page_image}\" -normalize -despeckle -colorspace Gray -median 2 -lat 15x15-30% -morphology Dilate Disk:1 -morphology Erode Disk:0.5 -unsharp 0x2+1.0+0.05 \"{$preprocessed_image_file}\" 2>&1"; // O -10%
					
					$command_preprocess = "magick convert \"{$original_page_image}\" -normalize -despeckle -colorspace Gray -median 2 -lat 18x18-15% -morphology Dilate Disk:1 -morphology Erode Disk:0.5 -unsharp 0x1+0.5+0.01 \"{$preprocessed_image_file}\" 2>&1"; // O -10%

					error_log("Ejecutando comando ImageMagick (preprocesamiento): " . $command_preprocess);
					$output_preprocess = [];
					$return_var_preprocess = 0;
					exec($command_preprocess, $output_preprocess, $return_var_preprocess);

					$image_to_ocr = $original_page_image; // Por defecto, usa la imagen original
					if ($return_var_preprocess == 0) {
						error_log("Preprocesamiento exitoso para página " . $page_num . ". Usando imagen preprocesada.");
						$image_to_ocr = $preprocessed_image_file;
						$image_files_to_clean[] = $preprocessed_image_file;
					} else {
						error_log("Error al preprocesar imagen para la página " . $page_num . ". Usando imagen original. Salida: " . implode("\n", $output_preprocess));
					}
					
					// --- FIN PASO DE PREPROCESAMIENTO ---
					
					try {
						$tesseract = new TesseractOCR($image_to_ocr);
						$tesseract->lang('spa'); // Idioma español
						// psm(6): "Assume a single uniform block of text." (Tu configuración actual)
						// psm(11): "Sparse text. Find as much text as possible in no particular order." (Suele ser mejor para boletas/recibos)
						$tesseract->psm(12);
						$tesseract->oem(3);
						$page_text = $tesseract->run();
						//echo '<pre>'; print_r($page_text);
						$full_ocr_text .= $this->clean_text($page_text) . "\n"; // Concatena el texto de cada página
						error_log("OCR exitoso para la página " . $page_num . " del PDF convertido. Texto (parcial): " . substr($this->clean_text($page_text), 0, 100) . "...");
						error_log("RAW OCR Text for page " . $page_num . ":\n" . $page_text); // <--- Añadido para depurar el texto RAW
					} catch (Exception $e) {
						error_log("Error con TesseractOCR en imagen " . $image_to_ocr . ": " . $e->getMessage());
					}
					$page_num++;
				}

				if (!$found_images) { // Si ImageMagick no generó ninguna imagen
					error_log("ImageMagick no generó ninguna imagen a partir del PDF: " . $uploaded_file_path);
					header('Content-Type: application/json');
					echo json_encode(["type" => "error", "msg" => "Error: ImageMagick no pudo generar imágenes a partir del PDF. El PDF podría estar corrupto o vacío."]);
					if (file_exists($uploaded_file_path)) {
						unlink($uploaded_file_path);
					}
					exit;
				}
				
				// Si el texto OCR extraído es suficiente, lo usamos
				if (!empty(trim($full_ocr_text)) && strlen(trim($full_ocr_text)) >= 50) {
					$text_content_for_pdf = $full_ocr_text;
				} else {
					error_log("El texto de OCR (después de convertir a imágenes) es demasiado corto o vacío.");
					// Si el OCR no produjo texto legible, informa un error.
					header('Content-Type: application/json');
					echo json_encode(["type" => "error", "msg" => "No se pudo extraer texto legible del PDF después de la conversión a imagen y OCR. El contenido podría ser ilegible o el OCR falló."]);
					// Limpia TODAS las imágenes temporales y el PDF subido.
					// foreach ($image_files_to_clean as $img) { // <--- Corregido: usa image_files_to_clean
						// if (file_exists($img)) unlink($img);
					// }
					if (file_exists($uploaded_file_path)) {
						unlink($uploaded_file_path);
					}
					exit;
				}
				
				// --- FIN DE LA LÓGICA DE CONVERSIÓN DE PDF A IMAGEN Y OCR ---

				//Limpiar TODAS las imágenes temporales generadas por ImageMagick
				// foreach ($image_files_to_clean as $img_file) { // <--- Corregido: usa image_files_to_clean
					// if (file_exists($img_file)) {
						// unlink($img_file);
						// error_log("Eliminada imagen temporal: " . $img_file);
					// }
				// }
			} // Cierre del if (strlen(trim($text_content_for_pdf)) < 50)
		} // Cierre del else (para PDF)
        
        // Call the main extraction router function with appropriate parameters
        if ($file_type === 'txt') {
            $extracted_data = $this->extract_data_from_text('', $client_name, $uploaded_file_path);
			//print_r($extracted_data);
             // Pass empty string for $text as it's not used for TXT, but $file_path_for_txt is.
        } else { // If it's a PDF
            $extracted_data = $this->extract_data_from_text($text_content_for_pdf, $client_name);
             // Pass the extracted text content from PDF processing.
        }

		// Delete the file after processing
		// if (file_exists($uploaded_file_path)) {
			// unlink($uploaded_file_path);
		// }

		$this->generate_excel($extracted_data, $client_name);
	}
	
	private function generate_excel($all_data, $client_name = '') {
		set_time_limit(0);
		ini_set("memory_limit", -1);
		// Cargar plantilla vacía
        $template_path = './template/purchase_order_template.xlsx';
        if (!file_exists($template_path)) {
            echo "Error: No se encontró la plantilla de Excel.";
            return;
        }
		
		$spreadsheet = IOFactory::load($template_path);
        //$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Estilos para los encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FF4F81BD']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ];
        $sheet->getStyle('A1:AI1')->applyFromArray($headerStyle);
		
		// Define el estilo para las celdas de datos
        $dataBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'], // Negro
                ],
            ],
        ];
        // --- Llenar datos ---
        $row = 2;
		$total_sum = 0;
		foreach ($all_data as $data){
			if (!empty($data['items'])) {
				foreach ($data['items'] as $item) {
					$sheet->setCellValue('A' . $row, $data['customer_po_no'] ?? '');
					$sheet->setCellValue('B' . $row, $data['ship_to'] ?? '');
					$sheet->setCellValue('C' . $row, $item['currency'] ?? '');
					$sheet->setCellValue('D' . $row, $data['request_arrival_date'] ?? '');
					$sheet->setCellValue('E' . $row, $item['modelo'] ?? '');
					$sheet->setCellValue('F' . $row, $item['qty'] ?? '');
					$sheet->setCellValue('G' . $row, $item['unit_selling_price'] ?? '');

					$sheet->setCellValue('H' . $row, $data['warehouse'] ?? '');
					$sheet->setCellValue('I' . $row, $data['payterm'] ?? '');
					$sheet->setCellValue('J' . $row, $item['shipping_remark'] ?? '');
					$sheet->setCellValue('K' . $row, $item['invoice_remark'] ?? '');
					$sheet->setCellValue('L' . $row, $data['request_arrival_date'] ?? '');
					$sheet->setCellValue('M' . $row, $data['customer_po_date'] ?? '');
					
					$sheet->setCellValue('N' . $row, $item['h_flag'] ?? '');
					$sheet->setCellValue('O' . $row, $item['op_code'] ??  '');
					$sheet->setCellValue('P' . $row, $item['country'] ??  '');
					$sheet->setCellValue('Q' . $row, $item['postal_code'] ?? '');
					$sheet->setCellValue('R' . $row, $item['address1'] ?? '');
					$sheet->setCellValue('S' . $row, $data['address2'] ?? '');
					$sheet->setCellValue('T' . $row, $item['address3'] ?? '');
					$sheet->setCellValue('U' . $row, $data['address4'] ?? '');
					
					$sheet->setCellValue('V' . $row, $item['city'] ?? '');
					$sheet->setCellValue('W' . $row, $item['state'] ??  '');
					$sheet->setCellValue('X' . $row, $item['province'] ??  '');
					$sheet->setCellValue('Y' . $row, $item['county'] ?? '');
					$sheet->setCellValue('Z' . $row, $item['consumer_name'] ?? '');
					$sheet->setCellValue('AA' . $row, $data['comsumer_phono_no'] ?? '');
					$sheet->setCellValue('AB' . $row, $item['receiver_name'] ?? '');
					$sheet->setCellValue('AC' . $row, $data['receiver_phono_no'] ?? '');
					$sheet->setCellValue('AD' . $row, $data['freight_charge'] ?? '');
					$sheet->setCellValue('AE' . $row, $data['freight_term'] ?? '');
					$sheet->setCellValue('AF' . $row, $data['price_condition'] ?? '');
					$sheet->setCellValue('AG' . $row, $data['picking_remark'] ?? '');
					$sheet->setCellValue('AH' . $row, $data['shipping_method'] ?? '');
					$sheet->setCellValue('AI' . $row, $data['results'] ?? '');
					// APLICAR BORDES A LA FILA ACTUAL
					$sheet->getStyle('A' . $row . ':AI' . $row)->applyFromArray($dataBorderStyle);
					
					//$total_sum += $item['total value'];
					$row++;
				}
				
			} else {
				// Si no se extrajeron ítems, solo se llenan los datos de cabecera y cliente en una fila
				$sheet->setCellValue('A' . $row, $data['date'] ?? 'N/A');
				$sheet->setCellValue('B' . $row, $data['boleta'] ?? 'N/A');
				$sheet->setCellValue('C' . $row, $data['ruc_proveedor'] ?? 'N/A');
				$sheet->setCellValue('D' . $row, $data['razon_social_proveedor'] ?? 'N/A');
				$sheet->setCellValue('E' . $row, $data['ruc_cliente'] ?? 'N/A');
				$sheet->setCellValue('F' . $row, $data['razon_social_cliente'] ?? 'N/A');
				
				//$sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray($dataBorderStyle);
					
				$row++;
			}
		}
		
		
        foreach (range('A', 'AI') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
		
		$customer_po_no = $all_data[0]['customer_po_no'] ?? 'N/A';
		log_message('info', "customer name: " . $client_name);
		
		//Limpiar el nombre del cliente para que sea seguro en nombres de archivo (ej. reemplazar espacios y caracteres especiales)
		$clean_client_name = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', strtoupper($client_name)));
		
		// Limpiar el customer_po_no para que sea seguro en nombres de archivo
		$clean_po_no = preg_replace('/[^a-zA-Z0-9_]/', '', $customer_po_no);

		$current_date_time = date('Ymd_Hi'); // Formato YYYYMMDD_HHMM (sin segundos)

		$filename = "{$clean_client_name}_{$clean_po_no}_Result_{$current_date_time}.xlsx";
		// Si $clean_client_name o $clean_po_no están vacíos, podrías tener un fallback.
		if (empty($clean_client_name) || empty($clean_po_no) || $clean_po_no === 'N_A') {
			$filename = "Extracted_Order_Result_{$current_date_time}.xlsx"; // Un nombre genérico de fallback
		}
		
        //$filename = 'boleta_extraida_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
	
	public function upload_saga() { // 
		$txt1_file_name = 'extract_file_eoc_' . uniqid(); 
		$txt2_file_name = 'extract_file_eod_' . uniqid();

		// 1. Subir el primer archivo TXT
		$txt1_upload_result = $this->_do_upload_file('attach_txt1', 'txt', $txt1_file_name);
		if ($txt1_upload_result['status'] !== 'success') {
			return ['type' => 'error', 'msg' => 'Error al subir el archivo EOC TXT: ' . $txt1_upload_result['msg']];
		}
		$txt1_path = $txt1_upload_result['file_path'];

		// 2. Subir el segundo archivo TXT
		$txt2_upload_result = $this->_do_upload_file('attach_txt2', 'txt', $txt2_file_name);
		if ($txt2_upload_result['status'] !== 'success') {
			// Asegúrate de limpiar el primer archivo subido si el segundo falla
			if (file_exists($txt1_path)) {
				unlink($txt1_path);
				error_log("Eliminado archivo temporal: " . $txt1_path . " debido a fallo en la segunda subida.");
			}
			return ['type' => 'error', 'msg' => 'Error al subir el archivo EOD TXT: ' . $txt2_upload_result['msg']];
		}
		$txt2_path = $txt2_upload_result['file_path'];

		// 3. Preparar las rutas para la extracción
		$file_paths = [$txt1_path, $txt2_path];
		error_log("Archivos TXT subidos exitosamente: " . implode(", ", $file_paths));

		// 4. Extraer los datos usando la función de enrutamiento
		$extracted_data = $this->extract_data_from_text('', $client_name, $file_paths);

		// Si extract_data_from_text devuelve un error (ej. si falta algo para SAGA)
		if (isset($extracted_data['type']) && $extracted_data['type'] === 'error') {
			// Limpia los archivos temporales subidos
			if (file_exists($txt1_path)) unlink($txt1_path);
			if (file_exists($txt2_path)) unlink($txt2_path);
			return $extracted_data; // Devuelve el error directamente
		}

		// 5. Generar el archivo Excel
		$excel_file_path = $this->generate_excel($extracted_data);

		// 6. Limpiar los archivos TXT temporales después de procesar
		if (file_exists($txt1_path)) {
			unlink($txt1_path);
			error_log("Eliminado archivo TXT temporal: " . $txt1_path);
		}
		if (file_exists($txt2_path)) {
			unlink($txt2_path);
			error_log("Eliminado archivo TXT temporal: " . $txt2_path);
		}

		// 7. Retornar el resultado para la función `upload` principal
		if ($excel_file_path && file_exists($excel_file_path)) {
			$msg = "Datos extraídos y Excel generado exitosamente.";
			$url = base_url('uploads/exports/') . basename($excel_file_path); // Asegúrate de que 'uploads/exports/' sea la ruta correcta
			return ['type' => 'success', 'msg' => $msg, 'url' => $url];
		} else {
			$msg = "Error al generar el archivo Excel.";
			error_log("Error: generate_excel no devolvió una ruta válida o el archivo no existe.");
			return ['type' => 'error', 'msg' => $msg];
		}
	}
	
	// New version
	public function upload(){
		$type = "error";
		$msg = $url = "";

		if (!$this->session->userdata('logged_in')) {
			$msg = "Your session is finished.";
			$url = base_url();
			header('Content-Type: application/json');
			echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
			return;
		}

		$client_name = $this->input->post('client');
		$list_client_txt = ['SAGA FALABELLA S.A.'];
		$list_client_excel = ['TIENDAS PERUANAS S.A. - OESCHLE', 'TIENDAS PERUANAS S.A. - OESCHLE (Yellow)', 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA - PLAZA VEA', 'HOMECENTERS PERUANOS S.A. - PROMART', 'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.', 'HIPERMERCADOS TOTTUS S.A.'];
		if (empty($client_name)) {
			$msg = "No se proporcionó el nombre del cliente. Por favor, seleccione un cliente.";
			error_log("Error en upload: Client name not provided.");
			header('Content-Type: application/json');
			echo json_encode(["type" => $type, "msg" => $msg]);
			return;
		}

		$process_result = ['type' => 'error', 'msg' => 'No se seleccionó ningún archivo o tipo de archivo no válido.'];

		if (in_array($client_name, $list_client_txt)) {
			$process_result = $this->process_saga($client_name);
		} elseif (in_array($client_name, $list_client_excel)){
			$process_result = $this->process_excel($client_name);
		} elseif (!empty($_FILES['attach']['name'])) {
			$pdf_upload_result = $this->_do_upload_file('attach', 'pdf', 'extract_file'); // Usar nombre de archivo único o específico
			
			if ($pdf_upload_result['status'] === 'success') {
				$pdf_path = $pdf_upload_result['file_path'];
				// Lógica existente de procesamiento de PDF (Smalot/OCR)
				// (La he movido a una función auxiliar `_process_pdf_content` para mantener `process` limpia)
				$process_result = $this->_process_pdf_content($pdf_path, $client_name);

				// Limpiar el archivo PDF temporal después de procesar
				if (file_exists($pdf_path)) {
					unlink($pdf_path);
					error_log("Eliminado archivo PDF temporal: " . $pdf_path);
				}
			} else {
				$process_result = ['type' => 'error', 'msg' => 'Error al subir el archivo PDF: ' . $pdf_upload_result['msg']];
			}
		} else {
			error_log("Error en upload: Ni TXT ni PDF fueron seleccionados/subidos.");
		}
		
		// Devolver el resultado final al frontend
		header('Content-Type: application/json');
		echo json_encode($process_result); // $process_result ya tiene 'type', 'msg', 'url'
	}

	

	private function _process_pdf_content($pdf_path, $client_name) {
		// 1. Intentar extracción con Smalot (para PDF editables)
		$text_content_for_pdf = '';
		$parser = new Smalot\PdfParser\Parser();
		try {
			$pdf = $parser->parseFile($pdf_path);
			$text_raw_smalot = $pdf->getText();
			$text_content_for_pdf = $this->clean_text($text_raw_smalot);
		} catch (Exception $e) {
			error_log("Error con Smalot\\PdfParser: " . $e->getMessage());
			$text_content_for_pdf = "";
		}

		// 2. Si el texto de Smalot es corto, usar el script de Python para OCR
		if (strlen(trim($text_content_for_pdf)) < 50) {
			error_log("El texto de Smalot es demasiado corto o vacío, intentando OCR con script de Python...");

			// Prepara el comando para ejecutar el script de Python
			// NOTA: Asegúrate de que las rutas sean correctas para tu proyecto.
			// FCPATH es la raíz de tu proyecto, por ejemplo 'C:\xampp\htdocs\llamasys\'
			$python_executable = 'C:/xampp/htdocs/llamasys/application/venv/venv_po/Scripts/python.exe';
			if ($client_name === 'REPRESENTACIONES VARGAS S.A.') $python_script = 'C:/xampp/htdocs/llamasys/application/venv/venv_po/ocr_script.py';
			elseif ($client_name === 'REPRESENTACIONES VARGAS S.A. (Bold Format)') $python_script = 'C:/xampp/htdocs/llamasys/application/venv/venv_po/ocr_bold_script.py';
			
			$command = escapeshellcmd("$python_executable $python_script " . escapeshellarg($pdf_path));

			// Ejecutar el script y capturar la salida JSON
			$json_output = shell_exec($command);
			$python_result = json_decode($json_output, true);

			// Manejar el resultado del script de Python
			if (isset($python_result['status']) && $python_result['status'] === 'success') {
				$text_content_for_pdf = $this->clean_text($python_result['full_text']);
			} else {
				error_log("El script de Python falló: " . ($python_result['message'] ?? 'Error desconocido'));
				return ['type' => 'error', 'msg' => 'Error al procesar el PDF con Python: ' . ($python_result['message'] ?? 'Error desconocido')];
			}
		}

		// 3. Si no hay texto extraído en ninguno de los dos intentos, devolver un error
		if (empty(trim($text_content_for_pdf)) || strlen(trim($text_content_for_pdf)) < 50) {
			error_log("No se pudo extraer texto legible del PDF.");
			return ['type' => 'error', 'msg' => 'No se pudo extraer texto legible del PDF.'];
		}

		// 4. Continuar con el flujo original: extraer datos del texto
		$extracted_data = $this->extract_data_from_text($text_content_for_pdf, $client_name);

		if (isset($extracted_data['type']) && $extracted_data['type'] === 'error') {
			return $extracted_data;
		}

		// 5. Generar el Excel con los datos
		$excel_file_path = $this->generate_excel($extracted_data, $client_name);

		if ($excel_file_path && file_exists($excel_file_path)) {
			return ['type' => 'success', 'msg' => 'Datos extraídos y Excel generado exitosamente.', 'url' => base_url('uploads/exports/') . basename($excel_file_path)];
		} else {
			error_log("Error: generate_excel no devolvió una ruta válida o el archivo no existe para PDF.");
			return ['type' => 'error', 'msg' => 'Error al generar el archivo Excel para PDF.'];
		}
	}

	// --------------------SAGA PROCESS-------------------------
	private function process_saga($client_name) {
		// Definimos nombres de archivo temporales con un ID único para evitar colisiones
		$txt1_file_name_prefix = 'extract_file_eoc'; 
		$txt2_file_name_prefix = 'extract_file_eod';

		// 1. Subir el primer archivo TXT (attach_txt1, debería ser EOC)
		$txt1_upload_result = $this->_do_upload_file('attach_txt1', 'txt', $txt1_file_name_prefix);
		if ($txt1_upload_result['status'] !== 'success') {
			return ['type' => 'error', 'msg' => 'Error uploading EOC TXT file: ' . $txt1_upload_result['msg']];
		}
		$txt1_path = $txt1_upload_result['file_path'];
		$txt1_original_name = $_FILES['attach_txt1']['name']; // Capturamos el nombre original para validación

		// 2. Subir el segundo archivo TXT (attach_txt2, debería ser EOD)
		$txt2_upload_result = $this->_do_upload_file('attach_txt2', 'txt', $txt2_file_name_prefix);
		if ($txt2_upload_result['status'] !== 'success') {
			// Asegúrate de limpiar el primer archivo subido si el segundo falla
			if (file_exists($txt1_path)) {
				unlink($txt1_path);
				error_log("Eliminado archivo temporal: " . $txt1_path . " debido a fallo en la segunda subida.");
			}
			return ['type' => 'error', 'msg' => 'Error uploading EOD TXT file: ' . $txt2_upload_result['msg']];
		}
		$txt2_path = $txt2_upload_result['file_path'];
		$txt2_original_name = $_FILES['attach_txt2']['name']; // Capturamos el nombre original para validación

		// --- Validación 1: Códigos Numéricos en Nombres de Archivo ---
		$code1 = $this->extract_code_from_filename($txt1_original_name);
		$code2 = $this->extract_code_from_filename($txt2_original_name);

		if ($code1 === null || $code2 === null || $code1 !== $code2) {
			error_log("Error de validación de nombres de archivo TXT (códigos no coinciden): Código 1: '{$code1}', Código 2: '{$code2}'. Nombres: '{$txt1_original_name}', '{$txt2_original_name}'");
			if (file_exists($txt1_path)) unlink($txt1_path);
			if (file_exists($txt2_path)) unlink($txt2_path);
			return ['type' => 'error', 'msg' => 'The numeric codes in the TXT file names do not match or the format is incorrect.'];
		}

		// --- Validación 2: Tipos de Archivo (EOC/EOD) en Nombres de Archivo ---
		$type1 = $this->extract_file_type_from_filename($txt1_original_name);
		$type2 = $this->extract_file_type_from_filename($txt2_original_name);

		// attach_txt1 debería ser EOC
		if ($type1 === null || $type1 !== 'eoc') {
			error_log("Error de validación de tipo de archivo: Archivo EOC incorrecto para attach_txt1. Tipo detectado: '{$type1}'");
			if (file_exists($txt1_path)) unlink($txt1_path);
			if (file_exists($txt2_path)) unlink($txt2_path);
			return ['type' => 'error', 'msg' => 'The first TXT file (EOC) name is incorrect or does not specify "eoc".'];
		}

		// attach_txt2 debería ser EOD
		if ($type2 === null || $type2 !== 'eod') {
			error_log("Error de validación de tipo de archivo: Archivo EOD incorrecto para attach_txt2. Tipo detectado: '{$type2}'");
			if (file_exists($txt1_path)) unlink($txt1_path);
			if (file_exists($txt2_path)) unlink($txt2_path);
			return ['type' => 'error', 'msg' => 'The second TXT file (EOD) name is incorrect or does not specify "eod".'];
		}

		error_log("Archivos TXT subidos exitosamente y validados por código y tipo.");

		// 3. Preparar las rutas para la extracción
		$file_paths = [$txt1_path, $txt2_path];
		error_log("Archivos TXT subidos exitosamente y validados por código: " . implode(", ", $file_paths) . " (Código: " . $code1 . ")");

		// 4. Extraer los datos usando la función de enrutamiento
		$extracted_data = $this->extract_data_from_text('', $client_name, $file_paths);

		// Si extract_data_from_text devuelve un error (ej. si falta algo para SAGA)
		if (isset($extracted_data['type']) && $extracted_data['type'] === 'error') {
			// Limpia los archivos temporales subidos
			if (file_exists($txt1_path)) unlink($txt1_path);
			if (file_exists($txt2_path)) unlink($txt2_path);
			return $extracted_data; 
		}

		// 5. Generar el archivo Excel
		if ($client_name === 'HIPERMERCADOS TOTTUS S.A.') $excel_file_path = $this->generate_excel($extracted_data, "TOTTUS_CUSTOMER");
		elseif ($client_name === 'SAGA FALABELLA S.A.') $excel_file_path = $this->generate_excel($extracted_data, "SAGA_CUSTOMER");

		// 6. Limpiar los archivos TXT temporales después de procesar
		if (file_exists($txt1_path)) {
			unlink($txt1_path);
			error_log("Eliminado archivo TXT temporal: " . $txt1_path);
		}
		if (file_exists($txt2_path)) {
			unlink($txt2_path);
			error_log("Eliminado archivo TXT temporal: " . $txt2_path);
		}

		// 7. Retornar el resultado para la función `upload` principal
		if ($excel_file_path && file_exists($excel_file_path)) {
			$msg = "Data extracted and Excel file generated successfully.";
			$url = base_url('uploads/exports/') . basename($excel_file_path);
			return ['type' => 'success', 'msg' => $msg, 'url' => $url];
		} else {
			$msg = "Error generating the Excel file.";
			error_log("Error: generate_excel no devolvió una ruta válida o el archivo no existe.");
			return ['type' => 'error', 'msg' => $msg];
		}
	}
	
	private function _do_upload_file($input_name, $allowed_type, $file_name_prefix) {
		$config = [
			'upload_path'   => './upload/',
			'allowed_types' => $allowed_type,
			'max_size'      => 10000,
			'overwrite'     => TRUE, // Esto podría ser un problema si usas nombres fijos sin uniqid()
			'file_name'     => $file_name_prefix, // Se usará para el nombre base, uniqid se le añade en `upload_and_process_txt`
		];
		$this->load->library('upload', $config);
		$this->upload->initialize($config); // Es buena práctica reinicializar
		
		if (!$this->upload->do_upload($input_name)) {
			return ['status' => 'error', 'msg' => $this->upload->display_errors('', '')];
		} else {
			$upload_data = $this->upload->data();
			return ['status' => 'success', 'file_path' => $upload_data['full_path'], 'msg' => 'Archivo subido.'];
		}
	}
	
	// --------------------EXCEL PROCESS------------------------
	private function process_excel($client_name){
		// 1. Subir el primer archivo TXT (attach_txt1, debería ser EOC)
		$result = $this->upload_excel('attach');
		if ($result['status'] !== 'success') {
			return ['type' => 'error', 'msg' => 'Error uploading file: ' . $result['msg']];
		}
		$excel_path = $result['file_path'];
		$excel_original_name = $_FILES['attach']['name']; // Capturamos el nombre original para validación
		
		$extracted_data = $this->extract_data_from_text('', $client_name, $excel_path, $excel_original_name);	 // function to handle data
		
		// if ($client_name === 'HIPERMERCADOS TOTTUS S.A.') {
			// $excel_file_path = $this->generate_excel_alter($extracted_data, "{$client_name}_CUSTOMER");
		// } else $excel_file_path = $this->generate_excel($extracted_data, "{$client_name}_CUSTOMER");  // generate excel
		
		$excel_file_path = $this->generate_excel($extracted_data, "{$client_name}_CUSTOMER");
		// 7. Retornar el resultado para la función `upload` principal
		if ($excel_file_path && file_exists($excel_file_path)) {
			$msg = "Data extracted and Excel file generated successfully.";
			$url = base_url('uploads/exports/') . basename($excel_file_path);
			return ['type' => 'success', 'msg' => $msg, 'url' => $url];
		} else {
			$msg = "Error generating the Excel file.";
			error_log("Error: generate_excel no devolvió una ruta válida o el archivo no existe.");
			return ['type' => 'error', 'msg' => $msg];
		}
	}
	
	public function upload_excel($input_name){
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 90000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'extract_excel.xlsx',
		];
		$this->load->library('upload', $config);
		$this->upload->initialize($config); // Es buena práctica reinicializar
		
		if (!$this->upload->do_upload($input_name)) {
			return ['status' => 'error', 'msg' => $this->upload->display_errors('', '')];
		} else {
			$upload_data = $this->upload->data();
			return ['status' => 'success', 'file_path' => $upload_data['full_path'], 'msg' => 'Archivo subido.'];
		}
	}
	// ---------------------------------------------------------
	
	private function extract_code_from_filename($filename) {
		// Ejemplo: "archivo-de-prueba-12345.txt" -> 12345
		// El patrón busca:
		// ^.*- : Cualquier cosa al inicio que termine en un guion.
		// (\d+) : Un grupo de captura para uno o más dígitos (el código numérico).
		// \.txt$ : La extensión .txt al final del string.
		if (preg_match('/-(\d+)\.txt$/', $filename, $matches)) {
			return $matches[1]; // Devuelve el primer grupo de captura (los dígitos)
		}
		return null; // Si no se encuentra el patrón, devuelve null
	}
	
	private function extract_file_type_from_filename($filename) {
		$parts = explode('-', $filename);
		if (count($parts) > 1) {
			$type = strtolower(trim($parts[1])); // El segundo segmento (índice 1)
			if ($type === 'eoc' || $type === 'eod') {
				return $type;
			}
		}
		return null;
	}
}