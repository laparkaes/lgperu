<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Ar_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$detail_data = $this->gen_m->filter("ar_detail", false, null, null, null, [['last_updated', 'desc']], 200);
		$aging_data = $this->gen_m->filter("ar_aging", false, null, null, null, [['last_updated', 'desc']], 200);
		$cash_data = $this->gen_m->filter("ar_cash_report", false, null, null, null, [['last_updated', 'desc']], 200);
		$data = [
			"detail_data"	=> $detail_data,
			"aging_data"	=> $aging_data,
			"cash_data"		=> $cash_data,
			"main" 			=> "data_upload/ar_report/index",
		];
		
		$this->load->view('layout', $data);
	}
		
	public function convert_date($excel_date) {
		if (is_numeric($excel_date)) {
			$unix_date = ($excel_date - 25569) * 86400;
			$date_object = new DateTime("@$unix_date");
			$date_object->setTimezone(new DateTimeZone('UTC'));
			return $date_object->format('Y-m-d');
		}

		$date_formats = ['d-M-y', 'd-M-Y', 'm/d/Y', 'Y-m-d', 'Ymd', 'd/m/Y'];
		foreach ($date_formats as $format) {
			$date_object = DateTime::createFromFormat($format, $excel_date); 
			if ($date_object) {
				return $date_object->format('Y-m-d');
			}
		}
		return null;
	}
	
	public function ar_convert_date($excel_date, $format) {
		$date_object = DateTime::createFromFormat($format, $excel_date);
		if ($date_object) {
			return $date_object->format('Y-m-d');
		}
		return null;
	}
	
	public function process_ar_detail($filename = "ar_detail.tsv", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		$file_path = "./upload/" . $filename;

		$key_list = [];
		$data_cash = $this->gen_m->filter_select('ar_detail', false, 'key_detail'); 
		foreach($data_cash as $item) $key_list[$item->key_detail] = $item->key_detail;

		if (($handle = fopen($file_path, "r")) === FALSE) {
			return "Error opening file: Could not read $filename.";
		}

		$h_file = fgetcsv($handle, 0, "\t"); 

		$h_origin = ["Invoice No.", "AR Class", "AR Type", "Trx Date", "Due Date", "GL Date"];
		
		$is_ok = true;
		if (count($h_file) < 6) {
			$is_ok = false;
		} else {
			for ($i = 0; $i < count($h_origin); $i++) {
				// if (trim($h_file[$i]) !== $h_origin[$i]) $is_ok = false;
			}
		}

		if ($is_ok){
			$batch_data = []; $batch_data_update = [];
			$inserted_rows = 0; $updated_rows = 0;
			$processed_rows = 0;
			$updated = date("Y-m-d H:i:s");
			
			while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
				$processed_rows++;

				if (count($data) < 14) continue;
				$row = [				
					"invoice_no" 							=> trim($data[0] ?? null),
					"ar_class"								=> trim($data[1] ?? null),
					"ar_type" 								=> trim($data[2] ?? null),
					"trx_date" 								=> trim($data[3] ?? null),				
					"due_date" 								=> trim($data[4] ?? null),
					"gl_date"								=> trim($data[5] ?? null),
					"period"								=> trim($data[6] ?? null),						
					"currency"								=> trim($data[7] ?? null),
					"original_amount_entered_curr" 			=> trim($data[8] ?? null),
					"offset" 								=> trim($data[9] ?? null),
					"cash_receipt" 							=> trim($data[10] ?? null),
					"on_account"							=> trim($data[11] ?? null),
					"note_to_cash"							=> trim($data[12] ?? null),				
					"cash_discount"							=> trim($data[13] ?? null),			
					"other_expense"							=> trim($data[14] ?? null),
					"note" 									=> trim($data[15] ?? null),
					"note_balance" 							=> trim($data[16] ?? null),
					"balance_total" 						=> trim($data[17] ?? null),
					"original_amount_functional_curr"		=> trim($data[18] ?? null),
					"receipt_amount_functional_curr"		=> trim($data[19] ?? null),				
					"on_account_functional"					=> trim($data[20] ?? null),				
					"balance_total_functional_curr"			=> trim($data[21] ?? null),			
					"opc_balance"							=> trim($data[22] ?? null),
					"hq_balance_except_opc" 				=> trim($data[23] ?? null),
					"batch_source" 							=> trim($data[24] ?? null),
					"transaction_type" 						=> trim($data[25] ?? null),
					"bill_to_code"							=> trim($data[26] ?? null),
					"bill_to_name"							=> trim($data[27] ?? null),			
					"au"									=> trim($data[28] ?? null),			
					"department"							=> trim($data[29] ?? null),
					"account" 								=> trim($data[30] ?? null),
					"payment_term"							=> trim($data[31] ?? null),
					"order_number" 							=> trim($data[32] ?? null),
					"model_category"						=> trim($data[33] ?? null),
					"reference_no"							=> trim($data[34] ?? null),
					"po_no"									=> trim($data[35] ?? null),
					"salesperson"							=> trim($data[36] ?? null),			
					"ship_to_code"							=> trim($data[37] ?? null),
					"ship_to_name"							=> trim($data[38] ?? null),
					"voucher_no"							=> trim($data[39] ?? null),
					"comments"								=> trim($data[40] ?? null),
					"ar_no"									=> trim($data[41] ?? null),
					"bad_ar"								=> trim($data[42] ?? null),
					"commbiz_no"							=> trim($data[43] ?? null),
					"collector"								=> trim($data[44] ?? null),
					"reason_code"							=> trim($data[45] ?? null),
					"sales_channel"							=> trim($data[46] ?? null),
					"dd_status"								=> trim($data[47] ?? null),
					"invoice_date"							=> trim($data[48] ?? null),
					"creation_date"							=> trim($data[49] ?? null),
					"fapiao_no"								=> trim($data[50] ?? null),
					"biz_no"								=> trim($data[51] ?? null),
					"worksheet_remark"						=> trim($data[52] ?? null),
					"last_updated"							=> $updated,
				];
				
				// Convert Dates
				$row["trx_date"] = $this->convert_date($row["trx_date"]);
				$row["due_date"] = $this->convert_date($row["due_date"]);
				$row["gl_date"] = $this->convert_date($row["gl_date"]);
				$row["invoice_date"] = $this->convert_date($row["invoice_date"]);
				$row["creation_date"] = $this->convert_date($row["creation_date"]);
				$row["key_detail"] = $row['invoice_no'] . "_" . $row['gl_date'] . "_" . $row['period'] . "_" . $row['currency'] . "_" . $row['original_amount_entered_curr'] ."_". $row['balance_total'] . "_" . $row['ar_no'] . "_" . $row['reference_no'];
				
				if (!in_array($row['key_detail'], $key_list)) {
					$row['status'] = 'Por Cobrar';
					$batch_data[] = $row;
				}else {
					$row['status'] = 'Cobrado';
					$batch_data_update[] = $row; 
				}
				
				
				if (count($batch_data) > 1000) {
					$this->gen_m->insert_m("ar_detail", $batch_data);
					$inserted_rows += count($batch_data);
					$batch_data = [];
					unset($batch_data);
				}
				
				if (count($batch_data_update) > 1000) {
					$this->gen_m->update_multi('ar_detail', $batch_data_update, 'key_detail');
					$updated_rows += count($batch_data_update);
					$batch_data_update = [];
					unset($batch_data_update);
				}
				
			}
			
			if(!empty($batch_data)){
				$this->gen_m->insert_m("ar_detail", $batch_data);
				$inserted_rows += count($batch_data);
			}
			if(!empty($batch_data_update)){
				$this->gen_m->update_multi('ar_detail', $batch_data_update, 'key_detail');
				$updated_rows += count($batch_data_update);
			}
			
			fclose($handle);

			$msg = $inserted_rows . " rows inserted.<br>";
			$msg .= $updated_rows . " rows updated.<br>";
			$msg .= "Total time: " . number_Format(microtime(true) - $start_time, 2) . " secs.";
			
			return $msg;
		} else {
			if (isset($handle) && $handle !== FALSE) fclose($handle);
			return "";
		}
	}
	
	public function process_ar_aging($filename = "ar_aging.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);

		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		
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

		//sales order header
		$h_origin = ["Period", "ER", "Index1", "Index2", "Index3", "Tipo", "Name", "Class", "Invoice No", "Bill Code", "Bill Name"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $is_ok = false;
		
		if ($is_ok){
			$batch_data = []; $batch_data_update = [];
			$inserted_rows = 0; $updated_rows = 0;
			$updated = date("Y-m-d H:i:s");
			$max_row = $sheet->getHighestRow();
			
			$key_list = [];
			$data_aging = $this->gen_m->filter_select('ar_aging', false, 'key_aging');
			foreach($data_aging as $item) $key_list[$item->key_aging] = $item->key_aging;
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"period" 					=> trim($sheet->getCell('A'.$i)->getValue()) ?? null,
					"er" 						=> trim($sheet->getCell('B'.$i)->getValue()) ?? null,
					"index1"					=> trim($sheet->getCell('C'.$i)->getValue()) ?? null,
					"index2" 					=> trim($sheet->getCell('D'.$i)->getValue()) ?? null,
					"index3" 					=> trim($sheet->getCell('E'.$i)->getValue()) ?? null,
					"type" 						=> trim($sheet->getCell('F'.$i)->getValue()) ?? null,
					"name"						=> trim($sheet->getCell('G'.$i)->getValue()) ?? null,
					"class"						=> trim($sheet->getCell('H'.$i)->getValue()) ?? null,
					"invoice_no"				=> trim($sheet->getCell('I'.$i)->getValue()) ?? null,
					"bill_code" 				=> trim($sheet->getCell('J'.$i)->getValue()) ?? null,
					"bill_name" 				=> trim($sheet->getCell('K'.$i)->getValue()) ?? null,
					"currency" 					=> trim($sheet->getCell('L'.$i)->getValue()) ?? null,
					"ar_type"					=> trim($sheet->getCell('M'.$i)->getValue()) ?? null,
					"amount"					=> trim($sheet->getCell('N'.$i)->getValue()) ?? null,
					"invoice_date"				=> trim($sheet->getCell('O'.$i)->getValue()) ?? null,
					"overdue_amount"			=> trim($sheet->getCell('P'.$i)->getValue()) ?? null,
					"overdue_days" 				=> trim($sheet->getCell('Q'.$i)->getValue()) ?? null,
					"due_date" 					=> trim($sheet->getCell('R'.$i)->getValue()) ?? null,
					"overdue_reason" 			=> trim($sheet->getCell('S'.$i)->getValue()) ?? null,
					"au"						=> trim($sheet->getCell('T'.$i)->getValue()) ?? null,
					"department"				=> trim($sheet->getCell('U'.$i)->getValue()) ?? null,
					"account_code"				=> trim($sheet->getCell('V'.$i)->getValue()) ?? null,
					"sales_person"				=> trim($sheet->getCell('W'.$i)->getValue()) ?? null,
					"reference_no"				=> trim($sheet->getCell('X'.$i)->getValue()) ?? null,
					"trx_number" 				=> trim($sheet->getCell('Y'.$i)->getValue()) ?? null,
					"remark" 					=> trim($sheet->getCell('Z'.$i)->getValue()) ?? null,
					"bad_ar" 					=> trim($sheet->getCell('AA'.$i)->getValue()) ?? null,
					"model_category"			=> trim($sheet->getCell('AB'.$i)->getValue()) ?? null,
					"payment_term_name"			=> trim($sheet->getCell('AC'.$i)->getValue()) ?? null,
					"sales_channel_name"		=> trim($sheet->getCell('AD'.$i)->getValue()) ?? null,
					"reason_code"				=> trim($sheet->getCell('AE'.$i)->getValue()) ?? null,
					"installment_seq" 			=> trim($sheet->getCell('AF'.$i)->getValue()) ?? null,
					"last_updated"				=> $updated,
				];
				
				
				// Convert Dates
				$row["invoice_date"] = $this->ar_convert_date($row["invoice_date"], 'Ymd');			
				$row["due_date"] = $this->ar_convert_date($row["due_date"], 'Ymd');
				
				$row['key_aging'] = $row["period"] . "_" . $row["invoice_no"] . "_" . $row["bill_code"] . "_" . $row["bill_name"] . "_" . $row["currency"] . "_" . $row["trx_number"] . "_" . $row["amount"] . "_" . $row["reference_no"];
				if (!in_array($row['key_aging'], $key_list)) {
					$row["status"] = 'Por Cobrar';
					$batch_data[] = $row;
				} else {
					$row["status"] = 'Cobrado';
					$batch_data_update[] = $row;
				}
								
				if (count($batch_data) > 1000) {
					$this->gen_m->insert_m("ar_aging", $batch_data);
					$inserted_rows += count($batch_data);
					$batch_data = [];
					unset($batch_data);
				}
				
				if (count($batch_data_update) > 1000) {
					$this->gen_m->update_multi('ar_aging', $batch_data_update, 'key_aging');
					$updated_rows += count($batch_data_update);
					$batch_data_update = [];
					unset($batch_data_update);
				}
			}

			if(!empty($batch_data)){
				$this->gen_m->insert_m("ar_aging", $batch_data);
				$inserted_rows += count($batch_data);
			}
			
			if(!empty($batch_data_update)){
				$this->gen_m->update_multi('ar_aging', $batch_data_update, 'key_aging');
				$updated_rows += count($batch_data_update);
			}
			
			$msg = $inserted_rows . " rows inserted.<br>";
			$msg .= $updated_rows . " rows updated.<br>";
			$msg .= "Total time: " . number_Format(microtime(true) - $start_time, 2) . " secs.";
			return  $msg;
		} else return "";
	}
	
	public function process_ar_cash_report($filename = "ar_cash_report.tsv", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		$file_path = "./upload/" . $filename;

		$key_list = [];
		$data_cash = $this->gen_m->filter_select('ar_cash_report', false, 'key_cash_report'); 
		foreach($data_cash as $item) $key_list[$item->key_cash_report] = $item->key_cash_report;

		if (($handle = fopen($file_path, "r")) === FALSE) {
			return "Error opening file: Could not read $filename.";
		}

		$h_file = fgetcsv($handle, 0, "\t"); 

		$h_origin = ["Invoice No.", "AR Class", "AR Type", "Trx Date", "Due Date", "GL Date"];
		
		$is_ok = true;
		if (count($h_file) < 6) {
			$is_ok = false;
		} else {
			for ($i = 0; $i < count($h_origin); $i++) {
				 // if (trim($h_file[$i]) !== $h_origin[$i]) $is_ok = false;
			}
		}

		if ($is_ok){
			$batch_data = [];
			$inserted_rows = 0;
			$processed_rows = 0;
			$updated = date("Y-m-d H:i:s");
			
			while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
				$processed_rows++;

				if (count($data) < 14) continue;
				$row = [
					"statement_id" 					=> trim($data[1] ?? null),
					"gl_date"						=> trim($data[2] ?? null),
					"deposit_amount" 				=> str_replace(',', '', $data[3]) ?? null,
					"bill_to_code" 					=> trim($data[4] ?? null),				
					"bill_to_name" 					=> trim($data[5] ?? null),
					"deposit_currency"				=> trim($data[6] ?? null),
					"alloc_amount"					=>str_replace(',', '', $data[7]) ?? null,						
					"bank_name"						=> trim($data[8] ?? null),
					"bank_account" 					=> trim($data[9] ?? null),
					"status" 						=> trim($data[10] ?? null),
					"requested_date" 				=> trim($data[11] ?? null),
					"requested_by"					=> trim($data[12] ?? null),
					"receipt_type"					=> trim($data[13] ?? null),				
					"batch_no"						=> trim($data[14] ?? null),			
					"last_updated"					=> $updated,
				];
				
				
	
				$row["gl_date"] = $this->ar_convert_date($row["gl_date"], 'd/m/Y');
				$row["requested_date"] = $this->ar_convert_date($row["requested_date"], 'd/m/Y');
				$row['key_cash_report'] = $row["statement_id"] . "_" . $row["gl_date"] . "_" . $row["deposit_amount"] . "_" . $row["bill_to_code"] . "_" . $row["bill_to_name"] . "_" . $row["bank_name"];
				
				if (!isset($key_list[$row['key_cash_report']])) {
					$batch_data[] = $row;
				}
				
				if (count($batch_data) >= 200) {
					$this->gen_m->insert_m("ar_cash_report", $batch_data);
					$inserted_rows += count($batch_data);
					
					// Limpiar el lote
					$batch_data = [];
				}
				
			}

			if(!empty($batch_data)){
				$this->gen_m->insert_m("ar_cash_report", $batch_data); 
				$inserted_rows += count($batch_data);
			}
			
			fclose($handle);

			$msg = $inserted_rows . " rows inserted.<br>";
			$msg .= "Total time: " . number_Format(microtime(true) - $start_time, 2) . " secs.";
			
			return $msg;
		} else {
			if (isset($handle) && $handle !== FALSE) fclose($handle);
			return "";
		}
	}

	public function upload_ar_detail(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ar_detail.tsv',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_ar_detail();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function upload_ar_aging(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ar_aging.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_ar_aging();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function upload_ar_cash_report(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ar_cash_report.tsv',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_ar_cash_report();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}