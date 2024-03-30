<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Sell_inout extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->color_rgb = [
			"green" => "198754",
			"red" => "dc3545",
		];
	}
	
	private function get_sell_inout($customer_id, $product_id){
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
			"sunday_date <" => ($sell_ins) ? $sell_ins[0]->closed_date : date("Y-m-d"),
		];
		$sell_out_first = $this->gen_m->filter("sell_out", true, $w_out, null, null, [["sunday_date", "desc"]], 1);
		
		//get sell-outs from first sell-out before first sell-in
		unset($w_out["sunday_date <"]);
		$w_out["sunday_date >="] = ($sell_out_first) ? $sell_out_first[0]->sunday_date : date("Y-m-d");
		
		$sell_outs = $this->gen_m->filter("sell_out", true, $w_out, null, null, [["sunday_date", "asc"]]);
		
		//invoice array
		$invoices = [];
		
		//merge sell-in and Sell-Out
		$inout = [];
		
		foreach($sell_ins as $in){
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
		
		foreach($sell_outs as $i => $out){
			$aux = clone $row;
			$aux->date = $out->sunday_date;
			$aux->sell_out = $out->qty;
			$aux->stock_customer = $out->stock;
			
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
			}
			
			if ($io->sell_out > 0){
				if ($i > 0){
					$var = abs($io->sell_out);
					foreach($ranges as $i_r => $r){
						$ranges[$i_r]["qty"] = $r["qty"] - $var;
						
						if ($ranges[$i_r]["qty"] <= 0){
							$var = abs($ranges[$i_r]["qty"]);
							unset($ranges[$i_r]);
						}else break;
					}
				}
				
				$io->stock_lg = 0;
				foreach($ranges as $r){
					$io->stock_lg += $r["qty"];
					$io->invoices[] = ($r["invoice_id"] > 0) ? ["qty" => $r["qty"], "invoice" => clone $invoices[$r["invoice_id"]]] : ["qty" => $r["qty"], "invoice" => null];
				}
				
				$io->stock_diff = $io->stock_lg - $io->stock_customer;
			}
		}
		
		return array_reverse($inout);
	}
	
	public function index(){
		//just apply where in customer_id
		$w = []; 
		if ($this->input->get("cus")) $w["customer_id"] = $this->input->get("cus");
		
		//product_id have to work for where in
		$product_ids = [];
		if ($this->input->get("prd")) $product_ids[] = $this->input->get("prd");
		elseif ($this->input->get("cat")){
			$prods = $this->gen_m->filter("product", true, ["category_id" => $this->input->get("cat")]);
			foreach($prods as $p) $product_ids[] = $p->product_id;
		}elseif ($this->input->get("grp")){
			$cats = $this->gen_m->filter("product_category", true, ["group_id" => $this->input->get("grp")]);
			foreach($cats as $c){
				$prods = $this->gen_m->filter("product", true, ["category_id" => $c->category_id]);
				foreach($prods as $p) $product_ids[] = $p->product_id;
			}
		}
		
		$w_in = []; 
		if ($product_ids) $w_in[] = ["field" => "product_id", "values" => $product_ids];
		
		//set up currency array
		$currency_arr = [];
		$currencies = $this->gen_m->all("currency");
		foreach($currencies as $curr) $currency_arr[$curr->currency_id] = $curr;
		
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
		$customers = $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]);
		foreach($customers as $cus) $customer_arr[$cus->customer_id] = $cus;
		
		//set up product array
		$groups = $this->gen_m->all("product_group", [["group_name", "asc"]]);
		$categories = $this->gen_m->all("product_category", [["category", "asc"]]);
		$products = $this->gen_m->all("product", [["model", "asc"]]);
		
		$group_arr = $category_arr = $product_arr = [];
		foreach($groups as $g) $group_arr[$g->group_id] = $g;
		foreach($categories as $c) $category_arr[$c->category_id] = $c;
		foreach($products as $p){
			if ($p->category_id){
				$p->category = $category_arr[$p->category_id]->category;
				$p->group = $group_arr[$category_arr[$p->category_id]->group_id]->group_name;
			}else $p->category = $p->group = "";
			
			
			$product_arr[$p->product_id] = $p;
		}
		
		$data = [
			"groups" => $groups,
			"categories" => $categories,
			"products" => $products,
			"customers" => $customers,
			"customer_arr" => $customer_arr,
			"invoice_arr" => $invoice_arr,
			"product_arr" => $product_arr,
			"currency_arr" => $currency_arr,
			"sell_ins" => $sell_ins,
			"sell_outs" => $this->gen_m->filter("sell_out", true, $w, null, $w_in, [["sunday_date", "desc"]], 1000),
			"main" => "sa/sell_inout/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function testing(){
		
		function print_sell_inout($inout){
			echo "<table>";
			echo "<tr><td>Date</td><td>U/Price</td><td>Sell-in</td><td>Sell-out</td><td>Stock Customer</td><td>Stock LG</td><td>Stock Diff</td><td>Invoice</td><td>Invoices</td></tr>";
			
			foreach($inout as $io){
				echo "<tr>";
				echo "<td>".$io->date."</td>";
				echo "<td>".(($io->u_price > 0) ? $io->currency." ".number_format($io->u_price, 2) : "")."</td>";
				echo "<td>".$io->sell_in."</td>";
				echo "<td>".$io->sell_out."</td>";
				echo "<td>".(($io->sell_out) ? $io->stock_customer : "")."</td>";
				echo "<td>".$io->stock_lg."</td>";
				echo "<td>".$io->stock_diff."</td>";
				echo "<td>".$io->invoice."</td>";
				echo "<td style='width: 300px;'>"; 
				//set invoices
				$aux = [];
				foreach($io->invoices as $inv){
					$i_aux = $inv["invoice"];
					$i_code = ($i_aux) ? $i_aux->invoice : "No Invoice";
					$i_price = ($i_aux) ? " * ".$i_aux->currency." ".number_format($i_aux->u_price, 2) : "";
					$aux[] = $i_code." (".number_format($inv["qty"]).$i_price.")";
				}
				echo implode("<br/>", $aux);
				echo "</td>";
				echo "</tr>";
			}
			
			echo "</table>";
		}
		
		echo "<style>table td{padding: 5px 10px;}</style>";
		
		$groups = $this->gen_m->all("product_group", [["group_name", "asc"]]);
		foreach($groups as $g_i => $grp){
			echo "Group: ".$grp->group_name."<br/><br/>";
			$categories = $this->gen_m->filter("product_category", true, ["group_id" => $grp->group_id]);
			foreach($categories as $cat){
				echo "Category: ".$cat->category."<br/><br/>";
				$products = $this->gen_m->filter("product", true, ["category_id" => $cat->category_id]);
				foreach($products as $prd){
					echo "Product: ".$prd->model."<br/><br/>";
					
					//customer_id = 9 is supermercados mercados (plaza vea)
					//product_id = 274 as test
					
					$inout = $this->get_sell_inout(9, $prd->product_id);
					if ($inout) print_sell_inout($inout);
					
					echo "<br/><br/>";
				}
				
				echo "<br/><br/>";
				break;
			}
			
			echo "<br/><br/>";
			if ($g_i > 0) break;
		}
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
	
	private function get_product($cat_id, $model){
		$aux = ["model" => $model];
		
		$prod = $this->gen_m->filter("product", true, $aux);
		if ($prod) $prod = $prod[0];
		else{
			$aux["category_id"] = $cat_id;
			$prod_id = $this->gen_m->insert("product", $aux);
			$prod = $this->gen_m->unique("product", "product_id", $prod_id);
		}
		
		return $prod;
	}
	
	private function get_invoice($invoice){
		$inv = $this->gen_m->unique("invoice", "invoice", $invoice);
		if (!$inv){
			$inv_id = $this->gen_m->insert("invoice", ["invoice" => $invoice]);
			$inv = $this->gen_m->unique("invoice", "invoice_id", $inv_id);
		}
		
		return $inv;
	}
	
	public function sell_in_excel($sheet){
		echo "Starting sell-in data save process. Don't close this tab.<br/><br/>";
		
		$max_row = $sheet->getHighestRow();
		//$max_row = 2000;
		
		//preparing product category id array
		$cat_arr = [];
		$cat_rec = $this->gen_m->all("product_category");
		foreach($cat_rec as $cat) $cat_arr[$cat->category] = $cat->category_id;
		
		$data = [];
		for ($row = 2; $row <= $max_row; $row++){
			$order_qty = trim($sheet->getCell('H'.$row)->getValue());
			if ($order_qty != 0){
				$invoice = $this->get_invoice(trim($sheet->getCell('F'.$row)->getValue()));
				$customer = $this->get_customer(trim($sheet->getCell('B'.$row)->getValue()), trim($sheet->getCell('C'.$row)->getValue()));
				$product = $this->get_product($cat_arr[trim($sheet->getCell('A'.$row)->getValue())], trim($sheet->getCell('D'.$row)->getValue()));
				$currency = $this->gen_m->unique("currency", "currency", trim($sheet->getCell('G'.$row)->getValue()));
				
				$aux = [
					"invoice_id" => ($invoice) ? $invoice->invoice_id : null,
					"customer_id" => ($customer) ? $customer->customer_id : null,
					"product_id" => ($product) ? $product->product_id : null,
					"currency_id" => ($currency) ? $currency->currency_id : null,
					"closed_date" => date("Y-m-d", strtotime(trim($sheet->getCell('E'.$row)->getFormattedValue()))),
					"order_qty" => $order_qty,
					"unit_selling_price" => trim($sheet->getCell('I'.$row)->getValue()),
					"order_amount" => trim($sheet->getCell('J'.$row)->getValue()),
					"order_amount_pen" => trim($sheet->getCell('K'.$row)->getValue()),
				];
				
				if (!$this->gen_m->filter("sell_in", true, $aux)) $data[] = $aux;
			}
		}
		
		echo number_format(($data) ? $this->gen_m->insert_m("sell_in", $data) : 0)." new sell-in registered. You can close this tab now.";
	}
	
	public function sell_out_excel($sheet){
		echo "Starting sell-out data save process. Don't close this tab.<br/><br/>";
		
		$max_row = $sheet->getHighestRow();
		//$max_row = 500;
		
		//preparing product channel id array
		$cha_arr = [];
		$cha_rec = $this->gen_m->all("sell_out_channel");
		foreach($cha_rec as $cha) $cha_arr[$cha->channel] = $cha->channel_id;
		
		$data = [];
		for ($row = 2; $row <= $max_row; $row++){
			$qty = trim($sheet->getCell('K'.$row)->getValue());
			if ($qty != 0){
				$customer = $this->get_customer(trim($sheet->getCell('C'.$row)->getValue()), trim($sheet->getCell('D'.$row)->getValue()));
				$product = $this->get_product(null, trim($sheet->getCell('J'.$row)->getValue()));
				
				$aux = [
					"customer_id" => ($customer) ? $customer->customer_id : null,
					"product_id" => ($product) ? $product->product_id : null,
					"channel_id" => $cha_arr[trim($sheet->getCell('B'.$row)->getFormattedValue())],
					"sunday_date" => date("Y-m-d", strtotime(trim($sheet->getCell('H'.$row)->getFormattedValue()))),
					"qty" => $qty,
					"amount" => trim($sheet->getCell('L'.$row)->getValue()),
					"stock" => trim($sheet->getCell('M'.$row)->getValue()),
				];
				
				if (!$this->gen_m->filter("sell_out", true, $aux)) $data[] = $aux;
			}
		}
		
		echo number_format(($data) ? $this->gen_m->insert_m("sell_out", $data) : 0)." new sell-out registered. You can close this tab now.";
	}
	
	public function process_sell_inout_file(){
		ini_set("memory_limit","1024M");
		set_time_limit(0);
		
		$spreadsheet = IOFactory::load("./upload/sales_admin/sell_inout.xlsx");
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
			($h[10] === "Order Amount (PEN)") and 
			($h[11] === "") and 
			($h[12] === "")
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
	
	public function upload_sell_inout_file(){
		$type = "error"; $url = ""; $msg = "";
		
		$config = [
			'upload_path'	=> './upload/sales_admin/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'sell_inout',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('md_uff_file')){
			$type = "success";
			$url = base_url()."sa/sell_inout/process_sell_inout_file";
			$msg = "File upload is done. Data saving will be started.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
