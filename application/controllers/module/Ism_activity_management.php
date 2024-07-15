<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Ism_activity_management extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$lines = $this->gen_m->all("product_line", [["line", "asc"]]);
		
		$data = [
			"lines" => $lines,
			"main" => "module/ism_activity_management/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function create(){
		$data = [
			"main" => "module/ism_activity_management/create",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function add_activity(){
		$type = "error"; $msg = ""; $url = "module/ism_activity_management";
		
		$activity = $this->input->post();
		if ($activity["title"]){
			foreach($activity as $key => $item) if (!$item) $activity[$key] = null;
			
			$activity["registered"] = date('Y-m-d H:i:s', time());
			$activity_id = $this->gen_m->insert("ism_activity", $activity);
			
			$type = "success";
			$msg = "Activity has been registered";
			$url = "module/ism_activity_management/edit/".$activity_id;
		}else $msg = "Activity title is required field.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	public function edit($activity_id){
		$data = [
			"activity" => $this->gen_m->unique("ism_activity", "activity_id", $activity_id),
			"main" => "module/ism_activity_management/edit",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function update_activity(){
		$type = "error"; $msg = ""; $url = "module/ism_activity_management";
		
		$activity = $this->input->post();
		if ($activity["title"]){
			foreach($activity as $key => $item) if (!$item) $activity[$key] = null;
			
			$this->gen_m->update("ism_activity", ["activity_id" => $activity["activity_id"]], $activity);
			
			$type = "success";
			$msg = "Activity has been updated";
			$url = "module/ism_activity_management/edit/".$activity["activity_id"];
		}else $msg = "Activity title is required field.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	
}
