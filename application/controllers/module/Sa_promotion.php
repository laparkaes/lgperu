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
	
	public function get_init_cost($bill_to, $model, $ins, $outs){
		//sell in taken value init
		foreach($ins as $item) $item->taken = 0;
		
		if ($outs) $stock_init = $outs[0]->stock + $outs[0]->units;
		else $stock_init = 1;
		
		$counter = $stock_init;
		$considered_qty = $amount = 0;
		foreach($ins as $item){
			if ($counter > 0){
				if ($item->order_qty < 0){
					$item->taken = $item->order_qty;
					$amount += $item->unit_selling_price * $item->order_qty;
					$counter += abs($item->order_qty);
				}elseif ($counter <= $item->order_qty) {
					$item->taken = $counter;
					$amount += $item->unit_selling_price * $counter;
					$counter = 0;
				}else{
					$item->taken = $item->order_qty;
					$amount += $item->unit_selling_price * $item->order_qty;
					$counter = $counter - $item->order_qty;
				}
				
				$considered_qty += $item->taken;
				
				if (!$outs) $item->taken = 0;
			}
		}
		
		if (!$considered_qty) $considered_qty = 1;
		
		return round($amount/$considered_qty, 2);
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
			foreach($models as $m) $data[$m] = ["model" => $m, "sell_ins" => [], "sell_outs" => [], "promotions" => []];
			
			$sell_in_result = [];
			$sell_ins = $this->gen_m->filter("sa_sell_in", false, ["bill_to_code" => $bill_to, "closed_date <" => $from], null, [["field" => "model", "values" => $models]], [["closed_date", "desc"]]);
			foreach($sell_ins as $item) $data[$item->model]["sell_ins"][] = $sell_in_result[] = $item;
			
			$sell_out_result = [];
			$sell_outs = $this->gen_m->filter("sa_sell_out", false, $w, null, [["field" => "suffix", "values" => $models]]);
			foreach($sell_outs as $item) $data[$item->suffix]["sell_outs"][] = $sell_out_result[] = $item;
			
			foreach($promotions as $item) $data[$item->prod_model]["promotions"][] = $item;
			
			//start calculation
			foreach($data as $item){
				if ($item["promotions"]){
					$outs = $item["sell_outs"];
					$ins = $item["sell_ins"];
					//$this->gen_m->filter("sa_sell_in", false, ["bill_to_code" => $bill_to, "model" => $item["model"], "closed_date <" => $from], null, null, [["closed_date", "desc"]]);
					
					//calculate init cost
					$init_cost = $this->get_init_cost($bill_to, $item["model"], $ins, $item["sell_outs"]);
					
					if ($show_msg){
						echo $item["model"]; echo " ==============================================================<br/><br/>";
						echo "init_cost: ".$init_cost."<br/><br/>";
						
						echo "----------------------------------------------------------<br/>";
						echo "Sell Ins ------------------------------------------------<br/>";
						foreach($ins as $item_i){
							$aux = clone $item_i;
							unset($aux->sell_in_id);
							unset($aux->bill_to_code);
							unset($aux->bill_to_name);
							unset($aux->product_level1_name);
							unset($aux->product_level2_name);
							unset($aux->product_level3_name);
							unset($aux->product_level4_name);
							unset($aux->model_category);
							unset($aux->invoice_no);
							unset($aux->model);
							unset($aux->customer_department);
							
							print_r($aux);
							echo " /// ".$item_i->invoice_no."<br/>";
							
							if (!$item_i->taken) break;
							
						}
						
						echo "<br/>";
						echo "----------------------------------------------------------<br/>";
						echo "Sell Outs ------------------------------------------------<br/>";
						foreach($item["sell_outs"] as $item_o){
							$aux = clone $item_o;
							unset($aux->sell_out_id);
							unset($aux->account);
							unset($aux->customer_code);
							unset($aux->division);
							unset($aux->line);
							unset($aux->model);
							unset($aux->suffix);
							
							print_r($aux);
							echo "<br/>";
						}
						
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
							unset($item_p->promotion_id);
							unset($item_p->cus_code);
							unset($item_p->prod_model);
							unset($item_p->price_prom);
							unset($item_p->new_margin);
							
							print_r($item_p);
							echo "<br/>";
						}
					}
					
					if ($show_msg) echo "<br/>===============================================================================<br/>";
				}
			}
		}
		
		return ["promotions" => $result, "sell_outs" => $sell_out_result];
	}
	
	private function write_promotions($promotions, $sheet, $show_msg){
		
		if ($show_msg){
			echo "==================================================<br/>";
			echo "Promotion result: ========================== <br/><br/>";
		}
		
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
	}
	
	private function write_sell_outs($sell_outs, $sheet, $show_msg){
		
		if ($show_msg){
			echo "==================================================<br/>";
			echo "Sell out result: =========================== <br/><br/>";
		}
		
		$row_i = 2;
		foreach($sell_outs as $item){
			$sheet->getCell("A".$row_i)->setValue($item->customer_code);
			$sheet->getCell("B".$row_i)->setValue($item->account);
			$sheet->getCell("D".$row_i)->setValue($item->suffix);
			$sheet->getCell("E".$row_i)->setValue(str_replace("-", "", $item->sunday));
			$sheet->getCell("H".$row_i)->setValue($item->units);
			$sheet->getCell("I".$row_i)->setValue($item->amount);
			$sheet->getCell("J".$row_i)->setValue($item->stock);
			
			$row_i++;
			
			if ($show_msg){
				print_r($item); echo "<br/><br/>";	
			}
		}
	}
	
	public function update_models(){//same with sa_sell_inout
		//based on lvl 4
		$s = ["model", "model_category", "product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name"];
		$models = $this->gen_m->filter_select("sa_sell_in", false, $s, ["model_category !=" => null], null, null, null, null, null, "product_level4_name");
		foreach($models as $m){
			//print_r($m); echo "<br/><br/>";
			$this->gen_m->update("sa_sell_in", ["product_level1_name" => $m->product_level1_name, "product_level2_name" => $m->product_level2_name, "product_level3_name" => $m->product_level3_name, "product_level4_name" => $m->product_level4_name, "model_category" => $m->model_category], ["model" => $m->model]);
		}
		
		//based on lvl 2
		$s = ["model", "model_category", "product_level1_name", "product_level2_name"];
		$models = $this->gen_m->filter_select("sa_sell_in", false, $s, ["model_category !=" => null], null, null, null, null, null, "product_level2_name");
		foreach($models as $m){
			//print_r($m); echo "<br/><br/>";
			$this->gen_m->update("sa_sell_in", ["product_level1_name" => $m->product_level1_name, "product_level2_name" => $m->product_level2_name, "model_category" => $m->model_category], ["model" => $m->model]);
		}
	}
	
	public function update_sell_in($sheet, $show_msg){
		$max_row = $sheet->getHighestRow();

		$rows = $date_arr = [];

		//save file records in array
		for($i = 2; $i < $max_row; $i++){
			$currency = trim($sheet->getCell("H".$i)->getValue());
			$order_amount = trim($sheet->getCell("K".$i)->getValue());
			$order_amount_pen = $currency === "PEN" ? $order_amount : $order_amount * trim($sheet->getCell("L".$i)->getValue());//ER
			
			$row = [
				"bill_to_code"			=> trim($sheet->getCell("C".$i)->getValue()),
				"bill_to_name"			=> trim($sheet->getCell("BB".$i)->getValue()),
				"product_level1_name" 	=> null,
				"product_level2_name" 	=> null,
				"product_level3_name" 	=> null,
				"product_level4_name" 	=> null,
				"model_category" 		=> null,
				"model"					=> trim($sheet->getCell("D".$i)->getValue()),
				"closed_date"			=> $this->my_func->date_convert_3(trim($sheet->getCell("E".$i)->getValue())),
				"invoice_no"			=> trim($sheet->getCell("F".$i)->getValue()),
				"currency"				=> $currency,
				"customer_department"	=> "LGEPR",//always LEGPR
				"order_qty"				=> trim($sheet->getCell("I".$i)->getValue()),
				"unit_selling_price"	=> trim($sheet->getCell("J".$i)->getValue()),
				"order_amount"			=> $order_amount,
				"order_amount_pen"		=> $order_amount_pen,
			];
			
			$rows[] = $row;
			$date_arr[] = $row["closed_date"];
			//if ($show_msg){ print_r($row); echo "<br/><br/>"; }
		}
		
		if ($rows){
			$date_arr = array_unique($date_arr);
			sort($date_arr);
			
			$bill_to = $rows[0]["bill_to_code"];
			$from = reset($date_arr);//first item
			$to = end($date_arr);//last item
			
			//remove
			$this->gen_m->delete("sa_sell_in", ["bill_to_code" => $bill_to, "closed_date >=" => $from, "closed_date <=" => $to]);
			
			//insert
			$inserted = $this->gen_m->insert_m("sa_sell_in", $rows);
			
			//model information update
			$this->update_models();
		}
	}
	
	public function file_process($show_msg = false){
		/*
		Logic
		1. update sell in
		2. calculate promotions
		3. copy result file
		4. write promotion in result file
		5. write sell out in result file
		6. save result file
		7. return msg
		*/
		
		set_time_limit(0);
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/sa_promotion.xls");
		
		//1. update sell in
		$this->update_sell_in($spreadsheet->getSheetByName("SELLIN"), $show_msg);
		
		//2. calculate promotions
		$result = $this->set_promotions($spreadsheet->getSheetByName("CALCULATE"), $show_msg);
		$promotions = $result["promotions"];
		$sell_outs = $result["sell_outs"];
		
		//3. copy result file
		copy("./upload/sa_promotion.xls", "./upload/sa_promotion_result.xls");
		
		//load result file: sa_promotion_result.xls
		$spreadsheet = IOFactory::load("./upload/sa_promotion_result.xls");
		
		//4. write promotion in result file
		$this->write_promotions($promotions, $spreadsheet->getSheetByName("CALCULATE"), $show_msg);
		
		//5. write sell out in result file
		$this->write_sell_outs($sell_outs, $spreadsheet->getSheetByName("SELLOUT"), $show_msg);
		
		//6. save result file
		$writer = new Xlsx($spreadsheet);
		$writer->save('./upload/sa_promotion_result.xls');
			
		//7. return msg
		return "Finished in ".number_Format(microtime(true) - $start_time, 2)." secs";
	}

	public function test(){
		echo $this->file_process(true);
	}

	public function calculation(){
		$type = "error"; $msg = $url = "";
		
		if ($this->session->userdata('logged_in')){
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'sa_promotion',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$type = "success";
				$msg = $this->file_process();
				$url = base_url()."upload/sa_promotion_result.xls";
			}else $msg = "Error occured.";
		}else{
			$msg = "Your session is finished.";
			$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
