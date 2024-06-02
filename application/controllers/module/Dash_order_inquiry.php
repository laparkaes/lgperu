<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Dash_order_inquiry extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"closed_updated"	=> $this->gen_m->filter("dash_closed_order_inquiry", false, null, null, null, [["updated", "desc"]], 1, 0)[0],
			"closed_first" 		=> $this->gen_m->filter("dash_closed_order_inquiry", false, null, null, null, [["closed_date", "asc"]], 1, 0)[0],
			"closed_last" 		=> $this->gen_m->filter("dash_closed_order_inquiry", false, null, null, null, [["closed_date", "desc"]], 1, 0)[0],
			"sales_updated"		=> $this->gen_m->filter("dash_sales_order_inquiry", false, null, null, null, [["updated", "desc"]], 1, 0)[0],
			"sales_first"		=> $this->gen_m->filter("dash_sales_order_inquiry", false, null, null, null, [["order_date", "asc"]], 1, 0)[0],
			"sales_last" 		=> $this->gen_m->filter("dash_sales_order_inquiry", false, null, null, null, [["order_date", "desc"]], 1, 0)[0],
			"main" 				=> "module/dash_order_inquiry/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'dash_order_inquiry',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$msg = $this->process_file();
			
			if ($msg) $type = "success";
			else $msg = "Wrong file.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function process_closed($sheet){
		//set max row value
		$max_row = $sheet->getHighestRow();
		//$max_col = $sheet->getHighestColumn();
		
		//result types
		$qty_insert = $qty_update = $qty_fail = 0;
		
		//define now
		$now = date('Y-m-d H:i:s', time());
		
		//echo "Processing closed order inquiry...<br/><br/>";
		
		$vars = ["category", "au", "bill_to_name", "ship_to_name", "model", "order_qty", "unit_list_price", "unit_selling_price", "total_amount_pen", "total_amount", "order_amount_pen", "order_amount", "line_charge_amount", "header_charge_amount", "tax_amount", "dc_amount", "dc_rate", "currency", "book_currency", "inventory_org", "sub_inventory", "sales_person", "pricing_group", "buying_group", "territory", "customer_code", "customer_name", "customer_department", "product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name", "model_category", "item_type_desctiption", "item_weight", "item_cbm", "order_date", "shipment_date", "lt_days", "closed_date", "aai_flag", "hq_au", "bill_to_code", "ship_to_code", "ship_to_country", "ship_to_city", "ship_to_state", "ship_to_zip_code", "payment_term", "sales_channel", "order_source", "order_type", "order_no", "line_no", "line_type", "invoice_no", "customer_po_no", "project_code", "comm_submission_no", "product_level4", "price_grade", "consumer_name", "receiver_name", "receiver_country", "receiver_postal_code", "receiver_city", "receiver_state", "receiver_province", "receiver_address1", "receiver_address2", "receiver_address3", "install_store_code", "install_type", "install_date", "fapiao_no", "fapiao_date", "cnpj", "nota_date", "acd_wh_code", "acd_wh_type", "net_price", "interest_amt", "original_list_pirce", "plp_submission_no", "price_condition", "nota_fiscal_serie_no", "shipping_method"];
		
		for($i = 2; $i < $max_row; $i++){
			$row = [];
			foreach($vars as $var_i => $var) $row[$var] = $sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue();
			
			//number conversion
			$row["order_qty"] = str_replace(",", "", $row["order_qty"]);
			$row["unit_list_price"] = str_replace(",", "", $row["unit_list_price"]);
			$row["unit_selling_price"] = str_replace(",", "", $row["unit_selling_price"]);
			$row["total_amount_pen"] = str_replace(",", "", $row["total_amount_pen"]);
			$row["total_amount"] = str_replace(",", "", $row["total_amount"]);
			$row["order_amount_pen"] = str_replace(",", "", $row["order_amount_pen"]);
			$row["order_amount"] = str_replace(",", "", $row["order_amount"]);
			$row["line_charge_amount"] = str_replace(",", "", $row["line_charge_amount"]);
			$row["header_charge_amount"] = str_replace(",", "", $row["header_charge_amount"]);
			$row["tax_amount"] = str_replace(",", "", $row["tax_amount"]);
			$row["dc_amount"] = str_replace(",", "", $row["dc_amount"]);
			$row["item_weight"] = str_replace(",", "", $row["item_weight"]);
			$row["item_cbm"] = str_replace(",", "", $row["item_cbm"]);
			$row["net_price"] = str_replace(",", "", $row["net_price"]);
			$row["interest_amt"] = str_replace(",", "", $row["interest_amt"]);
			$row["original_list_pirce"] = str_replace(",", "", $row["original_list_pirce"]);
			
			//% conversion
			$row["dc_rate"] = str_replace("%", "", str_replace(",", "", $row["dc_rate"]))/100;
			
			//date convertion
			$row["order_date"] = $this->my_func->date_convert($row["order_date"]);
			$row["shipment_date"] = $this->my_func->date_convert($row["shipment_date"]);
			$row["closed_date"] = $this->my_func->date_convert($row["closed_date"]);
			$row["fapiao_date"] = $this->my_func->date_convert($row["fapiao_date"]);
			
			//insert or update closed order inquiry
			$coi = $this->gen_m->filter("dash_closed_order_inquiry", false, ["order_no" => $row["order_no"], "line_no" => $row["line_no"]]);
			if ($coi){
				$row["updated"] = $now;
				
				if ($this->gen_m->update("dash_closed_order_inquiry", ["order_id" => $coi[0]->order_id], $row)) $qty_update++;
				else $qty_fail++;
			}else{
				$row["registered"] = $row["updated"] = $now;
				
				if ($this->gen_m->insert("dash_closed_order_inquiry", $row)) $qty_insert++;
				else $qty_fail++;
			}
			
			//print_r($soi); echo "<br/><br/>"; 
			//foreach($row as $key => $r) echo $key."=>".$r."<br/>";
			//print_r($row); echo "<br/><br/>";
		}
		
		$result = [];
		if ($qty_insert > 0) $result[] = number_format($qty_insert)." inserted";
		if ($qty_update > 0) $result[] = number_format($qty_update)." updated";
		if ($qty_fail > 0) $result[] = number_format($qty_fail)." failed";
		
		return "Closed order inquiry process result:<br/><br/>".implode(",", $result);
	}
	
	public function process_sales($sheet){
		//set max row value
		$max_row = $sheet->getHighestRow();
		//$max_col = $sheet->getHighestColumn();
		
		//result types
		$qty_insert = $qty_update = $qty_fail = 0;
		
		//define now
		$now = date('Y-m-d H:i:s', time());
		
		//echo "Processing sales order inquiry...<br/><br/>";
		
		$vars = ["bill_to_name", "ship_to_name", "model", "order_no", "line_no", "order_type", "line_status", "hold_flag", "ready_to_pick", "pick_released", "instock_flag", "order_qty", "unit_selling_price", "sales_amount", "tax_amount", "charge_amount", "line_total", "list_price", "original_list_price", "dc_rate", "currency", "dfi_applicable", "aai_applicable", "cancel_qty", "booked_date", "scheduled_cancel_date", "cancel_date", "expire_date", "req_arrival_date_from", "req_arrival_date_to", "req_ship_date", "shipment_date", "close_date", "line_type", "customer_name", "bill_to", "department", "ship_to", "ship_to_full_name", "store_no", "price_condition", "payment_term", "customer_po_no", "customer_po_date", "invoice_no", "invoice_line_no", "invoice_date", "sales_person", "pricing_group", "buying_group", "territory_code", "inventory_org", "sub__inventory", "shipping_method", "shipment_priority", "order_source", "order_status", "order_category", "quote_date", "quote_expire_date", "project_code", "comm_submission_no", "plp_submission_no", "bpm_request_no", "consumer_name", "consumer_phone_no", "consumer_mobile_no", "receiver_name", "receiver_phone_no", "receiver_mobile_no", "receiver_address1", "receiver_address2", "receiver_address3", "receiver_city", "receiver_city_desc", "receiver_county", "receiver_postal_code", "receiver_state", "receiver_province", "receiver_country", "item_division", "pl1_name", "pl2_name", "pl3_name", "pl4_name", "product_level4_code", "model_category", "item_type", "item_weight", "item_cbm", "sales_channel_high", "sales_channel_low", "ship_group", "back_order_hold", "credit_hold", "overdue_hold", "customer_hold", "payterm_term_hold", "fp_hold", "minimum_hold", "future_hold", "reserve_hold", "manual_hold", "auto_pending_hold", "sa_hold", "form_hold", "bank_collateral_hold", "insurance_hold", "partial_flag", "load_hold_flag", "inventory_reserved", "pick_release_qty", "long_multi_flag", "so_sa_mapping", "picking_remark", "shipping_remark", "create_employee_name", "create_date", "order_date", "expected_arrival_date", "fixed_arrival_date", "dls_interface", "sales_recognition_method", "billing_type", "lt_day", "edi_customer_remark", "carrier_code", "delivery_number", "manifest_grn_no", "warehouse_job_no", "customer_rad", "others_out_reason", "ship_set_name", "promising_txn_status", "promised_mad", "promised_arrival_date", "appointment_date", "promised_ship_date", "initial_promised_arrival_date", "accounting_unit", "rad_unmeet_reason", "install_type", "install_date", "acd_original_warehouse", "acd_original_wh_type", "customer_model", "customer_model_desc", "cnpj", "nota_no", "nota_date", "net_price", "interest_amt", "so_status2", "back_order_reason", "sbp_tax_include", "sbp_tax_exclude", "rrp_tax_include", "rrp_tax_exclude", "so_fap_flag", "so_fap_slot_date", "model_profit_level", "apms_no", "scheduled_back_date", "customer_po_type", "abcd", "revised_rsd", "revised_rad_from", "revised_rad_to", "pick_cancel_manual_hold"];
		
		for($i = 2; $i < $max_row; $i++){
			$row = [];
			foreach($vars as $var_i => $var) $row[$var] = $sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue();
			
			unset($row["abcd"]);
			
			//number conversion
			$row["order_qty"] = str_replace(",", "", $row["order_qty"]);
			$row["unit_selling_price"] = str_replace(",", "", $row["unit_selling_price"]);
			$row["sales_amount"] = str_replace(",", "", $row["sales_amount"]);
			$row["tax_amount"] = str_replace(",", "", $row["tax_amount"]);
			$row["charge_amount"] = str_replace(",", "", $row["charge_amount"]);
			$row["line_total"] = str_replace(",", "", $row["line_total"]);
			$row["list_price"] = str_replace(",", "", $row["list_price"]);
			$row["original_list_price"] = str_replace(",", "", $row["original_list_price"]);
			$row["sbp_tax_include"] = str_replace(",", "", $row["sbp_tax_include"]);
			$row["sbp_tax_exclude"] = str_replace(",", "", $row["sbp_tax_exclude"]);
			
			//% conversion
			$row["dc_rate"] = str_replace("%", "", str_replace(",", "", $row["dc_rate"]))/100;
			
			//date convertion
			$row["booked_date"] = $this->my_func->date_convert($row["booked_date"]);
			$row["scheduled_cancel_date"] = $this->my_func->date_convert($row["scheduled_cancel_date"]);
			$row["cancel_date"] = $this->my_func->date_convert($row["cancel_date"]);
			$row["expire_date"] = $this->my_func->date_convert($row["expire_date"]);
			$row["req_arrival_date_from"] = $this->my_func->date_convert($row["req_arrival_date_from"]);
			$row["req_arrival_date_to"] = $this->my_func->date_convert($row["req_arrival_date_to"]);
			$row["req_ship_date"] = $this->my_func->date_convert($row["req_ship_date"]);
			$row["shipment_date"] = $this->my_func->date_convert($row["shipment_date"]);
			$row["close_date"] = $this->my_func->date_convert($row["close_date"]);
			$row["customer_po_date"] = $this->my_func->date_convert($row["customer_po_date"]);
			$row["invoice_date"] = $this->my_func->date_convert($row["invoice_date"]);
			$row["quote_date"] = $this->my_func->date_convert($row["quote_date"]);
			$row["quote_expire_date"] = $this->my_func->date_convert($row["quote_expire_date"]);
			$row["create_date"] = $this->my_func->date_convert($row["create_date"]);
			$row["order_date"] = $this->my_func->date_convert($row["order_date"]);
			$row["expected_arrival_date"] = $this->my_func->date_convert($row["expected_arrival_date"]);
			$row["fixed_arrival_date"] = $this->my_func->date_convert($row["fixed_arrival_date"]);
			$row["customer_rad"] = $this->my_func->date_convert_2($row["customer_rad"]);
			
			$soi = $this->gen_m->filter("dash_sales_order_inquiry", false, ["order_no" => $row["order_no"], "line_no" => $row["line_no"]]);
			if ($soi){
				$row["updated"] = $now;
				
				if ($this->gen_m->update("dash_sales_order_inquiry", ["order_id" => $soi[0]->order_id], $row)) $qty_update++;
				else $qty_fail++;
			}else{
				$row["registered"] = $row["updated"] = $now;
				
				if ($this->gen_m->insert("dash_sales_order_inquiry", $row)) $qty_insert++;
				else $qty_fail++;
			}
			
			//print_r($soi); echo "<br/><br/>"; 
			//foreach($row as $key => $r) echo $key."=>".$r."<br/>";
			//print_r($row); echo "<br/><br/>";
		}
		
		$result = [];
		if ($qty_insert > 0) $result[] = number_format($qty_insert)." inserted";
		if ($qty_update > 0) $result[] = number_format($qty_update)." updated";
		if ($qty_fail > 0) $result[] = number_format($qty_fail)." failed";
		
		return "Sales order inquiry process result:<br/><br/>".implode(",", $result);
	}
	
	public function process_file(){
		set_time_limit(0);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/dash_order_inquiry.xls");
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
		
		//closed order inquiry file headers ~M1
		$h_closed = ["Category", "AU", "Bill To Name", "Ship To Name", "Model", "Order Qty", "Unit List  Price", "Unit Selling  Price", "Total Amount (PEN)", "Total Amount", "Order Amount (PEN)", "Order Amount", "Line Charge Amount"];
		
		//sales order inquiry file headers ~M1
		$h_sales = ["Bill To Name", "Ship To Name", "Model", "Order No.", "Line No.", "Order Type", "Line Status", "Hold Flag", "Ready To Pick", "Pick Released", "Instock Flag", "Order Qty", "Unit Selling Price"];
		
		//validate order inquiry type
		$is_closed_order = $is_sales_order = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_closed[$i]) $is_closed_order = false;
		foreach($h as $i => $h_i) if ($h_i !== $h_sales[$i]) $is_sales_order = false;
			
		if ($is_closed_order) $msg = $this->process_closed($sheet);
		elseif($is_sales_order) $msg = $this->process_sales($sheet);
		else $msg = "";

		return $msg;
	}
}
