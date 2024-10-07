<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pi_listening extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
		//completar todos los departamentos 
		$this->dpts = [
			"AM" => "Accounting",
			"AU" => "Air Solution",
			"AH" => "AR",
			
			
			"AY" => "AV Product",
			"AQ" => "Brand Marketing",
			"AL" => "Customs",
			"AD" => "GP",
			"AZ" => "HA Product",
			"AW" => "HA Sales",
			"AV" => "HE Sales",
			"AN" => "HR",
			"AT" => "ID Sales",
			"BD" => "ISM",
			"AR" => "IT Product",
			"AS" => "IT Sales",
			"AF" => "Legal",
			"AP" => "OBS",
			"AE" => "PI",
			"AC" => "Planning",
			"BB" => "Promotor / Retail Marketing",
			"AO" => "Sales Administration",
			"AG" => "SCM",
			"BC" => "SOM",
			"AB" => "SVC",
			"AK" => "Tax",
			"AJ" => "Treasury",
			"AX" => "TV Product",
		];
	}
		
	public function index(){
		
		$w = [];
		if ($this->input->get("dptFrom")) $w["dptFrom"] = $this->input->get("dptFrom");
		if ($this->input->get("dptTo")) $w["dptTo"] = $this->input->get("dptTo");
		
		$records = $this->gen_m->filter("pi_listening", false, $w);
		
		$data = [
			"dptsFrom" => $this->gen_m->only("pi_listening", "dptFrom"),
			"dptsTo" => $this->gen_m->only("pi_listening", "dptTo"),
			"records" => $records,
			"main" => "module/pi_listening/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function update(){
		$type = "success"; $msg = "Voice updated.";
		
		$data = $this->input->post();
		$this->gen_m->update("pi_listening", ["listening_id" => $data["listening_id"]], $data);
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
