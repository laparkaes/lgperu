<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// optional
use Smalot\PdfParser\Parser;

class Scm_gre_calculate extends CI_Controller {
	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"main" => "module/scm_gre_calculate/index",
		];
		
		$this->load->view('layout', $data);
	}
		
	public function date_convert_mm_dd_yyyy($date) {
    // Intentamos convertir con la lógica del valor numérico (excel date)
		if (is_numeric($date)) {
			// Si es un número, es probable que sea una fecha de Excel (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}
		
		// Aseguramos que la variable $date sea una cadena para DateTime::createFromFormat
		$date_str = (string) $date;
		$dateTime_dd_mmm_yy = DateTime::createFromFormat('d-M-y', $date_str);
		
		$dateTime_dd_mmm_yy = DateTime::createFromFormat('d-M-y', $date_str);
    
		// Si DateTime::createFromFormat devuelve un objeto DateTime, significa que la fecha fue parseada.
		// Para este formato, no necesitamos la comparación estricta de formato si el objeto ya es válido.
		if ($dateTime_dd_mmm_yy !== false) { // Verifica que no sea false (fallo en el parseo)
			return $dateTime_dd_mmm_yy->format('Y-m-d'); // Devuelve yyyy-mm-dd
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
			
	public function delete_temp_excel_file(){
		// error_reporting(E_ALL);
		// ini_set('display_errors', 1);
		// Asegúrate de que la petición sea POST y que el nombre del archivo se envíe.
        if ($this->input->is_ajax_request() && $this->input->post('fileName')) {
            $fileNameToDelete = $this->input->post('fileName');
            $filePath = FCPATH . 'upload_file/Scm/scm_gre_calculate/' . $fileNameToDelete;

            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    // Si se borró con éxito
                    echo json_encode(['type' => 'success', 'msg' => 'File deleted successfully.']);
                } else {
                    // Si falló el borrado (ej. problemas de permisos)
                    echo json_encode(['type' => 'error', 'msg' => 'Failed to delete file. Check permissions.']);
                }
            } else {
                // Si el archivo ya no existe (quizás ya fue borrado por un cronjob o una limpieza manual)
                echo json_encode(['type' => 'warning', 'msg' => 'File not found on server (might have been deleted already).']);
            }
        } else {
            // Si la petición no es válida (no es AJAX o falta el nombre del archivo)
            echo json_encode(['type' => 'error', 'msg' => 'Invalid request to delete file.']);
        }
        exit; // Termina la ejecución para solo enviar JSON
	}
		
	public function truncate_tables(){
		$this->gen_m->truncate("sa_calculate_promotion");
		$this->gen_m->truncate("sa_sell_in_promotion");
		$this->gen_m->truncate("sa_sell_out_promotion");
	}
		
	public function find_note_xml($invoice_data_to_search, $emp_number){
        $this->db->trans_start();    

        $base_e_documents_path = FCPATH . 'eDocuments/'; // Ruta base a la carpeta eDocuments
        $current_year = date('Y'); // Obtener el año actual dinámicamente

        // Subcarpetas específicas donde buscar los XML
        $target_subfolders = ['FACTURA_ELECTRONICA', 'NOTA_DE_CREDITO'];

        foreach ($invoice_data_to_search as $original_invoice_no => $search_value) {
            $found_delivery_note = ''; // Reset para cada factura
            $xml_found_and_processed = false; // Flag para saber si ya encontramos y procesamos el XML para esta factura

            if (empty($search_value)) {
                log_message('info', 'Skipping file search for empty/null processed invoice_no for original: ' . $original_invoice_no);
                $xml_found_and_processed = true; // No hay nada que buscar, consideramos "procesado"
            } else {
                // Iterar sobre cada mes (01 a 12)
                for ($month = 1; $month <= 12; $month++) {
                    if ($xml_found_and_processed) break; // Salir del bucle de meses si ya se encontró el XML

                    $month_padded = str_pad($month, 2, '0', STR_PAD_LEFT); // Formato MM (ej. 01, 02)
                    $month_folder_path = $base_e_documents_path . $current_year . '/' . $month_padded . '/';

                    // Verificar si la carpeta del mes existe antes de buscar dentro
                    if (!is_dir($month_folder_path)) {
                        log_message('debug', 'Monthly folder not found: ' . $month_folder_path);
                        continue; // Pasar al siguiente mes
                    }
					
                    // Iterar sobre las subcarpetas objetivo (FACTURA_ELECTRONICA, NOTA_DE_CREDITO)
                    foreach ($target_subfolders as $subfolder) {
                        if ($xml_found_and_processed) break; // Salir del bucle de subcarpetas si ya se encontró el XML

                        $target_path = $month_folder_path . $subfolder . '/';
                        $expected_file_path = $target_path . $search_value . '.xml';

                        log_message('debug', 'Attempting to find XML at: ' . $expected_file_path);

                        if (file_exists($expected_file_path)) {
                            libxml_use_internal_errors(true); // Habilitar manejo interno de errores XML
                            $xml = simplexml_load_file($expected_file_path);

                            if ($xml === false) {
                                $xml_errors = libxml_get_errors();
                                $error_msg = "XML parsing error for file {$expected_file_path}: ";
                                foreach ($xml_errors as $error) {
                                    $error_msg .= $error->message . " ";
                                }
                                log_message('error', $error_msg);
                                libxml_clear_errors();
                            } else {
                                // --- Lógica para buscar el GuiaRem dentro del XML (TU CÓDIGO EXISTENTE Y FUNCIONAL) ---
                                $ext_namespace_uri = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
                                $xml->registerXPathNamespace('ext', $ext_namespace_uri);
                                
                                $default_namespaces = $xml->getDocNamespaces(true);
                                $default_namespace_uri = isset($default_namespaces['']) ? $default_namespaces[''] : null;
                                $guia_rem_nodes = []; // Inicializar array para resultados
                                $xpath_query_used = ''; // Para fines de log

                                if ($default_namespace_uri) {
                                    $xml->registerXPathNamespace('def', $default_namespace_uri);
                                    $xpath_query_used = '//ext:UBLExtension/ext:ExtensionContent/def:CustomText/def:Text[@name="GuiaRem"]';
                                    $guia_rem_nodes = $xml->xpath($xpath_query_used);

                                    if (empty($guia_rem_nodes)) {
                                        $xpath_query_used = '//ext:UBLExtension/ext:ExtensionContent/CustomText/Text[@name="GuiaRem"]';
                                        $guia_rem_nodes = $xml->xpath($xpath_query_used);
                                    }
                                } else {
                                    $xpath_query_used = '//ext:UBLExtension/ext:ExtensionContent/CustomText/Text[@name="GuiaRem"]';
                                    $guia_rem_nodes = $xml->xpath($xpath_query_used);
                                }
                                
                                if (!empty($guia_rem_nodes)) {
                                    $found_delivery_note = (string)$guia_rem_nodes[0];
                                    log_message('info', 'Found delivery note: ' . $found_delivery_note . ' for invoice: ' . $original_invoice_no . ' in ' . $expected_file_path);
                                    $xml_found_and_processed = true; // Marcar como encontrado y procesado
                                } else {
                                    log_message('warning', 'Delivery note (GuiaRem) not found in XML: ' . $expected_file_path . ' using XPath: ' . $xpath_query_used);
                                }
                            }
                            libxml_clear_errors(); // Siempre limpiar errores después de procesar
                        } else {
                            log_message('debug', 'XML file NOT FOUND for ' . $original_invoice_no . ' in ' . $expected_file_path);
                        }
                    } // Fin del foreach ($target_subfolders)
                } // Fin del for ($month)
            }
            
            // Actualizar la base de datos con el valor encontrado (o vacío si no se encontró)
            $this->gen_m->update("scm_gre_calculate", ['invoice_no' => $original_invoice_no, 'user_pr' => $emp_number], ['delivery_note' => $found_delivery_note]);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Database transaction failed in find_note_xml.');
        }
    }
	
	public function generate_excel($emp_number){
		$data_for_export = $this->gen_m->filter('scm_gre_calculate', false, ['user_pr' => $emp_number]);

        if (empty($data_for_export)) {
            log_message('warning', 'No data generated for export for user: ' . $emp_number);
            return false; // Indica que no hay datos para generar
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		
		$directory = "./upload_file/Scm/scm_gre_calculate/";
		$files = scandir($directory);
		
		// Filtrar archivos que sean Excel (.xlsx o .xls)
		$excel_files = array_filter($files, function ($file) {
			return preg_match('/\.(xlsx|xls)$/i', $file);
		});
		
		if (!empty($excel_files)) {
			$excel_file = trim(array_values($excel_files)[0]); // Obtener el primer archivo encontrado
			$original_spreadsheet = IOFactory::load($directory . $excel_file);
			 // Eliminar el archivo después de cargarlo
			unlink($directory . $excel_file); // Borra el archivo de la carpeta
		} else {
			echo json_encode(["type" => "error", "msg" => "No se encontró ningún archivo Excel en la carpeta."]);
			return;
		}
		
        $output_headers_map = [
            'status' => 'Status',
            'ar_comment' => 'AR comment',
            'invoice_no' => 'Invoice No.',
            'ar_class' => 'AR Class',
            'ar_type' => 'AR Type',
            'trx_date' => 'Trx Date',
            'due_date' => 'Due Date',
            'due_date_month' => 'Due date Month',
            'month' => 'MONTH',
            'currency' => 'Currency',
            'original_amount_entered_curr' => 'Original Amount (Entered Curr.)',
            'bill_to_code' => 'Bill To Code',
            'bill_to_name' => 'Bill To Name',
            'po_no' => 'PO No',
            'status_may' => 'Status May',
            'delivery_note' => 'Delivery Note',
        ];

        $final_excel_headers = array_values($output_headers_map);
        $sheet->fromArray($final_excel_headers, NULL, 'A1');

        $row_index = 2;
        foreach ($data_for_export as $row) {
            $row_array = (array) $row;
            $output_row_values = [];
            foreach (array_keys($output_headers_map) as $db_col) {
                $output_row_values[] = $row_array[$db_col] ?? ''; 
            }
            $sheet->fromArray($output_row_values, NULL, 'A' . $row_index);
            $row_index++;
        }

        log_message('info', 'Spreadsheet object created successfully for user: ' . $emp_number);
        return $spreadsheet; // Devuelve el objeto Spreadsheet
	}
	
	public function downloadSpreadsheet($spreadsheet, $file_name_param) {
        // Asegúrate de que el nombre del archivo es seguro y tiene la extensión correcta.
        $file_name = $file_name_param; 
        if (pathinfo($file_name, PATHINFO_EXTENSION) !== 'xlsx') {
            $file_name .= '.xlsx'; // Asegura que tenga la extensión .xlsx
        }
         
        // Podrías usar 'FCPATH . 'temp_exports/' como en la discusión anterior para los CSV.
        $upload_dir = FCPATH . 'upload_file/Scm/scm_gre_calculate/'; 
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $save_path = $upload_dir . $file_name;
		
		log_message('debug', 'downloadSpreadsheet: Ruta completa de guardado: ' . $save_path);
		if (!is_writable(dirname($save_path))) {
			log_message('error', 'downloadSpreadsheet: La carpeta de destino NO ES ESCRIBIBLE: ' . dirname($save_path));
		} else {
			log_message('debug', 'downloadSpreadsheet: La carpeta de destino ES ESCRIBIBLE: ' . dirname($save_path));
		}
		
        try {
            log_message('debug', 'downloadSpreadsheet: Antes de guardar el archivo.');
			$writer = new Xlsx($spreadsheet);
			$writer->save($save_path);
			log_message('debug', 'downloadSpreadsheet: Archivo guardado con éxito.');


            $response = [
                'type' => 'success',
                'msg' => 'Report generated successfully!',
                'downloadUrl' => base_url(str_replace(FCPATH, '', $save_path)), // Genera la URL relativa desde base_url
                'fileNameOnServer' => $file_name // Nombre del archivo generado para borrarlo después
            ];
            
            header('Content-Type: application/json'); 
            echo json_encode($response);
            
        } catch (Exception $e) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $response = [
                'type' => 'error',
                'msg' => 'Error generating report: ' . $e->getMessage(),
                'downloadUrl' => null,
                'fileNameToDelete' => null
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        exit;
    }
	
	public function export_excel(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		$emp_number = $this->session->userdata('employee_number');
		
		$selected_files_json = $this->input->post('files'); 
        $selected_files = json_decode($selected_files_json, true);

        $selected_encrypted_file_name = !empty($selected_files) ? $selected_files[0] : null;
        $uploaded_file_full_path = './upload_file/Scm/scm_gre_calculate/' . $selected_encrypted_file_name;
		
		if (empty($emp_number)) {
            log_message('error', 'User not logged in during export_excel attempt (AJAX).');
            // Enviar respuesta JSON de error directamente
            echo json_encode(['type' => 'error', 'msg' => 'User not authenticated or session expired.']);
            exit(); // Es crucial salir aquí para no continuar
        }

        if (empty($selected_encrypted_file_name) || !file_exists($uploaded_file_full_path)) {
            log_message('error', 'No valid file selected for export_excel (AJAX) or file not found: ' . $selected_encrypted_file_name);
            echo json_encode(['type' => 'error', 'msg' => 'No file selected or file does not exist for processing.']);
            exit(); // Es crucial salir aquí
        }
		
		$invoice = [];
		$invoice_no = $this->gen_m->filter_select('scm_gre_calculate', false, 'invoice_no', ['user_pr' => $emp_number]);
		foreach($invoice_no as $item){
			if ($item->invoice_no === "(blank)" || empty($item->invoice_no) || $item->invoice_no === "") {
				$invoice[$item->invoice_no] = null;
			}else {
				$aux = explode("-", $item->invoice_no);
				$invoice[$item->invoice_no] = $aux[1] . "-" . $aux[2];
			}
		}
			
		//echo '<pre>'; print_r($invoice);
		
		$this->find_note_xml($invoice, $emp_number);
		
		$spreadsheet_object = $this->generate_excel($emp_number);
		
		if ($spreadsheet_object) {
            // --- NUEVA LÓGICA PARA EL NOMBRE DEL ARCHIVO GENERADO ---
            // 1. Obtener el nombre del archivo original (sin la extensión .xlsx si ya la tiene)
            // Asumo que selected_encrypted_file_name es el nombre del archivo en el servidor,
            // que puede tener la extensión original. Necesitamos el "nombre base" para el prefijo.
            $original_file_name_without_ext = pathinfo($selected_encrypted_file_name, PATHINFO_FILENAME);
            
            // 2. Construir el nuevo nombre con el prefijo y la extensión .xlsx
            $excel_file_name = "[Process] " . $original_file_name_without_ext . ".xlsx";
            // --------------------------------------------------------
			//echo '<pre>'; print_r($excel_file_name);
			//log_message('info', 'excel_file_name: ' . $excel_file_name);
            $this->downloadSpreadsheet($spreadsheet_object, $excel_file_name);
            
            if (file_exists($uploaded_file_full_path)) {
                unlink($uploaded_file_full_path);
                log_message('info', 'Deleted uploaded file: ' . $selected_encrypted_file_name);
            }

        } else {
            echo json_encode(['type' => 'error', 'msg' => 'Failed to generate the Excel report or no data available.']);
            exit(); 
        }
		//$this->generate_excel($emp_number);
	
	}
	
	public function process($file_ext){	
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		$user_name = $this->session->userdata('name');
		$emp_number = $this->session->userdata('employee_number');
		
		
		$this->gen_m->truncate("scm_gre_calculate");
	
		//load excel file
		$spreadsheet = IOFactory::load("./upload/scm_gre_calculate." . $file_ext);
		$sheet = $spreadsheet->getActiveSheet(0);
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue()),
			trim($sheet->getCell('J1')->getValue()),
			trim($sheet->getCell('K1')->getValue()),
		];
		//print_r($h); return;
		$h_origin = ["Status", "AR comment", "Invoice No.", "AR Class", "AR Type", "Trx Date", "Due Date", "Due date Month", "MONTH", "Currency", "Original Amount (Entered Curr.)"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		if ($header_validation){
			$max_row = $sheet->getHighestRow();
			$batch_size = 100;
			$rows = $date_arr = [];
			$updated = date("Y-m-d H:i:s");
			//save file records in array
			for ($i = 2; $i < $max_row; $i++) {
				//if ($is_empty_row) continue; // Si la fila está vacía, detenemos el bucle en esta hoja
				$row = [
					"user_pr"							=> $emp_number,
					"status" 							=> trim($sheet->getCell('A'.$i)->getValue()),
					"ar_comment" 						=> trim($sheet->getCell('B'.$i)->getValue()),					
					"invoice_no" 						=> trim($sheet->getCell('C'.$i)->getValue()),
					"ar_class" 							=> trim($sheet->getCell('D'.$i)->getValue()),
					"ar_type" 							=> trim($sheet->getCell('E'.$i)->getValue()),
					"trx_date" 							=> trim($sheet->getCell('F'.$i)->getValue()),
					"due_date" 							=> trim($sheet->getCell('G'.$i)->getValue()),					
					"due_date_month" 					=> trim($sheet->getCell('H'.$i)->getValue()),
					"month" 							=> trim($sheet->getCell('I'.$i)->getValue()),
					"currency" 							=> trim($sheet->getCell('J'.$i)->getValue()),
					"original_amount_entered_curr" 		=> trim($sheet->getCell('K'.$i)->getValue()),
					"bill_to_code" 						=> trim($sheet->getCell('L'.$i)->getValue()),
					"bill_to_name" 						=> trim($sheet->getCell('M'.$i)->getValue()),
					"po_no" 							=> trim($sheet->getCell('N'.$i)->getValue()),
					"status_may" 						=> trim($sheet->getCell('O'.$i)->getValue()),
					"delivery_note" 					=> null,
					"upload_date" 						=> $updated
				];
				
				$row['trx_date'] = $this->date_convert_mm_dd_yyyy($row['trx_date']);
				$row['due_date'] = $this->date_convert_mm_dd_yyyy($row['due_date']);
				
				$batch_data[] = $row;
				
				// Inserción por lotes
				if (count($batch_data) >= $batch_size) {
					$this->gen_m->insert_m("scm_gre_calculate", $batch_data);
					$batch_data = [];
				}
			}
			//echo '<pre>'; print_r($batch_data);
			// Insertar los datos restantes en el lote
			//echo '<pre>'; print_r($batch_data); return;
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("scm_gre_calculate", $batch_data);
			}
		
			
			//$this->db->trans_complete();
			
			// Obtener el nombre original del archivo usando $_FILES
			$originalFileName = $_FILES['attach']['name'];
			
			// Construir la ruta del archivo temporal
			$tempFilePath = './upload_file/Scm/scm_gre_calculate/' . $originalFileName;

			// Guardar el archivo Excel en la carpeta temporal con el nombre original
			$file_ext = ucfirst($file_ext);
			$writer = IOFactory::createWriter($spreadsheet, $file_ext);
			$writer->save($tempFilePath);
		
			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
			return $msg;
		}else return "";
	}
	
	public function get_uploaded_files() {
		$folder_path = './upload_file/Scm/scm_gre_calculate/';
		$files = [];

		if (is_dir($folder_path)) {
			foreach (scandir($folder_path) as $file) {
				 if ($file === "." || $file === "..") {
                    continue;
                }

                // Obtener la extensión del archivo
                $file_extension = pathinfo($file, PATHINFO_EXTENSION);

                if (strpos($file, '[Process]') === 0) {
                    continue; // Saltar este archivo y pasar al siguiente
                }

                // Incluir solo archivos .xlsx o .xls
                if ($file_extension === "xlsx" || $file_extension === "xls") {
                    $files[] = $file;
                }
			}
		}

		header('Content-Type: application/json');
		echo json_encode($files);
	}

	public function upload(){
		$type = "error"; $msg = "";
		$url = "";
		
		if ($this->session->userdata('logged_in')){
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'scm_gre_calculate',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$result = $this->upload->data();
				//$type = "success";
				if ($result['file_ext'] === '.xlsx') $file_ext = 'xlsx';
				elseif ($result['file_ext'] === '.xls') $file_ext = 'xls';
				$msg = $this->process($file_ext);
				//$url = base_url()."upload/scm_gre_calculate.xlsx";
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else{
			$msg = "Your session is finished.";
			//$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}

	function findRemisionBySerie(string $basePath = 'eDocuments/2025/', string $targetDir = 'GUIA_DE_REMISION', string $searchSerie = '406PNWK5T953', string $remisionPattern = '/(T\d{3}-\d{8})/'){ // PDF
		$resultados = [];
		$parser = new Parser();

		// 1. Recorrer las carpetas de los meses (01 a 12)
		for ($month = 1; $month <= 12; $month++) {
			$monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
			
			// Define la ruta completa hasta la carpeta GUIA_DE_REMISION
			$remisionDir = $basePath . $monthStr . DIRECTORY_SEPARATOR . $targetDir;
			
			// Verificar si la carpeta GUIA_DE_REMISION existe
			if (!is_dir($remisionDir)) {
				// echo "Saltando: Directorio $remisionDir no encontrado.\n";
				continue;
			}

			echo "🔍 Analizando directorio: " . $monthStr . DIRECTORY_SEPARATOR . $targetDir . "\n";
			
			// 2. Recorrer SOLO los archivos dentro de la carpeta GUIA_DE_REMISION
			try {
				$iterator = new DirectoryIterator($remisionDir);
				
				foreach ($iterator as $fileInfo) {
					// Saltar '.' y '..' y verificar que sea un archivo con extensión .pdf
					if ($fileInfo->isDot() || !$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'pdf') {
						continue;
					}
					
					$pdfPath = $fileInfo->getPathname();
					
					try {
						// a. Extraer el texto del PDF
						$pdf = $parser->parseFile($pdfPath);
						$textContent = $pdf->getText();

						// b. Buscar la serie (patrón de texto simple)
						if (strpos(strtolower($textContent), strtolower($searchSerie)) !== false) {
							
							// c. Serie encontrada: extraer la Guía de Remisión (T101-00023350)
							if (preg_match($remisionPattern, $textContent, $matches)) {
								$guiaRemision = $matches[1];
								
								$resultados[] = [
									'archivo' => $fileInfo->getFilename(),
									'ruta' => $pdfPath,
									'guia_remision' => $guiaRemision,
									'serie_buscada' => $searchSerie
								];
								
								echo "  ✅ Éxito en **" . $fileInfo->getFilename() . "**: Guía extraída: **$guiaRemision**\n";
								
							} else {
								echo "  ⚠️ Serie encontrada en " . $fileInfo->getFilename() . ", pero no se pudo extraer la Guía de Remisión.\n";
							}
						} 
						
					} catch (\Exception $e) {
						echo "  ❌ Error al procesar PDF " . $fileInfo->getFilename() . ": " . $e->getMessage() . "\n";
					}
				}

			} catch (\Exception $e) {
				echo "Error al recorrer el directorio $remisionDir: " . $e->getMessage() . "\n";
			}
		}

		return $resultados;
	}
	
	function findRemisionBySerie_xml(string $basePath = 'eDocuments/2025/', string $targetDir = 'GUIA_DE_REMISION', string $searchSerie = '406PNWK5T953'){ // XML
		$resultados = [];

		// --- Definición de Variables Locales Fijas (¡Solución al error de alcance!) ---
		$namespaceCbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
		$namespaceCac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
		$xpathGuiaRemisionId = '//cbc:ID'; 
		$xpathDescriptions = '//cac:Item/cbc:Description'; 

		// 1. Recorrer las carpetas de los meses (01 a 12)
		for ($month = 1; $month <= 12; $month++) {
			$monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
			$remisionDir = $basePath . $monthStr . DIRECTORY_SEPARATOR . $targetDir;
			
			if (!is_dir($remisionDir)) continue;

			echo "🔍 Analizando directorio: " . $monthStr . DIRECTORY_SEPARATOR . $targetDir . "\n";
			
			try {
				// Usa glob() para obtener solo archivos .xml
				$xmlFiles = glob($remisionDir . DIRECTORY_SEPARATOR . '*.xml');

				foreach ($xmlFiles as $xmlPath) {
					$fileName = basename($xmlPath);
					
					try {
						// --- Lógica de Carga y Limpieza de XML ---
						$xmlContent = file_get_contents($xmlPath);

						if ($xmlContent === false) continue;
						
						// a) Eliminar la declaración problemática: xmlns:schemaLocation="..."
						$xmlContentCleaned = preg_replace(
							'/xmlns:schemaLocation\s*=\s*".*?"/i', 
							'', 
							$xmlContent, 
							1
						);

						// b) Eliminar la declaración de xsi que a veces causa conflictos
						$xmlContentCleaned = preg_replace(
							'/xmlns:xsi\s*=\s*".*?"/i', 
							'', 
							$xmlContentCleaned, 
							1
						);

						// c) Cargar el XML limpio con flags de robustez
						$xml = simplexml_load_string(
							$xmlContentCleaned, 
							'SimpleXMLElement', 
							LIBXML_NOCDATA | LIBXML_NOERROR
						);

						if ($xml === false) {
							echo "  ❌ Error: No se pudo cargar el XML limpio $fileName.\n";
							continue;
						}
						
						// 2. Registrar Namespaces (Usando las variables locales)
						$xml->registerXPathNamespace('cbc', $namespaceCbc);
						$xml->registerXPathNamespace('cac', $namespaceCac);

						// 3. EXTRAER LA GUÍA DE REMISIÓN (Valor del cbc:ID)
						$guiaRemisionNodes = $xml->xpath($xpathGuiaRemisionId);
						$guiaRemision = !empty($guiaRemisionNodes) ? (string)$guiaRemisionNodes[0] : null;

						if (!$guiaRemision) continue;

						// 4. BUSCAR LA SERIE DENTRO DE LAS DESCRIPCIONES
						$descriptionNodes = $xml->xpath($xpathDescriptions);
						$found = false;

						foreach ($descriptionNodes as $descNode) {
							$textContent = (string)$descNode;
							
							// strpos() para buscar la serie (compatibilidad PHP < 8.0)
							if (strpos(strtolower($textContent), strtolower($searchSerie)) !== false) {
								$found = true;
								break; // Serie encontrada
							}
						}

						// 5. Registrar resultado
						if ($found) {
							$resultados[] = [
								'archivo' => $fileName,
								'ruta' => $xmlPath,
								'guia_remision' => $guiaRemision,
								'serie_buscada' => $searchSerie
							];
							
							echo "  ✅ Éxito en **" . $fileName . "**: Guía extraída: **$guiaRemision**\n";
						} 
						
					} catch (\Exception $e) {
						echo "  ❌ Error al procesar XML $fileName: " . $e->getMessage() . "\n";
					}
				}

			} catch (\Exception $e) {
				echo "Error al recorrer el directorio $remisionDir: " . $e->getMessage() . "\n";
			}
		}

		return $resultados;
	}

	function findRemisionByModel_xml(string $searchModel = '50UM670H0UA.AWF', string $basePath = 'eDocuments/2024/', string $targetDir = 'GUIA_DE_REMISION', string $filePrefix = 'TM01'){ 
		$resultados = [];

		// --- Definición de Variables Locales Fijas ---
		$namespaceCbc = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
		$namespaceCac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
		
		// XPath para encontrar todos los IDs de los artículos
		$xpathModelo = '//cac:DespatchLine/cac:Item/cac:SellersItemIdentification/cbc:ID';

		// 1. Recorrer las carpetas de los meses (01 a 12)
		for ($month = 1; $month <= 12; $month++) {
			$monthStr = str_pad($month, 2, '0', STR_PAD_LEFT); // '01', '02', '03', etc.
			$remisionDir = $basePath . $monthStr . DIRECTORY_SEPARATOR . $targetDir;
			
			if (!is_dir($remisionDir)) continue;

			echo "🔍 Analizando directorio: " . $monthStr . DIRECTORY_SEPARATOR . $targetDir . "\n";
			
			try {
				// Usa glob() para obtener solo archivos .xml que empiecen con el prefijo
				$xmlFiles = glob($remisionDir . DIRECTORY_SEPARATOR . $filePrefix . '*.xml');

				foreach ($xmlFiles as $xmlPath) {
					$fileName = basename($xmlPath);
					
					// --- Lógica de Carga y Limpieza de XML (robusta) ---
					try {
						$xmlContent = file_get_contents($xmlPath);
						if ($xmlContent === false) continue;
						
						// Limpieza de declaraciones conflictivas
						$xmlContentCleaned = preg_replace('/xmlns:schemaLocation\s*=\s*".*?"/i', '', $xmlContent, 1);
						$xmlContentCleaned = preg_replace('/xmlns:xsi\s*=\s*".*?"/i', '', $xmlContentCleaned, 1);
						
						$xml = simplexml_load_string($xmlContentCleaned, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);

						if ($xml === false) {
							// Manejo de error al cargar XML
							continue;
						}

						// 2. Registrar Namespaces
						$xml->registerXPathNamespace('cbc', $namespaceCbc);
						$xml->registerXPathNamespace('cac', $namespaceCac);

						// 3. BUSCAR EL MODELO ESPECÍFICO
						$modeloNodes = $xml->xpath($xpathModelo);
						$encontrado = false;

						foreach ($modeloNodes as $modeloNode) {
							$modelo = (string)$modeloNode;
							
							// Comparación insensible a mayúsculas y minúsculas
							if (strtolower($modelo) === strtolower($searchModel)) {
								$encontrado = true;
								break; // Modelo encontrado, salimos del bucle interno
							}
						}

						// 4. Registrar resultado con el mes y el nombre del archivo
						if ($encontrado) {
							$resultados[] = [
								'mes' => $monthStr, // <-- Añadido el mes
								'archivo' => $fileName, // <-- Nombre del archivo
								'ruta_completa' => $xmlPath,
								'modelo_buscado' => $searchModel
							];
							
							echo "  ✅ Éxito en **$fileName** (Mes: $monthStr): El modelo **$searchModel** fue encontrado.\n";
						} 
						
					} catch (\Exception $e) {
						echo "  ❌ Error al procesar XML $fileName: " . $e->getMessage() . "\n";
					}
				}

			} catch (\Exception $e) {
				echo "Error al recorrer el directorio $remisionDir: " . $e->getMessage() . "\n";
			}
		}

		return $resultados;
	}

}
