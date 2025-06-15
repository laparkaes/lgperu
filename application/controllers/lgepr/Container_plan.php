<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Container_plan extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$eta_from = $this->input->get("eta_from"); if (!$eta_from) $eta_from = date('Y-m-01', strtotime('-2 months'));
		$eta_to = $this->input->get("eta_to"); if (!$eta_to) $eta_to = date("Y-m-t");
		
		$w = ["eta >=" => $eta_from, "eta <=" => $eta_to,];
		$o = [["eta", "desc"], ["sa_no", "asc"], ["sa_line_no", "asc"], ["container", "asc"]];
		$containers = $this->gen_m->filter("lgepr_container", false, $w, null, null, $o);
		
		$data = [
			"containers"	=> $containers,
			"overflow"		=> "scroll",
			"main"			=> "lgepr/container_plan",
		];
		
		$this->load->view('layout_dashboard', $data);
	}
}
