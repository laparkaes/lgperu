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
		
		$stat = [];
		$stat["total"] = ["sales" => 0, "qty" => 0, "nsp" => 0];
		
		//load dates
		$dates = $this->my_func->dates_between($from, $to);
		$days = [];
		foreach($dates as $d){
			$day = date("d", strtotime($d));
			$days[] = $day;
			$stat[$day] = ["sales" => 0, "qty" => 0, "nsp" => 0];
		}
		
		$datas = [];
		
		foreach($divisions as $com => $divs){
			$datas[$com] = [
				"company" => $com,
				"stat" => $stat,
				"divs" => [],
			];
			
			foreach($divs as $div){
				$datas[$com]["divs"][$div] = [
					"division" => $div,
					"stat" => $stat,
					"models" => [],
				];
			}
		}
		
		//load rawdatas
		$s = ["model_category", "model", "bill_to_name"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount >" => 0];//where
		$g = ["model_category", "model", "bill_to_name"];//group fields
		
		$bill_tos = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($bill_tos as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			unset($item->model_category);
			
			foreach($dates as $d){
				$datas[$item->company]["divs"][$item->division]["models"][$item->model] = [
					"model" => $item->model,
					"stat" => $stat,
					"bill_tos" => [
						"D2C" => [
							"bill_to" => "D2C",
							"stat" => $stat,
						],
						"D2B2C" => [
							"bill_to" => "D2B2C",
							"stat" => $stat,
						],
						"ETC" => [
							"bill_to" => "ETC",
							"stat" => $stat,
						],
					],
				];
			}
		}
				
		//load rawdatas
		$s = ["model_category", "model", "bill_to_name", "close_date", "sum(sales_amount) as sales_amount", "sum(ordered_qty) as ordered_qty"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount >" => 0];//where
		$g = ["model_category", "model", "bill_to_name", "close_date"];//group fields
		
		$rawdatas = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($rawdatas as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			$item->sales_amount = round($item->sales_amount, 2);
			$item->nsp = round($item->sales_amount / $item->ordered_qty, 2);
			unset($item->model_category);
			
			$day = date("d", strtotime($item->close_date));
			
			$datas[$item->company]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$item->company]["stat"]["total"]["qty"] += $item->ordered_qty;
			$datas[$item->company]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$item->company]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$item->company]["stat"][$day]["qty"] += $item->ordered_qty;
			$datas[$item->company]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$item->company]["divs"][$item->division]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$item->company]["divs"][$item->division]["stat"]["total"]["qty"] += $item->ordered_qty;
			$datas[$item->company]["divs"][$item->division]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$item->company]["divs"][$item->division]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$item->company]["divs"][$item->division]["stat"][$day]["qty"] += $item->ordered_qty;
			$datas[$item->company]["divs"][$item->division]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["stat"]["total"]["qty"] += $item->ordered_qty;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["stat"][$day]["qty"] += $item->ordered_qty;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"]["total"]["qty"] += $item->ordered_qty;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"][$day]["qty"] += $item->ordered_qty;
			$datas[$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"][$day]["nsp"] += $item->nsp;
		}
		
		foreach($datas as $com){
			$divs = $com["divs"];
			foreach($divs as $div){
				$models = $div["models"];
				usort($models, function($a, $b) { return $b["stat"]["total"]["sales"] - $a["stat"]["total"]["sales"]; });
				
				$datas[$com["company"]]["divs"][$div["division"]]["models"] = $models;
			}
		}
		
		$data = [
			"days" => $days,
			"dates" => $dates,
			"datas" => $datas,
			"main" => "module/obs_nsp/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}

}
