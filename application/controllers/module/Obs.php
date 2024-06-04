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
			"sales_updated"	=> $this->gen_m->filter("obs_magento", false, null, null, null, [["updated", "desc"]], 1, 0)[0],
			"sales_first"	=> $this->gen_m->filter("obs_magento", false, null, null, null, [["local_time", "asc"]], 1, 0)[0],
			"sales_last" 	=> $this->gen_m->filter("obs_magento", false, null, null, null, [["local_time", "desc"]], 1, 0)[0],
			"main" 			=> "module/obs/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function process_magento(){
		set_time_limit(0);
		
		//load excel file
		$spreadsheet = IOFactory::load("./test_files/obs_backdata/master_export_rodrigo.oyarce_order_rodrigo.oyarce_24_05_31_17_22_07_1717176127.933.csv");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
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
		
		//magento report header
		$h_magento = ["ID", "Grand Total (Base)", "Grand Total (Purchased)", "Shipping Address", "Shipping and Handling", "Customer name", "SKU", "Level 1 Code", "Level 2 Code", "Level 3 Code", "Level 4 Code", "GERP Type", "GERP Order #"];
		
		//header validation
		$is_magento = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_magento[$i]) $is_magento = false;
		
		$result = [];
		
		if ($is_magento){
			$max_row = $sheet->getHighestRow();
			
			//result types
			$qty_insert = $qty_update = $qty_fail = 0;
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			//db fields
			$vars = ["magento_id", "grand_total_base", "grand_total_purchased", "shipping_address", "shipping_and_handling", "customer_name", "sku", "level_1_code", "level_2_code", "level_3_code", "level_4_code", "gerp_type", "gerp_order_no", "warehouse_code", "sku_price", "local_time", "company_name_through_vipkey", "vipkey", "pre_order", "error_code", "price_source", "coupon_code", "coupon_rule", "discount_amount", "devices", "knout_status", "status", "customer_group", "payment_method", "error_status", "opt_in_status", "purchase_date", "gerp_selling_price", "ip_address", "sale_channel", "is_export_order_to_gerp", "sku_without_prefix", "sku_without_prefix_and_suffix", "qty_ordered"];
			
			for($i = 2; $i < $max_row; $i++){
				$row = [];
				foreach($vars as $var_i => $var) $row[$var] = str_replace("N/A", null, $sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
				
				//unique gerp_order_no
				$row["gerp_order_no"] = explode("\n", $row["gerp_order_no"])[0];
				
				//line change char working
				$row["sku"] = str_replace(", \n", "**", $row["sku"]);
				$row["warehouse_code"] = str_replace("\n", "**", $row["warehouse_code"]);
				$row["sku_price"] = str_replace("\n", "**", $row["sku_price"]);
				$row["sku_without_prefix"] = str_replace("\n", "**", $row["sku_without_prefix"]);
				$row["sku_without_prefix_and_suffix"] = str_replace("\n", "**", $row["sku_without_prefix_and_suffix"]);
				
				//comma working
				$row["gerp_selling_price"] = str_replace(",", "**", $row["gerp_selling_price"]);
				
				//address working
				$address_aux = explode(",", $row["shipping_address"]);
				$row["zipcode"] = $address_aux[count($address_aux)-1];
				$row["department"] = $address_aux[count($address_aux)-2];
				$row["province"] = $address_aux[count($address_aux)-3];
				
				//model category working
				$row["model_category"] = $row["level_1_code"] ? explode(" ", explode(",", $row["level_1_code"])[0])[1] : "OTHER";
				
				$magento = $this->gen_m->unique("obs_magento", "magento_id", $row["magento_id"], false);
				if ($magento){
					$row["updated"] = $now;
					
					if ($this->gen_m->update("obs_magento", ["obs_magento_id" => $magento->obs_magento_id], $row)) $qty_update++;
					else $qty_fail++;
				}else{
					$row["registered"] = $row["updated"] = $now;
					
					if ($this->gen_m->insert("obs_magento", $row)) $qty_insert++;
					else $qty_fail++;
				}
				
				//print_r($row); 
				//foreach($row as $key => $r) echo $key." >>>> ".json_encode($r)."<br/>";
				//echo "<br/>======================<br/><br/>";
			}
			
			if ($qty_insert > 0) $result[] = number_format($qty_insert)." inserted";
			if ($qty_update > 0) $result[] = number_format($qty_update)." updated";
			if ($qty_fail > 0) $result[] = number_format($qty_fail)." failed";
		}else $result[] = "Wrong file.";
		
		return "OBS magento report process result:<br/><br/>".implode(",", $result);
	}
	
	public function test(){
		
		$msg = $this->process_magento();
		
		echo $msg;
	}
	
	public function test1(){//no use
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
