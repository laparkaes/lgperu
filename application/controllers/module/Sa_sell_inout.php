<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Sa_sell_inout extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function invoice_process($qty, $invoices){//ok 2024 0629
		foreach($invoices as $i => $inv){
			//rest sell out qty to actual invoice
			$inv["qty"] = $inv["qty"] - $qty;
			
			if ($inv["qty"] <= 0){
				$qty = abs($inv["qty"]);//update new qty value after rest actual invoice
				unset($invoices[$i]);//all products has been sold
				if ($qty == 0) break;//if qty is zero, stop calculating
			}else{
				$invoices[$i]["qty"] = $inv["qty"];//all remove qty has been applied
				break;
			}
		}
		
		return $invoices;
	}
	
	private function calculate_unit_price($invoices){//ok 2024 0629
		$qty = $amount = 0;
		foreach($invoices as $inv){
			if ($inv["unit_price"] > 0){
				$qty += $inv["qty"];
				$amount += $inv["qty"] * $inv["unit_price"];
			}
		}
		
		return ($qty > 0 ? round($amount / $qty, 2) : 0);
	}
	
	private function get_sell_inout($bill_to, $models_arr){//ok 2024 0629
		$sell_inouts = [];
		foreach($models_arr as $m) $sell_inouts[$m] = [];
		
		//use for each sell-in & sell-out item
		$structure = new stdClass;
		$structure->type = "";
		$structure->date = "";
		$structure->qty = 0;
		$structure->amount = 0;
		$structure->unit_price = 0;
		$structure->unit_cost = 0;
		$structure->unit_profit = 0;
		$structure->stock_cus = 0;
		$structure->stock_lg = 0;
		$structure->stock_diff = 0;
		$structure->invoices = [];
		$structure->invoice_no = "";
		
		//load sell-in records and insert to model array
		$s_in = [
			"model",
			"closed_date as date",
			"invoice_no",
			"order_qty as qty",
			"order_amount_pen as amount",
		];
		
		$sell_ins = $this->gen_m->filter_select("sa_sell_in", false, $s_in, ["bill_to_code" => $bill_to, "order_qty !=" => 0], null, [["field" => "model", "values" => $models_arr]], [["date", "asc"]]);
		foreach($sell_ins as $si){
			if ($si->qty != -1){
				$si->unit_price = round($si->amount / $si->qty, 2);
				
				$aux = clone $structure;
				$aux->type = "in";
				$aux->date = $si->date;
				$aux->qty = $si->qty;
				$aux->amount = $si->amount;
				$aux->unit_price = $si->unit_price;
				$aux->invoice_no = $si->invoice_no;
				
				$sell_inouts[$si->model][] = clone $aux;
			}
		}
		
		//load sell-out records and insert to model array
		$s_out = [
			"suffix as model",
			"sunday as date",
			"units as qty",
			"amount",
			"stock",
		];
		
		$sell_outs = $this->gen_m->filter_select("sa_sell_out", false, $s_out, ["customer_code" => $bill_to, "units >" => 0], null, [["field" => "suffix", "values" => $models_arr]], [["date", "asc"]]);
		foreach($sell_outs as $so){
			$so->unit_price = round($so->amount / $so->qty, 2);
			
			$aux = clone $structure;
			$aux->type = "out";
			$aux->date = $so->date;
			$aux->qty = $so->qty;
			$aux->amount = $so->amount;
			$aux->unit_price = $so->unit_price;
			$aux->stock_cus = $so->stock;
			
			$sell_inouts[$so->model][] = clone $aux;
		}
		
		//sell-in/out calculation start
		foreach($sell_inouts as $model => $inouts){
			//echo $model."---------------------------------------------------<br/>";
			
			//sort by date asc
			usort($inouts, function($a, $b) {
				return strtotime($a->date) > strtotime($b->date);
			});
			
			$sell_inouts[$model] = $inouts;
			
			$invoices = [];
			$is_started = false;
			$stock_cus = $stock_lg = 0;
			foreach($inouts as $i => $item){
				if ($is_started){
					//clone invoice list from recent record
					if ($i > 0) $item->invoices = $inouts[$i-1]->invoices;
					
					switch($item->type){
						case "in":
							$invoices[] = ["no" => $item->invoice_no, "qty" => $item->qty, "unit_price" => $item->unit_price];
							$item->invoices = $invoices;
							
							$item->stock_cus = $stock_cus;
							$stock_lg += $item->qty;
							break;
						case "out":
							$invoices = $this->invoice_process($item->qty, $invoices);
							$item->invoices = $invoices;
						
							$stock_cus = $item->stock_cus;
							$stock_lg -= $item->qty;
							break;
					}
					
					$item->unit_cost = $this->calculate_unit_price($invoices);
					$item->unit_profit = round($item->unit_price - $item->unit_cost, 2);
					
					$item->stock_lg = $stock_lg;
					$item->stock_diff = $item->stock_cus - $item->stock_lg;	
				}else{
					//first sell-out gives start because of customer stock
					if ($item->type === "out"){
						//make an invoice with no invoice number
						$invoices[] = ["no" => "No invoice", "qty" => $item->stock_cus, "unit_price" => 0];
						$stock_lg = $item->stock_cus;
						$is_started = true;
					}
				}
			}
		}
		
		return $sell_inouts;
	}
	
	public function update_models(){//ok 2024 0701
		//based on lvl 4
		$s = ["model_category", "product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name"];
		$models = $this->gen_m->filter_select("sa_sell_in", false, $s, ["model_category !=" => null], null, null, null, null, null, "product_level4_name");
		foreach($models as $m){
			//print_r($m); echo "<br/><br/>";
			$this->gen_m->update("sa_sell_in", ["product_level1_name" => $m->product_level1_name, "product_level2_name" => $m->product_level2_name, "product_level3_name" => $m->product_level3_name, "product_level4_name" => $m->product_level4_name], ["model_category" => $m->model_category]);
		}
		
		//based on lvl 4
		$s = ["model_category", "product_level1_name", "product_level2_name"];
		$models = $this->gen_m->filter_select("sa_sell_in", false, $s, ["model_category !=" => null], null, null, null, null, null, "product_level2_name");
		foreach($models as $m){
			//print_r($m); echo "<br/><br/>";
			$this->gen_m->update("sa_sell_in", ["product_level1_name" => $m->product_level1_name, "product_level2_name" => $m->product_level2_name], ["model_category" => $m->model_category]);
		}
	}
	
	public function index(){//ok 2024 0701
		$bill_to_code = $this->input->get("cus");
		$model_categories = $models = $lvl1s = $lvl2s = $lvl3s = $lvl4s = [];
		
		$s = ["product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name", "model_category", "model"];
		$order = [["model_category", "asc"], ["product_level1_name", "asc"], ["product_level2_name", "asc"], ["product_level3_name", "asc"], ["product_level4_name", "asc"], ["model", "asc"]];
		
		$model_categories = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "model_category");
		$lvl1s = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "product_level1_name");
		$lvl2s = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "product_level2_name");
		$lvl3s = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "product_level3_name");
		$lvl4s = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "product_level4_name");
		$models = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "model");
		
		$model_list = [];
		foreach($models as $m) $model_list[] = $m->model;
		
		$data = [
			"model_categories" => $model_categories,
			"lvl1s" => $lvl1s,
			"lvl2s" => $lvl2s,
			"lvl3s" => $lvl3s,
			"lvl4s" => $lvl4s,
			"models" => $models,
			"customers" => $this->gen_m->filter_select("sa_sell_in", false, ["bill_to_code", "bill_to_name"], null, null, null, [["bill_to_name", "asc"]], "", "", "bill_to_code"),
			"sell_inouts" => ($bill_to_code and $model_list) ? $this->get_sell_inout($bill_to_code, $model_list) : [],
			"sell_ins" => $this->gen_m->filter("sa_sell_in", false, ($bill_to_code ? ["bill_to_code" => $bill_to_code] : null), null, null, [["closed_date", "desc"]], 1000),
			"sell_outs" => $this->gen_m->filter("sa_sell_out", false, $bill_to_code ? ["customer_code" => $bill_to_code] : null, null, null, [["sunday", "desc"]], 1000),
			"main" => "module/sa_sell_inout/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function sell_in_excel($sheet){//ok 20240627
		echo "Starting sell-in data save process. Don't close this tab.<br/><br/>";
		
		$max_row = $sheet->getHighestRow();
		//$max_row = 2000;
		
		$vars = [
			"bill_to_code",
			"bill_to_name",
			"product_level1_name",
			"product_level2_name",
			"product_level3_name",
			"product_level4_name",
			"model_category",
			"model",
			"closed_date",
			"invoice_no",
			"currency",
			"customer_department",
			"order_qty",
			"unit_selling_price",
			"order_amount",
			"order_amount_pen",
		];
		
		$updated = $inserted = 0;
		
		for($i = 2; $i < $max_row; $i++){
			$row = [];
			foreach($vars as $var_i => $var){
				$row[$var] = trim($sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
				if (!$row[$var]) $row[$var] = null;
			}
			
			if ($row["order_qty"]){
				$row["closed_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('I'.$i)->getFormattedValue())));
				//print_r($row); echo "<br/>";
			
				//filter
				$si = $this->gen_m->filter("sa_sell_in", false, $row);
				if ($si){
					$this->gen_m->update("sa_sell_in", ["sell_in_id" => $si[0]->sell_in_id], $row);
					$updated++;
				}else{
					$this->gen_m->insert("sa_sell_in", $row);
					$inserted++;
				}
			}
		}
		
		$this->update_models();
		
		echo number_format($inserted)." inserted and ".number_format($updated)." updated.";
	}
	
	public function sell_out_excel($sheet){//ok 20240627
		echo "Starting sell-in data save process. Don't close this tab.<br/><br/>";
		
		$max_row = $sheet->getHighestRow();
		//$max_row = 2000;
		
		$vars = [
			"year", 
			"channel", 
			"account", 
			"customer_code", 
			"division", 
			"line", 
			"week", 
			"sunday", 
			"model", 
			"suffix", 
			"units", 
			"amount", 
			"stock", 
		];
		
		$updated = $inserted = 0;
		
		for($i = 2; $i < $max_row; $i++){
			$row = [];
			foreach($vars as $var_i => $var){
				$row[$var] = trim($sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
				if (!$row[$var]) $row[$var] = null;
			}
			
			$row["sunday"] = date("Y-m-d", strtotime(trim($sheet->getCell('H'.$i)->getFormattedValue())));
			if (!$row["units"]) $row["units"] = 0;
			if (!$row["amount"]) $row["amount"] = 0;
			
			//filter
			$w = [
				"customer_code" => $row["customer_code"],
				"sunday" => $row["sunday"],
				"suffix" => $row["suffix"],
			];
			$so = $this->gen_m->filter("sa_sell_out", false, $w);
			if ($so){
				$this->gen_m->update("sa_sell_out", ["sell_out_id" => $so[0]->sell_out_id], $row);
				$updated++;
			}else{
				$this->gen_m->insert("sa_sell_out", $row);
				$inserted++;
			}
		}
		
		echo number_format($inserted)." inserted and ".number_format($updated)." updated.";
	}
	
	public function process_sell_inout_file(){//ok 20240627
		ini_set("memory_limit","1024M");
		set_time_limit(0);
		
		$spreadsheet = IOFactory::load("./upload/sa_sell_inout.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		$headers = [
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
		$h_in = ["Bill To Code", "Bill To Name", "Product Level1 Name", "Product Level2 Name", "Product Level3 Name", "Product Level4 Name", "Model Category", "Model", "Closed Date", "Invoice No.", "Currency", "Customer Department", "Order Qty", "Unit Selling Price", "Order Amount", "Order Amount (PEN)"];
		$h_out = ["Year", "Channel", "Account", "Customer Code", "Division", "Line", "Week", "Sunday", "Model", "Suffix", "Units", "Amount", "Stock"];
		
		//determinate file type
		$f_in = "in";
		foreach($headers as $i => $h) if (trim($h) !== $h_in[$i]) $f_in = "";
		
		$f_out = "out";
		foreach($headers as $i => $h) if (trim($h) !== $h_out[$i]) $f_out = "";
		
		switch($f_in.$f_out){
			case "in": 
				$this->sell_in_excel($sheet);
				break;
			case "out": 
				$this->sell_out_excel($sheet);
				break;
			default: echo "File is not sell-in or sell-out.";
		}
	}
	
	public function upload_sell_inout_file(){//ok 20240627
		$type = "error"; $url = ""; $msg = "";
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'sa_sell_inout',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$type = "success";
			$url = base_url()."module/sa_sell_inout/process_sell_inout_file";
			$msg = "File upload is done. Data saving will be started.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}

	public function test(){
		$rows = $this->get_rows("PE001351001B");
	}

	private function get_rows($bill_to_code = "PE001351001B"){
		$s = ["product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name", "model_category", "model"];
		$order = [["model_category", "asc"], ["product_level1_name", "asc"], ["product_level2_name", "asc"], ["product_level3_name", "asc"], ["product_level4_name", "asc"], ["model", "asc"]];
		$models = $this->gen_m->filter_select("sa_sell_in", false, $s, ["bill_to_code" => $bill_to_code], null, null, $order, null, null, "model");
		
		$model_list = [];
		foreach($models as $m) $model_list[] = $m->model;
		
		$sell_inouts = $bill_to_code ? $this->get_sell_inout($bill_to_code, $model_list) : [];
		
		$count = 0;
		$rows = [];
		
		foreach($models as $m){
			//print_r($m); echo "<br/>";
			$items = array_reverse($sell_inouts[$m->model]);
			foreach($items as $item){
				$invoices_arr = [];
				$invoices = $item->invoices;
				foreach($invoices as $inv) $invoices_arr[] = $inv["no"].", ".$inv["qty"]." units, PEN ".number_format($inv["unit_price"], 2);
				
				if ($item->type === "out"){
					$val = abs($item->stock_diff);
					if ($val < 5) $alert = "";
					else if ($val < 10) $alert = "Warning";
					else $alert = "Danger";
				}else $alert = "";
				
				$row = [
					$m->product_level1_name, 
					$m->product_level2_name, 
					$m->product_level3_name, 
					$m->product_level4_name, 
					$m->model_category, 
					$m->model, 
					$item->date, 
					($item->type === "in" ? "Sell-In" : "Sell-Out"), 
					$item->qty, 
					$item->stock_cus, 
					$item->stock_lg, 
					$item->stock_diff, 
					$alert, 
					$item->amount, 
					$item->unit_price, 
					((($item->unit_cost) and ($item->type === "out")) ? $item->unit_cost : ""), 
					((($item->unit_cost) and ($item->type === "out")) ? $item->unit_profit : ""), 
					implode(" / ", $invoices_arr), 
				];
				
				$rows[] = $row;
				$count++;
			}
		}
		
		return $rows;
	}

	public function exp_report(){
		set_time_limit(0);
		$start_time = microtime(true);
		
		$type = "error"; $msg = $url = ""; 
		
		$header = [
			"Product Level 1",
			"Product Level 2",
			"Product Level 3",
			"Product Level 4",
			"Model Category",
			"Model",
			"Date",
			"Type",
			"Qty",
			"Stock Cus",
			"LG",
			"Diff",
			"Alert",
			"Amount",
			"U/Price",
			"U/Cost",
			"U/Profit",
			"Invoices",
		];
		
		$bill_to_code = $this->input->post("cus");
		
		if ($bill_to_code){
			$rows = $this->get_rows($bill_to_code);
			if ($rows){
				$url = $this->my_func->generate_excel_report("sa_sell_in_out_report.xlsx", null, $header, $rows);
				$type = "success";
				$msg = "Sell-In/Out report has been created. (".number_Format(microtime(true) - $start_time, 3)." sec)";
			}else $msg = "No data to make report.";
		}else $msg = "Select a customer.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
