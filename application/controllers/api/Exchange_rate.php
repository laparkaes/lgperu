<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exchange_rate extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){//20241014
		//llamasys/api/exchange_rate?k=lgepr&c=PEN&d=2024-10-09
		//llamasys/api/exchange_rate?k=lgepr&c=PYG&d=2024-10-09
		
		if ($this->input->get("k") === "lgepr"){
			$d = $this->input->get("d");
			$er = $this->gen_m->filter("exchange_rate", false, ["date <=" => $d, "currency" => $this->input->get("c")], null, null, [["date", "desc"]], 1);
			if ($er){
				$res = [
					"status"	=> "success",
					"requested"	=> $d,
					"pulished"	=> $er[0]->date,
					"currency"	=> $er[0]->currency,
					"buy"	=> $er[0]->buy,
					"sell"	=> $er[0]->sell,
				];	
			}else $res = ["status" => "error", "msg" => "No exchange rate registered before request date."];
		}else $res = ["status" => "error", "msg" => "Wrong key."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
}
