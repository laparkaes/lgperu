<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
	
	public function order_report_excel(){
		$from = $this->input->get("from");
		if (!$from) $from = date("Y-m-01");
		
		$sales = $this->gen_m->filter("lgepr_order", false, ["line_status !=" => "Closed"]);
		$closed = $this->gen_m->filter("lgepr_order", false, ["closed_date >=" => $from]);
		
		$orders = array_merge($sales, $closed);
		
		$excel = [];
		
		//header setting
		$header = [];
		foreach($orders[0] as $key => $val) $header[] = strtoupper(str_replace("_", " ", $key));
		
		$excel[] = $header;
		
		//content
		foreach($orders as $item){
			$aux = [];
			foreach($item as $key => $val) $aux[] = $val;
			
			$excel[] = $aux;
			//print_r($item); echo "<br/><br/>";
			//print_r($aux); echo "<br/><br/>";
		}
		
		//print_r($excel);
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		$sheet->setTitle('rawdata');
		
		foreach($excel as $j => $row) foreach($row as $i => $val) $sheet->setCellValueByColumnAndRow($i+1, $j+1, $val);
		
		//save file
		$writer = new Xlsx($spreadsheet);
		$filePath = 'report/lgepr_orders.xlsx';
		$writer->save($filePath);
		
		redirect($filePath);
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
		foreach ($period as $date) {
			$date_arr[$date->format('d')] = ["day" => $date->format('d'), "amount" => ["plan" => 0, "closed" => 0]];
		}
		
		//no appointments in SO status
		$date_arr["40"] = ["day" => "OPEN", "amount" => ["plan" => 0, "closed" => 0]];
		$date_arr["41"] = ["day" => "HOLD", "amount" => ["plan" => 0, "closed" => 0]];
		$date_arr["42"] = ["day" => "BACK", "amount" => ["plan" => 0, "closed" => 0]];
		
		//line total
		$date_arr["50"] = ["day" => "TOTAL", "amount" => ["plan" => 0, "closed" => 0]];
		
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
		
		$today = date("Y-m-d");
		$today_t = strtotime($today);
		
		$sales = $this->gen_m->filter("lgepr_order", false, ["line_status !=" => "Closed"]);
		foreach($sales as $item){
			
			//print_r($item); echo "<br/>";
			if ($item->appointment_date){
				if ($today_t <= strtotime($item->appointment_date)){
					$amount_usd = $item->order_amount_usd;
					$day = date("d", strtotime($item->appointment_date));
				}else{
					$amount_usd = 0;
					$day = "01";
				}
			}else{
				$amount_usd = $item->order_amount_usd;
				
				switch($item->so_status){
					case "OPEN": $day = "40"; break;
					case "HOLD": $day = "41"; break;
					case "BACK": $day = "42"; break;
					default: $day = "01"; $amount_usd = 0;
				}
			}
				
			if ($item->department === "LGEPR"){
				//sum to day
				$daily_plan["Total"]["closing"][$day]["amount"]["plan"] += $amount_usd;
				$daily_plan["Total"]["departments"][$item->department]["closing"][$day]["amount"]["plan"] += $amount_usd;
				$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["closing"][$day]["amount"]["plan"] += $amount_usd;
				$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["divisions"][$item->dash_division]["closing"][$day]["amount"]["plan"] += $amount_usd;
				
				//sum to line total
				$day = "50";
				$daily_plan["Total"]["closing"][$day]["amount"]["plan"] += $amount_usd;
				$daily_plan["Total"]["departments"][$item->department]["closing"][$day]["amount"]["plan"] += $amount_usd;
				$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["closing"][$day]["amount"]["plan"] += $amount_usd;
				$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["divisions"][$item->dash_division]["closing"][$day]["amount"]["plan"] += $amount_usd;
			}
			
		}
		
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
			
			//sum to line total
			$day = "50";
			
			$daily_plan["Total"]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
			$daily_plan["Total"]["departments"][$item->department]["companies"][$item->dash_company]["divisions"][$item->dash_division]["closing"][$day]["amount"]["closed"] += $item->order_amount_usd;
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
