<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_sales_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$o = [["create_date", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		$data = [
			"sales_orders"	=> $this->gen_m->filter("lgepr_sales_order", false, null, null, null, $o),
			"main" 			=> "data_upload/lgepr_sales_order/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function single_update_data(){
		$this->update_model_category();
		$this->update_dash_div_cat();
	}
	
	private function update_dash_div_cat(){
		$dash_mapping = [
			"REF" 	=> ["dash_company" => "HS"	, "dash_division" => "REF"],
			"CVT" 	=> ["dash_company" => "HS"	, "dash_division" => "Cooking"],
			"CDT" 	=> ["dash_company" => "HS"	, "dash_division" => "Dishwasher"],
			"W/M" 	=> ["dash_company" => "HS"	, "dash_division" => "W/M"],
			
			"LTV" 	=> ["dash_company" => "MS"	, "dash_division" => "LTV"],
			"CAV" 	=> ["dash_company" => "MS"	, "dash_division" => "Audio"],
			"MNT" 	=> ["dash_company" => "MS"	, "dash_division" => "MNT"],
			"DS" 	=> ["dash_company" => "MS"	, "dash_division" => "DS"],
			"SGN" 	=> ["dash_company" => "MS"	, "dash_division" => "MNT Signage"],
			"CTV" 	=> ["dash_company" => "MS"	, "dash_division" => "Commercial TV"],
			"PC" 	=> ["dash_company" => "MS"	, "dash_division" => "PC"],
			
			"RAC" 	=> ["dash_company" => "ES"	, "dash_division" => "RAC"],
			"SAC" 	=> ["dash_company" => "ES"	, "dash_division" => "SAC"],
			"A/C" 	=> ["dash_company" => "ES"	, "dash_division" => "Chiller"],
			
			"MC" 	=> ["dash_company" => "MC"	, "dash_division" => "MC"],
		];
		
		foreach($dash_mapping as $key => $item) $this->gen_m->update("lgepr_sales_order", ["model_category" => $key], $item);
		
		$this->gen_m->update("lgepr_sales_order", ["product_level2_name" => "SRAC"], $dash_mapping["RAC"]);
	}
	
	private function update_model_category(){
		//set mapping array
		$w = ["model_category !=" => ""];
		$s = ["model_category", "product_level4"];
		$closed_orders = $this->gen_m->filter_select("lgepr_closed_order", false, $s, $w, null, null, [["product_level4", "desc"]], null, null, "product_level4");
		
		$mapping = ["MC" => "MC"];
		foreach($closed_orders as $item){
			if ($item->model_category){
				$index_6 = substr($item->product_level4, 0, 6);
				$index_4 = substr($item->product_level4, 0, 4);
				$index_2 = substr($item->product_level4, 0, 2);
				
				if (array_key_exists($index_6, $mapping)){
					if (!$mapping[$index_6]) $mapping[$index_6] = $item->model_category;
				}else $mapping[$index_6] = $item->model_category;	
				
				if (array_key_exists($index_4, $mapping)){
					if (!$mapping[$index_4]) $mapping[$index_4] = $item->model_category;
				}else $mapping[$index_4] = $item->model_category;
				
				if (array_key_exists($index_2, $mapping)){
					if (!$mapping[$index_2]) $mapping[$index_2] = $item->model_category;
				}else $mapping[$index_2] = $item->model_category;
			}
		}
		
		//update blank model categories
		$w = ["model_category" => ""];
		$s = ["model_category", "product_level4_code"];
		$sales_orders = $this->gen_m->filter_select("lgepr_sales_order", false, $s, $w, null, null, [["product_level4_code", "desc"]], null, null, "product_level4_code");
		
		foreach($sales_orders as $item){
			$mc = "";
			
			$sub6 = substr($item->product_level4_code, 0, 6);
			$sub4 = substr($item->product_level4_code, 0, 4);
			$sub2 = substr($item->product_level4_code, 0, 2);
			
			if (array_key_exists($sub6, $mapping)) $mc = $mapping[$sub6];
			elseif (array_key_exists($sub4, $mapping)) $mc = $mapping[$sub4]; 
			elseif (array_key_exists($sub2, $mapping)) $mc = $mapping[$sub2];
			
			//echo $sub6." ".$sub4." >>> ".$mc."<br/>"; print_r($item); echo "<br/><br/>";
			
			if ($mc) $this->gen_m->update("lgepr_sales_order", ["product_level4_code" => $item->product_level4_code], ["model_category" => $mc]);
		}
		
		$this->update_dash_div_cat();
	}
	
	public function process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/lgepr_sales_order.xls");
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
		$h_gerp = ["Bill To Name", "Ship To Name", "Model", "Order No.", "Line No.", "Order Type", "Line Status", "Hold Flag", "Ready To Pick", "Pick Released", "Instock Flag", "Ordered Qty", "Unit Selling Price", ];
		
		//header validation
		$is_gerp = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_gerp[$i]) $is_gerp = false;
		
		if ($is_gerp){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			$rows = $order_lines = [];
			$records = 0;
			
			$this->gen_m->truncate("lgepr_sales_order");
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					'bill_to' 				=> trim($sheet->getCell('AI'.$i)->getValue()),
					'bill_to_name' 			=> trim($sheet->getCell('A'.$i)->getValue()),
					'ship_to' 				=> trim($sheet->getCell('AK'.$i)->getValue()),
					'ship_to_name' 			=> trim($sheet->getCell('B'.$i)->getValue()),
					'order_type' 			=> trim($sheet->getCell('F'.$i)->getValue()),
					'order_no' 				=> trim($sheet->getCell('D'.$i)->getValue()),
					'line_no' 				=> trim($sheet->getCell('E'.$i)->getValue()),
					'line_status' 			=> trim($sheet->getCell('G'.$i)->getValue()),
					'order_status' 			=> trim($sheet->getCell('BB'.$i)->getValue()),
					'order_category'		=> trim($sheet->getCell('BC'.$i)->getValue()),
					'model' 				=> trim($sheet->getCell('C'.$i)->getValue()),
					'ordered_qty' 			=> trim($sheet->getCell('L'.$i)->getValue()),
					'currency' 				=> trim($sheet->getCell('U'.$i)->getValue()),
					'unit_selling_price'	=> trim($sheet->getCell('M'.$i)->getValue()),
					'sales_amount' 			=> trim($sheet->getCell('N'.$i)->getValue()),
					'tax_amount' 			=> trim($sheet->getCell('O'.$i)->getValue()),
					'charge_amount'			=> trim($sheet->getCell('P'.$i)->getValue()),
					'line_total' 			=> trim($sheet->getCell('Q'.$i)->getValue()),
					'create_date' 			=> trim($sheet->getCell('DK'.$i)->getValue()),
					'booked_date' 			=> trim($sheet->getCell('Y'.$i)->getValue()),
					'req_arrival_date_to'	=> trim($sheet->getCell('AC'.$i)->getValue()),
					'shipment_date'			=> trim($sheet->getCell('AE'.$i)->getValue()),
					'close_date' 			=> trim($sheet->getCell('AF'.$i)->getValue()),
					'receiver_city'			=> trim($sheet->getCell('BT'.$i)->getValue()),
					'item_type_desctiption' => trim($sheet->getCell('CG'.$i)->getValue()),
					'item_division' 		=> trim($sheet->getCell('BZ'.$i)->getValue()),
					'model_category' 		=> trim($sheet->getCell('CF'.$i)->getValue()),
					'product_level1_name'	=> trim($sheet->getCell('CA'.$i)->getValue()),
					'product_level2_name' 	=> trim($sheet->getCell('CB'.$i)->getValue()),
					'product_level3_name' 	=> trim($sheet->getCell('CC'.$i)->getValue()),
					'product_level4_name' 	=> trim($sheet->getCell('CD'.$i)->getValue()),
					'product_level4_code' 	=> trim($sheet->getCell('CE'.$i)->getValue()),
					'customer_department'	=> trim($sheet->getCell('AJ'.$i)->getValue()),
					'inventory_org' 		=> trim($sheet->getCell('AW'.$i)->getValue()),
					'sub_inventory' 		=> trim($sheet->getCell('AX'.$i)->getValue()),
				];
				//apply trim
				$row["order_no"] = trim($row["order_no"]);
				$row["line_no"] = trim($row["line_no"]);
				$row["order_line"] = $row["order_no"]."_".$row["line_no"];
				
				//float_convert
				$row["unit_selling_price"] = str_replace(",", "", $row["unit_selling_price"]);
				$row["sales_amount"] = str_replace(",", "", $row["sales_amount"]);
				$row["tax_amount"] = str_replace(",", "", $row["tax_amount"]);
				$row["charge_amount"] = str_replace(",", "", $row["charge_amount"]);
				$row["line_total"] = str_replace(",", "", $row["line_total"]);
				
				//date convert: 24/06/2021 > 2021-10-28
				$row["booked_date"] = $this->my_func->date_convert_4($row["booked_date"]);
				$row["req_arrival_date_to"] = $this->my_func->date_convert_4($row["req_arrival_date_to"]);
				$row["shipment_date"] = $this->my_func->date_convert_4($row["shipment_date"]);
				$row["close_date"] = $this->my_func->date_convert_4($row["close_date"]);
				$row["create_date"] = $this->my_func->date_convert_4($row["create_date"]);
				
				//date format changed to number from 2025-02-06
				if (!$row["booked_date"]) $row["booked_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('Y'.$i)->getFormattedValue())));
				if (!$row["req_arrival_date_to"]) $row["req_arrival_date_to"] = date("Y-m-d", strtotime(trim($sheet->getCell('AC'.$i)->getFormattedValue())));
				if (!$row["shipment_date"]) $row["shipment_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('AE'.$i)->getFormattedValue())));
				if (!$row["close_date"]) $row["close_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('AF'.$i)->getFormattedValue())));
				if (!$row["create_date"]) $row["create_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('DK'.$i)->getFormattedValue())));
				
				if ($row["booked_date"] === "1969-12-31") $row["booked_date"] = null;
				if ($row["req_arrival_date_to"] === "1969-12-31") $row["req_arrival_date_to"] = null;
				if ($row["shipment_date"] === "1969-12-31") $row["shipment_date"] = null;
				if ($row["close_date"] === "1969-12-31") $row["close_date"] = null;
				if ($row["create_date"] === "1969-12-31") $row["create_date"] = null;
				
				//print_r($row); echo"<br/><br/>";
				
				//usd calculation
				$er = $row['currency'] === "USD" ? 1 : $this->gen_m->filter("exchange_rate", false, ["currency" => $row['currency'], "date <=" => $row["create_date"]], null, null, [["date", "desc"]], 1)[0]->sell;
				$row["sales_amount_usd"] =  round($row["sales_amount"] / $er, 2);
				
				if (count($rows) > 5000){
					$records += $this->gen_m->insert_m("lgepr_sales_order", $rows);
					$rows = [];
				}
				
				$rows[] = $row;
				$order_lines[] = $row["order_line"];
			}
			
			if ($rows) $records += $this->gen_m->insert_m("lgepr_sales_order", $rows);
			
			//remove closed orders in sales order table
			$order_lines_split = array_chunk($order_lines, 500);
			foreach($order_lines_split as $items){
				$closed_orders = $this->gen_m->filter_select("lgepr_closed_order", false, ["order_line"], null, null, [["field" => "order_line", "values" => $items]]);
				if ($closed_orders){
					$aux = [];
					foreach($closed_orders as $item) $aux[] = $item->order_line;
					
					$this->gen_m->delete_in("lgepr_sales_order", "order_line", $aux);
				}
			}
			
			$this->update_model_category();
			
			$msg = number_format($records)." record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'lgepr_sales_order.xls',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
				/*
				$msg = $this->process();//delete & insert
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
				*/
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
