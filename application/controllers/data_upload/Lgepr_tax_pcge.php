<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_tax_pcge extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"pcge"	=> $this->gen_m->filter("lgepr_tax_pcge", false, null, null, null, "", 1000),
			"main" 		=> "data_upload/lgepr_tax_pcge/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//delete all rows lgepr_price
		$this->gen_m->truncate("lgepr_tax_pcge");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/lgepr_tax_pcge.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
		];
		
		//magento report header
		$header = ["Concatenado", "Accounting Unit", "Account", "Account Desc", "PCGE","PCGE Decripción"];
		//print_r($h); echo '<br>'; return;
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
			//$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"concatenate" 				=> trim($sheet->getCell('A'.$i)->getValue()),
					"accounting_unit" 			=> trim($sheet->getCell('B'.$i)->getValue()),
					"account" 					=> trim($sheet->getCell('C'.$i)->getValue()),
					"account_desc" 				=> trim($sheet->getCell('D'.$i)->getValue()),
					"pcge" 						=> trim($sheet->getCell('E'.$i)->getValue()),
					"pcge_decripcion"			=> trim($sheet->getCell('F'.$i)->getValue()),
					"updated"					=> $updated,
				];
				// Manejo de valores vacios end_date_ative	
				
			//$row["pcge"] = 
			//print_r($row["pcge"]); echo '<br>';
				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("lgepr_tax_pcge", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("lgepr_tax_pcge", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			//$this->db->trans_complete();
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
				'file_name'		=> 'lgepr_tax_pcge.xlsx',
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
