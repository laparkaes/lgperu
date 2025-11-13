<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Ap_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"pcge"	=> $this->gen_m->filter("ap_report", false, null, null, null, "", 1000),
			"main" 		=> "data_upload/ap_report/index",
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
		
		$date_formats = ['d-M-y', 'd-M-Y', 'm/d/Y', 'Y-m-d'];
		foreach ($date_formats as $format) {
			$date_object = DateTime::createFromFormat($format, $excel_date);
			if ($date_object) {
				return $date_object->format('Y-m-d');
			}
		}
		return null;
	}
	
	function get_month_from_week($week_number, $year = null) {
		if ($year === null) {
			$year = date('Y');
		}
		
		$date_string = "{$year}W{$week_number}";
		
		$monday_timestamp = strtotime($date_string);

		if ($monday_timestamp === false) {
			return null;
		}

		$month_number_string = date('m', $monday_timestamp);
		
		return [
			'month_number' => $month_number_string,
			'year' => (int) $year
		];
	}

	public function process($filename) {
		set_time_limit(0);
		ini_set("memory_limit", -1);

		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/ap_report.xlsx");
		$sheet = $spreadsheet->getSheetByName('MP');
		//$sheet = $spreadsheet->getActiveSheet(0);
		
		// Get date values from file name
		$pattern = '/SEMANA\s*(\d+)\s*-\s*(\d{4})\.xlsx/i';
		if (preg_match($pattern, $filename, $matches)) {
			$week = $matches[1]; // Week number

			$month_year_block = $matches[2]; // mmyy
			
			$month = substr($month_year_block, 0, 2); // mm
			$year_short = substr($month_year_block, 2, 2); // yy
			
			$year = "20" . $year_short; // Format "20xx"
		}

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
		
		$header = ["Company", "Division Name", "Division Name Desc", "Supplier", "Department Name", "Department Name Desc", "Account Name", "Account Name Desc"];

		// header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;		
		
		if ($is_ok){
			$updated = date("Y-m-d H:i:s");
			$max_row = $sheet->getHighestRow();
			$batch_data =[]; $batch_exist_data = [];
			$batch_size = 80;
			$key_list = []; $inserted_rows = 0; $updated_rows = 0;
			$data_ap_list = [];
			
			$data_key = $this->gen_m->filter('ap_report', false);
			if ($data_key) {
				foreach ($data_key as $item) {
					$key_list[] = $item->key_ap;
					$data_ap_list[$item->key_ap] = $item;
				}
			}

			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"year"							=> $year,
					"company" 						=> trim($sheet->getCell('A'.$i)->getValue()),
					"division_name" 				=> trim($sheet->getCell('B'.$i)->getValue()),
					"division_name_desc" 			=> trim($sheet->getCell('C'.$i)->getValue()),
					"supplier" 						=> trim($sheet->getCell('D'.$i)->getValue()),
					"department_name" 				=> trim($sheet->getCell('E'.$i)->getValue()),
					"department_name_desc" 			=> trim($sheet->getCell('F'.$i)->getValue()),
					"account_name"					=> trim($sheet->getCell('G'.$i)->getValue()),
					"account_name_desc"				=> trim($sheet->getCell('H'.$i)->getValue()),
					"invoice_num"					=> trim($sheet->getCell('I'.$i)->getValue()),
					"invoice_date"					=> trim($sheet->getCell('J'.$i)->getValue()),
					"currency"						=> trim($sheet->getCell('K'.$i)->getValue()),
					"invoice_amount"				=> trim($sheet->getCell('L'.$i)->getValue()),
					"payment_amount"				=> trim($sheet->getCell('M'.$i)->getValue()),
					"porcentage_3"					=> trim($sheet->getCell('N'.$i)->getCalculatedValue()),
					"amount_remaining"				=> trim($sheet->getCell('O'.$i)->getCalculatedValue()),
					"pay_terms"						=> trim($sheet->getCell('P'.$i)->getValue()),
					"file_num"						=> trim($sheet->getCell('Q'.$i)->getValue()),
					"due_date"						=> trim($sheet->getCell('R'.$i)->getValue()),					
					"week"							=> trim($sheet->getCell('S'.$i)->getValue()),
					"porcentage_3_378"				=> trim($sheet->getCell('T'.$i)->getCalculatedValue()),
					"exchange_rate"					=> trim($sheet->getCell('U'.$i)->getValue()),
					"invoice_amount_fun"			=> trim($sheet->getCell('V'.$i)->getValue()),
					"payment_amount_fun"			=> trim($sheet->getCell('W'.$i)->getValue()),
					"amount_remaining_fun"			=> trim($sheet->getCell('X'.$i)->getValue()),
					"description"					=> trim($sheet->getCell('Y'.$i)->getValue()),
					"voucher_number"				=> trim($sheet->getCell('Z'.$i)->getValue()),
					"gl_date"						=> trim($sheet->getCell('AA'.$i)->getValue()),
					"creation_date"					=> trim($sheet->getCell('AB'.$i)->getValue()),
					"created_by"					=> trim($sheet->getCell('AC'.$i)->getValue()),
					"source"						=> trim($sheet->getCell('AD'.$i)->getValue()),
					"supplier_code"					=> trim($sheet->getCell('AE'.$i)->getValue()),
					"pay_group"						=> trim($sheet->getCell('AF'.$i)->getValue()),
					"biz_reg_no"					=> trim($sheet->getCell('AG'.$i)->getValue()),
					"consulta_ruc"					=> trim($sheet->getCell('AH'.$i)->getCalculatedValue()),
					"batch_name"					=> trim($sheet->getCell('AI'.$i)->getValue()),
					"invoice_received_date"			=> trim($sheet->getCell('AJ'.$i)->getValue()),
					"item_amount"					=> trim($sheet->getCell('AK'.$i)->getValue()),
					"tax_amount"					=> trim($sheet->getCell('AL'.$i)->getValue()),
					"pay_method"					=> trim($sheet->getCell('AM'.$i)->getValue()),
					"approver_emp_no"				=> trim($sheet->getCell('AN'.$i)->getValue()),
					"approver_name"					=> trim($sheet->getCell('AO'.$i)->getValue()),
					"approver_dept"					=> trim($sheet->getCell('AP'.$i)->getValue()),
				    "last_updated"					=> $updated,
				];
				
				// Month
				$row['month'] = ($this->get_month_from_week($row['week']))['month_number'];
				
				// Dates
				$row['invoice_date'] = $this->convert_date($row['invoice_date']);
				$row['due_date'] = $this->convert_date($row['due_date']);
				$row['gl_date'] = $this->convert_date($row['gl_date']);
				$row['creation_date'] = $this->convert_date($row['creation_date']);
				$row['invoice_received_date'] = $this->convert_date($row['invoice_received_date']);

				$row['key_ap'] = $row['division_name'] . "_" . $row['account_name'] . "_" . $row['invoice_num'] . "_" . $row['currency'];
				
				if (!in_array($row['key_ap'], $key_list)) {
					
					$batch_data[]=$row;
					if(count($batch_data)>=$batch_size){
						$this->gen_m->insert_m("ap_report", $batch_data);
						$inserted_rows += count($batch_data);
						//echo '<pre>'; print_r($batch_data);
						$batch_data = [];
						unset($batch_data);
					}
					
				} else {
					// update rows or avoid rows
					if (isset($data_ap_list[$row['key_ap']])){
						$key_excel = $row['week'].'_'.$row['company'].'_'.$row['department_name'].'_'.$row['invoice_num'].'_'.$row['invoice_date'].'_'.$row['invoice_amount'].'_'.$row['amount_remaining_fun'].'_'.$row['voucher_number'].'_'.$row['gl_date'].'_'.$row['creation_date'].'_'.$row['biz_reg_no'].'_'.$row['item_amount'].'_'.$row['tax_amount'];
						
						$key_db = $data_ap_list[$row['key_ap']]->week.'_'.$data_ap_list[$row['key_ap']]->company.'_'.$data_ap_list[$row['key_ap']]->department_name.'_'.$data_ap_list[$row['key_ap']]->invoice_num.'_'.$data_ap_list[$row['key_ap']]->invoice_date.'_'.$data_ap_list[$row['key_ap']]->invoice_amount.'_'.$data_ap_list[$row['key_ap']]->amount_remaining_fun.'_'.$data_ap_list[$row['key_ap']]->voucher_number.'_'.$data_ap_list[$row['key_ap']]->gl_date.'_'.$data_ap_list[$row['key_ap']]->creation_date.'_'.$data_ap_list[$row['key_ap']]->biz_reg_no.'_'.$data_ap_list[$row['key_ap']]->item_amount.'_'.$data_ap_list[$row['key_ap']]->tax_amount;
						
						if ($key_excel === $key_db) { // If equal rows, avoid 
							continue;
						} else {
							$batch_exist_data[] = $row; // rows to update
						}
					}		
				}
			}

			if (!empty($batch_data)) {
				$this->gen_m->insert_m("ap_report", $batch_data);
				$inserted_rows += count($batch_data);
				$batch_data = [];
				unset($batch_data);
			}
			
			if ($batch_exist_data) {
				$this->gen_m->update_multi('ap_report', $batch_exist_data, 'key_ap');
				$updated_rows = count($batch_exist_data);
				$batch_exist_data = [];
			}
			
			$msg = $inserted_rows . " rows inserted.<br>";
			$msg .= $updated_rows . " rows updated.<br>";
			$msg .= "Total time: " . number_Format(microtime(true) - $start_time, 2) . " secs.";
			//$this->db->trans_complete();
			return $msg;			
		}else return "";
	}
	
	public function upload(){
		$type = "error"; $msg = ""; $original_file_name = null;
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ap_report.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$upload_data = $this->upload->data();
				$original_file_name = $_FILES['attach']['name'];
				$msg = $this->process($original_file_name);
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
