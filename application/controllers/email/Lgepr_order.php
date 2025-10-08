<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$orders = $this->gen_m->filter("lgepr_order", false, null, null, null, [["order_date", "desc"], ["order_line", "asc"]], 1000);
		$header = $orders[0];
		
		echo "<table>";
		
		echo "<tr style='position: sticky;top: 0;'>";
		foreach($header as $k => $h) echo "<td style='background-color: white;'>".$k."</td>";
		echo "</tr>";
		
		foreach($orders as $item){
			echo "<tr>";
			foreach($item as $h) echo "<td>".$h."</td>";
			echo "</tr>";	
		}
		
		echo "</table>";
		
	}
	
	public function daily_closing_report(){
		//only_multi($tablename, $fields, $where = null, $groups = null);
		
		$from = date("Y-m-01");
		$to = date("Y-m-t");
		
		$start_date = new DateTime('first day of this month');
		$end_date = new DateTime('last day of this month +1 day');
		$interval = new DateInterval('P1D'); // P1D = 1 Day
		
		$period = new DatePeriod($start_date, $interval, $end_date);

		$date_arr = [];
		$date_arr["00"] = ["day" => "No Appo.", "amount" => ["plan" => 0, "closed" => 0]];
		foreach ($period as $date) {
			$date_arr[$date->format('d')] = ["day" => $date->format('d'), "amount" => ["plan" => 0, "closed" => 0]];
		}
		
		//print_r($date_arr); echo "<br/><br/>";

		$companies = [
			"HS" => [
				"rowname" => "HS",
				"closing" => $date_arr,
				"divisions" => [
					"REF" => ["rowname" => "REF", "closing" => $date_arr],
					"Cooking" => ["rowname" => "Cooking", "closing" => $date_arr],
					"W/M" => ["rowname" => "W/M", "closing" => $date_arr],
				],
			],
			"MS" => [
				"rowname" => "MS",
				"closing" => $date_arr,
				"divisions" => [
					"LTV" 			=> ["rowname" => "LTV", "closing" => $date_arr],
					"Audio" 		=> ["rowname" => "Audio", "closing" => $date_arr],
					"MNT" 			=> ["rowname" => "MNT", "closing" => $date_arr],
					"DS" 			=> ["rowname" => "DS", "closing" => $date_arr],
					"PC" 			=> ["rowname" => "PC", "closing" => $date_arr],
					"MNT Signage" 	=> ["rowname" => "MNT Signage", "closing" => $date_arr],
					"LED Signage" 	=> ["rowname" => "LED Signage", "closing" => $date_arr],
					"Commercial TV"	=> ["rowname" => "Commercial TV", "closing" => $date_arr],
				],
			],
			"ES" => [
				"rowname" => "ES",
				"closing" => $date_arr,
				"divisions" => [
					"RAC" 		=> ["rowname" => "RAC", "closing" => $date_arr],
					"SAC" 		=> ["rowname" => "SAC", "closing" => $date_arr],
					"Chiller" 	=> ["rowname" => "Chiller", "closing" => $date_arr],
				],
			],
		];
		
		$daily_plan = [
			"Total" => [
				"rowname" => "Total",
				"closing" => $date_arr,
				"departments" => [
					"LGEPR" => [
						"rowname" => "LGEPR",
						"closing" => $date_arr,
						"companies" => $companies,
					],
					"Branch" => [
						"rowname" => "Branch",
						"closing" => $date_arr,
						"companies" => $companies,
					],
				],
			],
		];
		
		$closed = $this->gen_m->filter("lgepr_order", false, ["closed_date >=" => $from]);
		foreach($closed as $item){
			//print_R($item); echo "<br/><br/>";
			//echo date("d", strtotime($item->closed_date))."<br/>";
			//echo $item->order_amount_usd."<br/>";
			//echo "<br/><br/>";
			
			$day = date("d", strtotime($item->closed_date));
			
			$daily_plan["Total"]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["divisions"][$item->dash_division]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
		}
		
		$appointments = $this->gen_m->filter("lgepr_order", false, ["line_status !=" => "Closed", "appointment_date >=" => $from]);
		foreach($appointments as $item){
			//print_R($item); echo "<br/><br/>";
			//echo date("d", strtotime($item->closed_date))."<br/>";
			//echo $item->order_amount_usd."<br/>";
			//echo "<br/><br/>";
			
			$day = date("d", strtotime($item->appointment_date));
			
			$daily_plan["Total"]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["divisions"][$item->dash_division]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
		}
		
		$no_appointments = $this->gen_m->filter("lgepr_order", false, ["line_status !=" => "Closed", "appointment_date" => null]);
		foreach($no_appointments as $item){
			foreach($item as $key => $val) echo $key." _____ ".$val."<br/>"; echo "<br/><br/>";
			//echo date("d", strtotime($item->closed_date))."<br/>";
			//echo $item->order_amount_usd."<br/>";
			//echo "<br/><br/>";
			
			/*
			$day = date("d", strtotime($item->appointment_date));
			
			$daily_plan["Total"]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["divisions"][$item->dash_division]["closing"][$day]["amount"]["plan"] += $item->order_amount_usd;
			*/
		}
		
		
		$data = [
			"today" => date("d"),
			"daily_plan_header" => $date_arr,
			"daily_plan" => $daily_plan,
		];
		
		$content = $this->load->view('email/lgepr_order/daily_closing_report', $data, true);
		echo $content;
		
		return;
		
		foreach($daily_plan as $total){
			echo $total["rowname"]."<br/>";
			print_r($total["closing"]); echo "<br/><br/>";
			
			foreach($total["departments"] as $department){
				echo $department["rowname"]."<br/>";
				print_r($department["closing"]); echo "<br/><br/>";
				
				foreach($department["companies"] as $company){
					echo $company["rowname"]."<br/>";
					print_r($company["closing"]); echo "<br/><br/>";
					
					foreach($company["divisions"] as $division){
						echo $division["rowname"]."<br/>";
						print_r($division["closing"]); echo "<br/><br/>";
						
						
					}
				}
			}
		}
		echo "<br/><br/>";
		
		$departments = $this->gen_m->only_multi("lgepr_order", ["department"], ["dash_company !=" => null, "booked_date >=" => $from]);
		foreach($departments as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
		
		
		$com_div = $this->gen_m->only_multi("lgepr_order", ["dash_company", "dash_division"], ["dash_company !=" => null, "booked_date >=" => $from]);
		foreach($com_div as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
		
		
		$bill_to_names = $this->gen_m->only_multi("lgepr_order", ["bill_to_name"], ["dash_company !=" => null, "booked_date >=" => $from]);
		foreach($bill_to_names as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
		
		
		$so_status = $this->gen_m->only_multi("lgepr_order", ["so_status"], ["dash_company !=" => null, "booked_date >=" => $from]);
		foreach($so_status as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
	}
	
}
