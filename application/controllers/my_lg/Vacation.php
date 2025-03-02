<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vacation extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$type_arr = [];
		$type_rec = $this->gen_m->filter("lgepr_lookup", false, ["code" => "vacation_type"], null, null, [["lookup", "asc"]]);
		foreach($type_rec as $item) $type_arr[$item->lookup_id] = $item->lookup;
		
		$records = $this->gen_m->filter("hr_vacation_request", false, ["emp_ep" => $this->session->userdata('ep_mail')], null, null, [["registed_at", "desc"]]);
		
		$data = [
			"records" => $records,
			"last_rec" => $records ? $records[0] : $this->gen_m->structure("hr_vacation_request"),
			"type_rec" => $type_rec,
			"type_arr" => $type_arr,
			"approvers" => $this->gen_m->filter("hr_employee", false, ["is_leader" => true], null, null, [["ep_mail", "asc"]]),
			"main" => "my_lg/vacation/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function request(){
		$type = "error"; $msg = "";
		
		$data = $this->input->post();
		$data["emp_ep"] = $this->session->userdata('ep_mail');
		
		//validation
		switch(true){
			case $data["type"] === "" : $msg = "Select a vacation type."; break;
			case $data["d_from"] === "" : $msg = "Select first date."; break;
			case $data["d_to"] === "" : $msg = "Select last date."; break;
			case $data["approver_1"] === "" : $msg = "Select an approver at least (1st Approver)."; break;
		}
		
		if (strtotime($data["d_to"]) < strtotime($data["d_from"])) $msg = "Last date must be same date or later than first date.";
		
		$w_from = [
			"emp_ep" => $data["emp_ep"],
			"d_from <=" => $data["d_from"],
			"d_to >=" => $data["d_from"],
		];
		
		$w_to = [
			"emp_ep" => $data["emp_ep"],
			"d_from <=" => $data["d_to"],
			"d_to >=" => $data["d_to"],
		];
		
		$w_in = [["field" => "status", "values" => ["Requested", "Approved"]]];
		
		if (
			$this->gen_m->filter("hr_vacation_request", false, $w_from, null, $w_in) or 
			$this->gen_m->filter("hr_vacation_request", false, $w_to, null, $w_in)
		) $msg = "Vacation record exist between dates.";
		
		if (!$msg){
			//set initial datas
			$data["status"] = "Requested";
			$data["qty_day"] = $this->my_func->day_counter($data["d_from"], $data["d_to"]);
			$data["approver_now"] = $data["approver_1"];
			$data["registed_at"] = date('Y-m-d H:i:s');
			
			//set 0.5 day in case of half day off
			if ($data["type"] !== "Vacation"){
				$data["qty_day"] = 0.5;
				$data["d_to"] = $data["d_from"];
			}
			
			//set approver key
			$this->load->helper('string');
			$data["approver_key"] = random_string('alpha', 5);
			
			//print_r($data);
			
			$req_id = $this->gen_m->insert("hr_vacation_request", $data);
			if ($req_id){
				$type = "success";
				$msg = "Vacation request has been registered.";
				
				$this->send_approval_notify($req_id);
			}else $msg = "An error occurred. Please try again.";
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function send_approval_notify($req_id){
		$req = $this->gen_m->unique("hr_vacation_request", "request_id", $req_id, false);
		//print_R($req);
		
		$from = $req->emp_ep."@lge.com";
		//$to = $req->approver_now."@lge.com";
		$to = "georgio.park@lge.com";
		//$content = $this->load->view('email/vacation_approval_request', null, true);
		$content = "Apruebameeeeee";
		
		return $this->my_func->send_email($from, $to, "[Llamasys] New vacation requeste is waiting your approval.", $content);
	}
	
	public function resend(){
		$type = "error";
		$msg = $this->send_approval_notify($this->input->post("request_id"));
		
		if (!$msg){
			$type = "success";
			$msg = "Approval request has been sent to last approver.";
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function cancel(){
		$type = "error"; $msg = "";
		
		//$req = $this->gen_m->unique("hr_vacation_request", "request_id", $this->input->post("request_id"), false);
		//print_r($req);
		
		$req_id = $this->input->post("request_id");
		if ($this->gen_m->update("hr_vacation_request", ["request_id" => $req_id], ["status" => "Cancelled"])){
			
			//important!!! cancel all hr_vacation records related to this request_id
			
			$type = "success";
			$msg = "Vacation plan has been canceled.";
		}else $msg = "An error occurred. Please try again.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
