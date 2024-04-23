<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Dashboard extends CI_Controller {

	public function index(){
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		$data = [
			"main" => "dashboard/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function date_convert($date){
		$aux = explode("/", $date);
		return $aux[2].$aux[1].$aux[0];
	}
	
	public function import_data(){
		$spreadsheet = IOFactory::load('./test_files/dashboard/Excel_1402160157.xls');
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
			$book_currency = trim($sheet->getCellByColumnAndRow(19, $row)->getValue());
			$inventory_org = trim($sheet->getCellByColumnAndRow(20, $row)->getValue());
			$sub_inventory = trim($sheet->getCellByColumnAndRow(21, $row)->getValue());
			$sales_person = trim($sheet->getCellByColumnAndRow(22, $row)->getValue());
			$customer_code = trim($sheet->getCellByColumnAndRow(26, $row)->getValue());
			$customer_name = trim($sheet->getCellByColumnAndRow(27, $row)->getValue());
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

			$od = $sheet->getCellByColumnAndRow(37, $row)->getValue();
			$sd = $sheet->getCellByColumnAndRow(38, $row)->getValue();
			$cd = $sheet->getCellByColumnAndRow(40, $row)->getValue();
			
			//echo $row." ***** ".$category." ***** ".$bill_to_name." ***** ".$ship_to_name." ***** ".$model." ***** ".$order_qty." ***** ".$unit_list_price." ***** ".$unit_selling_price." ***** ".$total_amount_pen." ***** ".$total_amount." ***** ".$order_amount_pen." ***** ".$order_amount." ***** ".$tax_amount." ***** ".$dc_amount." ***** ".$dc_rate." ***** ".$currency." ***** ".$book_currency." ***** ".$inventory_org." ***** ".$sub_inventory." ***** ".$sales_person." ***** ".$customer_code." ***** ".$customer_name." ***** ".$customer_department." ***** ".$product_level1_name." ***** ".$product_level2_name." ***** ".$product_level3_name." ***** ".$product_level4_name." ***** ".$model_category." ***** ".$item_type_desctiption." ***** ".$order_date." ***** ".$shipment_date." ***** ".$closed_date." ***** ".$bill_to_code." ***** ".$ship_to_code." ***** ".$payment_term." ***** ".$sales_channel." ***** ".$order_no." ***** ".$invoice_no." ***** ".$customer_po_no."<br/><br/>";
			echo $row." ***** ".$order_date." ***** ".$shipment_date." ***** ".$closed_date." ***** ".$order_no."<br/><br/>";
			
			
			if ($row >100) break;
		}
	}
}
