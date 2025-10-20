<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Ar_bank_code extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"stocks"	=> $this->gen_m->filter("ar_bank_code", false, null, null, null, [['updated', 'desc']], 100),
			"main" 		=> "data_upload/ar_bank_code/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function date_convert_mm_dd_yyyy($date) {
		if (is_numeric($date)) {
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		$aux = explode("/", $date);
		if (count($aux) == 3) {
			return $aux[2]."-".$aux[0]."-".$aux[1];
		}

		return null;
	}

	public function date_convert_dd_mm_yyyy($date) {
		if (is_numeric($date)) {
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
	}
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);

		$start_time = microtime(true);
		$updated = date("Y-m-d");
		$batch_size = 100;

		// Definir el mapeo de columnas para cada hoja
		$column_map = [
			"BBVA"    => ["header_row" => 11, "data_start_row" => 12, "date_operation" => 'A', "number_operation" => 'D', "total_amount" => 'F'],
			"BCP"     => ["header_row" => 5, "data_start_row"  => 6, "date_operation"  => 'A', "number_operation" => 'G', "total_amount" => 'D'],
			"CITI"    => ["header_row" => 7, "data_start_row"  => 8, "date_operation"  => 'D', "number_operation" => 'N', "total_amount" => 'K'],
			"SCB"     => ["header_row" => 8, "data_start_row"  => 9, "date_operation"  => 'A', "number_operation" => 'D', "total_amount" => 'C'],
			"IBK"     => ["header_row" => 12, "data_start_row" => 13, "date_operation" => 'B', "number_operation" => 'D', "total_amount" => 'H'],
			"NACION"  => ["header_row" => 1, "data_start_row"  => 2, "date_operation"  => 'B', "number_operation" => 'D', "total_amount" => 'H'],
		];

		$spreadsheet = IOFactory::load("./upload/ar_bank_code.xlsx");

		// Iniciar transacción para mejorar el rendimiento
		$this->db->trans_start();

		foreach ($spreadsheet->getSheetNames() as $sheetName) {
			$name_parts = explode(" ", $sheetName, 2);
			$bank_name = trim($name_parts[0] ?? "");
			$currency = trim($name_parts[1] ?? "");
			if($currency === 'ME'){
				$currency = 'USD';
			}
			else{
				$currency = 'PEN';
			}
			
			$sheet = $spreadsheet->getSheetByName($sheetName);
			$max_row = $sheet->getHighestRow();
			$batch_data = [];
			
			// Verificar las filas para cada hoja según el mapeo
			if (!isset($column_map[$bank_name])) continue;  // Si no hay configuración para la hoja, continuamos con la siguiente
			$header_row = $column_map[$bank_name]["header_row"];
			$data_start_row = $column_map[$bank_name]["data_start_row"];
			$date_col = $column_map[$bank_name]["date_operation"];
			$number_col = $column_map[$bank_name]["number_operation"];
			$amount_col = $column_map[$bank_name]["total_amount"];
	
			
			if ($bank_name === 'BBVA'){
				$data_start_row = $data_start_row + 1;
				$max_row = $max_row - 1;
			}
			
			// **Validar si la hoja está vacía verificando la primera fila de datos**
			$has_data = false;
			for ($col = 'A'; $col <= 'Z'; $col++) { // Recorre columnas desde A hasta Z
				$cell_value = trim($sheet->getCell($col . $header_row)->getValue());
				if (!empty($cell_value)) {
					$has_data = true;
					break;
				}
			}
			if (!$has_data) continue; // Si la fila de datos está vacía, pasar a la siguiente hoja


			// Recorrer las filas de datos (desde la fila de inicio de los valores)
			for ($i = $data_start_row; $i <= $max_row; $i++) {
				// **Verificar si toda la fila está vacía antes de insertarla**
				$is_empty_row = true;
				$cell_date_val = trim($sheet->getCell($date_col . $i)->getValue());
				if (!empty($cell_date_val)) {
					$is_empty_row = false;
				}
				
				if ($bank_name === 'SCB'){					
					$f_value = $sheet->getCell('F' . $i)->getValue();
					$g_value = $sheet->getCell('G' . $i)->getValue();
					$h_value = $sheet->getCell('H' . $i)->getValue();
					
					$f_value = str_pad($f_value, 3, '0', STR_PAD_LEFT);
					$g_value = str_pad($g_value, 3, '0', STR_PAD_LEFT);
					$h_value = str_pad($h_value, 4, '0', STR_PAD_LEFT);

					$column_value = $f_value . $g_value . $h_value;
				}
			
				if ($is_empty_row) continue; // Si la fila está vacía, detenemos el bucle en esta hoja
				$row = [
					"bank_name" 			=> $bank_name,
					"currency" 				=> $currency,					
					"date_operation" 		=> trim($sheet->getCell($date_col . $i)->getValue()),
					"number_operation" 		=> $bank_name === 'SCB' ? $column_value : trim($sheet->getCell($number_col . $i)->getValue()),
					"total_amount" 			=> trim($sheet->getCell($amount_col . $i)->getValue()),
					"updated" 				=> $updated
				];
				
				if (strlen($row["number_operation"]) < 10 && $row["number_operation"] !== '-' && 
					!empty($row["number_operation"]) && $bank_name !== 'BCP' && $bank_name !== 'IBK' 
					&& $bank_name !== 'NACION' && $bank_name !== 'SCB') {
					$row["number_operation"] = str_pad($row["number_operation"], 10, '0', STR_PAD_LEFT);
				}

				// Convert dates
				if($bank_name === 'BCP' || $bank_name === 'SCB' || $bank_name === 'IBK'){ //dd/mm/yyyy
					$row['date_operation'] = $this->date_convert_dd_mm_yyyy($row['date_operation']);
				}
				elseif($bank_name === 'BBVA' || $bank_name === 'CITI'){ // mm/dd/yyyy
					$row['date_operation'] = $this->date_convert_mm_dd_yyyy($row['date_operation']);
				}

				$batch_data[] = $row;

				// Inserción por lotes
				if (count($batch_data) >= $batch_size) {
					$this->gen_m->insert_m("ar_bank_code", $batch_data);
					$batch_data = [];
				}
			}

			// Insertar los datos restantes en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("ar_bank_code", $batch_data);
			}
		}
		
		// Finalizar transacción
		$this->db->trans_complete();

		return "Records uploaded in " . number_format(microtime(true) - $start_time, 2) . " secs.";
	}

	public function update(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ar_bank_code.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
