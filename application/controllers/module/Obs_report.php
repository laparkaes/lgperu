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

	public function test(){
		$weeks = $this->get_weeks_by_year(2024);
		foreach($weeks as $w){
			print_r($w); echo "<br/>";
		}
		
		echo "<br/><br/><br/>";
		
		print_r($this->get_week_by_date("2024-06-03"));
		
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
	
	public function index(){
		$exchange_rate = 3.8;
		$weeks = array_reverse($this->get_weeks_by_year(date("Y"))); //recent week at first
		
		//get date range
		$from = ($this->input->get("f") ? $this->input->get("f") : date("Y-m-01"));
		$to = ($this->input->get("t") ? $this->input->get("t") : date("Y-m-t"));
		
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
		
		//set magento gerp data filters
		$s_g = ["create_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		$w_g = ["create_date >=" => $from, "create_date <=" => $to, "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "desc"]]);
		//foreach($gerps as $g){echo $g->create_date." /// ".$g->order_status." - ".$g->line_status."<br/>";}
		
		
		
		$data = [
			"exchange_rate" => $exchange_rate,
			"weeks"			=> $weeks,
			"from"			=> $from,
			"to"			=> $to,
			"subsidiaries" 	=> $this->set_subsidiaries($from, $to, $exchange_rate),
			"magentos" 		=> $magentos,
			"gerps" 		=> $gerps,
			"main" 			=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}
}
