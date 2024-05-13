<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Invoice extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	
	public function index(){
		$data = [
			"main" => "tax/invoice/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function comparison($file_p, $file_g){
		set_time_limit(0);
		
		$invoices = [];
		
		//Peperless process
		$sheet = IOFactory::load($file_p)->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();

		for ($row = 3; $row <= $max_row; $row++){//Paperless excel starts from row 3
			$rowdata = $this->my_func->arr_trim($sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			if (!array_key_exists($rowdata[1], $invoices)) 
				$invoices[$rowdata[1]] = [$rowdata[0], $rowdata[1], $rowdata[11]];
		}
		
		//GERP process
		$sheet = IOFactory::load($file_g)->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();
		
		$rows = [];
		$blank_arr = ["", "", ""];
		
		for ($row = 2; $row <= $max_row; $row++){
			$rowdata = $this->my_func->arr_trim($sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			//print_r($rowdata); echo "<br/>";
			
			$aux = [];
			if ($rowdata[4]) $aux[] = $rowdata[4];
			if ($rowdata[5]) $aux[] = $rowdata[5];
			$inv = implode("-", $aux);
			
			$data = [$rowdata[7], $rowdata[0], $rowdata[1], date("Y-m-d", strtotime($rowdata[2])), $rowdata[9], $rowdata[10], $rowdata[13], $rowdata[14], $rowdata[15]];
			$rows[] = array_key_exists($inv, $invoices) ? array_merge($invoices[$inv], $data) : array_merge($blank_arr, $data);
		}
		
		usort($rows, function($a, $b) {
			if (!strcmp($a[0], $b[0])) return (strtotime($a[6]) > strtotime($b[6]));
			else return strcmp($a[0], $b[0]);
		});
		
		$header = ["Document type", "Documment number", "Status (Paperless)", "Status (GERP)", "Voucher_No", "Header_Id", "Date", "Business number", "Business name", "Amount", "VAT", "Total"];
		
		$url = $this->my_func->generate_excel_report("tax_invoice_comparison_report.xlsx", null, $header, $rows);
		return $url;
	}
	
	public function comparison_report(){
		$type = "error"; $msg = $url = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
			$start_time = microtime(true);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
			];
			$this->load->library('upload', $config);

			$name_p = $name_g = "";
			
			$config['file_name'] = 'tax_invoice_paperless';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_paperless')){
				$data = $this->upload->data();
				$name_p = $data['orig_name'];
			}
			
			$config['file_name'] = 'tax_invoice_gerp';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_gerp')){
				$data = $this->upload->data();
				$name_g = $data['orig_name'];
			}
			
			$name_p = "";
			$name_g = "";
			
			if ($name_p and $name_g){
				$type = "success";
				$msg = "Invoice record comparison report has been created. (".number_format(microtime(true) - $start_time, 2)." sec)";
				$url = $this->comparison($file_p, $file_g);
			}else $msg = "Select all files: Paperless and GERP Invoice reports.";
		}else{
			$msg = "Your session is finished.";
			$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
