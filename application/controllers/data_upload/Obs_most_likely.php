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
		$month = $this->input->get("m");
		if (!$month){
			$last = $this->gen_m->filter("obs_most_likely", false, ["subsidiary !=" => null], null, null, [["year", "desc"], ["month", "desc"]], 1, 0)[0];
			$month = $last->year."-".$last->month;
		}
		
		$subs = ["LGEPR", "Branch"];
		$coms = ["HS", "MS", "ES"];
		$divs = [
			"HS" => ["REF", "Cooking", "Dishwasher", "W/M"],
			"MS" => ["LTV", "Audio", "MNT", "DS", "MTN Signage", "Commercial TV", "PC"],
			"ES" => ["RAC", "SAC", "Chiller"],
			"MC" => ["MC"],
		];
		
		$rows = [];
		foreach($subs as $sub){
			//subsidiary ml timeline set
			$rows[$sub] = [
				"desc" => $sub,
				"bp" => 0,
				"target" => 0,
				"monthly_report" => 0,
				"ml" => 0,
				"ml_actual" => 0,
			];
			
			//company ml timeline set
			foreach($coms as $com){
				$rows[$sub."_".$com] = [
					"desc" => $sub."_".$com,
					"bp" => 0,
					"target" => 0,
					"monthly_report" => 0,
					"ml" => 0,
					"ml_actual" => 0,
				];
				
				//division ml timeline set
				$com_divs = $divs[$com];
				foreach($com_divs as $div){
					$rows[$sub."_".$com."_".$div] = [
						"desc" => $sub."_".$com."_".$div,
						"bp" => 0,
						"target" => 0,
						"monthly_report" => 0,
						"ml" => 0,
						"ml_actual" => 0,
					];
				}
			}
		}
		
		//ml values
		$filter = explode("-", $month);
		$mls = $this->gen_m->filter("obs_most_likely", false, ["year" => $filter[0], "month" => $filter[1]]);
		foreach($mls as $item){
			$k = $item->subsidiary;
			$rows[$k]["bp"] += $item->bp;
			$rows[$k]["target"] += $item->target;
			$rows[$k]["monthly_report"] += $item->monthly_report;
			$rows[$k]["ml"] += $item->ml;
			$rows[$k]["ml_actual"] += $item->ml_actual;
			
			$k = $item->subsidiary."_".$item->company;
			$rows[$k]["bp"] += $item->bp;
			$rows[$k]["target"] += $item->target;
			$rows[$k]["monthly_report"] += $item->monthly_report;
			$rows[$k]["ml"] += $item->ml;
			$rows[$k]["ml_actual"] += $item->ml_actual;
			
			$k = $item->subsidiary."_".$item->company."_".$item->division;
			$rows[$k]["bp"] += $item->bp;
			$rows[$k]["target"] += $item->target;
			$rows[$k]["monthly_report"] += $item->monthly_report;
			$rows[$k]["ml"] += $item->ml;
			$rows[$k]["ml_actual"] += $item->ml_actual;
		}
		
		//remove if division doesn't has ml
		//foreach($rows as $key => $item) if (!$item["ml"]) unset($rows[$key]);
		
		$data = [
			"month"		=> $month,
			"rows"		=> $rows,
			"main" 		=> "data_upload/obs_most_likely/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function update_division(){
		$mapping = [
			"REF" 	=> ["company" => "HS", "division" => "REF"],
			"COOK" 	=> ["company" => "HS", "division" => "Cooking"],
			"CDT" 	=> ["company" => "HS", "division" => "Dishwasher"],
			"W/M" 	=> ["company" => "HS", "division" => "W/M"],
			
			"TV" 	=> ["company" => "MS", "division" => "LTV"],
			"AV" 	=> ["company" => "MS", "division" => "Audio"],
			"MNT" 	=> ["company" => "MS", "division" => "MNT"],
			"DS" 	=> ["company" => "MS", "division" => "DS"],
			"SGN" 	=> ["company" => "MS", "division" => "MTN Signage"],
			"CTV" 	=> ["company" => "MS", "division" => "Commercial TV"],
			"PC" 	=> ["company" => "MS", "division" => "PC"],
			
			"RAC" 	=> ["company" => "ES", "division" => "RAC"],
			"SAC" 	=> ["company" => "ES", "division" => "SAC"],
			"A/C" 	=> ["company" => "ES", "division" => "Chiller"],
			
			"MC" 	=> ["company" => "MC", "division" => "MC"],
		];
		
		$this->gen_m->delete("obs_most_likely", ["division" => null]);
		
		$data = $this->gen_m->only("obs_most_likely", "division");
		foreach($data as $item) $this->gen_m->update("obs_most_likely", ["division" => $item->division], $mapping[$item->division]);
	}
	
	public function process($filename = "obs_ml.xls"){
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
			$this->gen_m->delete("obs_most_likely", ["year" => trim($sheet->getCell("B2")->getValue()), "month" => trim($sheet->getCell("C2")->getValue())]);
			
			$coms = ["HS", "MS", "ES"];
			$divs = ["REF", "Cooking", "Dishwasher", "W/M", "LTV", "Audio", "MNT", "DS", "MTN Signage", "Commercial TV", "PC", "RAC", "SAC", "Chiller", "MC"];
			
			$sheet_names = ["PR", "PY"];
			foreach($sheet_names as $sheet_name){
				$sheet = $spreadsheet->getSheetByName($sheet_name);
				
				switch($sheet_name){
					case "PY": $sub = "Branch"; break;
					default: $sub = "LGEPR";
				}
				
				$rows = [];
				$max_row = $sheet->getHighestRow();
				for($i = 2; $i < $max_row; $i++){
					$div = trim($sheet->getCell("A".$i)->getValue());
					
					//asign to company in case of company ML
					if (in_array($div, $coms)) $com = $div;
					
					if (in_array($div, $divs)){
						$row = [ 
							"subsidiary"		=> $sub,
							"company" 			=> $com,
							"division" 			=> $div, 
							"year" 				=> trim($sheet->getCell("B".$i)->getValue()), 
							"month" 			=> trim($sheet->getCell("C".$i)->getValue()), 
							"bp" 				=> trim($sheet->getCell("F".$i)->getValue()), 
							"target" 			=> trim($sheet->getCell("G".$i)->getValue()), 
							"monthly_report"	=> trim($sheet->getCell("I".$i)->getValue()), 
							"ml" 				=> trim($sheet->getCell("J".$i)->getValue()), 
							"ml_actual" 		=> trim($sheet->getCell("K".$i)->getValue()), 
						];
						
						$rows[] = $row;
						//print_r($row); echo "<br/>";
					}
				}
				
				$this->gen_m->insert_m("obs_most_likely", $rows);
			}
		}
		
		echo "ML has been inserted to DB.";
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
		
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
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
