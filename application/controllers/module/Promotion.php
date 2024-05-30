<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Promotion extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "module/promotion/index",
		];
		
		$this->load->view('layout', $data);
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
	
	public function test(){
		$type = "error"; $msg = "";
		
		//load excel file
		$spreadsheet = IOFactory::load("./test_files/sa_promotion/sa_promotion.xlsx");
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
		
		$h_origin = [
			"Seq",
			"Company Name",
			"Division Name",
			"Promotion No",
			"Promotion Line No",
			"Fecha Inicio",
			"Fecha Fin",
			"Customer Code",
			"Modelo",
			"PVP",
			"Costo Sellin",
			"* Precio Promotion",
			"* Nuevo Margen",
		];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		if ($header_validation){
			//save promotion rows in array
			$promotions = $promotions_by_model = [];
			
			$max_row = $sheet->getHighestRow();
			//$max_col = $sheet->getHighestColumn();
			
			for($i = 2; $i < $max_row; $i++){
				$promotions[] = [
					"prom" 			=> $sheet->getCell('D'.$i)->getValue(),
					"prom_line" 	=> $sheet->getCell('E'.$i)->getValue(),
					"date_start"	=> $this->my_func->date_convert_3($sheet->getCell('F'.$i)->getValue()),
					"date_end"		=> $this->my_func->date_convert_3($sheet->getCell('G'.$i)->getValue()),
					"cus_code"		=> $sheet->getCell('H'.$i)->getValue(),
					"prod_model"	=> $sheet->getCell('I'.$i)->getValue(),
					"cost_sellin"	=> $sheet->getCell('K'.$i)->getValue(),
					"price_prom" 	=> $sheet->getCell('L'.$i)->getValue(),
					"new_margin" 	=> $sheet->getCell('M'.$i)->getValue(),
					"cost_prom"		=> $sheet->getCell('N'.$i)->getValue(),
					"diff"			=> $sheet->getCell('O'.$i)->getValue(),
					"qty"			=> $sheet->getCell('P'.$i)->getValue(),
				];
			}
			
			//sort by promotion order
			usort($promotions, function($a, $b) {
				if ($a["prom"] === $b["prom"]) return ($a["prom_line"] > $b["prom_line"]);
				else return strcmp($a["prom"], $b["prom"]);
			});
			
			//set promotions by product model
			foreach($promotions as $i => $prom){
				//echo $i." =====> "; print_r($prom); echo "<br/>";
				//echo $i." =====> ".$prom["prom"]." ".$prom["prom_line"]." ".$prom["prod_model"]."<br/>";
				
				if (!array_key_exists($prom["prod_model"], $promotions_by_model)) $promotions_by_model[$prom["prod_model"]] = [];
				$promotions_by_model[$prom["prod_model"]][] = $prom;
			}
			
			//working promotions by each product
			foreach($promotions_by_model as $model => $proms){
				$promotions_by_model[$model]["msg"] = "";
				
				$product = $this->gen_m->unique("product", "model", $model);
				if ($product){
					$customer = $this->gen_m->unique("customer", "bill_to_code", $proms[0]["cus_code"]);
					if ($customer){
						//print_r($product); echo "<br/>";
						//print_r($customer); echo "<br/>";
						//echo "<br/>";
						
						//load prices: last sell-in and actual avg in customer's stock
						$price_sellin = $price_avg = 0;
						
						$sell_inout = $this->get_sell_inout($customer->customer_id, $product->product_id);
						foreach($sell_inout as $inout){
							//unset($inout->invoices); print_r($inout); echo "<br/>";
							
							if (strtotime($inout->date) < strtotime($proms[0]["date_start"])){
								//last price_avg is valid
								if (!$price_avg) $price_avg = $inout->price_avg;
								
								//break loop when this record es recent sell in
								if ($inout->u_price){
									$price_sellin = $inout->u_price;
									break;
								}	
							}
						}
						
						//echo "<br/>";
						//echo "Sell-in: ".$price_sellin." / Avg: ".$price_avg."<br/>";
						//echo "<br/>";
						
						//set price_avg as start price to apply promotions
						$cost_start = $price_avg ? $price_avg : $price_sellin;
						
						//work if you have start cost
						if ($cost_start){
							echo "Promotion starting price: ".$cost_start."<br/><br/>";
							
							print_r($customer); echo "<br/>";
							print_r($product); echo "<br/><br/>";
							
							foreach($proms as $i => $p){
								if ($i){
									//loop all previous promotions to get last valid cost prom 
									$i_start = strtotime($proms[$i]["date_start"]);
									$i_end = strtotime($proms[$i]["date_end"]);
									
									$j = 0;
									while($j < $i){
										$j_start = strtotime($proms[$j]["date_start"]);
										$j_end = strtotime($proms[$j]["date_end"]);
										
										if (($j_start <= $i_start) and ($i_end <= $j_end)) $cost_sellin = $proms[$j]["cost_prom"];
										
										$j++;
									}
								}else $cost_sellin = $cost_start;
								
								$proms[$i]["cost_sellin"] = $cost_sellin;
								//$p["cost_sellin"] = $cost_sellin;
								
								//print_r($p); echo "<br/>";
								unset($proms[$i]["cus_code"]); unset($proms[$i]["prod_model"]); print_r($proms[$i]); echo "<br/>";
								echo "<br/>";
							}
							
							//if (count($proms) > 2) break;	
						}else $promotions_by_model[$model]["msg"] = "No sell-in price or sell-out avg price.";
					}else $promotions_by_model[$model]["msg"] = "Product no exists.";
				}else $promotions_by_model[$model]["msg"] = "Customer no exists.";
				
				echo $model."<br/>";
				echo $promotions_by_model[$model]["msg"]."<br/>";
				echo "<br/>=======================================================<br/><br/>";
			}
		}else $msg = "Wrong file uploaded.";
		
		echo $msg;
	}
}
