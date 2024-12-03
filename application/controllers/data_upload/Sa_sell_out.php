<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Sa_sell_out extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$bill_to = $this->input->get("cust");
		$store = $this->input->get("store");
		
		$w = $bill_to ? ["customer" => $bill_to] : null;
		$l = $store ? [["field" => "cust_store_name", "values" => [$store]]] : null;
		
		$data = [
			"customers"	=> $this->gen_m->only_multi("sa_sell_out_", ["acct_gtm", "customer"]),
			"records" 	=> $this->gen_m->filter("sa_sell_out_", false, $w, $l, null, [["txn_date", "desc"], ["customer", "asc"]], $bill_to ? 10000 : 1000),
			"main" 		=> "data_upload/sa_sell_out/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function process($msg_print = false){
		ini_set("memory_limit","1024M");
		set_time_limit(0);
		$start_time = microtime(true);
		
		$spreadsheet = IOFactory::load("./upload/sa_sell_out.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		$headers = [
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
		
		$h_val = [
			"CUSTOMER", 
			"ACCT_GTM", 
			"CUSTOMER_MODEL", 
			"MODEL_SUFFIX_CODE", 
			"TXN_DATE", 
			"CUST_STORE_CODE", 
			"CUST_STORE_NAME", 
			"SELLOUT_UNIT", 
			"SELLOUT_AMT", 
			"STOCK", 
			"TICKET",
		];
		
		$is_ok = true;
		foreach($headers as $i => $h) if ($h !== $h_val[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//db fields
			$vars = [
				"customer", 
				"acct_gtm", 
				"customer_model", 
				"model_suffix_code", 
				"txn_date", 
				"cust_store_code", 
				"cust_store_name", 
				"sellout_unit", 
				"sellout_amt", 
				"stock", 
				"ticket",
			];
			
			
			$max_row += 1;//last customer need to be inserted
			$cust_last = $cust_now = "";
			$row_counter = 0;
			$rows = $dates = [];
			for($i = 2; $i <= $max_row; $i++){
				$row = [];
				foreach($vars as $var_i => $var){
					$row[$var] = trim($sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
					//if (!$row[$var]) $row[$var] = null;
				}
				
				$row["txn_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('E'.$i)->getFormattedValue())));
				
				$cust_now = $row["customer"];
				
				if (($cust_last !== $cust_now) and $rows){
					$dates = array_unique($dates);
					sort($dates);
					
					$from = min($dates);
					$to = max($dates);
					$rows_qty = count($rows);
					
					if ($msg_print){
						echo "========================================================================================================<br/>";
						echo $cust_last."<br/>";
						echo $from." ~ ".$to."<br/>";
						echo number_format($rows_qty); echo " sell out(s)<br/><br/>";	
					}
					
					//remove datas and insert new sell out records
					$w = ["customer" => $cust_last, "txn_date >=" => $from, "txn_date <=" => $to];
					$this->gen_m->delete("sa_sell_out_", $w);
					$this->gen_m->insert_m("sa_sell_out_", $rows);
					
					$row_counter += $rows_qty;
					
					$rows = [];
					$dates = [];
				}
				
				$rows[] = $row;
				$dates[] = $row["txn_date"];
					
				$cust_last = $cust_now;
			}
		}else $msg = "Wrong file.";
		
		$msg = "Finished.<br/><br/>Records: ".number_format($row_counter)."<br/>Time: ".number_Format(microtime(true) - $start_time, 2)." secs";
		
		if ($msg_print){
			echo "<br/>";
			echo "========================================================================================================<br/>";
			echo "========================================================================================================<br/>";
			echo "========================================================================================================<br/><br/>";
			echo $msg;
		}
		
		return $msg;
	}
	
	public function debug(){
		$this->process(true);
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'sa_sell_out',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$type = "success";
			$msg = $this->process();
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
