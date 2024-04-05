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
	
	private function hiraoka_original($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(explode(" ", $rows_input[5])[4]);
		
		$aux = explode("/", trim($rows_input[14]));
		$issue_date = $aux[2].$aux[1].$aux[0];
		
		$aux = explode("/", trim($rows_input[15]));
		$arrival_date = $aux[2].$aux[1].$aux[0];
		
		//$payment = trim($rows_input[18]);
		$currency = "PEN";
		
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
				$row[] = $po_num;//Customer PO No.
				$row[] = $ship_to->ship_to_code;//Ship To
				$row[] = $currency;//Currency
				$row[] = $arrival_date;//Request Arrival Date(YYYYMMDD)
				//$row[] = $code." ** ".$description;//Model
				$row[] = $description;//Model
				$row[] = $qty;//Quantity
				$row[] = str_replace(",", "", $unit_price);//Unit Selling Price
				$row[] = null;//Warehouse
				$row[] = null;//Payterm
				$row[] = null;//Shipping Remark
				$row[] = null;//Invoice Remark
				$row[] = null;//Customer RAD(YYYYMMDD)
				$row[] = $issue_date;//Customer PO Date(YYYYMMDD)
				$row[] = null;//H Flag
				$row[] = null;//OP Code
				$row[] = null;//Country
				$row[] = null;//Postal Code
				$row[] = null;//Address1
				$row[] = null;//Address2
				$row[] = null;//Address3
				$row[] = null;//Address4
				$row[] = null;//City
				$row[] = null;//State
				$row[] = null;//Province
				$row[] = null;//County
				$row[] = $ship_to->customer->customer;//Consumer Name
				$row[] = null;//Consumer Phone No.
				$row[] = null;//Receiver Name
				$row[] = null;//Receiver Phone No.
				$row[] = null;//Freight Charge
				$row[] = null;//Freight Term
				$row[] = null;//Price Condition
				$row[] = null;//Picking Remark
				$row[] = null;//Shipping Method
				
				$rows[] = $row;
			}
		}
		
		return $rows;
	}
	
	private function pdf_to_excel($filename, $po_pdf, $ship_to){
		$url = ""; $rows = [];
		
		$this->load->library('my_pdf');
		$rows = $this->my_pdf->to_text($filename);
		
		switch($po_pdf->code){
			case "hiraoka_original": 
				$rows = $this->hiraoka_original($rows, $ship_to);
				break;
		}
		
		if ($rows){
			$header = [
				"Customer PO No.",
				"Ship To",
				"Currency",
				"Request Arrival Date(YYYYMMDD)",
				"Model",
				"Quantity",
				"Unit Selling Price",
				"Warehouse",
				"Payterm",
				"Shipping Remark",
				"Invoice Remark",
				"Customer RAD(YYYYMMDD)",
				"Customer PO Date(YYYYMMDD)",
				"H Flag",
				"OP Code",
				"Country",
				"Postal Code",
				"Address1",
				"Address2",
				"Address3",
				"Address4",
				"City",
				"State",
				"Province",
				"County",
				"Consumer Name",
				"Consumer Phone No.",
				"Receiver Name",
				"Receiver Phone No.",
				"Freight Charge",
				"Freight Term",
				"Price Condition",
				"Picking Remark",
				"Shipping Method",
			];
			
			//make excel without title
			$url = $this->my_func->generate_excel_report("scm_po.xlsx", null, $header, $rows);
		}
		
		return $url;
	}
	
	public function convert_po(){
		$type = "error"; $msg = $url = "";
		
		$config = [
			'upload_path'	=> './upload/scm/',
			'allowed_types'	=> 'pdf',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'scm_po',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('pdf_file')){
			$pdf_file = './upload/scm/scm_po.pdf';
			$po_pdf = $this->gen_m->unique("purchase_order_pdf", "pdf_id", $this->input->post("po_pdf"));
			$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", $this->input->post("ship_to"));
			
			if ($po_pdf and $ship_to){
				$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
				$url = $this->pdf_to_excel($pdf_file, $po_pdf, $ship_to);
				if ($url){
					$type = "success";
					$msg = "PO conversion is completed.aaaa";
				}else $msg = "An error occurred. Please try again.";	
			}else $msg = "You must select PO template and customer ship to.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	public function index(){
		$ship_tos = $this->gen_m->all("customer_ship_to", [["ship_to_code", "asc"], ["address", "asc"]]);
		foreach($ship_tos as $s){
			$cus = $this->gen_m->unique("customer", "customer_id", $s->customer_id);
			$s->op = $cus->customer." ** ".$cus->bill_to_code." ** ".$s->ship_to_code." ** ".$s->address;
		}
		
		usort($ship_tos, function($a, $b) {
			return strcmp($a->op, $b->op);
		});
		
		$data = [
			"purchase_order_pdfs" => $this->gen_m->all("purchase_order_pdf", [["pdf", "asc"]]),
			"ship_tos" => $ship_tos,
			"main" => "scm/purchase_order/index",
		];
		
		$this->load->view('layout', $data);
	}
}
