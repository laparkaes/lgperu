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
		$m = $this->input->get("m"); //month
		if (!$m) $m = date("Y-m");
		
		$f = [
			"local_time >=" => date("Y-m-01", strtotime($m)),
			"local_time <=" => date("Y-m-t", strtotime($m)),
		];
		
		$sales = $this->gen_m->filter("obs_magento", false, $f, null, null, [["local_time", "desc"]]);
		print_r($f);
		print_r($sales);
		
		$data = [
			"sales" 	=> $sales,
			"main" 		=> "module/obs_report/index",
		];
		
		//$this->load->view('layout', $data);
	}
}
