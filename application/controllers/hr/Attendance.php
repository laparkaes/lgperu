<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends CI_Controller {

	public function index(){
		
		$data = [
			"main" => "hr/attendance/index",
		];
		$this->load->view('layout', $data);
	}
}
