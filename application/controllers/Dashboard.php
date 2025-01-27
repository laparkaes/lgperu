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
	
	public function new_project(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$o_emp = [
			["subsidiary", "asc"], 
			["organization", "asc"], 
			["department", "asc"],
			["name", "asc"],
			["employee_number", "asc"],
		];
		
		$data = [
			"employees"	=> $this->gen_m->filter("hr_employee", false, ["name !=" => "", "active" => true], null, null, $o_emp),
			"main"		=> "dashboard/new_project",
		];
		
		$this->load->view('layout', $data);
	}
	
}
