<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_inv_cbm extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"inv"		=> $this->gen_m->filter("lgepr_inv_cbm", false, null, null, null, "", 100),
			"main" 		=> "data_upload/lgepr_inv_cbm/index",
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
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}

		return null;
	}

	public function date_convert_dd_mm_yyyy($date) {
    // Intentamos convertir con la lógica del valor numérico (excel date)
		if (is_numeric($date)) {
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número, intentamos convertir con la lógica de fecha en formato dd/mm/yyyy
		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
	}
	
	function getExcelColumnLetter($colNum) {
		$excelColumn = '';
		while ($colNum > 0) {
			$modulo = ($colNum - 1) % 26;
			$excelColumn = chr(65 + $modulo) . $excelColumn;
			$colNum = intval(($colNum - 1) / 26);
		}
		return $excelColumn;
	}
	
	public function row_assigned($sheet, $i){
		$updated = date("Y-m-d H:i:s");
		$row = [];

		$company = trim($sheet->getCell('C'.$i)->getValue()) ?? null;
		$division = trim($sheet->getCell('D'.$i)->getValue()) ?? null;
		$model = trim($sheet->getCell('F'.$i)->getValue()) ?? null;
		$model_gross_cbm = round(trim($sheet->getCell('G'.$i)->getValue()), 3) ?? null;
		$inventory_org_code = trim($sheet->getCell('H'.$i)->getValue()) ?? null;
		$subinventory_code = trim($sheet->getCell('I'.$i)->getValue()) ?? null;
		$begining_qty = round(trim($sheet->getCell('J'.$i)->getValue()), 3) ?? null;
		$printOnText = trim($sheet->getCell('B5')->getValue());
		if (preg_match('/Print On: (\w+) (\d{1,2}) (\d{4})/', $printOnText, $matches)) {
			$monthName = $matches[1];
			$year = $matches[3];

			// Convertir el nombre del mes a número
			$monthNumber = date('n', strtotime($monthName));

			$periodValue = $year . "-" . sprintf("%02d", $monthNumber);
		} else {
			// Manejo de error si el formato del texto no coincide
			$periodValue = null;
			log_message('error', 'No se pudo extraer el mes y el año de la celda B6: ' . $printOnText);
		}

		$row = [
			"company"             => $company,
			"division"            => $division,
			"model"               => $model,
			"model_gross_cbm"     => $model_gross_cbm,
			"inventory_org_code"  => $inventory_org_code,
			"subinventory_code"   => $subinventory_code,
			"begining_qty"        => $begining_qty,
		];

		for ($day = 1; $day <= 31; $day++) {
			$colIndex = 11 + $day; // La columna L es la 12ª (índice 11 si empezamos desde 0)
			$col = $this->getExcelColumnLetter($colIndex);

			$total_cbm_cell = $sheet->getCell($col . ($i))->getValue();
			$balance_cell = $sheet->getCell($col . ($i + 1))->getValue();
			$in_cell = $sheet->getCell($col . ($i + 2))->getValue();
			$out_cell = $sheet->getCell($col . ($i + 3))->getValue();

			$row["total_cbm_day" . $day] = (trim($total_cbm_cell) !== '') ? round(trim($total_cbm_cell), 3) : null;
			$row["balance_day" . $day] = (trim($balance_cell) !== '') ? round(trim($balance_cell), 3) : null;
			$row["in_day" . $day] = (trim($in_cell) !== '') ? round(trim($in_cell), 3) : null;
			$row["out_day" . $day] = (trim($out_cell) !== '') ? round(trim($out_cell), 3) : null;
		}
		$row["period"] = $periodValue;
		$row["updated"] = $updated;
		//print_r($row);
		return $row;
	}

	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		//delete all rows ngsi_inventory 
		$this->gen_m->truncate("lgepr_inv_cbm");
		
		$start_time = microtime(true);

		// Cargar el archivo Excel
		$spreadsheet = IOFactory::load("./upload/lgepr_inv_cbm.xlsx");

		// Iniciar transacción para mejorar el rendimiento
		//$this->db->trans_start();

		
		$sheet_names = ['N4M(APM Warehouse)', 'N4S(KLO Warehouse)', 'N4J(CY)', 'N4D(CY)'];
		foreach ($spreadsheet->getSheetNames() as $sheetName) {
			//print_r($sheetName);
			if (in_array($sheetName, $sheet_names)){

				$sheet = $spreadsheet->getSheetByName($sheetName);
				$max_row = $sheet->getHighestRow();
				$batch_data = [];
				$batch_size = 100;
				
				// Procesar datos desde la fila 6 en adelante
				// Iniciar transacción para mejorar rendimiento
				//$this->db->trans_start();
				for($i = 10; $i < $max_row; $i+=4){
					$is_row_empty = true; // Inicializar como fila vacía
					// Verificar si todas las celdas están vacías
					foreach (range('C', 'AP') as $col) { // Recorrer las columnas de datos
						$cell_value = trim($sheet->getCell($col . $i)->getValue());
						if (!empty($cell_value)) {
							$is_row_empty = false; // Si alguna celda tiene valor, la fila no está vacía
							break; // No es necesario seguir verificando, la fila ya no está vacía
						}
					}
					
					if (!$is_row_empty) { // Si la fila no está vacía, procesarla
						$row = $this->row_assigned($sheet, $i);
						if (empty($row)) continue;
						
						$batch_data[] = $row;
							
						//print_r($batch_data);
						if(count($batch_data)>=$batch_size){
							$this->gen_m->insert_m("lgepr_inv_cbm", $batch_data);
							$batch_data = [];
							unset($batch_data);
						}
						
					}
				}
				//print_r($batch_data);
				// Insertar cualquier dato restante en el lote
				
				//echo '<pre>'; print_r($batch_data);
				if (!empty($batch_data)) {
					$this->gen_m->insert_m("lgepr_inv_cbm", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}

				
			}

		}

		// Finalizar transacción
		//$this->db->trans_complete();

		$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
		//print_r($msg); return;
		//$this->db->trans_complete();
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
				'file_name'		=> 'lgepr_inv_cbm.xlsx',
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
