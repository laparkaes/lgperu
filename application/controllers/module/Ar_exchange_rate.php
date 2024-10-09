<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ar_exchange_rate extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$from = date("Y-m-d", strtotime(date("Y-m-d")." -1 year"));
		
		$data = [
			"er_pyg" => $this->gen_m->filter("exchange_rate", false, ["date >=" => $from, "currency" => "PYG"], null, null, [["date", "desc"]]),
			"main" => "module/ar_exchange_rate/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function proxy_dnit(){
		$content = file_get_contents('https://www.dnit.gov.py/web/portal-institucional/cotizaciones');
		$content = str_replace("script", "div", $content);
		$content = str_replace("link", "div", $content);
		$content = str_replace("meta", "div", $content);
		$content = str_replace("style", "div", $content);
		
		echo $content;
	}
	
	public function upload_pyg(){
		$data = $this->input->post("data");
		foreach($data as $item){
			$aux_date = $item[0]."-".$item[1]."-".$item[2];
			
			$row = [
				"date" => $aux_date,
				"date_apply" => date("Y-m-d", strtotime($aux_date." +1 day")),
				"currency" => "PYG",
			];
			
			if (!$this->gen_m->filter("exchange_rate", false, $row)){
				$row["buy"] = $item[3];
				$row["sell"] = $item[4];
				$row["avg"] = round((floatval($item[3]) + floatval($item[4])) / 2 , 2);
				
				$this->gen_m->insert("exchange_rate", $row);
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => "success", "msg" => "Exchage rate USD > PYG has been updated."]);
	}
	
	public function upload_pen(){
		$now = "2024-09-18";
		$code = "02";
		
		
		//$er = $this->my_func->load_exchange_rate_sbs($now);
		
		if ($now) $now = date("dmY", strtotime($now));
		else $now = date("dmY");
		
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoibGdlIiwic3ViIjoibGdlIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjpbIk1hbmFnZXIiLCJTdXBlcnZpc29yIl0sIm5iZiI6MTcxODgxOTgzOSwiZXhwIjoxNzUwMzU1ODM5LCJpc3MiOiJodHRwOi8vand0YXV0aHpzcnYuYXp1cmV3ZWJzaXRlcy5uZXQiLCJhdWQiOiIwOTkxNTNjMjYyNTE0OWJjOGVjYjNlODVlMDNmMDAyMiJ9.1ejIUlAPbq8FhggDzJIhXkYrRCMli1ghC8OI2PETwZc';
		
		$ch = curl_init();
		$url = 'http://serviciosweb.sbs.gob.pe/api/tipocambio/'.$now.'/'.$code;
		$headers = [
			'Accept: application/json',
			'Authorization: Bearer '.$token,
		];
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);//max waiting time 5 secs

		$response = curl_exec($ch);
		
		curl_close($ch);
		
		
		print_r($response);
	}
	
	public function test(){
		
		$data = [
			"date" => "2024-10-08",
			"date_apply" => "2024-10-09",
			"currency" => "PYG",
		];
		
		print_r($this->gen_m->filter("exchange_rate", false, $data));
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
