<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_stock extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$w = ["updated >=" => date("Y-m-d", strtotime("-3 months"))];
		$o = [["updated", "desc"], ["model_description", "asc"], ["model", "asc"]];
		
		$data = [
			"stocks"	=> $this->gen_m->filter("lgepr_stock", false, $w, null, null, $o, 5000),
			"main" 		=> "data_upload/lgepr_stock/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function process($filename = "lgepr_stock_report.xlsx", $debug = false){
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
		];

		//magento report header
		$header = ["Org", "Sub inventory", "Grade", "Model Category Code", "Model Category Name", "UIT", "Model", "Model Description"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
		
		if ($is_ok){
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			for($i = 2; $i < $max_row; $i++){
				$row = [
					"org" 					=> trim($sheet->getCell('A'.$i)->getValue()),
					"sub_inventory" 		=> trim($sheet->getCell('B'.$i)->getValue()),
					"grade" 				=> trim($sheet->getCell('C'.$i)->getValue()),
					"model_category_code" 	=> trim($sheet->getCell('D'.$i)->getValue()),
					"model_Category_name" 	=> trim($sheet->getCell('E'.$i)->getValue()),
					"model"					=> trim($sheet->getCell('G'.$i)->getValue()),
					"model_description" 	=> trim($sheet->getCell('H'.$i)->getValue()),
					"model_status" 			=> trim($sheet->getCell('J'.$i)->getValue()),
					"available_qty"			=> trim($sheet->getCell('N'.$i)->getValue()),
					"updated"				=> $updated,
				];
				
				//$this->gen_m->insert("lgepr_stock", $row);//create daily stock record
				
				/* update stock
				*/
				$filter = [
					"org" 					=> $row["org"],
					"sub_inventory" 		=> $row["sub_inventory"],
					"grade" 				=> $row["grade"],
					"model"					=> $row["model"],
					"updated"				=> $row["updated"],
				];
				
				$stock = $this->gen_m->filter("lgepr_stock", false, $filter);
				if ($stock) $this->gen_m->update("lgepr_stock", ["stock_id" => $stock[0]->stock_id], $row);
				else $this->gen_m->insert("lgepr_stock", $row);
			}
			
			return "Stock update has been finished. (".$updated.")";
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
				'file_name'		=> 'lgepr_stock_report.xlsx',
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
