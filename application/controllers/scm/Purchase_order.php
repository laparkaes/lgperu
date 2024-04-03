<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//use PhpOffice\PhpSpreadsheet\IOFactory;

class Purchase_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->color_rgb = [
			"green" => "198754",
			"red" => "dc3545",
		];
	}
	
	private function hiraoka_original($rows_input){
		$rows = [];
		
		$customer = trim($rows_input[4]);
		$num_order = trim(explode(" ", $rows_input[5])[4]);
		$address = trim($rows_input[16]).", ".trim($rows_input[17]);
		
		foreach($rows_input as $r){
			$aux = explode(" ", $r);
			$aux = array_values(array_filter($aux));
			
			
			if (count($aux) > 6) if (is_numeric($aux[0])){
				//print_r($r); echo "<br/>";
				//print_r($aux); echo "<br/>";
				//foreach($aux as $a) var_dump($a); echo "<br/>";
				
				//get last position of number and extract total amount
				$aux_text = trim($aux[5]);
				preg_match('/[a-z]+/i', $aux_text, $matches, PREG_OFFSET_CAPTURE);
				$total = substr($aux_text, 0, $matches[0][1]);
				$aux[5] = str_replace($total, "", $aux_text);
				
				$row = [];
				$row[] = $customer;//customer
				$row[] = $address;//address
				$row[] = $num_order;//num order
				$row[] = trim($aux[0]); unset($aux[0]);//num
				$row[] = trim($aux[1]); unset($aux[1]);//code
				$row[] = trim($aux[2]); unset($aux[2]);//unit
				$row[] = trim($aux[3]); unset($aux[3]);//qty
				$row[] = trim($aux[4]); unset($aux[4]);//unit Price
				$row[] = $total;//total
				
				//clean aux => unset Pre-Distribucion
				foreach($aux as $a_i => $a) if (is_numeric($a)) unset($aux[$a_i]);
				
				//merge aux with " " to make product desription string
				$row[] = implode(" ", $aux);//desription
				
				//print_r($aux); echo "<br/>";
				print_r($row); echo "<br/>";
				//echo "<br/>";
				
				$rows[] = $row;
			}
		}
		echo "<br/><br/>";
		
		
		return $rows;
	}
	
	private function pdf_to_excel($filename, $logic_type = "hiraoka_original"){
		echo $filename." ========================<br/>";
		$rows = [];
		
		$this->load->library('my_pdf');
		$contents = $this->my_pdf->to_text("./test_files/scm/".$filename.".pdf");
		foreach($contents as $content){
			$page = $content["page"];
			$text = $content["text"];
			
			$lines = explode("\n", $text);
			$lines = array_values(array_filter($lines));
			foreach($lines as $line) $line = trim($line);
			
			$rows = array_merge($rows, $lines);
		}
		
		switch($logic_type){
			case "hiraoka_original": 
				$rows = $this->hiraoka_original($rows);
				break;
			default:
				echo "No type selected.";
		}
		
		if ($rows){
			$header = [
				"Customer",
				"Address",
				"#Order",
				"Num",
				"Code",
				"Unit",
				"Qty",
				"U/Price",
				"Total",
				"Description",
			];
			
			$url = $this->my_func->generate_excel_report($filename.".xlsx", "Purchase order items PDF to Excel", $header, $rows);
		}
	}
	
	public function pdf_to_excel_($filename){
		echo $filename." ========================<br/>";
		$products = [];
		
		$this->load->library('my_pdf');
		$contents = $this->my_pdf->to_text("./test_files/scm/".$filename.".pdf");
		foreach($contents as $content){
			$page = $content["page"];
			$text = $content["text"];
			
			echo $page." -----------------<br/>";
			
			$first_item_pos = stripos($text, "\n1 ");
			if ($first_item_pos){
				$aux = substr($text, stripos($text, "\n1 "), strlen($text));//put first item in start of string
				$aux = explode("\n", $aux);
				foreach($aux as $i => $line){
					if ($line){
						$line = array_values(array_filter(explode(" ", $line)));
						if (count($line) > 7){
							print_r($line); echo "<br/>";
							if (array_key_exists(0, $line) and ($i == $line[0])){
								
								$item_product = [];
								$item_product[] = trim($line[0]); unset($line[0]);
								$item_product[] = trim($line[1]); unset($line[1]);
								$item_product[] = trim($line[2]); unset($line[2]);
								$item_product[] = trim($line[3]); unset($line[3]);
								$item_product[] = trim($line[5]); unset($line[5]);
								
								$line[6] = trim($line[6]);
								
								//get last position of number and extract total amount
								preg_match('/[a-z]+/i', $line[6], $matches, PREG_OFFSET_CAPTURE);
								$total = substr($line[6], 0, $matches[0][1]);
								$item_product[] = $total;
								
								//remove total and join to make product
								$line[6] = str_replace($total, "", $line[6]);
								$item_product[] = implode(" ", $line);
								
								
								//print_r($item_product); echo "<br/>";
								
								$products[] = $item_product;
							}
						}
					}
				}
			}
			
			echo "<br/><br/>";
		}
		
		$header = [
			"Num",
			"Code",
			"Unit",
			"Qty",
			"U/Price",
			"Total",
			"Product",
		];
		
		//$url = $this->my_func->generate_excel_report($filename.".xlsx", "Purchase order items PDF to Excel", $header, $products); echo $url."<br/>";
	}
	
	public function index(){
		
		for($i = 0; $i < 24; $i++) $this->pdf_to_excel("test_hiraoka".$i);
		
		$data = [
			"main" => "sa/sell_inout/index",
		];
		
		//$this->load->view('layout', $data);
	}
}
