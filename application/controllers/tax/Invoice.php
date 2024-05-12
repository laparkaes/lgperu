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
		$sheet_p = IOFactory::load("./test_files/tax_e_invoice/paperless 202404.xlsx")->getActiveSheet();
		$max_row = $sheet_p->getHighestRow();
		$max_col = $sheet_p->getHighestColumn();

		for ($row = 3; $row <= $max_row; $row++){//Paperless excel starts from row 3
			$rowdata = $this->my_func->arr_trim($sheet_p->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			if (!array_key_exists($rowdata[1], $invoices)) $invoices[$rowdata[1]] = ["inv" => $rowdata[1], "type" => $rowdata[0], "status" => $rowdata[11]];
		}
		
		//GERP process
		
		/*
		$sheet_g = IOFactory::load("./test_files/tax_e_invoice/gerp 202404.xlsx")->getActiveSheet();
		$max_row = $sheet_g->getHighestRow();
		$max_col = $sheet_g->getHighestColumn();

		//TIPO DOCUMENTO, SERIE-CORRELATIVO, RUC RECEPTOR, RAZON SOCIAL, FECHA INGRESO, FECHA EMISION,  MONTO, MONEDA, SUCURSAL, CAJA, ESTADO SUNAT, DESC ESTADO SUNAT
				
		for ($row = 2; $row <= $max_row; $row++){
			$rowdata = $this->my_func->arr_trim($sheet_g->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
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
		}
		
		*/
		
		foreach($invoices as $num => $inv){
			echo $num." ====> ";
			print_r($inv);
			echo "<br/>";	
			
		}
		echo "<br/><br/>";
		echo count($invoices);
		
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
