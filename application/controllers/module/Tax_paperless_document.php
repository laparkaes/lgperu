<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tax_paperless_document extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"main" => "module/tax_paperless_document/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload(){
		/*
		php.ini
		max_input_vars = 990000
		restart server
		*/
		
		/*
		[doc_type] => FACTURA ELECTRONICA 
		[doc_number] => F100-00002126 
		[customer_id] => 10448519584 
		[customer_name] => Alejandro Francisco Ponce... 
		[date_enter] => 17-08-2024 20:30 
		[date_issue] => 17-08-2024 
		[amount] => 1849.00
		[currency] => PEN 
		[status] => 1 - Aceptado 
		[paperless_id] => TXYfNpnacT5a4oa(MaS)JKkt9A(IgU)(IgU) 
		
		https://ereceipt-pe-s02.sovos.com/Facturacion/PDFServlet?o=E&d=true&id=TXYfNpnacT5a4oa(MaS)JKkt9A(IgU)(IgU)
		https://ereceipt-pe-s02.sovos.com/Facturacion/XMLServlet?o=E&cl=true&d=true&id=TXYfNpnacT5a4oa(MaS)JKkt9A(IgU)(IgU)
		*/
		
		$invoices = [];
		$rows = $this->input->post("rows");
		if (!$rows) $rows = [];
		
		foreach($rows as $i => $item){
			$rows[$i]["date_enter"] = $this->my_func->date_convert_7($item["date_enter"]);
			$rows[$i]["date_issue"] = $this->my_func->date_convert_8($item["date_issue"]);
			
			$invoices[] = $item["doc_number"];
			
			if (count($invoices) > 1000){
				$this->gen_m->delete_in("tax_invoice", "doc_number", $invoices);
				$invoices = [];
			}
		}
		
		if ($invoices) $this->gen_m->delete_in("tax_invoice", "doc_number", $invoices);
		if ($rows) $this->gen_m->insert_m("tax_invoice", $rows);
		
		header('Content-Type: application/json');
		echo json_encode(["type" => "success", "msg" => number_format(count($rows))." invoices inserted."]);
	}
}
