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
		
		$from = $this->input->get("f") ? $this->input->get("f") : date("Y-m-01");
		$to = $this->input->get("t") ? $this->input->get("t") : date("Y-m-t");
		
		//put date range correctly
		$w = [
			"local_time >=" => $from." 00:00:00",
			"local_time <=" => $to." 23:59:59",
		];
		
		//just need to load valid order information
		$w_in = [
			[
				"field" => "status", 
				"values" => ["complete", "closed", "awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"],
			],
		];
		
		//sales records load
		$sales = $this->gen_m->filter("obs_magento", false, $w, null, $w_in, [["local_time", "desc"]]);
		
		//$status_valid 		= ["complete", "closed"];
		//$status_on_process	= ["awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"];
		$mapping = [
			//category => division
			"RF" => "HA", "CA" => "HA", "WM" => "HA", "AC" => "HA", "TV" => "HE", "AV" => "HE", "AO" => "HE", "CS" => "BS", "MN" => "BS", "ZZ" => "ETC",
			//status => status_type
			"complete" => "valid", "closed" => "valid", "awaiting_transfer" => "on_process", "processing" => "on_process", "holded" => "on_process", "preparing_for_delivery" => "on_process", "picking_for_delivery" => "on_process", "on_delivery" => "on_process", "delivery_completed" => "on_process",
		];
		
		//division difine
		$divisinos = [];
		$divisions["HA"] = [
			"summary" => ["valid" => 0, "on_process" => 0],
			"categories" => [
				"RF" => ["valid" => 0, "on_process" => 0],
				"CA" => ["valid" => 0, "on_process" => 0],
				"WM" => ["valid" => 0, "on_process" => 0],
				"AC" => ["valid" => 0, "on_process" => 0],
			],
		];
		$divisions["HE"] = [
			"summary" => ["valid" => 0, "on_process" => 0],
			"categories" => [
				"TV" => ["valid" => 0, "on_process" => 0],
				"AV" => ["valid" => 0, "on_process" => 0],
				"AO" => ["valid" => 0, "on_process" => 0],
			],
		];
		$divisions["BS"] = [
			"summary" => ["valid" => 0, "on_process" => 0],
			"categories" => [
				"CS" => ["valid" => 0, "on_process" => 0],
				"MN" => ["valid" => 0, "on_process" => 0],
			],
		];
		$divisions["ETC"] = [
			"summary" => ["valid" => 0, "on_process" => 0],
			"categories" => [
				"ZZ" => ["valid" => 0, "on_process" => 0],
			],
		];
		
		$sales_items = $this->gen_m->filter("obs_magento_item", false, $w, null, $w_in, [["local_time", "desc"]]);
		//print_r($sales_items); echo "<br/><br/>";
		foreach($sales_items as $si){
			$divisions[$mapping[$si->model_category]]["categories"][$si->model_category][$mapping[$si->status]] += $si->amount;
		}
		
		foreach($divisions as $div => $detail){
			foreach($detail["categories"] as $category => $summary){
				//convert to USD
				$divisions[$div]["categories"][$category]["valid"] = round($summary["valid"] / $exchange_rate, 2);
				$divisions[$div]["categories"][$category]["on_process"] = round($summary["on_process"] / $exchange_rate, 2);
				
				//add to division summary
				$divisions[$div]["summary"]["valid"] += $divisions[$div]["categories"][$category]["valid"];
				$divisions[$div]["summary"]["on_process"] += $divisions[$div]["categories"][$category]["on_process"];
			}
		}
		
		/* print divisions variable
		foreach($divisions as $div => $detail){
			echo $div." ===> "; print_r($detail["summary"]); echo "<br/><br/>";
			
			foreach($detail["categories"] as $category => $summary){
				echo "---> ".$category.": ";
				print_r($summary);
				echo "<br/><br/>";
			}
		}
		*/
		
		//set LG basic divisions
		$mc_rec = $this->gen_m->only("obs_magento_item", "model_category");
		
		
		//by sales status setting
		$status = [];
		$status_rec = $this->gen_m->only("obs_magento", "status");
		foreach($status_rec as $s) $status[$s->status] = ["code" => $s->status, "qty" => 0, "amount" => 0];
		
		//run each sale
		foreach($sales as $sale){
			//order by status
			$status[$sale->status]["qty"]++;
			$status[$sale->status]["amount"] += $sale->grand_total_purchased / $exchange_rate;
		}
		
		//status process data set
		$status_process = $this->status_process($status, $exchange_rate);
		
		$data = [
			"exchange_rate" => $exchange_rate,
			"from"			=> $from,
			"to"			=> $to,
			"status" 		=> $status_process["summary"],
			"status_chart"	=> $status_process["chart"],
			"sales" 		=> $sales,
			"main" 			=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}
}
