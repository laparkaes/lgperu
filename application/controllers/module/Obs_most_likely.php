<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Obs_most_likely extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"ml_first"	=> $this->gen_m->filter("obs_most_likely", false, ["subsidiary !=" => null], null, null, [["year", "asc"], ["month", "asc"]], 1, 0)[0],
			"ml_last" 	=> $this->gen_m->filter("obs_most_likely", false, ["subsidiary !=" => null], null, null, [["year", "desc"], ["month", "desc"]], 1, 0)[0],
			"main" 			=> "module/obs_most_likely/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function process($filename = "obs_ml.xls"){
		set_time_limit(0);
		
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
			trim($sheet->getCell('L1')->getValue()),
			trim($sheet->getCell('M1')->getValue()),
		];
		
		//magento report header
		$h_validate = ["DIVISION", "YYYY", "MM", "Y-2", "Y-1", "BP", "Target", "MP", "Monthly Report", "ML", "ML Actual", "M-1", "M-2",];
		
		
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validate[$i]) $is_ok = false;
		
		$result = [];
		
		if ($is_ok){
			
			$max_row = $sheet->getHighestRow();
			
			//result types
			$qty_insert = $qty_update = $qty_fail = 0;
			
			//mapping arrays
			$field_map = [
				"Total" => "subsidiary",
				"H&A" => "division",
				"HE" => "division",
				"BS" => "division",
				"REF" => "category",
				"Cooking" => "category",
				"W/M" => "category",
				"RAC DIVISION" => "category",
				"CAC DIVISION" => "category",
				"Chiller AC" => "category",
				"LTV" => "category",
				"AV" => "category",
				"MNT" => "category",
				"PC" => "category",
				"Data Storage" => "category",
				"Signage" => "category",
				"Commercial TV" => "category",
				"" => "",
			];

			$term_map = [
				"Total" => "LGEPR",//fixed to LGEPR
				"H&A" => "HA",
				"HE" => "HE",
				"BS" => "BS",
				"REF" => "REF",
				"Cooking" => "COOK",
				"W/M" => "W/M",
				"RAC DIVISION" => "RAC",
				"CAC DIVISION" => "SAC",
				"Chiller AC" => "A/C",
				"LTV" => "TV",
				"AV" => "AV",
				"MNT" => "MNT",
				"PC" => "PC",
				"Data Storage" => "DS",
				"Signage" => "SGN",
				"Commercial TV" => "CTV",
				"" => "",
			];
			
			//div mapping
			
			$sub = $div = $cat = null;
			for($i = 2; $i < $max_row; $i++){
				$first = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
				if ($first){
					//$row = ["subsidiary" => null, "division" => null, "category" => null];
					//$row[$field_map[$first]] = $term_map[$first];
					
					switch($field_map[$first]){
						case "subsidiary": $sub = $term_map[$first]; $div = $cat = null; break;
						case "division": $div = $term_map[$first]; $cat = null; break;
						case "category": $cat = $term_map[$first]; break;
					}
				
					$row = [
						"subsidiary" 	=> $sub, 
						"division" 		=> $div, 
						"category" 		=> $cat,
						"year" 			=> trim($sheet->getCellByColumnAndRow(2, $i)->getValue()), 
						"month" 		=> trim($sheet->getCellByColumnAndRow(3, $i)->getValue()), 
						"bp" 			=> trim($sheet->getCellByColumnAndRow(6, $i)->getValue()), 
						"target" 		=> trim($sheet->getCellByColumnAndRow(7, $i)->getValue()), 
						"monthly_report"=> trim($sheet->getCellByColumnAndRow(9, $i)->getValue()), 
						"ml" 			=> trim($sheet->getCellByColumnAndRow(10, $i)->getValue()), 
						"ml_actual" 	=> trim($sheet->getCellByColumnAndRow(11, $i)->getValue()),
					];
					
					//$row = array_merge($row, $row_);
					
					$ml = $this->gen_m->filter("obs_most_likely", false, ["subsidiary" => $row["subsidiary"], "division" => $row["division"], "category" => $row["category"], "year" => $row["year"], "month" => $row["month"],]);
					if ($ml){
						if ($this->gen_m->update("obs_most_likely", ["most_likely_id" => $ml[0]->most_likely_id], $row)) $qty_update++;
						else $qty_fail++;
					}else{
						if ($this->gen_m->insert("obs_most_likely", $row)) $qty_insert++;
						else $qty_fail++;
					}
				}
			}

			if ($qty_insert > 0) $result[] = number_format($qty_insert)." inserted";
			if ($qty_update > 0) $result[] = number_format($qty_update)." updated";
			if ($qty_fail > 0) $result[] = number_format($qty_fail)." failed";
		}
		
		return $result ? "OBS Most Likely process result:<br/><br/>".implode(",", $result) : null;
		//echo $result ? "OBS GERP Sales orders process result:<br/><br/>".implode(",", $result) : null;
		
	}
	
	public function test(){
		echo $this->process();
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'obs_ml.xls',
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
