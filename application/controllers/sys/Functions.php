<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Functions extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model','gen_m');
	}

	public function index(){
		//$funcs = $this->gen_m->filter("sys_function", true, null, null, null, [["title", "asc"], ["path", "asc"]]);
		
		$data = [
			"funcs" => $this->gen_m->filter("sys_function", true, null, null, null, [["type", "asc"], ["title", "asc"], ["path", "asc"]]),
			"main" => "sys/functions/index",
		];
		$this->load->view('layout', $data);
	}
	
	public function create(){
		$errors = [];
		
		$data = $this->input->post();
		
		if ($data["path"]){
			if ($this->gen_m->filter("sys_function", true, ["path" => $data["path"]])) $errors[] = "Path is duplicated.";
		}else $errors[] = "Path is required field.";
		
		if ($data["title"]){
			if ($this->gen_m->filter("sys_function", true, ["title" => $data["title"]])) $errors[] = "Title is duplicated.";
		}else $errors[] = "Title is required field.";
		
		if ($errors){
			$this->session->set_flashdata('errors', $errors);
			$this->session->set_flashdata('type', $data["type"]);
			$this->session->set_flashdata('path', $data["path"]);
			$this->session->set_flashdata('title', $data["title"]);
		}else{
			$data["valid"] = true;
			$this->gen_m->insert("sys_function", $data);
			$this->session->set_flashdata('success', "New function menu has been created.");
		}
		
		redirect("sys/functions");
	}

}
