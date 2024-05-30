<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Obs extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			//"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "module/obs/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function test(){
		// 읽어들일 CSV 파일 경로
		$file = './test_files/obs_backdata/master_export_rodrigo.oyarce_order_rodrigo.oyarce_24_05_29_22_35_07_1717022107.3641.csv';
		//$file = "./test_files/obs_backdata/license.txt";

		// 파일 내용을 텍스트로 읽기
		$content = file_get_contents($file);

		if ($content !== FALSE) {
			//echo json_encode($content); echo "<br/><br/><br/>";
			
			$content = str_replace('\"', '', $content);
			$content = str_replace('S, AHORRA', 'S AHORRA', $content);
			$content = str_replace('Qty Ordered"', 'Qty Ordered".0000', $content);
			$rows = explode(".0000\n", $content);
			
			$header = [];
			$data = [];
			
			foreach($rows as $index => $row){
				//print_r($row); echo "<br/><br/>";
				
				$row = str_replace('\n', ' ', $row);
				$row = str_replace(', \n', ' ', $row);
				$row = str_replace(',\n', ' ', $row);
				
				$row = strtr($row, ["\n" => " ", ", \n" => "; ", ",\n" => "; "]);
				
				//echo json_encode($row); echo "<br/><br/>";
				
				$row = explode(",", $row);
				
				//print_r($row); echo "<br/><br/>";
				
				if (count($row) > 30){
					if ($row[3] >= 1000){
						$row[10] = $row[10].$row[11];
						unset($row[11]);
					}
					
					$row = array_values($row);
					
					$row1 = array_slice($row, 0, 32);
					$row2 = array_slice($row, -14, 14);
					
					//unset items
					$remove = [[0, 31], [count($row)-14, count($row)-1]];
					foreach($remove as $rem) for($i = $rem[0]; $i <= $rem[1]; $i++) unset($row[$i]);
					
					$row = array_merge($row1, [implode(", ", $row)], $row2);
					
					foreach($row as $i => $r){
						$row[$i] = str_replace('"', '', $r);
						if ($row[$i] === "N/A") $row[$i] =null;
					}
					
					print_r($row); echo "<br/><br/>====================<br/><br/>";
				}
			}
		} else {
			echo "파일을 읽을 수 없습니다.";
		}
	}
}
