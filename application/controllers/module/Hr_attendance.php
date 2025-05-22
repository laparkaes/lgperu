<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
			["MV", "Vacation Morning - MV"],
			["AV", "Vacation Afternoon - AV"],
			["MED", "Medical - MED"],
			["MB", "Morning Birthday - MB"], //Edicion
			["AB", "Afternoon Birthday - AB"],
			["BT", "Biz Trip - BT"],
			["MBT", "Morning Biz Trip - MBT"],
			["ABT", "Afternoon Biz Trip - ABT"],
			["CE", "Ceased - CE"],
			["CO", "Commission - CO"],
			["MCO", "Morning Commission - MCO"],
			["ACO", "Afternoon Commission - ACO"],
			["CMP", "Compensation - CMP"],
			["MCMP", "Morning Compensation - MCMP"],
			["ACMP", "Afternoon Commission - ACMP"],
			["HO", "Home Office - HO"],
			["MHO", "Morning Home Office - MHO"],
			["AHO", "Afternoon Home Office - AHO"],
			["L", "License - L"],
			["MT", "Morning Topic - MT"],
			["AT", "Afternoon Topic - AT"],
			["J", "Justified - J"],
			["NEF", "No Early Friday - NEF"],
			["S", "Suspension - S"]
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
			//dates headers
			$sheet->setCellValueByColumnAndRow($j, 2, $item["date"]);
		
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
			$sheet->setCellValueByColumnAndRow(3, $row_2, $item["data"]->employee_number."1");
			$sheet->setCellValueByColumnAndRow(4, $row_1, $item["summary"]["check_days"]);
			$sheet->setCellValueByColumnAndRow(5, $row_1, $item["summary"]["tardiness"]);
			$sheet->setCellValueByColumnAndRow(5, $row_2, $item["summary"]["early_out"]);
			$sheet->setCellValueByColumnAndRow(6, $row_1, date("H:i", strtotime($data["schedule_pr"][$item["data"]->employee_number][$data["to"]]["start"])));
			$sheet->setCellValueByColumnAndRow(6, $row_2, date("H:i", strtotime($data["schedule_pr"][$item["data"]->employee_number][$data["to"]]["end"])));
			
			if ($item["summary"]["tardiness"] > 4) $sheet->getStyleByColumnAndRow(5, $row_1)->getFont()->getColor()->setARGB('RED');
			if ($item["summary"]["early_out"] > 4) $sheet->getStyleByColumnAndRow(5, $row_2)->getFont()->getColor()->setARGB('RED');
			
			$j = 7;
			foreach($item["access"] as $item_ac){
				
				$last_access_1d = $item_ac["last_access_export"]["time"];
				if (strpos($last_access_1d, "<br/>(+1D)") !== false) {
					$last_access_1d = trim(str_replace("<br/>(+1D)", "", $last_access_1d));
				} else {
					$last_access_1d =  $last_access_1d;
				}
				
		
				$sheet->setCellValueByColumnAndRow($j, $row_1, $item_ac["first_access_export"]["time"]); // Change fist_access to first_access_export
						
				$sheet->setCellValueByColumnAndRow($j, $row_2, $last_access_1d); // Change last_access to last_access_export				
				
				if ($item_ac["first_access_export"]["remark"] === "T") $sheet->getStyleByColumnAndRow($j, $row_1)->getFont()->getColor()->setARGB('RED');
				if ($item_ac["first_access_export"]["remark"] === "TT") $sheet->getStyleByColumnAndRow($j, $row_1)->getFont()->getColor()->setARGB('FFA500');//ORANGE
				if ($item_ac["last_access_export"]["remark"] === "E") $sheet->getStyleByColumnAndRow($j, $row_2)->getFont()->getColor()->setARGB('RED');
				
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
		//echo '<pre>'; print_r([$d_from, $d_to, $exc]); return;
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
	
	public function date_convert_mm_dd_yyyy($date) {
    // Intentamos convertir con la lógica del valor numérico (excel date)
		if (is_numeric($date)) {
			// Si es un número, es probable que sea una fecha de Excel (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número, intentamos convertir con la lógica de fecha en formato mm/dd/yyyy
		$aux = explode("/", $date);
		if (count($aux) == 3) {
			// Verificamos que la fecha esté en formato mm/dd/yyyy
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}
		
		// Si la fecha no está en un formato esperado, devolvemos null
		return null;
	}
	
	public function delete_data_file_first_modal() {
		$selected_exceptions = $this->input->post('archivos');
		
		//print_r($selected_exceptions);

		if ($selected_exceptions && is_array($selected_exceptions)) {
			foreach ($selected_exceptions as $exception_id) {
				$this->db->where('exception_id', $exception_id);
				$this->db->delete('hr_attendance_exception');
			}
		}

		$response = array('type' => 'success', 'msg' => 'Exceptions deleted successfully.');
		header('Content-Type: application/json');
		echo json_encode($response);
	}

	public function delete_data_file() {
		$archivos_seleccionados = $this->input->post('archivos');

		if ($archivos_seleccionados && is_array($archivos_seleccionados)) {
			log_message('debug', 'Archivos seleccionados para eliminar: ' . print_r($archivos_seleccionados, true));
			foreach ($archivos_seleccionados as $file_path) {
				log_message('debug', 'Ruta del archivo a procesar: ' . $file_path);
				$file_name = basename($file_path);

				// Eliminar de la DB
				$this->db->where('name_file', $file_name);
				$this->db->delete('hr_attendance_exception');

				// Eliminar el archivo físico
				log_message('debug', 'Verificando existencia del archivo: ' . $file_path);
				if (file_exists($file_path)) {
					log_message('debug', 'El archivo existe. Intentando eliminar: ' . $file_path);
					if (unlink($file_path)) {
						log_message('info', 'File Deleted: ' . $file_path);
					} else {
						$error = error_get_last();
						log_message('error', 'Error al eliminar el archivo: ' . $file_path . ' - ' . print_r($error, true));
					}
				} else {
					log_message('error', 'El archivo no existe (unlink): ' . $file_path);
				}
			}
		}

		$response = array('type' => 'success', 'msg' => 'Files deleted successfully.');
		header('Content-Type: application/json');
		echo json_encode($response);
	}
	
	public function delete_db_file_row() {
		if ($this->input->is_ajax_request()) {
			$pr = $this->input->post('pr');
			$exc_date = $this->input->post('exc_date');
			$file_path = $this->input->post('file_path');
			
			if ($file_path) { // Asegúrate de que $file_path no esté vacío
				$aux = explode('/', $file_path);
				$file_path = end($aux); // Obtener el último elemento del array
			}
			//print_r($file_path);
			if ($pr && $exc_date && $file_path) {
				
				$this->db->where('pr', $pr);
				$this->db->where('exc_date', $exc_date);
				$this->db->where('name_file', $file_path);
				$success = $this->db->delete('hr_attendance_exception');

				if ($success) {
					$response = ['success' => true, 'message' => 'Row deleted successfully'];
				} else {
					$response = ['success' => false, 'message' => 'Error deleting the row'];
				}
			} else {
				$response = ['success' => false, 'message' => 'Incomplete data'];
			}

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($response));
		} else {
			show_404(); // Mostrar error 404 si la solicitud no es AJAX
		}
	}
	
	public function data_db_file() {
		$file_path = $this->input->post('file_path');
		$file_name = basename($file_path);

		$this->db->where('name_file', $file_name);
		$query = $this->db->get('hr_attendance_exception');
		echo json_encode($query->result());
	}
	
	public function get_files() {
		$upload_path = FCPATH . 'upload_file/Hr/';
		$local_files = glob($upload_path . '*.xlsx');
		$period = $this->input->get('period');

		$file_data = [];
		$recent_files = [];
		$now = new DateTime();
		$one_week_ago = $now->modify('-1 week');

		// Obtener información de la base de datos para los archivos locales
		$this->db->select('name_file, exc_date'); // Selecciona las columnas necesarias
		$this->db->from('hr_attendance_exception');
		$db_results = $this->db->get()->result_array();

		// Crear un array asociativo para acceder fácilmente a las fechas por nombre de archivo
		$db_file_dates = [];
		foreach ($db_results as $row) {
			$db_file_dates[$row['name_file']] = $row['exc_date'];
		}

		foreach ($local_files as $file) {
			$file_name = basename($file);
			$modified_time = filemtime($file);
			$modified_date = new DateTime('@' . $modified_time);
			$exc_date = isset($db_file_dates[$file_name]) ? $db_file_dates[$file_name] : null;
			$exc_datetime = $exc_date ? new DateTime($exc_date) : null;

			$file_info = [
				'file_path' => str_replace(FCPATH, base_url(), $file),
				'file_name' => $file_name,
				'modified' => $modified_time, // Conservamos la fecha de modificación local (podrías no usarla)
				'exc_date' => $exc_date,
				'exc_timestamp' => $exc_datetime ? $exc_datetime->getTimestamp() : null // Timestamp para facilitar la comparación
			];

			$include_file = true;
			if (!empty($period) && $exc_datetime) {
				// Filtrar por el periodo usando la exc_date de la base de datos
				$period_start = new DateTime($period . '-01');
				$period_end = new DateTime($period . '-01');
				$period_end->modify('last day of this month')->setTime(23, 59, 59);

				if (!($exc_datetime >= $period_start && $exc_datetime <= $period_end)) {
					$include_file = false;
				}
			} elseif (!empty($period) && !$exc_datetime) {
				$include_file = false; // Si hay periodo pero no exc_date en la BD, no incluir
			}

			if ($include_file) {
				// Separate recent files (basado en la exc_date si está disponible, sino en la modified_date)
				$date_to_compare = $exc_datetime ?? $modified_date;
				if ($date_to_compare >= $one_week_ago) {
					$recent_files[] = $file_info;
				}
				$file_data[] = $file_info;
			}
		}

		$files_to_display = !empty($recent_files) ? $recent_files : $file_data;

		// Ordenar por exc_timestamp si está disponible, sino por modified
		usort($files_to_display, function($a, $b) {
			if ($a['exc_timestamp'] !== null && $b['exc_timestamp'] !== null) {
				return $b['exc_timestamp'] - $a['exc_timestamp'];
			} elseif ($a['exc_timestamp'] !== null) {
				return -1; // a con exc_date va primero
			} elseif ($b['exc_timestamp'] !== null) {
				return 1;  // b con exc_date va primero
			} else {
				return $b['modified'] - $a['modified']; // Si ninguno tiene exc_date, ordenar por modificado
			}
		});

		header('Content-Type: application/json');
		echo json_encode($files_to_display);
	}
	
    public function delete_old_files() {
		$upload_path = FCPATH . 'upload_file/Hr/';
		$files = glob($upload_path . '*.xlsx');

		$now = new DateTime();
		$for_weeks_ago = $now->modify('-4 weeks');

		$deleted_count = 0;
		foreach ($files as $file) {
			$modified_time = filemtime($file);
			$modified_date = new DateTime('@' . $modified_time);

			if ($modified_date < $for_weeks_ago) {
				if (unlink($file)) {
					$deleted_count++;
				}
			}
		}

		// Puedes devolver una respuesta JSON si lo llamas desde una petición HTTP
		header('Content-Type: application/json');
		echo json_encode(['message' => $deleted_count . ' old files has been deteled.']);
	}
	
	public function update_db_file_cell() {
		if ($this->input->is_ajax_request()) {
			$pr = $this->input->post('pr');
			$exc_date = $this->input->post('exc_date');
			$file_path = $this->input->post('file_path');
			$column = $this->input->post('column');
			$value = $this->input->post('value');
			$remark = $this->input->post('remark'); // Obtener el valor de "Remark"
			
			//print_r([$pr, $exc_date, $file_path, $column, $value, $remark]);
			if ($file_path) { // Asegúrate de que $file_path no esté vacío
				$aux = explode('/', $file_path);
				$file_path = end($aux); // Obtener el último elemento del array
			}
			
			if ($pr && $exc_date && $file_path && $column && isset($value) && ($column === 'remark' || $remark !== '')) {
			//if ($pr && $exc_date && $file_path && $column && $value && $remark) {
				if ($column === 'type') {
					$validTypes = ['EF', 'BT', 'CE', 'CO', 'CMP', 'HO', 'L', 'MV', 'AV', 'V', 'MED', 'MB', 'AB', 'MBT', 'ABT', 'MCO', 'ACO', 'MCMP', 'ACMP', 'MHO', 'AHO', 'MT', 'AT', 'J', 'NEF', 'S'];
					$isValid = false;
					foreach ($validTypes as $type) {
						if (strtolower($type) === strtolower($value)) {
							$isValid = true;
							break;
						}
					}

					if (!$isValid) {
						$response = ['success' => false, 'message' => 'Invalid Type. Please enter a valid type'];
						$this->output
							->set_content_type('application/json')
							->set_output(json_encode($response));
						return;
					}

					// Convertir el valor a mayúsculas antes de la actualización
					$value = strtoupper($value);
				}
				// Construir la cláusula WHERE para la consulta de actualización
				$this->db->where('pr', $pr);
				$this->db->where('exc_date', $exc_date);
				$this->db->where('name_file', $file_path);
				if ($column !== 'remark') {
					$this->db->where('remark', $remark);
				}
				//$this->db->where('remark', $remark);
				// Actualizar la columna especificada con el nuevo valor
				$this->db->update('hr_attendance_exception', [$column => $value]);

				if ($this->db->affected_rows() > 0) {
					$response = ['success' => true, 'message' => 'Cell updated successfully'];
				} else {
					$response = ['success' => false, 'message' => 'No changes were made or the row does not exist'];
				}
			} else {
				$response = ['success' => false, 'message' => 'Incomplete data'];
			}

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($response));
		} else {
			show_404();
		}
	}
	
	public function update_exception_cell() {
        if ($this->input->is_ajax_request()) {
            $exception_id = $this->input->post('exception_id');
            $column = $this->input->post('column');
            $value = $this->input->post('value');

            if ($exception_id && $column && isset($value)) {
                if ($column === 'type') {
                    $validTypes = ['EF', 'BT', 'CE', 'CO', 'CMP', 'HO', 'L', 'MV', 'AV', 'V', 'MED', 'MB', 'AB', 'MBT', 'ABT', 'MCO', 'ACO', 'MCMP', 'ACMP', 'MHO', 'AHO', 'MT', 'AT', 'J', 'NEF', 'S'];
                    if (!in_array(strtoupper($value), $validTypes)) {
                        $response = ['success' => false, 'message' => 'Tipo inválido. Por favor, ingrese un tipo válido'];
                        $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode($response));
                        return;
                    }
                    $value = strtoupper($value);
                }

                // Construir la cláusula WHERE utilizando el exception_id
                $this->db->where('exception_id', $exception_id);

                // Actualizar la columna especificada con el nuevo valor
                $this->db->update('hr_attendance_exception', [$column => $value]);

                if ($this->db->affected_rows() > 0) {
                    $response = ['success' => true, 'message' => 'Cell updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'No changes were made or the row does not exist'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Incomplete data'];
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            show_404();
        }
    }
	
	public function upload_absenteeism() {
		set_time_limit(0);
		ini_set("memory_limit", -1);

		$start_time = microtime(true);

		// Directorio para guardar los archivos
		$upload_dir = "./upload_file/Hr/";

		// Nombre único para el archivo
		$fecha_actual = date("dmY_His"); // Obtiene la fecha en formato ddmmyyyy
		$originalFileName = $_FILES['attach']['name'];
		$originalFileName = trim(str_replace(".xlsx", "", $originalFileName));
		$file_name = $originalFileName . ' [' . $fecha_actual . ']' . ".xlsx";
		$file_path = $upload_dir . $file_name;

		// Mueve el archivo subido y verifica
		if (rename("./upload/hr_absenteeism.xlsx", $file_path)) {
			//load excel file
			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet(0);

			//excel file header validation
			$h = [
				trim($sheet->getCell('A1')->getValue()),
				trim($sheet->getCell('B1')->getValue()),
				trim($sheet->getCell('C1')->getValue()),
				trim($sheet->getCell('D1')->getValue()),
				trim($sheet->getCell('E1')->getValue()),
				trim($sheet->getCell('F1')->getValue()),
				trim($sheet->getCell('G1')->getValue())
			];

			//magento report header
			$header = ["PR", "NOMBRES", "COD INCIDENCIA", "INCIDENCIA", "DIA", "FECHA INICIO", "FECHA FIN"];

			//header validation
			$is_ok = true;
			foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

			if ($is_ok){
				$updated = date("Y-m-d");
				$email_sent = 0;
				$max_row = $sheet->getHighestRow();
				$batch_data =[];
				$batch_size = 10;

				$incidence_char =["AD001" =>["MB", "Birthday - Morning"], "AD002" => ["MA", "Birthday - Afternoon"], "AD003" => ["BT", "Biz Trip"], "AD004" => ["MBT", "Biz Trip - Morning"], 
								  "AD005" =>["ABT", "Biz Trip - Afternoon"], "AD006" => ["CE", "Ceased"], "AD007" => ["CO", "Commission"], "AD008" => ["MCO", "Commission - Morning"],
								  "AD009" =>["ACO", "Commission - Afternoon"], "AD010" => ["CMP", "Compensation"], "AD011" => ["MCMP", "Compensation - Morning"], "AD012" => ["ACMP", "Compensation - Afternoon"], "AD013" => ["EF", "Early Friday"], "AD014" => ["V", "Vacation"], "AD015" => ["MV", "Vacation - Morning"], "AD016" => ["AV", "Vacation - Afternoon"],
								  "AD017" => ["H", "Holiday"], "AD018" => ["HO", "Home Office"], "AD019" => ["MHO", "Home Office - Morning"], "AD020" => ["AHO", "Home Office - Afternoon"],
								  "AD021" => ["MED", "Medical"], "AD022" => ["L", "License"], "AD023" => ["NEF", "No Early Friday"], "AD024" => ["J", "Justified"], "AD025" => ["MT", "Topic - Morning"], "AD026" => ["AT", "Topic - Afternoon"], "AD027" => ["S", "Suspension"]];
				//print_r($incidence_char); return;
				$remark = [];
				// Iniciar transacción para mejorar rendimiento
				$this->db->trans_start();
				for($i = 2; $i <= $max_row; $i++){
					if(empty(trim($sheet->getCell('C'.$i)->getValue()))) break;
					$row = [
						"pr"                         => trim($sheet->getCell('A'.$i)->getValue()),
						"name"                       => trim($sheet->getCell('B'.$i)->getValue()),
						"incidence_code"             => trim($sheet->getCell('C'.$i)->getValue()),
						"incidence"                  => $incidence_char[trim($sheet->getCell('C'.$i)->getValue())][1],
						"day"                        => trim($sheet->getCell('E'.$i)->getValue()),
						"start_day"                  => trim($sheet->getCell('F'.$i)->getValue()),
						"end_day"                    => trim($sheet->getCell('G'.$i)->getValue()),
						"exception_type"             => $incidence_char[trim($sheet->getCell('C'.$i)->getValue())][0],
						"name_file"            		 => $file_name, // Guardar el nombre del archivo
					];

					$row["start_day"] = $this->date_convert_mm_dd_yyyy($row["start_day"]);
					$row["end_day"] = $this->date_convert_mm_dd_yyyy($row["end_day"]);

					$exc_date = null;
					$batch_entries = [];

					if($row["day"] == 1 || $row["day"] == 0.5){
						$exc_date = $row["start_day"];

						// Verificar si ya existe en la BD
						$exists = $this->db->where("pr", $row["pr"])
										 ->where("exc_date", $exc_date)
										 ->count_all_results("hr_attendance_exception") > 0;

						if(!$exists){
							$batch_entries[] = [
								"pr" => $row["pr"],
								"exc_date" => $exc_date,
								"type" => $row["exception_type"],
								"remark" => $row["incidence"],
								"name_file" => $file_name,
							];
						}
					}

					elseif ($row["day"] > 1) {
						$current_date = strtotime($row["start_day"]);
						$end_date = strtotime($row["end_day"]);

						while ($current_date <= $end_date) {
							$exc_date = date("Y-m-d", $current_date);

							// Verificar si ya existe en la BD
							$exists = $this->db->where("pr", $row["pr"])
											 ->where("exc_date", $exc_date)
											 ->count_all_results("hr_attendance_exception") > 0;

							if (!$exists) {
								$batch_entries[] = [
									"pr" => $row["pr"],
									"exc_date" => $exc_date,
									"type" => $row["exception_type"],
									"remark" => $row["incidence"],
									"name_file" => $file_name,
								];
							}
							$current_date = strtotime("+1 day", $current_date);
						}
					}

					// Manejo de valores vacios end_date_ative
					if (!empty($batch_entries)) {
						$batch_data = array_merge($batch_data ?? [], $batch_entries);
					}

					if(count($batch_data)>=$batch_size){
						$this->gen_m->insert_m("hr_attendance_exception", $batch_data);
						$batch_data = [];
						unset($batch_data);
					}
				}
				// Insertar cualquier dato restante en el lote
				if (!empty($batch_data)) {
					$this->gen_m->insert_m("hr_attendance_exception", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}

				$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
				$this->db->trans_complete();
				return $msg;

			}else return "";
		} else {
			return "Error moving uploaded file.";
		}
	}
	
	public function update(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'hr_absenteeism.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->upload_absenteeism();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function generateDateRange($work_date_min, $work_date_max) {
		$start_date = new DateTime($work_date_min);
		$end_date = new DateTime($work_date_max);
		$end_date->modify('+1 day'); // Incluir la fecha de fin

		$interval = new DateInterval('P1D');
		$date_range = new DatePeriod($start_date, $interval, $end_date);

		$dates = [];
		foreach ($date_range as $date) {
			$dayOfWeek = $date->format('N'); // 'N' devuelve el día de la semana ISO-8601 (1 para lunes, 7 para domingo)
			if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Excluir sábado (6) y domingo (7)
				$dates[] = $date->format('Y-m-d');
			}
		}

		return $dates;
	}
	
	public function get_info_employee(){
		$employee_info = [];
		$employee_data = $this->gen_m->filter('hr_employee', false, ['working' => 1, 'employee_number !=' => 'LGEPR_FK']);
		//foreach($employee_data as $item) $employee_info[$item->employee_number] = ['employee_id'=>$item->employee_id, 'employee_number' => $item->employee_number, 'name' => $item->name]; 
		//echo '<pre>'; print_r($employee_info); echo '</pre>';
		
		foreach ($employee_data as $item) {
			$employee_info[] = [
				'employee_number' => $item->employee_number,
				'name' => $item->name,
				'organization' => $item->organization,
				'department' => $item->department
			];
		}

		header('Content-Type: application/json');
		echo json_encode($employee_info);
	}	
	
	public function real_worked_time($time_in, $real_out, $real_in) {
		// Convertir las horas a objetos DateTime
		$entrada_real = DateTime::createFromFormat('H:i:s', $real_in);
		$entrada_sch = DateTime::createFromFormat('H:i', $time_in);
		// Es importante crear un objeto DateTime completo para la comparación
		$entrada_sch_completa = DateTime::createFromFormat('H:i:s', $time_in . ':00');
		$salida = DateTime::createFromFormat('H:i:s', $real_out);

		if ($entrada_real >= $entrada_sch_completa) {
			$entrada = $entrada_real;
		} else {
			$entrada = $entrada_sch_completa;
		}

		if (!$entrada || !$salida) {
			return [
				'total_trabajado' => '',
				'horas_reales' => '',
				'horas_extras' => '',
			];
		}

		// Verificar si la hora de salida es anterior a la hora de entrada (madrugada)
		if ($salida < $entrada) {
			// Si es así, asumimos que la salida es del día siguiente
			$salida->modify('+1 day');
		}

		// Calcular la diferencia total
		$diferencia = $entrada->diff($salida);
		$totalTrabajadoSegundos = ($diferencia->h * 3600) + ($diferencia->i * 60) + $diferencia->s;

		// Definir el horario de fin del almuerzo (2:00 PM) como objeto DateTime
		$finAlmuerzo = new DateTime('14:00:00');
		// Asegurarse de que $finAlmuerzo esté en el mismo día que la entrada para la comparación
		$finAlmuerzo->setTime($finAlmuerzo->format('H'), $finAlmuerzo->format('i'), $finAlmuerzo->format('s'));
		$finAlmuerzo->setDate($entrada->format('Y'), $entrada->format('m'), $entrada->format('d'));


		// Inicializar el tiempo de almuerzo en segundos a 0
		$tiempoAlmuerzoSegundos = 0;

		// Verificar si la hora de salida (después de la posible modificación del día)
		// es posterior a la hora de fin del almuerzo en el día de entrada.
		// Solo restamos el almuerzo si la salida ocurre después de las 2 PM del día (posiblemente el siguiente).
		$salidaParaAlmuerzo = clone $salida; // Clonamos para no modificar el objeto original
		if ($salidaParaAlmuerzo > $finAlmuerzo) {
			// Tiempo de almuerzo en segundos (1 hora y media = 90 minutos = 5400 segundos)
			$tiempoAlmuerzoSegundos = 5400;
		}

		// Calcular las horas reales trabajadas restando el tiempo de almuerzo (si aplica)
		$horasRealesSegundos = $totalTrabajadoSegundos - $tiempoAlmuerzoSegundos;
		if ($horasRealesSegundos < 0) {
			$horasRealesSegundos = 0; // No puede ser negativo
		}

		// Definir el mínimo de horas (8 horas y 30 minutos = 8.5 horas = 30600 segundos)
		$minimoTrabajoSegundos = (8 * 3600) + (30 * 60);

		// Formatear el resultado total trabajado
		$totalTrabajadoFormateado = sprintf('%02d:%02d:%02d', $diferencia->h, $diferencia->i, $diferencia->s);

		// Formatear las horas reales trabajadas
		$horasRealesFormateado = sprintf('%02d:%02d:%02d', floor($horasRealesSegundos / 3600), floor(($horasRealesSegundos % 3600) / 60), $horasRealesSegundos % 60);

		// Calcular las horas extras
		$horasExtrasSegundos = 0;
		$horasExtrasFormateado = '00:00:00';

		if ($horasRealesSegundos >= $minimoTrabajoSegundos) {
			$horasRealesGuardar = '08:30:00';
			if ($horasRealesSegundos > $minimoTrabajoSegundos) {
				$horasExtrasSegundos = $horasRealesSegundos - $minimoTrabajoSegundos;
				$horasExtrasFormateado = sprintf('%02d:%02d:%02d', floor($horasExtrasSegundos / 3600), floor(($horasExtrasSegundos % 3600) / 60), $horasExtrasSegundos % 60);
			}
		} else {
			$horasRealesGuardar = $horasRealesFormateado;
		}

		return [
			'total_trabajado' => $totalTrabajadoFormateado,
			'horas_reales' => $horasRealesGuardar,
			'horas_extras' => $horasExtrasFormateado,
		];
	}

	public function generate_report_attendance(){
		ini_set('memory_limit', -1);
		set_time_limit(0);

		$template_path = './template/attendance_report_template.xlsx';
		if (!file_exists($template_path)) {
			echo "Error: No se encontró la plantilla de Excel.";
			return;
		}

		$employee_numbers_json = $this->input->post('prCodes');
		$from_date = $this->input->post('fromDate');
		$to_date = $this->input->post('toDate');
		$departments_json = $this->input->post('departments');


		$employee_numbers = [];

		if ($departments_json) {
			$departments = json_decode($departments_json);
			log_message('debug', 'Valor de $departments después de json_decode: ' . print_r($departments, true));
			if (is_array($departments)) {
				$employee_list_from_dept = [];
				foreach ($departments as $dept_array) {
					if (is_array($dept_array) && count($dept_array) === 2) {
						$organization = trim($dept_array[0]);
						$department = trim($dept_array[1]);
						log_message('debug', 'Buscando empleados para Organización: "' . $organization . '", Departamento: "' . $department . '"');
						$employees = $this->gen_m->filter('hr_employee', false, ['working' => 1, 'organization' => $organization, 'department' => $department, 'employee_number !=' => 'LGEPR_FK']);
						log_message('debug', 'Resultados de la consulta ($employees) para Organización "' . $organization . '", Departamento "' . $department . '": ' . print_r($employees, true));
						foreach ($employees as $emp) {
							if (!in_array($emp->employee_number, $employee_list_from_dept)) {
								$employee_list_from_dept[] = $emp->employee_number;
							}
						}
					} else {
						log_message('warning', 'Estructura incorrecta en el array de departamentos: ' . print_r($dept_array, true));
					}
				}
				log_message('debug', 'Lista de employee_number obtenidos por departamento ($employee_list_from_dept): ' . print_r($employee_list_from_dept, true));
				$employee_numbers = $employee_list_from_dept;
			} else {
				log_message('error', '$departments no es un array después de json_decode.');
			}
		} elseif ($employee_numbers_json) {
			// Lógica para obtener employee_numbers directamente
			$employee_numbers = json_decode($employee_numbers_json);
			log_message('debug', '$employee_numbers: '. print_r($employee_numbers, true));
		} else {
			echo "Error: No se seleccionaron empleados o departamentos.";
			return;
		}

		if (!is_array($employee_numbers) || empty($employee_numbers)) {
			echo "Error: No se encontraron empleados para generar el reporte.";
			return;
		}

		$pr_exclude = ['PR009297', 'PR009182']; //exclude CEO's PR
		//$employee_numbers = array_diff($employee_numbers, $pr_exclude);

		$where = [
			'work_date >=' => $from_date,
			'work_date <=' => $to_date
		];

		$spreadsheet = IOFactory::load($template_path);
		$sheet = $spreadsheet->getActiveSheet();

		$row_num = 2;
		foreach($employee_numbers as $employee_number){
			$employee_info = [];
			$employee_data = $this->gen_m->filter('hr_employee', false, ['employee_number' => $employee_number]);
			foreach($employee_data as $item) $employee_info = $item; 
			//print_r($employee_info); return;
			
			$employee_att = $this->gen_m->filter('v_hr_attendance_summary', false, ['pr' => $employee_number, 'work_date >=' => $from_date, 'work_date <=' => $to_date]);		
			$employee_access = [];
			foreach($employee_att as $i => $item) $employee_access[] = $item;
			//print_r($employee_access);
			
			
			// $employee_access = $this->gen_m->filter('hr_attendance', false, ['pr' => $employee_number, 'access' => $from_date."%"]);
			// $work_date_range = [];
			// foreach ($employee_access as $item) {
				// $work_date_range[] = $item->work_date;
			// }
			
			//first & last date
			//$from = date("Y-m-01", strtotime($period));
			//$to = date("Y-m-t", strtotime($period));
			
			/* load 00:00:00 ~ 03:59:59 check datas (< from and <= (to + 1) */
			//$w_dch = ["access >" => $from, "access <=" => date("Y-m-d", strtotime("+1 day", strtotime($to)))];
			//$l_dch = [["field" => "pr", "values" => ["PR"]]];
			//$dawn_checks = $this->gen_m->filter("v_hr_attendance_dawn", false, $w_dch, null, null, [['access'. 'desc']]);
			$dawn_checks = $this->gen_m->filter("v_hr_attendance_dawn", false, ['pr' => $employee_number, "access >" => $from_date, "access <=" => date("Y-m-d", strtotime("+1 day", strtotime($to_date)))], null, null, [['access', 'desc']]);
			
			//echo '<pre>'; print_r($dawn_checks);
			// $dawn_checks_arr = [];
			// foreach($dawn_checks as $item){
				// $dawn_checks_arr[$item->pr."_".date("Y-m-d", strtotime("-1 day", strtotime($item->access)))] = $item;
				// //print_r($item); echo "<br/>";
			// }
			// $work_date_max = max($work_date_range);
			// $work_date_min = min($work_date_range);
			
			$work_date_max = $to_date;
			$work_date_min = $from_date;
			$dates = $this->generateDateRange($work_date_min, $work_date_max);
			//print_r($dates); return;
			$employee_work = [];
			$employee_schedule = $this->gen_m->filter('hr_schedule', false, ['pr' => $employee_number], null, null, [['date_from', 'ASC']]);	
			foreach($employee_schedule as $item) $employee_work[] = $item;
			//print_r($employee_work); return;
			
			$emp_exception = [];
			$att_exception = $this->gen_m->filter('hr_attendance_exception', false, ['pr' => $employee_number], null, null);
			foreach($att_exception as $item_exception) $emp_exception[] = $item_exception;
			//print_r($emp_exception); return;
			
			$emp_exception_lgepr = [];
			$att_exception_lgepr = $this->gen_m->filter('hr_attendance_exception', false, ['pr' => 'LGEPR'], null, null);
			foreach($att_exception_lgepr as $item_exception) $emp_exception_lgepr[] = $item_exception;
			//print_r($emp_exception_lgepr); return;
			//$row_num = 2;
			
			$schedule_emp = $this->gen_m->filter_select('hr_schedule', false, ['work_start', 'work_end'], ['pr' => $employee_number], null, null, [['date_from', 'desc']]);
			
			foreach ($schedule_emp as $item_sch) {
				
				$work_start_obj = DateTime::createFromFormat('H:i:s', $item_sch->work_start);
				$work_end_obj = DateTime::createFromFormat('H:i:s', $item_sch->work_end);
				
				$item_sch->work_start = $work_start_obj->format('H:i') ?? '';
				$item_sch->work_end = $work_end_obj->format('H:i') ?? '';
			}
			//print_r($schedule_emp); return;
			$exc_list = [];
			$days_week_es = ['Monday'=>'Lunes', 'Tuesday'=>'Martes', 'Wednesday'=>'Miercoles', 'Thursday'=>'Jueves', 'Friday'=>'Viernes', 'Saturday'=>'Sabado', 'Sunday'=>'Domingo'];
			foreach ($dates as $fecha_str) {
				$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str);
				$day_week = $fecha_obj ? $fecha_obj->format('l') : '';
				
				for ($i=0;$i<=count($employee_work)-1;$i++){
					// $current_work_start = $employee_work[$i]->work_start;
					// $current_work_end = $employee_work[$i]->work_end;
					$current_date_from = $employee_work[$i]->date_from;
					
					if ($current_date_from <= $fecha_str){
						// $work_start = $employee_work[$i]->work_start;
						// $work_end = $employee_work[$i]->work_end;
						//$date_from = $employee_work[$i]->date_from;
						$index	= $i;					
					} else {
						break;				
					}
				}
				//print_r($index); return;
				// Buscar datos de asistencia para la fecha actual
				$employee_att = null;
				foreach ($employee_access as $item) {
					if ($item->work_date === $fecha_str) {
						$employee_att = $item;
						break;
					}
				}

				$real_in = '';
				$real_out = '';
				$time_in = '';
				$time_out = '';
				$days_worked = '';

				if ($employee_att) {
					$fecha_hora_str_first = $employee_att->first_access;
					$fecha_hora_str_last = $employee_att->last_access;

					$fecha_hora_obj_first = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_hora_str_first);
					$fecha_hora_obj_last = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_hora_str_last);

					if ($fecha_hora_obj_first && $fecha_hora_obj_last) {
						$real_in = $fecha_hora_obj_first->format('H:i:s');
						$real_out = $fecha_hora_obj_last->format('H:i:s');
					}

					if ($employee_work) {
						$time_in_str = $employee_work[$index]->work_start;
						$time_out_str = $employee_work[$index]->work_end;

						$time_in_obj = DateTime::createFromFormat('H:i:s', $time_in_str);
						$time_out_obj = DateTime::createFromFormat('H:i:s', $time_out_str);

						if ($time_in_obj && $time_out_obj) {
							$time_in = $time_in_obj->format('H:i');
							$time_out = $time_out_obj->format('H:i');
						}
					}

					if (!empty($real_in) && !empty($real_out)) {
						$days_worked = 1;
					}
				}

				$dept_name = $employee_info->department;
				
				

				$sheet->setCellValue("A" . $row_num, $employee_number ?? "");
				$sheet->setCellValue("B" . $row_num, $employee_info->organization ?? "");
				$sheet->setCellValue("C" . $row_num, $dept_name ?? "");
				$sheet->setCellValue("D" . $row_num, $employee_info->name ?? "");
				$sheet->setCellValue("E" . $row_num, $employee_info->location ?? "");
				$sheet->setCellValue("F" . $row_num, 'Lun-Vier' ?? "");
				//$sheet->setCellValue("G" . $row_num, $days_week_es[$day_week] ?? "");
				$sheet->setCellValue("G" . $row_num, $day_week ?? "");
				
				if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha_str, $matches)) {
					$año = $matches[1];
					$mes = $matches[2];
					$dia = $matches[3];
					$fecha_export = sprintf('%02d/%02d/%04d', $mes, $dia, $año);
				}
				$sheet->setCellValue("H" . $row_num, $fecha_export ?? "");
				
				//
				if (!empty($employee_work[$index]->work_start) && !empty($employee_work[$index]->work_end)){
					$work_start_obj = DateTime::createFromFormat('H:i:s', $employee_work[$index]->work_start);
					$work_end_obj = DateTime::createFromFormat('H:i:s', $employee_work[$index]->work_end);
					
					$work_start_format = $work_start_obj->format('H:i') ?? '';
					$work_end_format = $work_end_obj->format('H:i') ?? '';
				}
				
				// $work_start_format = $work_start_obj->format('H:i') ?? '';
				// $work_end_format = $work_end_obj->format('H:i') ?? '';
				//
				
				if($days_week_es[$day_week] === 'Sabado' || $days_week_es[$day_week] === 'Domingo'){
					$sheet->setCellValue("J" . $row_num, 'Rest Day' ?? "");
					$sheet->setCellValue("K" . $row_num, 'Rest Day' ?? "");
				}
				else {
					// $sheet->setCellValue("J" . $row_num, $time_in ?? "");
					// $sheet->setCellValue("K" . $row_num, $time_out ?? "");
					$sheet->setCellValue("J" . $row_num, $work_start_format ?? "");
					$sheet->setCellValue("K" . $row_num, $work_end_format ?? "");
					
					// $sheet->setCellValue("J" . $row_num, $schedule_emp[0]->work_start ?? "");
					// $sheet->setCellValue("K" . $row_num, $schedule_emp[0]->work_end ?? "");
				} 
				
				// Columna I
				
				
				
				if (empty($work_start_format)){
					$work_start_sch = '';
					$work_schedule = '';
				} else {
					$work_start_sch = $work_start_format;
					$work_schedule = $work_start_format . " - " . $work_end_format;
				}
				
				// // Columna I
				// if (empty($schedule_emp[0]->work_start)){
					// $work_start_sch = '';
					// $work_schedule = '';
				// } else {
					// $work_start_sch = $schedule_emp[0]->work_start;
					// $work_schedule = $schedule_emp[0]->work_start . " - " . $schedule_emp[0]->work_end;
				// }
				
				
				$sheet->setCellValue("I" . $row_num, $work_schedule ?? "");
		
				$sheet->setCellValue("L" . $row_num, $real_in ?? "");
				
				
				// Column M
				if(!empty($dawn_checks) && $fecha_str === date("Y-m-d", strtotime("-1 day", strtotime($dawn_checks[0]->access)))){
					$real_out = date("H:i:s", strtotime($dawn_checks[0]->access));
					$sheet->setCellValue("M" . $row_num, $real_out ?? "");
				}
				else $sheet->setCellValue("M" . $row_num, $real_out ?? "");
				//print_r($real_out);
				
				// Llenado de columna N, worked hours
				
				if (empty($work_start_format)){
					$work_real_time = '';
				}
				else{
					foreach ($emp_exception as $item){
						if ($item->exc_date === $fecha_str && $item->remark === 'Early Friday') {
							$work_start_format = '08:30';
							break;
						}
					}
					//if ($exc === 'Early Friday') $work_start_format = '08:30';
					$work_time = $this->real_worked_time($work_start_format, $real_out, $real_in);
					$work_real_time = $work_time['horas_reales'];
				}
				
				// if (empty($schedule_emp[0]->work_start)){
					// $work_real_time = '';
				// }
				// else{
					// $work_time = $this->real_worked_time($schedule_emp[0]->work_start, $real_out, $real_in);
					// $work_real_time = $work_time['horas_reales'];
				// }
								
				$sheet->setCellValue("N" . $row_num, $work_real_time ?? "");
				
				// Convertir las cadenas de tiempo a marcas de tiempo
				$marca_tiempo_in = strtotime("1970-01-01 " . $time_in);
				$marca_tiempo_real_in = strtotime("1970-01-01 " . $real_in);
				
				// Verificar si la conversión fue exitosa
				if ($marca_tiempo_in === false || $marca_tiempo_real_in === false) {
					return "Error: Formato de tiempo incorrecto";
				}
				
				if(!empty($real_in)){
					if($marca_tiempo_real_in >= $marca_tiempo_in){
						// Calcular la diferencia en segundos
						$diferencia_segundos = abs($marca_tiempo_real_in - $marca_tiempo_in);

						// Convertir la diferencia a horas y minutos
						$horas = floor($diferencia_segundos / 3600);
						$minutos = floor(($diferencia_segundos % 3600) / 60);
						$segundos = $diferencia_segundos % 60;
						$delay = sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
						$sheet->setCellValue("O" . $row_num, $delay);
						
						// $sheet->setCellValue("K" . $row_num, $real_in ?? "");
						$cell = $sheet->getCell('L' . ($row_num));
						$style = $cell->getStyle();
						// Para cambiar el color del texto a rojo:
						$color = new \PhpOffice\PhpSpreadsheet\Style\Color();
						$color->setRGB('FF0000');
						$style->getFont()->setColor($color);
						//$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FF0000');
						
					} else $sheet->setCellValue("O" . $row_num, "");
				} else $sheet->setCellValue("O" . $row_num, "");
				
				// if($real_in > $time_in){
					// $sheet->setCellValue("M" . $row_num, $real_in - $time_in);
				// } else $sheet->setCellValue("M" . $row_num, "");
				
				//'=IF(I'.$row_num.'<>"Rest Day",IF(L'.$row_num.'<J'.$row_num.',1,""),"")'
				$formula_n = '=IF(J'.$row_num.'<>"Rest Day",IF(M'.$row_num.'<J'.$row_num.',1,""),"")';
				//$sheet->setCellValue("N" . $row_num, $formula_n ?? "");
				
				$absentismo_work = ['Commission - Morning', 'Commission - Afternoon', 'Commission', 'Home Office - Morning', 'Home Office - Afternoon', 'Home Office', 'Compensation'];
				$morning_exception = ['Birthday - Morning', 'Biz Trip - Morning', 'Commission - Morning', 'Compensation - Morning', 'Vacation - Morning', 'Home Office - Morning', 'Topic - Morning', 'Justified'];
				$afternoon_exception = ['Birthday - Afternoon', 'Biz Trip - Afternoon', 'Commission - Afternoon', 'Compensation - Afternoon', 'Vacation - Afternoon', 'Home Office - Afternoon', 'Topic - Afternoon', 'Justified'];
				
				$sheet->setCellValue("P" . $row_num, $days_worked ?? "");
				
				
				
				// Buscar datos de absentismo para la fecha actual
				foreach ($emp_exception as $i => $item_exc) {
					if($item_exc->exc_date === $fecha_str){
						$remark_exc = $item_exc->remark;
						$exc_list = $item_exc->remark;
						$sheet->setCellValue("Q" . $row_num, $remark_exc ?? "");
						
						if (in_array($remark_exc, $absentismo_work)){
							$sheet->setCellValue("P" . $row_num, 1 ?? "");
						}
						if (in_array($remark_exc, $morning_exception)){
							$cell = $sheet->getCell('L' . ($row_num));
							$style = $cell->getStyle();
							// Para cambiar el color del texto a negro:
							$color = new \PhpOffice\PhpSpreadsheet\Style\Color();
							$color->setRGB('000000');
							$style->getFont()->setColor($color);
							
							$cell = $sheet->getCell('N' . ($row_num));
							$style = $cell->getStyle();
							$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');
							
							$sheet->setCellValue("O" . $row_num, "" ?? "");
						}
						
						if (in_array($remark_exc, $afternoon_exception)){
							//$sheet->setCellValue("N" . $row_num, "08:30:00" ?? "");
							$cell = $sheet->getCell('N' . ($row_num));
							$style = $cell->getStyle();
							$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');
						}
						
						if ($remark_exc === 'Vacation'){
							$cell = $sheet->getCell('Q' . ($row_num));
							$style = $cell->getStyle();
							$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
						}
						if ($remark_exc === 'Early Friday'){
							$columns_yellow = ['J', 'K'];
							foreach ($columns_yellow as $column) {
			
								$cell = $sheet->getCell($column . ($row_num));
								$style = $cell->getStyle();
								$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCFF99');

							}
						}
						
						
						break;
					}else $remark_exc = '';
				}
				
				//Company Exception
				foreach($emp_exception_lgepr as $i => $item_exc_lgepr){
					if($item_exc_lgepr->exc_date === $fecha_str){
						
						if($item_exc_lgepr->type === 'H'){
							$remark_exc_company = 'Holiday';
							$sheet->setCellValue("Q" . $row_num, $remark_exc_company ?? "");
						// elseif($item_exc_lgepr->type === 'NEF'){
						}
						elseif($item_exc_lgepr->type === 'EF' && $exc_list !== 'No Early Friday' && $exc_list !== 'Vacation' && $exc_list !== 'Justified'){
							$remark_exc_company = 'Early Friday';
							$sheet->setCellValue("Q" . $row_num, $remark_exc_company ?? "");
							
							$cell = $sheet->getCell('L' . ($row_num));
							$style = $cell->getStyle();
							// Para cambiar el color del texto a negro:
							$color = new \PhpOffice\PhpSpreadsheet\Style\Color();
							$color->setRGB('000000');
							$style->getFont()->setColor($color);
							
							$time_in_ef = '08:30:00';
							$time_in_ef = strtotime("1970-01-01 " . $time_in_ef);
							if ($marca_tiempo_real_in >= $time_in_ef){
								// Pintar rojo valores de la columna L
								$cell = $sheet->getCell('L' . ($row_num));
								$style = $cell->getStyle();

								$color = new \PhpOffice\PhpSpreadsheet\Style\Color();
								$color->setRGB('FF0000');
								$style->getFont()->setColor($color);

							} else {
								// Pintar negro
								$cell = $sheet->getCell('L' . ($row_num));
								$style = $cell->getStyle();
								$color = new \PhpOffice\PhpSpreadsheet\Style\Color();
								$color->setRGB('000000');
								$style->getFont()->setColor($color);
							}
						}
							// $remark_exc = 'No Early Friday';
							// $sheet->setCellValue("P" . $row_num, $remark_exc ?? "");
						// }
						break;
					}else $remark_exc_company = '';
				}
				

				// Aplicar borde a todas las columnas
				$max_column = 17;
				for ($col_index = 0; $col_index < $max_column; $col_index++) {
					$column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index + 1);
					$cell = $sheet->getCell($column . ($row_num));
					$style = $cell->getStyle();
					$style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('00000000');
				}

				$columns_yellow = ['J', 'K'];
				foreach ($columns_yellow as $column) {
					if($remark_exc === 'Early Friday' || $remark_exc === 'Holiday' || $remark_exc_company === 'Early Friday'){
						$cell = $sheet->getCell($column . ($row_num));
						$style = $cell->getStyle();
						$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCFF99');
						
						// Columna L horario de entrada para todos 08:30 am
						
					}
					else {
						$cell = $sheet->getCell($column . ($row_num));
						$style = $cell->getStyle();
						$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFCC');
					}
				}

				$columns_blue = ['L', 'M'];
				foreach ($columns_blue as $column) {
					$cell = $sheet->getCell($column . ($row_num));
					$style = $cell->getStyle();
					$style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');
				}

				$row_num++;
			}
		}
		//$this->downloadSpreadsheet($spreadsheet, "attendance_report_" . $employee_number . ".xlsx");
		$this->downloadSpreadsheet($spreadsheet, "attendance_report.xlsx");
	}
	
	public function downloadSpreadsheet($spreadsheet, $filename) {		

		// Configurar cabeceras de respuesta	
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		
		$tempFile = tempnam(sys_get_temp_dir(), 'excel');	
		// Escribir directamente al output
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		//$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		
		$writer->setPreCalculateFormulas(false);
		$writer->setUseDiskCaching(true); // Activa el uso de caché en disco
		ob_end_clean();
		$writer->save($tempFile);
		//$writer->save('php://output'); // Enviar directamente sin archivo temporal
		readfile($tempFile);
		
		// Limpiar la memoria
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);
		// Recolectar ciclos de basura
		gc_collect_cycles();
			
	}
}
