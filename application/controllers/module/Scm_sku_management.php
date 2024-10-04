<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_sku_management extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"main" => "module/scm_sku_management/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload(){
		
		return;
		
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
			$po_template = $this->gen_m->unique("scm_purchase_order_template", "template_id", $this->input->post("po_template"), false);
			$ship_to = $this->gen_m->unique("scm_ship_to", "ship_to_id", $this->input->post("ship_to"), false);
			
			if ($po_template and $ship_to){
				//$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
				
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
	
	public function test(){
		$res = ["type" => "error", "msg" => "Incorrect file to upload SKU."];
		
		set_time_limit(0);
		
		//load excel file
		$spreadsheet = IOFactory::load('./upload/scm_sku.xlsx');
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
		];
		
		//magento report header
		//$h_magento = ["ID", "Grand Total (Base)", "Grand Total (Purchased)", "Shipping Address", "Shipping and Handling", "Customer name", "SKU", "Level 1 Code", "Level 2 Code", "Level 3 Code", "Level 4 Code", "GERP Type", "GERP Order #"];
		$header = ["CUSTOMER BILL TO", "CUSTOMER NAME", "CUSTOMER SKU", "LG SKU"];

		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
	
		//incorrect file case
		if (!$is_ok) return $res;
		
		$max_row = $sheet->getHighestRow();
		for($i = 2; $i <= $max_row; $i++){
			
			$row = [
				"bill_to_code" => trim($sheet->getCell('A'.$i)->getValue()),
				"sku_customer" => trim($sheet->getCell('C'.$i)->getValue()),
				"sku" => trim($sheet->getCell('D'.$i)->getValue()),
			];
			
			if (!$this->gen_m->filter("scm_sku", false, $row)) $this->gen_m->insert("scm_sku", $row);
			
		}
		
		
	}
	
}
