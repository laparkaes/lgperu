<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Tax_invoice_comparison extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	
	public function index(){
		$data = [
			"main" => "module/tax_invoice_comparison/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function comparison($file_p, $file_g){
		set_time_limit(0);
		
		//Peperless process
		$sheet = IOFactory::load("./upload/".$file_p)->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();

		$invoices = [];
		
		for ($row = 3; $row <= $max_row; $row++){//Paperless excel starts from row 3
			$rowdata = [
				$sheet->getCell('A'.$row)->getValue(),
				$sheet->getCell('B'.$row)->getValue(),
				$sheet->getCell('C'.$row)->getValue(),
				$sheet->getCell('D'.$row)->getValue(),
				Date::excelToDateTimeObject($sheet->getCell('E'.$row)->getValue())->format('Y-m-d H:i:s'),
				Date::excelToDateTimeObject($sheet->getCell('F'.$row)->getValue())->format('Y-m-d'),
				$sheet->getCell('G'.$row)->getValue(),
				$sheet->getCell('H'.$row)->getValue(),
				$sheet->getCell('L'.$row)->getValue(),
			];
		
			$invoices[$rowdata[1]] = $rowdata;
		}
		
		$inv_blank = ["", "", "", "", "", "", "", "", ""];
		
		//GERP process
		$sheet = IOFactory::load("./upload/".$file_g)->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();

		$rows = [];

		for ($row = 3; $row <= $max_row; $row++){//Paperless excel starts from row 3
			$rowdata = [
				$sheet->getCell('A'.$row)->getValue(),
				$sheet->getCell('B'.$row)->getValue(),
				Date::excelToDateTimeObject($sheet->getCell('C'.$row)->getValue())->format('Y-m-d'),
				$sheet->getCell('G'.$row)->getValue(),
				$sheet->getCell('J'.$row)->getValue(),
				$sheet->getCell('K'.$row)->getValue(),
				$sheet->getCell('L'.$row)->getValue(),
				$sheet->getCell('M'.$row)->getValue(),
				$sheet->getCell('N'.$row)->getValue(),
				$sheet->getCell('O'.$row)->getValue(),
				$sheet->getCell('P'.$row)->getValue(),
				$sheet->getCell('Q'.$row)->getValue(),
				$sheet->getCell('R'.$row)->getValue(),
				$sheet->getCell('AC'.$row)->getValue(),
			];
			
			$inv_p = $inv_blank;
			$inv = $sheet->getCell('E'.$row)->getValue()."-".$sheet->getCell('F'.$row)->getValue();
			if ($inv !== "-") if (array_key_exists($inv, $invoices)) $inv_p = $invoices[$inv];
			
			//print_r($rowdata); echo "<br/>"; print_r($inv_p); echo "<br/><br/>";
			
			$data = [
				$inv_p[8],		//[0] => Aceptado
				$inv_p[0],		//[1] => FACTURA ELECTRONICA
				$inv_p[1],		//[2] => F001-271799
				$rowdata[3],	//[3] => 01-F001-00271799
				$rowdata[0],	//[16] => 110000549398
				$rowdata[1],	//[17] => 432812688
				$inv_p[4],		//[4] => 2024-04-02 11:00:00
				$rowdata[2],	//[5] => 2024-04-02
				$inv_p[7],		//[7] => PEN
				$inv_p[6],		//[8] => 60059.64
				"PEN",			//[9] => PEN
				$rowdata[8],	//[10] => 50898
				$rowdata[9],	//[11] => 9161.64
				$rowdata[10],	//[12] => 60059.64
				$rowdata[12],	//[13] => 1
				$rowdata[13],	//[6] => PE000968001B
				$rowdata[5],	//[15] => SAGA FALABELLA S A
				$rowdata[4],	//[14] => 20100128056
				$rowdata[6],	//[20] => PRODD18
				$rowdata[7],	//[21] => VAT 18% Sales Goods
			];
			
			$rows[] = $data;
		}
		
		usort($rows, function($a, $b) {
			if ($a[0] === $b[0]){
				if ($a[1] === $b[1]){
					if ($a[3] === $b[3]){
						return (strtotime($a[6]) < strtotime($b[6]));
					}else return strcmp($a[3], $b[3]);
				}else return strcmp($a[1], $b[1]);
			}else return strcmp($a[0], $b[0]);
		});
		
		$header = [
			"Sunat Status",
			"Document Type",
			"Invoice Number (P)",
			"Invoice Number (G)",
			"Voucher No",
			"Header Id",
			"Registration",
			"Date",
			"Currency",
			"Amount",
			"Soles",
			"Net Amount",
			"VAT",
			"Total Amount",
			"Exchange Rate",
			"Customer No",
			"Customer",
			"Tax ID (RUC)",
			"Tax_Rate_Code",
			"Tax Rate Name",
		];
		
		return $this->my_func->generate_excel_report("tax_invoice_comparison_report.xlsx", null, $header, $rows);
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
			if ($this->upload->do_upload('file_p')){
				$data = $this->upload->data();
				$name_p = $data['orig_name'];
			}
			
			$config['file_name'] = 'tax_invoice_gerp';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_g')){
				$data = $this->upload->data();
				$name_g = $data['orig_name'];
			}
			
			if ($name_p and $name_g){
				$type = "success";
				$msg = "Invoice record comparison report has been created.";
				$url = $this->comparison($name_p, $name_g);
			}else $msg = "Select all files: Paperless and GERP Invoice reports.";
		}else{
			$msg = "Your session is finished.";
			$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
