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
	/*
	public function get_sales_order(){
		//llamasys/local_api/get_sales_order?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"order_date >=" => $f,
				"order_date <=" => $t,
			];
			
			$res = $this->gen_m->filter("dash_sales_order_inquiry", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_closed_order(){
		//llamasys/local_api/get_closed_order?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"order_date >=" => $f,
				"order_date <=" => $t,
			];
			
			$res = $this->gen_m->filter("dash_closed_order_inquiry", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	*/
	
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
	
	private function get_gerp_iod($from, $to){
		//set db fields
		$s_g = ["create_date", "close_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level1_name","product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		
		//load all this month records
		$w_g = ["create_date >=" => $from, "create_date <=" => $to, "line_status !=" => "Cancelled"];
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "desc"], ["close_date", "desc"]]);
		
		//load no closed orders
		$w_g_ = ["create_date <" => $from];
		$w_in_ = [["field" => "line_status", "values" => ["Awaiting Fulfillment", "Awaiting Shipping", "Booked", "Pending pre-billing acceptance"]]];
		$gerps_ = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g_, null, $w_in_, [["create_date", "desc"], ["close_date", "desc"]]);
		
		//return merged array
		return array_merge($gerps, $gerps_);
	}
	
	public function get_obs_gerp_month(){
		//llamasys/local_api/get_obs_gerp_month?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$from = date("Y-m-01");
			$to = date("Y-m-t");
			
			$exr_ttm = round($this->my_func->get_exchange_rate_month_ttm(date("Y-m-d")), 2);
			$gerps = $this->get_gerp_iod($from, $to);
			foreach($gerps as $g){
				$g->line_no = "'".$g->line_no;
				$g->sales_amount_usd = $g->sales_amount / $exr_ttm;
				$g->model_category_dash = $g->model_category ? $this->category_map_inv[$g->model_category] : null;
				$g->division_dash = $g->model_category_dash ? $this->division_map_inv[$g->model_category_dash] : null;
			}
			
			$res = ["gerp_iods" => $gerps];
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
