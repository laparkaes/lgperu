<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scm_order_status extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->load->model('general_espr_model', 'gen_e');
	}
	
	public function index(){
		$line_status_detail = [
			"CLOSED", 
			"CON CITA", 
			"PICK", 
			"POR CANCELAR PEDIDO", 
			"POR CONFIRMAR CITA", 
			"POR SOLICITAR CITA", 
			"REFACTURACION", 
			"REGULARIZACION", 
			"SIN DISTRIBUCION", 
			"SIN LINEA DE CREDITO", 
			"SIN STOCK", 
		];
		
		$w = ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"];
		$o = [["order_no", "asc"], ["line_no", "asc"]];
		$sales = $this->gen_m->filter("lgepr_sales_order", false, $w, null, null, $o);
		
		$data = [
			"sales"	=> $sales,
			"main" 	=> "module/scm_order_status/index",
		];
		
		$this->load->view('layout', $data);
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
			
			if ($item["ESTATUS"]) $this->gen_m->update("lgepr_sales_order", ["order_no" => $item["SO NO"], "line_no" => $item["SO Line NO"]], ["line_status_detail" => $item["ESTATUS"]]);
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
