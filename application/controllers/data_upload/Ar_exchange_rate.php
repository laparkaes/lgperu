<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_exchange_rate extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$from = date("Y-m-d", strtotime(date("Y-m-d")." -1 year"));
		
		$data = [
			"er_pen" => $this->gen_m->filter("exchange_rate", false, ["date >=" => $from, "currency" => "PEN"], null, null, [["date", "desc"]]),
			"er_pyg" => $this->gen_m->filter("exchange_rate", false, ["date >=" => $from, "currency" => "PYG"], null, null, [["date", "desc"]]),
			"main" => "data_upload/ar_exchange_rate/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function proxy_dnit(){
		$content = file_get_contents('https://www.dnit.gov.py/web/portal-institucional/cotizaciones');
		$content = str_replace("script", "div", $content);
		$content = str_replace("link", "div", $content);
		$content = str_replace("meta", "div", $content);
		$content = str_replace("style", "div", $content);
		
		echo $content;
	}
	
	public function upload_pyg(){
		//edit php.ini > max_input_vars = 10000
		$data = $this->input->post("data");
		foreach($data as $item){
			$aux_date = $item[0]."-".$item[1]."-".$item[2];
			
			$row = [
				"date" => $aux_date,
				"date_apply" => date("Y-m-d", strtotime($aux_date." +1 day")),
				"currency" => "PYG",
			];
			
			if (!$this->gen_m->filter("exchange_rate", false, $row)){
				$row["buy"] = $item[3];
				$row["sell"] = $item[4];
				$row["avg"] = round((floatval($item[3]) + floatval($item[4])) / 2 , 2);
				
				$this->gen_m->insert("exchange_rate", $row);
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => "success", "msg" => "Exchage rate USD > PYG has been updated."]);
	}
	
	public function upload_pen(){
		set_time_limit(300);
		
		$date_start = $this->input->get("f");
		$date_end = $this->input->get("t");
		
		$last_ex = $this->gen_m->filter("exchange_rate", false, ["currency" => "PEN"], null, null, [["date", "desc"]], 1, 0);
		
		if (!$date_start) $date_start = $last_ex ? date('Y-m-d', strtotime($last_ex[0]->date . ' +1 day')) : "2024-01-01";
		if (!$date_end) $date_end = date("Y-m-d");
		
		if (strtotime($date_end) >= strtotime($date_start)){
			//echo "Exchange rate update start: ".$date_start." ~ ".$date_end; echo "<br/><br/>";
		
			$dates = $this->my_func->dates_between($date_start, $date_end);
			foreach($dates as $i => $d){
				if (!$this->gen_m->filter("exchange_rate", false, ["date" => $d, "currency" => "PEN"])){
					
					$ex = $this->my_func->load_exchange_rate_sbs($d);
					if ($ex){
					
						$row = [
							"date" => $d,
							"date_apply" => date("Y-m-d", strtotime($d." +1 day")),
							"currency" => "PEN",
							"buy" => str_replace(",", ".", $ex["valor_compra"]),
							"sell" => str_replace(",", ".", $ex["valor_venta"]),
						];	
						
						$row["avg"] = (floatval($row["buy"]) + floatval($row["sell"])) / 2;
						
						//print_r($row);
						$this->gen_m->insert("exchange_rate", $row);
					}
				}
			}
		}//else echo "Exchange rate is updated until today ".$date_end;
		
		header('Content-Type: application/json');
		echo json_encode(["type" => "success", "msg" => "Exchage rate USD > PEN has been updated."]);
	}
	
	public function test(){
		$ex = $this->my_func->load_exchange_rate_sbs("2025-06-17");
		print_r($ex);
	}
}
