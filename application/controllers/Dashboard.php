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
		
		$access = [];
		$acc_recs = $this->gen_m->filter("sys_access", false, ["employee_id" => $this->session->userdata('employee_id')]);
		foreach($acc_recs as $item) $access[] = $item->module;
		
		$this->session->set_userdata("access", $access);
		
		$data = [
			"main" => "dashboard/index",
		];
		
		$this->load->view('layout', $data);
	}
}
