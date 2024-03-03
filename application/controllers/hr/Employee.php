<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Employee extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
		$this->load->model('subsidiary_model', 'sub_m');
		$this->load->model('organization_model', 'org_m');
		$this->load->model('employee_model', 'emp_m');
		$this->load->model('vacation_model', 'vac_m');
		$this->load->model('office_model', 'off_m');
		$this->load->model('working_hour_model', 'whour_m');
		$this->nav_menu = ["hr", "employee"];
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
		
		$offs = [];
		$offs_rec = $this->off_m->all();
		foreach($offs_rec as $off) $offs[$off->office_id] = $off->office;
		
		$employees = $this->emp_m->all([["name", "asc"]], 30, 30*($page-1));
		foreach($employees as $emp){
			$emp->subsidiary = ($emp->subsidiary_id) ? $subs[$emp->subsidiary_id] : "";
			$emp->organization = ($emp->organization_id) ? $orgs[$emp->organization_id] : "";
			$emp->office = ($emp->office_id) ? $offs[$emp->office_id] : "";
		}
		
		$data = [
			"paging" => $this->my_func->set_page($page, $this->emp_m->qty()),
			"page" => $page,
			"employees" => $employees,
			"main" => "hr/employee/index",
		];
		$this->load->view('layout', $data);
	}
	
	public function upload_employee_from_file(){
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
			
			$sheet->setCellValue('E1', 'Upload Result');
			$sheet->getStyle('E')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('E')->getFill()->getStartColor()->setARGB('FFFF00');
			
			$sheet->setCellValue('F1', 'Upload Time');
			$sheet->getStyle('F')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('F')->getFill()->getStartColor()->setARGB('FFFF00');
			
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
					if (!$this->emp_m->unique("employee_number", $emp["employee_number"])){
						if ($this->emp_m->insert($emp)){
							$count++;
							$sheet->setCellValue('E'.$row, 'Success');
							$sheet->setCellValue('F'.$row, date('Y-m-d H:i:s'));
						}	
					}else{
						$sheet->setCellValue('E'.$row, 'Duplicated');
						$sheet->setCellValue('F'.$row, date('Y-m-d H:i:s'));
					}
				}
            }
			
			$writer = new Xlsx($spreadsheet);
			$writer->save($file_path);
			
			$type = "success";
			$msg = number_format($count)." new employee(s) has been inserted.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function upload_vacation_from_file(){
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
			
			$status = $this->vac_m->unique_status("status", "Approved");
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
						$type = $this->vac_m->unique_type("type", str_replace(" (", "(", $sheet->getCell('J'.$row)->getValue()));
						
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
						$sheet->setCellValue('O'.$row, 'Duplicated');
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

	public function upload_w_hour_from_file(){
		$type = "error"; $msg = null;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'working_hour',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('md_uff_file')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
			$sheet->setCellValue('D1', 'Upload Result');
			$sheet->getStyle('D')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('D')->getFill()->getStartColor()->setARGB('FFFF00');
			
			$sheet->setCellValue('E1', 'Upload Time');
			$sheet->getStyle('E')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('E')->getFill()->getStartColor()->setARGB('FFFF00');
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();
			
			$count = 0;
            for ($row = 2; $row <= $highestRow; $row++){
				$sheet->setCellValue('E'.$row, date('Y-m-d H:i:s'));
				$emp = $this->emp_m->unique("employee_number", trim($sheet->getCell('A'.$row)->getValue()));
				if ($emp){
					//update employee office
					$off = $this->off_m->unique("office", trim($sheet->getCell('B'.$row)->getValue()));
					if ($off){
						$this->emp_m->update(["employee_id" => $emp->employee_id], ["office_id" => $off->office_id]);
						$sheet->setCellValue('D'.$row, 'Office Updated');
					}
					
					//update_working hour
					$w_hours = explode(" - ", trim($sheet->getCell('C'.$row)->getValue()));
					$w_hour_op = $this->whour_m->filter_option($w_hours[0], $w_hours[1]);
					if ($w_hour_op){
						
						$today = date("Y-m-d");
						$tomorrow = date('Y-m-d', strtotime('+1 day'));
						
						$insert_new = true;
						$w_hour_tomorrow = $this->whour_m->get_by_employee($emp->employee_id, $tomorrow);
						if ($w_hour_tomorrow){
							$date_from = $tomorrow;//change will be apply from tomorrow
							
							//this change is exclusive for working hour change
							if ($w_hour_tomorrow->wh_option_id == $w_hour_op->option_id){
								$insert_new = false;
								$sheet->setCellValue('D'.$row, 'Success - No change');
							}else $this->whour_m->update(["employee_id" => $emp->employee_id], ["date_to" => $today]);
						}else $date_from = "1000-01-01";//in case of first working hour record
						
						if ($insert_new){
							//new working hour record form tomorrow to 9999-12-31
							$w_hour = [
								"employee_id" => $emp->employee_id,
								"wh_option_id" => $w_hour_op->option_id,
								"date_from" => $date_from,
								"date_to" => "9999-12-31",
							];
							if ($this->whour_m->insert($w_hour)){
								$count++;
								$sheet->setCellValue('D'.$row, 'Success');
							}
						}
					}else $sheet->setCellValue('D'.$row, 'Error - No working hour option');
				}else $sheet->setCellValue('D'.$row, 'Error - No Employee');
            }
			
			$type = "success";
			$msg = "Working hours has been updated.";
			
			$writer = new Xlsx($spreadsheet);
			$writer->save($file_path);
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
