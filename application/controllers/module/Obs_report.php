<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$exchange_rate = 3.8;
		
		$from = $this->input->get("f") ? $this->input->get("f") : date("Y-m-01");
		$to = $this->input->get("t") ? $this->input->get("t") : date("Y-m-t");
		
		$f = [
			"local_time >=" => $from,
			"local_time <=" => $to,
		];
		
		//by sales status setting
		total
		"complete", "closed", 
		
		"awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed", 
		
		"payment_declined", "transfer_cancelled", "canceled", 
		
		
		
		
		
		
		
		
		
		
		
		$status_valid = [];
		$status_delivery = [];
		$status_invalid = [];
		
		$status = [];
		$status["total"] = ["code" => "total", "qty" => 0, "amount" => 0];
		$status_rec = $this->gen_m->only("obs_magento", "status");
		foreach($status_rec as $s) $status[$s->status] = ["code" => $s->status, "qty" => 0, "amount" => 0];
		
		//sales records load
		$sales = $this->gen_m->filter("obs_magento", false, $f, null, null, [["local_time", "desc"]]);
		foreach($sales as $sale){
			$status["total"]["qty"]++;
			$status["total"]["amount"] += $sale->grand_total_purchased;
			$status[$sale->status]["qty"]++;
			$status[$sale->status]["amount"] += $sale->grand_total_purchased;
		}
		
		usort($status, function($a, $b) {
			return ($a["amount"] < $b["amount"]);
		});
		
		/*
		echo "<textarea>";
		$aux = $sales[0];
		foreach($aux as $k => $a) print_r('<td><?= $sale->'.$k.' ?></td>');
		echo "</textarea>";
		*/
		$data = [
			"exchange_rate" => $exchange_rate,
			"from"		=> $from,
			"to"		=> $to,
			"status" 	=> $status,
			"sales" 	=> $sales,
			"main" 		=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}
}
