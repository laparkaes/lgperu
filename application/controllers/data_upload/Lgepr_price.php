<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_price extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"stocks"	=> $this->gen_m->filter("lgepr_price", false, null, null, null, "", 1000),
			"main" 		=> "data_upload/lgepr_price/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function date_convert($date){//dd/mm/yyyy > yyyy-mm-dd
		if (empty($date)) {
			return null;
		}

		// Si la fecha es un número de serie de Excel
		if (is_numeric($date)) {
			$unix_date = ($date - 25569) * 86400;
			return gmdate("Y-m-d", $unix_date);
		}

		$dt = DateTime::createFromFormat('m/d/Y', $date);

		if ($dt === false) {
			return null; // Retorna null si la fecha no es válida
		}

		return $dt->format('Y-m-d');

	}
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//delete all rows lgepr_price
		$this->gen_m->truncate("lgepr_price");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/lgepr_price.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
		];
		
		//magento report header
		$header = ["Subsidiary", "Customer Type", "Customer", "Currency", "Model Code", "Unit Price"];
		
		// //header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

		
		
		if ($is_ok){
			// Obtener datos desde la fila 6 en adelante en un solo paso
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 200;

			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"subsidiary" 						=> trim($sheet->getCell('A'.$i)->getValue()),
					"customer_type" 					=> trim($sheet->getCell('B'.$i)->getValue()),
					"customer" 							=> trim($sheet->getCell('C'.$i)->getValue()),
					"currency" 							=> trim($sheet->getCell('E'.$i)->getValue()),
					"model_code" 						=> trim($sheet->getCell('F'.$i)->getValue()),
					"unit_price"						=> trim($sheet->getCell('H'.$i)->getValue()),
					"rpp_price" 						=> trim($sheet->getCell('I'.$i)->getValue()),
					"apply_date" 						=> trim($sheet->getCell('K'.$i)->getValue()),
					"product_level4"					=> trim($sheet->getCell('L'.$i)->getValue()),
					"price_list_line_id"				=> trim($sheet->getCell('O'.$i)->getValue()),
					"item_id"							=> trim($sheet->getCell('P'.$i)->getValue()),
					"status"							=> trim($sheet->getCell('Q'.$i)->getValue()),
					"request_date"						=> trim($sheet->getCell('R'.$i)->getValue()),		
					"expiration_date"					=> trim($sheet->getCell('S'.$i)->getValue()),
					"creation_date"						=> trim($sheet->getCell('T'.$i)->getValue()),
					"last_update_date"					=> trim($sheet->getCell('U'.$i)->getValue()),
					"updated"							=> $updated,
				];
				
				// Conversion de fechas
				// $row["invoice_date"] = $this -> date_convert($row["invoice_date"]);
				// $row["payment_due_date"] = $this -> date_convert($row["payment_due_date"]);
				// $row["payment_date"] = $this -> date_convert($row["payment_date"]);

				// Manejo de valores vacios end_date_ative					
				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("lgepr_price", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("lgepr_price", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			$this->db->trans_complete();
			return $msg;			
		}else return "";
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
				'file_name'		=> 'lgepr_price.xlsx',
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
