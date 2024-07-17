<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
		$this->exchange_rate = round($this->my_func->get_exchange_rate_month_ttm(date("Y-m-d")), 2);
		
		$this->divisions = ["HA", "HE", "BS"];
		$this->division_map = [
			"HA" => ["REF", "COOK", "W/M", "CDT", "RAC", "SAC", "A/C"],
			"HE" => ["TV", "AV"],
			"BS" => ["MNT", "PC", "DS", "SGN", "CTV"],
		];
		$this->division_map_inv = [];
		foreach($this->division_map as $div => $divisions) foreach($divisions as $cat) $this->division_map_inv[$cat] = $div;
		
		$this->categories = ["REF", "COOK", "W/M", "CDT", "A/C", "RAC", "SAC", "TV", "AV", "MNT", "PC", "DS", "SGN", "CTV"];
		$this->category_map = [
			"REF" => ["REF"],
			"COOK" => ["MWO", "O", "CVT"],
			"W/M" => ["W/M"],
			"CDT" => ["CDT"],
			"A/C" => ["A/C"],
			"RAC" => ["RAC"],
			"SAC" => ["SAC"],
			"TV" => ["LCD", "LTV"],
			"AV" => ["AUD", "CAV"],
			"MNT" => ["MNT"],
			"PC" => ["PC"],
			"DS" => ["DS"],
			"SGN" => ["SGN"],
			"CTV" => ["CTV"],
		];
		$this->category_map_inv = [];
		foreach($this->category_map as $cat => $categories) foreach($categories as $c) $this->category_map_inv[$c] = $cat;
		
		$this->dash_company = ["HA" => "H&A", "HE" => "HE", "BS" => "BS"];
		$this->dash_division = [
			"REF" => "REF", 
			"COOK" => "Cooking", 
			"W/M" => "W/M", 
			"CDT" => "CDT", 
			"A/C" => "Chiller", 
			"RAC" => "RAC", 
			"SAC" => "SAC", 
			"TV" => "LTV", 
			"AV" => "AV", 
			"MNT" => "MNT", 
			"PC" => "PC", 
			"DS" => "DS", 
			"SGN" => "Signage", 
			"CTV" => "Commercial TV",
		];
	}
	
	private function get_dates_by_week($week, $year){
		$dateTime = new DateTime();
		$dateTime->setISODate($year, $week);//1 week: from monday ~ sunday
		
		//need from sunday ~ saturday
		$startDate = date("Y-m-d", strtotime("-1 day", strtotime($dateTime->format('Y-m-d'))));
		if ((string)$year !== date("Y", strtotime($startDate))) $startDate = $year."-01-01";
		
		$dateTime->modify('+6 days');//add one week in days
		$endDate = date("Y-m-d", strtotime("-1 day", strtotime($dateTime->format('Y-m-d'))));
		if ((string)$year !== date("Y", strtotime($endDate))) $endDate = $year."-12-31";
		
		return (($startDate === $year."-01-01") and ($endDate === $year."-12-31")) ? null : [$startDate, $endDate];
	}
	
	private function get_week_by_date($date){
		$year = date("Y", strtotime($date));
		$week = 1;
		
		while (true){
			$res = $this->get_dates_by_week($week, $year);
			if (strtotime($res[1]) < strtotime($date)) $week++; else break;
		}
		
		return ["week" => $week, "dates" => $res];
	}
	
	private function get_weeks_by_year($year){
		$weeks = [];
		$i = 0;
		while(true){
			$i++;
			$res = $this->get_dates_by_week($i, 2024);
			
			if ($res) $weeks[] = ["week" => $i, "dates" => $res]; else break;
		}
		
		return $weeks;
	}
	
	private function data_cleansing($datas, $is_total = true){
		uasort($datas, function($a, $b) { return $a["amount"] < $b["amount"]; });
		foreach($datas as $i => $item){
			if ($datas[$i]["amount"]){
				if ($is_total) $datas[$i]["per"] = round($datas[$i]["amount"] / $datas["total"]["amount"] * 100, 2);
			}else unset($datas[$i]);
		}
		
		return $datas;
	}
	
	private function get_statistics($gerps, $from, $to){
		$devices = ["total" => ["device" => "Total", "qty" => 0, "amount" => 0]];
		$devices_rec = $this->gen_m->only("obs_magento", "devices", ["devices !=" => ""]);
		foreach($devices_rec as $d) $devices[$d->devices] = ["device" => $d->devices, "qty" => 0, "amount" => 0];
		
		$cus_group = ["total" => ["customer_group" => "Total", "qty" => 0, "amount" => 0]];
		$cus_group_rec = $this->gen_m->only("obs_magento", "customer_group", ["customer_group !=" => ""]);
		foreach($cus_group_rec as $c) $cus_group[$c->customer_group] = ["customer_group" => $c->customer_group, "qty" => 0, "amount" => 0];
		
		$d2b2c = ["total" => ["company" => "Total", "qty" => 0, "amount" => 0]];
		$d2b2c_rec = $this->gen_m->only("obs_magento", "company_name_through_vipkey", ["company_name_through_vipkey !=" => ""]);
		foreach($d2b2c_rec as $c) $d2b2c[trim($c->company_name_through_vipkey)] = ["company" => trim($c->company_name_through_vipkey), "qty" => 0, "amount" => 0];
		
		$cupons = ["total" => ["code" => "Total", "rule" => "", "qty" => 0, "amount" => 0]];
		$cupons_rec = $this->gen_m->only_multi("obs_magento", ["coupon_code", "coupon_rule"], ["coupon_code !=" => ""]);
		foreach($cupons_rec as $c) $cupons[$c->coupon_code] = ["code" => $c->coupon_code, "rule" => $c->coupon_rule, "qty" => 0, "amount" => 0];
		
		$departments = ["total" => ["department" => "Total", "qty" => 0, "amount" => 0, "provinces" => []]];
		$departments_rec = $this->gen_m->only("obs_magento", "department", ["department !=" => ""]);
		foreach($departments_rec as $d) if ($d->department) $departments[$d->department] = ["department" => $d->department, "qty" => 0, "amount" => 0, "provinces" => []];

		$provinces = ["total" => ["department" => "Total", "province" => "", "qty" => 0, "amount" => 0]];
		$provinces_rec = $this->gen_m->only_multi("obs_magento", ["department", "province"], ["province !=" => ""]);
		foreach($provinces_rec as $z) if ($z->province) $provinces[$z->department."_".$z->province] = ["department" => $z->department, "province" => $z->province, "qty" => 0, "amount" => 0];
		
		$models = [];
		$models_rec = $this->gen_m->only_multi("obs_gerp_sales_order", ["product_level1_name", "product_level4_name", "model_category", "model"]);
		foreach($models_rec as $m) if ($m->model_category){
			$div = $this->category_map_inv[$m->model_category];
			$com = $this->division_map_inv[$div];
			
			$models[$m->model] = [
				"company" => $com, 
				"division" => $div, 
				"model_category" => $m->model_category, 
				"product_level1_name" => $m->product_level1_name, 
				"product_level4_name" => $m->product_level4_name, 
				"model" => $m->model, 
				"qty" => 0, 
				"amount" => 0];
		}
		
		$days = [];
		$purchase = [];
		$closed = [];
		$dates_between = $this->my_func->dates_between($from, $to);
		foreach($dates_between as $item){
			$day = date("d", strtotime($item));
			$days[] = $day;
			
			$purchase[$day] = [
				4 => ["qty" => 0, "amount" => 0],
				8 => ["qty" => 0, "amount" => 0],
				12 => ["qty" => 0, "amount" => 0],
				16 => ["qty" => 0, "amount" => 0],
				20 => ["qty" => 0, "amount" => 0],
				24 => ["qty" => 0, "amount" => 0],
			];
			
			$closed[date("d", strtotime($item))] = ["qty" => 0, "amount" => 0];
		}
		
		//foreach($models as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		
		foreach($gerps as $item){
			//device
			if ($item->devices){
				$devices[$item->devices]["qty"]++;
				$devices[$item->devices]["amount"] += $item->sales_amount_usd;
				$devices["total"]["qty"]++;
				$devices["total"]["amount"] += $item->sales_amount_usd;	
			}//else echo "No devices<br/><br/>";
			
			//cus_group
			if ($item->customer_group){
				$cus_group[$item->customer_group]["qty"]++;
				$cus_group[$item->customer_group]["amount"] += $item->sales_amount_usd;
				$cus_group["total"]["qty"]++;
				$cus_group["total"]["amount"] += $item->sales_amount_usd;	
			}
			
			//d2b2c
			if ($item->company_name_through_vipkey){
				$item->company_name_through_vipkey = trim($item->company_name_through_vipkey);
				$d2b2c[$item->company_name_through_vipkey]["qty"]++;
				$d2b2c[$item->company_name_through_vipkey]["amount"] += $item->sales_amount_usd;
				$d2b2c["total"]["qty"]++;
				$d2b2c["total"]["amount"] += $item->sales_amount_usd;	
			}
			
			//cupons
			if ($item->coupon_code){
				$cupons[$item->coupon_code]["qty"]++;
				$cupons[$item->coupon_code]["amount"] += $item->sales_amount_usd;
				$cupons["total"]["qty"]++;
				$cupons["total"]["amount"] += $item->sales_amount_usd;	
			}
			
			//departments
			if ($item->department and $item->province){
				$departments[$item->department]["qty"]++;
				$departments[$item->department]["amount"] += $item->sales_amount_usd;
				$departments["total"]["qty"]++;
				$departments["total"]["amount"] += $item->sales_amount_usd;
			}
			
			//provinces
			if ($item->province){
				$provinces[$item->department."_".$item->province]["qty"]++;
				$provinces[$item->department."_".$item->province]["amount"] += $item->sales_amount_usd;
				$provinces["total"]["qty"]++;
				$provinces["total"]["amount"] += $item->sales_amount_usd;	
			}
			
			//model
			if ($item->model_category){
				$models[$item->model]["qty"]++;
				$models[$item->model]["amount"] += $item->sales_amount_usd;
			}
			
			//purchase time
			$limit_min = strtotime(date("Y-m-01 00:00:00", strtotime($from)));
			$limit_max = strtotime(date("Y-m-t 23:59:59", strtotime($to)));
			
			$local_time = strtotime($item->local_time);
			$closed_time = strtotime($item->close_date);
			
			if (($item->sales_amount_usd > 0) and ($limit_min <= $local_time) and ($local_time <= $limit_max)){
				$day_i = date("d", strtotime($item->local_time));
				$hour_i = (((int)(date("H", strtotime($item->local_time)) / 4) + 1) * 4);
				
				$purchase[$day_i][$hour_i]["qty"]++;
				$purchase[$day_i][$hour_i]["amount"] += $item->sales_amount_usd / 1000;
			}
			
			//closed date
			if (($item->sales_amount_usd > 0) and ($limit_min <= $closed_time) and ($closed_time <= $limit_max)){
				$day_i = date("d", strtotime($item->close_date));
				
				$closed[$day_i]["qty"]++;
				$closed[$day_i]["amount"] += $item->sales_amount_usd / 1000;
			}
			
			/*
			if ($item->order_no){
			//if (!$item->department and !$item->province){
				echo $item->order_no." /// ".$item->local_time." /// ".$item->line_status."<br/>";
				print_r($item); echo "<br/><br/>";	
			}
			*/
		}
		
		//data cleansing
		$devices = $this->data_cleansing($devices);
		$cus_group = $this->data_cleansing($cus_group);
		$d2b2c = $this->data_cleansing($d2b2c);
		$cupons = $this->data_cleansing($cupons);
		$departments = $this->data_cleansing($departments);
		$provinces = $this->data_cleansing($provinces);
		$models = $this->data_cleansing($models, false);
		
		//inserting province to departments
		unset($provinces["total"]);
		foreach($provinces as $item){
			$departments[$item["department"]]["provinces"][] = $item;
		}
		
		/*
		foreach($devices as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($cus_group as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($d2b2c as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($cupons as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($departments as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($provinces as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($models as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($purchase as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		foreach($closed as $i => $item){echo $i." >>>> "; print_r($item); echo "<br/>";} echo "<br/><br/><br/>";
		*/
		
		$purchase_qty = [4 => [], 8 => [], 12 => [], 16 => [], 20 => [], 24 => [], "total" => []];
		$purchase_amount = [4 => [], 8 => [], 12 => [], 16 => [], 20 => [], 24 => [], "total" => []];
		
		foreach($purchase as $i => $item){
			$qty = $amount = 0;
			
			foreach($item as $hr => $val){
				//echo $i." >>>>>> "; echo $hr." >>>>>> "; print_r($val); echo "<br/><br/>";
				
				$qty += $val["qty"];
				$amount += $val["amount"];
				
				$purchase_qty[$hr][] = $val["qty"] ? $val["qty"] : null;
				$purchase_amount[$hr][] = $val["amount"] ? round($val["amount"], 2) : null;
				
			}
			
			$purchase_qty["total"][] = $qty ? $qty : 0;
			$purchase_amount["total"][] = $amount ? round($amount, 2) : 0;
		}
		
		$closed_qty = [];
		$closed_amount = [];
		
		foreach($closed as $i => $item){
			//echo $i." >>>>>> "; print_r($item); echo "<br/><br/>";
			
			$closed_qty[] = $item["qty"] ? $item["qty"] : 0;
			$closed_amount[] = $item["amount"] ? round($item["amount"], 2) : 0;
		}
		
		//print_r($purchase_qty);
		//echo "<br/><br/><br/>";
		//print_r($purchase_amount);
		
		return [
			"devices" => $devices,
			"cus_group" => $cus_group,
			"d2b2c" => $d2b2c,
			"cupons" => $cupons,
			"models" => $models,
			"departments" => $departments,
			"days" => $days,
			"purchase" => $purchase,
			"purchase_qty" => $purchase_qty,
			"purchase_amount" => $purchase_amount,
			"closed" => $closed,
			"closed_qty" => $closed_qty,
			"closed_amount" => $closed_amount,
			"dates_between" => $dates_between,
		];
	}
	
	private function get_dashboard($gerps, $from, $to){
		//structure setting
		$dash = [];
		//echo $from." ".$to."<br/>";
		$subsidiaries = $this->gen_m->only("obs_gerp_sales_order", "customer_department", ["create_date >=" => date("Y-01-01", strtotime($from)), "create_date <=" => date("Y-12-t", strtotime($to))]);
		foreach($subsidiaries as $sub){
			$dash[$sub->customer_department] = ["sub" => $sub->customer_department, "div" => "", "cat" => "", "target" => 0, "target_per" => 0, "target_color" => 0, "ml" => 0, "ml_actual" => 0, "ml_per" => 0, "ml_color" => 0, "closed" => 0, "closed_per" => 0, "closed_color" => "", "m-1" => 0, "reserved" => 0];
			foreach($this->divisions as $div){
				$dash[$sub->customer_department."_".$div] = ["sub" => "", "div" => $div, "cat" => "", "target" => 0, "target_per" => 0, "target_color" => 0, "ml" => 0, "ml_actual" => 0, "ml_per" => 0, "ml_color" => 0, "closed" => 0, "closed_per" => 0, "closed_color" => "", "m-1" => 0, "reserved" => 0];
				
				$categories = $this->division_map[$div];
				foreach($categories as $cat){
					$dash[$sub->customer_department."_".$div."_".$cat] = ["sub" => "", "div" => "", "cat" => $cat, "target" => 0, "target_per" => 0, "target_color" => 0, "ml" => 0, "ml_actual" => 0, "ml_per" => 0, "ml_color" => 0, "closed" => 0, "closed_per" => 0, "closed_color" => "", "m-1" => 0, "reserved" => 0];
				}
			}
		}
		
		//print_r($dash); echo "<br/><br/>";
		
		$div_map = $this->division_map_inv;
		$cat_map = $this->category_map_inv;
		
		foreach($gerps as $item){
			if ($item->model_category){
				$cat = $cat_map[$item->model_category];
				$div = $div_map[$cat];
				$sub = $item->customer_department;
				
				$amount = $item->sales_amount_usd / 1000;
				
				switch($item->delivery){
					case "M-1": 
						$dash[$sub]["m-1"] += $amount;
						$dash[$sub."_".$div]["m-1"] += $amount;
						$dash[$sub."_".$div."_".$cat]["m-1"] += $amount;
						
						$dash[$sub]["closed"] += $amount;
						$dash[$sub."_".$div]["closed"] += $amount;
						$dash[$sub."_".$div."_".$cat]["closed"] += $amount;
						break;
					case "M": 
						$dash[$sub]["closed"] += $amount;
						$dash[$sub."_".$div]["closed"] += $amount;
						$dash[$sub."_".$div."_".$cat]["closed"] += $amount;
						break;
					case "M+1": 
						$dash[$sub]["reserved"] += $amount;
						$dash[$sub."_".$div]["reserved"] += $amount;
						$dash[$sub."_".$div."_".$cat]["reserved"] += $amount;
						break;
				}
			}
		}
		
		//ML Setting
		$ml_arr = [];
		
		$from_time = strtotime($from);
		$mls = $this->gen_m->filter("obs_most_likely", false, ["year" => date("Y", $from_time), "month" => date("m", $from_time)]);
		foreach($mls as $ml){
			//set ml_arr key
			$aux = [];
			if ($ml->subsidiary) $aux[] = $ml->subsidiary;
			if ($ml->division) $aux[] = $ml->division;
			if ($ml->category) $aux[] = $ml->category;
			
			//assign
			if ($aux){
				$key = implode("_", $aux); //echo $key."<br/>";
				$dash[$key]["target"] = $ml->target / 1000;
				$dash[$key]["target_per"] = $dash[$key]["target"] > 0 ? $dash[$key]["closed"] / $dash[$key]["target"] * 100 : 0;
				$dash[$key]["target_color"] = $dash[$key]["target_per"] >= 100 ? "success" : "danger";
				$dash[$key]["ml"] = $ml->ml / 1000;
				$dash[$key]["ml_actual"] = $ml->ml_actual / 1000;
				$dash[$key]["ml_per"] = $dash[$key]["ml_actual"] > 0 ? $dash[$key]["closed"] / $dash[$key]["ml_actual"] * 100 : 0;
				$dash[$key]["ml_color"] = $dash[$key]["ml_per"] >= 100 ? "success" : "danger";
			}
		}
		
		//echo $from." ~ ".$to."<br/>"; print_r($dash); echo "<br/><br/>";
		//foreach($dash as $key => $d){print_r($d); echo " =====> ".$key."<br/><br/>";}
		
		return $dash;
	}
	
	public function index(){
		$months = [];
		$year_act = date("Y");
		$month_act = date("m");
		for($i = 12; $i >= 1; $i--){
			if ($i <= $month_act) $months[] = $year_act."-".str_pad($i, 2, '0', STR_PAD_LEFT);
		}
		
		//set date range
		if ($this->input->get("w")){//week selected
			$dates = $this->get_dates_by_week($this->input->get("w"), date("Y"));
			$from = $dates[0];
			$to = $dates[1];
		}elseif ($this->input->get("m")){//month selected
			$m = strtotime($this->input->get("m"));
			$from = date("Y-m-01", $m);
			$to = date("Y-m-t", $m);
		}else{//default: this month
			//$aux = $this->get_week_by_date(date("Y-m-d"));
			$from = date("Y-m-01");
			$to = date("Y-m-t");
		}
		
		$this->exchange_rate = round($this->my_func->get_exchange_rate_month_ttm($to), 2);
		
		//get gerp records based on IOD
		$gerps = $this->my_func->get_gerp_iod($from, $to);
		
		$data = [
			"months"		=> $months,
			"from"			=> $from,
			"to"			=> $to,
			"gerps" 		=> $gerps,
			"dashboard" 	=> $this->get_dashboard($gerps, $from, $to),
			"statistics" 	=> $this->get_statistics($gerps, $from, $to),
			"main" 			=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}

	public function progress($qty = 12){
		$today = date("Y-m-d");
		
		$headers = $dates = $dashs = [];
		
		for($i = 0; $i < 24; $i++){
			$now = date("Y-m-d", strtotime("-".($i)." month", strtotime($today)));
			$from = date("Y-m-01", strtotime($now));
			$to = date("Y-m-t", strtotime($now));
			
			//$headers[] = date("M", strtotime($from)) === "Jan" ? date("M y", strtotime($from)) : date("M", strtotime($from));
			$headers[] = date("M y", strtotime($from));
			$dates[] = [$from, $to];
			
			if ($i >= $qty) break;
		}
		
		$headers = array_reverse($headers);
		$dates = array_reverse($dates);
		
		foreach($dates as $d){
			$gerps = $this->my_func->get_gerp_iod($d[0], $d[1]);
			$dashs[] = $this->get_dashboard($gerps, $d[0], $d[1]);
		}
		
		$chart_target = $chart_ml = $chart_closed = [];
		foreach($dashs as $i => $dash){
			$chart_target[] = round($dash["LGEPR"]["target"], 2);
			$chart_ml[] = round($dash["LGEPR"]["ml_actual"], 2);
			$chart_closed[] = round($dash["LGEPR"]["closed"], 2);
		}
		
		/* data print 
		print_r($headers); echo "<br/><br/>";
		print_r($dates); echo "<br/><br/>";
		
		foreach($dashs as $i => $dash){
			print_r($dates[$i]); echo "<br/>";
			foreach($dash as $d){
				print_r($d); echo "<br/>";
			}
			echo "<br/>";
		} echo "<br/>";
		
		print_r($chart_target); echo "<br/><br/>";
		print_r($chart_ml); echo "<br/><br/>";
		print_r($chart_closed); echo "<br/><br/>";
		*/
		
		$data = [
			"headers" 		=> $headers,
			"dates" 		=> $dates,
			"dashs" 		=> $dashs,
			"chart_target"	=> $chart_target,
			"chart_ml" 		=> $chart_ml,
			"chart_closed" 	=> $chart_closed,
			"main" 			=> "module/obs_report/progress",
		];
		
		$this->load->view('layout', $data);
	}

	public function test(){
		$from = date("Y-m-01");
		$to = date("Y-m-t");
			
		$gerps = $this->my_func->get_gerp_iod($from, $to);
		
		$statistics = $this->get_statistics($gerps, $from, $to);
		
		print_r($statistics["closed_amount"]);
	}
}
