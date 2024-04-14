<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Aging extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	
	public function index(){
		$data = [
			"main" => "ar/aging/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function data_process(){
		$result = [];
		
		$currencies = $this->gen_m->only("ar_aging", "currency");
		$ar_classes = $this->gen_m->only("ar_aging", "ar_class");
		$cus_nums = $this->gen_m->only("ar_aging", "cus_num");
		$ranges = [[-99999, 0], [1, 7], [8, 15], [16, 30], [31, 45], [46, 60], [61, 90], [91, 180], [181, 360], [361, 9999]];
		
		$rows = $w = [];
		foreach($currencies as $curr){
			$w["currency"] = $curr->currency;

			foreach($cus_nums as $cus_num){
				$w["cus_num"] = $cus_num->cus_num;
				$cus_name = $this->gen_m->unique("ar_aging", "cus_num", $cus_num->cus_num)->cus_h_name;

				$payterms = $this->gen_m->only("ar_aging", "payterm", ["currency" => $curr->currency, "cus_num" => $cus_num->cus_num]);
				foreach($payterms as $payterm){
					$w["payterm"] = $payterm->payterm;
					
					$row = ["row_info" => [$curr->currency, $cus_num->cus_num, $cus_name, $payterm->payterm]];
					
					$arr_class = []; 
					$has_value = false;
					foreach($ar_classes as $ar_class){
						$w["ar_class"] = $ar_class->ar_class;
						
						$arr_class[$ar_class->ar_class] = [];
						foreach($ranges as $range){
							$w["aging_day >="] = $range[0];
							$w["aging_day <="] = $range[1];
							
							$balance = $this->gen_m->sum("ar_aging", "balance", $w)->balance;
							$arr_class[$ar_class->ar_class][] = $balance;
							if ($balance) $has_value = true;
						}
						
					}
					$row = array_merge($row, $arr_class);
					if ($has_value) $rows[] = $row;
				}
			}
		}
		echo "<br/><br/>";
		
		foreach($rows as $row){
			//print_r($row);
			print_r($row["row_info"]); echo "<br/>";
			echo "---- Chargeback "; print_r($row["Chargeback"]); echo "<br/>";
			echo "---- Credit Memo "; print_r($row["Credit Memo"]); echo "<br/>";
			echo "---- Invoice "; print_r($row["Invoice"]); echo "<br/>";
			
			echo "<br/>";
		}
		
		echo "<br/><br/>";
		/*
		$agings = $this->gen_m->filter("ar_aging", true);
		foreach($agings as $ag){
			print_r($ag); echo "<br/>";
			
		}
		*/
		
		return $result;
	}
	
	public function test(){
		$result = $this->data_process();
		print_r($result);
	}
	
	private function conversion($sheet){
		$this->gen_m->delete("ar_aging", ["valid" => true]);
		
		$max_row = $sheet->getHighestRow();
		
		$data = [];
		for ($row = 2; $row <= $max_row; $row++){
			$cus_num = trim($sheet->getCell('A'.$row)->getValue());
			if ($cus_num){
				$data[] = [
					"cus_num" => $cus_num,
					"cus_h_name" => trim($sheet->getCell('B'.$row)->getValue()),
					"ar_class" => trim($sheet->getCell('G'.$row)->getValue()),
					"payterm" => trim($sheet->getCell('L'.$row)->getValue()),
					"currency" => trim($sheet->getCell('N'.$row)->getValue()),
					"balance" => trim($sheet->getCell('O'.$row)->getValue()),
					"aging_day" => trim($sheet->getCell('R'.$row)->getValue()),
				];				
			}
		}
		
		$result = [];
		if ($this->gen_m->insert_m("ar_aging", $data)) $result = $this->data_process();
		
		return $result;
	}
	
	public function upload_data(){
		ini_set("memory_limit","1024M");
		set_time_limit(0);
			
		$type = "error"; $url = ""; $msg = ""; $data = [];
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'ar_aging_report',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('datafile')){
			$spreadsheet = IOFactory::load("./upload/".$this->upload->data('file_name'));
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
				trim($sheet->getCell('N1')->getValue()),
				trim($sheet->getCell('O1')->getValue()),
				trim($sheet->getCell('P1')->getValue()),
				trim($sheet->getCell('Q1')->getValue()),
				trim($sheet->getCell('R1')->getValue()),
				trim($sheet->getCell('S1')->getValue()),
				trim($sheet->getCell('T1')->getValue()),
				trim($sheet->getCell('U1')->getValue()),
			];
			
			//determinate file type
			$f_type = "";
			
			if (
				($h[0] === "Customer Header Number") and 
				($h[1] === "Customer Header Name") and 
				($h[2] === "Customer Code") and 
				($h[3] === "Customer Name") and 
				($h[4] === "Collector NO") and 
				($h[5] === "Salesperson Name") and 
				($h[6] === "AR Class Name") and 
				($h[7] === "Trx Number") and 
				($h[8] === "Invoice NO") and 
				($h[9] === "Aging Bucket NO") and 
				($h[10] === "Aging Bucket Name") and 
				($h[11] === "AR Payment Terms Desc") and 
				($h[12] === "AR Type Name") and 
				($h[13] === "Transaction Currency Code") and 
				($h[14] === "AR Balance") and 
				($h[15] === "Due YYYYMMDD") and 
				($h[16] === "Reference NO") and 
				($h[17] === "Aging Day") and 
				($h[18] === "Additional Aging Bucket") and 
				($h[19] === "AR Balance(USD)") and 
				($h[20] === "AR Balance(Book)")
			){
				$data = $this->conversion($sheet);
				if ($data){
					$type = "success";
					$url = "upload/ar_aging_report_converted.xlsx";
					$msg = "Report conversion is done.";	
				}else $msg = "No data to process.";
			}else $msg = "Wrong data file.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url, "data" => $data]);
	}
	
	
	///////////////////////////////
	
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
		$row->price_avg = null;
		
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
		
		$inout = $this->get_sell_inout(5, 274);
		if ($inout){
			//echo "Product: ".$prd->model."<br/><br/>";
			print_sell_inout($inout);
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
		
		$data = [];
		for ($row = 2; $row <= $max_row; $row++){
			$order_qty = trim($sheet->getCell('H'.$row)->getValue());
			if ($order_qty != 0){
				$model = trim($sheet->getCell('D'.$row)->getValue());
				$product = $this->gen_m->unique("product", "model", $model);
				if ($product){
					$invoice = $this->get_invoice(trim($sheet->getCell('F'.$row)->getValue()));
					$customer = $this->get_customer(trim($sheet->getCell('B'.$row)->getValue()), trim($sheet->getCell('C'.$row)->getValue()));
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
				}else echo "No model registered: ".$model."<br/>";
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
				$model = trim($sheet->getCell('J'.$row)->getValue());
				$product = $this->gen_m->unique("product", "model", $model);
				if ($product){
					$customer = $this->get_customer(trim($sheet->getCell('C'.$row)->getValue()), trim($sheet->getCell('D'.$row)->getValue()));
					
					$aux = [
						"customer_id" => ($customer) ? $customer->customer_id : null,
						"product_id" => ($product) ? $product->product_id : null,
						"channel_id" => $cha_arr[trim($sheet->getCell('B'.$row)->getFormattedValue())],
						"date" => date("Y-m-d", strtotime(trim($sheet->getCell('H'.$row)->getFormattedValue()))),
						"qty" => $qty,
						"amount" => trim($sheet->getCell('L'.$row)->getValue()),
						"stock" => trim($sheet->getCell('M'.$row)->getValue()),
					];
					
					if (!$this->gen_m->filter("sell_out", true, $aux)) $data[] = $aux;
				}else echo "No model registered: ".$model."<br/>";
			}
		}
		
		echo number_format(($data) ? $this->gen_m->insert_m("sell_out", $data) : 0)." new sell-out registered. You can close this tab now.";
	}
	
	public function process_sell_inout_file(){
		ini_set("memory_limit","1024M");
		set_time_limit(0);
		
		$spreadsheet = IOFactory::load("./upload/sa/sell_inout.xlsx");
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
			'upload_path'	=> './upload/sa/',
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

	public function exp_report(){
		set_time_limit(0);
		
		$type = "error"; $msg = $url = ""; 
		
		$start_time = microtime(true);
		
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
		
		$customer = $this->gen_m->unique("customer", "customer_id", $this->input->post("cus"));
		$customer_id = $customer->customer_id;
		
		$lz = $this->input->post("lz");
		$li = $this->input->post("li");
		
		if ($customer and $lz and $li){
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
											$inouts = $this->get_sell_inout($customer_id, $prod->product_id);
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
			
			$url = $this->my_func->generate_excel_report("sell_in_out_report.xlsx", null, $header, $rows);
			if ($rows){
				$type = "success";
				$msg = "Sell-In/Out report has been created. (".number_Format(microtime(true) - $start_time, 3)." sec)";
			}else $msg = "No data to make report.";
		}else $msg = "Select customer, product division and product line 1 to generate report.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
