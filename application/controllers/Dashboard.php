<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index(){
		$module = $this->input->get("m");
		if (!$module){
			//load account module
			$module = "hr";
		}
		
		$data = [
			"title" => "Human Resource",
			"access" => ["attendance", "employee"],
			"main" => "dashboard/".$module,
		];
		
		$this->load->view('layout', $data);
	}
}
