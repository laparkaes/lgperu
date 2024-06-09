<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function status_process($sales, $exchange_rate){
		//by sales status setting
		$status = [];
		$status["total"] = ["code" => "total", "qty" => 0, "amount" => 0];
		$status_rec = $this->gen_m->only("obs_magento", "status");
		foreach($status_rec as $s) $status[$s->status] = ["code" => $s->status, "qty" => 0, "amount" => 0];
		
		foreach($sales as $sale){
			$status[$sale->status]["qty"]++;
			$status[$sale->status]["amount"] += $sale->grand_total_purchased / $exchange_rate;
		}
		
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
			"invalid" => [
				"color"		=> "danger",
				"color_hex"	=> "#dc3545",
				"group"		=> "Invalid",
				"list"		=> ["payment_declined", "transfer_cancelled", "canceled"],
				"qty" 		=> 0, 
				"amount" 	=> 0,
				"details"	=> [],
			],
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
		
		$f = [
			"local_time >=" => $from." 00:00:00",
			"local_time <=" => $to." 23:59:59",
		];
		
		//sales records load
		$sales = $this->gen_m->filter("obs_magento", false, $f, null, null, [["local_time", "desc"]]);
		
		//status process data set
		$status_process = $this->status_process($sales, $exchange_rate);
		
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
