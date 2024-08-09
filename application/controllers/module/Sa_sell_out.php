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
		$data = [
			//"purchase_order_temps" => $this->gen_m->all("product", [["category_id", "asc"], ["model", "asc"]]),
			"main" => "module/sa_sell_out/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function process($filename = "sa_sell_out.xlsx"){
		ini_set("memory_limit","1024M");
		set_time_limit(0);
		$start_time = microtime(true);
		
		$spreadsheet = IOFactory::load("./upload/".$filename);
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
			
			$cust_last = $cust_now = "";
			$rows = $dates = [];
			for($i = 2; $i <= $max_row; $i++){
				$row = [];
				foreach($vars as $var_i => $var){
					$row[$var] = trim($sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
					if (!$row[$var]) $row[$var] = null;
				}
				
				$row["txn_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('E'.$i)->getFormattedValue())));
				
				$cust_now = $row["customer"];
				if ($cust_last === $cust_now){
					$rows[] = $row;
					$dates[] = $row["txn_date"];
				}elseif ($rows){
					print_r($rows); echo "<br/><br/><br/>Cut here................................<br/><br/><br/>";
					
					$rows = [];
					$dates = [];
				}
				
				$cust_last = $cust_now;
				
				if ($i > 10000) break;
			}
		}else echo "wrong file.<br/>";
		
		echo number_Format(microtime(true) - $start_time, 2)." secs";
	}
	
	public function test(){
		$this->process();
	}
	
	public function upload(){
		
			
		$type = "error"; $url = ""; $msg = ""; $data = [];
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'sa_sell_out',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$filename = $this->upload->data('file_name');
			
			
			
			if ($is_ok){
				//$data = $this->process($sheet);
				if ($data["url"]){
					$type = "success";
					$msg = "All data has been processed. (".$data["runtime"]. "sec)";	
				}else $msg = "No data to process.";
			}else $msg = "Wrong data file.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "data" => $data]);
	}
}
