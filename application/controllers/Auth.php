<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function login(){
		if ($this->session->userdata('logged_in')) redirect("/dashboard");
		
		//$data = ["password" => password_hash("1234567890a", PASSWORD_BCRYPT)];
		
		$this->load->view('auth/login');
	}
	
	public function login_process(){
		$type = "error"; $msg = $url = "";
		
		$employee = $this->gen_m->unique("hr_employee", "ep_mail", $this->input->post("ep_mail"), false);
		if ($employee){
			if ($employee->password){
				if (password_verify($this->input->post("password"), $employee->password)){
					unset($employee->password);
					unset($employee->is_supervised);
					unset($employee->access);
					
					$func_ids = [-1];
					$access = $this->gen_m->filter("sys_access", true, ["employee_id" => $employee->employee_id]);
					foreach($access as $item) $func_ids[] = $item->function_id;
					
					$nav = ['module' => [], 'data_upload' => [], 'page' => [], 'sys' => []];
					$funcs = $this->gen_m->filter("sys_function", true, null, null, [["field" => "function_id", "values" => $func_ids]], [["title", "asc"]]);
					foreach($funcs as $item) $nav[$item->type][] = $item;
					
					$session_data = array(
						"employee_id" => $employee->employee_id,
						"employee_number" => $employee->employee_number,
						"name" => $employee->name,
						"department" => $employee->department,
						"nav" => $nav,
						"logged_in" => true
					);
					$this->session->set_userdata($session_data);
					
					$type = "success";
					$msg = "Welcome!";
					$url = base_url()."dashboard";
				}else $msg = "Wrong password.";
			}else $msg = "Employee doesn't have access. Contact with PI please.";
		}else $msg = "Employee doesn't exists.";
		
		//$msg = password_hash("1234567890a", PASSWORD_BCRYPT);
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	public function logout(){
		$this->session->sess_destroy();
		redirect("/", 'refresh');
	}
	
	public function change_password(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$this->load->view('auth/change_password');
	}
	
	public function change_password_process(){
		$type = "error"; $msg = $url = "";
		
		if ($this->input->post("password_n") === $this->input->post("password_c")){
			$employee = $this->gen_m->unique("hr_employee", "employee_id", $this->session->userdata('emp')->employee_id);
			if (password_verify($this->input->post("password"), $employee->password)){
				if ($this->gen_m->update("hr_employee", ["employee_id" => $employee->employee_id], ["password" => password_hash($this->input->post("password_n"), PASSWORD_BCRYPT)])){
					$this->session->sess_destroy();
					
					$type = "success";
					$msg = "Password changed. Please login again.";
					$url = "auth/login";
				}else $msg = "Internal error. Try again.";
			}else $msg = "Password error.";
		}else $msg = "Password confirm error.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
