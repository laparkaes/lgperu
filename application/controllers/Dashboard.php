<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index(){
		$data = [
			"main" => "dashboard/index",
		];
		
		$this->load->view('layout', $data);
	}


	public function index1(){
		$module = $this->input->get("m");
		if (!$module){
			//load account module
			$module = "hr";
		}
		
		$data = [
			"main" => "dashboard/".$module,
		];
		
		$this->load->view('layout', $data);
	}
}
