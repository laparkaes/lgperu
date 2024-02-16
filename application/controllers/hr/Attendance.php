<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		//$this->load->model('general_model','general');
		$this->nav_menu = ["hr", "attendance"];
	}

	public function index(){
		
		$data = [
			"main" => "hr/attendance/index",
		];
		$this->load->view('layout', $data);
	}
}
