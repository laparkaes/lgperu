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
		
		//1. capturar y guardar cada dato
			$data=$this->input->post();
			print_r($data); 	echo"<br>";
		//2. validar datos
			echo $data["inputFrom"];
			if ($data["inputFrom"]) {
			  echo "Have a good day 1!"; echo"<br>";
			} 
			echo $data["selectTo"];
			if ($data["selectTo"]) {
			  echo "Have a good day 2!";echo"<br>";
			} 
			echo $data["inputIssue"];
			if ($data["inputIssue"]) {
			  echo "Have a good day 3!";echo"<br>";
			} 
			echo $data["inputSolution"];
			if ($data["inputSolution"]) {
			  echo "Have a good day 4!";echo"<br>";
			} 
		
		
		
				
	}

}
