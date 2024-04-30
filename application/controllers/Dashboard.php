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
	
	private function date_convert($date){
		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
	}
	
	private function get_record($tablename, $data){
		$record = $this->gen_m->filter($tablename, true, $data);
		if (!$record){
			$this->gen_m->insert($tablename, $data);
			$record = $this->gen_m->filter($tablename, true, $data);
		}
		
		return $record[0];
	}
	
	public function process_closed_order($coi_file, $print_values = false){
		set_time_limit(0);
		$start_time = microtime(true);
		
		$spreadsheet = IOFactory::load($coi_file);
		//$spreadsheet->setActiveSheetIndex(0);
		//$spreadsheet->setActiveSheetIndexByName('My Second Sheet');
		$sheet = $spreadsheet->getActiveSheet();
		
		$closed_months = [];
		
		$max_row = $sheet->getHighestRow();
		
		for ($row = 2; $row <= $max_row; $row++){
			//initial variables
			$now = date('Y-m-d H:i:s', time());
			$customer = $this->get_record("customer", ["customer" => trim($sheet->getCellByColumnAndRow(3, $row)->getValue()), "bill_to_code" => trim($sheet->getCellByColumnAndRow(43, $row)->getValue())]);
			
			$currency_id = $this->get_record("currency", ["currency" => trim($sheet->getCellByColumnAndRow(18, $row)->getValue())])->currency_id;
			$status_id = $this->get_record("order_status", ["status" => "Closed"])->status_id;
			$subsidiary_id = $this->get_record("subsidiary", ["subsidiary" => trim($sheet->getCellByColumnAndRow(28, $row)->getValue())])->subsidiary_id;
			$this->gen_m->update("customer", ["customer_id" => $customer->customer_id], ["subsidiary_id" => $subsidiary_id]);

			//order record validation
			$order_no = trim($sheet->getCellByColumnAndRow(53, $row)->getValue());
			$order = $this->gen_m->unique("order_", "order_no", $order_no);
			if ($order){
				//update order status to closed
				$this->gen_m->update("order_", ["order_id" => $order->order_id], ["status_id" => $status_id, "updated" => $now]);
			}else{
				//make order record with closed status
				$s_person = $this->get_record("sales_person", ["name" => trim($sheet->getCellByColumnAndRow(22, $row)->getValue())]);
				$s_channel = $this->get_record("sales_channel", ["channel" => trim($sheet->getCellByColumnAndRow(50, $row)->getValue())]);
				$payment_term = $this->get_record("payment_term", ["term" => trim($sheet->getCellByColumnAndRow(49, $row)->getValue())]);
				$order_category = $this->get_record("order_category", ["category" => trim($sheet->getCellByColumnAndRow(1, $row)->getValue())]);

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
					"order_date" 		=> $this->date_convert($sheet->getCellByColumnAndRow(37, $row)->getValue()),
					"customer_po_no" 	=> trim($sheet->getCellByColumnAndRow(57, $row)->getValue()),
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
			$line_no = str_replace("' ", "", trim($sheet->getCellByColumnAndRow(54, $row)->getValue()));
			$closed_date = $this->date_convert($sheet->getCellByColumnAndRow(40, $row)->getValue());
			$closed_month = date("Y-m", strtotime($closed_date));
			if (!in_array($closed_month, $closed_months)) $closed_months[] = $closed_month;
			
			$address = implode(", ", [trim($sheet->getCellByColumnAndRow(69, $row)->getValue()), trim($sheet->getCellByColumnAndRow(66, $row)->getValue())]);
			if (strlen($address) <= 3) $address = "";
			$ship_to = $this->get_record("customer_ship_to", ["ship_to_code" => trim($sheet->getCellByColumnAndRow(44, $row)->getValue()), "customer_id" => $customer->customer_id, "address" => $address]);
			$product_level1 = $this->get_record("product_line", ["line" => trim($sheet->getCellByColumnAndRow(29, $row)->getValue()), "level" => 1]);
			$product_level2 = $this->get_record("product_line", ["parent_id" => $product_level1->line_id, "line" => trim($sheet->getCellByColumnAndRow(30, $row)->getValue()), "level" => 2]);
			$product_level3 = $this->get_record("product_line", ["parent_id" => $product_level2->line_id, "line" => trim($sheet->getCellByColumnAndRow(31, $row)->getValue()), "level" => 3]);
			$product_level4 = $this->get_record("product_line", ["parent_id" => $product_level3->line_id, "line" => trim($sheet->getCellByColumnAndRow(32, $row)->getValue()), "level" => 4]);
			$division_id = $product_level1 ? $product_level1->parent_id : -1;
			$order_itme_type = $this->get_record("order_itme_type", ["type" => trim($sheet->getCellByColumnAndRow(34, $row)->getValue())]);
			$inventory = $this->get_record("inventory", ["parent_id" => 0, "inventory" => trim($sheet->getCellByColumnAndRow(20, $row)->getValue())]);
			$sub_inventory = trim($sheet->getCellByColumnAndRow(21, $row)->getValue());
			$sub_inventory = $sub_inventory ? $this->get_record("inventory", ["parent_id" => $inventory->inventory_id, "inventory" => $sub_inventory]) : null;
			$invoice = $this->get_record("invoice", ["invoice" => trim($sheet->getCellByColumnAndRow(56, $row)->getValue())]);
			$product_category = $this->get_record("product_category", ["category" => trim($sheet->getCellByColumnAndRow(33, $row)->getValue())]);
			$product = $this->get_record("product", ["line_id" => $product_level4->line_id, "model" => trim($sheet->getCellByColumnAndRow(5, $row)->getValue())]);
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
				"shipment_date" 		=> $this->date_convert($sheet->getCellByColumnAndRow(38, $row)->getValue()),
				"closed_date" 			=> $closed_date,
				"order_qty" 			=> trim($sheet->getCellByColumnAndRow(6, $row)->getValue()),
				"unit_list_price" 		=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(7, $row)->getValue())),
				"unit_selling_price" 	=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(8, $row)->getValue())),
				"total_amount_pen" 		=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(9, $row)->getValue())),
				"total_amount" 			=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(10, $row)->getValue())),
				"order_amount_pen" 		=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(11, $row)->getValue())),
				"order_amount" 			=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(12, $row)->getValue())),
				"tax_amount" 			=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(15, $row)->getValue())),
				"dc_amount"				=> str_replace(",", "", trim($sheet->getCellByColumnAndRow(16, $row)->getValue())),
				"dc_rate" 				=> str_replace("%", "", trim($sheet->getCellByColumnAndRow(17, $row)->getValue())) / 100,
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
		
		//update monthly resume values - will be developed
		foreach($closed_months as $month){
			$from = date("Y-m-1", strtotime($month));
			$to = date("Y-m-t", strtotime($month));
			
			//echo $from." ".$to."<br/>";
		}
		
		return (microtime(true) - $start_time);
	}
	
	public function import_data(){
		$runtime_coi = $this->process_closed_order('./test_files/dashboard/PSI_Consolidated_Report/Excel_1413601738COI.xls');
		
		echo "closed order runtime: ".number_format($runtime_coi, 2)." seconds";
	}
	
	public function test(){
		set_time_limit(0);
		$start_time = microtime(true);
		
		//make blank excel file
		$spreadsheet = new Spreadsheet();
		$spreadsheet->removeSheetByIndex(0);
		
		//worksheets setting
		$spreadsheet->addSheet(new Worksheet($spreadsheet, 'Closed'));
		$spreadsheet->addSheet(new Worksheet($spreadsheet, 'SOI 1'));
		$spreadsheet->addSheet(new Worksheet($spreadsheet, 'SOI 2'));
		
		//COI -start
		$sheet = $spreadsheet->getSheetByName('Closed');
		$sheet = $spreadsheet->getActiveSheet();
		
		$spreadsheet_coi = IOFactory::load('./test_files/dashboard/PSI_Consolidated_Report/Excel_1413601738COI.xls');
		$sheet_coi = $spreadsheet_coi->getActiveSheet();
		
		$max_row = $sheet_coi->getHighestRow();
		$max_col = $sheet_coi->getHighestColumn();
		
		//header work
		$rows = $sheet_coi->rangeToArray("A1:{$max_col}1")[0];
		$rows = array_merge($rows, ["Column1", "", "Fixed Order Date", "Fixed Ship Date", "", "Fixed Closed Date"]);
		foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), 1)->setValue($val);
		//echo "1<br/><br/>----<br/><br/>"; print_r($rows); echo "<br/><br/>";
		
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
		
			//echo $row."<br/><br/>----<br/><br/>"; print_r($rows); echo "<br/><br/>";
		}
		//COI - end
		
		
		//$sheet = $spreadsheet->getSheetByName('Worksheet 1');
		//$sheet = $spreadsheet->getActiveSheet();
		
		
		//$spreadsheet->setActiveSheetIndexByName('My Second Sheet');
		
		//save excel file to a temporary directory
		$file_path = './upload/';
		$writer = new Xlsx($spreadsheet);
		$writer->save($file_path."dashboard_.xlsx");
		
		//runtime print
		echo (microtime(true) - $start_time);
	}
}
