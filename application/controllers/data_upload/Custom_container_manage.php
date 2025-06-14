<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Custom_container_manage extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function container_cleansing(){
		$list = [];//remove records without container number
		$containers = $this->gen_m->filter("lgepr_container", false);
		foreach($containers as $item){
			//print_r($item);
			if (strlen($item->container) < 5){
				$list[] = $item->container;
				//echo $item->container."<br/><br/>";
			}
		}
		
		$this->gen_m->delete_in("lgepr_container", "container", $list);
	}
	
	private function set_com_div(){
		$containers = $this->gen_m->filter("lgepr_container", false, ["company" => null]);
		foreach($containers as $item){
			$rec = $this->gen_m->unique("v_lgepr_model_master_stock", "model", $item->model, false);
			if ($rec) $this->gen_m->update("lgepr_container", ["model" => $item->model], ["company" => $rec->dash_company, "division" => $rec->dash_division]);
		}
		
		$containers = $this->gen_m->filter("lgepr_container", false, ["company" => null]);
		foreach($containers as $item){
			$rec = $this->gen_m->unique("v_lgepr_model_master", "model", $item->model, false);
			if ($rec) $this->gen_m->update("lgepr_container", ["model" => $item->model], ["company" => $rec->dash_company, "division" => $rec->dash_division]);
		}
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
		$containers = $this->gen_m->filter("lgepr_container", false, $w, null, null, $o);
		
		$data = [
			"eta_from"		=> $eta_from,
			"eta_to"		=> $eta_to,
			"containers"	=> $this->set_containers($containers),
			"main" 			=> "data_upload/custom_container_manage/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function dq_shipment_advise_upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'custom_dq_sa_report.xls',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
				/*
				$msg = $this->process();//delete & insert
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
				*/
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function dq_shipment_advise_process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/custom_dq_sa_report.xls");
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
		$h_validation = ["SUPPLY_TYPE_ORIGIN", "ORGANIZATION_CD", "SUBINVENTORY_CODE_ORIGIN", "SA_NO", "SA_LINE_NO"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validation[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			//set model master
			$model_master = [];
			$models = $this->gen_m->all("v_lgepr_model_master", [], "", "", false);
			foreach($models as $item) $model_master[$item->model] = $item;
			
			for($i = 2; $i <= $max_row; $i++){
				
				$sheet->getCell('D'.$i)->setDataType(DataType::TYPE_STRING);
				
				$row = [
					"sa_no" 		=> trim($sheet->getCell('D'.$i)->getValue()),
					"sa_line_no" 	=> trim($sheet->getCell('E'.$i)->getValue()),
					"house_bl"		=> trim($sheet->getCell('R'.$i)->getValue()),
					"container" 	=> trim($sheet->getCell('O'.$i)->getValue()),
					"organization" 	=> trim($sheet->getCell('B'.$i)->getValue()),
					"sub_inventory" => trim($sheet->getCell('C'.$i)->getValue()),
					"model" 		=> trim($sheet->getCell('U'.$i)->getValue()),
					"qty" 			=> trim($sheet->getCell('F'.$i)->getValue()),
					"cbm" 			=> trim($sheet->getCell('W'.$i)->getValue()),
					"weight" 		=> trim($sheet->getCell('V'.$i)->getValue()),
					"eta"			=> trim($sheet->getCell('H'.$i)->getValue()),
					"updated_at" 	=> $now,
				];
				
				//if this SA is not container, remove
				if ($row["container"]){
					if (array_key_exists($row["model"], $model_master)){				
						$row["company"] = $model_master[$row["model"]]->dash_company;
						$row["division"] = $model_master[$row["model"]]->dash_division;	
					}
					
					//date convert: 26-FEB-25 > 2025-02-26
					$row["eta"] = $this->my_func->date_convert_4($row["eta"]);
					
					//set status as pending
					$row["is_received"] = false;
				
					$container = $this->gen_m->filter("lgepr_container", false, ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
					if ($container){
						//if container is in SA list, this container is not received by 3PL
						$row["picked_up"] = null;
						$row["wh_arrival"] = null;
						
						$this->gen_m->update("lgepr_container", ["container_id" => $container[0]->container_id], $row);//update
					}else $this->gen_m->insert("lgepr_container", $row); //insert	
				}else $this->gen_m->delete("lgepr_container", ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
			}
			
			$msg = "Shipment advise has been updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		$this->container_cleansing();
		$this->set_com_div();
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
		
	public function receiving_confirm_upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'custom_receiving_confirm.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
				/*
				$msg = $this->process();//delete & insert
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
				*/
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function receiving_confirm_process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/custom_receiving_confirm.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
		];

		//file header
		$h_validation = ["Transfer Flag", "Transfer Date (Local Time)", "EDI Interface Date", "Source Header No", "Source Type Code"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validation[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			//set model master
			$model_master = [];
			$models = $this->gen_m->all("v_lgepr_model_master", [], "", "", false);
			foreach($models as $item) $model_master[$item->model] = $item;
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"sa_no" 		=> trim($sheet->getCell('D'.$i)->getCalculatedValue()),
					"sa_line_no" 	=> trim($sheet->getCell('F'.$i)->getValue()),
					"container" 	=> trim($sheet->getCell('W'.$i)->getValue()),
					"organization" 	=> trim($sheet->getCell('J'.$i)->getValue()),
					"sub_inventory" => trim($sheet->getCell('Q'.$i)->getValue()),
					"model" 		=> trim($sheet->getCell('G'.$i)->getValue()),
					"qty" 			=> trim($sheet->getCell('I'.$i)->getValue()),
					"updated_at" 	=> $now,
				];
				
				//if this SA is not container, remove
				if ($row["container"]){
					if (array_key_exists($row["model"], $model_master)){				
						$row["company"] = $model_master[$row["model"]]->dash_company;
						$row["division"] = $model_master[$row["model"]]->dash_division;	
					}
					
					//set received datetime
					$received = explode(" ", trim($sheet->getCell('B'.$i)->getValue()));
					$received[0] = $this->my_func->date_convert($received[0]);//dd/mm/yyyy > yyyy-mm-dd
					
					//get transfer flag
					$transfer_flag = trim($sheet->getCell('A'.$i)->getCalculatedValue());
					if ($transfer_flag === "Y"){
						$row["is_received"] = true;
						
						$container = $this->gen_m->filter("lgepr_container", false, ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
						if ($container){//update
							if (!$container[0]->picked_up) $row["picked_up"] = $received[0];
							$row["wh_arrival"] = $received[0];//wh_arrival always is received date
							
							$this->gen_m->update("lgepr_container", ["container_id" => $container[0]->container_id], $row);
						}else{//insert
							$row["picked_up"] = $received[0];
							$row["wh_arrival"] = $received[0];
							
							$this->gen_m->insert("lgepr_container", $row);
						}	
					}
				}else $this->gen_m->delete("lgepr_container", ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
			}
			
			$msg = "Shipment advise has been updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		$this->container_cleansing();
		$this->set_com_div();
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
	public function sa_inquiry_upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'custom_sa_inquiry.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
				/*
				$msg = $this->process();//delete & insert
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
				*/
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function sa_inquiry_process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/custom_sa_inquiry.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
		];

		//file header
		$h_validation = ["SA No", "House Bl No", "Invoice No", "Receiving Close", "Bl Date"];

		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validation[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"sa_no" 		=> trim($sheet->getCell('A'.$i)->getCalculatedValue()),
					"house_bl"		=> trim($sheet->getCell('B'.$i)->getValue()),
					"eta"		 	=> trim($sheet->getCell('V'.$i)->getValue()),
					"updated_at" 	=> $now,
				];
				
				//new eta has a lot of errors
				//$eta_new = trim($sheet->getCell('W'.$i)->getValue());
				//if ($eta_new) $row["eta"] = $eta_new;
				
				//date format dd/mm/yyyy > yyyy-mm-dd
				$row["eta"] = $this->my_func->date_convert($row["eta"]);
				
				$this->gen_m->update("lgepr_container", ["sa_no" => $row["sa_no"]], $row);
			}
			
			$msg = "Shipment advise has been updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		$this->container_cleansing();
		$this->set_com_div();
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
	public function container_dates_upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'custom_container_dates.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
				/*
				$msg = $this->process();//delete & insert
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
				*/
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function container_dates_process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/custom_container_dates.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A2')->getValue()),
			trim($sheet->getCell('B2')->getValue()),
			trim($sheet->getCell('C2')->getValue()),
			trim($sheet->getCell('D2')->getValue()),
			trim($sheet->getCell('E2')->getValue()),
		];

		//magento report header
		$h_validation = ["HBL No.", "Invoice No.", "CNTR No.", "MBL No.", "SHPR Name"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validation[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
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
					$this->gen_m->update("lgepr_container", ["eta >=" => date('Y-m-d', strtotime('-40 days', strtotime($row["eta"]))), "container" => $row["container"]], $row);
			}
			
			$msg = "Container dates are updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		$this->container_cleansing();
		$this->set_com_div();
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
}
