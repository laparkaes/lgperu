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
	
	private function get_subsidiaries(){
		//set LG basic divisions. Use this variables to show what divisions exists in order items
		//$mc_rec = $this->gen_m->only("obs_magento_item", "model_category");
		
		//$status_valid 		= ["complete", "closed"];
		//$status_on_process	= ["awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"];
		
		//division difine
		$subsidiaries = [
			"LGEPR" => [
				"summary" => ["valid" => 0, "on_process" => 0],
				"divisions" => [
					"HA" => [
						"summary" => ["valid" => 0, "on_process" => 0],
						"categories" => [
							"RF" => ["valid" => 0, "on_process" => 0],
							"CA" => ["valid" => 0, "on_process" => 0],
							"WM" => ["valid" => 0, "on_process" => 0],
							"AC" => ["valid" => 0, "on_process" => 0],
						],
					],
					"HE" => [
						"summary" => ["valid" => 0, "on_process" => 0],
						"categories" => [
							"TV" => ["valid" => 0, "on_process" => 0],
							"AV" => ["valid" => 0, "on_process" => 0],
							"AO" => ["valid" => 0, "on_process" => 0],
						],
					],
					"BS" => [
						"summary" => ["valid" => 0, "on_process" => 0],
						"categories" => [
							"CS" => ["valid" => 0, "on_process" => 0],
							"MN" => ["valid" => 0, "on_process" => 0],
						],
					],
					"ETC" => [
						"summary" => ["valid" => 0, "on_process" => 0],
						"categories" => [
							"ZZ" => ["valid" => 0, "on_process" => 0],
						],
					],
				],
			],
		];
		
		return $subsidiaries;
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
		
		//set sales by division
		$summary_structure = ["Closed" => 0, "On process" => 0];
		
		$subsidiaries = [
			"LGEPR" =>[
				"summary" => $summary_structure,
				"divisions" => [
					"HA" => [
						"summary" => $summary_structure,
						"categories" => [],
					],
					"HE" => [
						"summary" => $summary_structure,
						"categories" => [],
					],
					"BS" => [
						"summary" => $summary_structure,
						"categories" => [],
					],
				],
			],
		];
		
		$div_map = [
			"Airconditioner" => "HA",
			"AO" => "HE",
			"AV" => "HE",
			"Commercial Display_Signage" => "BS",
			"Cooking Appliance" => "HA",
			"Monitor" => "BS",
			"PC" => "BS",
			"Refrigerator" => "HA",
			"TV" => "HE",
			"Washing Machine" => "HA",
		];
		
		$lvl1_rec = $this->gen_m->only("obs_gerp_sales_order", "product_level1_name", ["product_level1_name !=" => "Not Define"]);
		foreach($lvl1_rec as $item){
			$subsidiaries["LGEPR"]["divisions"][$div_map[$item->product_level1_name]]["categories"][$item->product_level1_name]["summary"] = $summary_structure;
			//echo $item->product_level1_name."<br/>";
		}
		
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
		}
		
		
		
		
		$m_categories = [];
		$m_categories_rec = $this->gen_m->only("obs_gerp_sales_order", "model_category", ["model_category !=" => null]);
		foreach($m_categories_rec as $mc) if ($mc->model_category) $m_categories[] = $mc->model_category;
		//print_r($m_categories); echo "<br/><br/>";
		
		$mapping1 = [];
		$lvl1_mc = $this->gen_m->filter_select("obs_gerp_sales_order", false, ["product_level1_name", "model_category"], null, null, [["field" => "model_category", "values" => $m_categories]], [["product_level1_name", "asc"]], "", "", "model_category");
		foreach($lvl1_mc as $item){
			$mapping1[$item->model_category] = $item->product_level1_name;
			//print_r($item); echo "<br/>";
		}
		
		$mapping2 = [
			"Refrigerator" => "REF",
			"Cooking Appliance" => "Cooking",
			"Washing Machine" => "WM",
			"Airconditioner" => "AC",
			"TV" => "TV",
			"AO" => "AV",
			"Monitor" => "MNT",
			"PC" => "PC",
			"Commercial Display_Signage" => "CS",
		];
		
		
		
		/*
		
		
		"REF" => "REF",
		"O" => "COOK",
		"O" => "COOK",
		
		
		A/C
		RAC
		SAC
		
		AUD
		
		CAV
		CTV
		CVT
		LCD
		LTV
		MNT
		MWO
		
		PC
		SGN
		W/M
		*/
		
		$data = [
			"exchange_rate" => $exchange_rate,
			"from"			=> $from,
			"to"			=> $to,
			"magentos" 		=> $magentos,
			"gerps" 		=> $gerps,
			"main" 			=> "module/obs_report/index",
		];
		
		//$this->load->view('layout', $data);
		
		
		/*
		echo "<br/><br/><br/>";
		$status_rec = $this->gen_m->only("obs_gerp_sales_order", "line_status");
		print_r($status_rec);
		echo "<br/><br/><br/>";
		$status_rec = $this->gen_m->only("obs_gerp_sales_order", "order_status");
		print_r($status_rec);
		*/
		
		
		/*
		//put date range correctly
		$w = [
			"local_time >=" => $from." 00:00:00",
			"local_time <=" => $to." 23:59:59",
		];
		
		//just need to load valid order information
		
		//to be used for divisions
		$mapping = [
			//category => division
			"RF" => "HA", "CA" => "HA", "WM" => "HA", "AC" => "HA", 
			"TV" => "HE", "AV" => "HE", "AO" => "HE", 
			"CS" => "BS", "MN" => "BS", 
			"ZZ" => "ETC",
			//status => status_type
			"complete" => "valid", //"closed" => "valid", //closed is return
			"awaiting_transfer" => "on_process", "processing" => "on_process", "holded" => "on_process", "preparing_for_delivery" => "on_process", "picking_for_delivery" => "on_process", "on_delivery" => "on_process", "delivery_completed" => "on_process",
		];
		
		$subsidiaries = $this->get_subsidiaries();
		
		$sales_items = $this->gen_m->filter("obs_magento_item", false, $w, null, $w_in, [["local_time", "desc"]]); //print_r($sales_items); echo "<br/><br/>";
		foreach($sales_items as $si){
			$subsidiaries["LGEPR"]["divisions"][$mapping[$si->model_category]]["categories"][$si->model_category][$mapping[$si->status]] += $si->amount;
		}
		
		foreach($subsidiaries as $sub => $subsidiary){
			$divisions = $subsidiary["divisions"];
			foreach($divisions as $div => $division){
				$categories = $division["categories"];
				foreach($categories as $category => $summary){
					//convert to USD
					$subsidiaries[$sub]["divisions"][$div]["categories"][$category]["valid"] = round($summary["valid"] / $exchange_rate / 1.18, 2);
					$subsidiaries[$sub]["divisions"][$div]["categories"][$category]["on_process"] = round($summary["on_process"] / $exchange_rate / 1.18, 2);
					
					//add to division summary
					$subsidiaries[$sub]["divisions"][$div]["summary"]["valid"] += $divisions[$div]["categories"][$category]["valid"];
					$subsidiaries[$sub]["divisions"][$div]["summary"]["on_process"] += $divisions[$div]["categories"][$category]["on_process"];
					
					//add to LGEPR total
					$subsidiaries[$sub]["summary"]["valid"] += $divisions[$div]["categories"][$category]["valid"];
					$subsidiaries[$sub]["summary"]["on_process"] += $divisions[$div]["categories"][$category]["on_process"];
				}
			}
		}
		
		//by sales status setting
		$status = [];
		$status_rec = $this->gen_m->only("obs_magento", "status");
		foreach($status_rec as $s) $status[$s->status] = ["code" => $s->status, "qty" => 0, "amount" => 0];
		
		//sales records load
		$sales = $this->gen_m->filter("obs_magento", false, $w, null, $w_in, [["local_time", "desc"]]);
		foreach($sales as $sale){
			//order by status
			$status[$sale->status]["qty"]++;
			$status[$sale->status]["amount"] += $sale->grand_total_purchased / $exchange_rate;
		}
		
		//status process data set
		$status_process = $this->status_process($status, $exchange_rate);
		
		*/
	}
}
