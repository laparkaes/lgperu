<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vacation extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"main" => "my_lg/vacation/index",
		];
		
		$this->load->view('layout', $data);
	}
	
}
