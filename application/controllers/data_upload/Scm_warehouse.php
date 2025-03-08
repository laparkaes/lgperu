<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_warehouse extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"main" 			=> "data_upload/scm_warehouse/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload(){
		if (!$this->session->userdata('logged_in')){
			echo "Your session is finished. Login again please.<br/>";
			return;
		}
		
		set_time_limit(0);
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 90000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'scm_warehouse.xls',
		];
		$this->load->library('upload', $config);

		//print_r($this->input->post());

		if ($this->upload->do_upload('attach')){
			echo "Upload file is completed.<br/><br/>";
			echo "Data process result: ";
		
			switch($this->input->post("type")) {
				case "receiving": $this->receiving(); break;
				case "picking": $this->picking(); break;
				case "stock_klo": $this->stock_klo(); break;
				case "sa_report": $this->sa_report(); break;
			}
		}else echo str_replace("p>", "div>", $this->upload->display_errors())."<br/>";
		
		echo "<br/><br/>========================================<br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
	}
	
	private function receiving(){
		$spreadsheet = IOFactory::load("./upload/scm_warehouse.xls");
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
		];
		
		$h_v = ["SOURCE_HEADER_NO", "SOURCE_LINE_NO", "TRANSACTION_DATE", "TRANSFER_DATE", "STEP_CODE", "SOURCE_TYPE_CODE", "CONTAINER_NO"];
		
		$file_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_v[$i]) $file_validation = false;
		
		if ($file_validation){
			$qty_insert = $qty_update = 0;
			$max_row = $sheet->getHighestRow();
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"source_header_no" 		=> trim($sheet->getCell('A'.$i)->getValue()),
					"source_line_no" 		=> trim($sheet->getCell('B'.$i)->getValue()),
					"transaction_date" 		=> trim($sheet->getCell('C'.$i)->getValue()),
					"transfer_date" 		=> trim($sheet->getCell('D'.$i)->getValue()),
					"step_code" 			=> trim($sheet->getCell('E'.$i)->getValue()),
					"source_type_code" 		=> trim($sheet->getCell('F'.$i)->getValue()),
					"container_no" 			=> trim($sheet->getCell('G'.$i)->getValue()),
					"organization_code" 	=> trim($sheet->getCell('H'.$i)->getValue()),
					"subinventory_code" 	=> trim($sheet->getCell('I'.$i)->getValue()),
					"item_code" 			=> trim($sheet->getCell('J'.$i)->getValue()),
					"order_qty" 			=> trim($sheet->getCell('K'.$i)->getValue()),
					"transfer_flag" 		=> trim($sheet->getCell('L'.$i)->getValue()),
					"error_message_text" 	=> trim($sheet->getCell('M'.$i)->getValue()),
					"cancel_flag" 			=> trim($sheet->getCell('N'.$i)->getValue()),
					"transaction_date_3pl" 	=> trim($sheet->getCell('O'.$i)->getValue()),
					"receipt_qty" 			=> trim($sheet->getCell('P'.$i)->getValue()),
					"shipping_qty" 			=> trim($sheet->getCell('Q'.$i)->getValue()),
					"good_set_qty" 			=> trim($sheet->getCell('R'.$i)->getValue()),
					"unit_damage_qty" 		=> trim($sheet->getCell('S'.$i)->getValue()),
					"box_damage_qty" 		=> trim($sheet->getCell('T'.$i)->getValue()),
					"wrong_model_qty" 		=> trim($sheet->getCell('U'.$i)->getValue()),
				 ];
				 
				 //date conversion
				 $row["transaction_date"] 		= str_replace(".", "-", $row["transaction_date"]);
				 $row["transfer_date"] 			= str_replace(".", "-", $row["transfer_date"]);
				 $row["transaction_date_3pl"] 	= str_replace(".", "-", $row["transaction_date_3pl"]);
				 
				 //set no values as null
				 $row["error_message_text"] 	= $row["error_message_text"] ? $row["error_message_text"] : null;
				 $row["transaction_date_3pl"] 	= $row["transaction_date_3pl"] ? $row["transaction_date_3pl"] : null;
				 $row["receipt_qty"] 			= $row["receipt_qty"] ? $row["receipt_qty"] : null;
				 $row["shipping_qty"] 			= $row["shipping_qty"] ? $row["shipping_qty"] : null;
				 $row["good_set_qty"] 			= $row["good_set_qty"] ? $row["good_set_qty"] : null;
				 $row["unit_damage_qty"] 		= $row["unit_damage_qty"] ? $row["unit_damage_qty"] : null;
				 $row["box_damage_qty"] 		= $row["box_damage_qty"] ? $row["box_damage_qty"] : null;
				 $row["wrong_model_qty"] 		= $row["wrong_model_qty"] ? $row["wrong_model_qty"] : null;
				 
				 //DB parameter setting
				 $tablename = "scm_warehouse_receiving";
				 $w = [
					"source_header_no" 		=> $row["source_header_no"],
					"source_line_no" 		=> $row["source_line_no"],
				 ];
				 
				 $record = $this->gen_m->filter($tablename, false, $w);
				 if ($record){
					 $record = $record[0];
					 if ((!$record->transaction_date_3pl) and ($row["transaction_date_3pl"])){
						 $qty_update++;
						 $this->gen_m->update($tablename, ["receiving_id" => $record->receiving_id], $row); 
					 }
				 }else{
					 $qty_insert++;
					 $this->gen_m->insert($tablename, $row);
				 }
			}
			
			echo number_format($qty_insert)." inserted, ".number_format($qty_update)." updated.<br/>";
		}else echo "Wrong file.<br/>";
	}
	
	private function picking(){
		$spreadsheet = IOFactory::load("./upload/scm_warehouse.xls");
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
		];
		
		$h_v = ["SOURCE_HEADER_NO", "SOURCE_LINE_NO", "TRANSACTION_DATE", "TRANSFER_DATE", "STEP_CODE", "SOURCE_TYPE_CODE", "PICK_ORDER_NO"];
		
		$file_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_v[$i]) $file_validation = false;
		
		if ($file_validation){
			$qty_insert = $qty_update = 0;
			$max_row = $sheet->getHighestRow();
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"source_header_no" 		=> trim($sheet->getCell('A'.$i)->getValue()),
					"source_line_no" 		=> trim($sheet->getCell('B'.$i)->getValue()),
					"transaction_date" 		=> trim($sheet->getCell('C'.$i)->getValue()),
					"transfer_date" 		=> trim($sheet->getCell('D'.$i)->getValue()),
					"step_code" 			=> trim($sheet->getCell('E'.$i)->getValue()),
					"source_type_code" 		=> trim($sheet->getCell('F'.$i)->getValue()),
					"pick_order_no" 		=> trim($sheet->getCell('G'.$i)->getValue()),
					"pick_seq_no" 			=> trim($sheet->getCell('H'.$i)->getValue()),
					"organization_code" 	=> trim($sheet->getCell('I'.$i)->getValue()),
					"subinventory_code" 	=> trim($sheet->getCell('J'.$i)->getValue()),
					"item_code" 			=> trim($sheet->getCell('K'.$i)->getValue()),
					"order_qty" 			=> trim($sheet->getCell('L'.$i)->getValue()),
					"pick_qty" 				=> trim($sheet->getCell('M'.$i)->getValue()),
					"transfer_flag" 		=> trim($sheet->getCell('N'.$i)->getValue()),
					"error_message_text" 	=> trim($sheet->getCell('O'.$i)->getValue()),
					"cancel_flag" 			=> trim($sheet->getCell('P'.$i)->getValue()),
					"transaction_date_3pl" 	=> trim($sheet->getCell('Q'.$i)->getValue()),
					"delivery_qty" 			=> trim($sheet->getCell('R'.$i)->getValue()),
				 ];
				 
				 //date conversion
				 $row["transaction_date"] 		= str_replace(".", "-", $row["transaction_date"]);
				 $row["transfer_date"] 			= str_replace(".", "-", $row["transfer_date"]);
				 $row["transaction_date_3pl"] 	= str_replace(".", "-", $row["transaction_date_3pl"]);
				 
				 //set no values as null
				 $row["transfer_flag"] 			= $row["transfer_flag"] ? $row["transfer_flag"] : null;
				 $row["error_message_text"] 	= $row["error_message_text"] ? $row["error_message_text"] : null;
				 $row["cancel_flag"] 			= $row["cancel_flag"] ? $row["cancel_flag"] : null;
				 $row["transaction_date_3pl"] 	= $row["transaction_date_3pl"] ? $row["transaction_date_3pl"] : null;
				 $row["delivery_qty"] 			= $row["delivery_qty"] ? $row["delivery_qty"] : null;
				 
				 //DB parameter setting
				 $tablename = "scm_warehouse_picking";
				 $w = [
					"source_header_no" 		=> $row["source_header_no"],
					"source_line_no" 		=> $row["source_line_no"],
				 ];
				 
				 $record = $this->gen_m->filter($tablename, false, $w);
				 if ($record){
					 $record = $record[0];
					 if ((!$record->transaction_date_3pl) and ($row["transaction_date_3pl"])){
						 $qty_update++;
						 $this->gen_m->update($tablename, ["picking_id" => $record->picking_id], $row);
					 }
				 }else{
					 $qty_insert++;
					 $this->gen_m->insert($tablename, $row);
				 }
			}
			
			echo number_format($qty_insert)." inserted, ".number_format($qty_update)." updated.<br/>";
		}else echo "Wrong file.";
	}
	
	private function stock_klo(){
		$spreadsheet = IOFactory::load("./upload/scm_warehouse.xls");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
		];

		$h_v = ["CIA", "PRODUCT_WMS", "PRODUCT_LG", "DESCRIPTION", "SUB-INVENTORY", "STOCK"];
		
		$file_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_v[$i]) $file_validation = false;
		
		if ($file_validation){
			$qty_insert = $qty_update = 0;
			$max_row = $sheet->getHighestRow();
			
			//DB parameter setting
			$tablename = "scm_warehouse_klo_stock";
				 
			$this->gen_m->truncate($tablename);
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"cia" 			=> trim($sheet->getCell('A'.$i)->getValue()),
					"product_wms" 	=> trim($sheet->getCell('B'.$i)->getValue()),
					"product_lg" 	=> trim($sheet->getCell('C'.$i)->getValue()),
					"description" 	=> trim($sheet->getCell('D'.$i)->getValue()),
					"sub_inventory" => trim($sheet->getCell('E'.$i)->getValue()),
					"stock" 		=> trim($sheet->getCell('F'.$i)->getValue()),
				 ];
				 
				 //set no values as null
				 $row["cia"] 			= $row["cia"] ? $row["cia"] : null;
				 $row["product_wms"] 	= $row["product_wms"] ? $row["product_wms"] : null;
				 $row["product_lg"] 	= $row["product_lg"] ? $row["product_lg"] : null;
				 $row["description"] 	= $row["description"] ? $row["description"] : null;
				 $row["sub_inventory"]	= $row["sub_inventory"] ? $row["sub_inventory"] : null;
				 $row["stock"] 			= $row["stock"] ? $row["stock"] : null;
				 
				 $qty_insert++;
				 $this->gen_m->insert($tablename, $row);
			}
			
			echo number_format($qty_insert)." inserted, ".number_format($qty_update)." updated.<br/>";
		}else echo "Wrong file.";
	}
	
	
	
	
	
	private function update_model_category(){
		//get gerp records without model category group by product lvl4 code
		$s_g = ["model_category", "model", "product_level4_name", "product_level4_code"];
		$w_g = ["model_category =" => null, "product_level4_code !=" => "ZZZZZZZZ", "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		$gerps = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["product_level4_code", "desc"]], null, null, "product_level4_code");
		
		//get ger records with model category group by product lvl4 code
		$f = ["model_category !=" => null];
		$gerps_aux = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $f, null, null, [["product_level4_code", "desc"]], null, null, "product_level4_code");
		
		//set mapping array to assign model category
		$mapping = [];
		foreach($gerps_aux as $g){
			$mapping[substr($g->product_level4_code, 0, 4)] = $g->model_category;
			$mapping[substr($g->product_level4_code, 0, 2)] = $g->model_category;
			//print_r($g); echo "<br/><br/>";
		}
		//print_r($mapping);
		
		//start tu assign model category by product lvl 2 then lvl 1 (in case of no data with lvl 2)
		foreach($gerps as $g){
			if (!$g->model_category){
				$sub4 = substr($g->product_level4_code, 0, 4);
				if (array_key_exists($sub4, $mapping)) $g->model_category = $mapping[$sub4];
			}
			
			if (!$g->model_category){
				$sub2 = substr($g->product_level4_code, 0, 2);
				if (array_key_exists($sub2, $mapping)) $g->model_category = $mapping[$sub2];
			}
			
			if ($g->model_category) $this->gen_m->update("obs_gerp_sales_order", ["product_level4_code" => $g->product_level4_code], ["model_category" => $g->model_category]);
			
			//print_r($g); echo "<br/><br/>";
		}
		//echo "======================================<br/><br/>";
	}
	
	private function process($filename = "obs_gerp.xls"){
		set_time_limit(0);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
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
		
		$result = [];
		
		if ($is_gerp){
			$max_row = $sheet->getHighestRow();
			
			//result types
			$qty_insert = $qty_update = $qty_fail = 0;
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			//db fields
			$vars = ["bill_to_name",  "ship_to_name",  "model",  "order_no",  "line_no",  "order_type",  "line_status",  "hold_flag",  "ready_to_pick",  "pick_released",  "instock_flag",  "ordered_qty",  "unit_selling_price",  "sales_amount",  "tax_amount",  "charge_amount",  "line_total",  "list_price",  "original_list_price",  "dc_rate",  "currency",  "dfi_applicable",  "aai_applicable",  "cancel_qty",  "booked_date",  "scheduled_cancel_date",  "expire_date",  "req_arrival_date_from",  "req_arrival_date_to",  "req_ship_date",  "shipment_date",  "close_date",  "line_type",  "customer_name",  "bill_to",  "customer_department",  "ship_to",  "store_no",  "price_condition",  "payment_term",  "customer_po_no",  "customer_po_date",  "invoice_no",  "invoice_line_no",  "invoice_date",  "sales_person",  "pricing_group",  "buying_group",  "inventory_org",  "sub_inventory",  "shipping_method",  "shipment_priority",  "order_source",  "order_status",  "order_category",  "quote_date",  "quote_expire_date",  "project_code",  "comm_submission_no",  "plp_submission_no",  "bpm_request_no",  "consumer_name",  "receiver_name",  "consumer_phone_no",  "consumermobile_no",  "receiver_phone_no",  "receiver_mobile_no",  "receiver_address1",  "receiver_address2",  "receiver_address3",  "receiver_city",  "receiver_city_desc",  "receiver_county",  "receiver_postal_code",  "receiver_state",  "receiver_province",  "receiver_country",  "item_division",  "product_level1_name",  "product_level2_name",  "product_level3_name",  "product_level4_name",  "product_level4_code",  "model_category",  "item_type_desctiption",  "item_weight",  "item_cbm",  "sales_channel_high",  "sales_channel_low",  "ship_group",  "back_order_hold",  "credit_hold",  "overdue_hold",  "customer_hold",  "payterm_term_hold",  "fp_hold",  "minimum_hold",  "future_hold",  "reserve_hold",  "manual_hold",  "auto_pending_hold",  "sa_hold",  "form_hold",  "bank_collateral_hold",  "insurance_hold",  "partial_flag",  "load_hold_flag",  "inventory_reserved",  "pick_release_qty",  "long_multi_flag",  "so_sa_mapping",  "picking_remark",  "shipping_remark",  "create_employee_name",  "create_date",  "dls_interface",  "edi_customer_remark",  "sales_recognition_method",  "billing_type",  "lt_day",  "carrier_code",  "delivery_number",  "manifest_grn_no",  "warehouse_job_no",  "customer_rad",  "others_out_reason",  "ship_set_name",  "promising_txn_status",  "promised_mad",  "promised_arrival_date",  "promised_ship_date",  "initial_promised_arrival_date",  "accounting_unit",  "acd_original_warehouse",  "acd_original_wh_type",  "cnjp",  "nota_no",  "nota_date",  "so_status2",  "sbp_tax_include",  "sbp_tax_exclude",  "rrp_tax_include",  "rrp_tax_exclude",  "so_fap_flag",  "so_fap_slot_date"];
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [];
				foreach($vars as $var_i => $var){
					$row[$var] = trim($sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
					if (!$row[$var]) $row[$var] = null;
				}
				
				//print_r($row); echo "<br/><br/>";
				
				//apply trim
				$row["order_no"] = trim($row["order_no"]);
				$row["line_no"] = trim($row["line_no"]);
				
				//float_convert
				$row["unit_selling_price"] = str_replace(",", "", $row["unit_selling_price"]);
				$row["sales_amount"] = str_replace(",", "", $row["sales_amount"]);
				$row["tax_amount"] = str_replace(",", "", $row["tax_amount"]);
				$row["charge_amount"] = str_replace(",", "", $row["charge_amount"]);
				$row["line_total"] = str_replace(",", "", $row["line_total"]);
				$row["list_price"] = str_replace(",", "", $row["list_price"]);
				$row["original_list_price"] = str_replace(",", "", $row["original_list_price"]);
				$row["item_weight"] = str_replace(",", "", $row["item_weight"]);
				$row["item_cbm"] = str_replace(",", "", $row["item_cbm"]);
				$row["sbp_tax_include"] = str_replace(",", "", $row["sbp_tax_include"]);
				$row["sbp_tax_exclude"] = str_replace(",", "", $row["sbp_tax_exclude"]);
				$row["rrp_tax_include"] = str_replace(",", "", $row["rrp_tax_include"]);
				$row["rrp_tax_exclude"] = str_replace(",", "", $row["rrp_tax_exclude"]);
				
				//date convert: 28-OCT-21 > 2021-10-28
				$row["booked_date"] = $this->my_func->date_convert($row["booked_date"]);
				$row["scheduled_cancel_date"] = $this->my_func->date_convert($row["scheduled_cancel_date"]);
				$row["expire_date"] = $this->my_func->date_convert($row["expire_date"]);
				$row["req_arrival_date_from"] = $this->my_func->date_convert($row["req_arrival_date_from"]);
				$row["req_arrival_date_to"] = $this->my_func->date_convert($row["req_arrival_date_to"]);
				$row["req_ship_date"] = $this->my_func->date_convert($row["req_ship_date"]);
				$row["shipment_date"] = $this->my_func->date_convert($row["shipment_date"]);
				$row["close_date"] = $this->my_func->date_convert($row["close_date"]);
				$row["invoice_date"] = $this->my_func->date_convert($row["invoice_date"]);
				$row["create_date"] = $this->my_func->date_convert($row["create_date"]);
				$row["customer_po_date"] = $this->my_func->date_convert($row["customer_po_date"]);
				
				//date convert: 2021/11/02 00:00:00 > 2021-11-02
				//$row["customer_rad"] = $this->my_func->date_convert_2($row["customer_rad"]);
				
				//% > float
				$row["dc_rate"] = str_replace("%", "", $row["dc_rate"])/100;
				
				//foreach($row as $key => $val){echo $key."===> ".$val."<br/>";} echo "<br/><br/>";
				
				$f = ["order_no" => $row["order_no"], "line_no" => $row["line_no"]];
				$sales_order = $this->gen_m->filter("obs_gerp_sales_order", false, $f);
				if ($sales_order){
					//echo "Update<br/>".$row["create_date"]."<br/>";
					if ($this->gen_m->update("obs_gerp_sales_order", ["sales_order_id" => $sales_order[0]->sales_order_id], $row)) $qty_update++;
					else $qty_fail++;
				}else{
					//echo "Insert<br/>".$row["create_date"]."<br/>";
					if ($this->gen_m->insert("obs_gerp_sales_order", $row)) $qty_insert++;
					else $qty_fail++;
				}
				
				//print_r($f); echo "<br/><br/>";
				//if ($i > 100) break;
			}
			
			if ($qty_insert > 0) $result[] = number_format($qty_insert)." inserted";
			if ($qty_update > 0) $result[] = number_format($qty_update)." updated";
			if ($qty_fail > 0) $result[] = number_format($qty_fail)." failed";
		}
		
		$this->update_model_category();
		
		return $result ? "OBS GERP Sales orders process result:<br/><br/>".implode(", ", $result) : null;
		//echo $result ? "OBS GERP Sales orders process result:<br/><br/>".implode(", ", $result) : null;
		
	}
	
	public function debug(){
		//$this->gen_m->delete("obs_gerp_sales_order", ["create_date >=" => "2024-07-01", "create_date <=" => "2024-07-30"]);
		echo $this->process_new($filename = "obs_gerp.xls");
	}
	
	private function process_new($filename = "obs_gerp.xls"){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
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
		
		//echo number_Format(microtime(true) - $start_time, 2)." sec *** Ready to start<br/><br/>";
		
		$inserted = 0; $msg = ""; $date_arr = [];
		
		if ($is_gerp){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			//db fields
			$vars = ["bill_to_name",  "ship_to_name",  "model",  "order_no",  "line_no",  "order_type",  "line_status",  "hold_flag",  "ready_to_pick",  "pick_released",  "instock_flag",  "ordered_qty",  "unit_selling_price",  "sales_amount",  "tax_amount",  "charge_amount",  "line_total",  "list_price",  "original_list_price",  "dc_rate",  "currency",  "dfi_applicable",  "aai_applicable",  "cancel_qty",  "booked_date",  "scheduled_cancel_date",  "expire_date",  "req_arrival_date_from",  "req_arrival_date_to",  "req_ship_date",  "shipment_date",  "close_date",  "line_type",  "customer_name",  "bill_to",  "customer_department",  "ship_to",  "store_no",  "price_condition",  "payment_term",  "customer_po_no",  "customer_po_date",  "invoice_no",  "invoice_line_no",  "invoice_date",  "sales_person",  "pricing_group",  "buying_group",  "inventory_org",  "sub_inventory",  "shipping_method",  "shipment_priority",  "order_source",  "order_status",  "order_category",  "quote_date",  "quote_expire_date",  "project_code",  "comm_submission_no",  "plp_submission_no",  "bpm_request_no",  "consumer_name",  "receiver_name",  "consumer_phone_no",  "consumermobile_no",  "receiver_phone_no",  "receiver_mobile_no",  "receiver_address1",  "receiver_address2",  "receiver_address3",  "receiver_city",  "receiver_city_desc",  "receiver_county",  "receiver_postal_code",  "receiver_state",  "receiver_province",  "receiver_country",  "item_division",  "product_level1_name",  "product_level2_name",  "product_level3_name",  "product_level4_name",  "product_level4_code",  "model_category",  "item_type_desctiption",  "item_weight",  "item_cbm",  "sales_channel_high",  "sales_channel_low",  "ship_group",  "back_order_hold",  "credit_hold",  "overdue_hold",  "customer_hold",  "payterm_term_hold",  "fp_hold",  "minimum_hold",  "future_hold",  "reserve_hold",  "manual_hold",  "auto_pending_hold",  "sa_hold",  "form_hold",  "bank_collateral_hold",  "insurance_hold",  "partial_flag",  "load_hold_flag",  "inventory_reserved",  "pick_release_qty",  "long_multi_flag",  "so_sa_mapping",  "picking_remark",  "shipping_remark",  "create_employee_name",  "create_date",  "dls_interface",  "edi_customer_remark",  "sales_recognition_method",  "billing_type",  "lt_day",  "carrier_code",  "delivery_number",  "manifest_grn_no",  "warehouse_job_no",  "customer_rad",  "others_out_reason",  "ship_set_name",  "promising_txn_status",  "promised_mad",  "promised_arrival_date",  "promised_ship_date",  "initial_promised_arrival_date",  "accounting_unit",  "acd_original_warehouse",  "acd_original_wh_type",  "cnjp",  "nota_no",  "nota_date",  "so_status2",  "sbp_tax_include",  "sbp_tax_exclude",  "rrp_tax_include",  "rrp_tax_exclude",  "so_fap_flag",  "so_fap_slot_date"];
			
			$rows = [];
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [];
				foreach($vars as $var_i => $var){
					$row[$var] = trim($sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
					if (!$row[$var]) $row[$var] = null;
				}
				
				//print_r($row); echo "<br/><br/>";
				
				//apply trim
				$row["order_no"] = trim($row["order_no"]);
				$row["line_no"] = trim($row["line_no"]);
				
				//float_convert
				$row["unit_selling_price"] = str_replace(",", "", $row["unit_selling_price"]);
				$row["sales_amount"] = str_replace(",", "", $row["sales_amount"]);
				$row["tax_amount"] = str_replace(",", "", $row["tax_amount"]);
				$row["charge_amount"] = str_replace(",", "", $row["charge_amount"]);
				$row["line_total"] = str_replace(",", "", $row["line_total"]);
				$row["list_price"] = str_replace(",", "", $row["list_price"]);
				$row["original_list_price"] = str_replace(",", "", $row["original_list_price"]);
				$row["item_weight"] = str_replace(",", "", $row["item_weight"]);
				$row["item_cbm"] = str_replace(",", "", $row["item_cbm"]);
				$row["sbp_tax_include"] = str_replace(",", "", $row["sbp_tax_include"]);
				$row["sbp_tax_exclude"] = str_replace(",", "", $row["sbp_tax_exclude"]);
				$row["rrp_tax_include"] = str_replace(",", "", $row["rrp_tax_include"]);
				$row["rrp_tax_exclude"] = str_replace(",", "", $row["rrp_tax_exclude"]);
				
				//date convert: 28-OCT-21 > 2021-10-28
				$row["booked_date"] = $this->my_func->date_convert_4($row["booked_date"]);
				$row["scheduled_cancel_date"] = $this->my_func->date_convert_4($row["scheduled_cancel_date"]);
				$row["expire_date"] = $this->my_func->date_convert_4($row["expire_date"]);
				$row["req_arrival_date_from"] = $this->my_func->date_convert_4($row["req_arrival_date_from"]);
				$row["req_arrival_date_to"] = $this->my_func->date_convert_4($row["req_arrival_date_to"]);
				$row["req_ship_date"] = $this->my_func->date_convert_4($row["req_ship_date"]);
				$row["shipment_date"] = $this->my_func->date_convert_4($row["shipment_date"]);
				$row["close_date"] = $this->my_func->date_convert_4($row["close_date"]);
				$row["invoice_date"] = $this->my_func->date_convert_4($row["invoice_date"]);
				$row["create_date"] = $this->my_func->date_convert_4($row["create_date"]);
				$row["customer_po_date"] = $this->my_func->date_convert_4($row["customer_po_date"]);
				
				//date convert: 2021/11/02 00:00:00 > 2021-11-02
				//$row["customer_rad"] = $this->my_func->date_convert_2($row["customer_rad"]);
				
				//% > float
				$row["dc_rate"] = str_replace("%", "", $row["dc_rate"])/100;
				
				$rows[] = $row;
				
				$date_arr[] = $row["create_date"];
				
				//foreach($row as $key => $val){echo $key."===> ".$val."<br/>";} echo "<br/><br/>";
			}
			
			sort($date_arr);
			$date_arr = array_unique($date_arr);
			//print_r($date_arr);
			if ($rows){
				//echo number_Format(microtime(true) - $start_time, 2)." sec *** ".number_format(count($rows))." datas to insert<br/><br/>";
				
				$from = reset($date_arr);
				$to = end($date_arr);
				
				//remove
				$this->gen_m->delete("obs_gerp_sales_order", ["create_date >=" => $from, "create_date <=" => $to]);
				
				//insert
				$inserted = $this->gen_m->insert_m("obs_gerp_sales_order", $rows);
			}
		}
		
		$this->update_model_category();
		
		return number_format($inserted)." rows affected in ".number_Format(microtime(true) - $start_time, 2)." secs".$msg;
		//echo number_format($inserted)." rows affected in ".number_Format(microtime(true) - $start_time, 2)." secs";
	}
	
}
