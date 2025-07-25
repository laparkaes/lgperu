<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Hr_employee extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
		$this->load->model('vacation_model', 'vac_m');
		$this->load->model('working_hour_model', 'whour_m');
	}
	/*
	public function hc_update(){
		echo "HC 20241107<br/><br/>";
		
		$val = '[["PR008255","Accounting"],["PR009311","Accounting"],["PR009175","Accounting"],["PR009364","Air Solution"],["PR009224","Air Solution"],["PR008956","AR"],["PR009333","AR"],["PR009342","AR"],["PR009329","AV Product"],["PR009060","AV Product"],["PR009246","AV Product"],["PR009239","B2B2C"],["PR009237","Brand Marketing"],["PR009289","Brand Marketing"],["PR009180","Brand Marketing"],["PR008210","Business Management"],["PR008672","CE Sales"],["PR009297","CFO"],["PR008857","CIC Operation"],["PR009339","CIC Operation"],["PR009358","Customs"],["PR009344","Customs"],["PR001736","Finance"],["PR001849","Financial Control"],["PR008206","GP"],["PR009321","GP"],["PR009291","HA / HE Provincia"],["PR009305","HA Product"],["PR009230","HA Product"],["PR009232","HA Product"],["PR009179","HA Product"],["PR008756","HA Product"],["PR001100","HA Sales"],["PR008667","HA Sales"],["PR009250","HA Sales"],["PR009200","HA Sales"],["PR009212","HA Sales"],["PR009360","HA Sales"],["PR009011","HE Sales"],["PR009105","HE Sales"],["PR100000","HE Sales"],["PR009254","HE Sales"],["PR009350","HE Sales"],["PR009220","HE Sales"],["PR009255","Human Resources"],["PR009153","Human Resources"],["PR009361","Human Resources"],["PR009252","Human Resources"],["PR009370","Human Resources"],["PR009331","ID Sales"],["PR008997","ID Sales"],["PR009177","ID Sales"],["PR009287","ID Sales"],["PR009103","ID Sales"],["PR009234","ISM"],["PR001724","IT Sales"],["PR009348","IT Sales"],["PR009317","IT Sales"],["PR008451","IT&ID Product"],["PR009216","IT&ID Product"],["PR009352","IT&ID Product"],["PR009226","IT/ID PM"],["PR009238","IT/ID PM"],["PR009129","Legal"],["PR009326","Legal"],["PR008656","LG SVC Center"],["PR009102","LG SVC Center"],["PR008644","LG SVC Center"],["PR008901","LG SVC Center"],["PR008641","LG SVC Center"],["PR008860","LG SVC Center"],["PR008754","LG SVC Center"],["PR009286","LG SVC Center"],["PR009182","LGEPR"],["PR008295","Marketing"],["PR009229","Marketing"],["PR008522","OBS"],["PR009335","OBS"],["PR009027","OBS"],["PR009354","OBS"],["PR009156","OBS"],["PR008737","PI"],["PR009337","PI"],["PR008985","PI"],["PR008208","Planning"],["PR009100","Planning"],["PR009325","Planning"],["PR008161","Promotor/Retail Marketing CE"],["PR009132","Promotor/Retail Marketing CE"],["PR009070","Promotor/Retail Marketing CE"],["PR009242","Promotor/Retail Marketing CE"],["PR009243","Promotor/Retail Marketing CE"],["PR001808","RAC Sales"],["PR008978","SAC Engineering"],["PR009133","SAC Engineering"],["PR009162","SAC Engineering"],["PR009184","SAC Sales"],["PR008780","SAC Sales"],["PR009109","SAC Sales"],["PR009303","SAC Sales"],["PR009346","SAC Sales"],["PR009015","Sales Admin"],["PR009064","Sales Admin"],["PR009172","Sales Admin"],["PR009301","Sales Admin"],["PR001871","SCM"],["PR008941","SCM"],["PR009207","SCM"],["PR009327","SCM"],["PR009210","SCM"],["PR009356","SCM"],["PR009368","SCM"],["PR009134","SOM"],["PR008144","SOM"],["PR009130","SOM"],["PR009201","SOM"],["PR009174","SOM"],["PR009310","SOM"],["PR009115","SVC"],["PR001847","SVC Networks & Parts"],["PR001703","SVC Networks & Parts"],["PR008200","SVC Networks & Parts"],["PR001762","SVC Networks & Parts"],["PR008457","SVC Networks & Parts"],["PR009160","SVC Networks & Parts"],["PR009366","SVC Networks & Parts"],["PR009299","SVC Networks & Parts"],["PR009082","Tax Part"],["PR009092","Tax Part"],["PR009062","Tax Team"],["PR008652","Technical Support"],["PR009031","Technical Support"],["PR009236","Technical Support"],["PR008733","Technical Support"],["PR008793","Trade Marketing"],["PR009119","Treasury"],["PR009309","Treasury"],["PR009113","TV Product"],["PR008969","TV Product"],["PR009033","TV Product"]]';
		
		$rows = json_decode($val);
		
		foreach($rows as $item) $this->gen_m->update("hr_employee", ["employee_number" => $item[0]], ["department" => $item[1]]);
		
		//subsidiary, organization assigned
		$emps = $this->gen_m->only_multi("hr_employee", ["subsidiary", "organization", "department"]);
		foreach($emps as $item){
			if ($item->subsidiary and $item->organization and $item->department){
				//print_r($item); echo "<br/>";
				$this->gen_m->update("hr_employee", ["department" => $item->department], ["subsidiary" => $item->subsidiary, "organization" => $item->organization, "department" => $item->department]);
			}
		}
		
		echo "Done!!!";
	}
	*/
	public function index(){
		$employees = $this->gen_m->filter("hr_employee", false, ["active" => true], null, null, [["subsidiary", "asc"], ["organization", "asc"], ["department", "asc"], ["name", "asc"]]);
		
		$data = [
			"employees" => $employees,
			"main" => "module/hr_employee/index",
		];
		$this->load->view('layout', $data);
	}

	public function edit($employee_id){
		$emp = $this->gen_m->unique("hr_employee", "employee_id", $employee_id, false);
		
		$today = date("Y-m-d");
		$work_schedule_now = null;
		$work_schedule = $this->gen_m->filter("hr_schedule", false, ["pr" => $emp->employee_number], null, null, [["date_from", "desc"]]);
		foreach($work_schedule as $i => $item){
			if (strtotime($item->date_from) <= strtotime($today)) $work_schedule_now = $item;
			//else unset($work_schedule[$i]);
		}
		
		$emp->work_sch = $work_schedule_now ? date("H:i", strtotime($work_schedule_now->work_start))." ~ ".date("H:i", strtotime($work_schedule_now->work_end)) : "";
		$emp->dpt = $emp->subsidiary." > ".$emp->organization." > ".$emp->department;
		
		$schs = [
			"07:00 ~ 17:00",
			"07:30 ~ 16:30",
			"07:30 ~ 17:30",
			"08:00 ~ 18:00",
			"08:30 ~ 18:30",
			"09:00 ~ 19:00",
			"09:30 ~ 19:30",
		];
		
		$f = ["subsidiary", "organization", "department"];
		$dpts = [];
		$dpts_rec = $this->gen_m->only_multi("hr_employee", $f, ["department !=" => ""], $f);
		foreach($dpts_rec as $item) $dpts[] = $item->subsidiary." > ".$item->organization." > ".$item->department;
		sort($dpts);
		
		$data = [
			"subs" 		=> $this->gen_m->only("hr_employee", "subsidiary"),
			"orgs" 		=> $this->gen_m->only("hr_employee", "organization"),
			"dpts" 		=> $dpts,
			"emp"		=> $emp,
			"w_schs"	=> $work_schedule,
			"schs"		=> $schs,
			"main" 		=> "module/hr_employee/edit",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function create(){	
		$schs = [
			"07:00 ~ 17:00",
			"07:30 ~ 16:30",
			"07:30 ~ 17:30",
			"08:00 ~ 18:00",
			"08:30 ~ 18:30",
			"09:00 ~ 19:00",
			"09:30 ~ 19:30",
		];
		
		$f = ["subsidiary", "organization", "department"];
		$dpts = [];
		$dpts_rec = $this->gen_m->only_multi("hr_employee", $f, ["department !=" => ""], $f);
		foreach($dpts_rec as $item) $dpts[] = $item->subsidiary." > ".$item->organization." > ".$item->department;
		sort($dpts);
		
		$data = [
			"dpts" 		=> $dpts,
			"schs"		=> $schs,
			"main" 		=> "module/hr_employee/create",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function save_create_data(){
		
		$sub_org_dpt = $this->input->post('dpt');
		$aux = explode('>', $sub_org_dpt);
		$data = array(
			'subsidiary' 		=> trim($aux[0]),
			'organization' 		=> trim($aux[1]),
			'department'		=> trim($aux[2]),
			'location' 			=> $this->input->post('location'),
			'employee_number' 	=> $this->input->post('employee_number'),
			'ep_mail' 			=> $this->input->post('ep_mail'),
			'password' 			=> '$2y$10$OuF068yS5HokUgSY3Lw8YO1.mtiHfnNfAyFja7RgW.DT0yuXmYOQO',
			'name' 				=> $this->input->post('name'),
			'is_leader'			=> $this->input->post('')??0,
			'is_supervised' 	=> $this->input->post('')??0,
			'access' 			=> $this->input->post('')??0,
			'working' 			=> 1,
			'active' 			=> $this->input->post('active') ? true : false
		);
		$insert_id = $this->gen_m->insert("Hr_employee", $data);
		
		$last_id = $this->gen_m->filter_select("Hr_employee", false, 'employee_id', ['employee_number' => $this->input->post('employee_number')]);

		header('Content-Type: application/json');
        if ($insert_id) {
            // Success		
            echo json_encode(["type" => "success", "msg" => "Employee created successfully.", "url" => 'module/hr_employee/edit/'.$last_id[0]->employee_id]);
        } else {
            // Failure
            echo json_encode(["type" => "error", "message" => "Failed to insert data.", "url" => ""]);
        }
	}
	
	public function save_data(){
		$type = "error"; $msg = "";
		
		$data = $this->input->post();
		$data["active"] = $this->input->post("active") ? true : false;
		$data["is_leader"] = $this->input->post("is_leader") ? true : false;
		$data["working"] = $this->input->post("working") ? true : false;
		//$data["is_supervised"] = $this->input->post("is_supervised") ? true : false;
		//$data["access"] = $this->input->post("access") ? true : false;
		
		$to_updated = true;
		
		if ($data["employee_number"]){
			$f = [
				"employee_id != " => $data["employee_id"],
				"employee_number" => $data["employee_number"],
				"active" => true,
			];
			
			if ($this->gen_m->filter("hr_employee", false, $f)) $to_updated = false;
		}
		
		if ($to_updated){
			
			//department separating
			$aux = explode(" > ", $data["dpt"]);
			$data["subsidiary"] = array_key_exists(0, $aux) ? $aux[0] : "";
			$data["organization"] = array_key_exists(1, $aux) ? $aux[1] : "";
			$data["department"] = array_key_exists(2, $aux) ? $aux[2] : "";
			unset($data["dpt"]);
			
			//update employee data
			$this->gen_m->update("hr_employee", ["employee_id" => $data["employee_id"]], $data);
			
			$type = "success";
			$msg = "Employee updated.";
		}else $msg = "PR duplicated.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => "module/hr_employee/edit/".$data["employee_id"]]);
	}
	
	public function save_worktime(){
		$type = "error"; $msg = "";
		$data = $this->input->post(); 
		
		//validation
		if (!$data["work_schedule"]) $msg = "Select a schedule option.";
		elseif (!$data["date_from"]) $msg = "Select a date to apply new schedule.";
		
		if (!$msg){
			//work schedule update using $data["work_schedule"];
			$work_schedule_now = $this->gen_m->filter("hr_schedule", false, ["pr" => $data["employee_number"]], null, null, [["date_from", "desc"]], 1);
			if ($work_schedule_now){
				$work_schedule_now = $work_schedule_now[0];
				
				$work_sch = date("H:i", strtotime($work_schedule_now->work_start))." ~ ".date("H:i", strtotime($work_schedule_now->work_end));
				if ($data["work_schedule"] !== $work_sch){
					//update actual to (from - 1) day
					//insert new work schedule record
					
					//calculate date_from and aux schedule array
					if (!$data["date_from"]) $data["date_from"] = date("Y-m-d", strtotime("+1 day"));
					$aux_sch = explode(" ~ ", $data["work_schedule"]);
					
					//set basic schedule array
					$data_sch = [
						"pr" => $data["employee_number"],
						"name" => $data["name"],
						"date_from" => $data["date_from"],
					];
					
					//cleansing
					$this->gen_m->delete("hr_schedule", $data_sch);
					
					//insert
					$data_sch["work_start"] = $aux_sch[0];
					$data_sch["work_end"] = $aux_sch[1];
					
					if ($this->gen_m->insert("hr_schedule", $data_sch)){
						$type = "success";
						$msg = "worktime has been created.";
					}else $msg = "Error inserting schedule.";
				}else $msg = "Actual schedule is same to selected option.";
			}elseif ($data["date_from"]){//without cleansing work
				$aux_sch = explode(" ~ ", $data["work_schedule"]);
				
				//set basic schedule array
				$data_sch = [
					"pr" => $data["employee_number"],
					"name" => $data["name"],
					"date_from" => $data["date_from"],
				];
				
				//insert
				$data_sch["work_start"] = $aux_sch[0];
				$data_sch["work_end"] = $aux_sch[1];
				
				if ($this->gen_m->insert("hr_schedule", $data_sch)){
					$type = "success";
					$msg = "worktime has been created.";
				}else $msg = "Error inserting schedule.";
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => "module/hr_employee/edit/".$data["employee_id"]]);
	}
}
