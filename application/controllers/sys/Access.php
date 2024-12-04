<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
	}
	
	public function index(){
		
		$o_emp = [
			["subsidiary", "asc"], 
			["organization", "asc"], 
			["department", "asc"],
			["name", "asc"],
			["employee_number", "asc"],
		];
		
		$o_func = [
			["type", "asc"], 
			["title", "asc"], 
			["path", "asc"],
		];
		
		$access = $this->gen_m->filter("sys_access", false, null, null, null, [["valid", "asc"], ["employee_id", "asc"]]);
		foreach($access as $item){
			$func = $this->gen_m->unique("sys_function", "function_id", $item->function_id, false);
			$item->func = $func ? $func->type."_".$func->title : "";
			
			$emp = $this->gen_m->unique("hr_employee", "employee_id", $item->employee_id, false);
			$item->emp_dept = $emp->subsidiary."_".$emp->organization."_".$emp->department;
			$item->emp_pr = $emp->employee_number;
			$item->emp_ep = $emp->ep_mail;
			$item->emp_name = $emp->name;
		}
		//stdClass Object ( [access_id] => 19 [employee_id] => 24 [module] => hr_employee [function_id] => 2 [valid] => 0 [func] => module_[HR] Employee [emp_dept] => LGEPR_CFO_PI [emp_pr] => PR008985 [emp_ep] => ricardo.alvarez [emp_name] => ALVAREZ RAMIREZ, RICARDO JESUS )
		
		$data = [
			"employees"	=> $this->gen_m->filter("hr_employee", false, ["name !=" => "", "active" => true], null, null, $o_emp),
			"funcs"		=> $this->gen_m->filter("sys_function", false, null, null, null, $o_func),
			"access"	=> $access,
			"main"		=> "sys/access/index",
		];
		$this->load->view('layout', $data);
	}
	
	public function create(){
		$msgs = [];
		
		$employee_ids = $this->input->post('employee_ids');
		$function_ids = $this->input->post('function_ids');
		
		foreach($employee_ids as $emp_id){
			foreach($function_ids as $func_id){
				$data = ["employee_id" => $emp_id, "function_id" => $func_id];
				
				$func = $this->gen_m->unique("sys_function", "function_id", $func_id, false);
				$emp = $this->gen_m->unique("hr_employee", "employee_id", $emp_id, false);
				
				$access = $this->gen_m->filter("sys_access", false, $data);
				if ($access) $msgs[] = ['error', $emp->name."'s [".$func->title."] access request already exists. Actually access is <strong>".($access[0]->valid ? "allowed" : "denied")."</strong>."];
				else{
					$data["valid"] = false;
					$this->gen_m->insert("sys_access", $data);
					$this->session->set_flashdata('', );
					
					$msgs[] = ['success', $emp->name."'s [".$func->title."]new access request has been created. Please allow or deny this request."];
				}
			}	
		}
		
		$this->session->set_flashdata('msgs', $msgs);
		
		redirect("sys/access");
	}
	
	public function allow($access_id){
		$access = $this->gen_m->unique("sys_access", "access_id", $access_id, false);

		$func = $this->gen_m->unique("sys_function", "function_id", $access->function_id, false);
		$emp = $this->gen_m->unique("hr_employee", "employee_id", $access->employee_id, false);
		
		if ($this->gen_m->update("sys_access", ["access_id" => $access_id], ["valid" => true]))
			$msgs[] = ['success', $emp->name." has been <strong>allowed</strong> access to ".($func ? $func->type."_".$func->title : "")."."];
		else $msgs[] = ['error', "An error has been occurred. Try again."];
		
		$this->session->set_flashdata('msgs', $msgs);
		
		redirect("sys/access");
	}
	
	public function deny($access_id){
		$access = $this->gen_m->unique("sys_access", "access_id", $access_id, false);

		$func = $this->gen_m->unique("sys_function", "function_id", $access->function_id, false);
		$emp = $this->gen_m->unique("hr_employee", "employee_id", $access->employee_id, false);
		
		if ($this->gen_m->delete("sys_access", ["employee_id" => $access->employee_id, "access_id" => $access_id]))
			$msgs[] = ['success', $emp->name." has been <strong>denied</strong> access to ".($func ? $func->type."_".$func->title : "")."."];
		else $msgs[] = ['error', "An error has been occurred. Try again."];
		
		$this->session->set_flashdata('msgs', $msgs);
		
		redirect("sys/access");
	}

}
