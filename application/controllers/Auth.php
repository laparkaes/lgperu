<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		//$this->load->view('welcome_message');
		echo "hola soy Ricardo";
	}
	
	public function login(){
		
		
		//$data = ["password" => password_hash("1234567890a", PASSWORD_BCRYPT)];
		
		$this->load->view('auth/login');
	}
	
	public function login_process(){
		$type = "error"; $msg = $url = "";
		
		$employee = $this->gen_m->unique("employee", "ep_mail", $this->input->post("ep_mail"));
		if ($employee){
			if (password_verify($this->input->post("password"), $employee->password)){
				$session_data = array(
					"emp" => $employee,
					"logged_in" => true
				);
				$this->session->set_userdata($session_data);
				
				
				$type = "success";
				$msg = "Welcome!";
				$url = "dashboard";
			}else $msg = "Wrong password.";
		}else $msg = "Employee doesn't exists.";
		
		
		//$msg = password_hash("1234567890a", PASSWORD_BCRYPT);
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
