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
				$invoices[$rowdata[1]] = [$rowdata[11], $rowdata[0], $rowdata[1]];
		}
		
		//GERP process
		$sheet = IOFactory::load("./test_files/tax_e_invoice/gerp 202404.xlsx")->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();
		
		$report = [];
		$blank_arr = ["", "", ""];
		
		for ($row = 2; $row <= $max_row; $row++){
			$rowdata = $this->my_func->arr_trim($sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			$aux = [];
			if ($rowdata[4]) $aux[] = $rowdata[4];
			if ($rowdata[5]) $aux[] = $rowdata[5];
			$inv = implode("-", $aux);
			
			$data = [$rowdata[0], $rowdata[1], $rowdata[2], $rowdata[9], $rowdata[10]];
			$report[] = array_key_exists($inv, $invoices) ? array_merge($invoices[$inv], $data) : array_merge($blank_arr, $data);
			
			/*
			$inv_date = $rowdata[26];
			
			$aux = [];
			if ($rowdata[4]) $aux[] = $rowdata[4];
			if ($rowdata[5]) $aux[] = $rowdata[5];
			
			$inv = implode("-", $aux);
			if ($inv){
				$inv_key = $inv."_".$inv_date;
				
				if (!array_key_exists($inv_key, $invoices)) $invoices[$inv_key] = ["inv" => $inv, "date" => $inv_date, "g" => null, "p" => null];
				$invoices[$inv_key]["g"] = $rowdata[7];
			}
			*/
		}
		
		foreach($report as $num => $r){
			echo $num." ====> ";
			print_r($r);
			echo "<br/>";
			
		}
		echo "<br/><br/>";
		
		echo count($report);
		
		//echo $max_row." ".$max_col;
		
		/*
		echo "<br/><br/><br/>";
		
		$sheet_p = IOFactory::load("./test_files/tax_e_invoice/paperless 202404.xlsx")->getActiveSheet();
		
		$max_row = $sheet_p->getHighestRow();
		$max_col = $sheet_p->getHighestColumn();
		
		echo $max_row." ".$max_col;
		*/
	}
}
