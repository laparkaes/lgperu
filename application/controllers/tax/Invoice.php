<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Invoice extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	
	public function index(){
		$data = [
			"main" => "tax/invoice/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function comparison(){
		set_time_limit(0);
		$start_time = microtime(true);
		
		$sheet_g = IOFactory::load("./test_files/tax_e_invoice/gerp 202404.xlsx")->getActiveSheet();
		
		$max_row = $sheet_g->getHighestRow();
		$max_col = $sheet_g->getHighestColumn();
		
		echo $max_row." ".$max_col;
		
		echo "<br/><br/><br/>";
		
		$sheet_p = IOFactory::load("./test_files/tax_e_invoice/paperless 202404.xlsx")->getActiveSheet();
		
		$max_row = $sheet_p->getHighestRow();
		$max_col = $sheet_p->getHighestColumn();
		
		echo $max_row." ".$max_col;
	}
}
