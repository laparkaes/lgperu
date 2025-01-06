<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function get_company(){
		//llamasys/api/lgepr/get_company?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["company" => "HS", "seq" => "a"],
				["company" => "MS", "seq" => "b"],
				["company" => "ES", "seq" => "c"],
			];
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_division(){
		//llamasys/local_api/get_division?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["company" => "HS",	"division" => "REF",			"seq" => "a"],
				["company" => "HS",	"division" => "Cooking",		"seq" => "b"],
				["company" => "HS",	"division" => "Dishwasher",		"seq" => "c"],
				["company" => "HS",	"division" => "W/M",			"seq" => "d"],
				
				["company" => "MS",	"division" => "LTV",			"seq" => "e"],
				["company" => "MS",	"division" => "Audio",			"seq" => "f"],
				["company" => "MS",	"division" => "MNT",			"seq" => "g"],
				["company" => "MS",	"division" => "DS",				"seq" => "h"],
				["company" => "MS",	"division" => "PC",				"seq" => "i"],
				["company" => "MS",	"division" => "MTN Signage",	"seq" => "j"],
				["company" => "MS",	"division" => "Commercial TV",	"seq" => "k"],
				
				["company" => "ES",	"division" => "RAC",		"seq" => "l"],
				["company" => "ES",	"division" => "SAC",		"seq" => "m"],
				["company" => "ES",	"division" => "Chiller",	"seq" => "n"],
			];
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

	public function get_most_likely(){
		//llamasys/api/lgepr/get_most_likely?key=lgepr
		
		$last = $this->gen_m->filter("lgepr_most_likely", false, ["subsidiary !=" => null], null, null, [["year", "desc"], ["month", "desc"]], 1, 0)[0];
		
		if ($this->input->get("key") === "lgepr") $res = $this->gen_m->filter("obs_most_likely", false, ["year" => $last->year, "month" => $last->month]);
		else $res = ["Key error"];
		
		if (!$res) $res = ["No this month ML data in database."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_closed_order(){
		//llamasys/api/lgepr/get_closed_order?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			//$w = ["closed_date >=" => date("2024-12-01")];
			$w = ["closed_date >=" => date("Y-m-01")];
			$o = [["closed_date", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			
			$res = $this->gen_m->filter("lgepr_closed_order", false, $w, null, null, $o);
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_sales_order(){
		//llamasys/api/lgepr/get_closed_order?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$o = [["create_date", "desc"], ["req_arrival_date_to", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			
			$res = $this->gen_m->filter("lgepr_sales_order", false, null, null, null, $o);
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_monthly_closed_order(){
		if ($this->input->get("key") === "lgepr"){
			$res = [
				"LGEPR_HS_REF" 				=> ["seq" => "1a", "department" => "LGEPR", "company" => "HS", "division" => "REF",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_HS_Cooking" 			=> ["seq" => "1b", "department" => "LGEPR", "company" => "HS", "division" => "Cooking",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_HS_Dishwasher" 		=> ["seq" => "1c", "department" => "LGEPR", "company" => "HS", "division" => "Dishwasher",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_HS_W/M" 				=> ["seq" => "1d", "department" => "LGEPR", "company" => "HS", "division" => "W/M",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"LGEPR_MS_LTV" 				=> ["seq" => "1e", "department" => "LGEPR", "company" => "MS", "division" => "LTV",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_Audio" 			=> ["seq" => "1f", "department" => "LGEPR", "company" => "MS", "division" => "Audio",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_MNT" 				=> ["seq" => "1g", "department" => "LGEPR", "company" => "MS", "division" => "MNT",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_DS" 				=> ["seq" => "1h", "department" => "LGEPR", "company" => "MS", "division" => "DS",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_PC" 				=> ["seq" => "1i", "department" => "LGEPR", "company" => "MS", "division" => "PC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_MTN Signage" 		=> ["seq" => "1j", "department" => "LGEPR", "company" => "MS", "division" => "MTN Signage",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_Commercial TV" 	=> ["seq" => "1k", "department" => "LGEPR", "company" => "MS", "division" => "Commercial TV",	"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"LGEPR_ES_RAC" 				=> ["seq" => "1l", "department" => "LGEPR", "company" => "ES", "division" => "RAC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_ES_SAC" 				=> ["seq" => "1m", "department" => "LGEPR", "company" => "ES", "division" => "SAC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_ES_Chiller" 			=> ["seq" => "1n", "department" => "LGEPR", "company" => "ES", "division" => "Chiller",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"Branch_HS_REF" 			=> ["seq" => "2a", "department" => "Branch", "company" => "HS", "division" => "REF",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_HS_Cooking" 		=> ["seq" => "2b", "department" => "Branch", "company" => "HS", "division" => "Cooking",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_HS_Dishwasher" 		=> ["seq" => "2c", "department" => "Branch", "company" => "HS", "division" => "Dishwasher",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_HS_W/M" 			=> ["seq" => "2d", "department" => "Branch", "company" => "HS", "division" => "W/M",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"Branch_MS_LTV" 			=> ["seq" => "2e", "department" => "Branch", "company" => "MS", "division" => "LTV",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_Audio" 			=> ["seq" => "2f", "department" => "Branch", "company" => "MS", "division" => "Audio",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_MNT" 			=> ["seq" => "2g", "department" => "Branch", "company" => "MS", "division" => "MNT",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_DS" 				=> ["seq" => "2h", "department" => "Branch", "company" => "MS", "division" => "DS",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_PC" 				=> ["seq" => "2i", "department" => "Branch", "company" => "MS", "division" => "PC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_MTN Signage" 	=> ["seq" => "2j", "department" => "Branch", "company" => "MS", "division" => "MTN Signage",	"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_Commercial TV" 	=> ["seq" => "2k", "department" => "Branch", "company" => "MS", "division" => "Commercial TV",	"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"Branch_ES_RAC" 			=> ["seq" => "2l", "department" => "Branch", "company" => "ES", "division" => "RAC",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_ES_SAC" 			=> ["seq" => "2m", "department" => "Branch", "company" => "ES", "division" => "SAC",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_ES_Chiller" 		=> ["seq" => "2n", "department" => "Branch", "company" => "ES", "division" => "Chiller",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
			];
			
			//$w = ["month" => date("2024-11")];
			$w = ["month" => date("Y-m")];
			
			$monthly = $this->gen_m->filter("v_lgepr_monthly_closed_order", false, $w);
			foreach($monthly as $item){
				$res[$item->customer_department."_".$item->dash_company."_".$item->dash_division][$item->category] += round($item->total_order_amount_usd, 2);
			}
		}else $res = ["Key error"];
		
		foreach($res as $key => $item){
			$res[$key]["Total"] = $res[$key]["Sales"] + $res[$key]["Return"];
			//print_r($item); echo "<br/>";
		}
		
		//foreach($res as $key => $item){print_r($item); echo "<br/>";}
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
}
