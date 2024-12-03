<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
	}
	
	public function init(){
		$emp = [22, 23, 24];
		$funcs = $this->gen_m->all('sys_function');
		foreach($funcs as $item){
			foreach($emp as $emp_id){
				if (!$this->gen_m->filter('sys_access', false, ["employee_id" => $emp_id, "function_id" => $item->function_id])){
					$data = ["employee_id" => $emp_id, "function_id" => $item->function_id, "valid" => true];
					$this->gen_m->insert('sys_access', $data);		
				}
			}
		}
		
		print_r($funcs);
		
		return;
		$funcs = $this->gen_m->filter("sys_function", false);
		foreach($funcs as $item){
			$this->gen_m->update('sys_access', ["module" => $item->path], ["function_id" => $item->function_id]);
			echo $item->path." done!<br/>";
		}
		
	}

	public function index(){
		
		$o_emp = [
			["employee_number", "asc"],
			["name", "asc"],
			["subsidiary", "asc"], 
			["organization", "asc"], 
			["department", "asc"], 
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
		$data = $this->input->post();
		
		$access = $this->gen_m->filter("sys_access", false, $data);
		if ($access) $this->session->set_flashdata('error', "Access request already exists. Actually access is <strong>".($access[0]->valid ? "allowed" : "denied")."</strong>.");
		else{
			$data["valid"] = false;
			$this->gen_m->insert("sys_access", $data);
			$this->session->set_flashdata('success', "New access has been created. Please allow or deny this request.");
		}
		
		redirect("sys/access");
	}
	
	public function allow($access_id){
		$access = $this->gen_m->unique("sys_access", "access_id", $access_id, false);

		$func = $this->gen_m->unique("sys_function", "function_id", $access->function_id, false);
		$emp = $this->gen_m->unique("hr_employee", "employee_id", $access->employee_id, false);
		
		if ($this->gen_m->update("sys_access", ["access_id" => $access_id], ["valid" => true]))
			$this->session->set_flashdata('success', $emp->name." has been <strong>allowed</strong> access to ".($func ? $func->type."_".$func->title : "").".");
		else $this->session->set_flashdata('error', "An error has been occurred. Try again.");
		
		redirect("sys/access");
	}
	
	public function deny($access_id){
		$access = $this->gen_m->unique("sys_access", "access_id", $access_id, false);

		$func = $this->gen_m->unique("sys_function", "function_id", $access->function_id, false);
		$emp = $this->gen_m->unique("hr_employee", "employee_id", $access->employee_id, false);
		
		if ($this->gen_m->update("sys_access", ["access_id" => $access_id], ["valid" => false]))
			$this->session->set_flashdata('success', $emp->name." has been <strong>denied</strong> access to ".($func ? $func->type."_".$func->title : "").".");
		else $this->session->set_flashdata('error', "An error has been occurred. Try again.");
		
		redirect("sys/access");
	}

}
