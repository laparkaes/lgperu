<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function status_process($status, $exchange_rate){
		usort($status, function($a, $b) {
			return ($a["amount"] < $b["amount"]);
		});
		
		$status_summary = [
			"total" => [
				"color"		=> "primary",
				"color_hex"	=> "#0d6efd",
				"group"		=> "Total (Valid + On Process)",
				"list"		=> [],
				"qty" 		=> 0, 
				"amount" 	=> 0,
				"details"	=> [],
			],
			"valid" => [
				"color"		=> "success",
				"color_hex"	=> "#198754",
				"group"		=> "Valid",
				"list"		=> ["complete", "closed"],
				"qty" 		=> 0, 
				"amount" 	=> 0,
				"details"	=> [],
			],
			"on_process" => [
				"color"		=> "warning",
				"color_hex"	=> "#ffc107",
				"group"		=> "On Process",
				"list"		=> ["awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"],
				"qty" 		=> 0, 
				"amount" 	=> 0,
				"details"	=> [],
			],
			/*
			"invalid" => [
				"color"		=> "danger",
				"color_hex"	=> "#dc3545",
				"group"		=> "Invalid",
				"list"		=> ["payment_declined", "transfer_cancelled", "canceled"],
				"qty" 		=> 0, 
				"amount" 	=> 0,
				"details"	=> [],
			],
			*/
		];
		
		foreach($status as $s){
			foreach($status_summary as $ss_code => $ss){
				if (in_array($s["code"], $ss["list"])){
					$status_summary[$ss_code]["qty"] += $s["qty"];
					$status_summary[$ss_code]["amount"] += $s["amount"];
					$status_summary[$ss_code]["details"][] = $s;
				}
			}
		}
		$status_summary["total"]["qty"] = $status_summary["valid"]["qty"] + $status_summary["on_process"]["qty"];
		$status_summary["total"]["amount"] = $status_summary["valid"]["amount"] + $status_summary["on_process"]["amount"];
		
		$status_chart = ["amount" => [], "qty" => []];
		foreach($status_summary as $s){
			if ($s["group"] !== "Total (Valid + On Process)"){
				$status_chart["amount"][] = ["value" => round($s["amount"], 2), "name" => $s["group"], "itemStyle" => ["color" => $s["color_hex"]]];
				$status_chart["qty"][] = ["value" => $s["qty"], "name" => $s["group"], "itemStyle" => ["color" => $s["color_hex"]]];
			}
		}
		
		return ["summary" => $status_summary, "chart" => $status_chart];
	}
	
	public function index(){
		$exchange_rate = 3.8;
		
		//get date range
		$from = ($this->input->get("f") ? $this->input->get("f") : date("Y-m-01"));
		$to = ($this->input->get("t") ? $this->input->get("t") : date("Y-m-t"));
		
		//set magento data filters
		$s_m = ["grand_total_purchased", "gerp_order_no", "local_time", "company_name_through_vipkey", "vipkey", "coupon_code", "coupon_rule", "discount_amount", "devices", "status", "customer_group", "department", "province"];
		$w_m = ["local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"];
		$w_in_m = [
			[
				"field" => "status", 
				"values" => ["complete", "awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"],
			],
		];
		
		$magentos = $this->gen_m->filter_select("obs_magento", false, $s_m, $w_m, null, $w_in_m, [["local_time", "desc"]]);
		//foreach($magentos as $m){echo $m->local_time." /// ".$m->status."<br/>";}
		
		//set magento gerp data filters
		$s_g = ["create_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		$w_g = ["create_date >=" => $from, "create_date <=" => $to, "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "desc"]]);
		//foreach($gerps as $g){echo $g->create_date." /// ".$g->order_status." - ".$g->line_status."<br/>";}
		
		//set sales by subsidiaries
		//print_r($this->gen_m->only("obs_gerp_sales_order", "line_status")); echo "<br/><br/>";
		$f = [
			"order_status !=" => "Cancelled",
			"line_status !=" => "Cancelled",
			"create_date >=" => $from, 
			"create_date <=" => $to,
		];
		
		$categories = [
			"HA" => ["Refrigerator", "Cooking Appliance", "Washing Machine", "Airconditioner"],
			"HE" => ["TV", "AV", "AO"],
			"BS" => ["Monitor", "PC", "Commercial Display_Signage"],
		];
		$divisions = ["HA", "HE", "BS"];
		$subsidiaries = [];
		$subsidiaries_rec = $this->gen_m->only("obs_gerp_sales_order", "customer_department", ["customer_department !=" => null]);
		foreach($subsidiaries_rec as $sub){
			$sub_total = $sub_closed = $sub_on_process = 0;
			$f["customer_department"] = $sub->customer_department;
			
			$subsidiaries[$sub->customer_department] = [];
			$subsidiaries[$sub->customer_department]["divisions"] = [];
			foreach($divisions as $div){
				$div_total = $div_closed = $div_on_process = 0;
				
				$subsidiaries[$sub->customer_department]["divisions"][$div] = [];
				$subsidiaries[$sub->customer_department]["divisions"][$div]["categories"] = [];
				foreach($categories[$div] as $cat){
					$cat_closed = $cat_on_process = 0;
					$f["product_level1_name"] = $cat;
					
					//calculate category amount
					$w_in = [["field" => "line_status", "values" => ["Closed"]]];
					$cat_closed = $this->gen_m->sum("obs_gerp_sales_order", "sales_amount", $f, $w_in)->sales_amount / $exchange_rate;//convert to USD
					
					$w_in = [["field" => "line_status", "values" => ["Awaiting Fulfillment", "Awaiting Shipping", "Booked", "Pending pre-billing acceptance"]]];
					$cat_on_process = $this->gen_m->sum("obs_gerp_sales_order", "sales_amount", $f, $w_in)->sales_amount / $exchange_rate;//convert to USD
					
					$cat_total = $cat_closed + $cat_on_process;
					//add to division amount
					$div_total += $cat_total;
					$div_closed += $cat_closed;
					$div_on_process += $cat_on_process;
					
					//add to subsidiary amount
					$sub_total += $cat_total;
					$sub_closed += $cat_closed;
					$sub_on_process += $cat_on_process;
					
					$subsidiaries[$sub->customer_department]["divisions"][$div]["categories"][$cat] = [];
					$subsidiaries[$sub->customer_department]["divisions"][$div]["categories"][$cat]["summary"] = [
						"total" => $cat_total,
						"closed" => $cat_closed,
						"on_process" => $cat_on_process,
					];
				}
				
				$subsidiaries[$sub->customer_department]["divisions"][$div]["summary"] = [
					"total" => $div_total,
					"closed" => $div_closed,
					"on_process" => $div_on_process,
				];
			}
			
			$subsidiaries[$sub->customer_department]["summary"] = [
				"total" => $sub_total,
				"closed" => $sub_closed,
				"on_process" => $sub_on_process,
			];
		}
		
		/*
		foreach($subsidiaries as $sub => $subsidiary){
			echo $sub." ====> ";
			print_r($subsidiary["summary"]);
			echo "<br/><br/>";
			
			foreach($subsidiary["divisions"] as $div => $divisions){
				echo "---";
				echo $div." ====> ";
				print_r($divisions["summary"]);
				echo "<br/><br/>";
				
				foreach($divisions["categories"] as $cat => $category){
					echo "------";
					echo $cat." ====> ";
					print_r($category);
					echo "<br/><br/>";
				}
			}	
		} */
		
		$data = [
			"exchange_rate" => $exchange_rate,
			"from"			=> $from,
			"to"			=> $to,
			"subsidiaries" 		=> $subsidiaries,
			"magentos" 		=> $magentos,
			"gerps" 		=> $gerps,
			"main" 			=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}
}
