<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Ngsi_inventory extends CI_Controller {

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
			"stocks"	=> $this->gen_m->filter("ngsi_inventory", false, null, null, null, "", 100),
			"main" 		=> "data_upload/ngsi_inventory/index",
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
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		//delete all rows ngsi_inventory 
		$this->gen_m->truncate("ngsi_inventory");
		
		$start_time = microtime(true);
		// $updated = date("Y-m-d");
		// $batch_size = 100;

	
		// Cargar el archivo Excel
		$spreadsheet = IOFactory::load("./upload/ngsi_inventory.xlsx");

		// Iniciar transacción para mejorar el rendimiento
		//$this->db->trans_start();

		foreach ($spreadsheet->getSheetNames() as $sheetName) {
			//print_r($sheetName);
			if ($sheetName === 'NGSI by Model'){
				$updated = date("Y-m-d");
				$sheet = $spreadsheet->getSheetByName($sheetName);
				$max_row = $sheet->getHighestRow();
				$batch_data =[];
				$batch_size = 100;
				// Procesar datos desde la fila 6 en adelante
				// Iniciar transacción para mejorar rendimiento
				$this->db->trans_start();
				for($i = 8; $i < $max_row; $i++){
					$is_row_empty = true; // Inicializar como fila vacía
					// Verificar si todas las celdas están vacías
					foreach (range('B', 'AI') as $col) { // Recorrer las columnas de datos
						$cell_value = trim($sheet->getCell($col . $i)->getValue());
						if (!empty($cell_value)) {
							$is_row_empty = false; // Si alguna celda tiene valor, la fila no está vacía
							break; // No es necesario seguir verificando, la fila ya no está vacía
						}
					}
					
					if (!$is_row_empty) { // Si la fila no está vacía, procesarla
						$row = [
							"date" 										=> trim($sheet->getCell('B'.$i)->getValue()),
							"division" 									=> trim($sheet->getCell('C'.$i)->getValue()),
							"model"										=> trim($sheet->getCell('H'.$i)->getValue()),						
							"inv_org" 									=> trim($sheet->getCell('I'.$i)->getValue()),
							"inv_org_description" 						=> trim($sheet->getCell('J'.$i)->getValue()),
							"sub_inv_grade" 							=> trim($sheet->getCell('K'.$i)->getValue()),
							"sub_inv"									=> trim($sheet->getCell('L'.$i)->getValue()),
							"qty_onhand" 								=> trim($sheet->getCell('M'.$i)->getValue()),
							"qty_in_transit" 							=> trim($sheet->getCell('N'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('N'.$i)->getValue()),
							"qty_30"									=> trim($sheet->getCell('O'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('O'.$i)->getValue()),
							"qty_60"									=> trim($sheet->getCell('P'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('P'.$i)->getValue()),
							"qty_90"									=> trim($sheet->getCell('Q'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('Q'.$i)->getValue()),
							"qty_120"									=> trim($sheet->getCell('R'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('R'.$i)->getValue()),
							"qty_150"									=> trim($sheet->getCell('S'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('S'.$i)->getValue()),		
							"qty_180"									=> trim($sheet->getCell('T'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('T'.$i)->getValue()),
							"qty_360"									=> trim($sheet->getCell('U'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('U'.$i)->getValue()),
							"qty_over_360"								=> trim($sheet->getCell('V'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('V'.$i)->getValue()),
							"qty_non_history"							=> trim($sheet->getCell('W'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('W'.$i)->getValue()),
							"amt_onhand_1"								=> trim($sheet->getCell('X'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('X'.$i)->getValue()),
							"amt_in_transit_1"							=> trim($sheet->getCell('Y'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('Y'.$i)->getValue()),
							"amt_30_1"									=> trim($sheet->getCell('Z'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('Z'.$i)->getValue()),
							"amt_60_1"									=> trim($sheet->getCell('AA'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AA'.$i)->getValue()),
							"amt_90_1"									=> trim($sheet->getCell('AB'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AB'.$i)->getValue()),
							"amt_120_1"									=> trim($sheet->getCell('AC'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AC'.$i)->getValue()),
							"amt_150_1"									=> trim($sheet->getCell('AD'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AD'.$i)->getValue()),
							"amt_180_1"									=> trim($sheet->getCell('AE'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AE'.$i)->getValue()),
							"amt_360_1"									=> trim($sheet->getCell('AF'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AF'.$i)->getValue()),
							"over_360_1"								=> trim($sheet->getCell('AG'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AG'.$i)->getValue()),
							"non_history_1"								=> trim($sheet->getCell('AH'.$i)->getValue()) == 0 ? "" : trim($sheet->getCell('AH'.$i)->getValue()),
							"remark"									=> trim($sheet->getCell('AI'.$i)->getValue()),
							"updated"									=> $updated,
						];
						
						// Manejo de valores vacios end_date_ative
						//print_r($row);
						
						$row['date'] = date("Y-m-d", strtotime($row['date']));
						
						if($row['inv_org_description'] === ''){
							$row['inv_org_description'] = 'KLO';
						}
						
						$batch_data[]=$row;
						//print_r($batch_data);
						if(count($batch_data)>=$batch_size){
							$this->gen_m->insert_m("ngsi_inventory", $batch_data);
							$batch_data = [];
							unset($batch_data);
						}
						//$this->gen_m->insert("ar_mdms", $row);
						//$this->update_model_category();
					}
				}
				// Insertar cualquier dato restante en el lote
				
				if (!empty($batch_data)) {
					$this->gen_m->insert_m("ngsi_inventory", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}

				$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
				//print_r($msg); return;
				$this->db->trans_complete();
				return $msg;
			}
			
		}

		// Finalizar transacción
		//$this->db->trans_complete();

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
				'file_name'		=> 'ngsi_inventory.xlsx',
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
