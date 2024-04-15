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
			"main" => "ar/aging/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function data_process(){
		$start_time = microtime(true);
		
		$currencies = $this->gen_m->only("ar_aging", "currency");
		$ar_classes = $this->gen_m->only("ar_aging", "ar_class");
		$cus_nums = $this->gen_m->only("ar_aging", "cus_num");
		$ranges = [[-99999, 0], [1, 7], [8, 15], [16, 30], [31, 45], [46, 60], [61, 90], [91, 180], [181, 360], [361, 9999]];
		
		$rows = $w = [];
		foreach($currencies as $curr){
			$w["currency"] = $curr->currency;

			foreach($cus_nums as $cus_num){
				$w["cus_num"] = $cus_num->cus_num;
				$cus_name = $this->gen_m->unique("ar_aging", "cus_num", $cus_num->cus_num)->cus_h_name;

				$payterms = $this->gen_m->only("ar_aging", "payterm", ["currency" => $curr->currency, "cus_num" => $cus_num->cus_num]);
				foreach($payterms as $payterm){
					$w["payterm"] = $payterm->payterm;
					
					$arr_class = []; 
					$has_value = false;
					foreach($ar_classes as $ar_class){
						$w["ar_class"] = $ar_class->ar_class;
						
						$arr_class[$ar_class->ar_class] = [];
						foreach($ranges as $range){
							$w["aging_day >="] = $range[0];
							$w["aging_day <="] = $range[1];
							
							$balance = $this->gen_m->sum("ar_aging", "balance", $w)->balance; if (!$balance) $balance = 0;
							$arr_class[$ar_class->ar_class][] = $balance/1000;
							if ($balance) $has_value = true;
						}
						
					}
					
					if ($has_value){
						$row = [$curr->currency, $cus_num->cus_num, $cus_name, $payterm->payterm];
						
						$row[] = " ";
						$aux = $arr_class["Invoice"];
						foreach($aux as $a) $row[] = $a;
						
						$row[] = " ";
						$aux = $arr_class["Credit Memo"];
						foreach($aux as $a) $row[] = $a;
						
						$row[] = " ";
						$aux = $arr_class["Chargeback"];
						foreach($aux as $a) $row[] = $a;
						
						$rows[] = $row;
					}
				}
			}
		}
		
		/* row structure
			Row info --------		Invoice --------		Credit Memo --------		Chargeback --------
			0: currency				4: [space char]			15: [space char]			26: [space char]
			1: cus_num				5: current				16: current					27: current
			2: cus_name				6: 1~7 days				17: 1~7 days				28: 1~7 days
			3: payterm				7: 8~15 days			18: 8~15 days				29: 8~15 days
									8: 16~30 days			19: 16~30 days				30: 16~30 days
									9: 31~45 days			20: 31~45 days				31: 31~45 days
									10: 46~60 days			21: 46~60 days				32: 46~60 days
									11: 61~90 days			22: 61~90 days				33: 61~90 days
									12: 91~180 days			23: 91~180 days				34: 91~180 days
									13: 181~360 days		24: 181~360 days			35: 181~360 days
									14: 361+ days			25: 361+ days				36: 361+ days */
		
		//sort by current invoice amount
		usort($rows, function($a, $b) {
			if (!strcmp($a[0], $b[0])) return ($a[5] < $b[5]);
			else return strcmp($a[0], $b[0]);
		});
		
		$y_data = ["Invoice", "Credit Memo", "Chargeback"];
		$x_data = ["Current", "1~7 Days", "8~15 Days", "16~30 Days", "31~45 Days", "46~60 Days", "61+ Days"];
		$values = [];
		
		foreach($y_data as $yd){
			foreach($x_data as $xd){
				$values[$yd][$xd] = 0;
			}
		}
		
		$values_pen = $values_usd = $values;
		foreach($rows as $r){
			switch($r[0]){
				case "PEN": 
					$i = 4;
					foreach($y_data as $yd){
						$i++; //pass space char
						$values_pen[$yd]["Current"] += $r[$i]; $i++;
						$values_pen[$yd]["1~7 Days"] += $r[$i]; $i++;
						$values_pen[$yd]["8~15 Days"] += $r[$i]; $i++;
						$values_pen[$yd]["16~30 Days"] += $r[$i]; $i++;
						$values_pen[$yd]["31~45 Days"] += $r[$i]; $i++;
						$values_pen[$yd]["46~60 Days"] += $r[$i]; $i++;
						$values_pen[$yd]["61+ Days"] += $r[$i] + $r[$i+1] + $r[$i+2] + $r[$i+3]; $i = $i + 4;
					}
					break;
				case "USD": 
					$i = 4;
					foreach($y_data as $yd){
						$i++; //pass space char
						$values_usd[$yd]["Current"] += $r[$i]; $i++;
						$values_usd[$yd]["1~7 Days"] += $r[$i]; $i++;
						$values_usd[$yd]["8~15 Days"] += $r[$i]; $i++;
						$values_usd[$yd]["16~30 Days"] += $r[$i]; $i++;
						$values_usd[$yd]["31~45 Days"] += $r[$i]; $i++;
						$values_usd[$yd]["46~60 Days"] += $r[$i]; $i++;
						$values_usd[$yd]["61+ Days"] += $r[$i] + $r[$i+1] + $r[$i+2] + $r[$i+3]; $i = $i + 4;
					}
					break;
			}
		}
		
		foreach($values_pen as $i => $lvl_1) foreach($lvl_1 as $j => $lvl_2) $values_pen[$i][$j] = number_format(abs($lvl_2), 2);
		foreach($values_usd as $i => $lvl_1) foreach($lvl_1 as $j => $lvl_2) $values_usd[$i][$j] = number_format(abs($lvl_2), 2);
		
		//need to make chart based on $rows
		$data = [
			"pen" => $values_pen,
			"usd" => $values_usd,
		];
		
		$header = [
			"Currency", "Customer Header Number", "Customer Header Name", "Payterm",
			"[Invoice]", "Current", "1 ~ 7 Days", "8 ~ 15 Days", "16 ~ 30 Days", "31 ~ 45 Days", "46 ~ 60 Days", "61 ~ 90 Days", "91 ~ 180 Days", "181 ~ 360 Days", "361+ Days",
			"[Credit Memo]", "Current", "1 ~ 7 Days", "8 ~ 15 Days", "16 ~ 30 Days", "31 ~ 45 Days", "46 ~ 60 Days", "61 ~ 90 Days", "91 ~ 180 Days", "181 ~ 360 Days", "361+ Days",
			"[Chargeback]", "Current", "1 ~ 7 Days", "8 ~ 15 Days", "16 ~ 30 Days", "31 ~ 45 Days", "46 ~ 60 Days", "61 ~ 90 Days", "91 ~ 180 Days", "181 ~ 360 Days", "361+ Days",
		];
		
		$result = [
			"url" => $this->my_func->generate_excel_report("ar_aging_report_converted.xlsx", null, $header, $rows),
			"data" => $data,
			"runtime" => number_Format(microtime(true) - $start_time, 2),
		];
		
		return $result;
	}
	
	private function conversion($sheet){
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
		if ($this->gen_m->insert_m("ar_aging", $data)) $result = $this->data_process();
		
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
				$data = $this->conversion($sheet);
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
