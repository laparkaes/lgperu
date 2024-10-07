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
		
/*				
report/obs_nsp
report/pi_listening
		
module/gerp_sales_order
module/hr_attendance
module/hr_employee
module/ism_activity_management
module/obs_gerp
module/obs_magento
module/obs_most_likely
module/obs_report
module/sa_promotion
module/sa_sell_inout
module/sa_sell_out
module/scm_purchase_order
module/tax_invoice_comparison
module/tax_paperless_document
*/

		$access = [
			["gerp_sales_order", "GERP Sales order upload"],
			["hr_attendance", "HR - Attendance management"],
			["hr_employee", "HR - Employee management"],
			["ism_activity_management", "ISM - Activity management"],
			["obs_gerp", "OBS - GERP Sales order upload"],
			["obs_magento", "OBS - Magento upload"],
			["obs_most_likely", "OBS - ML upload"],
			["obs_report", "OBS - Sales report"],
			["sa_promotion", "SA - Promotion calculation"],
			["sa_sell_inout", "SA - Sell in/out report"],
			["sa_sell_out", "SA - Sell out upload"],
			["scm_purchase_order", "SCM - PO Conversion"],
			["tax_invoice_comparison", "TAX - Invoice comparison GERP & Paperless"],
			["tax_paperless_document", "TAX - Paperless eDocuments download"],
		];
		
		$acc_asg = [];
		$acc_recs = $this->gen_m->filter("sys_access", false, ["employee_id" => $employee_id]);
		foreach($acc_recs as $item) $acc_asg[] = $item->module;
		
		$data = [
			"subs" 		=> $this->gen_m->only("hr_employee", "subsidiary"),
			"orgs" 		=> $this->gen_m->only("hr_employee", "organization"),
			"dpts" 		=> $this->gen_m->only("hr_employee", "department"),
			"emp"		=> $this->gen_m->unique("hr_employee", "employee_id", $employee_id, false),
			"acc"		=> $access,
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
		
		$is_updated = true;
		
		if ($data["employee_number"]){
			$f = [
				"employee_id != " => $data["employee_id"],
				"employee_number" => $data["employee_number"],
				"active" => true,
			];
			
			if ($this->gen_m->filter("hr_employee", false, $f)) $is_updated = false;
		}
		
		if ($is_updated){
			$this->gen_m->update("hr_employee", ["employee_id" => $data["employee_id"]], $data);
			
			$type = "success";
			$msg = "Employee updated.";
		}else $msg = "PR duplicated.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
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
