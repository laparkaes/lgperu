<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Hr_attendance extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function set_attandance($period, $prs){
		if (!$prs) $prs = [-1];
		
		//first & last date
		$from = date("Y-m-01", strtotime($period));
		$to = date("Y-m-t", strtotime($period));
		
		//define days
		$days = [];//access data of each day
		$days_week = [];//day of week for each day
		$free_days = [];//free days to mark in red color in day header
		$free_days_w = ["Saturday", "Sunday"];//free days of week
		
		$dates = $this->my_func->dates_between($from, $to);
		foreach($dates as $item){
			$day = date("d", strtotime($item));
			$day_w = date("l", strtotime($item));
			if (in_array($day_w, $free_days_w)) $free_days[] = $day;
			
			$days_week[$day] = $day_w;
			$days[$day] = [
				"date" => $item,
				"day" => $day,
				"first_access" => ["time" => null, "remark" => null],
				"last_access" => ["time" => null, "remark" => null],
			];
		}
		
		//load active employees in month
		$order = [
			["subsidiary", "asc"], 
			["organization", "asc"], 
			["department", "asc"], 
			["name", "asc"],
		];
		
		//employee data array setting
		$employees = [];
		$employees_rec = $this->gen_m->filter("hr_employee", false, ["active" => true], null, [["field" => "employee_number", "values" => $prs]], $order);
		foreach($employees_rec as $item){
			unset($item->password);
			
			$aux = [];
			if ($item->subsidiary) $aux[] = $item->subsidiary;
			if ($item->organization) $aux[] = $item->organization;
			if ($item->department) $aux[] = $item->department;
			
			$item->dept = implode(" > ", $aux);
			
			/*
			foreach($item as $key => $val) echo $val." /// ";
			echo "<br/><br/>";
			*/
			//print_R($item); echo "<br/><br/>";
			
			$employees[$item->employee_number] = [
				"data" => $item,
				"summary" => [
					"check_days" => 0,
					"tardiness" => 0,
					"early_out" => 0,
				],
				"access" => $days,
			];
		}
		
		//assign asistance summary in array
		$w = ["work_date >=" => $from, "work_date <=" => $to];
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w, null, [["field" => "pr", "values" => $prs]]);
		foreach($records as $item){
			if ($item->pr){
				$day = date("d", strtotime($item->work_date));
				$first_time = date("H:i", strtotime($item->first_access));
				$last_time = date("H:i", strtotime($item->last_access));
				
				if (!array_key_exists($item->pr, $employees)){
					
					//create employee records
					$employee_id = $this->gen_m->insert("hr_employee", ["employee_number" => $item->pr, "name" => $item->name]);
					$aux = $this->gen_m->unique("hr_employee", "employee_id", $employee_id, false);
					$aux->dept = "";
					
					$employees[$aux->employee_number] = [
						"data" => clone $aux,
						"summary" => [
							"check_days" => 0,
							"tardiness" => 0,
							"early_out" => 0,
						],
						"access" => $days,
					];
					
				}
				
				$employees[$item->pr]["access"][$day]["first_access"]["time"] = $first_time;
				$employees[$item->pr]["access"][$day]["last_access"]["time"] = $last_time;
			}
		}
		
		//work schedule validation
		$day_pivot = $from;
		$schedule_days = [];
		while(strtotime($day_pivot) <= strtotime($to)){
			$schedule_days[$day_pivot] = ["start" => null, "end" => null];
			$day_pivot = date("Y-m-d", strtotime($day_pivot." +1 day"));
		}
		
		$schedule_pr = [];
		foreach($prs as $item) $schedule_pr[$item] = $schedule_days;
		
		$schedule = $this->gen_m->filter("hr_schedule", false, ["date_from <=" => $to], null, [["field" => "pr", "values" => $prs]], [["pr", "asc"], ["date_from", "desc"]]);
		foreach($schedule as $item){
			//print_r($item); echo "<br/>";
			
			$day_pivot = date("Y-m-d", max(strtotime($from), strtotime($item->date_from)));
			while(strtotime($day_pivot) <= strtotime($to)){
				//echo $day_pivot."<br/>";
				
				if (!$schedule_pr[$item->pr][$day_pivot]["start"]){
					$schedule_pr[$item->pr][$day_pivot]["start"] = $item->work_start;
					$schedule_pr[$item->pr][$day_pivot]["end"] = $item->work_end;
				}else break;
				
				$day_pivot = date("Y-m-d", strtotime($day_pivot." +1 day"));
			}
			//echo "<br/>";
		}
		
		//check if all employees has working time
		foreach($schedule_pr as $pr => $sch){
			foreach($sch as $day => $item){
				if (!$schedule_pr[$pr][$day]["start"]) $schedule_pr[$pr][$day]["start"] = "01:00";
				if (!$schedule_pr[$pr][$day]["end"]) $schedule_pr[$pr][$day]["end"] = "23:00";
			}
		}
		
		/* PR009370
		T: Tardiness
		E: Early-Out
		V: Vacation
		MED: Medical Vacation
		*/
		
		$exceptions = $this->gen_m->filter("hr_attendance_exception", false, ["exc_date >=" => $from, "exc_date <=" => $to, "pr" => "LGEPR"]);
		foreach($exceptions as $item) if ($item->type === "H") $free_days[] = date("d", strtotime($item->exc_date));
		
		$early_friday_days = [];
		$early_friday = $this->gen_m->filter("hr_attendance_exception", false, ["exc_date >=" => $from, "exc_date <=" => $to, "pr" => "LGEPR", "type" => "EF"]);
		foreach($early_friday as $item){
			$early_friday_days[] = date("d", strtotime($item->exc_date));
		}
		
		$no_attn_days = ["Sat", "Sun"];
		foreach($employees as $pr => $item){
			foreach($item["access"] as $aux => $access){
				$day_pivot = date("Y-m-", strtotime($from)).$access["day"];
				
				if (!(in_array($access["day"], $free_days))){
					
					if ($access["first_access"]["time"] === $access["last_access"]["time"]) $access["last_access"]["time"] = null;
					
					if ($access["first_access"]["time"]){
						$employees[$pr]["summary"]["check_days"]++;
						
						$start = in_array($access["day"], $early_friday_days) ? strtotime("08:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["start"]);
						$start_tolerance = in_array($access["day"], $early_friday_days) ? strtotime("08:34:00") : strtotime('+4 minutes', strtotime($schedule_pr[$pr][$day_pivot]["start"]));
						
						$first = strtotime($access["first_access"]["time"]);
						
						if ($start < $first){
							if ($start_tolerance < $first){
								$employees[$pr]["summary"]["tardiness"]++;
								$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";
							}else{
								$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "TT";//Tardeness Toleranced
							}
						}
					}
					
					if ($access["last_access"]["time"]){
						$end = in_array($access["day"], $early_friday_days) ? strtotime("12:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["end"]);
						$last = strtotime($access["last_access"]["time"]);
						
						if ($last < $end){
							$employees[$pr]["summary"]["early_out"]++;
							$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "E";
						}
					}
				}
			}
		}
		
		//exception days setting
		/*
		stdClass Object
        (
            [exception_id] => 4
            [pr] => PR009350
            [exc_date] => 2024-10-07
            [type] => V
            [remark] => 
        )
		*/
		//$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";
		$exceptions = $this->gen_m->filter("hr_attendance_exception", false, ["exc_date >=" => $from, "exc_date <=" => $to], null, null, [["pr", "asc"]]);
		foreach($exceptions as $item){
			$item->name = "";
			if ($item->pr !== "LGEPR"){
				$aux_emp = $this->gen_m->unique("hr_employee", "employee_number", $item->pr, false);
				$item->name = $aux_emp ? $aux_emp->name : "";
				$day = date("d", strtotime($item->exc_date));
				if (array_key_exists($item->pr, $employees)) switch($item->type){
					case "V":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "MV":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "AV":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "MED":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
				}
			}
		}
		
		/*
		foreach($schedule_pr as $pr => $sch){
			echo $pr."<br/>";
			foreach($sch as $item){
				print_r($item); echo "<br/>";
			}
			echo "<br/>";
		} echo "<br/><br/>";
		
		foreach($employees as $item){
			//print_r($item);
			print_r($item["data"]); echo "<br/><br/>";
			print_r($item["summary"]); echo "<br/><br/>";
			foreach($item["access"] as $acc){
				print_r($acc); echo "<br/>";
			}
			echo "<br/><br/>======================================<br/><br/>";
		}
		return;
		*/
		
		$data = [
			"period" => $period,
			"from" => $from,
			"to" => $to,
			"days" => $days,
			"days_week" => $days_week,
			"free_days" => $free_days,
			"employees" => $employees,
			"schedule_pr" => $schedule_pr,
			"exceptions" => $exceptions,
		];
		
		return $data;
	}
	
	public function index(){
		//mapping access update
		$pr_mapping = [
			["M875S9193", "PR009297"],//WOO WONSHIK
			["M60682453", "PR009182"],//CHO, HYUN
			["M75951391", "PR009329"],//HAN MUHYUN
		];
		foreach($pr_mapping as $item) $this->gen_m->update("hr_attendance", ["pr" => $item[0]], ["pr" => $item[1]]);
		
		//priod define
		$period = $this->input->get("p");
		if (!$period) $period = date("Y-m");
		//$period = "2024-09";
		
		//first & last date
		$from = date("Y-m-01", strtotime($period));
		$to = date("Y-m-t", strtotime($period));
		
		//access records load
		$w = ["work_date >=" => $from, "work_date <=" => $to];
		$l = [["field" => "pr", "values" => ["PR"]]];
		$prs = [];//used to load valid emmployee's schedules
		
		//load attendance records and 
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w, $l);
		foreach($records as $item) $prs[] = $item->pr;
		
		sort($prs);
		$prs = array_unique($prs);
		$prs = array_values($prs);
		
		//additional data set
		//period list
		$periods = [];
		$jan = date("Y-01"); 
		$now = date("Y-m");
		while(strtotime($now) >= strtotime($jan)){
			$periods[] = $now;
			$now = date("Y-m", strtotime($now." -1 month"));
		}
		
		//options to select in exception list for employee
		$exceptions_emp = [
			["V", "Vacation"],
			["MV", "Half Vacation (Morning)"],
			["AV", "Half Vacation (Afternoon)"],
			["MED", "Medical Vacation"],
		];
		
		//options to select in exception list for company
		$exceptions_com = [
			["H", "Holiday"],
			["EF", "Early Friday"],
		];
		
		$data = $this->set_attandance($period, $prs);
		
		$data["periods"] = $periods;
		$data["exceptions_emp"] = $exceptions_emp;
		$data["exceptions_com"] = $exceptions_com;
		$data["main"] = "module/hr_attendance/index";
		
		$this->load->view('layout', $data);
	}
	
	private function make_excel($data){
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		//col, row in number
		$sheet->setCellValueByColumnAndRow(1, 1, "Attendance Report ".$data["period"]);
		
		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->getColumnDimension('B')->setWidth(30);
		$sheet->getStyle('A:Z')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$sheet->getStyle('C:AZ')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		
		//header
		$sheet->setCellValueByColumnAndRow(1, 3, "Employee");
		$sheet->setCellValueByColumnAndRow(2, 3, "Department");
		$sheet->setCellValueByColumnAndRow(3, 3, "PR");
		$sheet->setCellValueByColumnAndRow(4, 3, "Days");
		$sheet->setCellValueByColumnAndRow(5, 3, "Tardness\nEarly-out");
		$sheet->setCellValueByColumnAndRow(6, 3, "Worktime");
		
		$sheet->getStyleByColumnAndRow(5, 3)->getAlignment()->setWrapText(true);
		
		$j = 7;
		foreach($data["days"] as $item){
			$sheet->setCellValueByColumnAndRow($j, 3, $item["day"]."\n".substr($data["days_week"][$item["day"]], 0, 3));
			$sheet->getStyleByColumnAndRow($j, 3)->getAlignment()->setWrapText(true);
			
			//setting color
			if (in_array($item["day"], $data["free_days"])) $sheet->getStyleByColumnAndRow($j, 3)->getFont()->getColor()->setARGB('RED');
			
			$j++;
		}
		
		$fill_color = "ffffff";
		$fill_type = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
		$i = 0;
		foreach($data["employees"] as $item){
			$row_1 = ($i * 2) + 4;
			$row_2 = $row_1 + 1;
			
			//employee datas
			$sheet->setCellValueByColumnAndRow(1, $row_1, $item["data"]->name);
			$sheet->setCellValueByColumnAndRow(2, $row_1, $item["data"]->subsidiary." > ".$item["data"]->organization);
			$sheet->setCellValueByColumnAndRow(2, $row_2, $item["data"]->department);
			$sheet->setCellValueByColumnAndRow(3, $row_1, $item["data"]->employee_number);
			$sheet->setCellValueByColumnAndRow(4, $row_1, $item["summary"]["check_days"]);
			$sheet->setCellValueByColumnAndRow(5, $row_1, $item["summary"]["tardiness"]);
			$sheet->setCellValueByColumnAndRow(5, $row_2, $item["summary"]["early_out"]);
			$sheet->setCellValueByColumnAndRow(6, $row_1, date("H:i", strtotime($data["schedule_pr"][$item["data"]->employee_number][$data["to"]]["start"])));
			$sheet->setCellValueByColumnAndRow(6, $row_2, date("H:i", strtotime($data["schedule_pr"][$item["data"]->employee_number][$data["to"]]["end"])));
			
			if ($item["summary"]["tardiness"] > 4) $sheet->getStyleByColumnAndRow(5, $row_1)->getFont()->getColor()->setARGB('RED');
			if ($item["summary"]["early_out"] > 4) $sheet->getStyleByColumnAndRow(5, $row_2)->getFont()->getColor()->setARGB('RED');
			
			$j = 7;
			foreach($item["access"] as $item_ac){
				$sheet->setCellValueByColumnAndRow($j, $row_1, $item_ac["first_access"]["time"]);
				$sheet->setCellValueByColumnAndRow($j, $row_2, $item_ac["last_access"]["time"]);
				
				if ($item_ac["first_access"]["remark"] === "T") $sheet->getStyleByColumnAndRow($j, $row_1)->getFont()->getColor()->setARGB('RED');
				if ($item_ac["first_access"]["remark"] === "TT") $sheet->getStyleByColumnAndRow($j, $row_1)->getFont()->getColor()->setARGB('FFA500');//ORANGE
				if ($item_ac["last_access"]["remark"] === "E") $sheet->getStyleByColumnAndRow($j, $row_2)->getFont()->getColor()->setARGB('RED');
				
				$j++;
			}
			
			$sheet->getStyle("A".$row_1.":AZ".$row_1)->getFill()->setFillType($fill_type)->getStartColor()->setARGB($fill_color);
			$sheet->getStyle("A".$row_2.":AZ".$row_2)->getFill()->setFillType($fill_type)->getStartColor()->setARGB($fill_color);
			
			$fill_color = ("ffffff" === $fill_color) ? "efefef" : "ffffff";
			
			$i++;
		}
		
		$sheet->getStyle("A1:AZ1")->getFill()->setFillType($fill_type)->getStartColor()->setARGB("ffffff");
		$sheet->getStyle("A2:AZ2")->getFill()->setFillType($fill_type)->getStartColor()->setARGB("ffffff");
		$sheet->getStyle("A3:AZ3")->getFill()->setFillType($fill_type)->getStartColor()->setARGB("efefef");

		$sheet->getStyle("A1:AZ1")->getFont()->setBold(true);
		$sheet->getStyle("A3:AZ3")->getFont()->setBold(true);
		
		//save excel file to a temporary directory
		$filename = "hr_attendance_report.xlsx";
		$writer = new Xlsx($spreadsheet);
		$writer->save('./upload/'.$filename);
		
		//file url
		$url = base_url()."upload/".$filename;
		
		//echo '<br/><a href="'.$url.'" download="Attendance '.$data["period"].'.xlsx">파일 다운로드</a><br/><br/><br/>';
		//foreach($data as $key => $item){ echo $key."================="; print_r($item); echo "<br/><br/><br/><br/>"; }
		
		return $url;
		
	}
	
	public function export(){
		$period = $this->input->get("p");
		if (!$period) $period = $this->input->post("p");
		if (!$period) $period = date("Y-m");
		
		//first & last date
		$from = date("Y-m-01", strtotime($period));
		$to = date("Y-m-t", strtotime($period));
		
		//access records load
		$w = ["work_date >=" => $from, "work_date <=" => $to];
		$l = [["field" => "pr", "values" => ["PR"]]];
		$prs = [];//used to load valid emmployee's schedules
		
		//load attendance records and 
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w, $l);
		foreach($records as $item) $prs[] = $item->pr;
		
		sort($prs);
		$prs = array_unique($prs);
		$prs = array_values($prs);
		
		$data = $this->set_attandance($period, $prs);
		$url = $this->make_excel($data);
		
		header('Content-Type: application/json');
		echo json_encode(["url" => $url, "period" => $period]);
	}
	
	public function add_exception(){
		$type = "error"; $msg = null;
		
		$d_from = $this->input->post("d_from");
		$d_to = $this->input->post("d_to");
		$exc = $this->input->post("exc");
		$no_attn_days = ["Sat", "Sun"];
		
		$free_days = [];
		$exceptions = $this->gen_m->filter("hr_attendance_exception", false, ["exc_date >=" => $d_from, "exc_date <=" => $d_to, "pr" => "LGEPR"]);
		foreach($exceptions as $item) $free_days[] = date("d", strtotime($item->exc_date));
		
		if (strtotime($d_from) <= strtotime($d_to)){
			if (!$this->gen_m->filter("hr_attendance_exception", false, ["pr" => $exc["pr"], "exc_date >=" => $d_from, "exc_date <=" => $d_to])){
				if ($exc["type"]){
					
					$now = $d_from;
					while (strtotime($now) <= strtotime($d_to)){
						/*
						Array
						(
							[d_from] => 2024-10-02
							[d_to] => 2024-10-02
							[type] => V
							[remark] => 
							[pr] => PR001736
						)
						*/
						
						if (!(in_array(date("D", strtotime($now)), $no_attn_days) or (in_array(date("d", strtotime($now)), $free_days)))){
							$exc["exc_date"] = $now;
							$this->gen_m->insert("hr_attendance_exception", $exc);
						}
						
						$now = date("Y-m-d", strtotime($now.' +1 day'));
					}
					
					$type = "success";
					$msg = "Exception has been registered.";
				}else $msg = "Select an exception type.";
			}else $msg = "There is at least one exception between the dates.";
		}else $msg = "Date selection error.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function remove_exception(){
		$this->gen_m->delete("hr_attendance_exception", ["exception_id" => $this->input->post("exc_id")]);
	}
}
