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
		
		//load dates
		$dates = $this->my_func->dates_between($from, $to);
		
		$datas = [];
		
		foreach($dates as $d){
			$day = date("d", strtotime($d));
			$datas[$day] = [];
			
			foreach($divisions as $com => $divs){
				$datas[$day][$com] = [
					"company" => $com,
					"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
					"divs" => [],
				];
				
				foreach($divs as $div){
					$datas[$day][$com]["divs"][$div] = [
						"division" => $div,
						"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
						"models" => [],
					];
				}
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
				$datas[date("d", strtotime($d))][$item->company]["divs"][$item->division]["models"][$item->model] = [
					"model" => $item->model,
					"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
					"bill_tos" => [
						"D2C" => [
							"bill_to" => "D2C",
							"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
						],
						"D2B2C" => [
							"bill_to" => "D2B2C",
							"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
						],
						"ETC" => [
							"bill_to" => "ETC",
							"stat" => ["sales" => 0, "qty" => 0, "nsp" => 0],
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
			
			$datas[$day][$item->company]["stat"]["sales"] += $item->sales_amount;
			$datas[$day][$item->company]["stat"]["qty"] += $item->ordered_qty;
			$datas[$day][$item->company]["stat"]["nsp"] += $item->nsp;
			
			$datas[$day][$item->company]["divs"][$item->division]["stat"]["sales"] += $item->sales_amount;
			$datas[$day][$item->company]["divs"][$item->division]["stat"]["qty"] += $item->ordered_qty;
			$datas[$day][$item->company]["divs"][$item->division]["stat"]["nsp"] += $item->nsp;
			
			$datas[$day][$item->company]["divs"][$item->division]["models"][$item->model]["stat"]["sales"] += $item->sales_amount;
			$datas[$day][$item->company]["divs"][$item->division]["models"][$item->model]["stat"]["qty"] += $item->ordered_qty;
			$datas[$day][$item->company]["divs"][$item->division]["models"][$item->model]["stat"]["nsp"] += $item->nsp;
			
			$datas[$day][$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"]["sales"] += $item->sales_amount;
			$datas[$day][$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"]["qty"] += $item->ordered_qty;
			$datas[$day][$item->company]["divs"][$item->division]["models"][$item->model]["bill_tos"][$item->bill_to_name]["stat"]["nsp"] += $item->nsp;
		}
		
		
		foreach($datas as $day => $coms){
			echo $day." ----------------------------------------------<br/>";
			foreach($coms as $com){
				print_r($com["company"]); echo " /// ";
				print_r($com["stat"]); echo "<br/>";
				
				$divs = $com["divs"];
				foreach($divs as $div){
					echo "--- ";
					print_r($div["division"]); echo " /// ";
					print_r($div["stat"]); echo "<br/>";
					
					$models = $div["models"];
					foreach($models as $model){
						echo "------ ";
						print_r($model["model"]); echo " /// ";
						print_r($model["stat"]); echo "<br/>";
						
						$bill_tos = $model["bill_tos"];
						foreach($bill_tos as $bill_to){
							echo "--------- ";
							print_r($bill_to["bill_to"]); echo " /// ";
							print_r($bill_to["stat"]); echo "<br/>";
						}
						echo "<br/>";
					}
					echo "<br/>";
				}
				echo "<br/>";
			}
		}
		
		return;
		
		$data = [
			"dates" => $dates,
			"datas" => $datas,
			"main" => "module/obs_nsp/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}

}
