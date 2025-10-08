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
	
	public function daily_order_closing_report(){
		//only_multi($tablename, $fields, $where = null, $groups = null);
		
		$from = date("Y-m-01");
		$to = date("Y-m-t");
		
		$start_date = new DateTime('first day of this month');
		$end_date = new DateTime('last day of this month +1 day');
		$interval = new DateInterval('P1D'); // P1D = 1 Day
		
		$period = new DatePeriod($start_date, $interval, $end_date);

		$date_arr = [];
		foreach ($period as $date) {
			$date_arr[] = [$date->format('Y-m-d') => ["plan" => 0, "close" => 0]];
		}
		
		print_r($date_arr);
		
		$rows = [
			"LGEPR" => [
				"HS" => [
					
				],
				"MS" => [
				
				],
				"ES" => [
				
				],
			],
			"Branch" => [
			
			],
		];
		
		
		
		$departments = $this->gen_m->only_multi("lgepr_order", ["department"], ["dash_company !=" => null, "booked_date <=" => $from]);
		foreach($departments as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
		
		
		$com_div = $this->gen_m->only_multi("lgepr_order", ["dash_company", "dash_division"], ["dash_company !=" => null, "booked_date <=" => $from]);
		foreach($com_div as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
		
		
		$bill_to_names = $this->gen_m->only_multi("lgepr_order", ["bill_to_name"], ["dash_company !=" => null, "booked_date <=" => $from]);
		foreach($bill_to_names as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
		
		
		$so_status = $this->gen_m->only_multi("lgepr_order", ["so_status"], ["dash_company !=" => null, "booked_date <=" => $from]);
		foreach($so_status as $item){
			print_r($item); echo "<br/>";
		}
		
		echo "<br/>";
	}
	
}
