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
		
		$employees = [];
		$employees_rec = $this->gen_m->filter("hr_employee", false, ["active" => true], null, null, $order);
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
		
		//access records load
		$w = ["work_date >=" => $from, "work_date <=" => $to];
		$l = [["field" => "pr", "values" => ["PR"]]];
		$prs = [-1];//used to load valid emmployee's schedules
		
		$records = $this->gen_m->filter("v_hr_attendance_summary", false, $w, $l);
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
				
				$prs[] = $item->pr;
				$employees[$item->pr]["summary"]["check_days"]++;
				$employees[$item->pr]["access"][$day]["first_access"]["time"] = $first_time;
				$employees[$item->pr]["access"][$day]["last_access"]["time"] = $last_time;
			}
		}
		
		//work schedule validation
		sort($prs);
		$prs = array_unique($prs);
		$prs = array_values($prs); //print_r($prs); echo "<br/><br/>";
		
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
		*/
		
		$no_attn_days = ["Sat", "Sun"];
		foreach($employees as $pr => $item){
			foreach($item["access"] as $aux => $access){
				$day_pivot = date("Y-m-", strtotime($from)).$access["day"];
				
				if (!in_array(date("D", strtotime($day_pivot)), $no_attn_days)){
					
					if ($access["first_access"]["time"]){
						$start = strtotime($schedule_pr[$pr][$day_pivot]["start"]);
						$first = strtotime($access["first_access"]["time"]);
						
						if ($start < $first){
							$employees[$pr]["summary"]["tardiness"]++;
							$employees[$pr]["access"][$access["day"]]["first_access"]["remark"] = "T";
						}
					}
					
					if ($access["last_access"]["time"]){
						$end = strtotime($schedule_pr[$pr][$day_pivot]["end"]);
						$last = strtotime($access["last_access"]["time"]);
						
						if ($last < $end){
							$employees[$pr]["summary"]["early_out"]++;
							$employees[$pr]["access"][$access["day"]]["last_access"]["remark"] = "E";
						}
					}
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
		
		$periods = [];
		$jan = date("Y-01"); 
		$now = date("Y-m");
		while(strtotime($now) >= strtotime($jan)){
			$periods[] = $now;
			$now = date("Y-m", strtotime($now." -1 month"));
		}
		
		$data = [
			"period" => $period,
			"periods" => $periods,
			"from" => $from,
			"to" => $to,
			"days" => $days,
			"days_week" => $days_week,
			"free_days" => $free_days,
			"employees" => $employees,
			"schedule_pr" => $schedule_pr,
			"main" => "module/hr_attendance/index", 
		];
		
		$this->load->view('layout', $data);
	}
		
}
