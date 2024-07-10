<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Ism_activity_management extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$lines = $this->gen_m->all("product_line", [["line", "asc"]]);
		
		$data = [
			"lines" => $lines,
			"main" => "module/ism_activity_management/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function create(){
		$lines = $this->gen_m->all("product_line", [["line", "asc"]]);
		
		$data = [
			"lines" => $lines,
			"main" => "module/ism_activity_management/create",
		];
		
		$this->load->view('layout', $data);
	}
}
