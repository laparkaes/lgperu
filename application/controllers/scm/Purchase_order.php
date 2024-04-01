<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//use PhpOffice\PhpSpreadsheet\IOFactory;

class Purchase_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->color_rgb = [
			"green" => "198754",
			"red" => "dc3545",
		];
	}
	
	public function index(){
		
		$this->load->library('my_pdf');
		
		$txt = $this->my_pdf->to_text("./test_files/scm/test.pdf");
		
		
		var_dump($txt);
		echo "<br/><br/><br/>";
		echo $txt;
		
		$data = [
			"main" => "sa/sell_inout/index",
		];
		
		//$this->load->view('layout', $data);
	}
}
