<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Employee extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('subsidiary_model', 'sub_m');
		$this->load->model('organization_model', 'org_m');
		$this->load->model('employee_model', 'emp_m');
		//$this->load->model('general_model','general');
		$this->nav_menu = ["hr", "employee"];
	}

	public function index(){
		$page = $this->input->get("page"); if (!$page) $page = 1;
		
		$subs = [];
		$subs_rec = $this->sub_m->all();
		foreach($subs_rec as $sub) $subs[$sub->subsidiary_id] = $sub->subsidiary;
		
		$orgs = [];
		$orgs_rec = $this->org_m->all();
		foreach($orgs_rec as $org) $orgs[$org->organization_id] = $org->organization;
		
		$employees = $this->emp_m->all([["name", "asc"]], 30, 30*($page-1));
		foreach($employees as $emp){
			$emp->subsidiary = $subs[$emp->subsidiary_id];
			$emp->organization = $orgs[$emp->organization_id];
		}
		
		$data = [
			"paging" => $this->my_func->set_page($page, $this->emp_m->qty()),
			"page" => $page,
			"employees" => $employees,
			"main" => "hr/employee/index",
		];
		$this->load->view('layout', $data);
	}
	
	public function upload_from_file(){
		$type = "error"; $msg = null;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'employee',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('md_uff_file')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();

			$count = 0;
            for ($row = 2; $row <= $highestRow; $row++){
				$sub = $this->sub_m->unique("subsidiary", trim($sheet->getCell('A'.$row)->getValue()));
				$org = $this->org_m->unique("organization", trim($sheet->getCell('B'.$row)->getValue()));
				
				if ($sub and $org){
					$emp = [
						"subsidiary_id" => $sub->subsidiary_id,
						"organization_id" => $org->organization_id,
						"employee_number" => trim($sheet->getCell('C'.$row)->getValue()),
						"name" => trim($sheet->getCell('D'.$row)->getValue()),
					];
					if (!$this->emp_m->unique("employee_number", $emp["employee_number"]))
						if ($this->emp_m->insert($emp)) $count++;	
				}
            }
			
			$type = "success";
			$msg = number_format($count)." new employee(s) has been inserted.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
