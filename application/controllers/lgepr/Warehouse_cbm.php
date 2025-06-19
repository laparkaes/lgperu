<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_cbm extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}

	public function index(){
		
		$detail = ["arrival" => 0, "sales" => 0, "actual" => 0];
		
		$row = [
			"HS" => $detail,
			"MS" => $detail,
			"ES" => $detail,
			"Total" => $detail,
		];
		
		$cbm_matrix = []; //arr[month][day][org][row]
		
		$inventory = $this->gen_m->filter("lgepr_stock", false, ["on_hand_cbm >" => 0]);
		foreach($inventory as $item){
			//print_r($item); echo "<br/><br/>";
			
			if (!array_key_exists($item->org, $cbm_matrix)) $cbm_matrix[$item->org] = [];
			
			$year = date("Y", strtotime($item->updated));
			if (!array_key_exists($year, $cbm_matrix[$item->org])) $cbm_matrix[$item->org][$year] = [];
			
			$month = date("m", strtotime($item->updated));
			if (!array_key_exists($month, $cbm_matrix[$item->org][$year])) $cbm_matrix[$item->org][$year][$month] = [];
			
			$day = date("d", strtotime($item->updated));
			if (!array_key_exists($day, $cbm_matrix[$item->org][$year][$month])) $cbm_matrix[$item->org][$year][$month][$day] = $row;
			
			if ($item->dash_company){
				/*
				Note!! projector line doesn't have company and division
				
				Examples)
				
				stdClass Object ( [stock_id] => 1480 [org] => N4M [dash_company] => [dash_division] => [sub_inventory] => GS-NA-IN [grade] => NONE [model_category_code] => PRJ [model_Category_name] => PROJECTOR [model] => SC-80.PRO [model_description] => Projection Screen [model_status] => Active [available_qty] => 138 [updated] => 2025-06-18 [seaStockTotal] => 0 [seaStockW1] => 0 [seaStockW2] => 0 [seaStockW3] => 0 [seaStockW4] => 0 [seaStockW5] => 0 [product_level4] => TVACPRNN [on_hand] => 138 [on_hand_cbm] => 3.37631 )
				
				stdClass Object ( [stock_id] => 2455 [org] => N4S [dash_company] => [dash_division] => [sub_inventory] => GOODSET-OB [grade] => GOOD [model_category_code] => PRJ [model_Category_name] => PROJECTOR [model] => HU710PB.AWF [model_description] => PJTR Laser UHD [model_status] => Active [available_qty] => 4 [updated] => 2025-06-18 [seaStockTotal] => 0 [seaStockW1] => 0 [seaStockW2] => 0 [seaStockW3] => 0 [seaStockW4] => 0 [seaStockW5] => 0 [product_level4] => TVPRPLNU [on_hand] => 4 [on_hand_cbm] => 0.04968 )
				*/
				$cbm_matrix[$item->org][$year][$month][$day]["Total"]["actual"] += $item->on_hand_cbm;
				$cbm_matrix[$item->org][$year][$month][$day][$item->dash_company]["actual"] += $item->on_hand_cbm;	
			}
		}
		
		/*
		Logic: normal arrival to warehouse is 2 days after eta => sum to current CBM
		*/
		$w = ["eta >=" => date("Y-m-d", strtotime("-2 day", strtotime($year."-".$month."-".$day)))];
		$arrivals = $this->gen_m->filter("lgepr_container", false, $w, null, null, [["eta", "asc"]]);
		foreach($arrivals as $item){
			//print_r($item); echo "<br/><br/>";
			
			if ($item->eta){
				echo $item->eta." ... ".$item->organization." ... ".$item->company." ... ".$item->cbm." cbm<br/>";
				
				$item->ref_date = date("Y-m-d", strtotime("+2 day", strtotime($item->eta)));
				
				if (!array_key_exists($item->organization, $cbm_matrix)) $cbm_matrix[$item->organization] = [];
				
				$year = date("Y", strtotime($item->ref_date));
				if (!array_key_exists($year, $cbm_matrix[$item->organization])) $cbm_matrix[$item->organization][$year] = [];
				
				$month = date("m", strtotime($item->ref_date));
				if (!array_key_exists($month, $cbm_matrix[$item->organization][$year])) $cbm_matrix[$item->organization][$year][$month] = [];
				
				$day = date("d", strtotime($item->ref_date));
				if (!array_key_exists($day, $cbm_matrix[$item->organization][$year][$month])) $cbm_matrix[$item->organization][$year][$month][$day] = $row;
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
