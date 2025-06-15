<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
	}
	
	public function index(){
		$data["overflow"] = "scroll";
		$data["main"] = "lgepr/reports";
		
		$this->load->view('layout_dashboard', $data);
	}
}
