<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_purchase_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function make_row($po_num, $ship_to_code, $currency, $arrival_date, $model, $qty, $unit_price, $issue_date, $customer_name){
		return [
			$po_num,//Customer PO No.
			$ship_to_code,//Ship To
			$currency,//Currency
			$arrival_date,//Request Arrival Date(YYYYMMDD)
			$model,//Model
			$qty,//Quantity
			$unit_price,//Unit Selling Price
			null,//Warehouse
			null,//Payterm
			null,//Shipping Remark
			null,//Invoice Remark
			null,//Customer RAD(YYYYMMDD)
			$issue_date,//Customer PO Date(YYYYMMDD)
			null,//H Flag
			null,//OP Code
			null,//Country
			null,//Postal Code
			null,//Address1
			null,//Address2
			null,//Address3
			null,//Address4
			null,//City
			null,//State
			null,//Province
			null,//County
			$customer_name,//Consumer Name
			null,//Consumer Phone No.
			null,//Receiver Name
			null,//Receiver Phone No.
			null,//Freight Charge
			null,//Freight Term
			null,//Price Condition
			null,//Picking Remark
			null,//Shipping Method
		];
	}
	
	private function hiraoka_pre($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(explode(" ", $rows_input[5])[4]);
		
		$aux = explode("/", trim($rows_input[14]));
		$issue_date = $aux[2].$aux[1].$aux[0];
		
		$aux = explode("/", trim($rows_input[15]));
		$arrival_date = $aux[2].$aux[1].$aux[0];
		
		$currency = "PEN";
		
		foreach($rows_input as $r){
			$aux = array_values(array_filter(explode(" ", $r)));
			
			if (count($aux) > 6) if (is_numeric($aux[0])){
				//get last position of number and extract total amount
				$aux_text = trim($aux[5]);
				preg_match('/[a-z]+/i', $aux_text, $matches, PREG_OFFSET_CAPTURE);
				
				$total = substr($aux_text, 0, $matches[0][1]);
				$sku = trim($aux[1]);
				$qty = trim($aux[3]);
				$unit_price = trim($aux[4]);
				
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				$model = ($prod) ? $prod->model : "No SKU: ".$sku;
				
				$rows[] = $this->make_row($po_num, $ship_to->ship_to_code, $currency, $arrival_date, $model, $qty, str_replace(",", "", $unit_price), $issue_date, $ship_to->customer->customer);
			}
		}
		
		return $rows;
	}
	
	private function hiraoka_sku($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(explode(" ", $rows_input[5])[4]);
		
		$aux = explode("/", trim($rows_input[14]));
		$issue_date = $aux[2].$aux[1].$aux[0];
		
		$aux = explode("/", trim($rows_input[15]));
		$arrival_date = $aux[2].$aux[1].$aux[0];
		
		$currency = "PEN";
		
		$prod_num = 1;
		foreach($rows_input as $i => $r){
			$aux = array_values(array_filter(explode(" ", $r)));
			if (count($aux) > 6) if (is_numeric($aux[0])){
				//get last position of number and extract total amount
				$aux_text = trim($aux[4]);
				preg_match('/[a-z]+/i', $aux_text, $matches, PREG_OFFSET_CAPTURE);
				
				$total = substr($aux_text, 0, $matches[0][1]);
				$sku = substr(trim($aux[0]), strlen((string)$prod_num));//need to work with sku = [num][sku] => need to extract num value
				$qty = trim($aux[2]);
				$unit_price = trim($aux[3]);
				
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				$model = ($prod) ? $prod->model : "No SKU: ".$sku;
				
				$rows[] = $this->make_row($po_num, $ship_to->ship_to_code, $currency, $arrival_date, $model, $qty, str_replace(",", "", $unit_price), $issue_date, $ship_to->customer->customer);
				
				$prod_num++;
			}
		}
		
		return $rows;
	}
	
	private function estilos_sku($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(array_values(array_filter(explode(" ", $rows_input[0])))[5]);
		
		$aux = explode("/", trim(str_replace("Se recepciona desde:", "", $rows_input[15])));
		$issue_date = $aux[2].$aux[1].$aux[0];
		
		$aux = explode("/", trim(str_replace(":", "", explode(" ", $rows_input[14])[3])));
		$arrival_date = $aux[2].$aux[1].$aux[0];
		
		$currency = "PEN";
		
		foreach($rows_input as $i => $r){
			$r = str_replace("\t", " ", $r);
			$r = str_replace(". .", " ", $r);
			$r = str_replace("NINGUNA", " ", $r);
			$r = str_replace("LG", " ", $r);
			
			$aux = array_values(array_filter(explode(" ", $r)));

			$numeric = $no_numeric = [];
			if (is_numeric($aux[0]) and (count($aux) > 9)){
				foreach($aux as $a) if (is_numeric(str_replace(",", "", $a))) $numeric[] = $a; else $no_numeric[] = $a;
				
				$sku = (int)$numeric[0];
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				
				$model = ($prod) ? $prod->model : "No SKU: ".$sku;
				$qty = (int)trim($numeric[1]);
				$unit_price = str_replace(",", "", $numeric[count($numeric) - 4]);
				$total = $unit_price * $qty;

				$rows[] = $this->make_row($po_num, $ship_to->ship_to_code, $currency, $arrival_date, $model, $qty, str_replace(",", "", $unit_price), $issue_date, $ship_to->customer->customer);
			}
		}
		
		return $rows;
	}
	
	private function sodimac($rows_input, $ship_to){
		$rows = $products = [];
		
		$i = 0;
		$limit = count($rows_input);
		$currency = "PEN";
		
		//foreach($rows_input as $i => $r){
		while($i < $limit){
			$r = $rows_input[$i];
			
			switch($r){
				case "Creada por": 
					$po_num = $rows_input[$i-1];
					break;
				case "Fecha Emision":
					$issue_date = date('Ymd', strtotime($rows_input[$i+1]));
					$i++;
					break;
				case "Fecha Recibo Esperada":
					$arrival_date = date('Ymd', strtotime($rows_input[$i+1]));
					$i++;
					break;
				case "Monto Total":
					$i++;
					$products = [];
					while(true){
						$prod = [];
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						$prod[] = $rows_input[$i]; $i++;
						
						$products[] = $prod;
						if ($rows_input[$i] === "TOTAL") break;
					}
					
					break;
			}
			
			if ($products) foreach($products as $p){
				//set model
				$sku = trim($p[2]);
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				$model = ($prod) ? $prod->model : "No SKU: ".$sku;
				
				$qty = intval(str_replace(',', '.', str_replace('.', '', $p[8])));
				$unit_price = floatval(str_replace(',', '.', str_replace('.', '', $p[7])));
				
				$rows[] = $this->make_row($po_num, $ship_to->ship_to_code, $currency, $arrival_date, $model, $qty, $unit_price, $issue_date, $ship_to->customer->customer);
			}
			
			$products = [];
			$i++;
		}
		
		return $rows;
	}
	
	private function chancafe($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(explode(" ", $rows_input[14])[3]);
		//echo "po_num: ".$po_num."<br/><br/>";
		
		$aux = explode("/", trim(str_replace(": ", "", $rows_input[13])));
		$aux_date = $aux[2]."-".$aux[1]."-".$aux[0];
		
		$issue_date = date("Ymd", strtotime($aux_date));
		//echo "issue_date: ".$issue_date."<br/><br/>";
		
		$arrival_date = date("Ymd", strtotime("+1 month", strtotime($aux_date)));
		//echo "arrival_date: ".$arrival_date."<br/><br/>";
		
		$currency = "PEN";
		
		$is_product = false;
		foreach($rows_input as $i => $r){
			if ($r === ".............................") $is_product = false;
			
			if ($is_product){
				$aux = array_values(array_filter(explode(" ", $r)));
				
				$qty = trim(str_replace("UND", "", $aux[1]));
				$unit_price = trim(str_replace(",", "", $aux[2]));
				$total = $unit_price * $qty;
				$sku = trim($aux[8]);
				
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				$model = ($prod) ? $prod->model : "No SKU: ".$sku;
				/*
				echo $i." ====> ";
				echo strlen($r);
				echo " ====> ";
				print_r($aux); echo "<br/>";
				echo $sku." /// ".$model." /// ".$qty." /// ".$unit_price." /// ".$total;
				echo "<br/><br/>";
				*/
				$rows[] = $this->make_row($po_num, $ship_to->ship_to_code, $currency, $arrival_date, $model, $qty, str_replace(",", "", $unit_price), $issue_date, $ship_to->customer->customer);
			}
			
			if ($r === "MARCA") $is_product = true;
			
			
		}
		
		return $rows;
	}
	
	public function conecta_excel($filename, $ship_to){
		$rows = [];
		
		$spreadsheet = IOFactory::load($filename);
		$sheet = $spreadsheet->getActiveSheet();
		
		$max_row = $sheet->getHighestRow();
		
		for ($row = 2; $row <= $max_row; $row++){
			$aux = explode("-", trim($sheet->getCell('G'.$row)->getValue()));
			$issue_date = $aux[0].$aux[1].$aux[2];
			
			$aux = explode("-", trim($sheet->getCell('H'.$row)->getValue()));
			$arrival_date = $aux[0].$aux[1].$aux[2];
			
			$sku = trim($sheet->getCell('K'.$row)->getValue());
			$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
			$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
			$model = ($prod) ? $prod->model : "No SKU: ".$sku;
			
			$rows[] = $this->make_row(trim($sheet->getCell('A'.$row)->getValue()), $ship_to->ship_to_code, trim($sheet->getCell('I'.$row)->getValue()), $arrival_date, $model, trim($sheet->getCell('U'.$row)->getValue()), trim($sheet->getCell('R'.$row)->getValue()), $issue_date, $ship_to->customer->customer);
		}
		
		return $rows;
	}
	
	private function pdf_to_excel($filename, $po_template, $ship_to){
		$url = ""; $rows = [];
		
		$this->load->library('my_pdf');
		$rows = $this->my_pdf->to_text($filename);
		
		switch($po_template->code){
			case "hiraoka_pre": $rows = $this->hiraoka_pre($rows, $ship_to); break;
			case "hiraoka_sku": $rows = $this->hiraoka_sku($rows, $ship_to); break;
			case "estilos_sku": $rows = $this->estilos_sku($rows, $ship_to); break;
			case "sodimac": $rows = $this->sodimac($rows, $ship_to); break;
			case "chancafe": $rows = $this->chancafe($rows, $ship_to); break;
			default: $rows = [];
		}
		
		if ($rows){
			$header = ["Customer PO No.","Ship To","Currency","Request Arrival Date(YYYYMMDD)","Model","Quantity","Unit Selling Price","Warehouse","Payterm","Shipping Remark","Invoice Remark","Customer RAD(YYYYMMDD)","Customer PO Date(YYYYMMDD)","H Flag","OP Code","Country","Postal Code","Address1","Address2","Address3","Address4","City","State","Province","County","Consumer Name","Consumer Phone No.","Receiver Name","Receiver Phone No.","Freight Charge","Freight Term","Price Condition","Picking Remark","Shipping Method",];
			
			//make excel without title
			$url = $this->my_func->generate_excel_report("scm_po_converted.xlsx", null, $header, $rows);
		}
		
		return $url;
	}
	
	private function excel_to_excel($filename, $po_template, $ship_to){
		$url = ""; $rows = [];
		
		switch($po_template->code){
			case "conecta_excel": $rows = $this->conecta_excel($filename, $ship_to); break;
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
			$url = $this->my_func->generate_excel_report("scm_po_converted.xlsx", null, $header, $rows);
		}
		
		return $url;
	}
	
	public function test(){
		//return;//just activate when you need to test conversion
		
		
		/* pdf to excel 
		*/
		$filename = './test_files/scm_po_chancafe/OC 011-469 LG RODRIGUEZ DE MENDOZA.pdf';
		//$filename = './test_files/scm_po_hiraoka/132527 LG - LB - VES_TIENDAS.pdf';
		$po_template = $this->gen_m->unique("purchase_order_template", "template_id", 6);//chancafe
		$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", 149);//chancafe
		$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
		
		echo $this->pdf_to_excel($filename, $po_template, $ship_to);
		
		/* excel to excel
		$filename = './test_files/module/conecta/conecta2.xls';
		$po_template = $this->gen_m->unique("purchase_order_template", "template_id", 3);//conecta excel
		$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", 3);//conecta
		$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
		
		echo $this->excel_to_excel($filename, $po_template, $ship_to);
		*/
	}
	
	public function convert_po(){
		ini_set('display_errors', 0);
		
		$type = "error"; $msg = $url = "";
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'pdf|xls|xlsx|csv',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'scm_po_file',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('po_file')){
			$result = $this->upload->data();
			//print_r($result);
			/*
			Array
			(
				[file_name] => po_file.pdf
				[file_type] => application/pdf
				[file_path] => C:/xampp_lg/htdocs/llamasys/upload/module/
				[full_path] => C:/xampp_lg/htdocs/llamasys/upload/module/po_file.pdf
				[raw_name] => po_file
				[orig_name] => po_file.pdf
				[client_name] => test_hiraoka5.pdf
				[file_ext] => .pdf
				[file_size] => 106.61
				[is_image] => 
				[image_width] => 
				[image_height] => 
				[image_type] => 
				[image_size_str] => 
			)
			*/
			
			$po_file = './upload/scm_po_file'.$result["file_ext"];
			$po_template = $this->gen_m->unique("purchase_order_template", "template_id", $this->input->post("po_template"));
			$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", $this->input->post("ship_to"));
			
			if ($po_template and $ship_to){
				$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
				
				try {
					switch($result["file_ext"]){
						case ".pdf": $url = $this->pdf_to_excel($po_file, $po_template, $ship_to); break;
						case ".xlsx": $url = $this->excel_to_excel($po_file, $po_template, $ship_to); break;
						case ".xls": $url = $this->excel_to_excel($po_file, $po_template, $ship_to); break;
						case ".csv": $url = $this->excel_to_excel($po_file, $po_template, $ship_to); break;
					}
					
					if ($url){
						$type = "success";
						$msg = "PO conversion is completed.";
					}else $msg = "An error occurred. Please try again.";	
				} catch (Exception $e) {
					$msg = 'Caught exception: '.$e->getMessage();
				} catch (Error $err) {
					$msg = 'Fatal error caught: '.$err->getMessage();
				}
			}else $msg = "You must select PO template and customer ship to.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	public function index(){
		$po_templates_words = [];
		$po_templates_rec = $this->gen_m->all("scm_purchase_order_template", [["template", "asc"]]);
		foreach($po_templates_rec as $temp) $po_templates_words[] = $temp->customer_word;
		$po_templates_words = array_unique($po_templates_words);

		$customer_ids = [];
		foreach($po_templates_words as $word){
			$customer_recs = $this->gen_m->filter("customer", true, null, [["field" => "customer", "values" => [$word]]]);
			foreach($customer_recs as $cus) $customer_ids[] = $cus->customer_id;
		}

		$ship_tos = $this->gen_m->filter("customer_ship_to", true, null, null, [["field" => "customer_id", "values" => $customer_ids]], [["ship_to_code", "asc"], ["address", "asc"]]);
		foreach($ship_tos as $i => $s){
			if ($s->address){
				$cus = $this->gen_m->unique("customer", "customer_id", $s->customer_id);
				$s->op = $cus->customer." ** ".$cus->bill_to_code." ** ".$s->ship_to_code." ** ".$s->address;	
			}else unset($ship_tos[$i]);
		}
		
		usort($ship_tos, function($a, $b) {
			return strcmp($a->op, $b->op);
		});
		
		$data = [
			"purchase_order_temps" => $po_templates_rec,
			"ship_tos" => $ship_tos,
			"main" => "module/purchase_order/index",
		];
		
		$this->load->view('layout', $data);
	}
}
