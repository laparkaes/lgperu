<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Dashboard extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$data = [
			"main" => "dashboard/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function update_exchange_rate(){
		set_time_limit(0);
		
		$date_start = $this->input->get("f");
		$date_end = $this->input->get("t");
		
		$last_ex = $this->gen_m->filter("exchange_rate", false, ["currency" => "USD"], null, null, [["date", "desc"]], 1, 0);
		
		if (!$date_start) $date_start = $last_ex ? date('Y-m-d', strtotime($last_ex[0]->date . ' +1 day')) : date("Y-m-01");
		if (!$date_end) $date_end = date("Y-m-d");
		
		if (strtotime($date_end) >= strtotime($date_start)){
			echo "Exchange rate update start: ".$date_start." ~ ".$date_end; echo "<br/><br/>";
		
			$dates = $this->my_func->dates_between($date_start, $date_end);
			foreach($dates as $i => $d){
				if (!$this->gen_m->filter("exchange_rate", false, ["date" => $d])){
					$ex = $this->my_func->get_exchange_rate_usd($d); print_r($ex); echo "<br/>";
					if ($ex){
						$f = ["date" => $ex["date"], "currency" => $ex["currency"]];
						$ex_rec = $this->gen_m->filter("exchange_rate", false, $f); //echo $this->db->last_query(); 
						//print_r($ex_rec);
						if ($ex_rec){
							$this->gen_m->update("exchange_rate", ["exchange_rate_id", $ex_rec[0]->exchange_rate_id], $ex);
							echo $d." exchange rate updated.<br/>";
						}else{
							$this->gen_m->insert("exchange_rate", $ex);
							echo $d." exchange rate inserted.<br/>";
						}
					}else echo $d." no exchange rate data from SBS.<br/>";
					
					//echo "<br/><br/>";
					//if ($i > 5) break;	
				}else echo $d." already exists.<br/>";
			}
		}else echo "Exchange rate is updated until today ".$date_end;
		
		echo "<br/>Exchange rate update finished.";
	}
	
	public function test_er(){
		//$date = date("Y-m-d"); $date = "2024-08-19";
		
		
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoibGdlIiwic3ViIjoibGdlIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjpbIk1hbmFnZXIiLCJTdXBlcnZpc29yIl0sIm5iZiI6MTcxODgxOTgzOSwiZXhwIjoxNzUwMzU1ODM5LCJpc3MiOiJodHRwOi8vand0YXV0aHpzcnYuYXp1cmV3ZWJzaXRlcy5uZXQiLCJhdWQiOiIwOTkxNTNjMjYyNTE0OWJjOGVjYjNlODVlMDNmMDAyMiJ9.1ejIUlAPbq8FhggDzJIhXkYrRCMli1ghC8OI2PETwZc';
		
		$ch = curl_init();
		$url = 'http://serviciosweb.sbs.gob.pe/api/tipocambio/contable/28082024';
		$headers = [
			'Accept: application/json',
			'Authorization: Bearer '.$token,
		];
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);//max waiting time 5 secs

		$response = curl_exec($ch);
		
		curl_close($ch);

		$ex = json_decode($response, true);
		
		print_r($ex);
		
		/*
		
		$dates = $this->my_func->dates_between("2024-08-01", "2024-08-31");
		foreach($dates as $d){
			echo $d."============================================<br/><br/>";
			
			for($i = 0; $i < 90; $i++){
				$ex = $this->my_func->load_exchange_rate_sbs($d, str_pad($i,2,0,STR_PAD_LEFT));
				if ($ex){ print_r($ex); echo "<br/>"; }
			}	
			
			echo "<br/><br/>";
		}
		
		*/
		
		
	}
}
