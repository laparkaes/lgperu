<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Sell_out extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			//"purchase_order_temps" => $this->gen_m->all("product", [["category_id", "asc"], ["model", "asc"]]),
			"main" => "module/sell_out/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload_data(){
		ini_set("memory_limit","1024M");
		set_time_limit(0);
			
		$type = "error"; $url = ""; $msg = ""; $data = [];
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'som_sell_out',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('datafile')){
			$spreadsheet = IOFactory::load("./upload/".$this->upload->data('file_name'));
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
				trim($sheet->getCell('N1')->getValue()),
				trim($sheet->getCell('O1')->getValue()),
				trim($sheet->getCell('P1')->getValue()),
				trim($sheet->getCell('Q1')->getValue()),
				trim($sheet->getCell('R1')->getValue()),
				trim($sheet->getCell('S1')->getValue()),
				trim($sheet->getCell('T1')->getValue()),
				trim($sheet->getCell('U1')->getValue()),
			];
			
			//determinate file type
			$f_type = "";
			
			if (
				($h[0] === "Customer Header Number") and 
				($h[1] === "Customer Header Name") and 
				($h[2] === "Customer Code") and 
				($h[3] === "Customer Name") and 
				($h[4] === "Collector NO") and 
				($h[5] === "Salesperson Name") and 
				($h[6] === "AR Class Name") and 
				($h[7] === "Trx Number") and 
				($h[8] === "Invoice NO") and 
				($h[9] === "Aging Bucket NO") and 
				($h[10] === "Aging Bucket Name") and 
				($h[11] === "AR Payment Terms Desc") and 
				($h[12] === "AR Type Name") and 
				($h[13] === "Transaction Currency Code") and 
				($h[14] === "AR Balance") and 
				($h[15] === "Due YYYYMMDD") and 
				($h[16] === "Reference NO") and 
				($h[17] === "Aging Day") and 
				($h[18] === "Additional Aging Bucket") and 
				($h[19] === "AR Balance(USD)") and 
				($h[20] === "AR Balance(Book)")
			){
				$data = $this->process($sheet);
				if ($data["url"]){
					$type = "success";
					$msg = "All data has been processed. (".$data["runtime"]. "sec)";	
				}else $msg = "No data to process.";
			}else $msg = "Wrong data file.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "data" => $data]);
	}
}
