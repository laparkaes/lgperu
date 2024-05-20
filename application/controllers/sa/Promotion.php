<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Promotion extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "sa/promotion/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function search_customer(){
		print_r($this->input->post());
	}
	
	public function search_product(){
		print_r($this->input->post());
	}
}
