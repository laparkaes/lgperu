<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Reports extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function set_attandance_v1($period, $prs){
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
		
		$depts = [];
		
		//assign asistance summary in array
		$w = ["work_date >=" => $from, "work_date <=" => $to];
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w, null, [["field" => "pr", "values" => $prs]]);
		foreach($records as $item){
			if ($item->pr){
				$day = date("d", strtotime($item->work_date));
				$first_time = date("H:i:s", strtotime($item->first_access));
				$last_time = date("H:i:s", strtotime($item->last_access));
				
				if ($first_time === $last_time) $last_time = date("H:i:s", strtotime("+1 second", strtotime($last_time)));
				
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
				
				if ($employees[$item->pr]["data"]->dept) $depts[] = $employees[$item->pr]["data"]->dept;
			}
		}
		
		$depts = array_unique($depts);
		sort($depts);
		//foreach($depts as $item) echo $item."<br/>";
		
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
		
		/* load 00:00:00 ~ 03:59:59 check datas (< from and <= (to + 1) */
		$w_dch = ["access >" => $from, "access <=" => date("Y-m-d", strtotime("+1 day", strtotime($to)))];
		$l_dch = [["field" => "pr", "values" => ["PR"]]];
		$dawn_checks = $this->gen_m->filter("v_hr_attendance_dawn", false, $w_dch, $l_dch);
		
		$dawn_checks_arr = [];
		foreach($dawn_checks as $item){
			$dawn_checks_arr[$item->pr."_".date("Y-m-d", strtotime("-1 day", strtotime($item->access)))] = $item;
			//print_r($item); echo "<br/>";
		}
		
		//echo "<br/>";echo "<br/>"; print_r($dawn_checks_arr); echo "<br/>";echo "<br/>";echo "<br/>";
		
		$today = date("Y-m-d");
		$no_attn_days = ["Sat", "Sun"];
		foreach($employees as $pr => $item){
			foreach($item["access"] as $aux => $access){
				$day_pivot = date("Y-m-", strtotime($from)).$access["day"];
				
				if (!(in_array($access["day"], $free_days))){
					
					if ($access["first_access"]["time"] === $access["last_access"]["time"]) $access["last_access"]["time"] = null;
					
					if ($access["first_access"]["time"]){
						$employees[$pr]["summary"]["check_days"]++;
						
						$start = in_array($access["day"], $early_friday_days) ? strtotime("08:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["start"]);
						/* use this if need to apply tolerance time
						$start_tolerance = in_array($access["day"], $early_friday_days) ? strtotime("08:34:00") : strtotime('+4 minutes', strtotime($schedule_pr[$pr][$day_pivot]["start"]));
						*/
						
						$first = strtotime($access["first_access"]["time"]);
						
						if ($start < $first){
							$employees[$pr]["summary"]["tardiness"]++;
							$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";//Tardeness Toleranced
							
							/* use this if need to apply tolerance time
							if ($start_tolerance < $first){
								$employees[$pr]["summary"]["tardiness"]++;
								$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";
							}else{
								$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "TT";//Tardeness Toleranced
							}
							*/
						}
						
						$employees[$pr]["access"][$access["day"]]["first_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["first_access"]["time"]));
					}
					
					if ($access["last_access"]["time"]){
						$end = in_array($access["day"], $early_friday_days) ? strtotime("12:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["end"]);
						$last = strtotime($access["last_access"]["time"]);
						
						if ($access["date"] !== $today){
							if ($last < $end){
								$key_dawn = $pr."_".$employees[$pr]["access"][$access["day"]]["date"];
								if (array_key_exists($key_dawn, $dawn_checks_arr)){
									$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($dawn_checks_arr[$key_dawn]->access))."<br/>(+1D)";
									$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "(+1D)";
								}else{
									$employees[$pr]["summary"]["early_out"]++;
									$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));
									$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "E";
								}
								
								//print_r($employees[$pr]["access"][$access["day"]]["last_access"]); echo "<br/>"; echo "<br/>";
							}else $employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));	
						}else $employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));	
					}
				}else{
					if ($employees[$pr]["access"][$access["day"]]["first_access"]["time"]) 
						$employees[$pr]["access"][$access["day"]]["first_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["first_access"]["time"]));
					
					if ($employees[$pr]["access"][$access["day"]]["last_access"]["time"])
						$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));
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
					
					// Edicion Morning
					case "MB":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MBT":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MCO":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MCMP":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MT":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					// Edicion Afternoon
					case "AB":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "ABT":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "ACO":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "ACMP":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "AHO":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "AT":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					// Edit other cases
					case "BT":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "CE":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "CO":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "CMP":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "HO":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "L":
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
			"depts" => $depts,
		];
		
		return $data;
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
				"first_access_export" => ["time" => null, "remark" => null], //add to export
				"last_access_export" => ["time" => null, "remark" => null], //add to export
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
		
		$depts = [];
		
		//assign asistance summary in array
		$w = ["work_date >=" => $from, "work_date <=" => $to];
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w, null, [["field" => "pr", "values" => $prs]]);
		foreach($records as $item){
			if ($item->pr){
				$day = date("d", strtotime($item->work_date));
				$first_time = date("H:i:s", strtotime($item->first_access));  // cambio de H_i
				$last_time = date("H:i:s", strtotime($item->last_access)); // cambio de H_i
				
				if ($first_time === $last_time) $last_time = date("H:i:s", strtotime("+1 second", strtotime($last_time)));
				
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
				
				$employees[$item->pr]["access"][$day]["first_access_export"]["time"] = $first_time; // add to export
				$employees[$item->pr]["access"][$day]["last_access_export"]["time"] = $last_time; // add to export
				
				if ($employees[$item->pr]["data"]->dept) $depts[] = $employees[$item->pr]["data"]->dept;
			}
		}
		
		$depts = array_unique($depts);
		sort($depts);
		//foreach($depts as $item) echo $item."<br/>";
		
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
		
		/* load 00:00:00 ~ 03:59:59 check datas (< from and <= (to + 1) */
		$w_dch = ["access >" => $from, "access <=" => date("Y-m-d", strtotime("+1 day", strtotime($to)))];
		$l_dch = [["field" => "pr", "values" => ["PR"]]];
		$dawn_checks = $this->gen_m->filter("v_hr_attendance_dawn", false, $w_dch, $l_dch);
		
		$dawn_checks_arr = [];
		foreach($dawn_checks as $item){
			$dawn_checks_arr[$item->pr."_".date("Y-m-d", strtotime("-1 day", strtotime($item->access)))] = $item;
			//print_r($item); echo "<br/>";
		}
		
		$today = date("Y-m-d");
		$no_attn_days = ["Sat", "Sun"];
		foreach($employees as $pr => $item){
			foreach($item["access"] as $aux => $access){
				$day_pivot = date("Y-m-", strtotime($from)).$access["day"];
				
				if (!(in_array($access["day"], $free_days))){
					
					if ($access["first_access"]["time"] === $access["last_access"]["time"]) {
						$access["last_access"]["time"] = null;
						$access["last_access_export"]["time"] = null; // add to export
					}
					
					if ($access["first_access"]["time"]){
						$employees[$pr]["summary"]["check_days"]++;
							
						$start = in_array($access["day"], $early_friday_days) ? strtotime("08:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["start"]);
						// use this if need to apply tolerance time
						// $start_tolerance = in_array($access["day"], $early_friday_days) ? strtotime("08:34:00") : strtotime('+4 minutes', strtotime($schedule_pr[$pr][$day_pivot]["start"]));
						
						$first = strtotime($access["first_access"]["time"]);
						
						if ($start < $first){
							$employees[$pr]["summary"]["tardiness"]++;
							$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";
							$employees[$pr]["access"][$access["day"]]["first_access_export"]["remark"] = "T"; //add to export
							// if ($start_tolerance < $first){
								// $employees[$pr]["summary"]["tardiness"]++;
								// $employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";
							// }
							// else{
								// $employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "TT";//Tardeness Toleranced
							// }
						}
						$employees[$pr]["access"][$access["day"]]["first_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["first_access"]["time"]));
						$employees[$pr]["access"][$access["day"]]["first_access_export"]["time"] = date("H:i:s", strtotime($employees[$pr]["access"][$access["day"]]["first_access_export"]["time"])); // add to export
					}
					
					// if ($access["last_access"]["time"]){
						// $end = in_array($access["day"], $early_friday_days) ? strtotime("12:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["end"]);
						// $last = strtotime($access["last_access"]["time"]);
						
						// if ($last < $end){
							// $employees[$pr]["summary"]["early_out"]++;
							// $employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "E";
						// }
					// }
					
					if ($access["last_access"]["time"]){
						$end = in_array($access["day"], $early_friday_days) ? strtotime("12:30:00") : strtotime($schedule_pr[$pr][$day_pivot]["end"]);
						$last = strtotime($access["last_access"]["time"]);
						
						if ($access["date"] !== $today){
							if ($last < $end){
								$key_dawn = $pr."_".$employees[$pr]["access"][$access["day"]]["date"];
								if (array_key_exists($key_dawn, $dawn_checks_arr)){									
									$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($dawn_checks_arr[$key_dawn]->access))."<br/>(+1D)";
									$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "(+1D)";
									
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["time"] = date("H:i:s", strtotime($dawn_checks_arr[$key_dawn]->access))."<br/>(+1D)"; // add to export
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["remark"] = "(+1D)"; // add to export
								}else{ 
									$employees[$pr]["summary"]["early_out"]++;
									$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));						
									$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "E";
									
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["time"] = date("H:i:s", strtotime($employees[$pr]["access"][$access["day"]]["last_access_export"]["time"])); // add to export
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["remark"] = "E";
								}
								
								//print_r($employees[$pr]["access"][$access["day"]]["last_access"]); echo "<br/>"; echo "<br/>";
							}else {
								$key_dawn = $pr."_".$employees[$pr]["access"][$access["day"]]["date"];
								if (array_key_exists($key_dawn, $dawn_checks_arr)){									
									$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($dawn_checks_arr[$key_dawn]->access))."<br/>(+1D)";
									$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "(+1D)";
									
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["time"] = date("H:i:s", strtotime($dawn_checks_arr[$key_dawn]->access))."<br/>(+1D)"; // add to export
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["remark"] = "(+1D)"; // add to export
								} else{
									$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));
									$employees[$pr]["access"][$access["day"]]["last_access_export"]["time"] = date("H:i:s", strtotime($employees[$pr]["access"][$access["day"]]["last_access_export"]["time"])); //add to export
								}
							}								
						}else {
							$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));
							$employees[$pr]["access"][$access["day"]]["last_access_export"]["time"] = date("H:i:s", strtotime($employees[$pr]["access"][$access["day"]]["last_access_export"]["time"])); // add to export
						}
					}
					
				}else{
					if ($employees[$pr]["access"][$access["day"]]["first_access"]["time"]) {
						$employees[$pr]["access"][$access["day"]]["first_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["first_access"]["time"]));
						$employees[$pr]["access"][$access["day"]]["first_access_export"]["time"] = date("H:i:s", strtotime($employees[$pr]["access"][$access["day"]]["first_access_export"]["time"])); // add to export
					}
					
					if ($employees[$pr]["access"][$access["day"]]["last_access"]["time"]) {
						$employees[$pr]["access"][$access["day"]]["last_access"]["time"] = date("H:i", strtotime($employees[$pr]["access"][$access["day"]]["last_access"]["time"]));
						$employees[$pr]["access"][$access["day"]]["last_access_export"]["time"] = date("H:i:s", strtotime($employees[$pr]["access"][$access["day"]]["last_access_export"]["time"])); // add to export
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
					// Edicion Morning
					case "MB":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MBT":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MCO":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MCMP":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "MT":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					case "NEF":
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
					
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						break;
					// Edicion Afternoon
					case "AB":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "ABT":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "ACO":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "ACMP":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "AHO":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "AT":
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
					
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					// Edit other cases
					case "BT":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "CE":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "CO":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "CMP":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "HO":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "L":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "J":
						if ($employees[$item->pr]["access"][$day]["first_access"]["time"]) $employees[$item->pr]["summary"]["check_days"]--;
						if ($employees[$item->pr]["access"][$day]["first_access"]["remark"] === "T") $employees[$item->pr]["summary"]["tardiness"]--;
						if ($employees[$item->pr]["access"][$day]["last_access"]["remark"] === "E") $employees[$item->pr]["summary"]["early_out"]--;
						
						$employees[$item->pr]["access"][$day]["first_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["first_access"]["remark"] = $item->type;
						$employees[$item->pr]["access"][$day]["last_access"]["time"] = null;
						$employees[$item->pr]["access"][$day]["last_access"]["remark"] = $item->type;
						break;
					case "S":
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
			"depts" => $depts,
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
			["V", "Vacation - V"],
			["MV", "Half Vacation (Morning) - MV"],
			["AV", "Half Vacation (Afternoon) - AV"],
			["MED", "Medical - MED"],
			["MB", "Birthday (Morning) - MB"], //Edicion
			["AB", "Birthday (Afternoon) - AB"],
			["BT", "Biz Trip - BT"],
			["MBT", "Biz Trip (Morning) - MBT"],
			["ABT", "Biz Trip (Afternoon) - ABT"],
			["CE", "Ceased - CE"],
			["CO", "Commission - CO"],
			["MCO", "Commission (Morning) - MCO"],
			["ACO", "Commission (Afternoon) - ACO"],
			["CMP", "Compensation - CMP"],
			["MCMP", "Compensation (Morning) - MCMP"],
			["ACMP", "Compensation (Afternoon) - ACMP"],
			["HO", "Home Office - HO"],
			["MHO", "Home Office (Morning) - MHO"],
			["AHO", "Home Office (Afternoon) - AHO"],
			["L", "License - L"],
			["MT", "Topic (Morning) - MT"],
			["AT", "Topic (Afternoon) - AT"],
			["J", "Justified - J"],
			["NEF", "No Early Friday - NEF"],
		];
		
		//options to select in exception list for company
		$exceptions_com = [
			["H", "Holiday - H"],
			["EF", "Early Friday - EF"],			
		];
		
		$data = $this->set_attandance($period, $prs);
		
		$data["periods"] = $periods;
		$data["exceptions_emp"] = $exceptions_emp;
		$data["exceptions_com"] = $exceptions_com;
		$data["overflow"] = "scroll";
		$data["main"] = "page/lgepr_punctuality/index";
		
		$this->load->view('layout_dashboard', $data);
	}
	
	public function index_v1(){
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
			["MB", "Birthday (Morning)"], //Edicion
			["AB", "Birthday (Afternoon)"],
			["BT", "Biz Trip"],
			["MBT", "Biz Trip (Morning)"],
			["ABT", "Biz Trip (Afternoon)"],
			["CE", "Ceased"],
			["CO", "Commission"],
			["MCO", "Commission (Morning)"],
			["ACO", "Commission (Afternoon)"],
			["CMP", "Compensation"],
			["MCMP", "Compensation (Morning)"],
			["ACMP", "Compensation (Afternoon)"],
			["HO", "Home Office"],
			["MHO", "Home Office (Morning)"],
			["AHO", "Home Office (Afternoon)"],
			["L", "License"],
			["MT", "Topic (Morning)"],
			["AT", "Topic (Afternoon)"],
			
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
		$data["overflow"] = "scroll";
		$data["main"] = "page/lgepr_punctuality/index";
		
		$this->load->view('layout_dashboard', $data);
	}

	public function daily($pr = null, $period = null){
		if ($pr == null){ echo "PR is NULL."; return; }
		if ($period == null){ echo "Period is NULL."; return; }
		
		$from = $pivot = strtotime(date("Y-m-01", strtotime($period)));
		$to = strtotime(date("Y-m-t", strtotime($period)));
		
		$dates = [];
		while($pivot <= $to){
			$dates[date("Y-m-d", $pivot)] = [];
			$pivot = strtotime("+1 day", $pivot);
		}
		
		$w = [
			"pr" => $pr,
			"access >=" => date("Y-m-d", $from),
			"access <=" => date("Y-m-d", $to),
		];
		
		$data = $this->gen_m->filter("hr_attendance", false, $w, null, null, [["access", "asc"]]);
		foreach($data as $item){
			$t = strtotime($item->access);
			$dates[date("Y-m-d", $t)][] = date("H:i:s", $t);
		}
		
		$data = [
			"period" => $period,
			"employee" => $this->gen_m->unique("hr_employee", "employee_number", $pr, false),
			"dates" => $dates,
			"overflow" => "scroll",
			"main" => "page/lgepr_punctuality/daily",
		];
		
		$this->load->view('layout_dashboard', $data);
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
