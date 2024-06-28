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
	
	private function get_sell_inout($bill_to){
		echo $bill_to;
	}
	
	public function test(){
		$this->get_sell_inout("PE000968001B");
	}
	
	private function get_sell_inout_($customer_id, $product_id){
		$row  = new stdClass;
		$row->date = null;
		$row->u_price = null;
		$row->currency = null;
		$row->sell_in = null;
		$row->sell_out = null;
		$row->stock_customer = null;
		$row->stock_lg = null;
		$row->stock_diff = null;
		$row->invoice = null;
		$row->invoices = [];
		$row->price_avg = null;
		$row->sale_price = null;
		$row->profit = null;
		
		$w_in = [
			"order_qty !=" => -1,
			"customer_id" => $customer_id,
			"product_id" => $product_id,
		];
		
		//load sell-ins
		$sell_ins = array_reverse($this->gen_m->filter("sell_in", true, $w_in, null, null, [["closed_date", "desc"], ["order_amount", "desc"]], 10)); //last 10 sell-ins
		
		//set first sell-out filter
		$w_out = [
			"customer_id" => $w_in["customer_id"],
			"product_id" => $w_in["product_id"],
			"date <" => ($sell_ins) ? $sell_ins[0]->closed_date : date("Y-m-d"),
		];
		$sell_out_first = $this->gen_m->filter("sell_out", true, $w_out, null, null, [["date", "desc"]], 1);
		
		$dates = [strtotime('-4 months')];
		if ($sell_out_first) $dates[] = strtotime($sell_out_first[0]->date);
		if ($sell_ins) $dates[] = strtotime($sell_ins[0]->closed_date);
		
		$date_start = date("Y-m-d", min($dates));

		//load real sell-in/out
		unset($w_out["date <"]);
		$w_in["closed_date >="] = $w_out["date >="] = $date_start;
		
		$sell_ins = $this->gen_m->filter("sell_in", true, $w_in, null, null, [["closed_date", "asc"], ["order_amount", "desc"]]);
		$sell_outs = $this->gen_m->filter("sell_out", true, $w_out, null, null, [["date", "asc"]]);
		
		//invoice array
		$invoices = [];
		
		//merge sell-in and Sell-Out
		$inout = [];
		
		foreach($sell_ins as $in){
			if ($in->closed_date > (($sell_outs) ? $sell_outs[0]->date : date("Y-m-d"))){
				$currency = $this->gen_m->unique("currency", "currency_id", $in->currency_id);
				
				$aux = clone $row;
				$aux->date = $in->closed_date;
				$aux->invoice_id = $in->invoice_id;
				$aux->currency = $currency->symbol;
				$aux->u_price = $in->unit_selling_price;
				$aux->sell_in = $in->order_qty;
				
				$inout[] = clone $aux;
				
				if ($aux->invoice_id){
					$inv = $this->gen_m->unique("invoice", "invoice_id", $aux->invoice_id);
					$inv->currency = $currency->symbol;
					$inv->u_price = $in->unit_selling_price;
					$invoices[$aux->invoice_id] = clone $inv;
				}
			}
		}
		
		foreach($sell_outs as $i => $out){
			$aux = clone $row;
			$aux->date = $out->date;
			$aux->sell_out = $out->qty;
			$aux->stock_customer = $out->stock;
			$aux->sale_price = round($out->amount/$out->qty, 2);
			
			$inout[] = clone $aux;
		}

		usort($inout, function($a, $b) {
			return strtotime($a->date) > strtotime($b->date);
		});
		
		$ranges = [];
		if ($sell_outs) $ranges[] = ["qty" => $sell_outs[0]->stock, "invoice_id" => ""];
		
		foreach($inout as $i => $io){
			if ($io->sell_in > 0){
				$io->invoice = (($io->invoice_id > 0) ? $invoices[$io->invoice_id]->invoice : "");
				$ranges[] = ["qty" => $io->sell_in, "invoice_id" => $io->invoice_id];
			}elseif ($io->sell_in < 0){
				$ranges = array_reverse($ranges);//reverse ranges
				
				$var = abs($io->sell_in);
				foreach($ranges as $i_r => $r){
					$ranges[$i_r]["qty"] = $r["qty"] - $var;
					
					if ($ranges[$i_r]["qty"] <= 0){
						$var = abs($ranges[$i_r]["qty"]);
						unset($ranges[$i_r]);
					}else break;
				}
				
				$ranges = array_reverse($ranges);//reverse ranges to original
			}
			
			if ($i){
				if ($io->sell_out > 0){
					$var = abs($io->sell_out);
					foreach($ranges as $i_r => $r){
						$ranges[$i_r]["qty"] = $r["qty"] - $var;
						
						if ($ranges[$i_r]["qty"] <= 0){
							$var = abs($ranges[$i_r]["qty"]);
							unset($ranges[$i_r]);
						}else break;
					}
				}elseif ($io->sell_out < 0){
					//use foreach because of array index
					foreach($ranges as $i_r => $r){
						$ranges[$i_r]["qty"] = $r["qty"] + abs($io->sell_out);
						break;
					}
				}
			}
			
			$io->stock_lg = 0;
			foreach($ranges as $r){
				$io->stock_lg += $r["qty"];
				$io->invoices[] = ($r["invoice_id"] > 0) ? ["qty" => $r["qty"], "invoice" => clone $invoices[$r["invoice_id"]]] : ["qty" => $r["qty"], "invoice" => null];
			}
			
			$io->stock_diff = $io->sell_out ? $io->stock_lg - $io->stock_customer : null;
			
			$aux_qty = 0;
			$aux_amount = 0;
			foreach($io->invoices as $inv){
				if ($inv["invoice"]){
					$aux_qty += $inv["qty"];
					$aux_amount += $inv["qty"] * $inv["invoice"]->u_price;
				}
			}
			
			$io->price_avg = ($aux_qty > 0) ? $aux_amount / $aux_qty : 0;
			$io->profit = (($io->price_avg > 0) and ($io->sale_price > 0)) ? round($io->sale_price - $io->price_avg, 2) : 0;
		}
		
		return array_reverse($inout);
	}
	
	private function set_product_ids($lz, $li, $lii, $liii, $liv, $prd){
		$product_ids = [];
		
		switch(true){
			case ($prd): 
				$product_ids[] = $prd;
				break;
			case ($liv): 
				$prods = $this->gen_m->filter("product", true, ["line_id" => $liv]);
				foreach($prods as $p) $product_ids[] = $p->product_id;
				break;
			case ($liii):
				$livs = $this->gen_m->filter("product_line", true, ["parent_id" => $liii]);
				$l_arr = []; foreach($livs as $l) $l_arr[] = $l->line_id;
				
				$prods = $this->gen_m->filter("product", true, null, null, [["field" => "line_id", "values" => $l_arr]]);
				foreach($prods as $p) $product_ids[] = $p->product_id;
			
				break;
			case ($lii):
				$liiis = $this->gen_m->filter("product_line", true, ["parent_id" => $lii]);
				$l_arr = []; foreach($liiis as $l) $l_arr[] = $l->line_id;
				
				$livs = $this->gen_m->filter("product_line", true, null, null, [["field" => "parent_id", "values" => $l_arr]]);
				$l_arr = []; foreach($livs as $l) $l_arr[] = $l->line_id;
				
				$prods = $this->gen_m->filter("product", true, null, null, [["field" => "line_id", "values" => $l_arr]]);
				foreach($prods as $p) $product_ids[] = $p->product_id;
			
				break;
			case ($li):
				$liis = $this->gen_m->filter("product_line", true, ["parent_id" => $li]);
				$l_arr = []; foreach($liis as $l) $l_arr[] = $l->line_id;
				
				$liiis = $this->gen_m->filter("product_line", true, null, null, [["field" => "parent_id", "values" => $l_arr]]);
				$l_arr = []; foreach($liiis as $l) $l_arr[] = $l->line_id;
				
				$livs = $this->gen_m->filter("product_line", true, null, null, [["field" => "parent_id", "values" => $l_arr]]);
				$l_arr = []; foreach($livs as $l) $l_arr[] = $l->line_id;
				
				$prods = $this->gen_m->filter("product", true, null, null, [["field" => "line_id", "values" => $l_arr]]);
				foreach($prods as $p) $product_ids[] = $p->product_id;
			
				break;
			case ($lz):
				$lis = $this->gen_m->filter("product_line", true, ["parent_id" => $lz]);
				$l_arr = []; foreach($lis as $l) $l_arr[] = $l->line_id;
				
				$liis = $this->gen_m->filter("product_line", true, null, null, [["field" => "parent_id", "values" => $l_arr]]);
				$l_arr = []; foreach($liis as $l) $l_arr[] = $l->line_id;
				
				$liiis = $this->gen_m->filter("product_line", true, null, null, [["field" => "parent_id", "values" => $l_arr]]);
				$l_arr = []; foreach($liiis as $l) $l_arr[] = $l->line_id;
				
				$livs = $this->gen_m->filter("product_line", true, null, null, [["field" => "parent_id", "values" => $l_arr]]);
				$l_arr = []; foreach($livs as $l) $l_arr[] = $l->line_id;
				
				$prods = $this->gen_m->filter("product", true, null, null, [["field" => "line_id", "values" => $l_arr]]);
				foreach($prods as $p) $product_ids[] = $p->product_id;
			
				break;
		}
		
		return $product_ids;
	}
	
	public function index(){
		/*
		//just apply where in customer_id
		$w = []; 
		if ($this->input->get("cus")) $w["customer_id"] = $this->input->get("cus");
		
		//product_id have to work for where in
		$lz = $this->input->get("lz");
		$li = $this->input->get("li");
		$lii = $this->input->get("lii");
		$liii = $this->input->get("liii");
		$liv = $this->input->get("liv");
		$prd = $this->input->get("prd");
		
		$product_ids = $this->set_product_ids($lz, $li, $lii, $liii, $liv, $prd);
		
		$w_in = []; 
		if ($product_ids) $w_in[] = ["field" => "product_id", "values" => $product_ids];
		
		//set up invoice array
		$sell_ins = $this->gen_m->filter("sell_in", true, $w, null, $w_in, [["closed_date", "desc"], ["order_amount", "asc"]], 1000);
		
		$invoice_ids = [];
		foreach($sell_ins as $in) $invoice_ids[] = $in->invoice_id;
		
		$invoice_ids = array_unique($invoice_ids);
		$invoices = ($invoice_ids) ? $this->gen_m->filter("invoice", true, null, null, [["field" => "invoice_id", "values" => $invoice_ids]]) : [];
		
		$invoice_arr = [];
		foreach($invoices as $inv) $invoice_arr[$inv->invoice_id] = $inv;
		
		//set up customer array
		$customer_arr = [];
		$customer_ids = [];
		
		$customers = array_merge($this->gen_m->only("sell_in", "customer_id"), $this->gen_m->only("sell_out", "customer_id"));
		foreach($customers as $c) $customer_ids[] = $c->customer_id;
		
		array_unique($customer_ids);
		
		//$customers = $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]);
		$customers = $this->gen_m->filter("customer", true, null, null, [["field" => "customer_id", "values" => $customer_ids]], [["customer", "asc"], ["bill_to_code", "asc"]]);
		
		foreach($customers as $cus) $customer_arr[$cus->customer_id] = $cus;
		
		//set up channel array
		$channel_arr = [];
		$channels = $this->gen_m->all("sell_out_channel");
		foreach($channels as $chan) $channel_arr[$chan->channel_id] = $chan;
		
		//set up currency array
		$currency_arr = [];
		$currencies = $this->gen_m->all("currency");
		foreach($currencies as $curr) $currency_arr[$curr->currency_id] = $curr;
		
		//set up line array
		$lvl_arr = [];
		$lvl_z = $this->gen_m->filter("product_line", true, ["level" => 0], null, null, [["line", "asc"]]);
		$lvl_i = $this->gen_m->filter("product_line", true, ["level" => 1], null, null, [["line", "asc"]]);
		$lvl_ii = $this->gen_m->filter("product_line", true, ["level" => 2], null, null, [["line", "asc"]]);
		$lvl_iii = $this->gen_m->filter("product_line", true, ["level" => 3], null, null, [["line", "asc"]]);
		$lvl_iv = $this->gen_m->filter("product_line", true, ["level" => 4], null, null, [["line", "asc"]]);
		
		foreach($lvl_z as $l) $lvl_arr[$l->line_id] = $l;
		foreach($lvl_i as $l) $lvl_arr[$l->line_id] = $l;
		foreach($lvl_ii as $l) $lvl_arr[$l->line_id] = $l;
		foreach($lvl_iii as $l) $lvl_arr[$l->line_id] = $l;
		foreach($lvl_iv as $l) $lvl_arr[$l->line_id] = $l;
		
		//set up product array
		$products = $this->gen_m->all("product", [["model", "asc"]]);
		foreach($products as $p){
			if ($p->line_id){
				@$p_lvl_iv = $lvl_arr[$p->line_id];
				@$p_lvl_iii = $lvl_arr[$p_lvl_iv->parent_id];
				@$p_lvl_ii = $lvl_arr[$p_lvl_iii->parent_id];
				@$p_lvl_i = $lvl_arr[$p_lvl_ii->parent_id];
				
				@$p->lines = $p_lvl_i->line; //implode(" > ", [$lvl_i->line, $lvl_ii->line]);
				@$p->lvl_z_id = $p_lvl_i->parent_id;
				@$p->lvl_i_id = $p_lvl_i->line_id;
				@$p->lvl_ii_id = $p_lvl_ii->line_id;
				@$p->lvl_iii_id = $p_lvl_iii->line_id;
				@$p->lvl_iv_id = $p_lvl_iv->line_id;
			}else $p->lines = $p->lvl_z_id = $p->lvl_i_id = $p->lvl_ii_id = $p->lvl_iii_id = $p->lvl_iv_id = "";
			
			$product_arr[$p->product_id] = $p;
		}
		
		$sell_inouts = [];
		if ($lz and $li and $lii and $this->input->get("cus")){
			$customer_id = $this->input->get("cus");
			foreach($product_ids as $prd_id){
				$ios = $this->get_sell_inout($customer_id, $prd_id);
				$sell_inouts[] = ["product_id" => $prd_id, "qty" => count($ios), "ios" => $ios];
			}
		}
		usort($sell_inouts, function($a, $b) {
			return $a["qty"] < $b["qty"];
		});
		
		$data = [
			"lvl_z" => $lvl_z,
			"lvl_i" => $lvl_i,
			"lvl_ii" => $lvl_ii,
			"lvl_iii" => $lvl_iii,
			"lvl_iv" => $lvl_iv,
			"products" => $products,
			"customers" => $customers,
			"customer_arr" => $customer_arr,
			"invoice_arr" => $invoice_arr,
			"product_arr" => $product_arr,
			"currency_arr" => $currency_arr,
			"channel_arr" => $channel_arr,
			"sell_ins" => $sell_ins,
			"sell_outs" => $this->gen_m->filter("sell_out", true, $w, null, $w_in, [["date", "desc"]], 1000),
			"sell_inouts" => $sell_inouts,
			"main" => "module/sell_inout/index",
		];
		*/
		
		$data = [
			"main" => "module/sa_sell_inout/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function get_customer($customer, $bill_to_code){
		$cus = $this->gen_m->unique("customer", "bill_to_code", $bill_to_code);
		if (!$cus){
			if ($bill_to_code){
				$cus_id = $this->gen_m->insert("customer", ["customer" => $customer, "bill_to_code" => $bill_to_code]);
				$cus = $this->gen_m->unique("customer", "customer_id", $cus_id);	
			}
		}
		
		return $cus;
	}
	
	private function get_invoice($invoice){
		$inv = $this->gen_m->unique("invoice", "invoice", $invoice);
		if (!$inv){
			$inv_id = $this->gen_m->insert("invoice", ["invoice" => $invoice]);
			$inv = $this->gen_m->unique("invoice", "invoice_id", $inv_id);
		}
		
		return $inv;
	}
	
	public function sell_in_excel($sheet){//ok 20240627
		echo "Starting sell-in data save process. Don't close this tab.<br/><br/>";
		
		$max_row = $sheet->getHighestRow();
		//$max_row = 2000;
		
		$vars = [
			"product_level1_name", 
			"bill_to_name", 
			"bill_to_code", 
			"model", 
			"closed_date", 
			"invoice_no", 
			"currency", 
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
				$row["closed_date"] = date("Y-m-d", strtotime(trim($sheet->getCell('E'.$i)->getFormattedValue())));
			
				//filter
				$w = ["bill_to_code" => $row["bill_to_code"], "model" => $row["model"], "closed_date" => $row["closed_date"], "invoice_no" => $row["invoice_no"]];
				$si = $this->gen_m->filter("sa_sell_in", false, $w);
				if ($si){
					$this->gen_m->update("sa_sell_in", ["sell_in_id" => $si[0]->sell_in_id], $row);
					$updated++;
				}else{
					$this->gen_m->insert("sa_sell_in", $row);
					$inserted++;
				}
			}
		}
		
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
			$w = ["customer_code" => $row["customer_code"], "sunday" => $row["sunday"], "suffix" => $row["suffix"]];
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
		
		//determinate file type
		$f_type = "";
		
		if (
			($h[0] === "Product Level1 Name") and 
			($h[1] === "Bill To Name") and 
			($h[2] === "Bill To Code") and 
			($h[3] === "Model") and 
			($h[4] === "Closed Date") and 
			($h[5] === "Invoice No.") and 
			($h[6] === "Currency") and 
			($h[7] === "Order Qty") and 
			($h[8] === "Unit Selling  Price") and 
			($h[9] === "Order Amount") and 
			($h[10] === "Order Amount (PEN)")
		) $f_type = "in";
		
		if (
			($h[0] === "Year") and 
			($h[1] === "Channel") and 
			($h[2] === "Account") and 
			($h[3] === "Customer Code") and 
			($h[4] === "Division") and 
			($h[5] === "Line") and 
			($h[6] === "Week") and 
			($h[7] === "Sunday") and 
			($h[8] === "Model") and 
			($h[9] === "Suffix") and 
			($h[10] === "Units") and 
			($h[11] === "Amount") and 
			($h[12] === "Stock")
		) $f_type = "out";
		
		switch($f_type){
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

	public function exp_report(){
		set_time_limit(0);
		$start_time = microtime(true);
		
		$type = "error"; $msg = $url = ""; 
		
		$header = [
			"Customer",
			"Bill To",
			"Division",
			"Line 1",
			"Line 2",
			"Line 3",
			"Line 4",
			"Model",
			"Date",
			"U/Price",
			"Sell-In",
			"Sell-Out",
			"Stock Customer",
			"Stock LG",
			"Stock Diff",
			"Alert",
			"Invoice",
			"Invoices",
			"Avg Price",
		];
		
		$rows = [];
		
		$cus_id = $this->input->post("cus");
		$customer_ids = [];
		if ($cus_id) $customer_ids[] = $this->input->post("cus");
		else{
			$customers = array_merge($this->gen_m->only("sell_in", "customer_id"), $this->gen_m->only("sell_out", "customer_id"));
			foreach($customers as $c) $customer_ids[] = $c->customer_id;
		}
		
		array_unique($customer_ids);
		$customers = $this->gen_m->filter("customer", true, null, null, [["field" => "customer_id", "values" => $customer_ids]], [["customer", "asc"], ["bill_to_code", "asc"]]);
		
		$lz = $this->input->post("lz");
		$li = $this->input->post("li");
		
		if ($customers and $lz and $li){
			$lvlzs = $this->gen_m->filter("product_line", true, ["level" => 0]);
			foreach($lvlzs as $lvlz){
				if ($lvlz->line_id == $lz){
					$lvlis = $this->gen_m->filter("product_line", true, ["parent_id" => $lvlz->line_id]);
					foreach($lvlis as $lvli){
						if ($lvli->line_id == $li){
							$lvliis = $this->gen_m->filter("product_line", true, ["parent_id" => $lvli->line_id]);
							foreach($lvliis as $lvlii){
								$lvliiis = $this->gen_m->filter("product_line", true, ["parent_id" => $lvlii->line_id]);
								foreach($lvliiis as $lvliii){
									$lvlivs = $this->gen_m->filter("product_line", true, ["parent_id" => $lvliii->line_id]);
									foreach($lvlivs as $lvliv){
										$prods = $this->gen_m->filter("product", true, ["line_id" => $lvliv->line_id]);
										foreach($prods as $prod){
											foreach($customers as $customer){
												$inouts = $this->get_sell_inout($customer->customer_id, $prod->product_id);
												if ($inouts) foreach($inouts as $i => $i_io){
													//stock alert processing
													if ($i_io->sell_out > 0){ 
														switch(true){
															case (abs($i_io->stock_diff) > 10) : $alert = "Danger"; break;
															case (abs($i_io->stock_diff) > 5) : $alert = "Warning"; break;
															default: $alert = "";
														}
													}else $alert = "";
													
													//invoices processing
													$aux = []; 
													foreach($i_io->invoices as $inv){
														$i_aux = $inv["invoice"];
														$i_code = ($i_aux) ? $i_aux->invoice : "No Invoice";
														$i_price = ($i_aux) ? " * ".$i_aux->currency." ".number_format($i_aux->u_price, 2) : "";
														$aux[] = $i_code." (".number_format($inv["qty"]).$i_price.")";
													}
													$invoices = implode(", ", $aux);
													
													$rows[] = [
														$customer->customer,
														$customer->bill_to_code,
														$lvlz->line,
														$lvli->line,
														$lvlii->line,
														$lvliii->line,
														$lvliv->line,
														$prod->model,
														$i_io->date,
														(($i_io->u_price > 0) ? $i_io->currency." ".number_format($i_io->u_price, 2) : ""),
														$i_io->sell_in,
														$i_io->sell_out,
														$i_io->sell_out ? $i_io->stock_customer : "",
														$i_io->sell_out ? $i_io->stock_lg : "",
														$i_io->sell_out ? $i_io->stock_diff : "",
														$alert,
														$i_io->invoice,
														$invoices,
														(($i_io->price_avg > 0) ? "S/ ".number_format($i_io->price_avg, 2) : ""),
													];
												}	
											}
										}
									}
								}
							}
						}
					}
				}
			}
			
			$url = $this->my_func->generate_excel_report("sa_sell_in_out_report.xlsx", null, $header, $rows);
			if ($rows){
				$type = "success";
				$msg = "Sell-In/Out report has been created. (".number_Format(microtime(true) - $start_time, 3)." sec)";
			}else $msg = "No data to make report.";
		}else $msg = "Select customer, product division and product line 1 to generate report.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
