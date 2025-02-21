<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Tax_purchase_register extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"stocks"	=> $this->gen_m->filter("tax_purchase_register", false, null, null, null, "", 1000),
			"main" 		=> "data_upload/tax_purchase_register/index",
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
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/tax_purchase_register.xlsx");
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
		];
		
		//magento report header
		$header = ["Voucher_No", "Header_Id", "Invoice_Date", "Payment_Due_Date", "Payment_Date", "Document Type", "Serial Number", "Invoice Number"];
		
		// //header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

		
		
		if ($is_ok){
			// Obtener datos desde la fila 6 en adelante en un solo paso
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 1000;

			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"voucher_no" 							=> trim($sheet->getCell('A'.$i)->getValue()),
					"header_id" 							=> trim($sheet->getCell('B'.$i)->getValue()),
					"invoice_date" 							=> trim($sheet->getCell('C'.$i)->getValue()),
					"payment_due_date" 						=> trim($sheet->getCell('D'.$i)->getValue()),
					"payment_date" 							=> trim($sheet->getCell('E'.$i)->getValue()),
					"document_type"							=> trim($sheet->getCell('F'.$i)->getValue()),
					"serial_number" 						=> trim($sheet->getCell('G'.$i)->getValue()),
					"invoice_number" 						=> trim($sheet->getCell('H'.$i)->getValue()),
					"customs_clearance_date"				=> trim($sheet->getCell('I'.$i)->getValue()),
					"customs_clearance_no"					=> trim($sheet->getCell('J'.$i)->getValue()),
					"tax_invoice_type_code"					=> trim($sheet->getCell('K'.$i)->getValue()),
					"customer_vat_no"						=> trim($sheet->getCell('L'.$i)->getValue()),
					"supplier_name"							=> trim($sheet->getCell('M'.$i)->getValue()),		
					"id_business_type"						=> trim($sheet->getCell('N'.$i)->getValue()),
					"tax_rate_code"							=> trim($sheet->getCell('O'.$i)->getValue()),
					"tax_rate_name"							=> trim($sheet->getCell('P'.$i)->getValue()),
					"report_net_amount"						=> trim($sheet->getCell('Q'.$i)->getValue()),
					"report_vat_amount"						=> trim($sheet->getCell('R'.$i)->getValue()),
					"inafecto_input"						=> trim($sheet->getCell('S'.$i)->getValue()),
					"inafecto_calculated"					=> trim($sheet->getCell('T'.$i)->getValue()),
					"total_amount"							=> trim($sheet->getCell('U'.$i)->getValue()),
					"exchange_rate"							=> trim($sheet->getCell('V'.$i)->getValue()),
					"entered_currency"						=> trim($sheet->getCell('W'.$i)->getValue()),
					"remark"								=> trim($sheet->getCell('X'.$i)->getValue()),
					"remark2"								=> trim($sheet->getCell('Y'.$i)->getValue()),
					"account_code"							=> trim($sheet->getCell('Z'.$i)->getValue()),
					"account_name"							=> trim($sheet->getCell('AA'.$i)->getValue()),
					"source_name"							=> trim($sheet->getCell('AB'.$i)->getValue()),
					"module"								=> trim($sheet->getCell('AC'.$i)->getValue()),
					"updated"								=> $updated,
				];
				
				// Conversion de fechas
				$row["invoice_date"] = $this -> date_convert($row["invoice_date"]);
				$row["payment_due_date"] = $this -> date_convert($row["payment_due_date"]);
				$row["payment_date"] = $this -> date_convert($row["payment_date"]);

				// Manejo de valores vacios end_date_ative					
				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("tax_purchase_register", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("tax_purchase_register", $batch_data);
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
				'file_name'		=> 'tax_purchase_register.xlsx',
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
