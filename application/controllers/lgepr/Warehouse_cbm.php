<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_cbm extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}

	public function index(){
		
		$inventory = $this->gen_m->filter("lgepr_stock", false, ["on_hand_cbm >" => 0]);//start
		$arrivals = $this->gen_m->filter("lgepr_container", false, ["eta >=" => date("Y-m-d", strtotime("-2 day", strtotime($inventory[0]->updated)))], null, null, [["eta", "asc"]]);//additional CBM
		$sales = $this->gen_m->filter("lgepr_sales_order", false, ["cbm >" => 0]);//reduce CBM
		
		//setting date range to CBM simulation
		$from = $inventory[0]->updated;
		$to = $arrivals[count($arrivals)-1]->eta;
		
		//define basic array
		$arr_detail = ["arrival" => 0, "sales" => 0, "actual" => 0, "progress" => 0];
		
		$arr_division = [
			"HS" => $arr_detail,
			"MS" => $arr_detail,
			"ES" => $arr_detail,
			"Total" => $arr_detail,
		];
		
		$arr_basic = [];
		
		$pivot = strtotime($from);
		$until = strtotime($to);
		while($pivot <= $until){
			//echo date("Y-m-d", $pivot)."<br/>";
			
			$y = date("Y", $pivot);
			$m = date("m", $pivot);
			$d = date("d", $pivot);
			
			if (!array_key_exists($y, $arr_basic)) $arr_basic[$y] = [];
			if (!array_key_exists($m, $arr_basic[$y])) $arr_basic[$y][$m] = [];
			if (!array_key_exists($d, $arr_basic[$y][$m])) $arr_basic[$y][$m][$d] = [];
			
			$arr_basic[$y][$m][$d] = $arr_division;
			
			$pivot = strtotime("+1 day", $pivot);
		}
		
		//echo $from." ".$to."<br/><br/><br/><br/><br/><br/>";
		//print_r($arr_basic); echo "<br/><br/><br/><br/>";
		
		$by_org = [];
		$by_3pl = [
			"APM" => $arr_basic,
			"KLO" => $arr_basic,
		];
		
		//set initial CBM
		foreach($inventory as $item){
			if ($item->dash_company){
				//print_r($item); echo "<br/><br/>";
				
				if (!array_key_exists($item->org, $by_org)) $by_org[$item->org] = $arr_basic;
				
				$y = date("Y", strtotime($item->updated));
				$m = date("m", strtotime($item->updated));
				$d = date("d", strtotime($item->updated));
				
				/*
				Note!! projector line doesn't have company and division
				
				Examples)
				
				stdClass Object ( [stock_id] => 1480 [org] => N4M [dash_company] => [dash_division] => [sub_inventory] => GS-NA-IN [grade] => NONE [model_category_code] => PRJ [model_Category_name] => PROJECTOR [model] => SC-80.PRO [model_description] => Projection Screen [model_status] => Active [available_qty] => 138 [updated] => 2025-06-18 [seaStockTotal] => 0 [seaStockW1] => 0 [seaStockW2] => 0 [seaStockW3] => 0 [seaStockW4] => 0 [seaStockW5] => 0 [product_level4] => TVACPRNN [on_hand] => 138 [on_hand_cbm] => 3.37631 )
				
				stdClass Object ( [stock_id] => 2455 [org] => N4S [dash_company] => [dash_division] => [sub_inventory] => GOODSET-OB [grade] => GOOD [model_category_code] => PRJ [model_Category_name] => PROJECTOR [model] => HU710PB.AWF [model_description] => PJTR Laser UHD [model_status] => Active [available_qty] => 4 [updated] => 2025-06-18 [seaStockTotal] => 0 [seaStockW1] => 0 [seaStockW2] => 0 [seaStockW3] => 0 [seaStockW4] => 0 [seaStockW5] => 0 [product_level4] => TVPRPLNU [on_hand] => 4 [on_hand_cbm] => 0.04968 )
				*/
				
				$by_org[$item->org][$y][$m][$d]["Total"]["actual"] += $item->on_hand_cbm;
				$by_org[$item->org][$y][$m][$d][$item->dash_company]["actual"] += $item->on_hand_cbm;
				
				if ($item->org === "N4M"){//APM	
					$by_3pl["APM"][$y][$m][$d]["Total"]["actual"] += $item->on_hand_cbm;
					$by_3pl["APM"][$y][$m][$d][$item->dash_company]["actual"] += $item->on_hand_cbm;
				}elseif ($item->org === "N4E"){//APM
					$by_3pl["APM"][$y][$m][$d]["Total"]["actual"] += $item->on_hand_cbm;
					$by_3pl["APM"][$y][$m][$d][$item->dash_company]["actual"] += $item->on_hand_cbm;
				}else{//KLO
					$by_3pl["KLO"][$y][$m][$d]["Total"]["actual"] += $item->on_hand_cbm;
					$by_3pl["KLO"][$y][$m][$d][$item->dash_company]["actual"] += $item->on_hand_cbm;
				}
			}
		}
		
		$from_t = strtotime($from);
		$to_t = strtotime($to);
		
		//normal arrival to warehouse is 2 days after eta => sum to current CBM
		foreach($arrivals as $item){
			if ($item->eta){
				//echo $item->eta." ... ".$item->organization." ... ".$item->company." ... ".$item->cbm." cbm<br/>";
				
				$item->ref_date = date("Y-m-d", strtotime("+2 day", strtotime($item->eta)));
				if ($item->company) if (($from_t <= strtotime($item->ref_date)) and (strtotime($item->ref_date) <= $to_t) and ($item->cbm < 500)){
					//echo $item->organization." /// ".$item->ref_date." /// ".$item->cbm."<br/>";
					//print_r($item); echo "<br/><br/>";
					
					if (!array_key_exists($item->organization, $by_org)) $by_org[$item->organization] = $arr_basic;
					
					$y = date("Y", strtotime($item->ref_date));
					$m = date("m", strtotime($item->ref_date));
					$d = date("d", strtotime($item->ref_date));
					
					$by_org[$item->organization][$y][$m][$d]["Total"]["arrival"] += $item->cbm;
					$by_org[$item->organization][$y][$m][$d][$item->company]["arrival"] += $item->cbm;
					
					if ($item->organization === "N4M"){//APM	
						$by_3pl["APM"][$y][$m][$d]["Total"]["arrival"] += $item->cbm;
						$by_3pl["APM"][$y][$m][$d][$item->company]["arrival"] += $item->cbm;
					}elseif ($item->organization === "N4E"){//APM
						$by_3pl["APM"][$y][$m][$d]["Total"]["arrival"] += $item->cbm;
						$by_3pl["APM"][$y][$m][$d][$item->company]["arrival"] += $item->cbm;
					}else{//KLO
						$by_3pl["KLO"][$y][$m][$d]["Total"]["arrival"] += $item->cbm;
						$by_3pl["KLO"][$y][$m][$d][$item->company]["arrival"] += $item->cbm;
					}
				}
			}
		}
		
		//sales cbm is negative volumes
		foreach($sales as $item){
			$item->ref_date = $item->booked_date;
			if ($item->booked_date) $item->ref_date = $item->booked_date;
			if ($item->req_arrival_date_to) $item->ref_date = $item->req_arrival_date_to;
			if ($item->appointment_date) $item->ref_date = $item->appointment_date;
			if ($item->shipment_date) $item->ref_date = $item->shipment_date;
			
			
			if ($item->dash_company) if (($from_t <= strtotime($item->ref_date)) and (strtotime($item->ref_date) <= $to_t)){
				//echo $item->ref_date." /// ".$item->inventory_org." /// ".$item->cbm."<br/>";
				//print_r($item); echo "<br/><br/>";
			
				if (!array_key_exists($item->inventory_org, $by_org)) $by_org[$item->inventory_org] = $arr_basic;
				
				$y = date("Y", strtotime($item->ref_date));
				$m = date("m", strtotime($item->ref_date));
				$d = date("d", strtotime($item->ref_date));
			
				$by_org[$item->inventory_org][$y][$m][$d]["Total"]["sales"] -= $item->cbm;
				$by_org[$item->inventory_org][$y][$m][$d][$item->dash_company]["sales"] -= $item->cbm;
				
				if ($item->inventory_org === "N4M"){//APM	
					$by_3pl["APM"][$y][$m][$d]["Total"]["sales"] -= $item->cbm;
					$by_3pl["APM"][$y][$m][$d][$item->dash_company]["sales"] -= $item->cbm;
				}elseif ($item->inventory_org === "N4E"){//APM
					$by_3pl["APM"][$y][$m][$d]["Total"]["sales"] -= $item->cbm;
					$by_3pl["APM"][$y][$m][$d][$item->dash_company]["sales"] -= $item->cbm;
				}else{//KLO
					$by_3pl["KLO"][$y][$m][$d]["Total"]["sales"] -= $item->cbm;
					$by_3pl["KLO"][$y][$m][$d][$item->dash_company]["sales"] -= $item->cbm;
				}
			}
		}
		
		foreach($by_org as $org => $years){
			//echo "Org: ".$org."<br/>";
			$progress_hs = $progress_ms = $progress_es = $progress_total = 0;
			
			foreach($years as $year => $months){
				//echo "Year: ".$year."<br/>";
				foreach($months as $month => $days){
					//echo "Month: ".$month."<br/>";
					foreach($days as $day => $divisions){
						//echo "Day: ".$day."<br/>";
						
						$progress_hs = $progress_hs + $divisions["HS"]["actual"] + $divisions["HS"]["arrival"] + $divisions["HS"]["sales"];
						$progress_ms = $progress_ms + $divisions["MS"]["actual"] + $divisions["MS"]["arrival"] + $divisions["MS"]["sales"];
						$progress_es = $progress_es + $divisions["ES"]["actual"] + $divisions["ES"]["arrival"] + $divisions["ES"]["sales"];
						$progress_total = $progress_total + $divisions["Total"]["actual"] + $divisions["Total"]["arrival"] + $divisions["Total"]["sales"];
						
						$by_org[$org][$year][$month][$day]["HS"]["progress"] = $progress_hs;
						$by_org[$org][$year][$month][$day]["MS"]["progress"] = $progress_ms;
						$by_org[$org][$year][$month][$day]["ES"]["progress"] = $progress_es;
						$by_org[$org][$year][$month][$day]["Total"]["progress"] = $progress_total;
					}
				}
			}
		}
		
		foreach($by_3pl as $ware => $years){
			//echo "Org: ".$org."<br/>";
			$progress_hs = $progress_ms = $progress_es = $progress_total = 0;
			
			foreach($years as $year => $months){
				//echo "Year: ".$year."<br/>";
				foreach($months as $month => $days){
					//echo "Month: ".$month."<br/>";
					foreach($days as $day => $divisions){
						//echo "Day: ".$day."<br/>";
						
						$progress_hs = $progress_hs + $divisions["HS"]["actual"] + $divisions["HS"]["arrival"] + $divisions["HS"]["sales"];
						$progress_ms = $progress_ms + $divisions["MS"]["actual"] + $divisions["MS"]["arrival"] + $divisions["MS"]["sales"];
						$progress_es = $progress_es + $divisions["ES"]["actual"] + $divisions["ES"]["arrival"] + $divisions["ES"]["sales"];
						$progress_total = $progress_total + $divisions["Total"]["actual"] + $divisions["Total"]["arrival"] + $divisions["Total"]["sales"];
						
						$by_3pl[$ware][$year][$month][$day]["HS"]["progress"] = $progress_hs;
						$by_3pl[$ware][$year][$month][$day]["MS"]["progress"] = $progress_ms;
						$by_3pl[$ware][$year][$month][$day]["ES"]["progress"] = $progress_es;
						$by_3pl[$ware][$year][$month][$day]["Total"]["progress"] = $progress_total;
					}
				}
			}
		}
		
		/*
		echo "<br/><br/><br/><br/>";
		foreach($by_org as $org => $item){
			echo $org." ............ "; print_r($item); echo "<br/><br/>";
		}
		
		return;
		*/
		
		$data["from"] = $from;
		$data["to"] = $to;
		$data["by_org"] = $by_org;
		$data["by_3pl"] = $by_3pl;
		$data["overflow"] = "scroll";
		$data["main"] = "lgepr/warehouse_cbm";
		
		$this->load->view('layout_dashboard', $data);
	}
}
