<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_report extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$from = $this->input->get("f") ? $this->input->get("f") : date("Y-m-01");
		$to = $this->input->get("t") ? $this->input->get("t") : date("Y-m-t");
		
		$f = [
			"local_time >=" => $from,
			"local_time <=" => $to,
		];
		
		$sales = $this->gen_m->filter("obs_magento", false, $f, null, null, [["local_time", "desc"]]);
		
		$status = [];
		$status_rec = $this->gen_m->only("obs_magento", "status", $f);
		foreach($status_rec as $s){
			$status[$s->status] = ["code" => $s->status, "qty" => 0, "amount" => 0];
		}
		print_R($status);
		
		/*
		echo "<textarea>";
		$aux = $sales[0];
		foreach($aux as $k => $a) print_r('<td><?= $sale->'.$k.' ?></td>');
		echo "</textarea>";
		*/
		$data = [
			"from"		=> $from,
			"to"		=> $to,
			"sales" 	=> $sales,
			"main" 		=> "module/obs_report/index",
		];
		
		$this->load->view('layout', $data);
	}
}
