<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Promotion extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "module/promotion/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function test(){
		$type = "error"; $msg = "";
		
		$spreadsheet = IOFactory::load("./test_files/sa_promotion/sa_promotion.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue()),
			trim($sheet->getCell('J1')->getValue()),
			trim($sheet->getCell('K1')->getValue()),
			trim($sheet->getCell('L1')->getValue()),
			trim($sheet->getCell('M1')->getValue()),
		];
		
		$h_origin = [
			"Seq",
			"Company Name",
			"Division Name",
			"Promotion No",
			"Promotion Line No",
			"Fecha Inicio",
			"Fecha Fin",
			"Customer Code",
			"Modelo",
			"PVP",
			"Costo Sellin",
			"* Precio Promotion",
			"* Nuevo Margen",
		];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		$promotions = [];
		
		if ($header_validation){
			$max_row = $sheet->getHighestRow();
			//$max_col = $sheet->getHighestColumn();
			
			for($i = 2; $i < $max_row; $i++){
				$promotions[] = [
					"prom" 			=> $sheet->getCell('D'.$i)->getValue(),
					"prom_line" 	=> $sheet->getCell('E'.$i)->getValue(),
					"date_start"	=> $sheet->getCell('F'.$i)->getValue(),
					"date_end"		=> $sheet->getCell('G'.$i)->getValue(),
					"cus_code"		=> $sheet->getCell('H'.$i)->getValue(),
					"prod_model"	=> $sheet->getCell('I'.$i)->getValue(),
					"price_sellin"	=> $sheet->getCell('K'.$i)->getValue(),
					"prom" 			=> $sheet->getCell('D'.$i)->getValue(),
					"prom" 			=> $sheet->getCell('D'.$i)->getValue(),
				];
				
				
				$prom = ;
				$ = ;
				
				echo $prom." ".$prom_line."<br/>";
			}
			
		}else $msg = "Wrong file uploaded.";
		
		echo $msg;
	}
}
