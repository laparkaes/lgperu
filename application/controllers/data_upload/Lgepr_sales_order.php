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
		
		$o = [["create_date", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
		
		$data = [
			"sales_orders"	=> $this->gen_m->filter("lgepr_sales_order", false, null, null, null, $o, 5000),
			"main" 			=> "data_upload/lgepr_sales_order/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function update_model_category(){
		//select fields
		$s = ["model_category", "model", "product_level4_name", "product_level4_code"];
		
		//get ger records with model category group by product lvl4 code
		$gerps_aux = $this->gen_m->filter_select("lgepr_sales_order", false, $s, ["model_category !=" => null], null, null, [["product_level4_code", "desc"]], null, null, "product_level4_code");
		
		//set mapping array to assign model category
		$mapping = [];
		foreach($gerps_aux as $g){
			$mapping[substr($g->product_level4_code, 0, 4)] = $g->model_category;
			$mapping[substr($g->product_level4_code, 0, 2)] = $g->model_category;
		}
		
		//get gerp records without model category group by product lvl4 code
		$w = ["model_category =" => null, "product_level4_code !=" => "ZZZZZZZZ", "order_status !=" => "Cancelled", "line_status !=" => "Cancelled"];
		$gerps = $this->gen_m->filter_select("lgepr_sales_order", false, $s, $w, null, null, [["product_level4_code", "desc"]], null, null, "product_level4_code");
		
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
			
			if ($g->model_category) $this->gen_m->update("lgepr_sales_order", ["product_level4_code" => $g->product_level4_code], ["model_category" => $g->model_category]);
		}
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
			
			//db fields
			$vars = ["bill_to_name",  "ship_to_name",  "model",  "order_no",  "line_no",  "order_type",  "line_status",  "hold_flag",  "ready_to_pick",  "pick_released",  "instock_flag",  "ordered_qty",  "unit_selling_price",  "sales_amount",  "tax_amount",  "charge_amount",  "line_total",  "list_price",  "original_list_price",  "dc_rate",  "currency",  "dfi_applicable",  "aai_applicable",  "cancel_qty",  "booked_date",  "scheduled_cancel_date",  "expire_date",  "req_arrival_date_from",  "req_arrival_date_to",  "req_ship_date",  "shipment_date",  "close_date",  "line_type",  "customer_name",  "bill_to",  "customer_department",  "ship_to",  "store_no",  "price_condition",  "payment_term",  "customer_po_no",  "customer_po_date",  "invoice_no",  "invoice_line_no",  "invoice_date",  "sales_person",  "pricing_group",  "buying_group",  "inventory_org",  "sub_inventory",  "shipping_method",  "shipment_priority",  "order_source",  "order_status",  "order_category",  "quote_date",  "quote_expire_date",  "project_code",  "comm_submission_no",  "plp_submission_no",  "bpm_request_no",  "consumer_name",  "receiver_name",  "consumer_phone_no",  "consumermobile_no",  "receiver_phone_no",  "receiver_mobile_no",  "receiver_address1",  "receiver_address2",  "receiver_address3",  "receiver_city",  "receiver_city_desc",  "receiver_county",  "receiver_postal_code",  "receiver_state",  "receiver_province",  "receiver_country",  "item_division",  "product_level1_name",  "product_level2_name",  "product_level3_name",  "product_level4_name",  "product_level4_code",  "model_category",  "item_type_desctiption",  "item_weight",  "item_cbm",  "sales_channel_high",  "sales_channel_low",  "ship_group",  "back_order_hold",  "credit_hold",  "overdue_hold",  "customer_hold",  "payterm_term_hold",  "fp_hold",  "minimum_hold",  "future_hold",  "reserve_hold",  "manual_hold",  "auto_pending_hold",  "sa_hold",  "form_hold",  "bank_collateral_hold",  "insurance_hold",  "partial_flag",  "load_hold_flag",  "inventory_reserved",  "pick_release_qty",  "long_multi_flag",  "so_sa_mapping",  "picking_remark",  "shipping_remark",  "create_employee_name",  "create_date",  "dls_interface",  "edi_customer_remark",  "sales_recognition_method",  "billing_type",  "lt_day",  "carrier_code",  "delivery_number",  "manifest_grn_no",  "warehouse_job_no",  "customer_rad",  "others_out_reason",  "ship_set_name",  "promising_txn_status",  "promised_mad",  "promised_arrival_date",  "promised_ship_date",  "initial_promised_arrival_date",  "accounting_unit",  "acd_original_warehouse",  "acd_original_wh_type",  "cnjp",  "nota_no",  "nota_date",  "so_status2",  "sbp_tax_include",  "sbp_tax_exclude",  "rrp_tax_include",  "rrp_tax_exclude",  "so_fap_flag",  "so_fap_slot_date"];
			
			$rows = $order_lines = [];
			$records = 0;
			
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
				
				$this->gen_m->delete("lgepr_sales_order", ["order_line" => $row["order_line"]]);
				
				if (count($order_lines) > 1000){
					//echo "Inserting ======================= <br/>"; print_r($order_lines);
					
					//remove
					$this->gen_m->delete_in("lgepr_sales_order", "order_line", $order_lines);
					//echo "<br/><br/>"; echo $this->db->last_query(); echo "<br/><br/>";
					
					//insert
					$records += $this->gen_m->insert_m("lgepr_sales_order", $rows);
					
					$rows = $order_lines = [];
				}
				
				$rows[] = $row;
				$order_lines[] = $row["order_line"];
			}
			
			if ($rows){
				//echo "Inserting ======================= <br/>"; print_r($order_lines);
				
				//remove
				$this->gen_m->delete_in("lgepr_sales_order", "order_line", $order_lines);
				//echo "<br/><br/>"; echo $this->db->last_query(); echo "<br/><br/>";
					
				//insert
				$records += $this->gen_m->insert_m("lgepr_sales_order", $rows);
			}
			
			$msg = "Records: ".number_format($records).".<br/><br/>".number_Format(microtime(true) - $start_time, 2)." secs";
		}else $msg = null;
		
		//return $msg;
		echo $msg;
	}
	
	public function debug(){
		echo $this->process();
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
				$msg = "File upload is completed. DB work will be started.";
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
