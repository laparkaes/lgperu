<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Attendance extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->load->model('subsidiary_model', 'sub_m');
		$this->load->model('organization_model', 'org_m');
		$this->load->model('employee_model', 'emp_m');
		$this->load->model('vacation_model', 'vac_m');
		$this->load->model('office_model', 'off_m');
		$this->load->model('working_hour_model', 'whour_m');
		$this->load->model('attendance_model', 'att_m');
		$this->nav_menu = ["hr", "attendance"];
	}
	
	private function set_mapping($ref_date){
		if (!$ref_date) $ref_date = date("Y-m-d");
		
		$headers = [];//save days and week days for table header
		$employees = $this->emp_m->all([["name", "asc"]]);
		
		$month = date("F", strtotime($ref_date));
		
		$start = new DateTime(date('Y-m-01', strtotime($ref_date)));
		$last = new DateTime(date('Y-m-t', strtotime($ref_date)));
		
		$day_red = ["Sat", "Sun"];
		$dates = [];
		$interval = new DateInterval('P1D');
		$now = clone $start;
		while ($now <= $last) {
			$aux = ["day" => $now->format('d'), "w_day" => $now->format('D'), "color" => ""];
			
			$dates[] = $now->format('Y-m-d');
			if (in_array($now->format('D'), $day_red)){
				$dates_red[] = $now->format('Y-m-d');
				$aux["color"] = "danger";
			}
			
			$headers[] = $aux;
			$now->add($interval);
		}
		
		//employee subsidiary, organization, office variable set
		$subs = []; $subs_rec = $this->sub_m->all();
		foreach($subs_rec as $sub) $subs[$sub->subsidiary_id] = $sub->subsidiary;
		
		$orgs = []; $orgs_rec = $this->org_m->all();
		foreach($orgs_rec as $org) $orgs[$org->organization_id] = $org->organization;
		
		$offs = []; $offs_rec = $this->off_m->all();
		foreach($offs_rec as $off) $offs[$off->office_id] = $off->office;
		
		//set mapping for save daily information
		$summary = $mapping = $vacation_emps = [];
		foreach($employees as $key => $emp){
			//basic vacation array
			$vacation_emps[$emp->employee_id] = [];
			
			$emp->subsidiary = ($emp->subsidiary_id) ? $subs[$emp->subsidiary_id] : "";
			$emp->organization = ($emp->organization_id) ? $orgs[$emp->organization_id] : "";
			$emp->office = ($emp->office_id) ? $offs[$emp->office_id] : "";
			
			$summary[$emp->employee_id] = [
				"abs" => 0,//absence qty
				"tar" => 0,//tardiness qty
				"vac" => 0,//vacacion qty
			];
			
			//daily check data array => [date][enter/leave] = [time, color]
			$mapping[$emp->employee_id] = [];
			
			$whour_op = null;
			$whour_f = [
				"employee_id" => $emp->employee_id,
				"date_from <=" => $dates[0],
				"date_to >=" => $dates[0],
			];
			$whour = $this->gen_m->filter("working_hour", true, $whour_f);
			if ($whour){
				$whour = $whour[0];
				$whour_op = $this->whour_m->unique_option("option_id", $whour->wh_option_id);
			}
			
			$has_att = false;
			foreach($dates as $d){
				$mapping[$emp->employee_id][$d] = ["e" => [], "l" => []];
				
				//check if actual day require update working hour
				if ($whour) if ($whour->date_to < $d){
					$whour_f["date_from <="] = $whour_f["date_to >="] = $d;
					$whour = $this->gen_m->filter("working_hour", true, $whour_f);
					if ($whour){
						$whour = $whour[0];
						$whour_op = $this->whour_m->unique_option("option_id", $whour->wh_option_id);
					}
				}
				
				$att = $this->gen_m->filter("attendance", true, ["employee_id" => $emp->employee_id, "date" => $d]);
				if ($att){//attendance record exists
					$has_att = true;
					$att = $att[0];
					$mapping[$emp->employee_id][$d]["e"] = ["time" => $att->enter_time, "color" => ""];//enter
					$mapping[$emp->employee_id][$d]["l"] = ["time" => $att->leave_time, "color" => ""];//leave
					
					if ($whour_op){//working hour record exists => evaluate if tardiness
						if (strtotime($whour_op->entrance_time) < strtotime($att->enter_time))
							$mapping[$emp->employee_id][$d]["e"]["color"] = "danger";
						
						if (strtotime($att->leave_time) < strtotime($whour_op->exit_time))
							$mapping[$emp->employee_id][$d]["l"]["color"] = "danger";
					}
				}else{//attendance record no exists
					/*
					1. check if vacation exists
					2. check if coorporation event exists
					*/
				}
			}
			
			if (!$has_att) unset($employees[$key]);
		}
		
		//start vacations
		$w = [
			"date_from <" => date('Y-m-01', strtotime($ref_date)),
			"date_to >=" => date('Y-m-01', strtotime($ref_date)),
			"date_to <=" =>date('Y-m-t', strtotime($ref_date))
		];
		$vacations_t = $this->gen_m->filter("vacation", true, $w, null, null, [["date_to", "asc"]]);
		
		$w = [
			"date_from >=" => date('Y-m-01', strtotime($ref_date)),
			"date_from <=" =>date('Y-m-t', strtotime($ref_date))
		];
		$vacations_f = $this->gen_m->filter("vacation", true, $w, null, null, [["date_from", "asc"]]);
		
		$vacations = array_merge($vacations_t, $vacations_f);
		foreach($vacations as $vac){
			$start = new DateTime($vac->date_from);
			$last = new DateTime($vac->date_to);
			$interval = new DateInterval('P1D');//each one day
			
			$now = clone $start;
			while ($now <= $last) {
				$vacation_emps[$vac->employee_id][] = $now->format('Y-m-d');
				$now->add($interval);
			}
		}
		
		$data = [
			"month" => $month,
			"headers" => $headers,
			"dates" => $dates,
			"dates_red" => $dates_red,
			"employees" => $employees,
			"summary" => $summary,
			"mapping" => $mapping,
			"vacation_emps" => $vacation_emps,
		];
		
		return $data;
	}

	public function index(){
		$ref_date = "2024-02";
		
		$data = $this->set_mapping($ref_date);
		$data["main"] = "hr/attendance/index";
		
		$this->load->view('layout', $data);
	}
	
	public function export_monthly_report(){
		$type = "error"; $msg = null; $url = "aaa";
		
		// Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set active sheet
        $sheet = $spreadsheet->getActiveSheet();

        // Add data to the cells
        $sheet->setCellValue('A1', 'Hello');
        $sheet->setCellValue('B1', 'World!');

        // Save Excel file to a temporary directory
		$file_name = 'attandance_202402.xlsx';
        $file_path = './upload/report/';
        $writer = new Xlsx($spreadsheet);
        $writer->save($file_path.$file_name);
		
		// Make file url
		if (file_exists($file_path)){
			$type = "success";
			$url = base_url()."upload/report/".$file_name;
		}else $msg = "An error occured exporting report. Try again.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	public function upload_device_file(){
		$type = "error"; $msg = null;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'attendance',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('md_uff_file')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
			/*
			$sheet->setCellValue('B1', 'Upload Result');
			$sheet->getStyle('B')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('B')->getFill()->getStartColor()->setARGB('FFFF00');
			
			$sheet->setCellValue('C1', 'Upload Time');
			$sheet->getStyle('C')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('C')->getFill()->getStartColor()->setARGB('FFFF00');
			*/
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();

			$new_rec = $upd_rec = [];
			$atts = [];
			$count = 0;
            for ($row = 2; $row <= $highestRow; $row++){
				//datas are joined with comma (,)
				//$row_now = explode(",", trim($sheet->getCell('A'.$row)->getValue()));
				
				//datas are separated in columns
				$row_now = [
					trim($sheet->getCell('A'.$row)->getValue()),
					trim($sheet->getCell('B'.$row)->getValue()),
					trim($sheet->getCell('C'.$row)->getValue()),
					trim($sheet->getCell('D'.$row)->getValue()),
					trim($sheet->getCell('E'.$row)->getValue()),
					trim($sheet->getCell('F'.$row)->getValue()),
					trim($sheet->getCell('G'.$row)->getValue()),
				];
				
				if ($row_now[5]){
					$aux = explode("(", str_replace(")", "", $row_now[5]));
					if (array_key_exists(1, $aux)){
						$date_split = explode(" ", $row_now[0]);
						
						/*
						$row_now[0]: check date
						$date_split[0]: check day
						$date_split[1]: check time
						$aux[0]: employee_number
						$aux[1]: name
						*/
						
						if (!array_key_exists($aux[0], $atts))
							$atts[$aux[0]] = ["name" => "", "check" => []];
						
						if (!array_key_exists($date_split[0], $atts[$aux[0]]["check"])) 
							$atts[$aux[0]]["check"][$date_split[0]] = [];
						
						$atts[$aux[0]]["name"] = $aux[1];
						$atts[$aux[0]]["check"][$date_split[0]][] = strtotime($date_split[1]);
						$atts[$aux[0]]["check"][$date_split[0]][] = strtotime($date_split[1]);
					}
				}
            }
			
			foreach($atts as $emp_num => $emp){
				$emp_rec = $this->emp_m->unique("employee_number", $emp_num);
				if (!$emp_rec){
					$this->emp_m->insert(["employee_number" => $emp_num, "name" => $emp["name"]]);
					$emp_rec = $this->emp_m->unique("employee_number", $emp_num);
				}
				
				$checks = $emp["check"];
				foreach($checks as $day => $times){
					if ($times){
						sort($times);
						
						$f = ["employee_id" => $emp_rec->employee_id, "date" => $day];
						$att_data = [
							"employee_id" => $emp_rec->employee_id,
							"date" => $day,
							"enter_time" => date("H:i", $times[0]),
							"leave_time" => date("H:i",$times[count($times) - 1]),
						];
						
						$att_rec = $this->gen_m->filter("attendance", true, $f);
						if ($att_rec){
							$att_data["attendance_id"] = $att_rec[0]->attendance_id;
							$upd_rec[] = $att_data;
						}else $new_rec[] = $att_data;
					}
				}
			}
			
			$new_qty = ($new_rec) ? $this->att_m->insert_m($new_rec) : 0;
			$upd_qty = ($upd_rec) ? $this->att_m->update_m($upd_rec) : 0;
			
			$type = "success";
			$msg = "Check-in time upload result: ".number_format($new_qty)." new and ".number_format($upd_qty)." updated.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
