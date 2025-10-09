<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Lgepr_sales_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		//$this->load->model('general_espr_model', 'gen_e');
	}
	
	public function index(){
		
		$o = [["req_arrival_date_to", "desc"], ["order_no", "asc"], ["line_no", "asc"]];
		
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
	
	public function test(){
		//$this->update_dash_div_cat("lgepr_order");
		
	}
	
	private function update_dash_div_cat($tablename = "lgepr_sales_order"){
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
			"LEDSGN" => ["dash_company" => "MS"	, "dash_division" => "LED Signage"],
			"CTV" 	=> ["dash_company" => "MS"	, "dash_division" => "Commercial TV"],
			"PC" 	=> ["dash_company" => "MS"	, "dash_division" => "PC"],
			"PRJ" 	=> ["dash_company" => "MS"	, "dash_division" => "MNT"],
			
			"RAC" 	=> ["dash_company" => "ES"	, "dash_division" => "RAC"],
			"SAC" 	=> ["dash_company" => "ES"	, "dash_division" => "SAC"],
			"A/C" 	=> ["dash_company" => "ES"	, "dash_division" => "Chiller"],
			
			"MC" 	=> ["dash_company" => "MC"	, "dash_division" => "MC"],
		];
		
		foreach($dash_mapping as $key => $item) $this->gen_m->update($tablename, ["model_category" => $key], $item);
		
		$this->gen_m->update($tablename, ["product_level2_name" => "SRAC"], $dash_mapping["RAC"]);
		$this->gen_m->update($tablename, ["product_level2_name" => "Commercial_LED Signage"], $dash_mapping["LEDSGN"]);
	}
	
	private function update_model_category($tablename = "lgepr_sales_order"){
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
		$sales_orders = $this->gen_m->filter_select($tablename, false, $s, $w, null, null, [["product_level4_code", "desc"]], null, null, "product_level4_code");
		
		foreach($sales_orders as $item){
			$mc = "";
			
			$sub6 = substr($item->product_level4_code, 0, 6);
			$sub4 = substr($item->product_level4_code, 0, 4);
			$sub2 = substr($item->product_level4_code, 0, 2);
			
			if (array_key_exists($sub6, $mapping)) $mc = $mapping[$sub6];
			elseif (array_key_exists($sub4, $mapping)) $mc = $mapping[$sub4]; 
			elseif (array_key_exists($sub2, $mapping)) $mc = $mapping[$sub2];
			
			//echo $sub6." ".$sub4." >>> ".$mc."<br/>"; print_r($item); echo "<br/><br/>";
			
			if ($mc) $this->gen_m->update($tablename, ["product_level4_code" => $item->product_level4_code], ["model_category" => $mc]);
		}
		
		$this->update_dash_div_cat($tablename);
	}
	
	public function process_snap(){//sales order shapshot
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
					'appointment_date'		=> trim($sheet->getCell('EA'.$i)->getValue()),
					'shipment_date'			=> trim($sheet->getCell('AE'.$i)->getValue()),
					//'close_date' 			=> trim($sheet->getCell('AF'.$i)->getValue()),
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
				
				//date convert: 28-OCT-21 > 2021-10-28
				$row["create_date"] = $this->my_func->date_convert_4($row["create_date"]);
				$row["booked_date"] = $this->my_func->date_convert_4($row["booked_date"]);
				$row["req_arrival_date_to"] = $this->my_func->date_convert_4($row["req_arrival_date_to"]);
				$row["appointment_date"] = $this->my_func->date_convert_4($row["appointment_date"]);
				$row["shipment_date"] = $this->my_func->date_convert_4($row["shipment_date"]);
				//$row["close_date"] = $this->my_func->date_convert_4($row["close_date"]);
				
				//date format changed to number from 2025-02-06
				if (!$row["create_date"]) $row["create_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('DK'.$i)->getFormattedValue())));
				if (!$row["booked_date"]) $row["booked_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('Y'.$i)->getFormattedValue())));
				if (!$row["req_arrival_date_to"]) $row["req_arrival_date_to"] = date("Y-m-d", strtotime(trim($sheet->getCell('AC'.$i)->getFormattedValue())));
				if (!$row["appointment_date"]) $row["appointment_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('EA'.$i)->getFormattedValue())));
				if (!$row["shipment_date"]) $row["shipment_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('AE'.$i)->getFormattedValue())));
				//if (!$row["close_date"]) $row["close_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('AF'.$i)->getFormattedValue())));
				
				if ($row["create_date"] === "1969-12-31") $row["create_date"] = null;
				if ($row["booked_date"] === "1969-12-31") $row["booked_date"] = null;
				if ($row["req_arrival_date_to"] === "1969-12-31") $row["req_arrival_date_to"] = null;
				if ($row["appointment_date"] === "1969-12-31") $row["appointment_date"] = null;
				if ($row["shipment_date"] === "1969-12-31") $row["shipment_date"] = null;
				//if ($row["close_date"] === "1969-12-31") $row["close_date"] = null;
				
				//print_r($row); echo"<br/><br/>";
				
				//usd calculation
				switch($row['currency']){
					case "BRL": $er = 3.25; break;
					case "USD": $er = 1; break;
					default:
						if ($row["booked_date"]){
							$rate = $this->gen_m->filter("exchange_rate", false, ["currency" => $row['currency'], "date <=" => $row["booked_date"]], null, null, [["date", "desc"]], 1);
							
							if ($rate) $er = $rate[0]->sell;
							else $er = 3.7;
						}else $er = 3.7;
						
						//$er = $this->gen_m->filter("exchange_rate", false, ["currency" => $row['currency'], "date <=" => $row["booked_date"]], null, null, [["date", "desc"]], 1)[0]->sell;
						break;
				}
				
				/*
				if ($row['currency'] === "BRL"){//forced if currency is not USD or PEN
					if ($row["booked_date"] === "2025-02-21") $er = 3.25;
				}else $er = $row['currency'] === "USD" ? 1 : $this->gen_m->filter("exchange_rate", false, ["currency" => $row['currency'], "date <=" => $row["create_date"]], null, null, [["date", "desc"]], 1)[0]->sell;
				*/
				
				$row["sales_amount_usd"] =  round($row["sales_amount"] / $er, 2);
				
				if (count($rows) > 5000){
					$records += $this->gen_m->insert_m("lgepr_sales_order", $rows);
					$rows = [];
				}
				//print_r($row); echo "<br/><br/>";
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
	
	public function process(){
		ini_set('memory_limit', '4G');
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
			trim($sheet->getCell('EL1')->getValue()),
		];
		
		//sales order header
		$h_gerp = ["Bill To Name", "Ship To Name", "Model", "Order No.", "Line No.", "RAD Unmeet Reason"];
		
		//header validation
		$is_gerp = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_gerp[$i]) $is_gerp = false;
		
		if ($is_gerp){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			$rows = $order_lines = [];
			$records = 0;
			$updated_at = date("Y-m-d H:i:s");
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					'bill_to' 				=> trim($sheet->getCell('AJ'.$i)->getValue()),
					'bill_to_name' 			=> trim($sheet->getCell('A'.$i)->getValue()),
					'ship_to' 				=> trim($sheet->getCell('AL'.$i)->getValue()),
					'ship_to_name' 			=> trim($sheet->getCell('B'.$i)->getValue()),
					'customer_name' 		=> trim($sheet->getCell('AI'.$i)->getValue()),
					'customer_po_no' 		=> trim($sheet->getCell('AQ'.$i)->getValue()),
					'customer_po_date' 		=> trim($sheet->getCell('AR'.$i)->getValue()),
					'order_type' 			=> trim($sheet->getCell('F'.$i)->getValue()),
					'order_no' 				=> trim($sheet->getCell('D'.$i)->getValue()),
					'line_no' 				=> trim($sheet->getCell('E'.$i)->getValue()),
					'line_status' 			=> trim($sheet->getCell('G'.$i)->getValue()),
					'so_status' 			=> trim($sheet->getCell('EW'.$i)->getValue()),
					'order_status' 			=> trim($sheet->getCell('BE'.$i)->getValue()),
					'order_category'		=> trim($sheet->getCell('BF'.$i)->getValue()),
					'model' 				=> trim($sheet->getCell('C'.$i)->getValue()),
					'ordered_qty' 			=> trim($sheet->getCell('L'.$i)->getValue()),
					'cbm' 					=> trim($sheet->getCell('CM'.$i)->getValue()),
					'currency' 				=> trim($sheet->getCell('U'.$i)->getValue()),
					'unit_selling_price'	=> trim($sheet->getCell('M'.$i)->getValue()),
					'sales_amount' 			=> trim($sheet->getCell('N'.$i)->getValue()),
					'tax_amount' 			=> trim($sheet->getCell('O'.$i)->getValue()),
					'charge_amount'			=> trim($sheet->getCell('P'.$i)->getValue()),
					'line_total' 			=> trim($sheet->getCell('Q'.$i)->getValue()),
					'create_date' 			=> trim($sheet->getCell('DO'.$i)->getValue()),
					'booked_date' 			=> trim($sheet->getCell('Y'.$i)->getValue()),
					'req_arrival_date_to'	=> trim($sheet->getCell('AD'.$i)->getValue()),
					'appointment_date'		=> trim($sheet->getCell('EH'.$i)->getValue()),
					'shipment_date'			=> trim($sheet->getCell('AF'.$i)->getValue()),
					'receiver_city'			=> trim($sheet->getCell('BX'.$i)->getValue()),
					'item_type_desctiption' => trim($sheet->getCell('CK'.$i)->getValue()),
					'item_division' 		=> trim($sheet->getCell('CD'.$i)->getValue()),
					'model_category' 		=> trim($sheet->getCell('CJ'.$i)->getValue()),
					'product_level1_name'	=> trim($sheet->getCell('CE'.$i)->getValue()),
					'product_level2_name' 	=> trim($sheet->getCell('CF'.$i)->getValue()),
					'product_level3_name' 	=> trim($sheet->getCell('CG'.$i)->getValue()),
					'product_level4_name' 	=> trim($sheet->getCell('CH'.$i)->getValue()),
					'product_level4_code' 	=> trim($sheet->getCell('CI'.$i)->getValue()),
					'customer_department'	=> trim($sheet->getCell('AK'.$i)->getValue()),
					'inventory_org' 		=> trim($sheet->getCell('AZ'.$i)->getValue()),
					'sub_inventory' 		=> trim($sheet->getCell('BA'.$i)->getValue()),
					'hold_flag' 			=> trim($sheet->getCell('H'.$i)->getValue()),
					'instock_flag' 			=> trim($sheet->getCell('K'.$i)->getValue()),
					'back_order_hold' 		=> trim($sheet->getCell('CQ'.$i)->getValue()),
					'credit_hold' 			=> trim($sheet->getCell('CR'.$i)->getValue()),
					'overdue_hold' 			=> trim($sheet->getCell('CS'.$i)->getValue()),
					'customer_hold' 		=> trim($sheet->getCell('CT'.$i)->getValue()),
					'payterm_term_hold' 	=> trim($sheet->getCell('CU'.$i)->getValue()),
					'fp_hold' 				=> trim($sheet->getCell('CV'.$i)->getValue()),
					'minimum_hold' 			=> trim($sheet->getCell('CW'.$i)->getValue()),
					'future_hold' 			=> trim($sheet->getCell('CX'.$i)->getValue()),
					'reserve_hold' 			=> trim($sheet->getCell('CY'.$i)->getValue()),
					'manual_hold' 			=> trim($sheet->getCell('CZ'.$i)->getValue()),
					'auto_pending_hold' 	=> trim($sheet->getCell('DA'.$i)->getValue()),
					'sa_hold' 				=> trim($sheet->getCell('DB'.$i)->getValue()),
					'form_hold' 			=> trim($sheet->getCell('DC'.$i)->getValue()),
					'bank_collateral_hold' 	=> trim($sheet->getCell('DD'.$i)->getValue()),
					'insurance_hold' 		=> trim($sheet->getCell('DE'.$i)->getValue()),
					'partial_flag' 			=> trim($sheet->getCell('DF'.$i)->getValue()),
					'load_hold_flag' 		=> trim($sheet->getCell('DG'.$i)->getValue()),
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
				
				//date convert: 28-OCT-2021 > 2021-10-28
				$row["appointment_date"] = $this->my_func->date_convert_5($row["appointment_date"]);
				
				//date convert: dd/mm/yyyy > yyyy-mm-dd
				$row["customer_po_date"] = $this->my_func->date_convert($row["customer_po_date"]);
				$row["create_date"] = $this->my_func->date_convert($row["create_date"]);
				$row["booked_date"] = $this->my_func->date_convert($row["booked_date"]);
				$row["req_arrival_date_to"] = $this->my_func->date_convert($row["req_arrival_date_to"]);
				$row["shipment_date"] = $this->my_func->date_convert($row["shipment_date"]);
				
				//usd calculation
				$er = 3.7;
				switch($row['currency']){
					case "BRL": $er = 3.25; break;
					case "USD": $er = 1; break;
					default:
						if ($row["booked_date"]){
							$rate = $this->gen_m->filter("exchange_rate", false, ["currency" => $row['currency'], "date <=" => $row["booked_date"]], null, null, [["date", "desc"]], 1);
							
							if ($rate){
								$er = $rate[0]->sell;
								if (!$er) $er = 3.7;
							}else $er = 3.7;
						}else $er = 3.7;
						
						//$er = $this->gen_m->filter("exchange_rate", false, ["currency" => $row['currency'], "date <=" => $row["booked_date"]], null, null, [["date", "desc"]], 1)[0]->sell;
						break;
				}
				
				$row["sales_amount_usd"] =  round($row["sales_amount"] / $er, 2);
				
				/*
				1. sales in sales order > update
				2. sales not in sales order > insert
				*/
				
				$row["updated_at"] = $updated_at;
				
				$w = ["order_line" => $row["order_line"]];
				
				if ($this->gen_m->filter("lgepr_sales_order", false, $w)) $this->gen_m->update("lgepr_sales_order", $w, $row);
				else $this->gen_m->insert("lgepr_sales_order", $row);
				
				$order_lines[] = $row["order_line"];
				$records++;
				
				//print_r($row); echo "<br/><br/>";
			}
			
			//remove sales orders in closed order
			$order_lines_split = array_chunk($order_lines, 500);
			foreach($order_lines_split as $items){
				$closed_orders = $this->gen_m->filter_select("lgepr_closed_order", false, ["order_line"], null, null, [["field" => "order_line", "values" => $items]]);
				if ($closed_orders){
					$aux = [];
					foreach($closed_orders as $item) $aux[] = $item->order_line;
					
					$this->gen_m->delete_in("lgepr_sales_order", "order_line", $aux);
				}
			}
			
			//remove all sales orders not updated
			$this->gen_m->delete("lgepr_sales_order", ["updated_at" => null]);
			$this->gen_m->delete("lgepr_sales_order", ["updated_at <" => $updated_at]);
			
			$this->update_model_category();
			
			/* Nueva tabla para gestion de pedidos */
			$this->process_lgepr_order();
			/* NO ELIMINAR */
			
			$msg = number_format($records)." record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
	public function process_lgepr_order(){
		ini_set('memory_limit', '4G');
		set_time_limit(0);
		
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
			trim($sheet->getCell('EL1')->getValue()),
		];
		
		//sales order header
		$h_gerp = ["Bill To Name", "Ship To Name", "Model", "Order No.", "Line No.", "RAD Unmeet Reason"];
		
		//header validation
		foreach($h as $i => $h_i) if ($h_i !== $h_gerp[$i]){ echo "Template error."; return; }
		
		//define now
		$now = date('Y-m-d H:i:s', time());
		
		$header_index = [];
		$header_mapping = ['Order Category' => 'order_category', 'Department' => 'department', 'Order No.' => 'order_no', 'Line No.' => 'line_no', 'SO Status(2)' => 'so_status', 'Order Status' => 'order_status', 'Line Status' => 'line_status', 'Original List Price' => 'original_list_price', 'List Price' => 'unit_list_price', 'Unit Selling Price' => 'unit_selling_price', 'Sales Amount' => 'order_amount', 'Tax Amount' => 'tax_amount', 'Charge Amount' => 'charge_amount', 'Line Total' => 'total_amount', 'DC Rate' => 'dc_rate', 'Currency' => 'currency', 'Delivery Number' => 'delivery_number', 'Invoice No.' => 'invoice_no', 'Customer Po Date' => 'customer_po_date', 'Create Date' => 'create_date', 'Booked Date' => 'booked_date', 'Order Date' => 'order_date', 'Customer RAD' => 'customer_rad', 'Req. Arrival Date From' => 'req_arrival_date_from', 'Req. Arrival Date To' => 'req_arrival_date_to', 'Appointment Date' => 'appointment_date', 'Req. Ship Date' => 'req_ship_date', 'Shipment Date' => 'shipment_date', 'Close Date' => 'closed_date', 'Inventory Org.' => 'inventory_org', 'Sub- Inventory' => 'sub_inventory', 'Order Type' => 'order_type', 'Line Type' => 'line_type', 'Bill To' => 'bill_to_code', 'Bill To Name' => 'bill_to_name', 'Ship To' => 'ship_to_code', 'Ship To Name' => 'ship_to_name', 'Order Qty' => 'order_qty', 'Cancel Qty' => 'cancel_qty', 'Item CBM' => 'item_cbm', 'Model' => 'model', 'Item Division' => 'item_division', 'Model Category' => 'model_category', 'PL1 Name' => 'product_level1_name', 'PL2 Name' => 'product_level2_name', 'PL3 Name' => 'product_level3_name', 'PL4 Name' => 'product_level4_name', 'Product Level4 Code' => 'product_level4', 'Instock Flag' => 'instock_flag', 'Inventory Reserved' => 'inventory_reserved', 'Partial Flag' => 'partial_flag', 'Pick Released' => 'pick_released', 'Ready To Pick' => 'ready_to_pick', 'SO-SA Mapping' => 'so_sa_mapping', 'Hold Flag' => 'hold_flag', 'Credit Hold' => 'credit_hold', 'Back Order Hold' => 'back_order_hold', 'Overdue Hold' => 'overdue_hold', 'Customer Hold' => 'customer_hold', 'Manual Hold' => 'manual_hold', 'Auto Pending Hold' => 'auto_pending_hold', 'Bank Collateral Hold' => 'bank_collateral_hold', 'Form Hold' => 'form_hold', 'FP Hold' => 'fp_hold', 'Future Hold' => 'future_hold', 'Insurance Hold' => 'insurance_hold', 'Minimum Hold' => 'minimum_hold', 'Payterm Term Hold' => 'payterm_term_hold', 'Pick Cancel Manual Hold' => 'pick_cancel_manual_hold', 'Reserve Hold' => 'reserve_hold', 'S/A Hold' => 'sa_hold', 'Accounting Unit' => 'accounting_unit', 'Carrier Code' => 'carrier_code', 'Customer Name' => 'customer_name', 'Customer PO No.' => 'customer_po_no', 'Install Type' => 'install_type', 'Interest Amt' => 'interest_amt', 'Item Type' => 'item_type_desctiption', 'Item Weight' => 'item_weight', 'Order Source' => 'order_source', 'Payment Term' => 'payment_term', 'Pick Release Qty' => 'pick_release_qty', 'Pricing Group' => 'pricing_group', 'Project Code' => 'project_code', 'Sales Channel (Low)' => 'sales_channel', 'Sales Person' => 'sales_person', 'Receiver City Desc' => 'ship_to_city', 'Shipping Method' => 'shipping_method', 'Price Condition' => 'price_condition',];
		
		// RowIterator로 행을 순회
		foreach ($sheet->getRowIterator() as $row){
			
			if ($row->getRowIndex() == 1){//make header_index setup
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); // 비어 있는 셀도 포함

				foreach ($cellIterator as $cell) {
					$header_index[] = $cell->getValue();
				}
				
				//print_r($header_index); echo "<br/><br/>";
			}else{//data insert/update
				
				$row_data = [];
				
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); // 비어 있는 셀도 포함

				foreach ($cellIterator as $cell) {
					$col_i = Coordinate::columnIndexFromString($cell->getColumn()) - 1;//index
					$col_h = $header_index[$col_i];//sales order report header
					//$col_h_mapped = $header_mapping[$col_h];
					
					$row_data[$col_h] = $cell->getValue();
				}
				
				$row_db = [];
				foreach($header_mapping as $k => $val){
					$row_db[$val] = trim($row_data[$k]);
				}
				
				//primary key
				$row_db["order_line"] = $row_db["order_no"]."_".$row_db["line_no"];
				
				//float comma
				$row_db["original_list_price"]	= str_replace(",", "", $row_db["original_list_price"]);
				$row_db["unit_list_price"]		= str_replace(",", "", $row_db["unit_list_price"]);
				$row_db["unit_selling_price"]	= str_replace(",", "", $row_db["unit_selling_price"]);
				$row_db["order_amount"] 		= str_replace(",", "", $row_db["order_amount"]);
				$row_db["tax_amount"] 			= str_replace(",", "", $row_db["tax_amount"]);
				$row_db["charge_amount"] 		= str_replace(",", "", $row_db["charge_amount"]);
				$row_db["total_amount"] 		= str_replace(",", "", $row_db["total_amount"]);
				
				//%
				$row_db["dc_rate"]	= $row_db["dc_rate"] ? str_replace("%", "", $row_db["dc_rate"])/100 : 0;
				
				//date convert: 28-OCT-2021 > 2021-10-28
				$row_db["appointment_date"] = $this->my_func->date_convert_5($row_db["appointment_date"]);
				
				//date convert: dd/mm/yyyy > yyyy-mm-dd
				$row_db["customer_po_date"] 	= $this->my_func->date_convert($row_db["customer_po_date"]);
				$row_db["create_date"] 			= $this->my_func->date_convert($row_db["create_date"]);
				$row_db["booked_date"] 			= $this->my_func->date_convert($row_db["booked_date"]);
				$row_db["order_date"] 			= $this->my_func->date_convert($row_db["order_date"]);
				$row_db["req_arrival_date_from"] = $this->my_func->date_convert($row_db["req_arrival_date_from"]);
				$row_db["req_arrival_date_to"] 	= $this->my_func->date_convert($row_db["req_arrival_date_to"]);
				$row_db["req_ship_date"] 		= $this->my_func->date_convert($row_db["req_ship_date"]);
				$row_db["shipment_date"] 		= $this->my_func->date_convert($row_db["shipment_date"]);
				$row_db["closed_date"] 			= $this->my_func->date_convert($row_db["closed_date"]);
				
				//customer_rad yyyy-mm-dd 0:00
				$row_db["customer_rad"] = $row_db["customer_rad"] ? explode(" ", $row_db["customer_rad"])[0] : null;
				
				//data from Closed order
				$row_db["category"] = $row_db["order_category"] === "RETURN" ? "Return" : "Sales";
				
				//usd calculation
				$er = 3.6;
				switch($row_db['currency']){
					case "BRL": $er = 3.25; break;
					case "USD": $er = 1; break;
					default:
						if ($row_db["booked_date"]){
							$rate = $this->gen_m->filter("exchange_rate", false, ["currency" => $row_db['currency'], "date <=" => $row_db["booked_date"]], null, null, [["date", "desc"]], 1);
							
							if ($rate){
								$er = $rate[0]->sell;
								if (!$er) $er = 3.6;
							}else $er = 3.6;
						}else $er = 3.6;
						
						break;
				}
				
				$row_db["order_amount_usd"] =  $row_db["order_amount"] ? round($row_db["order_amount"] / $er, 2) : 0;
				$row_db["total_amount_usd"] =  $row_db["total_amount"] ? round($row_db["total_amount"] / $er, 2) : 0;
				
				//updated
				$row_db["sales_updated_at"] = $now;
				
				//remove blank fields
				foreach($row_db as $key => $val) if (!$val) unset($row_db[$key]);
				
				//DB work
				$data = $this->gen_m->unique("lgepr_order", "order_line", $row_db["order_line"], false);
				if ($data){
					if ($data->line_status !== "Closed") $this->gen_m->update("lgepr_order", ["order_line" => $row_db["order_line"]], $row_db);
				}else $this->gen_m->insert("lgepr_order", $row_db);
					
				//print_r($row_db);
				//foreach($row_db as $k => $val){ echo $k."======>".$val."<br/>"; } echo "<br/><br/>";
			}
			
		}
		
		//fill dash company and division
		$this->update_dash_div_cat("lgepr_order");
		
		$records = $this->gen_m->filter("lgepr_order", false, ["dash_company" => null, "product_level4 !=" => "ZZZZZZZZ"]);
		foreach($records as $item){
			$aux = null;
			$aux = $this->gen_m->filter("lgepr_order", false, ["dash_company !=" => null], [["field" => "product_level4", "values" => [$item->product_level4]]], null, null, 1);
			if (!$aux) $aux = $this->gen_m->filter("lgepr_order", false, ["dash_company !=" => null], [["field" => "product_level4", "values" => [substr($item->product_level4, 0, 6)]]], null, null, 1);
			if (!$aux) $aux = $this->gen_m->filter("lgepr_order", false, ["dash_company !=" => null], [["field" => "product_level4", "values" => [substr($item->product_level4, 0, 4)]]], null, null, 1);
			if (!$aux) $aux = $this->gen_m->filter("lgepr_order", false, ["dash_company !=" => null], [["field" => "product_level4", "values" => [substr($item->product_level4, 0, 2)]]], null, null, 1);
			
			if ($aux){
				$val = ["dash_company" => $aux[0]->dash_company, "dash_division" => $aux[0]->dash_division, "model_category" => $aux[0]->model_category];
				$this->gen_m->update("lgepr_order", ["order_line" => $item->order_line], $val);	
			}
		}
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
