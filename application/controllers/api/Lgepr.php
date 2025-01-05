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
				["company" => "MS",	"division" => "MNT Signage",	"seq" => "j"],
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
		//use v_lgepr_monthly_closed_order
	}
	
}
