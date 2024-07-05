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
			"HA" => ["REF", "COOK", "W/M", "RAC", "SAC", "A/C"],
			"HE" => ["TV", "AV"],
			"BS" => ["MNT", "PC", "DS", "SGN", "CTV"],
		];
		$this->division_map_inv = [];
		foreach($this->division_map as $div => $divisions) foreach($divisions as $cat) $this->division_map_inv[$cat] = $div;
		
		$this->categories = ["REF", "COOK", "W/M", "A/C", "RAC", "SAC", "TV", "AV", "MNT", "PC", "DS", "SGN", "CTV"];
		$this->category_map = [
			"REF" => ["REF"],
			"COOK" => ["MWO", "O", "CVT"],
			"W/M" => ["W/M"],
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
	}
	
	function get_dates_by_week($week, $year){
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
	
	function get_week_by_date($date){
		$year = date("Y", strtotime($date));
		$week = 1;
		
		while (true){
			$res = $this->get_dates_by_week($week, $year);
			if (strtotime($res[1]) < strtotime($date)) $week++; else break;
		}
		
		return ["week" => $week, "dates" => $res];
	}
	
	function get_weeks_by_year($year){
		$weeks = [];
		$i = 0;
		while(true){
			$i++;
			$res = $this->get_dates_by_week($i, 2024);
			
			if ($res) $weeks[] = ["week" => $i, "dates" => $res]; else break;
		}
		
		return $weeks;
	}
	
	private function get_dashboard($gerps, $from, $to, $exchange_rate){
		//structure setting
		$dash = [];
		//echo $from." ".$to."<br/>";
		$subsidiaries = $this->gen_m->only("obs_gerp_sales_order", "customer_department", ["create_date >=" => date("Y-01-01", strtotime($from)), "create_date <=" => date("Y-12-t", strtotime($to))]);
		foreach($subsidiaries as $sub){
			$dash[$sub->customer_department] = ["sub" => $sub->customer_department, "div" => "", "cat" => "", "monthly_report" => 0, "ml" => 0, "ml_actual" => 0, "projection" => 0, "projection_per" => 0, "projection_color" => "", "actual" => 0, "actual_per" => 0, "actual_color" => "", "expected" => 0];
			foreach($this->divisions as $div){
				$dash[$sub->customer_department."_".$div] = ["sub" => "", "div" => $div, "cat" => "", "monthly_report" => 0, "ml" => 0, "ml_actual" => 0, "projection" => 0, "projection_per" => 0, "projection_color" => "", "actual" => 0, "actual_per" => 0, "actual_color" => "", "expected" => 0];
				
				$categories = $this->division_map[$div];
				foreach($categories as $cat){
					$dash[$sub->customer_department."_".$div."_".$cat] = ["sub" => "", "div" => "", "cat" => $cat, "monthly_report" => 0, "ml" => 0, "ml_actual" => 0, "projection" => 0, "projection_per" => 0, "projection_color" => "", "actual" => 0, "actual_per" => 0, "actual_color" => "", "expected" => 0];
				}
			}
		}
		
		//print_r($dash); echo "<br/><br/>";
		
		//get gerp records based on IOD
		$gerps = $this->my_func->get_gerp_iod($from, $to);
		
		$div_map = $this->division_map_inv;
		$cat_map = $this->category_map_inv;
		
		$to_time = strtotime($to);
		
		foreach($gerps as $item){
			if ($item->model_category){
				$cat = $cat_map[$item->model_category];
				$div = $div_map[$cat];
				$sub = $item->customer_department;
				
				$amount = $item->sales_amount / $exchange_rate / 1000;
				
				$dash[$sub]["projection"] += $amount;
				$dash[$sub."_".$div]["projection"] += $amount;
				$dash[$sub."_".$div."_".$cat]["projection"] += $amount;
				
				switch($item->delivery){
					case "M-1": 
						$dash[$sub]["actual"] += $amount;
						$dash[$sub."_".$div]["actual"] += $amount;
						$dash[$sub."_".$div."_".$cat]["actual"] += $amount;
						break;
					case "M": 
						$dash[$sub]["actual"] += $amount;
						$dash[$sub."_".$div]["actual"] += $amount;
						$dash[$sub."_".$div."_".$cat]["actual"] += $amount;
						break;
					case "M+1": 
						$dash[$sub]["expected"] += $amount;
						$dash[$sub."_".$div]["expected"] += $amount;
						$dash[$sub."_".$div."_".$cat]["expected"] += $amount;
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
				$dash[$key]["monthly_report"] = $ml->monthly_report / 1000;
				$dash[$key]["ml"] = $ml->ml / 1000;
				$dash[$key]["ml_actual"] = $ml->ml_actual / 1000;
				$dash[$key]["projection_per"] = $dash[$key]["ml_actual"] > 0 ? $dash[$key]["projection"] / $dash[$key]["ml_actual"] * 100 : 0;
				$dash[$key]["projection_color"] = $dash[$key]["projection_per"] >= 100 ? "success" : "danger";
				$dash[$key]["actual_per"] = $dash[$key]["ml_actual"] > 0 ? $dash[$key]["actual"] / $dash[$key]["ml_actual"] * 100 : 0;
				$dash[$key]["actual_color"] = $dash[$key]["actual_per"] >= 100 ? "success" : "danger";
			}
		}
		
		//echo $from." ~ ".$to."<br/>"; print_r($dash); echo "<br/><br/>";
		//foreach($dash as $key => $d){print_r($d); echo " =====> ".$key."<br/><br/>";}
		
		return $dash;
	}
	
	private function get_sales($gerps, $from, $to, $exchange_rate){
		//structure setting
		$sales = [];
		
		$div_map = $this->division_map_inv;
		$cat_map = $this->category_map_inv;
		
		$subsidiaries = $this->gen_m->only("obs_gerp_sales_order", "customer_department", ["create_date >=" => date("Y-01-01", strtotime($from)), "create_date <=" => date("Y-12-t", strtotime($to))]);
		foreach($subsidiaries as $sub){
			foreach($this->divisions as $div){
				$categories = $this->division_map[$div];
				foreach($categories as $cat){
					$sales[$sub->customer_department][$div][$cat] = [];
				}
			}
		}
		
		$to_time = strtotime($to);
		foreach($gerps as $g){
			if ($g->model_category and $g->line_status === "Closed" and (strtotime($g->close_date) <= $to_time)){
				$cat = $cat_map[$g->model_category];
				$div = $div_map[$cat];
				
				//print_r($g); echo "<br/><br/>";
				if (!array_key_exists($g->model, $sales[$g->customer_department][$div][$cat])) $sales[$g->customer_department][$div][$cat][$g->model] = ["qty" => 0, "amount" => 0];
				
				$sales[$g->customer_department][$div][$cat][$g->model]["qty"] += $g->ordered_qty;
				$sales[$g->customer_department][$div][$cat][$g->model]["amount"] += $g->sales_amount / $exchange_rate / 1000;	
			}//else{print_r($g); echo "<br/><br/>";}
		}
		
		$div_map = $this->division_map;
		foreach($sales as $subsidiary => $sales_sub){
			foreach($div_map as $div => $categories){
				foreach($categories as $cat){
					if (array_key_exists($cat, $sales[$subsidiary][$div])) {
						uasort($sales[$subsidiary][$div][$cat], function($a, $b) {
							return $a["amount"] < $b["amount"];
						});
					}else $sales[$subsidiary][$div][$cat] = [];
				}
			}
		}
		
		/* use this code to print sales
		$div_map = $this->division_map;
		foreach($sales as $subsidiary => $sales_sub){
			echo $subsidiary."<br/>";
			
			foreach($div_map as $div => $categories){
				echo $div."<br/>";
				
				foreach($categories as $cat){
					echo $cat."<br/>";
					
					if (array_key_exists($cat, $sales[$subsidiary][$div])) {
						uasort($sales[$subsidiary][$div][$cat], function($a, $b) {
							return $a["amount"] < $b["amount"];
						});
						
						foreach($sales[$subsidiary][$div][$cat] as $model => $data){
							echo $model." ====> "; print_r($data); echo "<br/>";
						}
						
						//print_r($sales[$subsidiary][$div][$cat]); echo "<br/>";
						
					}else $sales[$subsidiary][$div][$cat] = [];
					
					echo "<br/>";
				}
				echo "<br/>";
			}
			echo "<br/>";
		}
		*/
	
		return $sales;
	}

	private function get_magento_statistics($magentos, $from, $to, $exchange_rate){
		$devices = ["total" => ["device" => "Total", "qty" => 0, "amount" => 0]];
		$devices_rec = $this->gen_m->only("obs_magento", "devices", ["devices !=" => "", "local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"]);
		foreach($devices_rec as $d) $devices[$d->devices] = ["device" => $d->devices, "qty" => 0, "amount" => 0];
		
		$cus_group = ["total" => ["customer_group" => "Total", "qty" => 0, "amount" => 0]];
		$cus_group_rec = $this->gen_m->only("obs_magento", "customer_group", ["customer_group !=" => "", "local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"]);
		foreach($cus_group_rec as $c) $cus_group[$c->customer_group] = ["customer_group" => $c->customer_group, "qty" => 0, "amount" => 0];
		
		$d2b2c = ["total" => ["company" => "Total", "qty" => 0, "amount" => 0]];
		$d2b2c_rec = $this->gen_m->only("obs_magento", "company_name_through_vipkey", ["grand_total_purchased >" => 0, "company_name_through_vipkey !=" => "", "local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"]);
		foreach($d2b2c_rec as $c) $d2b2c[$c->company_name_through_vipkey] = ["company" => $c->company_name_through_vipkey, "qty" => 0, "amount" => 0];
		
		$cupons = ["total" => ["cupon" => "Total", "rule" => "", "qty" => 0, "amount" => 0]];
		$cupons_rec = $this->gen_m->only_multi("obs_magento", ["coupon_code", "coupon_rule"], ["grand_total_purchased >" => 0, "local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"]);
		foreach($cupons_rec as $c) $cupons[$c->coupon_code] = ["cupon" => $c->coupon_code, "rule" => $c->coupon_rule, "qty" => 0, "amount" => 0];
		
		$departments = ["total" => ["department" => "Total", "province" => "", "qty" => 0, "amount" => 0]];
		$departments_rec = $this->gen_m->only_multi("obs_magento", ["department", "province"], ["grand_total_purchased >" => 0, "local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"]);
		foreach($departments_rec as $z) $departments[$z->department."_".$z->province] = ["department" => $z->department, "province" => $z->province, "qty" => 0, "amount" => 0];
		
		$daily = [];
		$dates_between = $this->my_func->dates_between($from, $to);
		foreach($dates_between as $item){
			$daily[date("d", strtotime($item))] = [
				4 => ["qty" => 0, "amount" => 0],
				8 => ["qty" => 0, "amount" => 0],
				12 => ["qty" => 0, "amount" => 0],
				16 => ["qty" => 0, "amount" => 0],
				20 => ["qty" => 0, "amount" => 0],
				24 => ["qty" => 0, "amount" => 0],
			];
		}
		
		foreach($magentos as $m){
			$amount = $m->grand_total_purchased / 1.18 / $exchange_rate / 1000;
			
			$day_i = date("d", strtotime($m->local_time));
			$hour_i = (((int)(date("H", strtotime($m->local_time)) / 4) + 1) * 4);
			
			$daily[$day_i][$hour_i]["qty"]++;
			$daily[$day_i][$hour_i]["amount"] += $amount;	
			
			if ($m->devices){
				$devices["total"]["qty"]++;
				$devices["total"]["amount"] += $amount;
				$devices[$m->devices]["qty"]++;
				$devices[$m->devices]["amount"] += $amount;
			}
			
			if ($m->customer_group){
				$cus_group["total"]["qty"]++;
				$cus_group["total"]["amount"] += $amount;
				$cus_group[$m->customer_group]["qty"]++;
				$cus_group[$m->customer_group]["amount"] += $amount;
			}
			
			if ($m->company_name_through_vipkey){
				$d2b2c["total"]["qty"]++;
				$d2b2c["total"]["amount"] += $amount;
				$d2b2c[$m->company_name_through_vipkey]["qty"]++;
				$d2b2c[$m->company_name_through_vipkey]["amount"] += $amount;
			}
			
			if ($m->coupon_code){
				$cupons["total"]["qty"]++;
				$cupons["total"]["amount"] += $amount;
				$cupons[$m->coupon_code]["qty"]++;
				$cupons[$m->coupon_code]["amount"] += $amount;
			}
			
			if ($m->department){
				$departments["total"]["qty"]++;
				$departments["total"]["amount"] += $amount;
				$departments[$m->department."_".$m->province]["qty"]++;
				$departments[$m->department."_".$m->province]["amount"] += $amount;
			}
			
			//print_r($m); echo "<br/><br/>";	
		}
		
		uasort($devices, function($a, $b) {
			return $a["amount"] < $b["amount"];
		});
		
		uasort($cus_group, function($a, $b) {
			return $a["amount"] < $b["amount"];
		});
		
		uasort($d2b2c, function($a, $b) {
			return $a["amount"] < $b["amount"];
		});
		
		uasort($cupons, function($a, $b) {
			return $a["amount"] < $b["amount"];
		});
		
		uasort($departments, function($a, $b) {
			return $a["amount"] < $b["amount"];
		});
		
		//foreach($cus_group as $C){print_r($C); echo "<br/>";}
		//foreach($devices as $d){print_r($d); echo "<br/>";}
		//foreach($d2b2c as $d){print_r($d); echo "<br/>";}
		//foreach($cupons as $c){print_r($c); echo "<br/>";}
		//foreach($departments as $d){print_r($d); echo "<br/>";}
		//foreach($daily as $t){print_r($t); echo "<br/>";}
		
		return [
			"cus_group" => $cus_group,
			"devices" => $devices,
			"d2b2c" => $d2b2c,
			"cupons" => $cupons,
			"departments" => $departments,
			"daily" => $daily,
			"dates_between" => $dates_between,
		];
	}
	
	public function index(){
		$today = strtotime(date("Y-m-d"));
		$weeks = array_reverse($this->get_weeks_by_year(date("Y")));//recent week at first
		foreach($weeks as $i => $w) if (strtotime($w["dates"][0]) > $today) unset($weeks[$i]);//remove future weeks
		
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
		
		//set magento data filters > no IOD based. no close_date field
		$w_m = ["local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"];
		$w_in_m = [
			[
				"field" => "status", 
				"values" => ["complete", "awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"],
			],
		];
		
		$magentos = $this->gen_m->filter("obs_magento", false, $w_m, null, $w_in_m, [["local_time", "desc"]]);
		
		//get gerp records based on IOD
		$gerps = $this->my_func->get_gerp_iod($from, $to);
		
		$data = [
			"exchange_rate" => $this->exchange_rate,
			"weeks"			=> $weeks,
			"months"		=> $months,
			"from"			=> $from,
			"to"			=> $to,
			"dashboard" 	=> $this->get_dashboard($gerps, $from, $to, $this->exchange_rate),
			"sales" 		=> $this->get_sales($gerps, $from, $to, $this->exchange_rate),
			"statistics" 	=> $this->get_magento_statistics($magentos, $from, $to, $this->exchange_rate),
			"magentos" 		=> $magentos,
			"gerps" 		=> $gerps,
			"main" 			=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}

	public function progress($period, $qty = 24){
		$start_time = microtime(true);
		
		$exchange_rate = $this->exchange_rate;
		$today = date("Y-m-d");
		
		$headers = $progress = $dates = $row_headers = $dashs = [];
		
		switch($period){
			case "m": 
				for($i = 0; $i < 24; $i++){
					$now = date("Y-m-d", strtotime("-".($i)." month", strtotime($today)));
					$from = date("Y-m-01", strtotime($now));
					$to = date("Y-m-t", strtotime($now));
					
					$headers[] = date("M", strtotime($from)) === "Jan" ? date("M y", strtotime($from)) : date("M", strtotime($from));
					$dates[] = [$from, $to];
					
					if ($i >= $qty) break;
				}
				break;
			case "w":
				for($i = 0; $i < 24; $i++){
					$now = date("Y-m-d", strtotime("-".(7 * $i)." day", strtotime($today)));
					$now_w = $this->get_week_by_date($now);
					
					$headers[] = "W".$now_w["week"];
					$dates[] = [$now_w["dates"][0], $now_w["dates"][1]];
					
					if ($i >= $qty) break;
				}
				break;
		}
		
		foreach($dates as $i => $d){
			$dashs_now = $this->get_dashboard($this->my_func->get_gerp_iod($d[0], $d[1]), $d[0], $d[1], $exchange_rate);
			foreach($dashs_now as $key => $dash){
				$dashs[$i][$key] = ["actual" => $dash["actual"], "actual_per" => $dash["actual_per"]];
				$row_headers[] = $key;
			}
		}
		
		$row_headers_arr = [];
		$row_headers = array_unique($row_headers);
		foreach($row_headers as $rh){
			$aux = ["sub" => null, "div" => null, "cat" => null, "code" => $rh];
			$rh_ = explode("_", $rh);
			switch(count($rh_)){
				case 1: $aux["sub"] = $rh_[count($rh_) - 1]; break;
				case 2: $aux["div"] = $rh_[count($rh_) - 1]; break;
				case 3: $aux["cat"] = $rh_[count($rh_) - 1]; break;
			}
			
			//print_r($aux); echo "<br/><br/>";
			$row_headers_arr[] = $aux;
		}
		
		//print_r($headers); echo "<br/><br/>";
		//print_r($dates); echo "<br/><br/>";
		//print_r($dashs); echo "<br/><br/>";
		//print_r($row_headers); echo "<br/><br/>";
		
		$data = [
			"period"	=> $period === "w" ? "Weekly" : "Monthly",
			"qty"		=> $qty,
			"headers"	=> $headers,
			"dashs"		=> $dashs,
			//"row_headers" => $row_headers,
			"row_headers" => $row_headers_arr,
			"main" 		=> "module/obs_report/progress",
		];
		
		$this->load->view('layout', $data);
	}

}
