<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_sales_deduction extends CI_Controller {

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
			"ml"	=> $this->gen_m->filter("lgepr_sales_deduction", false, null, null, null, "", 100),
			"main" 		=> "data_upload/lgepr_sales_deduction/index",
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
	
	public function row_assigned($sheet, $i, $company_mapping, $country){
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
					"company"						=> $dash_mapping[trim($sheet->getCell('A'.$i)->getValue())],
					"division" 						=> trim($sheet->getCell('A'.$i)->getValue()),
					"yyyy" 							=> trim($sheet->getCell('B'.$i)->getValue()),
					"mm" 							=> trim($sheet->getCell('C'.$i)->getValue()),
					"mp_sales_deduction" 			=> trim($sheet->getCell('D'.$i)->getValue()) == 0 ? NULL : trim($sheet->getCell('D'.$i)->getValue()),
					"sd_rate" 						=> is_numeric(trim($sheet->getCell('E'.$i)->getValue())) ? round(trim($sheet->getCell('E'.$i)->getValue())/100, 4) : 0,
					"country"						=> $country,
					//"updated"						=> $updated,
				];
				
		return $row;
	}
	
	public function process_rows($max_row, $sheet, $company_mapping, $data_sd, $flag_unique, $country, $batch_data, $batch_size){
		for($i = 3; $i <= $max_row; $i++){
			$is_row_empty = true; // Inicializar como fila vacía

			// Verificar si todas las celdas están vacías
			foreach (range('A', 'E') as $col) { // Recorrer las columnas de datos
				$cell_value = trim($sheet->getCell($col . $i)->getValue());
				if (!empty($cell_value)) {
					$is_row_empty = false; // Si alguna celda tiene valor, la fila no está vacía
					break; // No es necesario seguir verificando, la fila ya no está vacía
				}
			}
			
			if (!$is_row_empty) { // Si la fila no está vacía, procesarla
				$row = $this->row_assigned($sheet, $i, $company_mapping, $country);
				if (empty($row)) continue;
				
				foreach($data_sd as $item_sd){
					if($item_sd->mp_sales_deduction == 0) $item_sd->mp_sales_deduction = NULL;
					//echo '<pr>'; print_r($item_sd);
					if ($row['division'] === $item_sd->division && $row['yyyy'] == $item_sd->yyyy &&  $row['mm'] == $item_sd->mm && 
						$row['mp_sales_deduction'] === $item_sd->mp_sales_deduction && $row['company'] === $item_sd->company && $row['sd_rate'] == $item_sd->sd_rate){
						$flag_unique = 0;
						break;
					}
					if ($row['division'] === $item_sd->division && $row['yyyy'] == $item_sd->yyyy &&  $row['mm'] == $item_sd->mm){
						$flag_unique = 0;
						$where = ['division' => $row['division'], 'yyyy' => $row['yyyy'], 'mm' => $row['mm']];
						$row['updated'] = date("Y-m-d H:i:s");
						$this->gen_m->update("lgepr_sales_deduction", $where, $row);
						break;
					}
					else $flag_unique = 1; //Not found in DB
				}
				
				if ($flag_unique == 1){
					$row['updated'] = date("Y-m-d H:i:s");
					$batch_data[] = $row;
					
					//print_r($batch_data);
					if(count($batch_data)>=$batch_size){
						$this->gen_m->insert_m("lgepr_sales_deduction", $batch_data);
						$batch_data = [];
						unset($batch_data);
					}
				} else continue;
			}
		}
		if (!empty($batch_data)) {
			$this->gen_m->insert_m("lgepr_sales_deduction", $batch_data);
			$batch_data = [];
			unset($batch_data);
		}

		
	}
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		//delete all rows ngsi_inventory 
		//$this->gen_m->truncate("lgepr_ml");
		
		$start_time = microtime(true);

		// Cargar el archivo Excel
		$spreadsheet = IOFactory::load("./upload/lgepr_sales_deduction.xlsx");
		
		$company_mapping = ["HS", "MS", "ES", "MC"];
		$filter_select = ['company', 'division', 'yyyy', 'mm', 'mp_sales_deduction', 'sd_rate'];
	
		$data_sd = $this->gen_m->filter_select('lgepr_sales_deduction', false, $filter_select);

		$is_ok = true;
	
		if ($is_ok){

			foreach($spreadsheet->getSheetNames() as $sheetName){
				
				if ($sheetName === 'PR'){					
					$sheet = $spreadsheet->getSheetByName($sheetName);
					$max_row = $sheet->getHighestRow();
					$batch_data = [];
					$batch_size = 100;
					$flag_unique = 1;
					$country = 'PR';
					$this->process_rows($max_row, $sheet, $company_mapping, $data_sd, $flag_unique, $country, $batch_data, $batch_size);
				}
				elseif ($sheetName === 'PY'){
					$sheet = $spreadsheet->getSheetByName($sheetName);
					$max_row = $sheet->getHighestRow();
					$batch_data = [];
					$batch_size = 100;
					$flag_unique = 1;
					$country = 'PY';
					
					$this->process_rows($max_row, $sheet, $company_mapping, $data_sd, $flag_unique, $country, $batch_data, $batch_size);
				}
				elseif ($sheetName === 'UY'){
					$sheet = $spreadsheet->getSheetByName($sheetName);
					$max_row = $sheet->getHighestRow();
					$batch_data = [];
					$batch_size = 100;
					$flag_unique = 1;
					$country = 'UY';
					
					$this->process_rows($max_row, $sheet, $company_mapping, $data_sd, $flag_unique, $country, $batch_data, $batch_size);
				}
			}
			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
			
			$this->db->trans_complete();
			return $msg;

			
		}else return '';

		// Finalizar transacción
		//$this->db->trans_complete();
		
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
				'file_name'		=> 'lgepr_sales_deduction.xlsx',
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
