<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_container_location extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function set_containers($containers){
		$today = date("Y-m-d");
		$summary = [];
		foreach($containers as $item){
			$is_no_data = false;
			$item->dem_reminds = $item->det_reminds = $item->dem_days = $item->det_days = $item->no_data = 0;
			
			if ($item->ata and $item->picked_up){
				$days = $this->my_func->day_counter($item->ata, $item->picked_up) - 1;
				if ($days > 2){
					$item->dem_days = $days - 2;
				}
			}else{
				$ata = $item->ata ? $item->ata : $item->eta;
				$picked_up = $item->picked_up ? $item->picked_up : $today;

				if (strtotime($ata) <= strtotime($picked_up)){
					$days = $this->my_func->day_counter($ata, $picked_up) - 1;
					if ($days > 2){
						$item->dem_days = $days - 2;
					}else $item->dem_reminds = 2 - $this->my_func->day_counter($ata, $today) + 1;
				}
				
				$is_no_data = true;
			}
			
			if ($item->returned and $item->return_due){
				if (strtotime($item->return_due) < strtotime($item->returned)){
					$item->det_days = $this->my_func->day_counter($item->returned, $item->return_due) - 1;
				}
			}else{
				$returned = $item->returned ? $item->returned : $today;
				$return_due = $item->return_due ? $item->return_due : date('Y-m-d', strtotime('+25 days', strtotime($item->eta)));
				
				if (strtotime($return_due) < strtotime($returned)){
					$item->det_days = $this->my_func->day_counter($returned, $return_due) - 1;
				}else $item->det_reminds = $this->my_func->day_counter($returned, $return_due) - 1;
				
				$is_no_data = true;
			}
			
			if ($is_no_data) $item->no_data = true;
			
			$summary[] = clone $item;
		}
		
		return $summary;
	}
	
	public function index(){
		$eta_from = $this->input->get("eta_from"); if (!$eta_from) $eta_from = date('Y-m-01', strtotime('-2 months'));
		$eta_to = $this->input->get("eta_to"); if (!$eta_to) $eta_to = date("Y-m-t");
		
		$w = ["eta >=" => $eta_from, "eta <=" => $eta_to,];
		$o = [["eta", "desc"], ["sa_no", "asc"], ["sa_line_no", "asc"], ["container", "asc"]];
		$containers = $this->gen_m->filter("custom_container", false, $w, null, null, $o);
		
		$data = [
			"eta_from"		=> $eta_from,
			"eta_to"		=> $eta_to,
			"containers"	=> $this->set_containers($containers),
			"main" 			=> "data_upload/scm_container_location/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function container_location_upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'scm_container_location.xlsx',
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
	
	public function container_location_process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/scm_container_location.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
		];

		//magento report header
		$h_validation = ["LINE", "VESSEL", "File", "MODEL", "PRODUCT"];

		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validation[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			$rows = [];
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"container"			=> trim($sheet->getCell('G'.$i)->getValue()),
					"ctn_type"			=> trim($sheet->getCell('M'.$i)->getValue()),
					"wh_temp"			=> trim($sheet->getCell('R'.$i)->getValue()),
					"destination"		=> trim($sheet->getCell('T'.$i)->getValue()),
					"picked_up"			=> $sheet->getCell('Q'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('Q'.$i)->getFormattedValue()))) : null,
					"wh_arrival_plan"	=> $sheet->getCell('S'.$i)->getValue() !== "DD" ? date("Y-m-d", strtotime(trim($sheet->getCell('S'.$i)->getFormattedValue()))) : null,
					"updated_at"			=> $now,
				];
				
				if (strlen($row["container"]) > 8){
					if ($row["ctn_type"] !== "DD") $row["ctn_type"] = "3PL";
					if (!$row["wh_arrival_plan"]) $row["wh_arrival_plan"] = date("Y-m-d", strtotime(trim($sheet->getCell('U'.$i)->getFormattedValue())));
					
					$rows[] = $row;
				}
				
			}
			
			$today_time = time();
			$w = ["eta >" => date('Y-m-d', strtotime('-2 months', strtotime($now)))];
			
			$rows = array_map("unserialize", array_unique(array_map("serialize", $rows)));
			foreach($rows as $item){
				if (strtotime($item["picked_up"]) <= $today_time){
					$w["container"] = $item["container"];
					$this->gen_m->update("custom_container", $w, $item);	
				}
				
				
				print_r($item); echo "<br/>";
			}
			
			

			
			/*
			
			
			for($i = 3; $i <= $max_row; $i++){
				$row = [
					"master_bl"				=> trim($sheet->getCell('D'.$i)->getValue()),
					"house_bl"				=> trim($sheet->getCell('A'.$i)->getValue()),
					"invoice"				=> trim($sheet->getCell('B'.$i)->getValue()),
					"carrier_line"			=> trim($sheet->getCell('K'.$i)->getValue()),
					"carrier_name"			=> trim($sheet->getCell('L'.$i)->getValue()),
					"current_vessel"		=> trim($sheet->getCell('M'.$i)->getValue()),
					"shipper"				=> trim($sheet->getCell('E'.$i)->getValue()),
					"incoterms"				=> trim($sheet->getCell('J'.$i)->getValue()),
					"ctn_size"				=> trim($sheet->getCell('I'.$i)->getValue()),
					"container"				=> trim($sheet->getCell('C'.$i)->getValue()),
					"product"				=> trim($sheet->getCell('F'.$i)->getValue()),
					"transshipment"			=> trim($sheet->getCell('P'.$i)->getValue()),
					"transshipment_op"		=> trim($sheet->getCell('Q'.$i)->getValue()),
					"transshipment_route"	=> trim($sheet->getCell('R'.$i)->getValue()),
					"transshipment_loc"		=> trim($sheet->getCell('S'.$i)->getValue()),
					"transshipment_vessel"	=> trim($sheet->getCell('T'.$i)->getValue()),
					"port_departure"		=> trim($sheet->getCell('N'.$i)->getValue()),
					"port_terminal"			=> trim($sheet->getCell('U'.$i)->getValue()),
					"atd"					=> $sheet->getCell('O'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('O'.$i)->getFormattedValue()))) : null,
					"eta_initial"			=> $sheet->getCell('V'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('V'.$i)->getFormattedValue()))) : null,
					"eta"					=> $sheet->getCell('W'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('W'.$i)->getFormattedValue()))) : null,
					"ata"					=> $sheet->getCell('X'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('X'.$i)->getFormattedValue()))) : null,
					"picked_up"				=> $sheet->getCell('Y'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('Y'.$i)->getFormattedValue()))) : null,
					"wh_arrival"			=> $sheet->getCell('Z'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('Z'.$i)->getFormattedValue()))) : null,
					"returned"				=> $sheet->getCell('AA'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('AA'.$i)->getFormattedValue()))) : null,
					"return_due"			=> $sheet->getCell('AG'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('AG'.$i)->getFormattedValue()))) : null,
					"updated_at"			=> $now,
				];
				
				if ($row["ata"] or $row["picked_up"] or $row["wh_arrival"] or $row["returned"]) $row["is_received"] = true;
				
				//terminal information reset
				if (stripos($row["port_terminal"], 'APM') !== false) $row["port_terminal"] = "APM";
				elseif (stripos($row["port_terminal"], 'DP') !== false) $row["port_terminal"] = "DPW";
				elseif (stripos($row["port_terminal"], 'DUBAI') !== false) $row["port_terminal"] = "DPW";
				elseif (stripos($row["port_terminal"], 'PECLL') !== false) $row["port_terminal"] = "PECLL";
				else unset($row["port_terminal"]);
				
				//clean null values
				foreach($row as $k => $val) if (!$val) unset($row[$k]);
				
				//return due date is a formula
				if ($row["return_due"] === "1969-12-31") $row["return_due"] = null;
				
				//update container information
				if (array_key_exists('container', $row) and array_key_exists('eta', $row))
					$this->gen_m->update("custom_container", ["eta >=" => date('Y-m-d', strtotime('-40 days', strtotime($row["eta"]))), "container" => $row["container"]], $row);
			}
			
			$msg = "Container dates are updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
			
			*/
		}else $msg = "File template error. Please check upload file.";
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
}
