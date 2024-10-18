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

	public function index(){
		$employees = $this->gen_m->filter("hr_employee", false, ["active" => true], null, null, [["subsidiary", "asc"], ["organization", "asc"], ["department", "asc"], ["name", "asc"]]);
		
		$data = [
			"employees" => $employees,
			"main" => "module/hr_employee/index",
		];
		$this->load->view('layout', $data);
	}

	public function edit($employee_id){
		if ($this->session->userdata('department') !== "Process Innovation & IT") redirect("/module/hr_employee");
		
		$emp = $this->gen_m->unique("hr_employee", "employee_id", $employee_id, false);
		
		$today = date("Y-m-d");
		$work_schedule_now = null;
		$work_schedule = $this->gen_m->filter("hr_schedule", false, ["pr" => $emp->employee_number], null, null, [["date_from", "desc"]]);
		foreach($work_schedule as $i => $item){
			if (strtotime($item->date_from) <= strtotime($today)) $work_schedule_now = $item;
			//else unset($work_schedule[$i]);
		}
		
		$emp->work_sch = $work_schedule ? date("H:i", strtotime($work_schedule_now->work_start))." ~ ".date("H:i", strtotime($work_schedule_now->work_end)) : "";
		$emp->dpt = $emp->subsidiary." > ".$emp->organization." > ".$emp->department;
		
		$schs = [
			"07:00 ~ 17:00",
			"07:30 ~ 17:30",
			"08:00 ~ 18:00",
			"08:30 ~ 18:30",
			"09:00 ~ 19:00",
			"09:30 ~ 19:30",
		];
		
		$access_module = [
			["hr_attendance", "HR - Attendance"],
			["hr_employee", "HR - Employee"],
			["ism_activity_management", "ISM - Activity"],
			["obs_report", "OBS - Sales report"],
			["pi_listening", "PI - Listening to You"],
			["sa_promotion", "SA - Promotion calculation"],
			["sa_sell_inout", "SA - Sell in/out report"],
			["scm_purchase_order", "SCM - PO Conversion"],
			["tax_invoice_comparison", "TAX - Invoice comparison GERP & Paperless"],
		];
		
		$access_data_upload = [
			["gerp_sales_order", "GERP Sales order upload"],
			["ar_exchange_rate", "HR - Exchange rate"],
			["hr_access_record", "HR - Access record"],
			["obs_gerp", "OBS - GERP Sales order upload"],
			["obs_magento", "OBS - Magento upload"],
			["obs_most_likely", "OBS - ML upload"],
			["sa_sell_out", "SA - Sell out upload"],
			["tax_paperless_document", "TAX - Paperless eDocuments download"],
		];
		
		$acc_asg = [];
		$acc_recs = $this->gen_m->filter("sys_access", false, ["employee_id" => $employee_id]);
		foreach($acc_recs as $item) $acc_asg[] = $item->module;
		
		
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
			"acc_mod"	=> $access_module,
			"acc_du"	=> $access_data_upload,
			"acc_asg"	=> $acc_asg,
			"main" 		=> "module/hr_employee/edit",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function save_data(){
		$type = "error"; $msg = "";
		
		$data = $this->input->post();
		$data["active"] = $this->input->post("active") ? true : false;
		$data["is_supervised"] = $this->input->post("is_supervised") ? true : false;
		$data["access"] = $this->input->post("access") ? true : false;
		
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
			//work schedule update using $data["work_schedule"];
			$work_schedule_now = null;
			$work_schedule = $this->gen_m->filter("hr_schedule", false, ["pr" => $data["employee_number"]], null, null, [["date_from", "desc"]]);
			foreach($work_schedule as $item){
				if (strtotime($item->date_from) <= strtotime($data["date_from"])){
					$work_schedule_now = $item;
				}
			}
			
			if ($work_schedule_now){
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
					
					$this->gen_m->insert("hr_schedule", $data_sch);
				}
			}
			
			unset($data["work_schedule"]);
			unset($data["date_from"]);
			
			//department separating
			$aux = explode(" > ", $data["dpt"]);
			$data["subsidiary"] = $aux[0];
			$data["organization"] = $aux[1];
			$data["department"] = $aux[2];
			unset($data["dpt"]);
			
			//update employee data
			$this->gen_m->update("hr_employee", ["employee_id" => $data["employee_id"]], $data);
			
			$type = "success";
			$msg = "Employee updated.";
		}else $msg = "PR duplicated.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => "module/hr_employee/edit/".$data["employee_id"]]);
	}
	
	public function acc_ctrl(){
		$data = $this->input->post();
		
		if ($data["checked"] === "true"){
			unset($data["checked"]);
			$this->gen_m->insert("sys_access", $data);
			$msg = "Access assigned.";
			
			$access = [];
			$acc_recs = $this->gen_m->filter("sys_access", false, ["employee_id" => $data["employee_id"]]);
			foreach($acc_recs as $item) $access[] = $item->module;
			
			$this->session->set_userdata('access', $access);
		}else{
			unset($data["checked"]);
			$this->gen_m->delete("sys_access", $data);
			$msg = "Access removed.";
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => "success", "msg" => $msg]);
	}
	
}
