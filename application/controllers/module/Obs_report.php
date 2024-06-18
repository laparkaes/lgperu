<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
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

	public function test_history(){
		$exchange_rate = 3.8;
		$today = date("Y-m-d");
		
		$start_time = microtime(true);
		
		/* get last 12 weeks summaries / 2.51 sec laptop / 16.16 sec laptop LG
		*/
		for($i = 0; $i < 12; $i++){
			$now = date("Y-m-d", strtotime("-".(7 * $i)." day", strtotime($today)));
			echo $now."<br/>";
			
			$now_w = $this->get_week_by_date($now);
			print_r($now_w); echo "<br/>";
			
			$subsidiaries = $this->set_subsidiaries($now_w["dates"][0], $now_w["dates"][1], $exchange_rate);
			print_r($subsidiaries); echo "<br/>";
			
			echo "<br/>";
		}
		
		/* get last 12 months summaries / 2.55 sec laptop / 16.99 sec laptop LG
		for($i = 0; $i < 12; $i++){
			$now = date("Y-m-d", strtotime("-".($i)." month", strtotime($today)));
			echo $now."<br/>";
			
			$from = date("Y-m-01", strtotime($now));
			$to = date("Y-m-t", strtotime($now));
			echo $from." ~ ".$to."<br/>";
			
			$subsidiaries = $this->set_subsidiaries($from, $to, $exchange_rate);
			print_r($subsidiaries); echo "<br/>";
			
			echo "<br/>";
		}
		*/
		
		$end_time = microtime(true);
		echo "<br/><br/>Exec time: ".number_format($end_time - $start_time, 2)." sec";
		
	}
	
	private function set_subsidiaries($from, $to, $exchange_rate){
		//print_r($this->gen_m->only("obs_gerp_sales_order", "line_status")); echo "<br/><br/>";
		$f = [
			"order_status !=" => "Cancelled",
			"line_status !=" => "Cancelled",
			"create_date >=" => $from, 
			"create_date <=" => $to,
		];
		
		$categories = [
			"HA" => ["Refrigerator", "Cooking Appliance", "Washing Machine", "Airconditioner"],
			"HE" => ["TV", "AV", "AO"],
			"BS" => ["Monitor", "PC", "Commercial Display_Signage"],
		];
		$divisions = ["HA", "HE", "BS"];
		$subsidiaries = [];
		$subsidiaries_rec = $this->gen_m->only("obs_gerp_sales_order", "customer_department", ["customer_department !=" => null]);
		foreach($subsidiaries_rec as $sub){
			$sub_total = $sub_closed = $sub_on_process = 0;
			$f["customer_department"] = $sub->customer_department;
			
			$subsidiaries[$sub->customer_department] = [];
			$subsidiaries[$sub->customer_department]["divisions"] = [];
			foreach($divisions as $div){
				$div_total = $div_closed = $div_on_process = 0;
				
				$subsidiaries[$sub->customer_department]["divisions"][$div] = [];
				$subsidiaries[$sub->customer_department]["divisions"][$div]["categories"] = [];
				foreach($categories[$div] as $cat){
					$cat_closed = $cat_on_process = 0;
					$f["product_level1_name"] = $cat;
					
					//calculate category amount
					$w_in = [["field" => "line_status", "values" => ["Closed"]]];
					$cat_closed = $this->gen_m->sum("obs_gerp_sales_order", "sales_amount", $f, $w_in)->sales_amount / $exchange_rate;//convert to USD
					
					$w_in = [["field" => "line_status", "values" => ["Awaiting Fulfillment", "Awaiting Shipping", "Booked", "Pending pre-billing acceptance"]]];
					$cat_on_process = $this->gen_m->sum("obs_gerp_sales_order", "sales_amount", $f, $w_in)->sales_amount / $exchange_rate;//convert to USD
					
					$cat_total = $cat_closed + $cat_on_process;
					//add to division amount
					$div_total += $cat_total;
					$div_closed += $cat_closed;
					$div_on_process += $cat_on_process;
					
					//add to subsidiary amount
					$sub_total += $cat_total;
					$sub_closed += $cat_closed;
					$sub_on_process += $cat_on_process;
					
					$subsidiaries[$sub->customer_department]["divisions"][$div]["categories"][$cat] = [];
					$subsidiaries[$sub->customer_department]["divisions"][$div]["categories"][$cat]["summary"] = [
						"total" => $cat_total,
						"closed" => $cat_closed,
						"on_process" => $cat_on_process,
					];
				}
				
				$subsidiaries[$sub->customer_department]["divisions"][$div]["summary"] = [
					"total" => $div_total,
					"closed" => $div_closed,
					"on_process" => $div_on_process,
				];
			}
			
			$subsidiaries[$sub->customer_department]["summary"] = [
				"total" => $sub_total,
				"closed" => $sub_closed,
				"on_process" => $sub_on_process,
			];
		}
		
		/* to print data structure
		foreach($subsidiaries as $sub => $subsidiary){
			echo $sub." ====> ";
			print_r($subsidiary["summary"]);
			echo "<br/><br/>";
			
			foreach($subsidiary["divisions"] as $div => $divisions){
				echo "---";
				echo $div." ====> ";
				print_r($divisions["summary"]);
				echo "<br/><br/>";
				
				foreach($divisions["categories"] as $cat => $category){
					echo "------";
					echo $cat." ====> ";
					print_r($category);
					echo "<br/><br/>";
				}
			}	
		} */
		
		return $subsidiaries;
	}
	
	private function set_sales($gerps, $from, $to, $exchange_rate){
		$subsidiaries = $this->gen_m->only("obs_gerp_sales_order", "customer_department", ["create_date >=" => $from, "create_date <=" => $to]);
		$lvl1s = $this->gen_m->only("obs_gerp_sales_order", "product_level1_name", ["create_date >=" => $from, "create_date <=" => $to]);
		
		$sales = [];
		foreach($subsidiaries as $sub){
			$sales[$sub->customer_department] = [];
			$sales[$sub->customer_department]["total"] = 0;
			$sales[$sub->customer_department]["lvl1s"] = [];
			foreach($lvl1s as $lvl1){
				$sales[$sub->customer_department][$lvl1->product_level1_name]["total"] = 0;
				$sales[$sub->customer_department][$lvl1->product_level1_name]["models"] = [];
				
				$models = $this->gen_m->only("obs_gerp_sales_order", "model", ["create_date >=" => $from, "create_date <=" => $to, "product_level1_name" => $lvl1->product_level1_name]);
				foreach($models as $model){
					$sales[$sub->customer_department][$lvl1->product_level1_name]["models"][$model->model] = 0;
				}
			}
		}
		
		$categories = [
			"HA" => [
				"Refrigerator" => 0, 
				"Cooking Appliance" => 0, 
				"Washing Machine" => 0, 
				"Airconditioner" => 0,
			],
			"HE" => [
				"TV" => 0, 
				"AV" => 0, 
				"AO" => 0,
			],
			"BS" => [
				"Monitor" => 0, 
				"PC" => 0, 
				"Commercial Display_Signage" => 0,
			],
		];
		
		foreach($gerps as $g){
			//print_r($g); echo "<br/><br/>";
			//print_r($g->model); echo "<br/><br/>";
			$sales[$g->customer_department][$g->product_level1_name]["total"] += $g->sales_amount;
			$sales[$g->customer_department][$g->product_level1_name]["models"][$g->model] += $g->sales_amount;
		}
		
		print_r($sales);
		
		/*
		foreach($sales as $sub => $s){
			echo $sub."<br/>";
			foreach($s as $lvl1 => $);
		}
		*/
		
		echo "<br/><br/>";
		
	}
	
	public function test(){
		$exchange_rate = 3.8;
		$from = "2024-05-01";
		$to = "2024-05-31";
		
		//set gerp data filters
		$s_g = ["create_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level1_name","product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		$w_g = ["create_date >=" => $from, "create_date <=" => $to, "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "desc"]]);
		
		$this->set_sales($gerps, $from, $to, $exchange_rate);
	}
	
	public function index(){
		$exchange_rate = 3.8;
		
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
		}else{//default: this week
			$aux = $this->get_week_by_date(date("Y-m-d"));
			$from = $aux["dates"][0];
			$to = $aux["dates"][1];
		}
		
		//set magento data filters
		$s_m = ["grand_total_purchased", "gerp_order_no", "local_time", "company_name_through_vipkey", "vipkey", "coupon_code", "coupon_rule", "discount_amount", "devices", "status", "customer_group", "department", "province"];
		$w_m = ["local_time >=" => $from." 00:00:00", "local_time <=" => $to." 23:59:59"];
		$w_in_m = [
			[
				"field" => "status", 
				"values" => ["complete", "awaiting_transfer", "processing", "holded", "preparing_for_delivery", "picking_for_delivery", "on_delivery", "delivery_completed"],
			],
		];
		
		$magentos = $this->gen_m->filter_select("obs_magento", false, $s_m, $w_m, null, $w_in_m, [["local_time", "desc"]]);
		//foreach($magentos as $m){echo $m->local_time." /// ".$m->status."<br/>";}
		
		//set gerp data filters
		$s_g = ["create_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level1_name","product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		$w_g = ["create_date >=" => $from, "create_date <=" => $to, "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "desc"]]);
		//foreach($gerps as $g){echo $g->create_date." /// ".$g->order_status." - ".$g->line_status."<br/>";}
		
		$data = [
			"exchange_rate" => $exchange_rate,
			"weeks"			=> $weeks,
			"months"		=> $months,
			"from"			=> $from,
			"to"			=> $to,
			"subsidiaries" 	=> $this->set_subsidiaries($from, $to, $exchange_rate),
			//"sales" 		=> $this->set_sales($gerps, $from, $to, $exchange_rate),
			"magentos" 		=> $magentos,
			"gerps" 		=> $gerps,
			"main" 			=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}

	public function progress($period){
		$start_time = microtime(true);
		
		echo $period."<br/><br/>";
		
		$exchange_rate = 3.8;
		$today = date("Y-m-d");
		
		$summaries = [];
		
		switch($period){
			case "m": 
				for($i = 0; $i < 12; $i++){
					$now = date("Y-m-d", strtotime("-".($i)." month", strtotime($today)));
					$from = date("Y-m-01", strtotime($now));
					$to = date("Y-m-t", strtotime($now));
					
					$summaries[] = [
						"dates" => [$from, $to],
						"subsidiaries" => $this->set_subsidiaries($from, $to, $exchange_rate),
					];
				}
				break;
			case "w":
				for($i = 0; $i < 12; $i++){
					$now = date("Y-m-d", strtotime("-".(7 * $i)." day", strtotime($today)));
					$now_w = $this->get_week_by_date($now);
					
					$summaries[] = [
						"dates" => [$now_w["dates"][0], $now_w["dates"][1]],
						"subsidiaries" => $this->set_subsidiaries($now_w["dates"][0], $now_w["dates"][1], $exchange_rate),
					];
				}
				break;
		}
		
		print_r($summaries);
		
		$end_time = microtime(true);
		echo "<br/><br/>Exec time: ".number_format($end_time - $start_time, 2)." sec";
	}

}
