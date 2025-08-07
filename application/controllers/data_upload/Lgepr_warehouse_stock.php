<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Lgepr_warehouse_stock extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		//$w = ["updated >=" => date("Y-m-d", strtotime("-3 months"))];
		//$o = [["updated", "desc"], ["model_description", "asc"], ["model", "asc"]];
		$w_stocks = $this->gen_m->filter("lgepr_warehouse_stock", false);
		$data = [
			"w_stocks"		=> $w_stocks,
			"count_wstocks" => count($w_stocks),
			"main" 			=> "data_upload/lgepr_warehouse_stock/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function get_comparison_values(){
		
		 
		
		$w_stock = [];
		$w_stocks = $this->gen_m->filter('lgepr_warehouse_stock', false);
		foreach($w_stocks as $item) $w_stock[] = ['warehouse' => $item->warehouse, 'model' => $item->sku_lg, 'sub_inventory' => $item->sub_inventory, 'stock' => $item->stock_total]; 
		
		$list_warehouse = [];
		$list_warehouses = $this->gen_m->filter_select('lgepr_warehouse_stock', false, 'warehouse');
		foreach($list_warehouses as $item) $list_warehouse[] = $item->warehouse;
		$list_warehouse = array_unique($list_warehouse);
		
		// Filter warehouse
		if ($list_warehouse[0] === 'KLO'){
			if (isset($list_warehouse[1]) && $list_warehouse[1] === 'APM'){
				$wh_filter = ['N4E', 'N4M', 'N4S'];
			} elseif(!isset($list_warehouse[1])) $wh_filter = ['N4S'];
			
		} elseif ($list_warehouse[0] === 'APM'){
			if (isset($list_warehouse[1]) && $list_warehouse[1] === 'KLO'){
				$wh_filter = ['N4E', 'N4M', 'N4S'];
			} elseif(!isset($list_warehouse[1])) $wh_filter = ['N4E', 'N4M'];
		}
		
		// Obtener el valor de 'updated' más reciente de toda la tabla
		$this->db->select_max('updated');
		$query_max_updated = $this->db->get('lgepr_stock');
		$max_updated_row = $query_max_updated->row();
		$latest_datetime = $max_updated_row->updated;
		//echo '<pre>'; print_r($latest_datetime);
		$w_in = [
					[
						'field'  => 'org',
						'values' => $wh_filter
					]
				];
		$lg_stock = [];
		$lg_stocks = $this->gen_m->filter_select('lgepr_stock', false, ['org', 'model', 'sub_inventory', 'on_hand'], ['sub_inventory NOT LIKE' => '%OUT', 'updated' => $latest_datetime], null, $w_in);
		//echo '<pre>'; print_r($lg_stocks);
		foreach($lg_stocks as $item) $lg_stock[] = ['warehouse' => ($item->org === 'N4S' ? 'KLO' : 'APM'),'model' => $item->model, 'sub_inventory' => $item->sub_inventory, 'stock' => $item->on_hand];
		
		$consolidated_data = [];

		// Paso 1: Procesar $w_stock primero para establecer la base de los modelos y subinventarios válidos
		// Esto asegura que solo las combinaciones que existen en w_stock serán consideradas
		foreach ($lg_stock as $item) {
			// La clave ahora incluye 'warehouse'
			$key = $item['warehouse'] . '_' . $item['model'] . '_' . $item['sub_inventory'];
			if (!isset($consolidated_data[$key])) {
				$consolidated_data[$key] = [
					'warehouse' => $item['warehouse'],
					'model' => $item['model'],
					'sub_inventory' => $item['sub_inventory'],
					'lg_stock' => 0, // Inicializar lg_stock a 0
					'w_stock' => 0,
					'diff' => 0
				];
			}
			$consolidated_data[$key]['lg_stock'] += $item['stock'];
		}

		// Paso 2: Procesar $lg_stock y agregar sus valores a las entradas existentes
		// Solo se actualizarán las entradas donde la combinación 'warehouse_model_sub_inventory' ya exista en consolidated_data (desde w_stock)
		foreach ($w_stock as $item) {
			$key = $item['warehouse'] . '_' . $item['model'] . '_' . $item['sub_inventory'];
			
			// Solo agregar si la clave ya existe en consolidated_data (viene de w_stock)
			if (isset($consolidated_data[$key])) {
				$consolidated_data[$key]['w_stock'] += $item['stock'];
			}
			// Si una combinación de LG no está en WMS, simplemente la ignoramos, que es lo que deseas.
		}


		// Paso 3: Calcular la diferencia para todos los elementos consolidados
		foreach ($consolidated_data as &$item) {
			$item['diff'] = $item['lg_stock'] - $item['w_stock'];
		}

		// Opcional: Convertir el array asociativo a un array indexado numéricamente
		$data['final_result'] = array_values($consolidated_data);
		$final_result = array_values($consolidated_data);
		return [$data, $final_result];
	}
	
	public function data_comparision(){
		
		[$data,] = $this->get_comparison_values();
		// $this->output->set_content_type('application/json');
		// echo json_encode($final_result);
		//echo '<pre>'; print_r($final_result);
		$data["overflow"] = "scroll";
		$data['main'] = "data_upload/lgepr_warehouse_stock/comparison_stock";
		$this->load->view('layout_dashboard', $data);
	}
	
	public function data_comparision2(){
		
		$w_in = [
					[
						'field'  => 'org',
						'values' => ['N4E', 'N4M', 'N4S']
					]
				];
		$lg_stock = [];
		$lg_stocks = $this->gen_m->filter_select('lgepr_stock', false, ['org', 'model', 'sub_inventory', 'on_hand'], ['sub_inventory NOT LIKE' => '%OUT'], null, $w_in);
		foreach($lg_stocks as $item) $lg_stock[] = ['warehouse' => ($item->org === 'N4S' ? 'KLO' : 'APM'),'model' => $item->model, 'sub_inventory' => $item->sub_inventory, 'stock' => $item->on_hand]; 
		
		$w_stock = [];
		$w_stocks = $this->gen_m->filter('lgepr_warehouse_stock', false);
		foreach($w_stocks as $item) $w_stock[] = ['warehouse' => $item->warehouse, 'model' => $item->sku_lg, 'sub_inventory' => $item->sub_inventory, 'stock' => $item->stock_total]; 
		
		$consolidated_data = [];
        foreach ($lg_stock as $item) {
            $key = $item['warehouse'] . '_' . $item['model'] . '_' . $item['sub_inventory'];
            if (!isset($consolidated_data[$key])) {
                $consolidated_data[$key] = [
                    'warehouse' => $item['warehouse'], 'model' => $item['model'], 'sub_inventory' => $item['sub_inventory'],
                    'lg_stock' => 0, 'w_stock' => 0, 'diff' => 0
                ];
            }
            $consolidated_data[$key]['lg_stock'] += $item['stock'];
        }
        foreach ($w_stock as $item) {
            $key = $item['warehouse'] . '_' . $item['model'] . '_' . $item['sub_inventory'];
            if (!isset($consolidated_data[$key])) {
                $consolidated_data[$key] = [
                    'warehouse' => $item['warehouse'], 'model' => $item['model'], 'sub_inventory' => $item['sub_inventory'],
                    'lg_stock'  => 0, 'w_stock' => 0, 'diff' => 0
                ];
            }
            $consolidated_data[$key]['w_stock'] += $item['stock'];
        }
        foreach ($consolidated_data as &$item) {
            $item['diff'] = $item['lg_stock'] - $item['w_stock'];
        }
        $data['final_result'] = array_values($consolidated_data);
		
		// $this->output->set_content_type('application/json');
		// echo json_encode($final_result);
		//echo '<pre>'; print_r($final_result);
		$data["overflow"] = "scroll";
		$data['main'] = "data_upload/lgepr_warehouse_stock/comparison_stock";
		$this->load->view('layout_dashboard', $data);
	}
	
	public function process_warehouse_klo($filename = "lgepr_warehouse_klo_stock.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		$klo_data = $this->gen_m->filter_select('lgepr_warehouse_stock', false, ['warehouse'], ['warehouse' => 'KLO']);
		if(!empty($klo_data)){
			$this->gen_m->truncate("lgepr_warehouse_stock");
		}
		//echo '<pre>'; print_r($klo_data);
		//delete all rows lgepr_warehouse_stock 
		// $this->gen_m->truncate("lgepr_warehouse_stock");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows = [];
		//foreach ($spreadsheet->getSheetNames() as $sheetName) {
		//$sheet = $spreadsheet->getActiveSheet();
	
			//$sheet = $spreadsheet->getSheetByName($sheetName);
			
		$updated = date("Y-m-d");
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			$row = [
				"sku_warehouse"			=> null,
				"sku_lg" 				=> !empty(trim($sheet->getCell('A'.$i)->getValue())) ? trim($sheet->getCell('A'.$i)->getValue()) : null,
				"description" 			=> null,
				"warehouse"				=> !empty(trim($sheet->getCell('A'.$i)->getValue())) ? 'KLO' : null,
				"sub_inventory" 		=> trim($sheet->getCell('B'.$i)->getValue()) ?? null,
				"stock" 				=> trim($sheet->getCell('C'.$i)->getValue()) ?? null,
				"stock_pre" 			=> trim($sheet->getCell('D'.$i)->getValue()) ?? null,
				"stock_total"			=> trim($sheet->getCell('E'.$i)->getValue()) ?? null,				
				"updated"				=> $updated,
			];
			
			if(empty($row['sku_lg']) && empty($row['warehouse'])) continue;
			else $rows[] = $row;			
		}
		//}
		$this->gen_m->insert_m('lgepr_warehouse_stock', $rows);
		return "Stock update has been finished. (".$updated.")";
	}
	
	public function process_warehouse_apm($filename = "lgepr_warehouse_apm_stock.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
	
		$apm_data = $this->gen_m->filter_select('lgepr_warehouse_stock', false, ['warehouse'], ['warehouse' => 'APM']);
		
		if (!empty($apm_data)){
			$this->gen_m->truncate("lgepr_warehouse_stock");
		}
		//echo '<pre>'; print_r($apm_data);
		//delete all rows lgepr_warehouse_stock 
		// $this->gen_m->truncate("lgepr_warehouse_stock");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows = [];
		//foreach ($spreadsheet->getSheetNames() as $sheetName) {
		
	
		//$sheet = $spreadsheet->getSheetByName($sheetName);
		
		$updated = date("Y-m-d");
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			$row = [
				"sku_warehouse"			=> null,
				"sku_lg" 				=> !empty(trim($sheet->getCell('A'.$i)->getValue())) ? trim($sheet->getCell('A'.$i)->getValue()) : null,
				"description" 			=> null,
				"warehouse"				=> !empty(trim($sheet->getCell('A'.$i)->getValue())) ? 'APM' : null,
				"sub_inventory" 		=> trim($sheet->getCell('B'.$i)->getValue()) ?? null,
				"stock" 				=> trim($sheet->getCell('C'.$i)->getValue()) ?? null,
				"stock_pre" 			=> trim($sheet->getCell('D'.$i)->getValue()) ?? null,
				"stock_total"			=> trim($sheet->getCell('E'.$i)->getValue()) ?? null,				
				"updated"				=> $updated,
			];

			if(empty($row['sku_lg']) && empty($row['warehouse'])) continue;
			else $rows[] = $row;	
		}
		//}
		$this->gen_m->insert_m('lgepr_warehouse_stock', $rows);
		return "Stock update has been finished. (".$updated.")";
	}
	
	public function process($filename = "lgepr_warehouse_stock.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);

		//delete all rows lgepr_warehouse_stock 
		$this->gen_m->truncate("lgepr_warehouse_stock");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$row = [];
		foreach ($spreadsheet->getSheetNames() as $sheetName) {
		//$sheet = $spreadsheet->getActiveSheet();
	
			$sheet = $spreadsheet->getSheetByName($sheetName);
			
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			for($i = 2; $i <= $max_row; $i++){
				$row[] = [
					"sku_warehouse"			=> trim($sheet->getCell('A'.$i)->getValue()),
					"sku_lg" 				=> trim($sheet->getCell('B'.$i)->getValue()),
					"description" 			=> trim($sheet->getCell('C'.$i)->getValue()),
					"warehouse"				=> $sheetName === 'KLO' ? 'KLO' : 'APM',
					"sub_inventory" 		=> trim($sheet->getCell('D'.$i)->getValue()),
					"stock" 				=> trim($sheet->getCell('E'.$i)->getValue()),
					"stock_pre" 			=> trim($sheet->getCell('F'.$i)->getValue()),
					"stock_total"			=> trim($sheet->getCell('G'.$i)->getValue()),				
					"updated"				=> $updated,
				];					
			}
		}
		$this->gen_m->insert_m('lgepr_warehouse_stock', $row);
		return "Stock update has been finished. (".$updated.")";
	}
		
	public function export_comparison_data_to_excel() {
		[,$final_result] = $this->get_comparison_values();
		// Generate excel
		
		// --- Agrupar los datos por Warehouse ---
        $data_by_warehouse = [];
        foreach ($final_result as $row) {
            $warehouse_name = $row['warehouse'];
            if (!isset($data_by_warehouse[$warehouse_name])) {
                $data_by_warehouse[$warehouse_name] = [];
            }
            $data_by_warehouse[$warehouse_name][] = $row;
        }

        // --- Generación del archivo Excel con PhpSpreadsheet ---
        $spreadsheet = new Spreadsheet();
        $headers = ['Warehouse', 'Model', 'Sub Inventory', 'LG Stock', 'Warehouse Stock', 'Difference'];
        $firstSheetCreated = false;

        foreach ($data_by_warehouse as $warehouse_name => $rows) {
            if (!$firstSheetCreated) {
                // Para la primera pestaña, usamos la hoja activa por defecto
                $sheet = $spreadsheet->getActiveSheet();
                $firstSheetCreated = true;
            } else {
                // Para las siguientes, creamos una nueva hoja
                $sheet = $spreadsheet->createSheet();
            }

            // Establecer el título de la pestaña (máx. 31 caracteres, y evitar caracteres especiales)
            $sheetTitle = substr(str_replace([':', '/', '\\', '?', '*', '[', ']'], '', $warehouse_name), 0, 31);
            $sheet->setTitle($sheetTitle);

            // Escribir encabezados en la primera fila
            $sheet->fromArray($headers, null, 'A1');

            // Escribir datos a partir de la segunda fila
		
            $rowIndex = 2;
            foreach ($rows as $row) {
                $sheet->setCellValue('A' . $rowIndex, $row['warehouse']);
                $sheet->setCellValue('B' . $rowIndex, $row['model']);
                $sheet->setCellValue('C' . $rowIndex, $row['sub_inventory']);
                $sheet->setCellValue('D' . $rowIndex, $row['lg_stock']);
                $sheet->setCellValue('E' . $rowIndex, $row['w_stock']);
                $sheet->setCellValue('F' . $rowIndex, $row['diff']);
                $rowIndex++;
            }

            // Autoajustar el ancho de las columnas para la hoja actual
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Si no hay datos, y por lo tanto no se creó ninguna hoja, aseguramos una hoja vacía
        if (!$firstSheetCreated && !empty($headers)) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('No Data');
            $sheet->fromArray($headers, null, 'A1');
        }


        // Preparar la respuesta para la descarga del archivo
        $filename = 'comparison_stock_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output'); // Envía el archivo directamente a la salida
        exit; // Termina la ejecución del script
		
		
        // // --- Generación del archivo Excel con PhpSpreadsheet ---
        // $spreadsheet = new Spreadsheet();
        // $sheet = $spreadsheet->getActiveSheet();
        // $sheet->setTitle('comparison_stock');

        // // Encabezados
        // $headers = ['Warehouse', 'Model', 'Sub Inventory', 'LG Stock', 'Warehouse Stock', 'Difference'];
        // $sheet->fromArray($headers, null, 'A1');

        // // Datos
        // $rowIndex = 2; // Empezar en la fila 2 después de los encabezados
        // foreach ($final_result as $row) {
            // $sheet->setCellValue('A' . $rowIndex, $row['warehouse']);
            // $sheet->setCellValue('B' . $rowIndex, $row['model']);
            // $sheet->setCellValue('C' . $rowIndex, $row['sub_inventory']);
            // $sheet->setCellValue('D' . $rowIndex, $row['lg_stock']);
            // $sheet->setCellValue('E' . $rowIndex, $row['w_stock']);
            // $sheet->setCellValue('F' . $rowIndex, $row['diff']);
            // $rowIndex++;
        // }

        // // Autoajustar el ancho de las columnas
        // foreach (range('A', $sheet->getHighestColumn()) as $col) {
            // $sheet->getColumnDimension($col)->setAutoSize(true);
        // }

        // // Preparar la respuesta para la descarga del archivo
        // $filename = 'comparison_stock_' . date('Ymd_His') . '.xlsx';
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment;filename="' . $filename . '"');
        // header('Cache-Control: max-age=0');

        // $writer = new Xlsx($spreadsheet);
        // $writer->save('php://output');
        // exit;
    }
	
	public function send_email_v1(){
		// send email report comparison stocks
		//llamasys/data_upload/lgepr_warehouse_stock/send_email
		
		$w_in = [
            [
                'field' => 'org',
                'values' => ['N4E', 'N4M', 'N4S'] // Ajusta esto a tus necesidades reales
            ]
        ];

        $lg_stock = [];
        $lg_stocks = $this->gen_m->filter_select('lgepr_stock', false, ['org', 'model', 'sub_inventory', 'on_hand'], ['sub_inventory NOT LIKE' => '%OUT'], null, $w_in);
        foreach($lg_stocks as $item) {
            $lg_stock[] = ['warehouse' => ($item->org === 'N4S' ? 'KLO' : 'APM'), 'model' => $item->model, 'sub_inventory' => $item->sub_inventory, 'stock' => $item->on_hand];
        }

        $w_stock = [];
        $w_stocks = $this->gen_m->filter('lgepr_warehouse_stock', false);
        foreach($w_stocks as $item) {
            $w_stock[] = ['warehouse' => $item->warehouse, 'model' => $item->sku_lg, 'sub_inventory' => $item->sub_inventory, 'stock' => $item->stock_total];
        }

        $consolidated_data = [];
        foreach ($lg_stock as $item) {
            $key = $item['warehouse'] . '_' . $item['model'] . '_' . $item['sub_inventory'];
            if (!isset($consolidated_data[$key])) {
                $consolidated_data[$key] = [
                    'warehouse' => $item['warehouse'], 'model' => $item['model'], 'sub_inventory' => $item['sub_inventory'],
                    'lg_stock' => 0, 'w_stock' => 0, 'diff' => 0
                ];
            }
            $consolidated_data[$key]['lg_stock'] += $item['stock'];
        }
        foreach ($w_stock as $item) {
            $key = $item['warehouse'] . '_' . $item['model'] . '_' . $item['sub_inventory'];
            if (!isset($consolidated_data[$key])) {
                $consolidated_data[$key] = [
                    'warehouse' => $item['warehouse'], 'model' => $item['model'], 'sub_inventory' => 'N/A',
                    'lg_stock' => 0, 'w_stock' => 0, 'diff' => 0
                ];
            }
            $consolidated_data[$key]['w_stock'] += $item['stock'];
        }
		
		$data_email = [];
        foreach ($consolidated_data as &$item) {
            $item['diff'] = $item['lg_stock'] - $item['w_stock'];
			if($item['diff'] != 0){
				$data_email[] = [
								'warehouse' => $item['warehouse'], 'model' => $item['model'], 'sub_inventory' => $item['sub_inventory'],
								'lg_stock' => $item['lg_stock'], 'w_stock' => $item['w_stock'], 'diff' => $item['diff']
								];
			}
							
        }
		 
        $final_result = array_values($consolidated_data);
		$data_final = array_values($data_email);
		
		// --- 2. Limitar el arreglo a las primeras 10-20 líneas ---
        $limit = 30; // Define el número de filas que quieres enviar (entre 10 y 20)
        //$limited_data = array_slice($data_final, 0, $limit);
		$limited_data = array_slice($data_final, 0);
        // --- 3. Generar el HTML de la tabla para el correo ---
        $subject = 'Comparison Stock - ' . date('Ymd');
        $to = 'lbernaldo.js@lgepartner.com';

        $message_content = '<p>Saludos,</p>';
        $message_content .= '<p>Se adjunta un resumen de comparacion de stock:</p>';

        $message_content .= '<div style="font-family: Arial, sans-serif; font-size: 14px;">';
        $message_content .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $message_content .= '<thead>';
        $message_content .= '<tr style="background-color: #f2f2f2;">';
        $message_content .= '<th style="padding: 8px; text-align: left;">Warehouse</th>';
        $message_content .= '<th style="padding: 8px; text-align: left;">Model</th>';
        $message_content .= '<th style="padding: 8px; text-align: left;">Sub Inventory</th>';
        $message_content .= '<th style="padding: 8px; text-align: right;">LG Stock</th>';
        $message_content .= '<th style="padding: 8px; text-align: right;">Warehouse Stock</th>';
        $message_content .= '<th style="padding: 8px; text-align: right;">Difference</th>';
		$message_content .= '<th> </th>';
        $message_content .= '</tr>';
        $message_content .= '</thead>';
        $message_content .= '<tbody>';

        if (empty($limited_data)) {
            $message_content .= '<tr><td colspan="6" style="padding: 8px; text-align: center;">No data to show.</td></tr>';
        } else {
            foreach ($limited_data as $row) {				
				if ($row['diff'] == 0){
					$status_color = 'green';
				} elseif($row['diff'] <= 5 && $row['diff'] >= -5 && $row['diff'] != 0){
					$status_color = 'yellow';
				} else{
					$status_color = 'red';
				}
                $message_content .= '<tr>';
                $message_content .= '<td style="padding: 8px; text-align: left;">' . htmlspecialchars($row['warehouse']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: left;">' . htmlspecialchars($row['model']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: left;">' . htmlspecialchars($row['sub_inventory']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: right;">' . htmlspecialchars($row['lg_stock']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: right;">' . htmlspecialchars($row['w_stock']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: right;">' . htmlspecialchars($row['diff']) . '</td>';
				$message_content .= '<td style="text-align: center;">';
				$message_content .= '<span style="color: ' . $status_color . '; font-size: 1.2em;">&#x25CF;</span>'; // Círculo Unicode con color en línea
				$message_content .= '</td>';
                $message_content .= '</tr>';
            }
        }
        $message_content .= '</tbody>';
        $message_content .= '</table>';
		$message_content .= '<p><strong>Process Innovation Team</strong></p>';
		$message_content .= '<div class="footer"><p>Este es un mensaje generado automaticamente. Por favor, no respondas a este correo.</p></div>';
        $message_content .= '</div>';
        
       
		
        // --- 4. Enviar el correo usando tu función my_func->send_email ---
        // El remitente 'rpa.espr@lgepartner.com' ya está fijo en tu función my_func->send_email
        $send_result = $this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message_content);

        if (empty($send_result)) { // Si $result está vacío, significa que el envío fue exitoso
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Correo enviado exitosamente.']));
        } else {
            // Si $result contiene algo, significa que hubo un error (el print_debugger)
            log_message('error', 'Error al enviar correo: ' . $send_result);
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Fallo al enviar el correo.', 'debug' => $send_result]));
        }
	}
	
	public function comparison_excel_to_email($return_path = false) {
		[, $final_result] = $this->get_comparison_values();
        // --- Agrupar los datos por Warehouse para las pestañas ---
        $data_by_warehouse = [];
        foreach ($final_result as $row) {
            $warehouse_name = $row['warehouse'];
            if (!isset($data_by_warehouse[$warehouse_name])) {
                $data_by_warehouse[$warehouse_name] = [];
            }
            $data_by_warehouse[$warehouse_name][] = $row;
        }

        // --- Generación del archivo Excel con PhpSpreadsheet ---
        $spreadsheet = new Spreadsheet();
        $headers = ['Warehouse', 'Model', 'Sub Inventory', 'LG Stock', 'Warehouse Stock', 'Difference'];
        $firstSheetCreated = false;

        foreach ($data_by_warehouse as $warehouse_name => $rows) {
            if (!$firstSheetCreated) {
                $sheet = $spreadsheet->getActiveSheet();
                $firstSheetCreated = true;
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $sheetTitle = substr(str_replace([':', '/', '\\', '?', '*', '[', ']'], '', $warehouse_name), 0, 31);
            $sheet->setTitle($sheetTitle);
            $sheet->fromArray($headers, null, 'A1');
            $rowIndex = 2;
            foreach ($rows as $row) {
                $sheet->setCellValue('A' . $rowIndex, $row['warehouse']);
                $sheet->setCellValue('B' . $rowIndex, $row['model']);
                $sheet->setCellValue('C' . $rowIndex, $row['sub_inventory']);
                $sheet->setCellValue('D' . $rowIndex, $row['lg_stock']);
                $sheet->setCellValue('E' . $rowIndex, $row['w_stock']);
                $sheet->setCellValue('F' . $rowIndex, $row['diff']);
                $rowIndex++;
            }
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        if (!$firstSheetCreated && !empty($headers)) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('No Data');
            $sheet->fromArray($headers, null, 'A1');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'comparison_stock_' . date('Ymd') . '.xlsx';
        
        // --- Lógica condicional para descargar o devolver la ruta ---
        if ($return_path) {
            // Guardar en una ubicación temporal
            $temp_dir = sys_get_temp_dir(); // Directorio temporal del sistema
            $file_path = $temp_dir . DIRECTORY_SEPARATOR . $filename;
            $writer->save($file_path);
            return $file_path; // Devuelve la ruta del archivo guardado
        } else {
            // Descargar directamente al navegador (comportamiento original del botón de exportar)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }
    }
	
	private function generate_single_warehouse_excel($data_rows, $warehouse_name) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Limpia el nombre del warehouse para el título de la pestaña
        $sheetTitle = substr(str_replace([':', '/', '\\', '?', '*', '[', ']'], '', $warehouse_name), 0, 31);
        $sheet->setTitle($sheetTitle);

        $headers = ['Warehouse', 'Model', 'Sub Inventory', 'LG Stock', 'Warehouse Stock', 'Difference'];
        $sheet->fromArray($headers, null, 'A1');

        $rowIndex = 2;
        foreach ($data_rows as $row) {
            $sheet->setCellValue('A' . $rowIndex, $row['warehouse']);
            $sheet->setCellValue('B' . $rowIndex, $row['model']);
            $sheet->setCellValue('C' . $rowIndex, $row['sub_inventory']);
            $sheet->setCellValue('D' . $rowIndex, $row['lg_stock']);
            $sheet->setCellValue('E' . $rowIndex, $row['w_stock']);
            $sheet->setCellValue('F' . $rowIndex, $row['diff']);
            $rowIndex++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar un nombre de archivo único para este warehouse
        // $filename = 'comparacion_stock_' . $warehouse_name . '_' . date('Ymd_His') . '.xlsx';
        // $temp_dir = sys_get_temp_dir();
        // $file_path = $temp_dir . DIRECTORY_SEPARATOR . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($file_path);
        
        return $file_path; // Devuelve la ruta del archivo temporal
    }
	
	public function send_email() {		
		[, $final_result] = $this->get_comparison_values();
        // --- 2. Calcular el resumen por Warehouse ---
        $summary_by_warehouse = [];
		
        foreach ($final_result as $row) {
            $warehouse_name = $row['warehouse'];
            if (!isset($summary_by_warehouse[$warehouse_name])) {
                $summary_by_warehouse[$warehouse_name] = [
                    'total_models' => 0,
                    'models_with_diff' => 0,
                    'diff_percentage' => 0
                ];
            }
            
            $summary_by_warehouse[$warehouse_name]['total_models']++; // Cada fila representa un modelo único (o sub-inventario por modelo)

            if ($row['diff'] != 0) {
				$summary_by_warehouse[$warehouse_name]['models_with_diff']++;
            }
        }
		
        // Calcular porcentajes
        foreach ($summary_by_warehouse as $warehouse_name => &$summary) {
            if ($summary['total_models'] > 0) {
				$summary['diff_percentage'] = ($summary['models_with_diff'] / $summary['total_models']) * 100;
            } else {
				$summary['diff_percentage'] = 0;
            }
        }
		unset($summary);

        // --- 3. Generar el HTML del correo ---
        $subject = 'Comparison Stock - ' . date('Ymd');
        $to = 'lbernaldo.js@lgepartner.com';

        $message_content = '<p>Estimados,</p>';
        $message_content .= '<p>Se adjunta un resumen de los valores obtenidos por almacen:</p>';

        $message_content .= '<div style="font-family: Arial, sans-serif; font-size: 14px;">';
        $message_content .= '<table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $message_content .= '<thead>';
        $message_content .= '<tr style="background-color: #f2f2f2;">';
        $message_content .= '<th style="padding: 8px; text-align: left;">Warehouse</th>';
        $message_content .= '<th style="padding: 8px; text-align: right;">Models</th>';
        $message_content .= '<th style="padding: 8px; text-align: right;">Differences</th>';
        $message_content .= '<th style="padding: 8px; text-align: right;">Porcentage</th>';
        $message_content .= '</tr>';
        $message_content .= '</thead>';
        $message_content .= '<tbody>';

        if (empty($summary_by_warehouse)) {
            $message_content .= '<tr><td colspan="4" style="padding: 8px; text-align: center;">No se encontraron datos de resumen para mostrar.</td></tr>';
        } else {
            foreach ($summary_by_warehouse as $warehouse_name => $summary) {
                $message_content .= '<tr>';
                $message_content .= '<td style="padding: 8px; text-align: left; font-weight: bold;">' . htmlspecialchars($warehouse_name) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: right;">' . htmlspecialchars($summary['total_models']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: right;">' . htmlspecialchars($summary['models_with_diff']) . '</td>';
                $message_content .= '<td style="padding: 8px; text-align: right; color: ' . ($summary['diff_percentage'] > 0 ? 'red' : 'green') . ';">' . number_format($summary['diff_percentage'], 2) . '%</td>';
                $message_content .= '</tr>';
            }
        }
        $message_content .= '</tbody>';
        $message_content .= '</table>';
        $message_content .= '</div>';
        
        $message_content .= '<p>Detalle completo de las diferencias calculadas en el archivo adjunto.</p>';
        $message_content .= '<p>Atentamente,<br>Process Innovation Team</p>';

        // --- 4. Enviar el correo usando tu función my_func->send_email ---
		$excel_file_path = $this->comparison_excel_to_email(true); // Llama a la función para guardar el Excel
        
        $send_result = $this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message_content, $excel_file_path);

        if (empty($send_result)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Correo enviado exitosamente.']));
        } else {
            log_message('error', 'Error al enviar correo: ' . $send_result);
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Fallo al enviar el correo.', 'debug' => $send_result]));
        }
    }
	
	public function upload_warehouse_klo(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'lgepr_warehouse_klo_stock.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_warehouse_klo();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function upload_warehouse_apm(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'lgepr_warehouse_apm_stock.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_warehouse_apm();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
