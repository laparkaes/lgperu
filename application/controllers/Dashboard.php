<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Dashboard extends CI_Controller {

	public function __construct(){
		parent::__construct();
		//if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$data = [
			"main" => "dashboard/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function date_convert($date){//dd/mm/yyyy > yyyy-mm-dd
		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
	}
	
	private function date_convert_2($date){//yyyy/mm/dd hh:mm:ss > yyyy-mm-dd
		return str_replace("/", "-", explode(" ", $date)[0]);
	}
	
	private function arr_trim($arr){
		$new = [];
		foreach($arr as $val) $new[] = trim($val);
		return $new;
	}
	
	//insert Closed order to database
	private function get_record($tablename, $data){
		$record = $this->gen_m->filter($tablename, true, $data);
		if (!$record){
			$this->gen_m->insert($tablename, $data);
			$record = $this->gen_m->filter($tablename, true, $data);
		}
		
		return $record[0];
	}
	
	public function process_sales_order($soi_file, $print_values = false){
		$header = ["Bill To Name","Ship To Name","Model","Order No.","Line No.","Order Type","Line Status","Hold Flag","Ready To Pick","Pick Released","Instock Flag","Order Qty","Unit Selling Price","Sales Amount","Tax Amount","Charge Amount","Line Total","List Price","Original List Price","DC Rate","Currency","DFI Applicable","AAI Applicable","Cancel Qty","Booked Date","Scheduled Cancel Date","Cancel Date","Expire Date","Req. Arrival Date From","Req. Arrival Date To","Req. Ship Date","Shipment Date","Close Date","Line Type","Customer Name","Bill To","Department","Ship To","Ship To Full Name","Store No","Price Condition","Payment Term","Customer PO No.","Customer Po Date","Invoice No.","Invoice Line No.","Invoice Date","Sales Person","Pricing Group","Buying Group","Territory Code","Inventory Org.","Sub- Inventory","Shipping Method","Shipment Priority","Order Source","Order Status","Order Category","Quote Date","Quote Expire Date","Project Code","Comm. Submission No.","PLP Submission No.","BPM Request No.","Consumer Name","Consumer Phone No","Consumer Mobile NO","Receiver Name","Receiver Phone No","Receiver Mobile NO","Receiver Address1","Receiver Address2","Receiver Address3","Receiver City","Receiver City Desc","Receiver County","Receiver Postal code","Receiver State","Receiver Province","Receiver Country","Item Division","PL1 Name","PL2 Name","PL3 Name","PL4 Name","Product Level4 Code","Model Category","Item Type","Item Weight","Item CBM","Sales Channel (High)","Sales Channel (Low)","Ship Group","Back Order Hold","Credit Hold","Overdue Hold","Customer Hold","Payterm Term Hold","FP Hold","Minimum Hold","Future Hold","Reserve Hold","Manual Hold","Auto Pending Hold","S/A Hold","Form Hold","Bank Collateral Hold","Insurance Hold","Partial Flag","Load Hold Flag","Inventory Reserved","Pick Release Qty","Long & Multi Flag","SO-SA Mapping","Picking Remark","Shipping Remark","Create Employee Name","Create Date","Order Date","Expected Arrival Date","Fixed Arrival Date","DLS Interface","Sales Recognition Method","Billing Type","LT DAY","EDI Customer Remark","Carrier Code","Delivery Number","Manifest/ GRN No","Warehouse Job No","Customer RAD","Others Out Reason","Ship Set Name","Promising Txn Status","Promised MAD","Promised Arrival Date","Appointment Date","Promised Ship Date","Initial Promised Arrival Date","Accounting Unit","RAD Unmeet Reason","Install Type","Install Date","ACD Original Warehouse","ACD Original W/H Type","Customer Model","Customer Model Desc","CNPJ","Nota No","Nota Date","Net Price","Interest Amt","SO Status(2)","Back Order Reason","SBP Tax Include","SBP Tax Exclude","RRP Tax Include","RRP Tax Exclude","SO FAP Flag","SO FAP Slot Date","Model  Profit Level","APMS NO","Scheduled Back Date","Customer PO Type","","Revised RSD","Revised RAD From","Revised RAD To","Pick Cancel Manual Hold",];
		$exr_usd_pen = $this->gen_m->filter("exchange_rate", true, ["currency_from" => "USD", "currency_to" => "PEN"], null, null, [["date", "desc"]], 1, 0)[0];
		
		set_time_limit(0);
		$start_time = microtime(true);
		
		$spreadsheet = IOFactory::load($soi_file);
		$sheet = $spreadsheet->getActiveSheet();
		
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();
		
		$rows = $sheet->rangeToArray("A1:{$max_col}1")[0];
		if ($this->header_compare($header, $rows)) for ($row = 2; $row <= $max_row; $row++){
			//init variables
			$rowdata = $sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0];
			$now = date('Y-m-d H:i:s', time());

			$customer = $this->get_record("customer", ["customer" => $rowdata[0], "bill_to_code" => $rowdata[35]]);
			$currency = $this->get_record("currency", ["currency" => $rowdata[20]]);
			$status_id = $this->get_record("order_status", ["status" => $rowdata[152]])->status_id;
			$subsidiary_id = $this->get_record("subsidiary", ["subsidiary" => $rowdata[36]])->subsidiary_id;
			$this->gen_m->update("customer", ["customer_id" => $customer->customer_id], ["subsidiary_id" => $subsidiary_id]);
			
			//order record validation
			$order_no = $rowdata[3];
			$order = $this->gen_m->unique("order_", "order_no", $order_no);
			if ($order){
				//update order status to closed
				//$this->gen_m->update("order_", ["order_id" => $order->order_id], ["status_id" => $status_id, "updated" => $now]);
			}else{
				//create new order
				$s_person = $this->get_record("sales_person", ["name" => $rowdata[47]]);
				$s_channel = $this->get_record("sales_channel", ["channel" => $rowdata[91]]);
				$payment_term = $this->get_record("payment_term", ["term" => $rowdata[41]]);
				$order_category = $this->get_record("order_category", ["category" => $rowdata[57]]);
				
				$order = [
					"status_id"			=> $status_id,
					"customer_id" 		=> $customer->customer_id,
					"sales_channel_id" 	=> $s_channel->channel_id,
					"sales_person_id" 	=> $s_person->person_id,
					"payment_term_id" 	=> $payment_term->term_id,
					"order_category_id"	=> $order_category->category_id,
					"currency_id" 		=> $currency->currency_id,
					"subsidiary_id" 	=> $subsidiary_id,
					"order_no" 			=> $order_no,
					"order_date" 		=> $this->date_convert($rowdata[118]),
					"customer_po_no" 	=> $rowdata[42],
					"updated" 			=> $now,
					"registered" 		=> $now,
				];
				
				$order_id = $this->gen_m->insert("order_", $order);
				$order = $this->gen_m->unique("order_", "order_id", $order_id);
			}
			
			//set order item data
			$line_no = $rowdata[4];
			$address = implode(", ", [$rowdata[70], $rowdata[74]]);
			if (strlen($address) <= 3) $address = "";
			$ship_to = $this->get_record("customer_ship_to", ["ship_to_code" => $rowdata[37], "customer_id" => $customer->customer_id, "address" => $address]);
			$product_level1 = $this->get_record("product_line", ["line" => $rowdata[81], "level" => 1]);
			$product_level2 = $this->get_record("product_line", ["parent_id" => $product_level1->line_id, "line" => $rowdata[82], "level" => 2]);
			$product_level3 = $this->get_record("product_line", ["parent_id" => $product_level2->line_id, "line" => $rowdata[83], "level" => 3]);
			$product_level4 = $this->get_record("product_line", ["parent_id" => $product_level3->line_id, "line" => $rowdata[84], "level" => 4]);
			$division_id = $product_level1 ? $product_level1->parent_id : -1;
			$order_itme_type = $this->get_record("order_itme_type", ["type" => $rowdata[87]]);
			$inventory = $this->get_record("inventory", ["parent_id" => 0, "inventory" => $rowdata[51]]);
			$sub_inventory = $rowdata[52];
			$sub_inventory = $sub_inventory ? $this->get_record("inventory", ["parent_id" => $inventory->inventory_id, "inventory" => $sub_inventory]) : null;
			$product_category = $this->get_record("product_category", ["category" => $rowdata[86]]);
			$product = $this->get_record("product", ["line_id" => $product_level4->line_id, "model" => $rowdata[2]]);
			if (!$product->category_id) $this->gen_m->update("product", ["product_id" => $product->product_id], ["category_id" => $product_category->category_id]);
			
			$total_amount = str_replace(",", "", $rowdata[16]);
			$order_amount = str_replace(",", "", $rowdata[13]);
			
			$order_item_arr = [
				"subsidiary_id" 		=> $subsidiary_id,
				"order_status_id" 		=> $order->status_id,
				"type_id" 				=> $order_itme_type->type_id,
				"ship_to_id" 			=> $ship_to->ship_to_id,
				"division_id" 			=> $division_id,
				"product_l1_line_id" 	=> $product_level1->line_id,
				"product_l2_line_id" 	=> $product_level2->line_id,
				"product_l3_line_id" 	=> $product_level3->line_id,
				"product_l4_line_id" 	=> $product_level4->line_id,
				"product_category_id" 	=> $product_category->category_id,
				"product_id" 			=> $product->product_id,
				"inventory_id" 			=> $inventory ? $inventory->inventory_id : null,
				"sub_inventory_id" 		=> $sub_inventory ? $sub_inventory->inventory_id :null,
				"currency_id" 			=> $currency->currency_id,
				"line_no"				=> $line_no,
				"order_date" 			=> $order->order_date,
				"shipment_date" 		=> $this->date_convert($rowdata[31]),
				"order_qty" 			=> str_replace(",", "", $rowdata[11]),
				"unit_list_price" 		=> str_replace(",", "", $rowdata[17]),
				"unit_selling_price" 	=> str_replace(",", "", $rowdata[12]),
				"total_amount_pen" 		=> $currency->currency === "USD" ? $total_amount * $exr_usd_pen->rate : $total_amount,
				"total_amount" 			=> $total_amount,
				"order_amount_pen" 		=> $currency->currency === "USD" ? $order_amount * $exr_usd_pen->rate : $order_amount,
				"order_amount" 			=> $order_amount,
				"tax_amount" 			=> str_replace(",", "", $rowdata[14]),
				"dc_rate" 				=> (str_replace("%", "", $rowdata[19]) / 100),
			];
			
			$order_item = $this->gen_m->filter("order_item", true, ["order_id" => $order->order_id, "line_no" => $line_no]);
			if ($order_item){
				//update order_item only in case of order is not closed
				$closed_id = $this->get_record("order_status", ["status" => "CLOSED"])->status_id;
				if ($order_item[0]->order_status_id != $closed_id){
					$order_item_arr["updated"] = $now;
					$this->gen_m->update("order_item", ["item_id" => $order_item[0]->item_id], $order_item_arr);	
				}
			}else{
				//new order_item
				$order_item_arr["order_id"] = $order->order_id;
				$order_item_arr["updated"] = $order_item_arr["registered"] = $now;
				$this->gen_m->insert("order_item", $order_item_arr);
			}
			
			
			echo "order ---------- <br/>"; print_r($order); echo "<br/><br/>";
			echo "order item ---------- <br/>"; print_r($order_item_arr); echo "<br/><br/>";
			echo "<br/>";
			echo $row." ------------ ";
			print_r($rowdata);
			echo "<br/><br/><br/><br/>";
			
			if ($row > 5) break;
		}
		
		return (microtime(true) - $start_time);
	}
	
	public function process_closed_order($coi_file, $print_values = false){
		$header = ["Category","AU","Bill To Name","Ship To Name","Model","Order Qty","Unit List  Price","Unit Selling  Price","Total Amount (PEN)","Total Amount","Order Amount (PEN)","Order Amount","Line Charge Amount","Header Charge Amount","Tax Amount","DC Amount","DC Rate","Currency","Book Currency","Inventory Org.","Sub- Inventory","Sales Person","Pricing Group","Buying Group","Territory","Customer Code","Customer Name","Customer Department","Product Level1 Name","Product Level2 Name","Product Level3 Name","Product Level4 Name","Model Category","Item Type Desctiption","Item Weight","Item CBM","Order Date","Shipment Date","LT Days","Closed Date","AAI Flag","HQ AU","Bill To Code","Ship To Code","Ship To Country","Ship To City","Ship To  State","Ship To Zip Code","Payment Term","Sales Channel","Order Source","Order Type","Order No.","Line No.","Line  Type","Invoice No.","Customer PO No.","Project Code","Comm. Submission No.","Product Level4","Price Grade","Consumer Name","Receiver Name","Receiver Country","Receiver Postal Code","Receiver City","Receiver State","Receiver Province","Receiver Address1","Receiver Address2","Receiver Address3","Install Store Code","Install Type","Install Date","Fapiao No.","Fapiao Date","CNPJ","Nota Date","ACD W/H Code","ACD W/H Type","Net Price","Interest Amt","Original List Pirce","PLP  Submission No","Price Condition","Nota Fiscal Serie No","Shipping Method"];
		
		set_time_limit(0);
		$start_time = microtime(true);
		
		$spreadsheet = IOFactory::load($coi_file);
		$sheet = $spreadsheet->getActiveSheet();
		
		$max_row = $sheet->getHighestRow();
		$max_col = $sheet->getHighestColumn();
		
		$rows = $sheet->rangeToArray("A1:{$max_col}1")[0];
		if ($this->header_compare($header, $rows)) for ($row = 2; $row <= $max_row; $row++){
			//initial variables
			$rowdata = $this->arr_trim($sheet->rangeToArray("A{$row}:{$max_col}{$row}")[0]);
			
			$now = date('Y-m-d H:i:s', time());
			$customer = $this->get_record("customer", ["customer" => $rowdata[2], "bill_to_code" => $rowdata[42]]);
			$currency_id = $this->get_record("currency", ["currency" => $rowdata[17]])->currency_id;
			$status_id = $this->get_record("order_status", ["status" => "CLOSED"])->status_id;
			$subsidiary_id = $this->get_record("subsidiary", ["subsidiary" => $rowdata[27]])->subsidiary_id;
			$this->gen_m->update("customer", ["customer_id" => $customer->customer_id], ["subsidiary_id" => $subsidiary_id]);

			//order record validation
			$order_no = $rowdata[52];
			$order = $this->gen_m->unique("order_", "order_no", $order_no);
			if ($order){
				//update order status to closed
				$this->gen_m->update("order_", ["order_id" => $order->order_id], ["status_id" => $status_id, "updated" => $now]);
			}else{
				//make order record with closed status
				$s_person = $this->get_record("sales_person", ["name" => $rowdata[21]]);
				$s_channel = $this->get_record("sales_channel", ["channel" => $rowdata[49]]);
				$payment_term = $this->get_record("payment_term", ["term" => $rowdata[48]]);
				$order_category = $this->get_record("order_category", ["category" => $rowdata[0]]);

				$order = [
					"status_id"			=> $status_id,
					"customer_id" 		=> $customer->customer_id,
					"sales_channel_id" 	=> $s_channel->channel_id,
					"sales_person_id" 	=> $s_person->person_id,
					"payment_term_id" 	=> $payment_term->term_id,
					"order_category_id"	=> $order_category->category_id,
					"currency_id" 		=> $currency_id,
					"subsidiary_id" 	=> $subsidiary_id,
					"order_no" 			=> $order_no,
					"order_date" 		=> $this->date_convert($rowdata[36]),
					"customer_po_no" 	=> $rowdata[56],
					"updated" 			=> $now,
					"registered" 		=> $now,
				];
				
				$order_id = $this->gen_m->insert("order_", $order);
				$order = $this->gen_m->unique("order_", "order_id", $order_id);
			}
			
			if ($print_values){
				echo "<strong>order:</strong><br/>";
				foreach($order as $key => $val) echo $key."=> ".$val."<br/>";
				echo "<br/><br/>";
			}
			
			//set order item data
			$line_no = str_replace("' ", "", $rowdata[53]);
			$address = implode(", ", [$rowdata[68], $rowdata[65]]);
			if (strlen($address) <= 3) $address = "";
			$ship_to = $this->get_record("customer_ship_to", ["ship_to_code" => $rowdata[43], "customer_id" => $customer->customer_id, "address" => $address]);
			$product_level1 = $this->get_record("product_line", ["line" => $rowdata[28], "level" => 1]);
			$product_level2 = $this->get_record("product_line", ["parent_id" => $product_level1->line_id, "line" => $rowdata[29], "level" => 2]);
			$product_level3 = $this->get_record("product_line", ["parent_id" => $product_level2->line_id, "line" => $rowdata[30], "level" => 3]);
			$product_level4 = $this->get_record("product_line", ["parent_id" => $product_level3->line_id, "line" => $rowdata[31], "level" => 4]);
			$division_id = $product_level1 ? $product_level1->parent_id : -1;
			$order_itme_type = $this->get_record("order_itme_type", ["type" => $rowdata[33]]);
			$inventory = $this->get_record("inventory", ["parent_id" => 0, "inventory" => $rowdata[19]]);
			$sub_inventory = $rowdata[20];
			$sub_inventory = $sub_inventory ? $this->get_record("inventory", ["parent_id" => $inventory->inventory_id, "inventory" => $sub_inventory]) : null;
			$invoice = $this->get_record("invoice", ["invoice" => $rowdata[55]]);
			$product_category = $this->get_record("product_category", ["category" => $rowdata[32]]);
			$product = $this->get_record("product", ["line_id" => $product_level4->line_id, "model" => $rowdata[4]]);
			if (!$product->category_id) $this->gen_m->update("product", ["product_id" => $product->product_id], ["category_id" => $product_category->category_id]);
			
			$order_item_arr = [
				"subsidiary_id" 		=> $subsidiary_id,
				"order_status_id" 		=> $order->status_id,
				"type_id" 				=> $order_itme_type->type_id,
				"ship_to_id" 			=> $ship_to->ship_to_id,
				"division_id" 			=> $division_id,
				"product_l1_line_id" 	=> $product_level1->line_id,
				"product_l2_line_id" 	=> $product_level2->line_id,
				"product_l3_line_id" 	=> $product_level3->line_id,
				"product_l4_line_id" 	=> $product_level4->line_id,
				"product_category_id" 	=> $product_category->category_id,
				"product_id" 			=> $product->product_id,
				"inventory_id" 			=> $inventory ? $inventory->inventory_id : null,
				"sub_inventory_id" 		=> $sub_inventory ? $sub_inventory->inventory_id :null,
				"currency_id" 			=> $currency_id,
				"invoice_id" 			=> $invoice->invoice_id,
				"line_no"				=> $line_no,
				"order_date" 			=> $order->order_date,
				"shipment_date" 		=> $this->date_convert($rowdata[37]),
				"closed_date" 			=> $this->date_convert($rowdata[39]),
				"order_qty" 			=> str_replace(",", "", $rowdata[5]),
				"unit_list_price" 		=> str_replace(",", "", $rowdata[6]),
				"unit_selling_price" 	=> str_replace(",", "", $rowdata[7]),
				"total_amount_pen" 		=> str_replace(",", "", $rowdata[8]),
				"total_amount" 			=> str_replace(",", "", $rowdata[9]),
				"order_amount_pen" 		=> str_replace(",", "", $rowdata[10]),
				"order_amount" 			=> str_replace(",", "", $rowdata[11]),
				"tax_amount" 			=> str_replace(",", "", $rowdata[14]),
				"dc_rate" 				=> (str_replace("%", "", $rowdata[16]) / 100),
			];
			
			//order item data record validation
			$order_item = $this->gen_m->filter("order_item", true, ["order_id" => $order->order_id, "line_no" => $line_no]);
			if ($order_item){
				//update order_item
				$order_item_arr["updated"] = $now;
				$this->gen_m->update("order_item", ["item_id" => $order_item[0]->item_id], $order_item_arr);
			}else{
				//new order_item
				$order_item_arr["order_id"] = $order->order_id;
				$order_item_arr["updated"] = $order_item_arr["registered"] = $now;
				$this->gen_m->insert("order_item", $order_item_arr);
			}
			
			if ($print_values){
				echo "<strong>order item:</strong><br/>"; 
				foreach($order_item_arr as $key => $val) echo $key."=> ".$val."<br/>";
				echo "<br/><br/>----------------------------------------------------------------------------------------------------<br/><br/>";
			}
		}
	
		return (microtime(true) - $start_time);
	}
	
	public function import_data(){
		$runtime_coi = $runtime_soi = 0;
		
		//$runtime_coi = $this->process_closed_order('./test_files/dashboard/PSI_Consolidated_Report/Excel_1413601738COI.xls');
		$runtime_soi = $this->process_sales_order('./test_files/dashboard/PSI_Consolidated_Report/Excel_1413685085SOI2.xls', true);
		
		echo "closed order runtime: ".number_format($runtime_coi + $runtime_soi, 2)." seconds";
	}
	//end Closed order
	
	public function header_compare($h1, $h2){
		$res = true;
		
		$h1_qty = count($h1);
		$h2_qty = count($h2);
		
		if ($h1_qty == $h2_qty){
			for($i = 0; $i < $h1_qty; $i++) $res = ($res and (trim($h1[$i]) === trim($h2[$i])));
		}else $res = false;
		
		return $res;
	}
	
	public function h_cmp(){
		$header_coi = ["Category","AU","Bill To Name","Ship To Name","Model","Order Qty","Unit List  Price","Unit Selling  Price","Total Amount (PEN)","Total Amount","Order Amount (PEN)","Order Amount","Line Charge Amount","Header Charge Amount","Tax Amount","DC Amount","DC Rate","Currency","Book Currency","Inventory Org.","Sub- Inventory","Sales Person","Pricing Group","Buying Group","Territory","Customer Code","Customer Name","Customer Department","Product Level1 Name","Product Level2 Name","Product Level3 Name","Product Level4 Name","Model Category","Item Type Desctiption","Item Weight","Item CBM","Order Date","Shipment Date","LT Days","Closed Date","AAI Flag","HQ AU","Bill To Code","Ship To Code","Ship To Country","Ship To City","Ship To  State","Ship To Zip Code","Payment Term","Sales Channel","Order Source","Order Type","Order No.","Line No.","Line  Type","Invoice No.","Customer PO No.","Project Code","Comm. Submission No.","Product Level4","Price Grade","Consumer Name","Receiver Name","Receiver Country","Receiver Postal Code","Receiver City","Receiver State","Receiver Province","Receiver Address1","Receiver Address2","Receiver Address3","Install Store Code","Install Type","Install Date","Fapiao No.","Fapiao Date","CNPJ","Nota Date","ACD W/H Code","ACD W/H Type","Net Price","Interest Amt","Original List Pirce","PLP  Submission No","Price Condition","Nota Fiscal Serie No","Shipping Method"];
		$header_soi = ["Bill To Name","Ship To Name","Model","Order No.","Line No.","Order Type","Line Status","Hold Flag","Ready To Pick","Pick Released","Instock Flag","Order Qty","Unit Selling Price","Sales Amount","Tax Amount","Charge Amount","Line Total","List Price","Original List Price","DC Rate","Currency","DFI Applicable","AAI Applicable","Cancel Qty","Booked Date","Scheduled Cancel Date","Cancel Date","Expire Date","Req. Arrival Date From","Req. Arrival Date To","Req. Ship Date","Shipment Date","Close Date","Line Type","Customer Name","Bill To","Department","Ship To","Ship To Full Name","Store No","Price Condition","Payment Term","Customer PO No.","Customer Po Date","Invoice No.","Invoice Line No.","Invoice Date","Sales Person","Pricing Group","Buying Group","Territory Code","Inventory Org.","Sub- Inventory","Shipping Method","Shipment Priority","Order Source","Order Status","Order Category","Quote Date","Quote Expire Date","Project Code","Comm. Submission No.","PLP Submission No.","BPM Request No.","Consumer Name","Consumer Phone No","Consumer Mobile NO","Receiver Name","Receiver Phone No","Receiver Mobile NO","Receiver Address1","Receiver Address2","Receiver Address3","Receiver City","Receiver City Desc","Receiver County","Receiver Postal code","Receiver State","Receiver Province","Receiver Country","Item Division","PL1 Name","PL2 Name","PL3 Name","PL4 Name","Product Level4 Code","Model Category","Item Type","Item Weight","Item CBM","Sales Channel (High)","Sales Channel (Low)","Ship Group","Back Order Hold","Credit Hold","Overdue Hold","Customer Hold","Payterm Term Hold","FP Hold","Minimum Hold","Future Hold","Reserve Hold","Manual Hold","Auto Pending Hold","S/A Hold","Form Hold","Bank Collateral Hold","Insurance Hold","Partial Flag","Load Hold Flag","Inventory Reserved","Pick Release Qty","Long & Multi Flag","SO-SA Mapping","Picking Remark","Shipping Remark","Create Employee Name","Create Date","Order Date","Expected Arrival Date","Fixed Arrival Date","DLS Interface","Sales Recognition Method","Billing Type","LT DAY","EDI Customer Remark","Carrier Code","Delivery Number","Manifest/ GRN No","Warehouse Job No","Customer RAD","Others Out Reason","Ship Set Name","Promising Txn Status","Promised MAD","Promised Arrival Date","Appointment Date","Promised Ship Date","Initial Promised Arrival Date","Accounting Unit","RAD Unmeet Reason","Install Type","Install Date","ACD Original Warehouse","ACD Original W/H Type","Customer Model","Customer Model Desc","CNPJ","Nota No","Nota Date","Net Price","Interest Amt","SO Status(2)","Back Order Reason","SBP Tax Include","SBP Tax Exclude","RRP Tax Include","RRP Tax Exclude","SO FAP Flag","SO FAP Slot Date","Model  Profit Level","APMS NO","Scheduled Back Date","Customer PO Type","","Revised RSD","Revised RAD From","Revised RAD To","Pick Cancel Manual Hold",];
		
		$spreadsheet_coi = IOFactory::load('./upload/so_soi1.xls');
		$sheet_coi = $spreadsheet_coi->getActiveSheet();
		
		$max_row = $sheet_coi->getHighestRow();
		$max_col = $sheet_coi->getHighestColumn();
		
		//header work
		$rows = $sheet_coi->rangeToArray("A1:{$max_col}1")[0];
		
		print_r($rows);
		echo "<br/><br/><br/>";
		print_r($header_soi);
		echo "<br/><br/><br/>";
		
		echo $this->header_compare($header_soi, $rows);
	}
	
	public function set_coi($filename, $sheetname, $filename_coi){
		$header = ["Category","AU","Bill To Name","Ship To Name","Model","Order Qty","Unit List  Price","Unit Selling  Price","Total Amount (PEN)","Total Amount","Order Amount (PEN)","Order Amount","Line Charge Amount","Header Charge Amount","Tax Amount","DC Amount","DC Rate","Currency","Book Currency","Inventory Org.","Sub- Inventory","Sales Person","Pricing Group","Buying Group","Territory","Customer Code","Customer Name","Customer Department","Product Level1 Name","Product Level2 Name","Product Level3 Name","Product Level4 Name","Model Category","Item Type Desctiption","Item Weight","Item CBM","Order Date","Shipment Date","LT Days","Closed Date","AAI Flag","HQ AU","Bill To Code","Ship To Code","Ship To Country","Ship To City","Ship To  State","Ship To Zip Code","Payment Term","Sales Channel","Order Source","Order Type","Order No.","Line No.","Line  Type","Invoice No.","Customer PO No.","Project Code","Comm. Submission No.","Product Level4","Price Grade","Consumer Name","Receiver Name","Receiver Country","Receiver Postal Code","Receiver City","Receiver State","Receiver Province","Receiver Address1","Receiver Address2","Receiver Address3","Install Store Code","Install Type","Install Date","Fapiao No.","Fapiao Date","CNPJ","Nota Date","ACD W/H Code","ACD W/H Type","Net Price","Interest Amt","Original List Pirce","PLP  Submission No","Price Condition","Nota Fiscal Serie No","Shipping Method"];
		
		$spreadsheet = IOFactory::load($filename);
		$sheet = $spreadsheet->getSheetByName($sheetname);
		
		$spreadsheet_coi = IOFactory::load($filename_coi);
		$sheet_coi = $spreadsheet_coi->getActiveSheet();
		
		$max_row = $sheet_coi->getHighestRow();
		$max_col = $sheet_coi->getHighestColumn();
		
		//load header and compare to continue work
		$rows = $sheet_coi->rangeToArray("A1:{$max_col}1")[0];
		if ($this->header_compare($header, $rows)){
			//header work
			$rows = array_merge($rows, ["Column1", "", "Fixed Order Date", "Fixed Ship Date", "", "Fixed Closed Date"]);
			foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), 1)->setValue($val);
			
			//index of array to remove commas
			$nums = [5,6,7,8,9,10,11,12,13,14,15,82];
			
			for($row = 2; $row <= $max_row; $row++){
				$rows = $sheet_coi->rangeToArray("A{$row}:{$max_col}{$row}")[0];
				
				//dates convert to 20240422 format
				$rows = array_merge($rows, ["", "", str_replace("-", "", $this->date_convert($rows[36])), str_replace("-", "", $this->date_convert($rows[37])), "", str_replace("-", "", $this->date_convert($rows[39]))]);
				
				//remove commas of numbers
				foreach($nums as $n) $rows[$n] = str_replace(",", "", $rows[$n]);
				
				//write to merged file
				foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), $row)->setValue($val);
			}
		}else $sheet->getCellByColumnAndRow(1, 1)->setValue("Wrong file.");
		
		$writer = new Xlsx($spreadsheet);
		$writer->save("./upload/so__espr.xlsx");
	}
	
	public function set_soi($filename, $sheetname, $filename_coi){
		$header = ["Bill To Name","Ship To Name","Model","Order No.","Line No.","Order Type","Line Status","Hold Flag","Ready To Pick","Pick Released","Instock Flag","Order Qty","Unit Selling Price","Sales Amount","Tax Amount","Charge Amount","Line Total","List Price","Original List Price","DC Rate","Currency","DFI Applicable","AAI Applicable","Cancel Qty","Booked Date","Scheduled Cancel Date","Cancel Date","Expire Date","Req. Arrival Date From","Req. Arrival Date To","Req. Ship Date","Shipment Date","Close Date","Line Type","Customer Name","Bill To","Department","Ship To","Ship To Full Name","Store No","Price Condition","Payment Term","Customer PO No.","Customer Po Date","Invoice No.","Invoice Line No.","Invoice Date","Sales Person","Pricing Group","Buying Group","Territory Code","Inventory Org.","Sub- Inventory","Shipping Method","Shipment Priority","Order Source","Order Status","Order Category","Quote Date","Quote Expire Date","Project Code","Comm. Submission No.","PLP Submission No.","BPM Request No.","Consumer Name","Consumer Phone No","Consumer Mobile NO","Receiver Name","Receiver Phone No","Receiver Mobile NO","Receiver Address1","Receiver Address2","Receiver Address3","Receiver City","Receiver City Desc","Receiver County","Receiver Postal code","Receiver State","Receiver Province","Receiver Country","Item Division","PL1 Name","PL2 Name","PL3 Name","PL4 Name","Product Level4 Code","Model Category","Item Type","Item Weight","Item CBM","Sales Channel (High)","Sales Channel (Low)","Ship Group","Back Order Hold","Credit Hold","Overdue Hold","Customer Hold","Payterm Term Hold","FP Hold","Minimum Hold","Future Hold","Reserve Hold","Manual Hold","Auto Pending Hold","S/A Hold","Form Hold","Bank Collateral Hold","Insurance Hold","Partial Flag","Load Hold Flag","Inventory Reserved","Pick Release Qty","Long & Multi Flag","SO-SA Mapping","Picking Remark","Shipping Remark","Create Employee Name","Create Date","Order Date","Expected Arrival Date","Fixed Arrival Date","DLS Interface","Sales Recognition Method","Billing Type","LT DAY","EDI Customer Remark","Carrier Code","Delivery Number","Manifest/ GRN No","Warehouse Job No","Customer RAD","Others Out Reason","Ship Set Name","Promising Txn Status","Promised MAD","Promised Arrival Date","Appointment Date","Promised Ship Date","Initial Promised Arrival Date","Accounting Unit","RAD Unmeet Reason","Install Type","Install Date","ACD Original Warehouse","ACD Original W/H Type","Customer Model","Customer Model Desc","CNPJ","Nota No","Nota Date","Net Price","Interest Amt","SO Status(2)","Back Order Reason","SBP Tax Include","SBP Tax Exclude","RRP Tax Include","RRP Tax Exclude","SO FAP Flag","SO FAP Slot Date","Model  Profit Level","APMS NO","Scheduled Back Date","Customer PO Type","","Revised RSD","Revised RAD From","Revised RAD To","Pick Cancel Manual Hold",];

		$spreadsheet = IOFactory::load($filename);
		$sheet = $spreadsheet->getSheetByName($sheetname);
		
		$spreadsheet_coi = IOFactory::load($filename_coi);
		$sheet_coi = $spreadsheet_coi->getActiveSheet();
		
		$max_row = $sheet_coi->getHighestRow();
		$max_col = $sheet_coi->getHighestColumn();
		
		$rows = $sheet_coi->rangeToArray("A1:{$max_col}1")[0];
		if ($this->header_compare($header, $rows)){
			//header work
			$rows = array_merge($rows, ["Column1", "Fixed PO Date", "Fixed Create Date", "Fixed RAD Date", "Fixed Ship Date"]);
			foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), 1)->setValue($val);
			
			//index of array to remove commas
			$nums = [11,12,13,14,15,16,17,18,23,154,155,156,157];
			
			for($row = 2; $row <= $max_row; $row++){
				$rows = $sheet_coi->rangeToArray("A{$row}:{$max_col}{$row}")[0];
				
				//dates convert to 20240422 format
				$rows = array_merge($rows, ["", str_replace("-", "", $this->date_convert($rows[43])), str_replace("-", "", $this->date_convert($rows[117])), str_replace("-", "", $this->date_convert_2($rows[130])), str_replace("-", "", $this->date_convert($rows[31]))]);
				
				//remove commas of numbers
				foreach($nums as $n) $rows[$n] = str_replace(",", "", $rows[$n]);
				
				//write to merged file
				foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), $row)->setValue($val);
			}
		}else $sheet->getCellByColumnAndRow(1, 1)->setValue("Wrong file.");
		
		$writer = new Xlsx($spreadsheet);
		$writer->save("./upload/so__espr.xlsx");
	}
	
	public function espr_merge_order_inquiry(){
		set_time_limit(0);
		$start_time = microtime(true);
		
		$type = "error"; $msg = $url = "";
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
		];
		$this->load->library('upload', $config);

		$name_coi = $name_soi1 = $name_soi2 = "";
		
		$config['file_name'] = 'so_coi';
		$this->upload->initialize($config);
		if ($this->upload->do_upload('file_coi')){
			$data = $this->upload->data();
			$name_coi = $data['orig_name'];
		}
		
		$config['file_name'] = 'so_soi1';
		$this->upload->initialize($config);
		if ($this->upload->do_upload('file_soi1')){
			$data = $this->upload->data();
			$name_soi1 = $data['orig_name'];
		}
		
		$config['file_name'] = 'so_soi2';
		$this->upload->initialize($config);
		if ($this->upload->do_upload('file_soi2')){
			$data = $this->upload->data();
			$name_soi2 = $data['orig_name'];
		}
		
		if ($name_coi and $name_soi1 and $name_soi2){
			//start to create excel file
			$spreadsheet = new Spreadsheet();
			$spreadsheet->removeSheetByIndex(0);
			
			//worksheets setting
			$spreadsheet->addSheet(new Worksheet($spreadsheet, 'Closed'));
			$spreadsheet->addSheet(new Worksheet($spreadsheet, 'SOI 1'));
			$spreadsheet->addSheet(new Worksheet($spreadsheet, 'SOI 2'));
			
			$writer = new Xlsx($spreadsheet);
			$writer->save("./upload/so__espr.xlsx");
			
			//copy COI content to excel file
			$this->set_coi('./upload/so__espr.xlsx', 'Closed', './upload/'.$name_coi);
			
			//copy SOI 1 content to excel file
			$this->set_soi('./upload/so__espr.xlsx', 'SOI 1', './upload/'.$name_soi1);
			
			//copy SOI 2 content to excel file
			$this->set_soi('./upload/so__espr.xlsx', 'SOI 2', './upload/'.$name_soi2);
			
			$type = "success";
			$msg = "Merged file download will be started. (".number_format(microtime(true) - $start_time, 2)." sec)";
			$url = base_url()."upload/so__espr.xlsx";
		}else $msg = "Select all files: COI, SOI 1 and SOI 2.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}

	public function espr_file(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$data = [
			"main" => "dashboard/espr_file",
		];
		
		$this->load->view('layout', $data);
	}
}
