<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Lgepr_closed_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$d = $this->input->get("d");
		if (!$d) $d = date("Y-m");
		
		$first = $this->gen_m->filter("lgepr_closed_order", false, null, null, null, [["closed_date", "asc"]], 1);
		
		$w = ["closed_date >=" => date("Y-m-01", strtotime($d)), "closed_date <=" => date("Y-m-t", strtotime($d))];
		$o = [["closed_date", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
		
		$closed_orders = $this->gen_m->filter("lgepr_closed_order", false, $w, null, null, $o);
		
		$data = [
			"first" 		=> strtotime($first ? date("Y-m", strtotime($first[0]->closed_date)) : date("Y-m")),
			"last"			=> strtotime(date("Y-m")),
			"d" 			=> $d,
			"closed_orders"	=> $closed_orders,
			"main" 			=> "data_upload/lgepr_closed_order/index",
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
			"LEDSGN" => ["dash_company" => "MS"	, "dash_division" => "LED Signage"],
			"CTV" 	=> ["dash_company" => "MS"	, "dash_division" => "Commercial TV"],
			"PC" 	=> ["dash_company" => "MS"	, "dash_division" => "PC"],
			
			"RAC" 	=> ["dash_company" => "ES"	, "dash_division" => "RAC"],
			"SAC" 	=> ["dash_company" => "ES"	, "dash_division" => "SAC"],
			"A/C" 	=> ["dash_company" => "ES"	, "dash_division" => "Chiller"],
			
			"MC" 	=> ["dash_company" => "MC"	, "dash_division" => "MC"],
		];
		
		foreach($dash_mapping as $key => $item) $this->gen_m->update("lgepr_closed_order", ["model_category" => $key], $item);
		
		$this->gen_m->update("lgepr_closed_order", ["product_level2_name" => "SRAC"], $dash_mapping["RAC"]);
		$this->gen_m->update("lgepr_closed_order", ["product_level2_name" => "Commercial_LED Signage"], $dash_mapping["LEDSGN"]);
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
		$s = ["model_category", "product_level4"];
		$closed_orders = $this->gen_m->filter_select("lgepr_closed_order", false, $s, $w, null, null, [["product_level4", "desc"]], null, null, "product_level4");
		
		foreach($closed_orders as $item){
			$mc = "";
			
			$sub6 = substr($item->product_level4, 0, 6);
			$sub4 = substr($item->product_level4, 0, 4);
			$sub2 = substr($item->product_level4, 0, 2);
			
			if (array_key_exists($sub6, $mapping)) $mc = $mapping[$sub6];
			elseif (array_key_exists($sub4, $mapping)) $mc = $mapping[$sub4]; 
			elseif (array_key_exists($sub2, $mapping)) $mc = $mapping[$sub2]; 
			
			//echo $sub6." ".$sub4." >>> ".$mc."<br/>"; print_r($item); echo "<br/><br/>";
			
			if ($mc) $this->gen_m->update("lgepr_closed_order", ["product_level4" => $item->product_level4], ["model_category" => $mc]);
		}
		
		//update division & category for dashboard
		$this->update_dash_div_cat();
	}
	
	public function process(){
		ini_set('memory_limit', '4G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		libxml_disable_entity_loader(true); // XML 외부 엔티티 로더 비활성화
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/lgepr_closed_order.xls");
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
		$h_gerp = ["Category", "AU", "Bill To Name", "Ship To Name", "Model", "Order Qty", "Unit List  Price", "Unit Selling  Price", "Total Amount (USD)", "Total Amount", "Order Amount (USD)", "Order Amount", "Line Charge Amount", ];
		
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
			
			$rows_eq = [];
			
			for($i = 2; $i <= $max_row; $i++){
				
				$row = [
					'category'				=> trim($sheet->getCell('A'.$i)->getValue()),
					'bill_to_name'			=> trim($sheet->getCell('C'.$i)->getValue()),
					'ship_to_name'			=> trim($sheet->getCell('D'.$i)->getValue()),
					'customer_name'			=> trim($sheet->getCell('AA'.$i)->getValue()),
					'model'					=> trim($sheet->getCell('E'.$i)->getValue()),
					'order_qty'				=> trim($sheet->getCell('F'.$i)->getValue()),
					'total_amount_usd'		=> trim($sheet->getCell('I'.$i)->getValue()),
					'total_amount'			=> trim($sheet->getCell('J'.$i)->getValue()),
					'order_amount_usd'		=> trim($sheet->getCell('K'.$i)->getValue()),
					'order_amount'			=> trim($sheet->getCell('L'.$i)->getValue()),
					'line_charge_amount'	=> trim($sheet->getCell('M'.$i)->getValue()),
					'header_charge_amount'	=> trim($sheet->getCell('N'.$i)->getValue()),
					'tax_amount'			=> trim($sheet->getCell('O'.$i)->getValue()),
					'dc_amount'				=> trim($sheet->getCell('P'.$i)->getValue()),
					'dc_rate'				=> trim($sheet->getCell('Q'.$i)->getValue()),
					'currency'				=> trim($sheet->getCell('R'.$i)->getValue()),
					'inventory_org'			=> trim($sheet->getCell('T'.$i)->getValue()),
					'sub_inventory'			=> trim($sheet->getCell('U'.$i)->getValue()),
					'sales_person'			=> trim($sheet->getCell('V'.$i)->getValue()),
					'customer_department'	=> trim($sheet->getCell('AB'.$i)->getValue()),
					'product_level1_name'	=> trim($sheet->getCell('AC'.$i)->getValue()),
					'product_level2_name'	=> trim($sheet->getCell('AD'.$i)->getValue()),
					'product_level3_name'	=> trim($sheet->getCell('AE'.$i)->getValue()),
					'product_level4_name'	=> trim($sheet->getCell('AF'.$i)->getValue()),
					'model_category'		=> trim($sheet->getCell('AG'.$i)->getValue()),
					'item_weight'			=> trim($sheet->getCell('AI'.$i)->getValue()),
					'item_cbm'				=> trim($sheet->getCell('AJ'.$i)->getValue()),
					'order_date'			=> trim($sheet->getCell('AK'.$i)->getValue()),
					'shipment_date'			=> trim($sheet->getCell('AL'.$i)->getValue()),
					'closed_date'			=> trim($sheet->getCell('AN'.$i)->getValue()),
					'bill_to_code'			=> trim($sheet->getCell('AQ'.$i)->getValue()),
					'ship_to_code'			=> trim($sheet->getCell('AR'.$i)->getValue()),
					'ship_to_city'			=> trim($sheet->getCell('AT'.$i)->getValue()),
					'sales_channel'			=> trim($sheet->getCell('AX'.$i)->getValue()),
					'order_source'			=> trim($sheet->getCell('AY'.$i)->getValue()),
					'order_type'			=> trim($sheet->getCell('AZ'.$i)->getValue()),
					'order_no'				=> trim($sheet->getCell('BA'.$i)->getValue()),
					'line_no'				=> trim($sheet->getCell('BB'.$i)->getValue()),
					'customer_po_no'		=> trim($sheet->getCell('BE'.$i)->getValue()),
					'project_code'			=> trim($sheet->getCell('BF'.$i)->getValue()),
					'product_level4'		=> trim($sheet->getCell('BH'.$i)->getValue()),
					'receiver_city'			=> trim($sheet->getCell('BN'.$i)->getValue()),
					'invoice_no'			=> trim($sheet->getCell('BW'.$i)->getValue()),
					'invoice_date'			=> trim($sheet->getCell('BX'.$i)->getValue()),
					'shipping_method'		=> trim($sheet->getCell('CI'.$i)->getValue()),
					'updated_at'			=> $updated_at,
				];
				
				//create order_line as key
				$row["line_no"] = str_replace("' ", "", $row["line_no"]);
				$row["order_line"] = $row["order_no"]."_".$row["line_no"];
				
				//integer & float_convert
				$row["order_qty"] = str_replace(",", "", $row["order_qty"]);
				$row["total_amount_usd"] = str_replace(",", "", $row["total_amount_usd"]);
				$row["total_amount"] = str_replace(",", "", $row["total_amount"]);
				$row["order_amount_usd"] = str_replace(",", "", $row["order_amount_usd"]);
				$row["order_amount"] = str_replace(",", "", $row["order_amount"]);
				$row["line_charge_amount"] = str_replace(",", "", $row["line_charge_amount"]);
				$row["header_charge_amount"] = str_replace(",", "", $row["header_charge_amount"]);
				$row["tax_amount"] = str_replace(",", "", $row["tax_amount"]);
				$row["dc_amount"] = str_replace(",", "", $row["dc_amount"]);
				$row["item_weight"] = str_replace(",", "", $row["item_weight"]);
				$row["item_cbm"] = str_replace(",", "", $row["item_cbm"]);
				
				//% remove
				$row["dc_rate"] = str_replace("%", "", $row["dc_rate"]);
				
				//date convert: 24/06/2021 > 2021-10-28
				$row["order_date"] = $this->my_func->date_convert($row["order_date"]);
				$row["shipment_date"] = $this->my_func->date_convert($row["shipment_date"]);
				$row["closed_date"] = $this->my_func->date_convert($row["closed_date"]);
				$row["invoice_date"] = $this->my_func->date_convert($row["invoice_date"]);
				
				
				//print_r($row); echo "<br/><br/>";
				
				$closed_order = $this->gen_m->filter("lgepr_closed_order", false, ["order_line" => $row["order_line"]]);
				if ($closed_order) $this->gen_m->update("lgepr_closed_order", ["order_id" => $closed_order[0]->order_id], $row);
				else $this->gen_m->insert("lgepr_closed_order", $row);
				
				/* removed by code performance
				if (!$this->gen_m->filter("lgepr_closed_order", false, ["order_line" => $row["order_line"]])){
					$rows[] = $row;
					$order_lines[] = $row["order_line"];
				}
				
				//codigo update customer name
				elseif($this->gen_m->filter("lgepr_closed_order", false, ["order_line" => $row["order_line"]])){
					$rows_eq[] = $row;
				}
				*/
				//echo $i." Check ".number_Format(microtime(true) - $start_time, 2)." secs.<br/>";
			}
			
			/* removed by code performance
			$rows_split_eq = array_chunk($rows_eq, 1000);
			foreach($rows_split_eq as $items) $this->gen_m->update_multi("lgepr_closed_order", $items, 'order_line');
			////// termina codigo update customer Name
			
			//insert closed orders
			$rows_split = array_chunk($rows, 1000);
			foreach($rows_split as $items) $records += $this->gen_m->insert_m("lgepr_closed_order", $items);
			
			//remove closed orders in sales order table
			$order_lines_split = array_chunk($order_lines, 500);
			foreach($order_lines_split as $items) $this->gen_m->delete_in("lgepr_sales_order", "order_line", $items);
			
			*/
			
			$this->update_model_category();
			
			/* NO BORRAR!! Nueva tabla para pedidos unificados */
			$this->process_lgepr_order();
			
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
		$spreadsheet = IOFactory::load("./upload/lgepr_closed_order.xls");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('BW1')->getValue()),
		];
		
		//sales order header
		$h_gerp = ["Category", "AU", "Bill To Name", "Ship To Name", "Model", "Fapiao No."];
		
		//header validation
		foreach($h as $i => $h_i) if ($h_i !== $h_gerp[$i]){ echo "Template error."; return; }
		
		//define now
		$now = date('Y-m-d H:i:s', time());
		
		$header_index = [];
		$header_mapping = ['Category' => 'category', 'Customer Department' => 'department', 'Order No.' => 'order_no', 'Line No.' => 'line_no', 'Original List Pirce' => 'original_list_price', 'Unit List  Price' => 'unit_list_price', 'Unit Selling  Price' => 'unit_selling_price', 'Order Amount' => 'order_amount', 'Order Amount (USD)' => 'order_amount_usd', 'Tax Amount' => 'tax_amount', 'Line Charge Amount' => 'charge_amount', 'Total Amount' => 'total_amount', 'Total Amount (USD)' => 'total_amount_usd', 'DC Rate' => 'dc_rate', 'Currency' => 'currency', 'Invoice No.' => 'invoice_no', 'Order Date' => 'order_date', 'Shipment Date' => 'shipment_date', 'Closed Date' => 'closed_date', 'Inventory Org.' => 'inventory_org', 'Sub- Inventory' => 'sub_inventory', 'Order Type' => 'order_type', 'Line  Type' => 'line_type', 'Bill To Code' => 'bill_to_code', 'Bill To Name' => 'bill_to_name', 'Ship To Code' => 'ship_to_code', 'Ship To Name' => 'ship_to_name', 'Order Qty' => 'order_qty', 'Item CBM' => 'item_cbm', 'Model' => 'model', 'HQ AU' => 'item_division', 'Model Category' => 'model_category', 'Product Level1 Name' => 'product_level1_name', 'Product Level2 Name' => 'product_level2_name', 'Product Level3 Name' => 'product_level3_name', 'Product Level4 Name' => 'product_level4_name', 'Product Level4' => 'product_level4', 'AU' => 'accounting_unit', 'Book Currency' => 'book_currency', 'Customer Name' => 'customer_name', 'Customer PO No.' => 'customer_po_no', 'Install Type' => 'install_type', 'Interest Amt' => 'interest_amt', 'Item Type Desctiption' => 'item_type_desctiption', 'Item Weight' => 'item_weight', 'Order Source' => 'order_source', 'Payment Term' => 'payment_term', 'Pricing Group' => 'pricing_group', 'Project Code' => 'project_code', 'Sales Channel' => 'sales_channel', 'Sales Person' => 'sales_person', 'Ship To City' => 'ship_to_city', 'Shipping Method' => 'shipping_method', 'Price Condition' => 'price_condition',];
		
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
				$row_db["line_no"] = str_replace("' ", "", $row_db["line_no"]);
				$row_db["order_line"] = $row_db["order_no"]."_".$row_db["line_no"];
				
				//integer & float_convert
				$row_db["order_qty"] = str_replace(",", "", $row_db["order_qty"]);
				$row_db["original_list_price"] = str_replace(",", "", $row_db["original_list_price"]);
				$row_db["unit_list_price"] = str_replace(",", "", $row_db["unit_list_price"]);
				$row_db["unit_selling_price"] = str_replace(",", "", $row_db["unit_selling_price"]);
				$row_db["order_amount"] = str_replace(",", "", $row_db["order_amount"]);
				$row_db["order_amount_usd"] = str_replace(",", "", $row_db["order_amount_usd"]);
				$row_db["tax_amount"] = str_replace(",", "", $row_db["tax_amount"]);
				$row_db["charge_amount"] = str_replace(",", "", $row_db["charge_amount"]);
				$row_db["total_amount"] = str_replace(",", "", $row_db["total_amount"]);
				$row_db["total_amount_usd"] = str_replace(",", "", $row_db["total_amount_usd"]);
				$row_db["item_weight"] = str_replace(",", "", $row_db["item_weight"]);
				$row_db["item_cbm"] = str_replace(",", "", $row_db["item_cbm"]);
				
				//% remove
				$row_db["dc_rate"] = str_replace("%", "", $row_db["dc_rate"]);
				
				//date convert: 24/06/2021 > 2021-10-28
				$row_db["order_date"] = $this->my_func->date_convert($row_db["order_date"]);
				$row_db["shipment_date"] = $this->my_func->date_convert($row_db["shipment_date"]);
				$row_db["closed_date"] = $this->my_func->date_convert($row_db["closed_date"]);
				
				//default values
				$row_db["so_status"] = "INVO";
				$row_db["order_status"] = "Closed";
				$row_db["line_status"] = "Closed";
				
				//updated
				$row_db["closed_updated_at"] = $now;
				
				//DB work
				$data = $this->gen_m->unique("lgepr_order", "order_line", $row_db["order_line"], false);
				if ($data) $this->gen_m->update("lgepr_order", ["order_line" => $row_db["order_line"]], $row_db);
				else $this->gen_m->insert("lgepr_order", $row_db);
				
				//print_r($row_db); echo "<br/><br/>";
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
		
		//print_r($header_mapping);
		
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
				'file_name'		=> 'lgepr_closed_order.xls',
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
