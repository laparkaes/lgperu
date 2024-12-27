<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Lgepr_closed_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$o = [["closed_date", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
		
		$data = [
			"closed_orders"	=> $this->gen_m->filter("lgepr_closed_order", false, null, null, null, $o, 5000),
			"main" 			=> "data_upload/lgepr_closed_order/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function check_model_categories(){
		$s = ["model_category", "product_level1_name", "product_level2_name"];
		$w = ["model_category !=" => ""];
		
		$models = $this->gen_m->filter_select("lgepr_sales_order", false, $s, $w, null, null, [["model_category", "asc"]], null, null, "model_category");
		
		//check product level 1 & 2
		foreach($models as $item){
			print_r($item);
			echo "<br/>";
		}
		
		echo "<br/><br/>";
		
		//make mapping structure
		foreach($models as $item){
			echo '"'.$item->model_category.'" => ["dash_division" => "", "dash_category" => ""],';
			echo "<br/>";
		}
	}
	
	private function update_dash_div_cat(){
		$dash_mapping = [
			"A/C" 	=> ["dash_division" => "H&A"	, "dash_category" => "Chiller"],
			"CAV" 	=> ["dash_division" => "HE"		, "dash_category" => "AV"],
			"CDT" 	=> ["dash_division" => "H&A"	, "dash_category" => "DW"],
			"CTV" 	=> ["dash_division" => "BS"		, "dash_category" => "Commercial TV"],
			"CVT" 	=> ["dash_division" => "H&A"	, "dash_category" => "Cooking"],
			"DS" 	=> ["dash_division" => "BS"		, "dash_category" => "DS"],
			"LTV" 	=> ["dash_division" => "HE"		, "dash_category" => "LTV"],
			"MNT" 	=> ["dash_division" => "BS"		, "dash_category" => "MNT"],
			"PC" 	=> ["dash_division" => "BS"		, "dash_category" => "PC"],
			"RAC" 	=> ["dash_division" => "H&A"	, "dash_category" => "RAC"],
			"REF" 	=> ["dash_division" => "H&A"	, "dash_category" => "REF"],
			"SAC" 	=> ["dash_division" => "H&A"	, "dash_category" => "SAC"],
			"SGN" 	=> ["dash_division" => "BS"		, "dash_category" => "MTN Signage"],
			"W/M" 	=> ["dash_division" => "H&A"	, "dash_category" => "W/M"],
		];
		
		foreach($dash_mapping as $key => $item) $this->gen_m->update("lgepr_closed_order", ["model_category" => $key], $item);
	}
	
	private function update_model_category(){
		//set mapping array
		$w = ["model_category !=" => ""];
		$s = ["model_category", "product_level4"];
		$closed_orders = $this->gen_m->filter_select("lgepr_closed_order", false, $s, $w, null, null, [["product_level4", "desc"]], null, null, "product_level4");
		
		$mapping = [];
		foreach($closed_orders as $item){
			if ($item->model_category){
				$index_6 = substr($item->product_level4, 0, 6);
				$index_4 = substr($item->product_level4, 0, 4);
				
				if (array_key_exists($index_6, $mapping)){
					if (!$mapping[$index_6]) $mapping[$index_6] = $item->model_category;
				}else $mapping[$index_6] = $item->model_category;	
				
				if (array_key_exists($index_4, $mapping)){
					if (!$mapping[$index_4]) $mapping[$index_4] = $item->model_category;
				}else $mapping[$index_4] = $item->model_category;
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
			
			if (array_key_exists($sub6, $mapping)) $mc = $mapping[$sub6];
			elseif (array_key_exists($sub4, $mapping)) $mc = $mapping[$sub4]; 
			
			//echo $sub6." ".$sub4." >>> ".$mc."<br/>"; print_r($item); echo "<br/><br/>";
			
			if ($mc) $this->gen_m->update("lgepr_closed_order", ["product_level4" => $item->product_level4], ["model_category" => $mc]);
		}
		
		//update division & category for dashboard
		$this->update_dash_div_cat();
	}
	
	public function process(){
		ini_set('memory_limit', '2G');
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
			
			for($i = 2; $i <= $max_row; $i++){
				
				$row = [
					'category'				=> trim($sheet->getCell('A'.$i)->getValue()),
					'bill_to_name'			=> trim($sheet->getCell('C'.$i)->getValue()),
					'ship_to_name'			=> trim($sheet->getCell('D'.$i)->getValue()),
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
					'closed_date'			=> trim($sheet->getCell('AN'.$i)->getValue()),
					'bill_to_code'			=> trim($sheet->getCell('AQ'.$i)->getValue()),
					'ship_to_code'			=> trim($sheet->getCell('AR'.$i)->getValue()),
					'ship_to_city'			=> trim($sheet->getCell('AT'.$i)->getValue()),
					'sales_channel'			=> trim($sheet->getCell('AX'.$i)->getValue()),
					'order_source'			=> trim($sheet->getCell('AY'.$i)->getValue()),
					'order_no'				=> trim($sheet->getCell('BA'.$i)->getValue()),
					'line_no'				=> trim($sheet->getCell('BB'.$i)->getValue()),
					'customer_po_no'		=> trim($sheet->getCell('BE'.$i)->getValue()),
					'project_code'			=> trim($sheet->getCell('BF'.$i)->getValue()),
					'product_level4'		=> trim($sheet->getCell('BH'.$i)->getValue()),
					'receiver_city'			=> trim($sheet->getCell('BN'.$i)->getValue()),
					'invoice_no'			=> trim($sheet->getCell('BW'.$i)->getValue()),
					'invoice_date'			=> trim($sheet->getCell('AX'.$i)->getValue()),
					'shipping_method'		=> trim($sheet->getCell('CI'.$i)->getValue()),
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
				$row["closed_date"] = $this->my_func->date_convert($row["closed_date"]);
				$row["invoice_date"] = $this->my_func->date_convert($row["invoice_date"]);
				
				if (!$this->gen_m->filter("lgepr_closed_order", false, ["order_line" => $row["order_line"]])){
					$rows[] = $row;
					$order_lines[] = $row["order_line"];
				}
			}
			
			//insert closed orders
			$rows_split = array_chunk($rows, 1000);
			foreach($rows_split as $items) $records += $this->gen_m->insert_m("lgepr_closed_order", $items);
			
			//remove orders in sales order
			$order_lines_split = array_chunk($order_lines, 1000);
			foreach($order_lines_split as $items) $this->gen_m->delete_in("lgepr_sales_order", "order_line", $items);
			
			$this->update_model_category();
			
			$msg = number_format($records)." record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
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
