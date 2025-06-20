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
		$arr_detail = ["arrival" => 0, "sales" => 0, "actual" => 0];
		
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
		
		echo $from." ".$to."<br/><br/><br/><br/><br/><br/>";
		//print_r($arr_basic); echo "<br/><br/><br/><br/>";
		
		$by_org = [];
		
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
			}
		}
		
		foreach($by_org as $org => $div){
			echo $org."<br/>";
			print_r($div);
			echo "<br/><br/>";
		}
		echo "<br/><br/><br/><br/>";
		
		//normal arrival to warehouse is 2 days after eta => sum to current CBM
		foreach($arrivals as $item){
			//print_r($item); echo "<br/><br/>";
			
			if ($item->eta){
				//echo $item->eta." ... ".$item->organization." ... ".$item->company." ... ".$item->cbm." cbm<br/>";
				
				$item->ref_date = date("Y-m-d", strtotime("+2 day", strtotime($item->eta)));
				
				if (!array_key_exists($item->organization, $cbm_matrix)) $cbm_matrix[$item->organization] = [];
				
				$year = date("Y", strtotime($item->ref_date));
				if (!array_key_exists($year, $cbm_matrix[$item->organization])) $cbm_matrix[$item->organization][$year] = [];
				
				$month = date("m", strtotime($item->ref_date));
				if (!array_key_exists($month, $cbm_matrix[$item->organization][$year])) $cbm_matrix[$item->organization][$year][$month] = [];
				
				$day = date("d", strtotime($item->ref_date));
				if (!array_key_exists($day, $cbm_matrix[$item->organization][$year][$month])) $cbm_matrix[$item->organization][$year][$month][$day] = $row;
				
				$cbm_matrix[$item->organization][$year][$month][$day]["Total"]["arrival"] += $item->cbm;
				$cbm_matrix[$item->organization][$year][$month][$day][$item->company]["arrival"] += $item->cbm;	
			}
			
		}
		
		
		
		
		
		echo "<br/><br/><br/><br/>";
		foreach($cbm_matrix as $org => $item){
			echo $org." ............ "; print_r($item); echo "<br/><br/>";
		}
		
		return;
		
		$data["overflow"] = "scroll";
		$data["main"] = "lgepr/warehouse_cbm";
		
		$this->load->view('layout_dashboard', $data);
	}
}
