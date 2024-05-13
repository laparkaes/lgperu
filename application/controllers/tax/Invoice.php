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
	
	public function comparison(){
		set_time_limit(0);
		$start_time = microtime(true);
		
		$invoices = [];
		
		//Peperless process
		$sheet = IOFactory::load("./test_files/tax_e_invoice/paperless 202404.xlsx")->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();

		for ($row = 3; $row <= $max_row; $row++){//Paperless excel starts from row 3
			$rowdata = $this->my_func->arr_trim($sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			if (!array_key_exists($rowdata[1], $invoices)) 
				$invoices[$rowdata[1]] = [$rowdata[0], $rowdata[1], $rowdata[11]];
		}
		
		//GERP process
		$sheet = IOFactory::load("./test_files/tax_e_invoice/gerp 202404.xlsx")->getActiveSheet();
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
		
		$url = $this->my_func->generate_excel_report("tax_invoice_comparison.xlsx", null, $header, $rows);
		return $url;
	}
}
