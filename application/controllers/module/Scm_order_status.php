<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scm_order_status extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		//$this->load->model('general_espr_model', 'gen_e');
	}
	
	public function index(){
		
		$f = [
			"f_company"		=> $this->input->get("f_company"),
			"f_division"	=> $this->input->get("f_division"),
			"f_om_status"	=> $this->input->get("f_om_status"),
			"f_so_status"	=> $this->input->get("f_so_status"),
			"f_customer"	=> $this->input->get("f_customer"),
		];
		
		//return;
		
		$w = ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"];
		$l = [];
		$o = [["bill_to_name", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		if ($f){
			if ($f["f_company"]) $w["dash_company"] = $f["f_company"];
			if ($f["f_division"]) $w["dash_division"] = $f["f_division"];
			if ($f["f_om_status"]) $w["om_line_status"] = $f["f_om_status"];
			if ($f["f_so_status"]) $w["so_status"] = $f["f_so_status"];
			
			if ($f["f_customer"]) $l[] = ["field" => "bill_to_name", "values" => [$f["f_customer"]]];
		}
		
		$sales = $this->gen_m->filter("lgepr_sales_order", false, $w, $l, null, $o);
		
		$om_status_list = [
			"CLOSED", 
			"PICK", 
			"CON CITA", 
			"POR CONFIRMAR CITA", 
			"POR SOLICITAR CITA", 
			"REFACTURACION", 
			"REGULARIZACION", 
			"POR CANCELAR PEDIDO", 
			"SIN DISTRIBUCION", 
			"SIN LINEA DE CREDITO", 
			"SIN STOCK", 
		];
		
		$so_status_list = $this->gen_m->only("lgepr_sales_order", "so_status", ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"]);
		$company_list = $this->gen_m->only("lgepr_sales_order", "dash_company", ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"]);
		$division_list = $this->gen_m->only("lgepr_sales_order", "dash_division", ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"]);
		
		$data = [
			"filter"		 => $f,
			"company_list"	 => $company_list,
			"division_list"	 => $division_list,
			"om_status_list" => $om_status_list,
			"so_status_list" => $so_status_list,
			"sales"			 => $sales,
			"main" 			 => "module/scm_order_status/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function load_sales_order(){
		$order_id = $this->input->post("order_id");
		$order = $this->gen_m->unique("lgepr_sales_order", "sales_order_id", $order_id, false);
		
		if ($order->om_appointment){
			$order->om_appointment_date = date("Y-m-d", strtotime($order->om_appointment));
			$order->om_appointment_time_hh = date("h", strtotime($order->om_appointment));
			$order->om_appointment_time_mm = date("m", strtotime($order->om_appointment));	
		}else{
			$order->om_appointment_date = null;
			$order->om_appointment_time_hh = null;
			$order->om_appointment_time_mm = null;
		}
		
		//echo $order_id; echo "<br/><br/>"; print_r($order);
		
		header('Content-Type: application/json');
		echo json_encode($order);
	}
	
	public function om_update(){
		$data = $this->input->post();
		
		$appointment_aux = "";
		if ($data["om_appointment_date"]){
			$appointment_aux = $appointment_aux.$data["om_appointment_date"];
			if ($data["om_appointment_time_hh"]){
				$appointment_aux = $appointment_aux." ".$data["om_appointment_time_hh"];
				if ($data["om_appointment_time_mm"]){
					$appointment_aux = $appointment_aux.":".$data["om_appointment_time_mm"].":00";
				}else $appointment_aux = $appointment_aux." :00:00";
			}else $appointment_aux = $appointment_aux." 00:00:00";
		}
		
		$om = [
			"om_line_status" 	=> $data["om_line_status"],
			"om_appointment" 	=> $appointment_aux === "" ? null : $appointment_aux,
			"om_appointment_remark" => $data["om_appointment_remark"],
			"om_updated_at" 	=> date("Y-m-d H:i:s"),
		];
		
		if ($this->gen_m->update("lgepr_sales_order", ["sales_order_id" => $data["order_id"]], $om)){
			$type = "success";
			$msg = "Appointment information has been updated.";
		}else{
			$type = "danger";
			$msg = "An error occured. Try again.";
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function make_report(){
		$f = $this->input->post();
		
		$w = ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"];
		$l = [];
		$o = [["bill_to_name", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		if ($f){
			if ($f["f_company"]) $w["dash_company"] = $f["f_company"];
			if ($f["f_division"]) $w["dash_division"] = $f["f_division"];
			if ($f["f_om_status"]) $w["om_line_status"] = $f["f_om_status"];
			if ($f["f_so_status"]) $w["so_status"] = $f["f_so_status"];
			
			if ($f["f_customer"]) $l[] = ["field" => "bill_to_name", "values" => [$f["f_customer"]]];
		}
		
		$sales = $this->gen_m->filter("lgepr_sales_order", false, $w, $l, null, $o);
		
		print_r($sales);
	}
	
	public function espr(){
		
		/*
		T_OPEN_STATUS
		T_OPEN_ORDER
		*/
		
		ini_set('sqlsrv.ClientBufferMaxKBSize', 100000); // 20MB
		
		
		$order_status = $this->gen_e->only("T_OPEN_STATUS", "ESTATUS");
		$open_order_status = $this->gen_e->all("T_OPEN_STATUS", [["ESTATUS", "asc"]]);
		$open_order = $this->gen_e->all("T_OPEN_ORDER");
		
		foreach($order_status as $item){echo $item["ESTATUS"]."<br/>";} echo "<br/><br/><br/>";
		
		echo count($open_order_status)."<br/><br/><br/>";
		
		echo count($open_order)."<br/><br/><br/>";
		
		foreach($open_order_status as $item){
			print_r($item); echo "<br/>";
			
			if ($item["ESTATUS"]) $this->gen_m->update("lgepr_sales_order", ["order_no" => $item["SO NO"], "line_no" => $item["SO Line NO"]], ["om_line_status" => $item["ESTATUS"]]);
		}
		
		echo "<br/>//////////////////////////////////////////////<br/><br/>";
		
		foreach($open_order as $k => $item){
			//print_r($item); echo "<br/>";
			echo $item["SO Line Status Code3"]." ||| ".$item["SO NO"]." ||| ".$item["SO Line NO"]." ||| ".$item["Sales Order Amount (USD,Tax Exclude)"]."<br/>";
			
			//echo "<br/>";
		}
		
		
		
		return;
		
	}
	
}
