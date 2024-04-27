<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
		else return "";
	}
	
	public function import_data(){
		$spreadsheet = IOFactory::load('./test_files/dashboard/dashboard_test.xls');
		$spreadsheet->setActiveSheetIndex(0);
		$sheet = $spreadsheet->getActiveSheet();
		
		$max_row = $sheet->getHighestRow();
		
		for ($row = 2; $row <= $max_row; $row++){
			$category = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());
			$bill_to_name = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());
			$ship_to_name = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());
			$model = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());
			$order_qty = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());
			$unit_list_price = trim($sheet->getCellByColumnAndRow(7, $row)->getValue());
			$unit_selling_price = trim($sheet->getCellByColumnAndRow(8, $row)->getValue());
			$total_amount_pen = trim($sheet->getCellByColumnAndRow(9, $row)->getValue());
			$total_amount = trim($sheet->getCellByColumnAndRow(10, $row)->getValue());
			$order_amount_pen = trim($sheet->getCellByColumnAndRow(11, $row)->getValue());
			$order_amount = trim($sheet->getCellByColumnAndRow(12, $row)->getValue());
			$tax_amount = trim($sheet->getCellByColumnAndRow(15, $row)->getValue());
			$dc_amount = trim($sheet->getCellByColumnAndRow(16, $row)->getValue());
			$dc_rate = trim($sheet->getCellByColumnAndRow(17, $row)->getValue());
			$currency = trim($sheet->getCellByColumnAndRow(18, $row)->getValue());
			//$currency_book = trim($sheet->getCellByColumnAndRow(19, $row)->getValue());
			$inventory_org = trim($sheet->getCellByColumnAndRow(20, $row)->getValue());
			$sub_inventory = trim($sheet->getCellByColumnAndRow(21, $row)->getValue());
			$sales_person_name = trim($sheet->getCellByColumnAndRow(22, $row)->getValue());
			//$customer_code = trim($sheet->getCellByColumnAndRow(26, $row)->getValue());
			//$customer_name = trim($sheet->getCellByColumnAndRow(27, $row)->getValue());
			$customer_department = trim($sheet->getCellByColumnAndRow(28, $row)->getValue());
			$product_level1_name = trim($sheet->getCellByColumnAndRow(29, $row)->getValue());
			$product_level2_name = trim($sheet->getCellByColumnAndRow(30, $row)->getValue());
			$product_level3_name = trim($sheet->getCellByColumnAndRow(31, $row)->getValue());
			$product_level4_name = trim($sheet->getCellByColumnAndRow(32, $row)->getValue());
			$model_category = trim($sheet->getCellByColumnAndRow(33, $row)->getValue());
			$item_type_desctiption = trim($sheet->getCellByColumnAndRow(34, $row)->getValue());
			$order_date = $this->date_convert($sheet->getCellByColumnAndRow(37, $row)->getValue());
			$shipment_date = $this->date_convert($sheet->getCellByColumnAndRow(38, $row)->getValue());
			$closed_date = $this->date_convert($sheet->getCellByColumnAndRow(40, $row)->getValue());
			$bill_to_code = trim($sheet->getCellByColumnAndRow(43, $row)->getValue());
			$ship_to_code = trim($sheet->getCellByColumnAndRow(44, $row)->getValue());
			$payment_term = trim($sheet->getCellByColumnAndRow(49, $row)->getValue());
			$sales_channel = trim($sheet->getCellByColumnAndRow(50, $row)->getValue());
			$order_no = trim($sheet->getCellByColumnAndRow(53, $row)->getValue());
			$invoice_no = trim($sheet->getCellByColumnAndRow(56, $row)->getValue());
			$customer_po_no = trim($sheet->getCellByColumnAndRow(57, $row)->getValue());

			//echo $row." ***** ".$category." ***** ".$bill_to_name." ***** ".$ship_to_name." ***** ".$model." ***** ".$order_qty." ***** ".$unit_list_price." ***** ".$unit_selling_price." ***** ".$total_amount_pen." ***** ".$total_amount." ***** ".$order_amount_pen." ***** ".$order_amount." ***** ".$tax_amount." ***** ".$dc_amount." ***** ".$dc_rate." ***** ".$currency." ***** ".$book_currency." ***** ".$inventory_org." ***** ".$sub_inventory." ***** ".$sales_person_name." ***** ".$customer_code." ***** ".$customer_name." ***** ".$customer_department." ***** ".$product_level1_name." ***** ".$product_level2_name." ***** ".$product_level3_name." ***** ".$product_level4_name." ***** ".$model_category." ***** ".$item_type_desctiption." ***** ".$order_date." ***** ".$shipment_date." ***** ".$closed_date." ***** ".$bill_to_code." ***** ".$ship_to_code." ***** ".$payment_term." ***** ".$sales_channel." ***** ".$order_no." ***** ".$invoice_no." ***** ".$customer_po_no."<br/>";
			echo $row." *****<br/>";

			//set order
			$customer = $this->gen_m->unique("customer", "bill_to_code", $bill_to_code);
			if (!$customer){
				$this->gen_m->insert("customer", ["customer" => $bill_to_name, "bill_to_code" => $bill_to_code]);
				$customer = $this->gen_m->unique("customer", "bill_to_code", $bill_to_code);
			}
			
			$s_person = $this->gen_m->unique("sales_person", "name", $sales_person_name);
			if (!$s_person){
				$this->gen_m->insert("sales_person", ["name" => $sales_person_name]);
				$s_person = $this->gen_m->unique("sales_person", "name", $sales_person_name);
			}
			
			$s_channel = $this->gen_m->unique("sales_channel", "channel", $sales_channel);
			if (!$s_channel){
				$this->gen_m->insert("sales_channel", ["channel" => $sales_channel]);
				$s_channel = $this->gen_m->unique("sales_channel", "channel", $sales_channel);
			}
			
			$payterm = $this->gen_m->unique("payment_term", "term", $payment_term);
			if (!$payterm){
				$this->gen_m->insert("payment_term", ["term" => $payment_term]);
				$payterm = $this->gen_m->unique("payment_term", "term", $payment_term);
			}

			$product_level1 = $this->gen_m->unique("product_line", "line", $product_level1_name);
			if (!$product_level1){
				$this->gen_m->insert("product_line", ["parent_id" => -1, "level" => 1, "line" => $product_level1_name]);
				$product_level1 = $this->gen_m->unique("product_line", "line", $product_level1_name);
			}
			
			$product_level2 = $this->gen_m->unique("product_line", "line", $product_level2_name);
			if (!$product_level2){
				$this->gen_m->insert("product_line", ["parent_id" => $product_level1->line_id, "level" => 2, "line" => $product_level2_name]);
				$product_level2 = $this->gen_m->unique("product_line", "line", $product_level2_name);
			}
			
			$product_level3 = $this->gen_m->unique("product_line", "line", $product_level3_name);
			if (!$product_level3){
				$this->gen_m->insert("product_line", ["parent_id" => $product_level2->line_id, "level" => 3, "line" => $product_level3_name]);
				$product_level3 = $this->gen_m->unique("product_line", "line", $product_level3_name);
			}
			
			$product_level4 = $this->gen_m->unique("product_line", "line", $product_level4_name);
			if (!$product_level4){
				$this->gen_m->insert("product_line", ["parent_id" => $product_level3->line_id, "level" => 4, "line" => $product_level4_name]);
				$product_level4 = $this->gen_m->unique("product_line", "line", $product_level4_name);
			}
			
			$division_id = $product_level1 ? $product_level1->parent_id : -1;
			
			$order_category = $this->gen_m->unique("order_category", "category", $category);
			$currency = $this->gen_m->unique("currency", "currency", $currency);
			
			//set order item
			$order = [
				"order_no" => $order_no,
				"order_date" => $order_date,
				"customer_po_no" => $customer_po_no,
			];
			
			
			
			$order_itme_type = $this->gen_m->unique("order_itme_type", "type", $item_type_desctiption);
			if (!$order_itme_type){
				$this->gen_m->insert("order_itme_type", ["type" => $item_type_desctiption]);
				$order_itme_type = $this->gen_m->unique("order_itme_type", "type", $item_type_desctiption);
			}

			$inventory = $this->gen_m->unique("inventory", "inventory", $inventory_org);
			if (!$inventory){
				$this->gen_m->insert("inventory", ["parent_id" => 0, "inventory" => $inventory_org]);
				$inventory = $this->gen_m->unique("inventory", "inventory", $inventory_org);
			}
			
			if ($sub_inventory){
				$sub_inventory_aux = ["parent_id" => $inventory->inventory_id, "inventory" => $sub_inventory];
				$sub_inventory = $this->gen_m->filter("inventory", true, $sub_inventory_aux);
				if ($sub_inventory) $sub_inventory = $sub_inventory[0];
				else{
					$inv_id = $this->gen_m->insert("inventory", $sub_inventory_aux);
					$sub_inventory = $this->gen_m->unique("inventory", "inventory_id", $inv_id);
				}	
			}else $sub_inventory = null;

			$invoice = $this->gen_m->unique("invoice", "invoice", $invoice_no);
			if (!$invoice){
				$this->gen_m->insert("invoice", ["invoice" => $invoice_no]);
				$invoice = $this->gen_m->unique("invoice", "invoice", $invoice_no);
			}
			
			$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_code", $ship_to_code);
			if (!$ship_to){
				$this->gen_m->insert("customer_ship_to", ["ship_to_code" => $ship_to_code, "customer_id" => $customer->customer_id, "address" => ""]);
				$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_code", $ship_to_code);
			}
			
			$product_category = $this->gen_m->unique("product_category", "category", $model_category);
			if (!$product_category){
				$this->gen_m->insert("product_category", ["category" => $model_category]);
				$product_category = $this->gen_m->unique("product_category", "category", $model_category);
			}
			
			$product = $this->gen_m->unique("product", "model", $model);
			if (!$product){
				$this->gen_m->insert("product", ["line_id" => $product_level4->line_id, "model" => $model]);
				$product = $this->gen_m->unique("product", "model", $model);
			}
			
			if (!$product->category_id) $this->gen_m->update("product", ["product_id" => $product->product_id], ["category_id" => $product_category->category_id]);
			
			$order_item = [
				"shipment_date" => $shipment_date,
				"closed_date" => $closed_date,
				"order_qty" => $order_qty,
				"unit_list_price" => $unit_list_price,
				"unit_selling_price" => $unit_selling_price,
				"total_amount_pen" => $total_amount_pen,
				"total_amount" => $total_amount,
				"order_amount_pen" => $order_amount_pen,
				"order_amount" => $order_amount,
				"tax_amount" => $tax_amount,
				"dc_amount" => $dc_amount,
				"dc_rate" => $dc_rate,
				"tax_amount" => $tax_amount,
			];
			
			
			
			
			
			echo "<strong>sales_person</strong>: "; print_r($s_person); echo "<br/>";
			echo "<strong>inventory</strong>: "; print_r($inventory); echo "<br/>";
			echo "<strong>sub_inventory</strong>: "; print_r($sub_inventory); echo "<br/>";
			echo "<strong>order</strong>: "; print_r($order); echo "<br/>";
			echo "<strong>invoice</strong>: "; print_r($invoice); echo "<br/>";
			echo "<strong>order category</strong>: "; print_r($order_category); echo "<br/>";
			echo "<strong>customer</strong>: "; print_r($customer); echo "<br/>";
			echo "<strong>ship to</strong>: "; print_r($ship_to); echo "<br/>";
			echo "<strong>Division ID</strong>: "; echo $division_id; echo "<br/>";
			echo "<strong>line lvl 1</strong>: "; print_r($product_level1); echo "<br/>";
			echo "<strong>line lvl 2</strong>: "; print_r($product_level2); echo "<br/>";
			echo "<strong>line lvl 3</strong>: "; print_r($product_level3); echo "<br/>";
			echo "<strong>line lvl 4</strong>: "; print_r($product_level4); echo "<br/>";
			echo "<strong>product_category</strong>: "; print_r($product_category); echo "<br/>";
			echo "<strong>product</strong>: "; print_r($product); echo "<br/>";
			echo "<strong>currency</strong>: "; print_r($currency); echo "<br/>";
			echo "<strong>order_itme_type</strong>: "; print_r($order_itme_type); echo "<br/>";
			echo "<strong>payterm</strong>: "; print_r($payterm); echo "<br/>";
			echo "<strong>s_channel</strong>: "; print_r($s_channel); echo "<br/>";
			
			echo "<br/><br/>----------------------------------------------------------------------------------------------------<br/><br/>";
			//echo $row." ***** ".$order_date." ***** ".$shipment_date." ***** ".$closed_date." ***** ".$order_no."<br/><br/>";
			
			
			if ($row >100) break;
		}
	}
}
