<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
		$sheet = IOFactory::load("./upload/".$file_p)->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();

		for ($row = 3; $row <= $max_row; $row++){//Paperless excel starts from row 3
			$rowdata = $this->my_func->arr_trim($sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			if (!array_key_exists($rowdata[1], $invoices)) 
				$invoices[$rowdata[1]] = [$rowdata[0], $rowdata[1], $rowdata[11]];
		}
		
		//GERP process
		$sheet = IOFactory::load("./upload/".$file_g)->getActiveSheet();
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

	public function test(){
		//Peperless process
		$sheet = IOFactory::load("./test_files/tax_e_invoice/paperless.xlsx")->getActiveSheet();
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
		$sheet = IOFactory::load("./test_files/tax_e_invoice/gerp.xlsx")->getActiveSheet();
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();

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
			
			print_r($rowdata); echo "<br/>";
			
			$inv = $sheet->getCell('E'.$row)->getValue()."-".$sheet->getCell('F'.$row)->getValue();
			if ($inv !== "-"){//invoice number
				if (array_key_exists($inv, $invoices)){
					print_r($invoices[$inv]); echo "<br/>";
				}
			}else{
				print_r($inv_blank); echo "<br/>";
			}
			
			
			echo "<br/>";
		}
	}
}
