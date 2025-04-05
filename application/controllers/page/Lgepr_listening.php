<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr_listening extends CI_Controller {

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
		$sql = "
			SELECT pl.*, 
				   COALESCE(MAX(plc.comment_id), 0) AS latest_comment_id, 
				   COALESCE(MAX(plc.updated), pl.registered) AS latest_date
			FROM pi_listening pl
			LEFT JOIN pi_listening_comment plc 
				   ON pl.listening_id = plc.listening_id
			GROUP BY pl.listening_id
			ORDER BY latest_date DESC, pl.registered DESC
		";
		
		$records = $this->db->query($sql)->result();
		
		// Obtener los comentarios en orden descendente por updated
		$orders = [["updated", "DESC"]];
		$records_comment = $this->gen_m->filter("pi_listening_comment", false, null, null, null, $orders);
		$user_name = $this->session->userdata('name');
		$user = $this->gen_m->filter("hr_employee", false, ['name'=>$user_name]);
		//print_r($user[0]->ep_mail);
		
		$data = [
			"dpts" => $this->dpts,
			"overflow" => "hidden",
			"records" => $records,
			"records_comment" => $records_comment,
			"user" => $user[0]->ep_mail,
			"main" => "page/lgepr_listening/index",
		];

		$this->load->view('layout_dashboard', $data);
	}

	public function cpilistening(){
		
		$data = $this->input->post();
		
		
		/* without dpt from validation */
		$data["dptTo"] = $dpts[$data["dptTo"]];
		$data["status"] = "Registered";
		$data["registered"] = date('Y-m-d H:i:s', time());
		$this->gen_m->insert("pi_listening", $data);
		
		$this->session->set_flashdata('success_msg', 'Your voice has been registered.');
		
		redirect("./page/lgepr_listening");
	}

	public function update(){
		$type = "success"; $msg = "Voice updated.";
		
		$data = $this->input->post();
		$this->gen_m->update("pi_listening", ["listening_id" => $data["listening_id"]], $data);
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function load_add_problem(){
		$this->load->view('page/pi_listening_request/index'); // Carga la vista sin layout
	}

	public function load_pi_listening(){
		//$this->load->view('page/pi_listening_request/index');
		redirect("./page/pi_listening_request");
	}
}
