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
		
		$access = $this->gen_m->filter("sys_access", false);
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
		$errors = [];
		
		$data = $this->input->post();
		
		if ($data["title"]){
			if ($this->gen_m->filter("sys_function", true, ["title" => $data["title"]])) $errors[] = "Title is duplicated.";
		}else $errors[] = "Title is required field.";
		
		if (!$data["type"]) $errors[] = "Type is required field.";
		
		if ($data["path"]){
			if ($this->gen_m->filter("sys_function", true, ["path" => $data["path"]])) $errors[] = "Path is duplicated.";
		}else $errors[] = "Path is required field.";
		
		
		if ($errors){
			$this->session->set_flashdata('errors', $errors);
			$this->session->set_flashdata('type', $data["type"]);
			$this->session->set_flashdata('path', $data["path"]);
			$this->session->set_flashdata('title', $data["title"]);
		}else{
			$data["valid"] = true;
			$data["created_at"] = $data["updated_at"] = date('Y-m-d H:i:s');
			$this->gen_m->insert("sys_function", $data);
			$this->session->set_flashdata('success', "New function menu has been created.");
		}
		
		redirect("sys/access");
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	///////////////////////////////////////////////////

	public function update(){
		$data = $this->input->post();
		$data["valid"] = $data["valid"] === "true" ? true : false;
		$data["updated_at"] = date('Y-m-d H:i:s');
		
		$func = $this->gen_m->unique("sys_function", "function_id", $data["function_id"], false);
		
		if ($this->gen_m->update("sys_function", ["function_id" => $data["function_id"]], $data)){
			$msg = $func->title." has been ";
			$res = ["type" => "success", "msg" => $msg.($data["valid"] ? "enabled." : "disabled.")];
		}else{
			$res = ["type" => "error", "msg" => "An error occurred. Try again please."];
		}
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

}
