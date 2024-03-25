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
	
	public function index(){
		
		$data = [
			"groups" => $this->gen_m->all("product_group", [["group_name", "asc"]]),
			"categories" => $this->gen_m->all("product_category", [["category", "asc"]]),
			"products" => $this->gen_m->all("product", [["model", "asc"]]),
			"customers" => $this->gen_m->all("customer", [["customer", "asc"], ["bill_to_code", "asc"]]),
			"main" => "sa/sell_inout/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function testing(){
		$row  = new stdClass;
		$row->date = null;
		$row->invoice_id = null;
		$row->u_price = null;
		$row->sell_in = null;
		$row->sell_out = null;
		$row->stock = null;
		$row->stock_sell_out = null;
		
		$w_in = [
			"order_qty !=" => -1,
			"customer_id" => 5,
		];
		echo "<style>table td{padding: 5px 10px;}</style>";
		$groups = $this->gen_m->all("product_group", [["group_name", "asc"]]);
		foreach($groups as $g_i => $grp){
			echo "Group: ".$grp->group_name."<br/>";
			$categories = $this->gen_m->filter("product_category", true, ["group_id" => $grp->group_id]);
			foreach($categories as $cat){
				echo "Category: ".$cat->category."<br/><br/>";
				$products = $this->gen_m->filter("product", true, ["category_id" => $cat->category_id]);
				foreach($products as $prd){
					$prod_arr = [];
					
					$w_in["product_id"] = $prd->product_id;
					$sell_ins = $this->gen_m->filter("sell_in", true, $w_in, null, null, [["closed_date", "asc"]]);
					if ($sell_ins){
						$w_out = [
							"customer_id" => $w_in["customer_id"],
							"product_id" => $w_in["product_id"],
							"sunday_date >=" => ($sell_ins) ? $sell_ins[0]->closed_date : "2000-01-01",
						];
						$sell_outs = $this->gen_m->filter("sell_out", true, $w_out, null, null, [["sunday_date", "asc"]]);
						
						foreach($sell_ins as $i => $in){
							$aux = clone $row;
							$aux->date = $in->closed_date;
							$aux->invoice_id = $in->invoice_id;
							$aux->u_price = $in->unit_selling_price;
							$aux->sell_in = $in->order_qty;
							if (!$i) $aux->stock = ($sell_outs) ? $sell_outs[0]->stock + $sell_outs[0]->qty : 0;
							
							$prod_arr[] = clone $aux;
						}
						
						foreach($sell_outs as $out){
							$aux = clone $row;
							$aux->date = $out->sunday_date;
							$aux->sell_out = $out->qty;
							$aux->stock_sell_out = $out->stock;
							
							$prod_arr[] = clone $aux;
						}
						
						usort($prod_arr, function($a, $b) {
							return strtotime($a->date) > strtotime($b->date);
						});
						
						echo "- ".$prd->model."<br/><br/>";
						
						echo "<table>";
						echo "<tr><td>Date</td><td>Invoice ID</td><td>U/Price</td><td>Sell-In</td><td>Sell-Out</td><td>Stock</td><td>Stock Sell-Out</td><td>Result</td></tr>";
						
						foreach($prod_arr as $i => $pr){
							if ($i){
								if (($i == 1) and ($pr->sell_in)) $pr->stock = $prod_arr[$i-1]->stock;
								else $pr->stock = $prod_arr[$i-1]->stock + $pr->sell_in - $pr->sell_out;
							}
							echo "<tr><td>".$pr->date."</td><td>".$pr->invoice_id."</td><td>".$pr->u_price."</td><td>".$pr->sell_in."</td><td>".$pr->sell_out."</td><td>".$pr->stock."</td><td>".$pr->stock_sell_out."</td><td>Result</td></tr>";
							//print_r($pr); echo "<br/>";
							
						}
						
						echo "</table>";
						/*
						
						echo "sell-in<br/>";
						echo "<table>";
						echo "<tr><td>closed_date</td><td>invoice_id</td><td>order_qty</td><td>unit_selling_price</td><td>order_amount</td></tr>";
						foreach($sell_ins as $in){
							echo "<tr><td>".$in->closed_date."</td><td>".$in->invoice_id."</td><td>".$in->order_qty."</td><td>".$in->unit_selling_price."</td><td>".$in->order_amount."</td></tr>";
						}
						echo "</table>";
						echo "<br/>";	
						echo "sell-out<br/>";
						echo "<table>";
						echo "<tr><td>sunday_date</td><td>qty<td>stock</td></tr>";
						
						foreach($sell_outs as $out){
							echo "<tr><td>".$out->sunday_date."</td><td>".$out->qty."</td><td>".$out->stock."</td></tr>";
						}
						echo "</table>";
						*/
						
						echo "<br/><br/>";
					}
				}
				
				echo "<br/><br/>";
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
	
	
	public function set_attendance($period = null, $employee_id = null){
		if (!$period) $period = date("Y-m");
		$month = date("F", strtotime($period));
		$today = date("Y-m-d");
		
		//set dates
		$red_days = ["Sat", "Sun"];//red calendar days
		$red_dates = [];//red dates in array
		$dates = [];//all dates of month
		$headers = [];//day, day of the week
		
		$start = new DateTime(date('Y-m-01', strtotime($period)));
		$last = new DateTime(date('Y-m-t', strtotime($period)));
		$interval = new DateInterval('P1D');
		
		$now = clone $start;
		while ($now <= $last) {
			$dates[] = $now->format('Y-m-d');
			if (in_array($now->format('D'), $red_days)){
				$red_dates[] = $now->format('Y-m-d');
				$type = "H";//holiday
			}else $type = "N";//normal
			
			$headers[] = ["day" => $now->format('d'), "day_w" => $now->format('D'), "type" => $type];
			$now->add($interval);
		}
		
		//employee subsidiary, organization, department and office variable set
		$locs = [];
		$locs_rec = $this->gen_m->all("location");
		foreach($locs_rec as $l) $locs[$l->location_id] = $l;
		
		$depts = [];
		$depts_rec = $this->gen_m->all("department");
		foreach($depts_rec as $d) $depts[$d->department_id] = $d;
		
		$orgs = [];
		$orgs_rec = $this->gen_m->all("organization");
		foreach($orgs_rec as $o) $orgs[$o->organization_id] = $o;
		
		$subs = [];
		$subs_rec = $this->gen_m->all("subsidiary");
		foreach($subs_rec as $s) $subs[$s->subsidiary_id] = $s;
		
		//set employee array
		$employees = [];
		
		if ($employee_id) $w = ["employee_id" => $employee_id]; else $w = null;
		$employees_rec = $this->gen_m->filter("employee", true, $w, null, null, [["name", "asc"]]);
		foreach($employees_rec as $emp){
			$w = [
				"employee_id" => $emp->employee_id,
				"date>=" => $dates[0],
				"date<=" => $dates[count($dates)-1],
			];
			$atts = $this->gen_m->filter("attendance", true, $w, null, null, [["date", "asc"]]);
			if ($atts){
				$emp->location = ($emp->location_id) ? $locs[$emp->location_id]->location : "";
				if ($emp->department_id){
					$emp->department = $depts[$emp->department_id]->department;
					$emp->organization = $orgs[$depts[$emp->department_id]->organization_id]->organization;
					$emp->subsidiary = $subs[$orgs[$depts[$emp->department_id]->organization_id]->subsidiary_id]->subsidiary;
				}else $emp->department = $emp->organization = $emp->subsidiary = "";
				
				$emp->vacation_qty = 0;
				$emp->absence_qty = 0;
				$emp->tardiness_qty = 0;
				$emp->early_exit_qty = 0;
				$emp->tardiness_acc = "00:00";
				
				//set vacation date array
				$w = [
					"employee_id" => $emp->employee_id,
					"date_from <" => date('Y-m-01', strtotime($period)),
					"date_to >=" => date('Y-m-01', strtotime($period)),
					"date_to <=" =>date('Y-m-t', strtotime($period))
				];
				$vacations_t = $this->gen_m->filter("vacation", true, $w, null, null, [["date_to", "asc"]]);
				
				$w = [
					"employee_id" => $emp->employee_id,
					"date_from >=" => date('Y-m-01', strtotime($period)),
					"date_from <=" =>date('Y-m-t', strtotime($period))
				];
				$vacations_f = $this->gen_m->filter("vacation", true, $w, null, null, [["date_from", "asc"]]);
				
				$vacation_dates = []; $vacation_exception = [];
				$vacations = array_merge($vacations_t, $vacations_f);
				foreach($vacations as $vac){
					if ($vac->day_count < 1){
						$type = $this->gen_m->unique("vacation_type", "type_id", $vac->type_id);
						
						//$vacation_exception["entrance", "exit"] as time in string
						if (strpos($type->type, 'Morning') !== false) //half day - morning: entrance is 2pm
							$vacation_exception[$vac->date_from] = ["14:00", null];
						elseif (strpos($type->type, 'Afternoon') !== false) //half day - afternoon: exit is 2pm
							$vacation_exception[$vac->date_from] = [null, "12:30"];
					}else{
						$start = new DateTime($vac->date_from);
						$last = new DateTime($vac->date_to);
						$interval = new DateInterval('P1D');//each one day
						
						$now = clone $start;
						while ($now <= $last) {
							if (!in_array($d, $red_dates)) $vacation_dates[] = $now->format('Y-m-d');
							$now->add($interval);
						}	
					}
				}//end vacations
				
				/*
				daily attendance types
				N: normal
				X: no mark(absence)
				H: holiday
				V: vacation
				M: medical
				*/
				
				//set daily check list as no mark for all working days
				$emp->daily = [];
				foreach($dates as $d){
					if (in_array($d, $red_dates)) $emp->daily[$d] = ["type" => "H"];//holiday
					elseif (in_array($d, $vacation_dates)){
						$emp->daily[$d] = ["type" => "V"];//vacation
					}elseif (strtotime($d) < strtotime($today)){
						$emp->daily[$d] = ["type" => "X"];//no mark
					}else $emp->daily[$d] = ["type" => ""];//not yet
				}
				
				//load work hour and option records
				$w = [
					"employee_id" => $emp->employee_id,
					"date_from <=" => $dates[0],
					"date_to >=" => $dates[0],
				];
				
				$whour = $this->gen_m->filter("working_hour", true, $w);
				if ($whour){
					$whour = $whour[0];
					$whour_op = $this->gen_m->unique("working_hour_option", "option_id", $whour->wh_option_id);
				}else $whour_op = null;
				
				foreach($atts as $att){
					//update working hour option when out of range
					if ($whour) if (strtotime($whour->date_to) < strtotime($att->date)){
						$w = [
							"employee_id" => $emp->employee_id,
							"date_from <=" => $att->date,
							"date_to >=" => $att->date,
						];
						
						$whour = $this->gen_m->filter("working_hour", true, $w);
						if ($whour){
							$whour = $whour[0];
							$whour_op = $this->gen_m->unique("working_hour_option", "option_id", $whour->wh_option_id);
						}else $whour_op = null;
					}
					
					/*
					access check types
					O: ok
					T: tardance
					E: early exit
					*/
					
					$emp->daily[$att->date] = [
						"type" => "N",
						"entrance" => ["time" => $att->enter_time, "result" => "O"],
						"exit" => ["time" => $att->leave_time, "result" => "O"],
					];
					
					if ($whour_op){
						$wo_e = strtotime(date("H:i", strtotime($whour_op->entrance_time)));
						$wo_l = strtotime(date("H:i", strtotime($whour_op->exit_time)));
						
						$at_e = strtotime(date("H:i", strtotime($att->enter_time)));
						$at_l = strtotime(date("H:i", strtotime($att->leave_time)));
						
						$no_holiday = (!in_array($att->date, $red_dates));
						
						if (($at_e > $wo_e) and ($no_holiday)){//tardance
							$emp->daily[$att->date]["entrance"]["result"] = "T";
							
							//need to check if emp has entrance exception
							if (array_key_exists($att->date, $vacation_exception)){
								if (array_key_exists(0, $vacation_exception[$att->date])){
									$wo_e = strtotime(date("H:i", strtotime($vacation_exception[$att->date][0])));
									if ($at_e < $wo_e){
										$emp->daily[$att->date]["entrance"]["result"] = "V";
									}
								}
							}
							
							if ($emp->daily[$att->date]["entrance"]["result"] === "T"){
								if (strtotime($emp->tardiness_acc) < strtotime("23:59"))
									$emp->tardiness_acc = date("H:i", strtotime($emp->tardiness_acc) + $at_e - $wo_e);
								
								if (strtotime($emp->tardiness_acc) > strtotime("23:59")) $emp->tardiness_acc = "23:59";
							}
						}
						
						if (($at_l < $wo_l) and ($no_holiday)){//early exit
							$emp->daily[$att->date]["exit"]["result"] = "E";
							
							//need to check if emp has exit exception
							if (array_key_exists($att->date, $vacation_exception)){
								if (array_key_exists(1, $vacation_exception[$att->date])){
									$wo_l = strtotime(date("H:i", strtotime($vacation_exception[$att->date][1])));
									if ($at_l > $wo_l){
										$emp->daily[$att->date]["exit"]["result"] = "V";
									}
								}
							}
						}
					}
				}
				
				foreach($emp->daily as $date => $val){
					switch($val["type"]){
						case "N": 
							if ($val["entrance"]["result"] === "T") $emp->tardiness_qty++;
							elseif ($val["entrance"]["result"] === "V") $emp->vacation_qty += 0.5;
							
							if ($val["exit"]["result"] === "E") $emp->early_exit_qty++;
							else if ($val["exit"]["result"] === "V") $emp->vacation_qty += 0.5;
							break;
						case "X": $emp->absence_qty++; break;
						case "V": $emp->vacation_qty++; break;
					}
				}
				
				$employees[] = clone $emp;
			}
		}
		
		return [
			"month" => date("F", strtotime($period)),
			"employees" => $employees,
			"dates" => $dates,
			"headers" => $headers,
		];
	}
	
	private function columnIndexToLetters($index) {
		$letters = '';
		while ($index > 0) {
			$index--; // 1부터 시작하도록 감소
			$letters = chr($index % 26 + 65) . $letters; // ASCII 코드를 문자로 변환하여 문자열에 추가
			$index = intval($index / 26); // 다음 자리수 계산
		}
		return $letters;
	}

	public function set_attendance_view(){
		$data = $this->set_attendance("2024-02");
		print_r($data);
	}

	public function export_monthly_report(){
		$type = "error"; $msg = null; $url = "";
		
		$period = $this->input->post("period");
		if (!$period) $period = date("Y-m");
		
		$period = "2024-02";
		
		$data = $this->set_attendance($period);
		
		$headers = $data["headers"];
		$dates = $data["dates"];
		$employees = $data["employees"];
		
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		
		//set report parameters
		$sheet->setCellValueByColumnAndRow(1, 1, "Attendance Monthly Report");
		$sheet->setCellValueByColumnAndRow(1, 2, "Period");
		$sheet->setCellValueByColumnAndRow(2, 2, $period);
		$sheet->setCellValueByColumnAndRow(1, 3, "Created");
		$sheet->setCellValueByColumnAndRow(2, 3, date('Y-m-d H:i:s'));
		
		//set headers
		$sheet->setCellValueByColumnAndRow(1, 5, '#');
		$sheet->setCellValueByColumnAndRow(2, 5, 'Emp.Num');
		$sheet->setCellValueByColumnAndRow(3, 5, 'Employee');
		$sheet->setCellValueByColumnAndRow(4, 5, 'Subsidiary');
		$sheet->setCellValueByColumnAndRow(5, 5, 'Organization');
		$sheet->setCellValueByColumnAndRow(6, 5, 'Department');
		$sheet->setCellValueByColumnAndRow(7, 5, 'Location');
		$sheet->setCellValueByColumnAndRow(8, 5, 'Vacation');
		$sheet->setCellValueByColumnAndRow(9, 5, 'Absence');
		$sheet->setCellValueByColumnAndRow(10, 5, 'Tardiness');
		$sheet->setCellValueByColumnAndRow(11, 5, 'Tard.Acc.');
		$sheet->setCellValueByColumnAndRow(12, 5, 'Early Exit');
		
		$x_start = 13;
		foreach($headers as $i => $h){//header structure: ["day", "day_w", "type"]
			$x = $x_start + $i;
			
			$sheet->setCellValueByColumnAndRow($x, 5, $h["day"]." ".$h["day_w"]);
			if ($h["type"] === "H") $sheet->getStyleByColumnAndRow($x, 5)->getFont()->setColor(new Color($this->color_rgb["red"]));
		}
		
		$v_center = Alignment::VERTICAL_CENTER;
		$y_start = 6;
		foreach($employees as $i => $emp){
			$y = ($i * 2) + $y_start;
			
			$sheet->setCellValueByColumnAndRow(1, $y, $i + 1);
			$sheet->setCellValueByColumnAndRow(2, $y, $emp->employee_number);
			$sheet->setCellValueByColumnAndRow(3, $y, $emp->name);
			$sheet->setCellValueByColumnAndRow(4, $y, $emp->subsidiary);
			$sheet->setCellValueByColumnAndRow(5, $y, $emp->organization);
			$sheet->setCellValueByColumnAndRow(6, $y, $emp->department);
			$sheet->setCellValueByColumnAndRow(7, $y, $emp->location);
			
			$sheet->setCellValueByColumnAndRow(8, $y, ($emp->vacation_qty > 0) ? $emp->vacation_qty : "");
			$sheet->setCellValueByColumnAndRow(9, $y, ($emp->absence_qty > 0) ? $emp->absence_qty : "");
			$sheet->setCellValueByColumnAndRow(10, $y, ($emp->tardiness_qty > 0) ? $emp->tardiness_qty : "");
			$sheet->setCellValueByColumnAndRow(11, $y, ($emp->tardiness_qty > 0) ? $emp->tardiness_acc : "");
			$sheet->setCellValueByColumnAndRow(12, $y, ($emp->early_exit_qty > 0) ? $emp->early_exit_qty : "");

			foreach($dates as $idate => $d){
				$x = $x_start + $idate;
				$xl = $this->columnIndexToLetters($x);
				
				if ($emp->daily[$d]["type"] === "N"){
					if ($emp->daily[$d]["entrance"]["result"] === "V"){ 
						$en_color = $this->color_rgb["green"];
						$en_val = $emp->daily[$d]["entrance"]["result"];
					}else{
						$en_color = ($emp->daily[$d]["entrance"]["result"] === "T") ? $this->color_rgb["red"] : ""; 
						$en_val = date("H:i", strtotime($emp->daily[$d]["entrance"]["time"]));
					}
					
					if ($emp->daily[$d]["exit"]["result"] === "V"){ 
						$ex_color = $this->color_rgb["green"];
						$ex_val = $emp->daily[$d]["exit"]["result"];
					}else{
						$ex_color = ($emp->daily[$d]["exit"]["result"] === "E") ? $this->color_rgb["red"] : ""; 
						$ex_val = date("H:i", strtotime($emp->daily[$d]["exit"]["time"]));
					}
					
					//entrance value & color
					$sheet->setCellValueByColumnAndRow($x, $y, $en_val);
					$sheet->getStyleByColumnAndRow($x, $y)->getFont()->setColor(new Color($en_color));
					
					//exit value & color
					$sheet->setCellValueByColumnAndRow($x, ($y + 1), $ex_val);
					$sheet->getStyleByColumnAndRow($x, ($y + 1))->getFont()->setColor(new Color($ex_color));
				}else{
					switch($emp->daily[$d]["type"]){
						case "X": $d_color = $this->color_rgb["red"]; break;
						case "V": $d_color = $this->color_rgb["green"]; break;
						default: $d_color = "";
					}
					
					$sheet->setCellValueByColumnAndRow($x, $y, $emp->daily[$d]["type"]);
					$sheet->getStyleByColumnAndRow($x, $y)->getFont()->setColor(new Color($d_color));
					
					$sheet->mergeCells($xl.$y.':'.$xl.($y + 1));
					$sheet->getStyle($xl.$y)->getAlignment()->setVertical($v_center);
				}
			}

			//merge cells of employee general info
			for($c = 1; $c < $x_start; $c++){
				$cl = $this->columnIndexToLetters($c);
				
				$sheet->mergeCells($cl.$y.':'.$cl.($y + 1));
				$sheet->getStyle($cl.$y)->getAlignment()->setVertical($v_center);
			}
		}
		
        // Save Excel file to a temporary directory
		$file_name = 'attandance_202402.xlsx';
        $file_path = './upload/report/';
        $writer = new Xlsx($spreadsheet);
        $writer->save($file_path.$file_name);
		
		// Make file url
		if (file_exists($file_path)){
			$type = "success";
			$url = base_url()."upload/report/".$file_name;
		}else $msg = "An error occured exporting report. Try again.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
