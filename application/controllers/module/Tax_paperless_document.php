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
			"invoices" => $this->gen_m->filter("tax_invoice", false, null, null, null, [["date_enter" , "desc"]], "1000"),
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
		*/
		
		$invoices = [];
		$rows = $this->input->post("rows");
		if (!$rows) $rows = [];
		//print_r($rows); echo "<br/><br/>";
		foreach($rows as $i => $item){
			//echo $i.". "; print_r($item); echo "<br/><br/>";
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
	
	public function test(){
		$filepath = "./upload/hr_attendance.csv";
		
		if (file_exists($filepath)) echo "ok";
	}
	
	
	public function download(){
		/*
		https://ereceipt-pe-s02.sovos.com/Facturacion/PDFServlet?o=E&d=true&id=TXYfNpnacT5a4oa(MaS)JKkt9A(IgU)(IgU)
		https://ereceipt-pe-s02.sovos.com/Facturacion/XMLServlet?o=E&cl=true&d=true&id=TXYfNpnacT5a4oa(MaS)JKkt9A(IgU)(IgU)
		*/
		
		set_time_limit(0);
		$start_time = microtime(true);
		
		$base_pdf = "https://ereceipt-pe-s02.sovos.com/Facturacion/PDFServlet?o=E&d=true&id=";
		$base_xml = "https://ereceipt-pe-s02.sovos.com/Facturacion/XMLServlet?o=E&cl=true&d=true&id=";
		
		$dir = "./eDocuments";
		if (!file_exists($dir)) mkdir($dir, 0777, true);
		
		$count = 0;
		
		$invoices = $this->gen_m->filter("tax_invoice", false, ["downloaded" => false], null, null, [["date_enter", "asc"]], 1000, 0);
		while(count($invoices) > 0){
			foreach($invoices as $item){
				$is_error = false;
				
				$date_aux = explode("-", $item->date_issue);
				
				$dir = "./eDocuments/".$date_aux[0];
				if (!file_exists($dir)) mkdir($dir, 0777, true);
				
				$dir = "./eDocuments/".$date_aux[0]."/".$date_aux[1];
				if (!file_exists($dir)) mkdir($dir, 0777, true);
				
				$dir = "./eDocuments/".$date_aux[0]."/".$date_aux[1]."/".str_replace(" ", "_", $item->doc_type);
				if (!file_exists($dir)) mkdir($dir, 0777, true);
				
				//pdf document
				$pdf_file = $dir."/".$item->doc_number.".pdf";
				
				if (file_exists($pdf_file)) echo $pdf_file." already exists.<br/>";
				else{
					$fileContent = @file_get_contents($base_pdf.$item->paperless_id);
					if ($fileContent !== false) file_put_contents($pdf_file, $fileContent);
					else{
						echo "<br/>--- Error PDF --- ".$item->doc_number;
						$is_error = true;
					}				
				}
				
				//xml document
				$xml_file = $dir."/".$item->doc_number.".xml";
				
				if (file_exists($xml_file)) echo $xml_file." already exists.<br/>";
				else{
					$fileContent = @file_get_contents($base_xml.$item->paperless_id);
					if ($fileContent !== false) file_put_contents($xml_file, $fileContent);
					else{
						echo "<br/>--- Error XML --- ".$item->doc_number;
						$is_error = true;
					}	
				}
				
				if (!$is_error){
					$count++;
					
					//update downloaded field of invoice
					$this->gen_m->update("tax_invoice", ["invoice_id" => $item->invoice_id], ["downloaded" => true]);
				}
				
				//echo "OK. ".$item->doc_number."<br/>";
				
				//break;
			}
			
			//break;
			$invoices = $this->gen_m->filter("tax_invoice", false, ["downloaded" => false], null, null, [["date_enter", "asc"]], 1000, 0);
		}
		
		echo "Finished. ".number_format($count)." eDocuments downloaded.<br/>";
		echo number_Format(microtime(true) - $start_time, 2)." secs";
	}
}
