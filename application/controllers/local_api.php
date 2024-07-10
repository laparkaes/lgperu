<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Local_api extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
		$this->divisions = ["HA", "HE", "BS"];
		$this->division_map = [
			"HA" => ["REF", "COOK", "W/M", "RAC", "SAC", "A/C"],
			"HE" => ["TV", "AV"],
			"BS" => ["MNT", "PC", "DS", "SGN", "CTV"],
		];
		$this->division_map_inv = [];
		foreach($this->division_map as $div => $divisions) foreach($divisions as $cat) $this->division_map_inv[$cat] = $div;
		
		$this->categories = ["REF", "COOK", "W/M", "A/C", "RAC", "SAC", "TV", "AV", "MNT", "PC", "DS", "SGN", "CTV"];
		$this->category_map = [
			"REF" => ["REF"],
			"COOK" => ["MWO", "O", "CVT"],
			"W/M" => ["W/M"],
			"A/C" => ["A/C"],
			"RAC" => ["RAC"],
			"SAC" => ["SAC"],
			"TV" => ["LCD", "LTV"],
			"AV" => ["AUD", "CAV"],
			"MNT" => ["MNT"],
			"PC" => ["PC"],
			"DS" => ["DS"],
			"SGN" => ["SGN"],
			"CTV" => ["CTV"],
		];
		$this->category_map_inv = [];
		foreach($this->category_map as $cat => $categories) foreach($categories as $c) $this->category_map_inv[$c] = $cat;
	}
	
	public function test(){
		$order_items = $this->gen_m->all("order_item");
		
		//print_r($order_items);
		header('Content-Type: application/json');
		echo json_encode(["order_items" => $order_items]);
	}
	
	public function get_exchange_rate(){
		//llamasys/local_api/get_exchange_rate?key=lgepr
		
		$key = $this->input->get("key");
		$res = [];
		
		if ($key === "lgepr") $res = ["exchange_rate_ttm" => round($this->my_func->get_exchange_rate_month_ttm(date("Y-m-d")), 2)];
		else $res = ["msg" => "Key error."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_obs_magento_month(){
		//llamasys/local_api/get_obs_magento?key=lgepr&f=2024-01-01&t=2024-12-31
		
		if ($this->input->get("key") === "lgepr"){
			$w_m = ["local_time >=" => date("Y-m-01 00:00:00")." 00:00:00", "local_time <=" => date("Y-m-t 23:59:59")];
			$w_in_m = [
				[
					"field" => "status", 
					"values" => ["complete", "awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"],
				],
			];
			
			$exr_ttm = round($this->my_func->get_exchange_rate_month_ttm(date("Y-m-d")), 2);
			$magentos = $this->gen_m->filter("obs_magento", false, $w_m, null, $w_in_m, [["local_time", "desc"]]);
			foreach($magentos as $m) $m->grand_total_purchased_usd = $m->grand_total_purchased / $exr_ttm;
			
			$res = ["magentos" => $magentos];
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_obs_sales(){
		//llamasys/local_api/get_obs_sales?key=lgepr
		
		if ($this->input->get("key") === "lgepr") $res = ["gerp_iods" => $this->my_func->get_gerp_iod(date("Y-m-01"), date("Y-m-t"))];
		else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_division(){
		//llamasys/local_api/get_division?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["division" => "H&A", "order" => "a"], 
				["division" => "HE", "order" => "b"], 
				["division" => "BS", "order" => "c"],
			];
		}
		else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_category(){
		//llamasys/local_api/get_category?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["seq" => 1, "division" => "H&A", "categry" => "REF"], 
				["seq" => 2, "division" => "H&A", "categry" => "Cooking"], 
				["seq" => 3, "division" => "H&A", "categry" => "W/M"], 
				["seq" => 4, "division" => "H&A", "categry" => "RAC"], 
				["seq" => 5, "division" => "H&A", "categry" => "SAC"], 
				["seq" => 6, "division" => "H&A", "categry" => "Chiller"], 
				["seq" => 7, "division" => "HE", "categry" => "TV"], 
				["seq" => 8, "division" => "HE", "categry" => "AV"],
				["seq" => 9, "division" => "BS", "categry" => "MNT"], 
				["seq" => 10, "division" => "BS", "categry" => "Signage"], 
				["seq" => 11, "division" => "BS", "categry" => "Commercial TV"],
			];
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
				

	public function get_obs_ml_month(){
		//llamasys/local_api/get_obs_ml_month?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$d = date("Y-m-d");
			
			$mls = $this->gen_m->filter("obs_most_likely", false, ["year" => date("Y", strtotime($d)), "month" => date("m", strtotime($d)), "category !=" => null]);
			
			$res = ["mls" => $mls];
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
		
		
		
		
	}
}
