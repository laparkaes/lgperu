<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//use PhpOffice\PhpSpreadsheet\IOFactory;

class Purchase_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->color_rgb = [
			"green" => "198754",
			"red" => "dc3545",
		];
	}
	
	private function hiraoka_original($rows_input, $bill_to, $ship_to){
		$customer = $this->gen_m->unique("customer", "bill_to_code", $bill_to);
		//$ship_to = $this->gen_m->filter("customer_ship_to", true, ["customer_id" => $customer->customer_id, "ship_to" => $ship_to]);
		$ship_to = null;
		
		$rows = [];
		
		$ruc = str_replace("RUC: ", "", trim($rows_input[3]));
		$customer = $customer->customer;
		$num_order = trim(explode(" ", $rows_input[5])[4]);
		$issue_date = trim($rows_input[14]);
		$end_date = trim($rows_input[15]);
		$address = $ship_to ? $ship_to->address : trim($rows_input[16]).", ".trim($rows_input[17]);
		$payment = trim($rows_input[18]);
		$currency = "S/";
		
		foreach($rows_input as $r){
			$aux = array_values(array_filter(explode(" ", $r)));
			
			if (count($aux) > 6) if (is_numeric($aux[0])){
				//get last position of number and extract total amount
				$aux_text = trim($aux[5]);
				preg_match('/[a-z]+/i', $aux_text, $matches, PREG_OFFSET_CAPTURE);
				$total = substr($aux_text, 0, $matches[0][1]);
				$aux[5] = str_replace($total, "", $aux_text);
				
				$code = trim($aux[1]); unset($aux[1]);
				$num = trim($aux[0]); unset($aux[0]);
				$unit = trim($aux[2]); unset($aux[2]);
				$qty = trim($aux[3]); unset($aux[3]);
				$unit_price = trim($aux[4]); unset($aux[4]);
				
				//clean aux => unset Pre-Distribucion
				foreach($aux as $a_i => $a) if (is_numeric($a)) unset($aux[$a_i]);
				
				//merge aux with " " to make product desription string
				$description = implode(" ", $aux);//desription
				
				//set row
				$row = [];
				$row[] = $ruc;//Razon Social
				$row[] = $customer;//Customer Name
				$row[] = $num_order;//Customer PO No
				$row[] = $end_date;//Customer PO End Date
				$row[] = $issue_date;//Customer PO Issue Date
				$row[] = $address;//Customer Shop Code
				$row[] = $address;//Customer Shop Name
				$row[] = $currency;//Currency
				$row[] = $end_date;//Request Arrival Date
				$row[] = $code;//Customer Model Code
				$row[] = null;//LG Model Code
				$row[] = null;//LG Model Code.Suffix
				$row[] = $description;//Customer Model Description
				$row[] = $qty;//Qty
				$row[] = str_replace(",", "", $unit_price);//Unit Price
				$row[] = null;//Descuento
				$row[] = str_replace(",", "", $total);//Total
				$row[] = null;//EAN/UP C Code
				$row[] = $payment;//Payment Days
				$row[] = null;//Payment Terms
				$row[] = null;//Shipping Remark
				$row[] = null;//Picking Remark
				$row[] = null;//Invoice Remark
				
				$rows[] = $row;
			}
		}
		
		return $rows;
	}
	
	private function pdf_to_excel($filename, $logic_type, $bill_to, $ship_to){
		$url = ""; $rows = [];
		
		$this->load->library('my_pdf');
		$contents = $this->my_pdf->to_text("./test_files/scm/".$filename.".pdf");
		foreach($contents as $content){
			$page = $content["page"];
			$text = $content["text"];
			
			$lines = explode("\n", $text);
			$lines = array_values(array_filter($lines));
			foreach($lines as $line) $line = trim($line);
			
			$rows = array_merge($rows, $lines);
		}
		
		switch($logic_type){
			case "hiraoka_original": 
				$rows = $this->hiraoka_original($rows, $bill_to, $ship_to);
				break;
			default:
				echo "No type selected.";
		}
		
		if ($rows){
			$header = [
				"Razon Social",
				"Customer Name",
				"Customer PO No",
				"Customer PO End Date",
				"Customer PO Issue Date",
				"Customer Shop Code",
				"Customer Shop Name",
				"Currency",
				"Request Arrival Date",
				"Customer Model Code",
				"LG Model Code",
				"LG Model Code.Suffix",
				"Customer Model Description",
				"Qty",
				"Unit Price",
				"Descuento",
				"Total",
				"EAN/UP C Code",
				"Payment Days",
				"Payment Terms",
				"Shipping Remark",
				"Picking Remark",
				"Invoice Remark",
			];
			
			//make excel without title
			$url = $this->my_func->generate_excel_report($filename.".xlsx", null, $header, $rows);
		}
		
		return $url;
	}
	
	public function index(){
		
		//for($i = 0; $i < 2; $i++) echo $this->pdf_to_excel("test_hiraoka".$i, "hiraoka_original", "PE000816001B", "815VS-S")."<br/>";
		
		$data = [
			"purchase_order_pdfs" => $this->gen_m->all("purchase_order_pdf", [["pdf", "asc"]]),
			"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "scm/purchase_order/index",
		];
		
		$this->load->view('layout', $data);
	}
}
