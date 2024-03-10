<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
		$this->color_rgb = [
			"red" => "dc3545",
		];
	}
	
	public function set_attendance($period = null, $employee_id = null){
		if (!$period) $period = date("Y-m");
		$period = "2024-02";
		$month = date("F", strtotime($period));
		
		//set dates
		$red_days = ["Sat", "Sun"];//red calendar days
		$red_dates = [];//red dates in array
		$dates = [];//all dates of month
		$headers = [];//day, day of the week
		
		$start = new DateTime(date('Y-m-01', strtotime($period)));
		$last = new DateTime(date('Y-m-t', strtotime($period)));
		$interval = new DateInterval('P1D');
		
		$now = clone $start;
		while ($now <= $last) {
			$dates[] = $now->format('Y-m-d');
			if (in_array($now->format('D'), $red_days)){
				$red_dates[] = $now->format('Y-m-d');
				$type = "H";//holiday
			}else $type = "N";//normal
			
			$headers[] = ["day" => $now->format('d'), "day_w" => $now->format('D'), "type" => $type];
			$now->add($interval);
		}
		
		echo $period; echo "<br/><br/>";
		print_r($dates); echo "<br/><br/>";
		print_r($red_dates); echo "<br/><br/>";
		print_r($headers); echo "<br/><br/>";
	}
	
	private function set_mapping($period){
		if (!$period) $period = date("Y-m");
		
		//set header
		$month = date("F", strtotime($period));
		
		$day_red = ["Sat", "Sun"];//red calendar days
		$headers = [];//day, day of week, header color
		$dates = [];//all dates of month
		$dates_red = [];//red dates array
		
		$start = new DateTime(date('Y-m-01', strtotime($period)));
		$last = new DateTime(date('Y-m-t', strtotime($period)));
		$interval = new DateInterval('P1D');
		
		$now = clone $start;
		while ($now <= $last) {
			$dates[] = $now->format('Y-m-d');
			if (in_array($now->format('D'), $day_red)){
				$dates_red[] = $now->format('Y-m-d');
				$type = "H";//holiday
			}else $type = "N";//normal
			
			$headers[] = ["day" => $now->format('d'), "w_day" => $now->format('D'), "type" => $type];
			$now->add($interval);
		}
		
		//load all employees
		$employees = $this->emp_m->all([["name", "asc"]]);
		
		//start vacations
		$w = [
			"date_from <" => date('Y-m-01', strtotime($period)),
			"date_to >=" => date('Y-m-01', strtotime($period)),
			"date_to <=" =>date('Y-m-t', strtotime($period))
		];
		$vacations_t = $this->gen_m->filter("vacation", true, $w, null, null, [["date_to", "asc"]]);
		
		$w = [
			"date_from >=" => date('Y-m-01', strtotime($period)),
			"date_from <=" =>date('Y-m-t', strtotime($period))
		];
		$vacations_f = $this->gen_m->filter("vacation", true, $w, null, null, [["date_from", "asc"]]);
		
		$vacation_emps = [];
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
		}//end vacations
		
		//employee subsidiary, organization, office variable set
		$subs = []; $subs_rec = $this->sub_m->all();
		foreach($subs_rec as $sub) $subs[$sub->subsidiary_id] = $sub->subsidiary;
		
		$orgs = []; $orgs_rec = $this->org_m->all();
		foreach($orgs_rec as $org) $orgs[$org->organization_id] = $org->organization;
		
		$offs = []; $offs_rec = $this->off_m->all();
		foreach($offs_rec as $off) $offs[$off->office_id] = $off->office;
		
		//set mapping for save daily information
		$summary = $mapping = [];
		foreach($employees as $key => $emp){
			$emp->subsidiary = ($emp->subsidiary_id) ? $subs[$emp->subsidiary_id] : "";
			$emp->organization = ($emp->organization_id) ? $orgs[$emp->organization_id] : "";
			$emp->office = ($emp->office_id) ? $offs[$emp->office_id] : "";
			
			$summary[$emp->employee_id] = [
				"abs" => 0,//absence qty
				"tar" => 0,//tardiness qty
				"tar_acc" => "00:00",//tardiness accumulate hour
				"ove" => 0,//overtime qty
				"ove_acc" => "00:00",//overtime accumulate hour
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
			foreach($dates as $idate => $d){
				$mapping[$emp->employee_id][$d] = ["type" => null];
				
				//check if actual day require update working hour
				if ($whour) if ($whour->date_to < $d){
					$whour_f["date_from <="] = $whour_f["date_to >="] = $d;
					$whour = $this->gen_m->filter("working_hour", true, $whour_f);
					if ($whour){
						$whour = $whour[0];
						$whour_op = $this->whour_m->unique_option("option_id", $whour->wh_option_id);
					}
				}
				
				/*
				access check types
				T: tardance
				E: early exit
				
				daily attendance types
				N: normal
				X: no mark
				H: holiday
				V: vacation
				M: medical
				*/
				
				$att = $this->gen_m->filter("attendance", true, ["employee_id" => $emp->employee_id, "date" => $d]);
				if ($att){//attendance record exists
					$mapping[$emp->employee_id][$d]["type"] = "N";
				
					$has_att = true;
					$att = $att[0];
					$mapping[$emp->employee_id][$d]["e"] = ["time" => $att->enter_time, "type" => ""];//enter
					$mapping[$emp->employee_id][$d]["l"] = ["time" => $att->leave_time, "type" => ""];//leave
					
					if (($whour_op) and ($headers[$idate]["type"] !== "H")){//working hour record exists => evaluate if tardiness
						$wo_e = strtotime(date("H:i", strtotime($whour_op->entrance_time)));
						$wo_l = strtotime(date("H:i", strtotime($whour_op->exit_time)));
						
						$at_e = strtotime(date("H:i", strtotime($att->enter_time)));
						$at_l = strtotime(date("H:i", strtotime($att->leave_time)));
						
						$diff_e = $at_e - $wo_e;
						$diff_l = $at_l - $wo_l;
					
						if (0 < $diff_e){//tardance
							$mapping[$emp->employee_id][$d]["e"]["type"] = "T";
							$summary[$emp->employee_id]["tar"]++;
							$summary[$emp->employee_id]["tar_acc"] = "1:00";
						}
							
						if ($diff_l < 0){//early exit
							$mapping[$emp->employee_id][$d]["l"]["type"] = "E";
						}else{//overtime
							$summary[$emp->employee_id]["ove"]++;
							$summary[$emp->employee_id]["ove_acc"] = "1:00";
						}
					}
				}else{//attendance record no exists
					if ($headers[$idate]["type"] !== "H"){
						//No mark is default
						$mapping[$emp->employee_id][$d]["type"] = "X";
						$mapping[$emp->employee_id][$d]["e"] = ["time" => null, "type" => ""];//enter
						$mapping[$emp->employee_id][$d]["l"] = ["time" => null, "type" => ""];//leave
						
						//vacation
						if (array_key_exists($emp->employee_id, $vacation_emps)){
							if (in_array($d, $vacation_emps[$emp->employee_id])) 
								$mapping[$emp->employee_id][$d]["type"] = "V";
						}
					}
					
					//else 
				
					/*
					1. check if vacation exists
					2. check if coorporation event exists
					*/
				}
			}
			
			if (!$has_att) unset($employees[$key]);
		}
		
		//employee array key clean working
		$emp_arr = [];
		foreach($employees as $e) $emp_arr[] = clone $e;
		
		$data = [
			"month" => $month,
			"headers" => $headers,
			"dates" => $dates,
			"dates_red" => $dates_red,
			"employees" => $emp_arr,
			"summary" => $summary,
			"mapping" => $mapping,
			"vacation_emps" => $vacation_emps,
		];
		
		return $data;
	}
	
	private function columnIndexToLetters($index) {
		$letters = '';
		while ($index > 0) {
			$index--; // 1부터 시작하도록 감소
			$letters = chr($index % 26 + 65) . $letters; // ASCII 코드를 문자로 변환하여 문자열에 추가
			$index = intval($index / 26); // 다음 자리수 계산
		}
		return $letters;
	}

	public function index(){
		$ref_date = "2024-02";
		
		$data = $this->set_mapping($ref_date);
		$data["main"] = "hr/attendance/index";
		
		$this->load->view('layout', $data);
	}
	
	public function export_monthly_report(){
		$type = "error"; $msg = null; $url = "aaa";
		
		$period = $this->input->post("period");
		if (!$period) $period = date("Y-m");
		
		$data = $this->set_mapping($period);
		
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		
		//set report parameters
		$sheet->setCellValueByColumnAndRow(1, 1, "Attendance Monthly Report");
		$sheet->setCellValueByColumnAndRow(1, 2, "Period");
		$sheet->setCellValueByColumnAndRow(2, 2, $period);
		$sheet->setCellValueByColumnAndRow(1, 3, "Created");
		$sheet->setCellValueByColumnAndRow(2, 3, date('Y-m-d H:i:s'));
		
		//set headers
		$sheet->setCellValueByColumnAndRow(1, 5, 'Num');
		$sheet->setCellValueByColumnAndRow(2, 5, 'Employee');
		$sheet->setCellValueByColumnAndRow(3, 5, 'Code');
		$sheet->setCellValueByColumnAndRow(4, 5, 'Location');
		$sheet->setCellValueByColumnAndRow(5, 5, 'Subsidiary');
		$sheet->setCellValueByColumnAndRow(6, 5, 'Organization');
		$sheet->setCellValueByColumnAndRow(7, 5, 'Vacation');
		$sheet->setCellValueByColumnAndRow(8, 5, 'Tardiness');
		$sheet->setCellValueByColumnAndRow(9, 5, 'Overtime');
		$sheet->setCellValueByColumnAndRow(10, 5, 'Absence');
		
		$x_start = 11;
		
		$headers = $data["headers"];
		foreach($headers as $i => $h){
			$x = $x_start + $i;
			
			$sheet->setCellValueByColumnAndRow($x, 5, $h["day"]." ".$h["w_day"]);
			//$sheet->getStyleByColumnAndRow($x, 5)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
			
			if ($h["type"] === "H") $sheet->getStyleByColumnAndRow($x, 5)->getFont()->setColor(new Color($this->color_rgb["red"]));
		}
		
		$v_center = Alignment::VERTICAL_CENTER;
		
		$summary = $data["summary"];
		$vacation_emps = $data["vacation_emps"];
		$dates = $data["dates"];
		$mapping = $data["mapping"];
		$employees = $data["employees"];//employee info from row 6
		foreach($employees as $i => $emp){
			$y = ($i * 2) + 6;
			$sheet->setCellValueByColumnAndRow(1, $y, $i + 1);
			$sheet->setCellValueByColumnAndRow(2, $y, $emp->name);
			$sheet->setCellValueByColumnAndRow(3, $y, $emp->employee_number);
			$sheet->setCellValueByColumnAndRow(4, $y, $emp->office);
			$sheet->setCellValueByColumnAndRow(5, $y, $emp->subsidiary);
			$sheet->setCellValueByColumnAndRow(6, $y, $emp->organization);
			
			$sheet->setCellValueByColumnAndRow(8, $y, $summary[$emp->employee_id]["tar"]." / ".$summary[$emp->employee_id]["tar_acc"]);
			$sheet->setCellValueByColumnAndRow(9, $y, $summary[$emp->employee_id]["ove"]." / ".$summary[$emp->employee_id]["ove_acc"]);
			
			for($c = 1; $c < $x_start; $c++){
				$cl = $this->columnIndexToLetters($c);
				$sheet->mergeCells($cl.$y.':'.$cl.($y + 1));
				
				$sheet->getStyle($cl.$y)->getAlignment()->setVertical($v_center);
			}
			
			foreach($dates as $idate => $d){
				$x = $x_start + $idate;
				$xl = $this->columnIndexToLetters($x);
				
				$aux = $mapping[$emp->employee_id][$d];
				
				if ($aux["type"] === "N"){
					
					$sheet->setCellValueByColumnAndRow($x, $y, $aux["type"]);
					$sheet->setCellValueByColumnAndRow($x, $y + 1, $aux["type"]);
					
					if ($aux["e"] or $aux["l"]){
						$sheet->setCellValueByColumnAndRow($x, $y, date("H:i", strtotime($aux["e"]["time"])));
						$sheet->setCellValueByColumnAndRow($x, $y + 1, date("H:i", strtotime($aux["l"]["time"])));
						
						$color_e = ($aux["e"]["type"] === "T") ? $this->color_rgb["red"] : "";
						$color_l = ($aux["l"]["type"] === "E") ? $this->color_rgb["red"] : "";
						
						$sheet->getStyleByColumnAndRow($x, $y)->getFont()->setColor(new Color($color_e));
						$sheet->getStyleByColumnAndRow($x, $y + 1)->getFont()->setColor(new Color($color_l));
					}else{//no mark
						$sheet->setCellValueByColumnAndRow($x, $y, "No mark");
						$sheet->mergeCells($xl.$y.':'.$xl.($y + 1));
						$sheet->getStyle($xl.$y)->getAlignment()->setVertical($v_center);
					}
				}elseif ($aux["type"]){
					$sheet->setCellValueByColumnAndRow($x, $y, $aux["type"]);
					$sheet->mergeCells($xl.$y.':'.$xl.($y + 1));
					$sheet->getStyle($xl.$y)->getAlignment()->setVertical($v_center);
				}
				
				
				/*
				if ($headers[$idate]["color_bt"] !== "danger"){
					if (in_array($d, $vacation_emps[$emp->employee_id])){
						
					}else{
						if (array_key_exists("time", $aux["e"]) or array_key_exists("time", $aux["l"])){
							
						}else{
							$sheet->setCellValueByColumnAndRow($x, $y, "N");
							$sheet->mergeCells($xl.$y.':'.$xl.($y + 1));
							$sheet->getStyle($xl.$y)->getAlignment()->setVertical($v_center);
						}
					}
				}
				*/
			}
		}
		
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
