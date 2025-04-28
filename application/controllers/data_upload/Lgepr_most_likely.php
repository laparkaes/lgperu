<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_most_likely extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		// $w = ["updated >=" => date("Y-m-d", strtotime("-3 months"))];
		// $o = [["updated", "desc"], ["model_description", "asc"], ["model", "asc"]];
		
		$data = [
			"ml"	=> $this->gen_m->filter("lgepr_most_likely", false, null, null, null, "", 100),
			"main" 		=> "data_upload/lgepr_most_likely/index",
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

		// Si no es un número, intentamos convertir con la lógica de fecha en formato mm/dd/yyyy
		$aux = explode("/", $date);
		if (count($aux) == 3) {
			// Verificamos que la fecha esté en formato mm/dd/yyyy
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}
		
		// Si la fecha no está en un formato esperado, devolvemos null
		return null;
	}

	public function date_convert_dd_mm_yyyy($date) {
    // Intentamos convertir con la lógica del valor numérico (excel date)
		if (is_numeric($date)) {
			// Si es un número, es probable que sea una fecha de Excel (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número, intentamos convertir con la lógica de fecha en formato dd/mm/yyyy
		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
	}
	
	public function row_assigned($sheet, $country, $i, $company_mapping){
		$updated = date("Y-m-d H:i:s");
		$row = [];
		if (in_array(trim($sheet->getCell('A'.$i)->getValue()), $company_mapping)) return $row;
		
		
		$dash_mapping = [
						"REF"		 	=> "HS",
						"Cooking"		=> "HS",
						"Dishwasher"	=> "HS",
						"W/M"			=> "HS",
						
						"LTV"			=> "MS",
						"Audio"			=> "MS",
						"MNT"			=> "MS",
						"DS"			=> "MS",
						"MNT Signage"	=> "MS",
						"LED Signage"	=> "MS",
						"Commercial TV" => "MS",
						"PC"			=> "MS",
						
						"RAC"			=> "ES",
						"SAC"			=> "ES",
						"Chiller"		=> "ES",
						
						"MC"			=> "MC"
		];
		
		$row = [
					"country" 						=> $country,
					"company"						=> $dash_mapping[trim($sheet->getCell('A'.$i)->getValue())],
					"division" 						=> trim($sheet->getCell('A'.$i)->getValue()),
					"yyyy" 							=> trim($sheet->getCell('B'.$i)->getValue()),
					"mm" 							=> trim($sheet->getCell('C'.$i)->getValue()),
					"y_2" 							=> round(trim($sheet->getCell('D'.$i)->getValue()), 3),
					"y_1"							=> round(trim($sheet->getCell('E'.$i)->getValue()), 3),
					"bp" 							=> round(trim($sheet->getCell('F'.$i)->getValue()), 3),
					"target" 						=> round(trim($sheet->getCell('G'.$i)->getValue()), 3),		
					"mp"							=> round(trim($sheet->getCell('H'.$i)->getValue()), 3),
					"monthly_report"				=> round(trim($sheet->getCell('I'.$i)->getValue()), 3),
					"ml"							=> round(trim($sheet->getCell('J'.$i)->getValue()), 3),
					"ml_actual"						=> round(trim($sheet->getCell('K'.$i)->getValue()), 3),
					"m_1"							=> round(trim($sheet->getCell('L'.$i)->getValue()), 3),		
					"m_2"							=> round(trim($sheet->getCell('M'.$i)->getValue()), 3),
					"m_3"							=> round(trim($sheet->getCell('N'.$i)->getValue()), 3),
					//"updated"						=> $updated,
				];
				
		return $row;
	}
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		//delete all rows ngsi_inventory 
		//$this->gen_m->truncate("lgepr_most_likely");
		
		$start_time = microtime(true);

		// Cargar el archivo Excel
		$spreadsheet = IOFactory::load("./upload/lgepr_most_likely.xlsx");

		// Iniciar transacción para mejorar el rendimiento
		//$this->db->trans_start();
		
		$company_mapping = ["HS", "MS", "ES", "MC"];
		$filter_select = ['country', 'company', 'division', 'yyyy', 'mm', 'y_2', 'y_1', 'bp', 'target', 'mp', 'monthly_report', 'ml', 'ml_actual', 'm_1', 'm_2', 'm_3'];
	
		$data_ml = $this->gen_m->filter_select('lgepr_most_likely', false, $filter_select);
		//foreach($data_total_ml as $item) $data_ml = $item;
		//echo '<pr>'; print_r($data_ml); return;
			
		foreach ($spreadsheet->getSheetNames() as $sheetName) {
			//print_r($sheetName);
			if ($sheetName === 'PR'){
				$updated = date("Y-m-d H:i:s");
				$sheet = $spreadsheet->getSheetByName($sheetName);
				$max_row = $sheet->getHighestRow();
				$batch_data = [];
				$batch_size = 100;
				$country = 'PR';
				$flag_unique = 1;
				// Procesar datos desde la fila 6 en adelante
				// Iniciar transacción para mejorar rendimiento
				//$this->db->trans_start();
				for($i = 3; $i < $max_row; $i++){
					$is_row_empty = true; // Inicializar como fila vacía
					$index_ml = $i - 3;
					// Verificar si todas las celdas están vacías
					foreach (range('A', 'N') as $col) { // Recorrer las columnas de datos
						$cell_value = trim($sheet->getCell($col . $i)->getValue());
						if (!empty($cell_value)) {
							$is_row_empty = false; // Si alguna celda tiene valor, la fila no está vacía
							break; // No es necesario seguir verificando, la fila ya no está vacía
						}
					}
					
					if (!$is_row_empty) { // Si la fila no está vacía, procesarla
						$row = $this->row_assigned($sheet, $country, $i, $company_mapping);
						if (empty($row)) continue;
						
						foreach($data_ml as $item_ml){
							//echo '<pr>'; print_r($row);
							if ($row['division'] === $item_ml->division && $row['yyyy'] == $item_ml->yyyy &&  $row['mm'] == $item_ml->mm &&  $row['country'] === $item_ml->country &&
								$row['company'] === $item_ml->company && $row['y_2'] == $item_ml->y_2 &&  $row['y_1'] == $item_ml->y_1 &&  $row['bp'] == $item_ml->bp &&
								$row['target'] == $item_ml->target && $row['mp'] == $item_ml->mp &&  $row['monthly_report'] == $item_ml->monthly_report &&  $row['ml'] == $item_ml->ml &&
								$row['ml_actual'] == $item_ml->ml_actual && $row['m_1'] == $item_ml->m_1 &&  $row['m_2'] == $item_ml->m_2 &&  $row['m_3'] == $item_ml->m_3){
								$flag_unique = 0;
								break;
							}
							if ($row['division'] === $item_ml->division && $row['yyyy'] == $item_ml->yyyy &&  $row['mm'] == $item_ml->mm &&  $row['country'] === $item_ml->country){
								$flag_unique = 0;
								$where = ['division' => $row['division'], 'yyyy' => $row['yyyy'], 'mm' => $row['mm'], 'country' => $row['country']];
								$row['updated'] = date("Y-m-d H:i:s");
								$this->gen_m->update("lgepr_most_likely", $where, $row);
								break;
							}
							else $flag_unique = 1; //Not found in DB
						}
						
						if ($flag_unique == 1){
							$row['updated'] = date("Y-m-d H:i:s");
							$batch_data[] = $row;
							
							//print_r($batch_data);
							if(count($batch_data)>=$batch_size){
								$this->gen_m->insert_m("lgepr_most_likely", $batch_data);
								$batch_data = [];
								unset($batch_data);
							}
						} else continue;
					}
				}
				//print_r($batch_data);
				// Insertar cualquier dato restante en el lote
				
				//echo '<pre>'; print_r($batch_data);
				if (!empty($batch_data)) {
					$this->gen_m->insert_m("lgepr_most_likely", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}

				
			}

			elseif ($sheetName === 'UY'){
				$flag_unique = 1;
				$updated = date("Y-m-d H:i:s");
				$sheet = $spreadsheet->getSheetByName($sheetName);
				$max_row = $sheet->getHighestRow();
				$batch_data =[];
				$batch_size = 100;
				$country = 'UY';
				// Procesar datos desde la fila 6 en adelante
				// Iniciar transacción para mejorar rendimiento
				//$this->db->trans_start();
				for($i = 3; $i < $max_row; $i++){
					$is_row_empty = true; // Inicializar como fila vacía
					// Verificar si todas las celdas están vacías
					foreach (range('A', 'N') as $col) { // Recorrer las columnas de datos
						$cell_value = trim($sheet->getCell($col . $i)->getValue());
						if (!empty($cell_value)) {
							$is_row_empty = false; // Si alguna celda tiene valor, la fila no está vacía
							break; // No es necesario seguir verificando, la fila ya no está vacía
						}
					}
					
					if (!$is_row_empty) { // Si la fila no está vacía, procesarla
						$row = $this->row_assigned($sheet, $country, $i, $company_mapping);
						if (empty($row)) continue;
						
						foreach($data_ml as $item_ml){
							if ($row['division'] === $item_ml->division && $row['yyyy'] == $item_ml->yyyy &&  $row['mm'] == $item_ml->mm &&  $row['country'] === $item_ml->country &&
								$row['company'] === $item_ml->company && $row['y_2'] == $item_ml->y_2 &&  $row['y_1'] == $item_ml->y_1 &&  $row['bp'] == $item_ml->bp &&
								$row['target'] == $item_ml->target && $row['mp'] == $item_ml->mp &&  $row['monthly_report'] == $item_ml->monthly_report &&  $row['ml'] == $item_ml->ml &&
								$row['ml_actual'] == $item_ml->ml_actual && $row['m_1'] == $item_ml->m_1 &&  $row['m_2'] == $item_ml->m_2 &&  $row['m_3'] == $item_ml->m_3){
								$flag_unique = 0;
								break;
							}
							if ($row['division'] === $item_ml->division && $row['yyyy'] === $item_ml->yyyy &&  $row['mm'] === $item_ml->mm &&  $row['country'] === $item_ml->country){
								$flag_unique = 0;
								$where = ['division' => $row['division'], 'yyyy' => $row['yyyy'], 'mm' => $row['mm'], 'country' => $row['country']];
								$row['updated'] = date("Y-m-d H:i:s");
								$this->gen_m->update("lgepr_most_likely", $where, $row);
								break;
							}
							else $flag_unique = 1; //Not found in DB
						}
						
						if ($flag_unique == 1){
							$row['updated'] = date("Y-m-d H:i:s");
							$batch_data[]=$row;
							//print_r($batch_data);
							if(count($batch_data)>=$batch_size){
								$this->gen_m->insert_m("lgepr_most_likely", $batch_data);
								$batch_data = [];
								unset($batch_data);
							}
						}
					}
				}
				// Insertar cualquier dato restante en el lote
				
				if (!empty($batch_data)) {
					$this->gen_m->insert_m("lgepr_most_likely", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			
			elseif ($sheetName === 'PY'){
				$flag_unique = 1;
				$updated = date("Y-m-d H:i:s");
				$sheet = $spreadsheet->getSheetByName($sheetName);
				$max_row = $sheet->getHighestRow();
				$batch_data =[];
				$batch_size = 100;
				$country = 'PY';
				// Procesar datos desde la fila 6 en adelante
				// Iniciar transacción para mejorar rendimiento
				//$this->db->trans_start();
				for($i = 3; $i < $max_row; $i++){
					$is_row_empty = true; // Inicializar como fila vacía
					// Verificar si todas las celdas están vacías
					foreach (range('A', 'N') as $col) { // Recorrer las columnas de datos
						$cell_value = trim($sheet->getCell($col . $i)->getValue());
						if (!empty($cell_value)) {
							$is_row_empty = false; // Si alguna celda tiene valor, la fila no está vacía
							break; // No es necesario seguir verificando, la fila ya no está vacía
						}
					}
					
					if (!$is_row_empty) { // Si la fila no está vacía, procesarla
						$row = $this->row_assigned($sheet, $country, $i, $company_mapping);
						if (empty($row)) continue;
						
						foreach($data_ml as $item_ml){
							if ($row['division'] === $item_ml->division && $row['yyyy'] == $item_ml->yyyy &&  $row['mm'] == $item_ml->mm &&  $row['country'] === $item_ml->country &&
								$row['company'] === $item_ml->company && $row['y_2'] == $item_ml->y_2 &&  $row['y_1'] == $item_ml->y_1 &&  $row['bp'] == $item_ml->bp &&
								$row['target'] == $item_ml->target && $row['mp'] == $item_ml->mp &&  $row['monthly_report'] == $item_ml->monthly_report &&  $row['ml'] == $item_ml->ml &&
								$row['ml_actual'] == $item_ml->ml_actual && $row['m_1'] == $item_ml->m_1 &&  $row['m_2'] == $item_ml->m_2 &&  $row['m_3'] == $item_ml->m_3){
								$flag_unique = 0;
								break;
							}
							if ($row['division'] === $item_ml->division && $row['yyyy'] === $item_ml->yyyy &&  $row['mm'] === $item_ml->mm &&  $row['country'] === $item_ml->country){
								$flag_unique = 0;
								$where = ['division' => $row['division'], 'yyyy' => $row['yyyy'], 'mm' => $row['mm'], 'country' => $row['country']];
								$row['updated'] = date("Y-m-d H:i:s");
								$this->gen_m->update("lgepr_most_likely", $where, $row);
								break;
							}
							else $flag_unique = 1; //Not found in DB
						}
						
						if ($flag_unique == 1){
							$row['updated'] = date("Y-m-d H:i:s");
							$batch_data[]=$row;
							//print_r($batch_data);
							if(count($batch_data)>=$batch_size){
								$this->gen_m->insert_m("lgepr_most_likely", $batch_data);
								$batch_data = [];
								unset($batch_data);
							}
						}
					}
				}
				// Insertar cualquier dato restante en el lote
				
				if (!empty($batch_data)) {
					$this->gen_m->insert_m("lgepr_most_likely", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			
			// Recorrer las filas de datos (desde la fila de inicio de los valores)
			
		}

		// Finalizar transacción
		//$this->db->trans_complete();

		$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
		//print_r($msg); return;
		$this->db->trans_complete();
		return $msg;
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
				'file_name'		=> 'lgepr_most_likely.xlsx',
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
