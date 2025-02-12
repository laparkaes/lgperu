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
	
	public function single_update_data(){
		$this->update_model_category();
		$this->update_dash_div_cat();
	}
	
	private function update_dash_div_cat(){
		$dash_mapping = [
			"REF" 	=> ["dash_company" => "HS"	, "dash_division" => "REF"],
			"CVT" 	=> ["dash_company" => "HS"	, "dash_division" => "Cooking"],
			"CDT" 	=> ["dash_company" => "HS"	, "dash_division" => "Dishwasher"],
			"W/M" 	=> ["dash_company" => "HS"	, "dash_division" => "W/M"],
			
			"LTV" 	=> ["dash_company" => "MS"	, "dash_division" => "LTV"],
			"CAV" 	=> ["dash_company" => "MS"	, "dash_division" => "Audio"],
			"MNT" 	=> ["dash_company" => "MS"	, "dash_division" => "MNT"],
			"DS" 	=> ["dash_company" => "MS"	, "dash_division" => "DS"],
			"SGN" 	=> ["dash_company" => "MS"	, "dash_division" => "MNT Signage"],
			"CTV" 	=> ["dash_company" => "MS"	, "dash_division" => "Commercial TV"],
			"PC" 	=> ["dash_company" => "MS"	, "dash_division" => "PC"],
			
			"RAC" 	=> ["dash_company" => "ES"	, "dash_division" => "RAC"],
			"SAC" 	=> ["dash_company" => "ES"	, "dash_division" => "SAC"],
			"A/C" 	=> ["dash_company" => "ES"	, "dash_division" => "Chiller"],
			
			"MC" 	=> ["dash_company" => "MC"	, "dash_division" => "MC"],
		];
		
		foreach($dash_mapping as $key => $item) $this->gen_m->update("lgepr_stock", ["model_category_code" => $key], $item);
	}
	
	private function update_model_category(){
		//set mapping array
		$w = ["model_category_code !=" => ""];
		$s = ["model_category_code", "product_level4"];
		$closed_orders = $this->gen_m->filter_select("lgepr_stock", false, $s, $w, null, null, [["product_level4", "desc"]], null, null, "product_level4");
		
		$mapping = ["MC" => "MC"];
		foreach($closed_orders as $item){
			if ($item->model_category_code){
				$index_6 = substr($item->product_level4, 0, 6);
				$index_4 = substr($item->product_level4, 0, 4);
				$index_2 = substr($item->product_level4, 0, 2);
				
				if (array_key_exists($index_6, $mapping)){
					if (!$mapping[$index_6]) $mapping[$index_6] = $item->model_category_code;
				}else $mapping[$index_6] = $item->model_category_code;	
				
				if (array_key_exists($index_4, $mapping)){
					if (!$mapping[$index_4]) $mapping[$index_4] = $item->model_category_code;
				}else $mapping[$index_4] = $item->model_category_code;
				
				if (array_key_exists($index_2, $mapping)){
					if (!$mapping[$index_2]) $mapping[$index_2] = $item->model_category_code;
				}else $mapping[$index_2] = $item->model_category_code;
			}
		}
		
		//update blank model categories
		$w = ["model_category_code" => ""];
		$s = ["model_category_code", "product_level4"];
		$closed_orders = $this->gen_m->filter_select("lgepr_stock", false, $s, $w, null, null, [["product_level4", "desc"]], null, null, "product_level4");
		
		foreach($closed_orders as $item){
			$mc = "";
			
			$sub6 = substr($item->product_level4, 0, 6);
			$sub4 = substr($item->product_level4, 0, 4);
			$sub2 = substr($item->product_level4, 0, 2);
			
			if (array_key_exists($sub6, $mapping)) $mc = $mapping[$sub6];
			elseif (array_key_exists($sub4, $mapping)) $mc = $mapping[$sub4]; 
			elseif (array_key_exists($sub2, $mapping)) $mc = $mapping[$sub2]; 
			
			//echo $sub6." ".$sub4." >>> ".$mc."<br/>"; print_r($item); echo "<br/><br/>";
			
			if ($mc) $this->gen_m->update("lgepr_stock", ["product_level4" => $item->product_level4], ["model_category_code" => $mc]);
		}
		
		//update division & category for dashboard
		$this->update_dash_div_cat();
	}
	
	public function process($filename = "lgepr_stock_report.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);

		//delete all rows lgepr_stock 
		$this->gen_m->truncate("lgepr_stock");
		
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
					"seaStockTotal"			=> trim($sheet->getCell('BN'.$i)->getValue()),
					"seaStockW1"			=> trim($sheet->getCell('BO'.$i)->getValue()),
					"seaStockW2"			=> trim($sheet->getCell('BP'.$i)->getValue()),
					"seaStockW3"			=> trim($sheet->getCell('BQ'.$i)->getValue()),
					"seaStockW4"			=> trim($sheet->getCell('BR'.$i)->getValue()),
					"seaStockW5"			=> trim($sheet->getCell('BS'.$i)->getValue()),
					"product_level4"		=> trim($sheet->getCell('BY'.$i)->getValue()),					
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
				//$this->update_model_category();
			}
			$this->update_model_category();
			return "Stock update has been finished. (".$updated.")";
			
		}else return "";
		//$this->update_model_category();
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
				'file_name'		=> 'lgepr_stock_report.xls',
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
