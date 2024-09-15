<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_nsp extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		//basic filters
		$today = date("Y-m-d");
		$today = "2024-06-13";
		
		$from = date("Y-m-01", strtotime($today));
		$to = date("Y-m-t", strtotime($today));
		
		//mapping datas
		$m_division = [
			"" => "",//PTO case
			"A/C" => "Chilller",
			"AUD" => "Audio",
			"CAV" => "Audio",
			"CTV" => "Commercial TV",
			"CVT" => "Cooking",
			"DS" => "DS",
			"LCD" => "LTV",
			"LTV" => "LTV",
			"MNT" => "MNT",
			"MWO" => "Cooking",
			"O" => "Cooking",
			"PC" => "PC",
			"RAC" => "RAC",
			"REF" => "REF",
			"SAC" => "SAC",
			"SGN" => "MNT Signage",
			"W/M" => "W/M",
		];
		
		$m_company = [
			"" => "",//PTO case
			"REF" => "H&A",
			"Cooking" => "H&A",
			"W/M" => "H&A",
			"RAC" => "H&A",
			"SAC" => "H&A",
			"Chilller" => "H&A",
			"LTV" => "HE",
			"Audio" => "HE",
			"MNT" => "BS",
			"PC" => "BS",
			"DS" => "BS",
			"MNT Signage" => "BS",
			"Commercial TV" => "BS",
		];
		
		$m_bill_to = [
			"B2C" => "D2C",
			"B2B2C" => "D2B2C",
			"B2P" => "ETC",
			"B2E" => "ETC",
			"One time_Boleta" => "ETC",
		];
		
		//set summary array structure
		$divisions = [
			"H&A" => ["REF", "Cooking", "W/M", "RAC", "SAC", "Chilller"], 
			"HE" => ["LTV", "Audio"], 
			"BS" => ["MNT", "PC", "DS", "MNT Signage", "Commercial TV"],
		];
		
		$datas = [];
		
		foreach($divisions as $com => $divs){
			$datas[$com] = [
				"company" => $com,
				"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
				"divs" => [],
			];
			
			foreach($divs as $div){
				$datas[$com]["divs"][$div] = [
					"division" => $div,
					"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
					"models" => [],
				];
				
				//load models, sort by sales_amount, set bill_tos
			}
		}
		
		//load rawdatas
		$s = ["model_category", "model", "bill_to_name", "close_date", "sum(sales_amount) as sales_amount", "sum(ordered_qty) as ordered_qty"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount >" => 0];//where
		$g = ["model_category", "model", "close_date"];//group fields
		
		$rawdatas = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($rawdatas as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			$item->sales_amount = round($item->sales_amount, 2);
			$item->nsp = round($item->sales_amount / $item->ordered_qty, 2);
			
			unset($item->model_category);
		}
		
		foreach($datas as $com){
			print_r($com["company"]); echo " /// ";
			print_r($com["stat"]); echo "<br/>";
			
			$divs = $com["divs"];
			foreach($divs as $div){
				echo "--- ";
				print_r($div["division"]); echo " /// ";
				print_r($div["stat"]); echo " /// ";
				
				print_r($div["models"]); echo "<br/>";
			}
			echo "<br/>";
		}
		
		return;
		//new stdClass;
		
		
		
		$data = [
			"rawdatas" => $rawdatas,
			"main" => "module/obs_nsp/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}

}
