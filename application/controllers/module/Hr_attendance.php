<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Hr_attendance extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$period = "2024-08";
		
		$w = [
			"work_date >=" => date("Y-m-01", strtotime($period)),
			"work_date <=" => date("Y-m-t", strtotime($period)),
		];
		
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w);
		echo $this->db->last_query();
		
		print_r($records);
		
		//$data = $this->set_attendance($period);
		$data = [
			"main" => "module/hr_attendance/index", 
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload(){
		$type = "error"; $msg = null; $inserted = 0;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'hr_attendance',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
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

			$rows = [];
            for ($j = 2; $j <= $highestRow; $j++){
				//datas are separated in columns
				$row = [
					trim($sheet->getCell('A'.$j)->getValue()),
					trim($sheet->getCell('B'.$j)->getValue()),
					trim($sheet->getCell('C'.$j)->getValue()),
					trim($sheet->getCell('D'.$j)->getValue()),
					trim($sheet->getCell('E'.$j)->getValue()),
					trim($sheet->getCell('F'.$j)->getValue()),
					trim($sheet->getCell('G'.$j)->getValue()),
				];
				
				if ($row[5]){
					$aux = explode("(", str_replace(")", "", $row[5]));
					//print_r($aux); echo "<br/>";
					
					$rows[] = [
						"pr" => $aux[0],
						"name" => (array_key_exists(1, $aux) ? $aux[1] : ""),
						"access" => $row[0],
					];
					
				}
            }
			
			if ($rows){
				$this->gen_m->delete("hr_attendance", ["access >=" => $rows[count($rows)-1]["access"], "access <=" => $rows[0]["access"]]);
				$inserted = $this->gen_m->insert_m("hr_attendance", $rows); 
			}
			
			$type = "success";
			$msg = number_format($inserted)." rows inserted.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
		
	
	public function set_attendance($period = null, $employee_id = null){
		if (!$period) $period = date("Y-m");
		$month = date("F", strtotime($period));
		$today = date("Y-m-d");
		
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
		
		//employee subsidiary, organization, department and office variable set
		$locs = [];
		$locs_rec = $this->gen_m->all("location");
		foreach($locs_rec as $l) $locs[$l->location_id] = $l;
		
		$depts = [];
		$depts_rec = $this->gen_m->all("department");
		foreach($depts_rec as $d) $depts[$d->department_id] = $d;
		
		$orgs = [];
		$orgs_rec = $this->gen_m->all("organization");
		foreach($orgs_rec as $o) $orgs[$o->organization_id] = $o;
		
		$subs = [];
		$subs_rec = $this->gen_m->all("subsidiary");
		foreach($subs_rec as $s) $subs[$s->subsidiary_id] = $s;
		
		//set employee array
		$employees = [];
		
		if ($employee_id) $w = ["employee_id" => $employee_id]; else $w = null;
		$employees_rec = $this->gen_m->filter("employee", true, $w, null, null, [["name", "asc"]]);
		foreach($employees_rec as $emp){
			$w = [
				"employee_id" => $emp->employee_id,
				"date>=" => $dates[0],
				"date<=" => $dates[count($dates)-1],
			];
			$atts = $this->gen_m->filter("attendance", true, $w, null, null, [["date", "asc"]]);
			if ($atts){
				$emp->location = ($emp->location_id) ? $locs[$emp->location_id]->location : "";
				if ($emp->department_id){
					$emp->department = $depts[$emp->department_id]->department;
					$emp->organization = $orgs[$depts[$emp->department_id]->organization_id]->organization;
					$emp->subsidiary = $subs[$orgs[$depts[$emp->department_id]->organization_id]->subsidiary_id]->subsidiary;
				}else $emp->department = $emp->organization = $emp->subsidiary = "";
				
				$emp->vacation_qty = 0;
				$emp->absence_qty = 0;
				$emp->tardiness_qty = 0;
				$emp->early_exit_qty = 0;
				$emp->tardiness_acc = "00:00";
				
				//set vacation date array
				$w = [
					"employee_id" => $emp->employee_id,
					"date_from <" => date('Y-m-01', strtotime($period)),
					"date_to >=" => date('Y-m-01', strtotime($period)),
					"date_to <=" =>date('Y-m-t', strtotime($period))
				];
				$vacations_t = $this->gen_m->filter("vacation", true, $w, null, null, [["date_to", "asc"]]);
				
				$w = [
					"employee_id" => $emp->employee_id,
					"date_from >=" => date('Y-m-01', strtotime($period)),
					"date_from <=" =>date('Y-m-t', strtotime($period))
				];
				$vacations_f = $this->gen_m->filter("vacation", true, $w, null, null, [["date_from", "asc"]]);
				
				$vacation_dates = []; $vacation_exception = [];
				$vacations = array_merge($vacations_t, $vacations_f);
				foreach($vacations as $vac){
					if ($vac->day_count < 1){
						$type = $this->gen_m->unique("vacation_type", "type_id", $vac->type_id);
						
						//$vacation_exception["entrance", "exit"] as time in string
						if (strpos($type->type, 'Morning') !== false) //half day - morning: entrance is 2pm
							$vacation_exception[$vac->date_from] = ["14:00", null];
						elseif (strpos($type->type, 'Afternoon') !== false) //half day - afternoon: exit is 2pm
							$vacation_exception[$vac->date_from] = [null, "12:30"];
					}else{
						$start = new DateTime($vac->date_from);
						$last = new DateTime($vac->date_to);
						$interval = new DateInterval('P1D');//each one day
						
						$now = clone $start;
						while ($now <= $last) {
							if (!in_array($d, $red_dates)) $vacation_dates[] = $now->format('Y-m-d');
							$now->add($interval);
						}	
					}
				}//end vacations
				
				/*
				daily attendance types
				N: normal
				X: no mark(absence)
				H: holiday
				V: vacation
				M: medical
				*/
				
				//set daily check list as no mark for all working days
				$emp->daily = [];
				foreach($dates as $d){
					if (in_array($d, $red_dates)) $emp->daily[$d] = ["type" => "H"];//holiday
					elseif (in_array($d, $vacation_dates)){
						$emp->daily[$d] = ["type" => "V"];//vacation
					}elseif (strtotime($d) < strtotime($today)){
						$emp->daily[$d] = ["type" => "X"];//no mark
					}else $emp->daily[$d] = ["type" => ""];//not yet
				}
				
				//load work hour and option records
				$w = [
					"employee_id" => $emp->employee_id,
					"date_from <=" => $dates[0],
					"date_to >=" => $dates[0],
				];
				
				$whour = $this->gen_m->filter("working_hour", true, $w);
				if ($whour){
					$whour = $whour[0];
					$whour_op = $this->gen_m->unique("working_hour_option", "option_id", $whour->wh_option_id);
				}else $whour_op = null;
				
				foreach($atts as $att){
					//update working hour option when out of range
					if ($whour) if (strtotime($whour->date_to) < strtotime($att->date)){
						$w = [
							"employee_id" => $emp->employee_id,
							"date_from <=" => $att->date,
							"date_to >=" => $att->date,
						];
						
						$whour = $this->gen_m->filter("working_hour", true, $w);
						if ($whour){
							$whour = $whour[0];
							$whour_op = $this->gen_m->unique("working_hour_option", "option_id", $whour->wh_option_id);
						}else $whour_op = null;
					}
					
					/*
					access check types
					O: ok
					T: tardance
					E: early exit
					*/
					
					$emp->daily[$att->date] = [
						"type" => "N",
						"entrance" => ["time" => $att->enter_time, "result" => "O"],
						"exit" => ["time" => $att->leave_time, "result" => "O"],
					];
					
					if ($whour_op){
						$wo_e = strtotime(date("H:i", strtotime($whour_op->entrance_time)));
						$wo_l = strtotime(date("H:i", strtotime($whour_op->exit_time)));
						
						$at_e = strtotime(date("H:i", strtotime($att->enter_time)));
						$at_l = strtotime(date("H:i", strtotime($att->leave_time)));
						
						$no_holiday = (!in_array($att->date, $red_dates));
						
						if (($at_e > $wo_e) and ($no_holiday)){//tardance
							$emp->daily[$att->date]["entrance"]["result"] = "T";
							
							//need to check if emp has entrance exception
							if (array_key_exists($att->date, $vacation_exception)){
								if (array_key_exists(0, $vacation_exception[$att->date])){
									$wo_e = strtotime(date("H:i", strtotime($vacation_exception[$att->date][0])));
									if ($at_e < $wo_e){
										$emp->daily[$att->date]["entrance"]["result"] = "V";
									}
								}
							}
							
							if ($emp->daily[$att->date]["entrance"]["result"] === "T"){
								if (strtotime($emp->tardiness_acc) < strtotime("23:59"))
									$emp->tardiness_acc = date("H:i", strtotime($emp->tardiness_acc) + $at_e - $wo_e);
								
								if (strtotime($emp->tardiness_acc) > strtotime("23:59")) $emp->tardiness_acc = "23:59";
							}
						}
						
						if (($at_l < $wo_l) and ($no_holiday)){//early exit
							$emp->daily[$att->date]["exit"]["result"] = "E";
							
							//need to check if emp has exit exception
							if (array_key_exists($att->date, $vacation_exception)){
								if (array_key_exists(1, $vacation_exception[$att->date])){
									$wo_l = strtotime(date("H:i", strtotime($vacation_exception[$att->date][1])));
									if ($at_l > $wo_l){
										$emp->daily[$att->date]["exit"]["result"] = "V";
									}
								}
							}
						}
					}
				}
				
				foreach($emp->daily as $date => $val){
					switch($val["type"]){
						case "N": 
							if ($val["entrance"]["result"] === "T") $emp->tardiness_qty++;
							elseif ($val["entrance"]["result"] === "V") $emp->vacation_qty += 0.5;
							
							if ($val["exit"]["result"] === "E") $emp->early_exit_qty++;
							else if ($val["exit"]["result"] === "V") $emp->vacation_qty += 0.5;
							break;
						case "X": $emp->absence_qty++; break;
						case "V": $emp->vacation_qty++; break;
					}
				}
				
				$employees[] = clone $emp;
			}
		}
		
		return [
			"month" => date("F", strtotime($period)),
			"employees" => $employees,
			"dates" => $dates,
			"headers" => $headers,
		];
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

	public function set_attendance_view(){
		$data = $this->set_attendance("2024-02");
		print_r($data);
	}

	public function export_monthly_report(){
		$type = "error"; $msg = null; $url = "";
		
		$period = $this->input->post("period");
		if (!$period) $period = date("Y-m");
		
		$period = "2024-02";
		
		$data = $this->set_attendance($period);
		
		$headers = $data["headers"];
		$dates = $data["dates"];
		$employees = $data["employees"];
		
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		
		//set report parameters
		$sheet->setCellValueByColumnAndRow(1, 1, "Attendance Monthly Report");
		$sheet->setCellValueByColumnAndRow(1, 2, "Period");
		$sheet->setCellValueByColumnAndRow(2, 2, $period);
		$sheet->setCellValueByColumnAndRow(1, 3, "Created");
		$sheet->setCellValueByColumnAndRow(2, 3, date('Y-m-d H:i:s'));
		
		//set headers
		$sheet->setCellValueByColumnAndRow(1, 5, '#');
		$sheet->setCellValueByColumnAndRow(2, 5, 'Emp.Num');
		$sheet->setCellValueByColumnAndRow(3, 5, 'Employee');
		$sheet->setCellValueByColumnAndRow(4, 5, 'Subsidiary');
		$sheet->setCellValueByColumnAndRow(5, 5, 'Organization');
		$sheet->setCellValueByColumnAndRow(6, 5, 'Department');
		$sheet->setCellValueByColumnAndRow(7, 5, 'Location');
		$sheet->setCellValueByColumnAndRow(8, 5, 'Vacation');
		$sheet->setCellValueByColumnAndRow(9, 5, 'Absence');
		$sheet->setCellValueByColumnAndRow(10, 5, 'Tardiness');
		$sheet->setCellValueByColumnAndRow(11, 5, 'Tard.Acc.');
		$sheet->setCellValueByColumnAndRow(12, 5, 'Early Exit');
		
		$x_start = 13;
		foreach($headers as $i => $h){//header structure: ["day", "day_w", "type"]
			$x = $x_start + $i;
			
			$sheet->setCellValueByColumnAndRow($x, 5, $h["day"]." ".$h["day_w"]);
			if ($h["type"] === "H") $sheet->getStyleByColumnAndRow($x, 5)->getFont()->setColor(new Color($this->color_rgb["red"]));
		}
		
		$v_center = Alignment::VERTICAL_CENTER;
		$y_start = 6;
		foreach($employees as $i => $emp){
			$y = ($i * 2) + $y_start;
			
			$sheet->setCellValueByColumnAndRow(1, $y, $i + 1);
			$sheet->setCellValueByColumnAndRow(2, $y, $emp->employee_number);
			$sheet->setCellValueByColumnAndRow(3, $y, $emp->name);
			$sheet->setCellValueByColumnAndRow(4, $y, $emp->subsidiary);
			$sheet->setCellValueByColumnAndRow(5, $y, $emp->organization);
			$sheet->setCellValueByColumnAndRow(6, $y, $emp->department);
			$sheet->setCellValueByColumnAndRow(7, $y, $emp->location);
			
			$sheet->setCellValueByColumnAndRow(8, $y, ($emp->vacation_qty > 0) ? $emp->vacation_qty : "");
			$sheet->setCellValueByColumnAndRow(9, $y, ($emp->absence_qty > 0) ? $emp->absence_qty : "");
			$sheet->setCellValueByColumnAndRow(10, $y, ($emp->tardiness_qty > 0) ? $emp->tardiness_qty : "");
			$sheet->setCellValueByColumnAndRow(11, $y, ($emp->tardiness_qty > 0) ? $emp->tardiness_acc : "");
			$sheet->setCellValueByColumnAndRow(12, $y, ($emp->early_exit_qty > 0) ? $emp->early_exit_qty : "");

			foreach($dates as $idate => $d){
				$x = $x_start + $idate;
				$xl = $this->columnIndexToLetters($x);
				
				if ($emp->daily[$d]["type"] === "N"){
					if ($emp->daily[$d]["entrance"]["result"] === "V"){ 
						$en_color = $this->color_rgb["green"];
						$en_val = $emp->daily[$d]["entrance"]["result"];
					}else{
						$en_color = ($emp->daily[$d]["entrance"]["result"] === "T") ? $this->color_rgb["red"] : ""; 
						$en_val = date("H:i", strtotime($emp->daily[$d]["entrance"]["time"]));
					}
					
					if ($emp->daily[$d]["exit"]["result"] === "V"){ 
						$ex_color = $this->color_rgb["green"];
						$ex_val = $emp->daily[$d]["exit"]["result"];
					}else{
						$ex_color = ($emp->daily[$d]["exit"]["result"] === "E") ? $this->color_rgb["red"] : ""; 
						$ex_val = date("H:i", strtotime($emp->daily[$d]["exit"]["time"]));
					}
					
					//entrance value & color
					$sheet->setCellValueByColumnAndRow($x, $y, $en_val);
					$sheet->getStyleByColumnAndRow($x, $y)->getFont()->setColor(new Color($en_color));
					
					//exit value & color
					$sheet->setCellValueByColumnAndRow($x, ($y + 1), $ex_val);
					$sheet->getStyleByColumnAndRow($x, ($y + 1))->getFont()->setColor(new Color($ex_color));
				}else{
					switch($emp->daily[$d]["type"]){
						case "X": $d_color = $this->color_rgb["red"]; break;
						case "V": $d_color = $this->color_rgb["green"]; break;
						default: $d_color = "";
					}
					
					$sheet->setCellValueByColumnAndRow($x, $y, $emp->daily[$d]["type"]);
					$sheet->getStyleByColumnAndRow($x, $y)->getFont()->setColor(new Color($d_color));
					
					$sheet->mergeCells($xl.$y.':'.$xl.($y + 1));
					$sheet->getStyle($xl.$y)->getAlignment()->setVertical($v_center);
				}
			}

			//merge cells of employee general info
			for($c = 1; $c < $x_start; $c++){
				$cl = $this->columnIndexToLetters($c);
				
				$sheet->mergeCells($cl.$y.':'.$cl.($y + 1));
				$sheet->getStyle($cl.$y)->getAlignment()->setVertical($v_center);
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
	
}
