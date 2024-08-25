<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Hr_employee extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
		$this->load->model('vacation_model', 'vac_m');
		$this->load->model('working_hour_model', 'whour_m');
	}

	public function index(){
		$employees = $this->gen_m->filter("hr_employee", false, null, null, null, [["subsidiary", "asc"], ["organization", "asc"], ["department", "asc"], ["name", "asc"]]);
		
		$data = [
			"employees" => $employees,
			"main" => "module/hr_employee/index",
		];
		$this->load->view('layout', $data);
	}

	public function edit($employee_id){
		$employee = $this->gen_m->unique("hr_employee", "employee_id", $employee_id, false);
		
		$data = [
			"employee" => $employee,
			"main" => "module/hr_employee/edit",
		];
		$this->load->view('layout', $data);
		//print_r($employee);
	}
}
