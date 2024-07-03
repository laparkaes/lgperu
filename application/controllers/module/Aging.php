<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Aging extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	
	public function index(){
		$data = [
			"main" => "module/aging/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function data_process($er, $to_currency){
		$start_time = microtime(true);
		
		$ar_classes = ["Invoice", "Credit Memo", "Chargeback"];
		$cus_nums = $this->gen_m->only("ar_aging", "cus_num");
		$ranges = [[-99999, 0], [1, 7], [8, 15], [16, 30], [31, 45], [46, 60], [61, 90], [91, 180], [181, 360], [361, 9999]];
		
		//$values_pen = $values_usd = [];
		
		$rows = $w = [];
		foreach($cus_nums as $cus_num){
			$cus = $this->gen_m->unique("ar_aging", "cus_num", $cus_num->cus_num);
			$row = [$cus->cus_num, $cus->cus_h_name];
			
			$w["cus_num"] = $cus->cus_num;
			foreach($ar_classes as $ar_class){
				$w["ar_class"] = $ar_class;
				
				$row[] = " "; //[2] Invoice, [13] Credit Note, [24] Chargeback
				foreach($ranges as $range){
					$w["aging_day >="] = $range[0];
					$w["aging_day <="] = $range[1];
					
					$w["currency"] = "USD";
					$usd = $this->gen_m->sum("ar_aging", "balance", $w)->balance; 
					$usd = $usd ? $usd/1000 : 0;
					
					$w["currency"] = "PEN";
					$pen = $this->gen_m->sum("ar_aging", "balance", $w)->balance; 
					$pen = $pen ? $pen/1000 : 0;
					
					$row[] = $to_currency === "usd" ? $usd + ($pen / $er) : ($usd * $er) + $pen;
				}
			}	
			
			$rows[] = $row;
		}
		
		usort($rows, function($a, $b) {
			if ($a[3] != $b[3]) return ($a[3] < $b[3]);
			else return ($a[4] < $b[4]);
		});
		
		foreach($rows as $i => $row){
			foreach($row as $j => $r) $rows[$i][$j] = is_numeric($r) ? number_format($r, 2) : $r;
		}
		
		$header = [
			"Customer Header Number", "Customer Header Name",
			"[Invoice]", "Current", "1 ~ 7 Days", "8 ~ 15 Days", "16 ~ 30 Days", "31 ~ 45 Days", "46 ~ 60 Days", "61 ~ 90 Days", "91 ~ 180 Days", "181 ~ 360 Days", "361+ Days",
			"[Credit Memo]", "Current", "1 ~ 7 Days", "8 ~ 15 Days", "16 ~ 30 Days", "31 ~ 45 Days", "46 ~ 60 Days", "61 ~ 90 Days", "91 ~ 180 Days", "181 ~ 360 Days", "361+ Days",
			"[Chargeback]", "Current", "1 ~ 7 Days", "8 ~ 15 Days", "16 ~ 30 Days", "31 ~ 45 Days", "46 ~ 60 Days", "61 ~ 90 Days", "91 ~ 180 Days", "181 ~ 360 Days", "361+ Days",
		];
		
		$result = [
			"url" => $this->my_func->generate_excel_report("ar_aging_report_converted.xlsx", null, $header, $rows),
			"rows" => $rows,
			"runtime" => number_Format(microtime(true) - $start_time, 2),
		];
		
		return $result;
	}
	
	public function test(){
		$result = $this->data_process(3.8, "pen");
		$rows = $result["rows"];
		foreach($rows as $row){
			print_r($row);
			echo "<br/><br/>";
		}
		
		echo $result["runtime"];
	}
	
	private function conversion($sheet, $er = 3.8, $to_currency = "usd"){
		$this->gen_m->truncate("ar_aging");
		
		$max_row = $sheet->getHighestRow();
		
		$data = [];
		for ($row = 2; $row <= $max_row; $row++){
			$cus_num = trim($sheet->getCell('A'.$row)->getValue());
			if ($cus_num){
				$data[] = [
					"cus_num" => $cus_num,
					"cus_h_name" => trim($sheet->getCell('B'.$row)->getValue()),
					"ar_class" => trim($sheet->getCell('G'.$row)->getValue()),
					"payterm" => trim($sheet->getCell('L'.$row)->getValue()),
					"currency" => trim($sheet->getCell('N'.$row)->getValue()),
					"balance" => trim($sheet->getCell('O'.$row)->getValue()),
					"aging_day" => trim($sheet->getCell('R'.$row)->getValue()),
				];				
			}
		}
		
		$result = [];
		if ($this->gen_m->insert_m("ar_aging", $data)) $result = $this->data_process($er, $to_currency);
		
		return $result;
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
			'file_name'		=> 'ar_aging_report',
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
				$er = $this->input->post("er"); if (!$er) $er = 3.8;
				$curr = $this->input->post("curr"); if (!$curr) $curr = "usd";
				
				$data = $this->conversion($sheet, $er, $curr);
				if ($data["url"]){
					$type = "success";
					$msg = "Report conversion is done. (".$data["runtime"]. "sec)";	
				}else $msg = "No data to process.";
			}else $msg = "Wrong data file.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "data" => $data]);
	}
}
