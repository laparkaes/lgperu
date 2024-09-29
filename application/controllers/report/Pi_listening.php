<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pi_listening extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
		
	public function index(){
	
		
		$data = [
			"main" => "report/pi_listening/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}
	
	public function cpilistening(){
		/* 
		1. capturar cada dato
		2. guardar en una variable
		3. guardar en db
		4. redireccionar a la pagina principal
		*/
		
		//completar todos los departamentos 
		$dpts = [
			"aaa" => "CFO Organization",
			"bbb" => "Planning",
			"ccc" => "GP",
			"ddd" => "Legal",
			"eee" => "Process Innovation & IT",
			"fff" => "AR & AP",
			"ggg" => "Tax & Custom",
			"hhh" => "Sales Admin & Accounting",
			"iii" => "SCM & Order Management",
		];
		
		
		//1. capturar y guardar cada dato
		$data = $this->input->post();
		
		if (array_key_exists($data["dptFrom"], $dpts)){
			$data["dptFrom"] = $dpts[$data["dptFrom"]];
			
			if (!$this->gen_m->filter("pi_listening", false, $data)){
				$data["registered"] = date('Y-m-d H:i:s', time());
				$this->gen_m->insert("pi_listening", $data);
			}
			
			$this->session->set_flashdata('success_msg', 'Your voice has been registered as '.$data["dptFrom"].".");
		}else{
			$this->session->set_flashdata('dptFrom', $data["dptFrom"]);
			$this->session->set_flashdata('dptTo', $data["dptTo"]);
			$this->session->set_flashdata('issue', $data["issue"]);
			$this->session->set_flashdata('solution', $data["solution"]);
			
			$this->session->set_flashdata('error_msg', 'Insert your department code correctly.');
		}
		
		redirect("./report/pi_listening");
	}

}
