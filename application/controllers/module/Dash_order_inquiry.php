<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Dash_order_inquiry extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			//"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "module/dash_order_inquiry/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function test(){
		
	}
}
