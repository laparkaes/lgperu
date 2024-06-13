<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Local_api extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function test(){
		$order_items = $this->gen_m->all("order_item");
		
		//print_r($order_items);
		header('Content-Type: application/json');
		echo json_encode(["order_items" => $order_items]);
	}
	
	public function get_sales_order(){
		//llamasys/local_api/get_sales_order?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"order_date >=" => $f,
				"order_date <=" => $t,
			];
			
			$res = $this->gen_m->filter("dash_sales_order_inquiry", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_closed_order(){
		//llamasys/local_api/get_closed_order?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"order_date >=" => $f,
				"order_date <=" => $t,
			];
			
			$res = $this->gen_m->filter("dash_closed_order_inquiry", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_obs_magento(){
		//llamasys/local_api/get_obs_magento?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"local_time >=" => $f." 00:00:00",
				"local_time <=" => $t." 23:59:59",
			];
			
			$res = $this->gen_m->filter("obs_magento_item", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_obs_gerp_month(){
		//llamasys/local_api/get_obs_gerp_month?key=lgepr
		
		$key = $this->input->get("key");
		$res = [];
		
		if ($key === "lgepr"){
			$filter = [
				"create_date >=" => date("Y-m-01")." 00:00:00",
				"create_date <=" => date("Y-m-t")." 23:59:59",
			];
			
			$s = [
				"bill_to_name", 
				"ship_to_name", 
				"model", 
				"order_no", 
				"line_no", 
				//"order_type", 
				"line_status", 
				//"hold_flag", 
				//"ready_to_pick", 
				//"pick_released", 
				//"instock_flag", 
				"ordered_qty", 
				"unit_selling_price", 
				"sales_amount", 
				"tax_amount", 
				"charge_amount", 
				"line_total", 
				"list_price", 
				"original_list_price", 
				"dc_rate", 
				"currency", 
				//"dfi_applicable", 
				//"aai_applicable", 
				"cancel_qty", 
				"booked_date", 
				//"scheduled_cancel_date", 
				//"expire_date", 
				//"req_arrival_date_from", 
				//"req_arrival_date_to", 
				//"req_ship_date", 
				"shipment_date", 
				"close_date", 
				//"line_type", 
				"customer_name", 
				"bill_to", 
				"customer_department", 
				"ship_to", 
				//"store_no", 
				//"price_condition", 
				//"payment_term", 
				//"customer_po_no", 
				//"customer_po_date", 
				//"invoice_no", 
				//"invoice_line_no", 
				"invoice_date", 
				//"sales_person", 
				//"pricing_group", 
				//"buying_group", 
				"inventory_org", 
				"sub_inventory", 
				//"shipping_method", 
				//"shipment_priority", 
				//"order_source", 
				"order_status", 
				"order_category", 
				//"quote_date", 
				//"quote_expire_date", 
				//"project_code", 
				//"comm_submission_no", 
				//"plp_submission_no", 
				//"bpm_request_no", 
				"consumer_name", 
				"receiver_name", 
				//"consumer_phone_no", 
				//"consumermobile_no", 
				//"receiver_phone_no", 
				//"receiver_mobile_no", 
				//"receiver_address1", 
				//"receiver_address2", 
				//"receiver_address3", 
				"receiver_city", 
				//"receiver_city_desc", 
				//"receiver_county", 
				"receiver_postal_code", 
				//"receiver_state", 
				//"receiver_province", 
				"receiver_country", 
				"item_division", 
				"product_level1_name", 
				"product_level2_name", 
				"product_level3_name", 
				"product_level4_name", 
				"product_level4_code", 
				"model_category", 
				"item_type_desctiption", 
				//"item_weight", 
				//"item_cbm", 
				"sales_channel_high", 
				"sales_channel_low", 
				//"ship_group", 
				//"back_order_hold", 
				//"credit_hold", 
				//"overdue_hold", 
				//"customer_hold", 
				//"payterm_term_hold", 
				//"fp_hold", 
				//"minimum_hold", 
				//"future_hold", 
				//"reserve_hold", 
				//"manual_hold", 
				//"auto_pending_hold", 
				//"sa_hold", 
				//"form_hold", 
				//"bank_collateral_hold", 
				//"insurance_hold", 
				//"partial_flag", 
				//"load_hold_flag", 
				//"inventory_reserved", 
				//"pick_release_qty", 
				//"long_multi_flag", 
				//"so_sa_mapping", 
				//"picking_remark", 
				//"shipping_remark", 
				//"create_employee_name", 
				"create_date", 
				/* "dls_interface", 
				"edi_customer_remark", 
				"sales_recognition_method", 
				"billing_type", 
				"lt_day", 
				"carrier_code", 
				"delivery_number", 
				"manifest_grn_no", 
				"warehouse_job_no", 
				"customer_rad", 
				"others_out_reason", 
				"ship_set_name", 
				"promising_txn_status", 
				"promised_mad", 
				"promised_arrival_date", 
				"promised_ship_date", 
				"initial_promised_arrival_date", 
				"accounting_unit", 
				"acd_original_warehouse", 
				"acd_original_wh_type", 
				"cnjp", 
				"nota_no", 
				"nota_date", 
				"so_status2", 
				"sbp_tax_include", 
				"sbp_tax_exclude", 
				"rrp_tax_include", 
				"rrp_tax_exclude", 
				"so_fap_flag", 
				"so_fap_slot_date", */
			];
			
			$res = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s, $filter, null, null, [["create_date", "asc"]]);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
}
