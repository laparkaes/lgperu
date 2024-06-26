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
	}
	
	public function test(){
		$order_items = $this->gen_m->all("order_item");
		
		//print_r($order_items);
		header('Content-Type: application/json');
		echo json_encode(["order_items" => $order_items]);
	}
	
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
	
	public function get_obs_magento(){
		//llamasys/local_api/get_obs_magento?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"local_time >=" => $f." 00:00:00",
				"local_time <=" => $t." 23:59:59",
			];
			
			$res = $this->gen_m->filter("obs_magento_item", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	private function get_gerp_iod($from, $to){
		//set db fields
		$s_g = ["create_date", "close_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level1_name","product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		
		//load all this month records
		$w_g = ["create_date >=" => $from, "create_date <=" => $to, "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "asc"], ["close_date", "asc"]]);
		
		//load all past month closed in this month
		$w_g_ = ["create_date <" => $from, "close_date >=" => $from, "close_date <=" => $to, "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		$gerps_ = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g_, null, null, [["create_date", "asc"], ["close_date", "asc"]]);
		
		//return merged array
		return array_merge($gerps_, $gerps);
	}
	
	public function get_obs_gerp_month(){
		//llamasys/local_api/get_obs_gerp_month?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$from = date("Y-m-01");
			$to = date("Y-m-t");
			
			$gerps = $this->get_gerp_iod($from, $to);
			$exr_ttm = round($this->my_func->get_exchange_rate_month_ttm(date("Y-m-d")), 2);
			
			foreach($gerps as $g){
				/*
				$r->unit_selling_price = round($r->unit_selling_price / $exchange_rate, 2);
				$r->sales_amount = round($r->sales_amount / $exchange_rate, 2);
				$r->tax_amount = round($r->tax_amount / $exchange_rate, 2);
				$r->charge_amount = round($r->charge_amount / $exchange_rate, 2);
				$r->line_total = round($r->line_total / $exchange_rate, 2);
				$r->list_price = round($r->list_price / $exchange_rate, 2);
				$r->original_list_price = round($r->original_list_price / $exchange_rate, 2);
				*/
				$g->sales_amount_usd = $g->sales_amount / $exr_ttm;
			}
			
			$res = ["gerp_iods" => $gerps];
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
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
}
