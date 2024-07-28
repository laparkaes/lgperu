<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Sa_promotion extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"f_sellin" => $this->gen_m->filter("sa_sell_in", false, null, null, null, [["closed_date", "asc"]], 1, 0)[0]->closed_date,
			"l_sellin" => $this->gen_m->filter("sa_sell_in", false, null, null, null, [["closed_date", "desc"]], 1, 0)[0]->closed_date,
			"f_sellout" => $this->gen_m->filter("sa_sell_out", false, null, null, null, [["sunday", "asc"]], 1, 0)[0]->sunday,
			"l_sellout" => $this->gen_m->filter("sa_sell_out", false, null, null, null, [["sunday", "desc"]], 1, 0)[0]->sunday,
			"customers" => $this->gen_m->filter_select("sa_sell_in", false, ["bill_to_code", "bill_to_name"], null, null, null, [["bill_to_name", "asc"]], "", "", "bill_to_code"),
			"main" => "module/sa_promotion/index",
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
	
	private function calculate_promotion($name_p){
		set_time_limit(0);
		
		$prom_result = [];
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$name_p);
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
		
		$h_origin = ["Seq", "Company Name", "Division Name", "Promotion No", "Promotion Line No", "Fecha Inicio", "Fecha Fin", "Customer Code", "Modelo", "PVP", "Costo Sellin", "* Precio Promotion", "* Nuevo Margen"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		if ($header_validation){
			//save promotion rows in array
			$promotions = $promotions_by_model = [];
			
			$max_row = $sheet->getHighestRow();
			//$max_col = $sheet->getHighestColumn();
			
			for($i = 2; $i < $max_row; $i++){
				$prom = [
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
					"total"			=> 0,
				];
				
				$promotions[] = $prom;
				
				$prom_result[$prom["prom"]][$prom["prom_line"]] = $prom;
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
				$total_to_pay = 0;
				$cus = "";
				
				$product = $this->gen_m->unique("product", "model", $model);
				if ($product){
					$customer = $this->gen_m->unique("customer", "bill_to_code", $proms[0]["cus_code"]);
					if ($customer){
						$cus = $customer->customer;
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
							//echo "Promotion starting price: ".$cost_start."<br/><br/>";
							//print_r($customer); echo "<br/>";
							//print_r($product); echo "<br/><br/>";
							
							foreach($proms as $i => $p){
								//$proms[$i]["customer"] = $cus;
								
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
								$proms[$i]["diff"] = $proms[$i]["cost_sellin"] - $proms[$i]["cost_prom"];
								
								$f = [
									"customer_id" => $customer->customer_id,
									"product_id" => $product->product_id,
									"date >=" => $proms[$i]["date_start"],
									"date <=" => $proms[$i]["date_end"],
								];
								
								$proms[$i]["qty"] = $this->gen_m->sum("sell_out", "qty", $f)->qty; if (!$proms[$i]["qty"]) $proms[$i]["qty"] = 0;
								$proms[$i]["total"] = $proms[$i]["diff"] * $proms[$i]["qty"];
								$total_to_pay += $proms[$i]["total"];
								
								//print_r($p); echo "<br/>";
								//unset($proms[$i]["cus_code"]); unset($proms[$i]["prod_model"]); 
								//print_r($proms[$i]); echo "<br/><br/>";
								
								$prom_result[$proms[$i]["prom"]][$proms[$i]["prom_line"]] = $proms[$i];
							}
							
							//$promotions_by_model[$model]["msg"] = "Success.";
							//$promotions_by_model[$model]["total_to_pay"] = $total_to_pay;
							//if (count($proms) > 2) break;	
						}//else $promotions_by_model[$model]["msg"] = "No sell-in price or sell-out avg price.";
					}//else $promotions_by_model[$model]["msg"] = "Product no exists.";
				}//else $promotions_by_model[$model]["msg"] = "Customer no exists.";
				
				//echo $cus."<br/>";
				//echo $model."<br/>";
				//echo $total_to_pay."<br/>";
				//echo $promotions_by_model[$model]["msg"]."<br/>";
				//echo "<br/>=======================================================<br/><br/>";
			}
		}
		
		/*
		foreach($prom_result as $prom => $lines){
			echo $prom."<br/>";
			
			foreach($lines as $line => $data){
				echo $line."<br/>";
				print_r($data);
				echo "<br/><br/>";
			}
			
			echo "<br/><br/>=========================<br/>";
		}
		
		return $prom_result;
		*/
	}

	private function update_report($promotions, $name_g){
		$msgs = [];
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$name_g);
		$sheet = $spreadsheet->getActiveSheet();
		
		$max_row = $sheet->getHighestRow();
		//$max_col = $sheet->getHighestColumn();
		
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
		
		$h_origin = ["Promotion No", "Promotion Name", "Promotion Line No", "Seq No", "Property Type", "Estimate Sales PGM No(Editable)", "Registration Request Date(Editable)", "Budget AU", "Pre Sales PGM No(Editable)", "Sales PGM No", "Sales PGM Name(Editable)", "Apply Date(From)", "Apply Date(To)"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		if ($header_validation){
			//set promotion results
			for($i = 2; $i < $max_row; $i++){
				$prom = trim($sheet->getCell('A'.$i)->getValue());
				$prom_line = trim($sheet->getCell('C'.$i)->getValue());
				
				if (@array_key_exists($prom, $promotions)){
					if (@array_key_exists($prom_line, $promotions[$prom])){
						$sheet->getCell("AV".$i)->setValue(round($promotions[$prom][$prom_line]["diff"], 2));
						$sheet->getCell("AW".$i)->setValue(round($promotions[$prom][$prom_line]["qty"], 2));
						$sheet->getCell("AX".$i)->setValue(round($promotions[$prom][$prom_line]["total"], 2));
						$sheet->getCell("AY".$i)->setValue(date("Ym"));
					}else $msgs[] = $prom." (".$prom_line.") no exists in promotion file.";
				}else $msgs[] = $prom." no exists in promotion file.";
			}
		}else $msgs = ["Wrong GERP file."];
		
		//unique messages
		$msgs = array_unique($msgs);
		
		//success cases if msgs is empty
		if (!$msgs) $msgs[] = "Success";
		
		//give instruction to remove message lines before upload to GERP
		array_unshift($msgs, "System msgs: (Remove these messages before upload to GERP)");
		
		//write msgs
		$msg_row = $max_row + 2;
		foreach($msgs as $msg){
			$sheet->getCell("A".$msg_row)->setValue($msg);
			$msg_row++;
		}
		
		//save excel file to a temporary directory
		$file_path = './upload/';
		$writer = new Xlsx($spreadsheet);
		$writer->save('./upload/'.$name_g);
	}
	
	
	
	
	private function get_last_cost_prom($promotions, $i, $item_p){//get last sell in price
		$cost_prom = 0;
		foreach($promotions as $index => $item){
			if ($i == $index) break;
			elseif ((strtotime($item->date_start) <= strtotime($item_p->date_start)) and (strtotime($item_p->date_end) <= strtotime($item->date_end))){
				//print_r($item); echo "<br/><br/>";
				if ($item->cost_prom) $cost_prom = $item->cost_prom;
			}
		}
		
		return $cost_prom;
	}
	
	private function get_last_key($arr, $key){
		$now_i = array_key_first($arr);
		foreach($arr as $i => $a){
			if ($key == $i) break;
			else $now_i = $i;
		}
		
		return $now_i;
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
	
	private function clean_sell_ins($sell_ins){
		/* print before
		$l_model = "";
		foreach($sell_ins as $i => $in){
			if ($in->qty != -1){
				if ($l_model !== $in->model){echo "<br/>"; $l_model = $in->model;}
				echo $i." / ".$in->date." / ".$in->qty." / ".$in->u_price." / ".$in->model."<br/>";
			}
		}
		*/
		
		foreach($sell_ins as $i => $in){
			if ($in->qty != -1){
				if ($in->qty < 0){
					//clean sell in
					$now_i = $this->get_last_key($sell_ins, $i);
					$remove_qty = $in->qty;
					
					//echo "[[[[[[[[".$now_i."/// remove: ".$remove_qty."]]]]]]]]]<br/>";
					while($in->model === $sell_ins[$now_i]->model and ($remove_qty < 0)){
						if (abs($in->unit_selling_price) === abs($sell_ins[$now_i]->unit_selling_price)){
							$sell_ins[$now_i]->qty += $remove_qty;
							if ($sell_ins[$now_i]->qty <= 0){
								$remove_qty = abs($sell_ins[$now_i]->qty);
								unset($sell_ins[$now_i]);
								$now_i = $this->get_last_key($sell_ins, $now_i);
							}else break;	
						}
					}
					unset($sell_ins[$i]);
				}
			}
		}
		
		/* print after
		echo "<br/><br/>----------------------------------<br/><br/>";
		$l_model = "";
		foreach($sell_ins as $i => $in){
			if ($in->qty != -1){
				if ($l_model !== $in->model){echo "<br/>"; $l_model = $in->model;}
				echo $i." / ".$in->date." / ".$in->qty." / ".$in->u_price." / ".$in->model."<br/>";
			}
		}
		*/
		
		return $sell_ins;
	}
	
	private function get_sell_inout_new($bill_to_code, $model_list){//get avg sell in price
		$sell_inouts = $ins_model = [];
		foreach($model_list as $m){
			$sell_inouts[$m] = [];
			$ins_model[$m] = [];
		}
		
		//use for each sell-in & sell-out item
		$structure = new stdClass;
		$structure->id = 0;
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
			"unit_selling_price",
		];
		
		//set sell ins buy model
		$sell_ins = $this->gen_m->filter_select("sa_sell_in", false, $s_in, ["bill_to_code" => $bill_to_code, "order_qty !=" => -1, "invoice_no !=" => "(blank)"], null, [["field" => "model", "values" => $model_list]], [["date", "asc"], ["qty", "desc"]]);
		foreach($sell_ins as $in) $ins_model[$in->model][] = $in;
		
		//clean negative sell ins
		foreach($ins_model as $model => $ins){
			$ins_model[$model] = $this->clean_sell_ins($ins_model[$model]);
			
			foreach($ins_model[$model] as $si){
				$si->unit_price = round($si->amount / $si->qty, 2);
				
				$aux = clone $structure;
				//$aux->type = $si->id;
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
		
		$sell_outs = $this->gen_m->filter_select("sa_sell_out", false, $s_out, ["customer_code" => $bill_to_code, "units !=" => 0], null, [["field" => "suffix", "values" => $model_list]], [["date", "asc"]]);
		foreach($sell_outs as $so){
			$so->unit_price = round($so->amount / $so->qty, 2);
			
			$aux = clone $structure;
			//$aux->type = $so->id;
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
	
	private function get_last_unit_cost($sell_inouts, $model, $to){
		$unit_cost = 0;
		
		if (array_key_exists($model, $sell_inouts)){
			foreach($sell_inouts[$model] as $item)
				if (strtotime($item->date) < strtotime($to)) $unit_cost = $item->unit_cost;
		}
		
		return $unit_cost;
	}
	
	private function set_promotions($sheet, $show_msg){
		$result = [];
		
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
		
		$h_origin = ["Seq", "Company Name", "Division Name", "Promotion No", "Promotion Line No", "Fecha Inicio", "Fecha Fin", "Customer Code", "Modelo", "PVP", "Costo Sellin", "* Precio Promotion", "* Nuevo Margen"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		if ($header_validation){
			//get loop limit
			$max_row = $sheet->getHighestRow();
			//$max_col = $sheet->getHighestColumn();
			
			//save promotion rows in array
			$rows = [];
			
			//save file records in array
			for($i = 2; $i < $max_row; $i++){
				$rows[] = [
					"prom" 			=> $sheet->getCell('D'.$i)->getValue(),
					"prom_line" 	=> $sheet->getCell('E'.$i)->getValue(),
					"date_start"	=> $this->my_func->date_convert_3($sheet->getCell('F'.$i)->getValue()),
					"date_end"		=> $this->my_func->date_convert_3($sheet->getCell('G'.$i)->getValue()),
					"cus_code"		=> $sheet->getCell('H'.$i)->getValue(),
					"prod_model"	=> $sheet->getCell('I'.$i)->getValue(),
					"cost_sellin"	=> $sheet->getCell('K'.$i)->getValue(),
					"price_prom" 	=> $sheet->getCell('L'.$i)->getValue(),
					"new_margin" 	=> $sheet->getCell('M'.$i)->getValue()/100,
					"cost_prom"		=> $sheet->getCell('N'.$i)->getValue(),
					"diff"			=> $sheet->getCell('O'.$i)->getValue(),
					"qty"			=> $sheet->getCell('P'.$i)->getValue(),
					"total"			=> 0,
				];
			}
			
			//clean db and insert
			$this->gen_m->delete("sa_promotion", ["promotion_id >" => 0]);
			$this->gen_m->insert_m("sa_promotion", $rows);
			
			//ger promotions in order
			//["prom", "asc"], ["prom_line", "asc"]
			$promotions = $this->gen_m->all("sa_promotion", [["prod_model", "asc"], ["date_start", "asc"], ["date_end", "desc"], ["cost_prom", "desc"]], "", "", false);
			
			//set promotion parameters
			$bill_to = $promotions[0]->cus_code; 
			$from = date("Y-m-01", strtotime($promotions[0]->date_start));
			$to = date("Y-m-t", strtotime($promotions[0]->date_start));
			
			//init variables
			$models = [];
			$w = ["customer_code" => $bill_to, "sunday >=" => $from, "sunday <=" => $to];
			
			//set model array
			$prod_models = $this->gen_m->only("sa_promotion", "prod_model");
			foreach($prod_models as $item) $models[] = $item->prod_model;
			
			$suffixs = $this->gen_m->only("sa_sell_out", "suffix", $w);
			foreach($suffixs as $item) $models[] = $item->suffix;
			
			$models = array_unique($models);
			sort($models);
			
			//define data array
			$data = [];
			foreach($models as $m) $data[$m] = ["model" => $m, "sell_outs" => [], "promotions" => []];
			
			$sell_outs = $this->gen_m->filter("sa_sell_out", false, $w);
			foreach($sell_outs as $item) $data[$item->suffix]["sell_outs"][] = $item;
			
			foreach($promotions as $item) $data[$item->prod_model]["promotions"][] = $item;
			
			//get sell in/out
			$sell_inouts = $this->get_sell_inout_new($bill_to, $models);
			
			//start calculation
			foreach($data as $item){
				if ($item["promotions"]){
					$last_sell_in = $this->gen_m->filter("sa_sell_in", false, ["bill_to_code" => $bill_to, "model" => $item["model"], "closed_date <" => $item["promotions"][0]->date_start, "order_qty >" => 0], null, null, [["closed_date", "desc"]], 1);
					
					//calculate init cost
					//$init_cost = $last_sell_in ? $last_sell_in[0]->unit_selling_price : 0;//based in last sell in
					$init_cost = $this->get_last_unit_cost($sell_inouts, $item["model"], $to);//based in client recent unit cost
					
					
					if ($show_msg){
						echo $item["model"]; echo " ==============================================================<br/><br/>";
						echo "init_cost: ".$init_cost."<br/><br/>";
						
						if (array_key_exists($item["model"], $sell_inouts)){
							echo "----------------------------------------------------------<br/>";
							echo "Sell in/outs ------------------------------------------------<br/>";
							foreach($sell_inouts[$item["model"]] as $item_io){
								unset($item_io->invoice_no);
								unset($item_io->invoices);
								print_r($item_io); echo "<br/>";
							}
							echo "<br/>";
						}
						
						echo "----------------------------------------------------------<br/>";
						echo "Sell Outs ------------------------------------------------<br/>";
						foreach($item["sell_outs"] as $item_s){
							//print_r($item_s);
							echo $item_s->sunday." /// ".$item_s->units." /// ".round($item_s->amount/$item_s->units, 2)." /// ".$item_s->amount."<br/>";
						}
						echo "<br/>";
						echo "----------------------------------------------------------<br/>";
						echo "Last Sell In ---------------------------------------------<br/>";
						if ($last_sell_in) echo $last_sell_in[0]->closed_date." /// ".$last_sell_in[0]->unit_selling_price."<br/>";
						echo "<br/>";
						echo "----------------------------------------------------------<br/>";
						echo "Promotions -----------------------------------------------<br/>";
					}					
					
					foreach($item["promotions"] as $i => $item_p){
						$item_p->cost_sellin = $i > 0 ? $this->get_last_cost_prom($item["promotions"], $i, $item_p) : $init_cost;
						
						if ($item_p->cost_sellin and $item_p->cost_prom){
							//calculate qty
							$qty = 0;
							foreach($item["sell_outs"] as $sell_out)
								if ((strtotime($item_p->date_start) <= strtotime($sell_out->sunday)) and (strtotime($sell_out->sunday) <= strtotime($item_p->date_end)))
									$qty += $sell_out->units;
							
							$item_p->qty = $qty;
							
							//calculate diff
							$item_p->diff = round($item_p->cost_sellin - $item_p->cost_prom, 2);
							
							//calculate total
							$item_p->total = $item_p->diff * $item_p->qty;
						}
						
						$result[$item_p->prom."_".$item_p->prom_line] = clone $item_p;
						
						if ($show_msg){
							echo $item_p->prom." /// ".$item_p->prom_line." /// ".$item_p->date_start." /// ".$item_p->date_end." /// ".$item_p->cost_sellin." /// ".$item_p->cost_prom." /// ".$item_p->diff." /// ".$item_p->qty." /// ".$item_p->total;
							echo "<br/>";
						}
					}
					
					
					if ($show_msg) echo "<br/>===============================================================================<br/>";
				}
			}
		}
		
		return $result;
	}
	
	public function file_process($show_msg = false){
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		copy("./upload/sa_promotion.xls", "./upload/sa_promotion_result.xls");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/sa_promotion.xls");
		$sheet = $spreadsheet->getSheetByName("CALCULATE");
		
		$promotions = $this->set_promotions($sheet, $show_msg);
		
		//result writing - promotion
		$spreadsheet = IOFactory::load("./upload/sa_promotion_result.xls");
		$sheet = $spreadsheet->getSheetByName("CALCULATE");
		
		$max_row = $sheet->getHighestRow();

		//save file records in array
		for($i = 2; $i < $max_row; $i++){
			$prom_key = $sheet->getCell('D'.$i)->getValue()."_".$sheet->getCell('E'.$i)->getValue();
			$prom = $promotions[$prom_key];
			/*
			stdClass Object ( 
				[promotion_id] => 14158 
				[prom] => PR-NTSO-20240529-0008 
				[prom_line] => 19 
				[date_start] => 2024-06-01 
				[date_end] => 2024-06-30 
				[cus_code] => PE008204001B 
				[prod_model] => 75NANO75SQA.AWF 
				[cost_sellin] => 3634.93 
				[price_prom] => 3799 
				[new_margin] => 0.18 
				[cost_prom] => 2639.98 
				[diff] => 994.95 
				[qty] => 1 
				[total] => 994.95 
			) 
			*/
			
			$sheet->getCell("K".$i)->setValue($prom->cost_sellin);
			$sheet->getCell("O".$i)->setValue($prom->diff);
			$sheet->getCell("P".$i)->setValue($prom->qty);
			$sheet->getCell("V".$i)->setValue($prom->total);
			
			if ($show_msg){
				echo $prom_key."<br/>";
				print_r($prom); echo "<br/><br/>";	
			}
		}
		
		$writer = new Xlsx($spreadsheet);
		$writer->save('./upload/sa_promotion_result.xls');
			
		return "Finished in ".number_Format(microtime(true) - $start_time, 2)." secs";
	}

	public function test(){
		echo $this->file_process(true);
	}


	public function calculation(){
		$type = "error"; $msg = $url = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
			$start_time = microtime(true);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
			];
			$this->load->library('upload', $config);

			$name_p = $name_g = "";
			
			$config['file_name'] = 'sa_promotion';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_p')){
				$data = $this->upload->data();
				$name_p = $data['orig_name'];
			}
			
			$config['file_name'] = 'sa_promotion_result';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_g')){
				$data = $this->upload->data();
				$name_g = $data['orig_name'];
			}
			
			if ($name_p and $name_g){
				$this->update_report($this->calculate_promotion($name_p), "sa_promotion_result.xlsx");
				
				$type = "success";
				$msg = "Promotion result has been calculated.";
				$url = base_url()."upload/".$name_g;
			}else $msg = "Select all files: Promotion and GERP upload file.";
		}else{
			$msg = "Your session is finished.";
			$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
