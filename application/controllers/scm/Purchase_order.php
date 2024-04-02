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
	
	public function pdf_to_excel($filename){
		
		$products = [];
		
		$this->load->library('my_pdf');
		$contents = $this->my_pdf->to_text("./test_files/scm/".$filename.".pdf");
		foreach($contents as $content){
			$page = $content["page"];
			$text = $content["text"];
			
			$first_item_pos = stripos($text, "\n1 ");
			if ($first_item_pos){
				$aux = substr($text, stripos($text, "\n1 "), strlen($text));//put first item in start of string
				$aux = explode("\n", $aux);
				foreach($aux as $i => $line){
					if ($line){
						$line = explode(" ", $line);
						if ($i == $line[0]){
							unset($line[4]);//blank
							
							$item_product = [];
							$item_product[] = $line[0]; unset($line[0]);
							$item_product[] = $line[1]; unset($line[1]);
							$item_product[] = $line[2]; unset($line[2]);
							$item_product[] = $line[3]; unset($line[3]);
							$item_product[] = $line[5]; unset($line[5]);
							
							//get last position of number and extract total amount
							preg_match('/[a-z]+/i', $line[6], $matches, PREG_OFFSET_CAPTURE);
							$total = substr($line[6], 0, $matches[0][1]);
							$item_product[] = $total;
							
							//remove total and join to make product
							$line[6] = str_replace($total, "", $line[6]);
							$item_product[] = implode(" ", $line);
							
							$products[] = $item_product;
						}
					}
				}
			}
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
		
		$url = $this->my_func->generate_excel_report($filename.".xlsx", "Purchase order items PDF to Excel", $header, $products);
		
		echo $url."<br/>";
	}
	
	public function index(){
		
		for($i = 0; $i < 24; $i++) $this->pdf_to_excel("test_hiraoka".$i);
		
		$data = [
			"main" => "sa/sell_inout/index",
		];
		
		//$this->load->view('layout', $data);
	}
}
