<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_nsp extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function calculate_nsp($stats, $order_by = "sales"){
		$nsp_arr = [];
		foreach($stats as $day => $stat){
			if ($day !== "total"){
				if ($order_by === "sales"){
					$stats[$day]["nsp"] = $stats[$day]["qty"] ? $stats[$day]["sales"] / $stats[$day]["qty"] : 0;
					if ($stats[$day]["nsp"]) $nsp_arr[] = $stats[$day]["nsp"];
				}else{
					$stats[$day]["nsp"] = $stats[$day]["qty"];
					if ($stats[$day]["nsp"]) $nsp_arr[] = $stats[$day]["nsp"];
				}
			}
			
			//echo $day." /// "; print_r($stats[$day]); echo "<br/>";
		}
		
		$stats["total"]["nsp"] = $nsp_arr ? array_sum($nsp_arr) / count($nsp_arr) : 0;
		
		//echo "total /// "; print_r($stats["total"]); echo "<br/>";
		
		return $stats;
	}
	
	public function index(){
		//basic filters
		$today = date("Y-m-d");
		//$today = "2024-06-13";
		
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
		$subsidiaries = ["LGEPR"];
		
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
		foreach($subsidiaries as $sub){
			$datas[$sub] = [
				"subsidiary" => $sub,
				"stat" => $stat,
				"coms" => [],
			];
			
			foreach($divisions as $com => $divs){
				$datas[$sub]["coms"][$com] = [
					"company" => $com,
					"stat" => $stat,
					"divs" => [],
				];
				
				foreach($divs as $div){
					$datas[$sub]["coms"][$com]["divs"][$div] = [
						"division" => $div,
						"stat" => $stat,
						"models" => [],
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
		}
		
		//load rawdatas
		$s = ["customer_department", "model_category", "model", "bill_to_name"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount !=" => 0];//where
		$g = ["customer_department", "model_category", "model", "bill_to_name"];//group fields
		
		$bill_tos = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($bill_tos as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			unset($item->model_category);
			
			foreach($dates as $d){
				$datas[$item->customer_department]["coms"][$item->company]["divs"][$item->division]["models"][$item->model] = [
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
		$s = ["customer_department", "model_category", "model", "bill_to_name", "close_date", "sum(sales_amount) as sales_amount", "sum(ordered_qty) as ordered_qty"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount !=" => 0];//where
		$g = ["customer_department", "model_category", "model", "bill_to_name", "close_date"];//group fields
		
		$rawdatas = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($rawdatas as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			$item->sales_amount = round($item->sales_amount, 2);
			//$item->nsp = $item->ordered_qty ? round($item->sales_amount / $item->ordered_qty, 2) : 0;
			unset($item->model_category);
			
			$sub = $item->customer_department;
			$com = $item->company;
			$div = $item->division;
			$mod = $item->model;
			$bto = $item->bill_to_name;
			$day = date("d", strtotime($item->close_date));
			
			//set up total stats
			
			$datas[$sub]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["bill_tos"][$bto]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["bill_tos"][$bto]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["bill_tos"][$bto]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["bill_tos"][$bto]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["bill_tos"][$bto]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["bill_tos"][$bto]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"][$day]["nsp"] += $item->nsp;
		}
		
		//sort criteria
		$order_by = $this->input->get("sort");
		if (!$order_by) $order_by = "sales";
		
		//nsp calculation and sort by 
		foreach($datas as $sub){
			$datas[$sub["subsidiary"]]["stat"] = $this->calculate_nsp($sub["stat"], $order_by);
			
			/* subsidiary stat print
			echo $sub["subsidiary"]."<br/>";
			foreach($datas[$sub["subsidiary"]]["stat"] as $day => $stat){
				echo $day." /// ";
				print_r($stat);
				echo "<br/>";
			}
			echo "<br/>"; */
			
			foreach($sub["coms"] as $com){
				$datas[$sub["subsidiary"]]["coms"][$com["company"]]["stat"] = $this->calculate_nsp($com["stat"], $order_by);
				
				/* company stat print
				echo $com["company"]."<br/>";
				foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["stat"] as $day => $stat){
					echo $day." /// ";
					print_r($stat);
					echo "<br/>";
				}
				echo "<br/>"; */
				
				foreach($com["divs"] as $div){
					$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["stat"] = $this->calculate_nsp($div["stat"], $order_by);
					
					/* division stat print
					echo $div["division"]."<br/>";
					foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["stat"] as $day => $stat){
						echo $day." /// ";
						print_r($stat);
						echo "<br/>";
					}
					echo "<br/>"; */
					
					foreach($div["bill_tos"] as $bill_to){
						$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["bill_tos"][$bill_to["bill_to"]]["stat"] = $this->calculate_nsp($bill_to["stat"], $order_by);
						
						/* company stat print
						echo $bill_to["bill_to"]."<br/>";
						foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["bill_tos"][$bill_to["bill_to"]]["stat"] as $day => $stat){
							echo $day." /// ";
							print_r($stat);
							echo "<br/>";
						}
						echo "<br/>"; */
					}
					
					foreach($div["models"] as $model){
						$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["stat"] = $this->calculate_nsp($model["stat"], $order_by);
						
						/* model stat print
						echo $model["model"]."<br/>";
						foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["stat"] as $day => $stat){
							echo $day." /// ";
							print_r($stat);
							echo "<br/>";
						}
						echo "<br/>"; */
						
						foreach($model["bill_tos"] as $bill_to){
							$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["bill_tos"][$bill_to["bill_to"]]["stat"] = $this->calculate_nsp($bill_to["stat"], $order_by);
							
							/* company stat print
							echo $bill_to["bill_to"]."<br/>";
							foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["bill_tos"][$bill_to["bill_to"]]["stat"] as $day => $stat){
								echo $day." /// ";
								print_r($stat);
								echo "<br/>";
							}
							echo "<br/>"; */
						}
					}
					
					//model sort by criteria
					$models = $datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"];
				
					if ($order_by === "sales") usort($models, function($a, $b) { return $b["stat"]["total"]["sales"] - $a["stat"]["total"]["sales"]; });
					elseif ($order_by === "qty"){
						usort($models, function($a, $b) {
							if ($b["stat"]["total"]["qty"] == $a["stat"]["total"]["qty"]) return $b["stat"]["total"]["sales"] - $a["stat"]["total"]["sales"];
							else return $b["stat"]["total"]["qty"] - $a["stat"]["total"]["qty"];
						});
					}
					
					$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"] = $models;
					
					/* model sorted stat print
					foreach($models as $model){
						echo $model["model"]." sorted <br/>";
						foreach($model["stat"] as $day => $stat){
							echo $day." /// ";
							print_r($stat);
							echo "<br/>";
						}
						echo "<br/>";
					} */
				}
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
	
	public function debug(){
		//basic filters
		$today = date("Y-m-d");
		//$today = "2024-06-13";
		
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
		$subsidiaries = ["LGEPR"];
		
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
		foreach($subsidiaries as $sub){
			$datas[$sub] = [
				"subsidiary" => $sub,
				"stat" => $stat,
				"coms" => [],
			];
			
			foreach($divisions as $com => $divs){
				$datas[$sub]["coms"][$com] = [
					"company" => $com,
					"stat" => $stat,
					"divs" => [],
				];
				
				foreach($divs as $div){
					$datas[$sub]["coms"][$com]["divs"][$div] = [
						"division" => $div,
						"stat" => $stat,
						"models" => [],
					];
				}
			}
		}
		
		//load rawdatas
		$s = ["customer_department", "model_category", "model", "bill_to_name"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount !=" => 0];//where
		$g = ["customer_department", "model_category", "model", "bill_to_name"];//group fields
		
		$bill_tos = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($bill_tos as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			unset($item->model_category);
			
			foreach($dates as $d){
				$datas[$item->customer_department]["coms"][$item->company]["divs"][$item->division]["models"][$item->model] = [
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
		$s = ["customer_department", "model_category", "model", "bill_to_name", "close_date", "sum(sales_amount) as sales_amount", "sum(ordered_qty) as ordered_qty"];//select
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount !=" => 0];//where
		$g = ["customer_department", "model_category", "model", "bill_to_name", "close_date"];//group fields
		
		$rawdatas = $this->gen_m->only_multi("v_obs_sales_order", $s, $w, $g);
		foreach($rawdatas as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			$item->sales_amount = round($item->sales_amount, 2);
			//$item->nsp = $item->ordered_qty ? round($item->sales_amount / $item->ordered_qty, 2) : 0;
			unset($item->model_category);
			
			$sub = $item->customer_department;
			$com = $item->company;
			$div = $item->division;
			$mod = $item->model;
			$bto = $item->bill_to_name;
			$day = date("d", strtotime($item->close_date));
			
			//set up total stats
			
			$datas[$sub]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["stat"][$day]["nsp"] += $item->nsp;
			
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"]["total"]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"]["total"]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"]["total"]["nsp"] += $item->nsp;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"][$day]["sales"] += $item->sales_amount;
			$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"][$day]["qty"] += $item->ordered_qty;
			//$datas[$sub]["coms"][$com]["divs"][$div]["models"][$mod]["bill_tos"][$bto]["stat"][$day]["nsp"] += $item->nsp;
		}
		
		//sort criteria
		$order_by = $this->input->get("sort");
		if (!$order_by) $order_by = "sales";
		
		//nsp calculation and sort by 
		foreach($datas as $sub){
			$datas[$sub["subsidiary"]]["stat"] = $this->calculate_nsp($sub["stat"]);
			
			/* subsidiary stat print */
			echo $sub["subsidiary"]."<br/>";
			foreach($datas[$sub["subsidiary"]]["stat"] as $day => $stat){
				echo $day." /// ";
				print_r($stat);
				echo "<br/>";
			}
			echo "<br/>";
			
			foreach($sub["coms"] as $com){
				$datas[$sub["subsidiary"]]["coms"][$com["company"]]["stat"] = $this->calculate_nsp($com["stat"]);
				
				/* company stat print */
				echo $com["company"]."<br/>";
				foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["stat"] as $day => $stat){
					echo $day." /// ";
					print_r($stat);
					echo "<br/>";
				}
				echo "<br/>";
				
				foreach($com["divs"] as $div){
					$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["stat"] = $this->calculate_nsp($div["stat"]);
					
					/* division stat print */
					echo $div["division"]."<br/>";
					foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["stat"] as $day => $stat){
						echo $day." /// ";
						print_r($stat);
						echo "<br/>";
					}
					echo "<br/>";
					
					foreach($div["models"] as $model){
						$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["stat"] = $this->calculate_nsp($model["stat"]);
						
						/* model stat print */
						echo $model["model"]."<br/>";
						foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["stat"] as $day => $stat){
							echo $day." /// ";
							print_r($stat);
							echo "<br/>";
						}
						echo "<br/>";
						
						foreach($model["bill_tos"] as $bill_to){
							$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["bill_tos"][$bill_to["bill_to"]]["stat"] = $this->calculate_nsp($bill_to["stat"]);
							
							/* company stat print */
							echo $bill_to["bill_to"]."<br/>";
							foreach($datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"][$model["model"]]["bill_tos"][$bill_to["bill_to"]]["stat"] as $day => $stat){
								echo $day." /// ";
								print_r($stat);
								echo "<br/>";
							}
							echo "<br/>";
						}
					}
					
					//model sort by criteria
					$models = $datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"];
				
					if ($order_by === "sales") usort($models, function($a, $b) { return $b["stat"]["total"]["sales"] - $a["stat"]["total"]["sales"]; });
					elseif ($order_by === "qty"){
						usort($models, function($a, $b) {
							if ($b["stat"]["total"]["qty"] == $a["stat"]["total"]["qty"]) return $b["stat"]["total"]["sales"] - $a["stat"]["total"]["sales"];
							else return $b["stat"]["total"]["qty"] - $a["stat"]["total"]["qty"];
						});
					}
					
					$datas[$sub["subsidiary"]]["coms"][$com["company"]]["divs"][$div["division"]]["models"] = $models;
					
					/* model sorted stat print */
					foreach($models as $model){
						echo $model["model"]." sorted <br/>";
						foreach($model["stat"] as $day => $stat){
							echo $day." /// ";
							print_r($stat);
							echo "<br/>";
						}
						echo "<br/>";
					}
				}
			}
		}
	}
}
