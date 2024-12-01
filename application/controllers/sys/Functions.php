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
		$data = [
			"funcs" => $this->gen_m->filter("sys_function", false, null, null, null, [["type", "asc"], ["title", "asc"], ["path", "asc"]]),
			"main" => "sys/functions/index",
		];
		$this->load->view('layout', $data);
	}
	
	public function create(){
		$errors = [];
		
		$data = $this->input->post();
		
		if ($data["title"]){
			if ($this->gen_m->filter("sys_function", true, ["title" => $data["title"]])) $errors[] = "Title is duplicated.";
		}else $errors[] = "Title is required field.";
		
		if (!$data["type"]) $errors[] = "Type is required field.";
		
		if ($data["path"]){
			if ($this->gen_m->filter("sys_function", true, ["path" => $data["path"]])) $errors[] = "Path is duplicated.";
		}else $errors[] = "Path is required field.";
		
		
		if ($errors){
			$this->session->set_flashdata('errors', $errors);
			$this->session->set_flashdata('type', $data["type"]);
			$this->session->set_flashdata('path', $data["path"]);
			$this->session->set_flashdata('title', $data["title"]);
		}else{
			$data["valid"] = true;
			$data["created_at"] = $data["updated_at"] = date('Y-m-d H:i:s');
			$this->gen_m->insert("sys_function", $data);
			$this->session->set_flashdata('success', "New function menu has been created.");
		}
		
		redirect("sys/functions");
	}

	public function update(){
		$data = $this->input->post();
		$data["valid"] = $data["valid"] === "true" ? true : false;
		$data["updated_at"] = date('Y-m-d H:i:s');
		
		$func = $this->gen_m->unique("sys_function", "function_id", $data["function_id"], false);
		
		if ($this->gen_m->update("sys_function", ["function_id" => $data["function_id"]], $data)){
			$msg = $func->title." has been ";
			$res = ["type" => "success", "msg" => $msg.($data["valid"] ? "enabled." : "disabled.")];
		}else{
			$res = ["type" => "error", "msg" => "An error occurred. Try again please."];
		}
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

}
