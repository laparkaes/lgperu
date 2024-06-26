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
		$date_start = $this->input->get("f");
		$date_end = $this->input->get("t");
		
		$last_ex = $this->gen_m->filter("exchange_rate", false, ["currency_from" => "PEN", "currency_to" => "USD"], null, null, [["date", "desc"]], 1, 0);
		
		if (!$date_start) $date_start = $last_ex ? date('Y-m-d', strtotime($last_ex[0]->date . ' +1 day')) : date("Y-m-01");
		if (!$date_end) $date_end = date("Y-m-d");
		
		if (strtotime($date_end) >= strtotime($date_start)){
			echo "Inserting exchange rates between: ".$date_start." ~ ".$date_end; echo "<br/><br/>";
		
			$dates = $this->my_func->dates_between($date_start, $date_end);
			foreach($dates as $d){
				$ex = $this->my_func->get_exchange_rate_usd($d);
				if ($ex){
					$f = ["date" => $d, "currency_from" => $ex["currency_from"], "currency_to" => $ex["currency_to"]];
					$ex_rec = $this->gen_m->filter("exchange_rate", false, $f, null, null, null, 1, 0);
					echo $this->db->last_query();
					print_r($ex_rec);
					if ($ex_rec){
						$this->gen_m->update("exchange_rate", ["exchange_rate_id", $ex_rec[0]->exchange_rate_id], $ex);
						echo $d." exchange rate updated.<br/>";
					}else{
						$this->gen_m->insert("exchange_rate", $ex);
						echo $d." exchange rate inserted.<br/>";
					}
					print_r($ex);
				}else echo $d." no exchange rate data from SBS.<br/>";
				
				echo "<br/><br/>";
			}
			
		}else echo "Exchange rate is updated until today ".$date_end;
		
		
		
		//$this->my_func->load_exchange_rate_sbs("2024-06-02");
		//echo $this->my_func->last_working_date($dateString = "2024-06-2");
	}
	
}
