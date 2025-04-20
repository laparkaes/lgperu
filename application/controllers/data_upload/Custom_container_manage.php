<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Custom_container_manage extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$w = ["returned" => null];
		$w = null;
		$o = [["eta", "asc"], ["sa_no", "asc"], ["sa_line_no", "asc"], ["container", "asc"]];
		$containers = $this->gen_m->filter("custom_container", false, $w, null, null, $o);
		
		$data = [
			"containers"	=> $containers,
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
				'file_name'		=> 'custom_dq_sa_report.xlsx',
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
		$spreadsheet = IOFactory::load("./upload/custom_dq_sa_report.xlsx");
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
				$row = [
					"sa_no" 		=> trim($sheet->getCell('D'.$i)->getCalculatedValue()),
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
					
					$container = $this->gen_m->filter("custom_container", false, ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
					if ($container){
						//$row["eta"] = null;
						$row["ata"] = null;
						$row["picked_up"] = null;
						$row["wh_arrival"] = null;
						
						$this->gen_m->update("custom_container", ["container_id" => $container[0]->container_id], $row);//update
					}else $this->gen_m->insert("custom_container", $row); //insert	
				}else $this->gen_m->delete("custom_container", ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
			}
			
			$msg = "Shipment advise has been updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
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
					"sa_line_no" 	=> trim($sheet->getCell('E'.$i)->getValue()),
					//"house_bl"		=> trim($sheet->getCell('R'.$i)->getValue()),
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
					
					//set status as received
					$row["is_received"] = true;
					
					$container = $this->gen_m->filter("custom_container", false, ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
					if ($container){//update
						//if (!$container[0]->eta) $row["eta"] = $received[0];
						//if (!$container[0]->ata) $row["ata"] = $received[0];
						if (!$container[0]->picked_up) $row["picked_up"] = $received[0];
						
						$row["wh_arrival"] = $received[0];//wh_arrival always is received date
						
						$this->gen_m->update("custom_container", ["container_id" => $container[0]->container_id], $row);
					}else{//insert
						//$row["eta"] = $received[0];
						//$row["ata"] = $received[0];
						$row["picked_up"] = $received[0];
						$row["wh_arrival"] = $received[0];
						
						$this->gen_m->insert("custom_container", $row); 
					}
				}else $this->gen_m->delete("custom_container", ["sa_no" => $row["sa_no"], "sa_line_no" => $row["sa_line_no"]]);
			}
			
			$msg = "Shipment advise has been updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
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
				
				$eta_new = trim($sheet->getCell('W'.$i)->getValue());
				if ($eta_new) $row["eta"] = $eta_new;
				
				//date format dd/mm/yyyy > yyyy-mm-dd
				$row["eta"] = $this->my_func->date_convert($row["eta"]);
				
				$this->gen_m->update("custom_container", ["sa_no" => $row["sa_no"]], $row);
			}
			
			$msg = "Shipment advise has been updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
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
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
		];

		//magento report header
		$h_validation = ["HBL No", "CNTR No", "Carrier Grp", "ETA", "ATA"];

		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_validation[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"house_bl" 		=> trim($sheet->getCell('A'.$i)->getValue()),
					"container" 	=> trim($sheet->getCell('B'.$i)->getValue()),
					"carrier_line" 	=> trim($sheet->getCell('C'.$i)->getValue()),
					//"eta" 			=> $sheet->getCell('D'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('D'.$i)->getFormattedValue()))) : null,
					"ata"		 	=> $sheet->getCell('E'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('E'.$i)->getFormattedValue()))) : null,
					"picked_up" 	=> $sheet->getCell('F'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('F'.$i)->getFormattedValue()))) : null,
					"wh_arrival" 	=> $sheet->getCell('G'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('G'.$i)->getFormattedValue()))) : null,
					"returned" 		=> $sheet->getCell('H'.$i)->getValue() ? date("Y-m-d", strtotime(trim($sheet->getCell('H'.$i)->getFormattedValue()))) : null,
					"updated_at" 	=> $now,
				];
				
				//if (!$row["eta"]) unset($row["eta"]);
				if (!$row["ata"]) unset($row["ata"]);
				if (!$row["picked_up"]) unset($row["picked_up"]);
				if (!$row["wh_arrival"]) unset($row["wh_arrival"]);
				if (!$row["returned"]) unset($row["returned"]);
				
				$this->gen_m->update("custom_container", ["house_bl" => $row["house_bl"], "container" => $row["container"]], $row);
			}
			
			$msg = "Container dates are updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
}
