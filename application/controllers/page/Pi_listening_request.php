<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pi_listening_request extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
		//completar todos los departamentos 
		$this->dpts = [
			"AM" => "Accounting",
			"AU" => "Air Solution",
			"AH" => "AR",
			"AY" => "AV Product",
			"AQ" => "Brand Marketing",
			"AL" => "Customs",
			"AD" => "GP",
			"AZ" => "HA Product",
			"AW" => "HA Sales",
			"AV" => "HE Sales",
			"AN" => "HR",
			"AT" => "ID Sales",
			"BD" => "ISM",
			"AR" => "IT Product",
			"AS" => "IT Sales",
			"AF" => "Legal",
			"AP" => "OBS",
			"AE" => "PI",
			"AC" => "Planning",
			"BB" => "Promotor / Retail Marketing",
			"AO" => "Sales Administration",
			"AG" => "SCM",
			"BC" => "SOM",
			"AB" => "SVC",
			"AK" => "Tax",
			"AJ" => "Treasury",
			"AX" => "TV Product",
		];
	}
		
	public function index(){
		
		$data = [
			"dpts" => $this->dpts,
			"overflow" => "hidden",
			"main" => "page/pi_listening_request/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}
	// public function load_add_problem(){
		// $this->load->view('page/pi_listening_request/index'); // Carga la vista sin layout
	// }
	public function cpilistening(){
		/* 
		1. capturar cada dato
		2. guardar en una variable
		3. guardar en db
		4. redireccionar a la pagina principal
		*/
		
		//completar todos los departamentos 
		$dpts = $this->dpts;
		
		
		//1. capturar y guardar cada dato
		$data = $this->input->post();

		
		/* without dpt from validation */
		$data["dptTo"] = $dpts[$data["dptTo"]];
		$data["status"] = "Registered";
		$data["registered"] = date('Y-m-d H:i:s', time());
		$this->gen_m->insert("pi_listening", $data);
		
		$this->session->set_flashdata('success_msg', 'Your voice has been registered.');
		
		redirect("./page/Pi_listening_request");
	}

	public function test(){
		$dpts = ['Marketing', 'Support', 'Development', 'HR', 'IT', 'Sales', 'Operations', 'Admin', 'Finance'];
		$status = ['Registered', 'Accepted', 'Rejected', 'On progress', 'Closed'];
		
		$datas = [];
		for ($i = 1; $i <= 1000; $i++) {
			$now = rand(10000, 99999) * $i;
			
			$datas[] = [
				"dptFrom" => $dpts[array_rand($dpts)],
				"dptTo" => $dpts[array_rand($dpts)],
				"issue" => "Issue description ".$now,
				"solution" => "Solution description ".$now,
				"status" => $status[array_rand($status)],
				"registered" => date('Y-m-d H:i:s', time()),
			];
        }
		
		$this->gen_m->insert_m("pi_listening", $datas);
	}

}
