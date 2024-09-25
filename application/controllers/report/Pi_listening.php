<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pi_listening extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
		
	public function index(){
	
		
		$data = [
			"main" => "report/pi_listening/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}
}
