<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Vacation extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
		$this->load->model('subsidiary_model', 'sub_m');
		$this->load->model('organization_model', 'org_m');
		$this->load->model('employee_model', 'emp_m');
		$this->load->model('vacation_model', 'vac_m');
		$this->nav_menu = ["hr", "vacation"];
	}
	
	private function excel_date_to_php($value){
		return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
	}

	public function index(){
		$page = $this->input->get("page"); if (!$page) $page = 1;
		
		$subs = [];
		$subs_rec = $this->sub_m->all();
		foreach($subs_rec as $sub) $subs[$sub->subsidiary_id] = $sub->subsidiary;
		
		$orgs = [];
		$orgs_rec = $this->org_m->all();
		foreach($orgs_rec as $org) $orgs[$org->organization_id] = $org->organization;
		
		$emps = [];
		$emps_rec = $this->emp_m->all();
		foreach($emps_rec as $emp){
			$emp->subsidiary = $subs[$emp->subsidiary_id];
			$emp->organization = $orgs[$emp->organization_id];
			$emps[$emp->employee_id] = $emp;
		}
		
		$vac_status = [];
		$vac_status_rec = $this->vac_m->all_status();
		foreach($vac_status_rec as $s) $vac_status[$s->status_id] = $s->status;
		
		$vac_type = [];
		$vac_type_rec = $this->vac_m->all_type();
		foreach($vac_type_rec as $t) $vac_type[$t->type_id] = $t->type;
		
		$vacations = $this->vac_m->all([["date_from", "desc"]], 30, 30*($page-1));
		foreach($vacations as $vac){
			$vac->employee = $emps[$vac->employee_id];
			$vac->status = $vac_status[$vac->status_id];
			$vac->type = $vac_type[$vac->type_id];
		}
		
		$data = [
			"paging" => $this->my_func->set_page($page, $this->vac_m->qty()),
			"page" => $page,
			"vacations" => $vacations,
			"main" => "hr/vacation/index",
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
			'file_name'		=> 'vacation',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('md_uff_file')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
			$sheet->setCellValue('O1', 'Upload Result');
			$sheet->getStyle('O')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('O')->getFill()->getStartColor()->setARGB('FFFF00');
			
			$sheet->setCellValue('P1', 'Upload Time');
			$sheet->getStyle('P')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('P')->getFill()->getStartColor()->setARGB('FFFF00');
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();

			
			$status = $this->vac_m->unique_status("Approved");
			$count = 0;
            for ($row = 2; $row <= $highestRow; $row++){
				$emp = $this->emp_m->unique("employee_number", trim($sheet->getCell('C'.$row)->getValue()));
				if ($emp){
					$from = $this->excel_date_to_php($sheet->getCell('G'.$row)->getValue());
					$to = $this->excel_date_to_php($sheet->getCell('H'.$row)->getValue());
					
					$w = [
						"status_id" => $status->status_id,
						"employee_id" => $emp->employee_id,
						"date_from <=" => $from,
						"date_to >=" => $to,
					];
					
					if (!$this->gen_m->filter("vacation", true, $w)){
						$type = $this->vac_m->unique_type(str_replace(" (", "(", $sheet->getCell('J'.$row)->getValue()));
						
						$vac = [
							"status_id" => $status->status_id,
							"type_id" => $type->type_id,
							"employee_id" => $emp->employee_id,
							"date_from" => $from,
							"date_to" => $to,
							"day" => ($type->type === "All") ? $this->my_func->day_counter($from, $to) + 1 : 0.5,
							"request" => $this->excel_date_to_php($sheet->getCell('M'.$row)->getValue()),
						];
						if ($this->vac_m->insert($vac)){
							$count++;
							$sheet->setCellValue('O'.$row, 'Success');
							$sheet->setCellValue('P'.$row, date('Y-m-d H:i:s'));
						}
					}else{
						$sheet->setCellValue('O'.$row, 'Already Exists');
						$sheet->setCellValue('P'.$row, date('Y-m-d H:i:s'));
					}
				}else{
					$sheet->setCellValue('O'.$row, 'No Employee');
					$sheet->setCellValue('P'.$row, date('Y-m-d H:i:s'));
				}
            }
			
			$type = "success";
			$msg = number_format($count)." new vacation(s) has been inserted.";	
			
			$writer = new Xlsx($spreadsheet);
			$writer->save($file_path);
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
