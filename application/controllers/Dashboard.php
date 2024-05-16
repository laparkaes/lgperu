<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Dashboard extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$data = [
			"main" => "dashboard/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function test(){
		//echo"hola";
		
		//$db_home = $this->load->database('sqlsvr_home', TRUE);
		$db_lg = $this->load->database('sqlsvr_lg', TRUE);
        //$query = $db_home->get('product'); // 'products' 테이블을 다른 데이터베이스에서 가져옴
        //$product = $query->result();
		
		echo phpinfo();
	}
}
