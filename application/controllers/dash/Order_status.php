<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Order_status extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	private function get_dash_company_division($debug = false){
		$dpts = $this->gen_m->filter("lgepr_lookup", false, ["code" => "department"], null, null, [["seq", "asc"]]);
		$coms = $this->gen_m->filter("lgepr_lookup", false, ["code" => "company"], null, null, [["seq", "asc"]]);
		$divs = $this->gen_m->filter("lgepr_lookup", false, ["code" => "division"], null, null, [["attr_1", "asc"], ["seq", "asc"]]);//attr_1: company
		
		if ($debug){
			echo "Debug start: Departments, Companies & Divisions ------------------------------------<br/><br/>";
			
			foreach($dpts as $item){ print_R($item); echo "<br/>"; }
			echo "<br/><br/>";
			foreach($coms as $item){ print_R($item); echo "<br/>"; }
			echo "<br/><br/>";
			foreach($divs as $item){ print_R($item); echo "<br/>"; }
			
			echo "<br/>Debug end: Departments, Companies & Divisions ------------------------------------<br/><br/>";
		}
		
		$cols = [
			"monthly_report" => 0,
			"ml" => 0,
			"ml_actual" => 0,
			"po_needs" => 0,
			"sales_projection" => 0,
			"sales_projection_per" => 0,
			"actual_original" => 0,
			"actual" => 0,
			"actual_per" => 0,
			"expected" => 0,
			"shipped" => 0,
			"picking" => 0,
			"appointment" => 0,
			"customer" => 0,
			"requested" => 0,
			"no_booked" => 0,
			"no_alloc" => 0,
			"sales_deduction" => 0,
		];
		
		$data = [];
		foreach($dpts as $dpt){
			$data[$dpt->lookup]["data"] = $cols;
			$data[$dpt->lookup]["coms"] = [];
		
			foreach($coms as $com){
				$data[$dpt->lookup]["coms"][$com->lookup]["data"] = $cols;
				$data[$dpt->lookup]["coms"][$com->lookup]["divs"] = [];
			}
			
			foreach($divs as $item){
				$data[$dpt->lookup]["coms"][$item->attr_1]["divs"][$item->lookup]["data"] = $cols;
			}
		}
		
		if ($debug){
			echo "Debug start: Data array ------------------------------------<br/><br/>";
			
			foreach($data as $dpt => $dpt_item){
				echo $dpt."<br/>";
				print_r($dpt_item["data"]);
				echo "<br/><br/>";
				
				foreach($dpt_item["coms"] as $com => $com_item){
					echo $dpt." >>> ".$com."<br/>";
					print_r($com_item["data"]);
					echo "<br/><br/>";	
					
					foreach($com_item["divs"] as $div => $div_item){
						echo $dpt." >>> ".$com." >>> ".$div."<br/>";
						print_r($div_item["data"]);
						echo "<br/><br/>";	
					}	
				}
			}
			
			echo "<br/>Debug end: Data array ------------------------------------<br/><br/>";
		}
		
		return $data;
	}
	
	public function index(){
		$d = $this->input->get("d");
		if (!$d) $d = date("Y-m");
		
		$total = [
			"monthly_report" => 0,
			"ml" => 0,
			"ml_actual" => 0,
			"po_needs" => 0,
			"sales_projection" => 0,
			"sales_projection_per" => 0,
			"actual_original" => 0,
			"actual" => 0,
			"actual_per" => 0,
			"expected" => 0,
			"shipped" => 0,
			"picking" => 0,
			"appointment" => 0,
			"customer" => 0,
			"requested" => 0,
			"no_booked" => 0,
			"no_alloc" => 0,
			"sales_deduction" => 0,
		];
		
		//setting initial arrays
		$rows = $this->get_dash_company_division(false);
		$data_no_mapping = [];
		
		//setting closed orders
		$w = ["closed_date >=" => date("Y-m-01", strtotime($d)), "closed_date <=" => date("Y-m-t", strtotime($d))];
		$o = [["closed_date", "desc"], ["order_no", "desc"], ["line_no", "asc"]];
		
		$closed_orders = $this->gen_m->filter("lgepr_closed_order", false, $w, null, null, $o);
		foreach($closed_orders as $item){
			/* closed_orders debugging
			print_r($item);
			echo "<br/>";
			echo $item->order_no." ".$item->line_no."<br/>";
			echo $item->category."<br/>";
			echo $item->customer_department."<br/>";
			echo $item->dash_company."<br/>";
			echo $item->dash_division."<br/>";
			echo $item->order_amount_usd."<br/>";
			echo "<br/><br/>";
			*/
			
			if ($item->order_amount_usd){
				if (array_key_exists($item->customer_department, $rows)) {
					if (array_key_exists($item->dash_company, $rows[$item->customer_department]["coms"])) {
						if (array_key_exists($item->dash_division, $rows[$item->customer_department]["coms"][$item->dash_company]["divs"])) {
							$total["actual_original"] += $item->order_amount_usd;
							$rows[$item->customer_department]["data"]["actual_original"] += $item->order_amount_usd;
							$rows[$item->customer_department]["coms"][$item->dash_company]["data"]["actual_original"] += $item->order_amount_usd;
							$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"]["actual_original"] += $item->order_amount_usd;
						}else $data_no_mapping[] = clone $item;
					}else $data_no_mapping[] = clone $item;
				}else $data_no_mapping[] = clone $item;	
			}
		}
		
		//setting sales deduction (sd)
		$dpt_deductions = ["LGEPR"];//LGEPR is unique department applying sales deduction
		$w_sd = ["yyyy" => date("Y", strtotime($d)), "mm" => date("m", strtotime($d))];
		
		$sub_sd_amount = 0;
		foreach($dpt_deductions as $dpt){
			$dpt_sd_amount = 0;
			
			foreach($rows[$dpt]["coms"] as $com => $item_com){
				$com_sd_amount = 0;
				
				foreach($item_com["divs"] as $div => $item_div){
					$div_sd_amount = 0;
					
					//load sd
					$w_sd["company"] = $com;
					$w_sd["division"] = $div;
					
					$sd = $this->gen_m->filter("lgepr_sales_deduction", false, $w_sd);
					if ($sd) $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["sales_deduction"] = $sd[0]->sd_rate;
					
					//calculate sales deduction % and amount of division
					$div_sd = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["sales_deduction"];
					$div_sd_amount = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual_original"] * $div_sd;
					
					//calculate actual
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual"] = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual_original"] - $div_sd_amount;
					
					$sub_sd_amount += $div_sd_amount;
					$dpt_sd_amount += $div_sd_amount;
					$com_sd_amount += $div_sd_amount;
				}
				
				$com_actual_original = $rows[$dpt]["coms"][$com]["data"]["actual_original"];
				
				//calculate sales deduction % and amount of company
				$rows[$dpt]["coms"][$com]["data"]["actual"] = $com_actual_original - $com_sd_amount;
				$rows[$dpt]["coms"][$com]["data"]["sales_deduction"] = $com_sd_amount / $com_actual_original;
			}
				
			$dpt_actual_original = $rows[$dpt]["data"]["actual_original"];
			
			//calculate sales deduction % and amount of company
			$rows[$dpt]["data"]["actual"] = $dpt_actual_original - $dpt_sd_amount;
			$rows[$dpt]["data"]["sales_deduction"] = $dpt_sd_amount / $dpt_actual_original;
		}
		
		$sub_actual_original = $total["actual_original"];

		//calculate sales deduction % and amount of company
		$total["actual"] = $sub_actual_original - $sub_sd_amount;
		$total["sales_deduction"] = $sub_sd_amount / $sub_actual_original;
		
		//setting sales orders
		$w = [];
		$o = [["booked_date", "desc"], ["order_no", "desc"], ["line_no", "asc"]];
		
		$sales_orders = $this->gen_m->filter("lgepr_sales_order", false, $w, null, null, $o);
		foreach($sales_orders as $item){
			/*
			echo "bill_to ".$item->bill_to."<br/>";
			echo "dash_company ".$item->dash_company."<br/>";
			echo "dash_division ".$item->dash_division."<br/>";
			echo "order_no ".$item->order_no."<br/>";
			echo "line_no ".$item->line_no."<br/>";
			echo "line_status ".$item->line_status."<br/>";
			echo "model ".$item->model."<br/>";
			echo "ordered_qty ".$item->ordered_qty."<br/>";
			echo "sales_amount_usd ".$item->sales_amount_usd."<br/>";
			echo "customer_department ".$item->customer_department."<br/>";
			echo "--------------------------<br/>";
			echo "create_date ".$item->create_date."<br/>";
			echo "booked_date ".$item->booked_date."<br/>";
			echo "req_arrival_date_to ".$item->req_arrival_date_to."<br/>";
			echo "appointment_date ".$item->appointment_date."<br/>";
			echo "shipment_date ".$item->shipment_date."<br/>";
			echo "<br/><br/>";
			print_r($item); echo "<br/><br/>";
			*/
			
			if ($item->sales_amount_usd){
				if (array_key_exists($item->customer_department, $rows)) {
					if (array_key_exists($item->dash_company, $rows[$item->customer_department]["coms"])) {
						if (array_key_exists($item->dash_division, $rows[$item->customer_department]["coms"][$item->dash_company]["divs"])) {
							if ($item->shipment_date){
								$total["shipped"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["data"]["shipped"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["data"]["shipped"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"]["shipped"] += $item->sales_amount_usd;	
							}elseif ($item->appointment_date){
								$total["appointment"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["data"]["appointment"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["data"]["appointment"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"]["appointment"] += $item->sales_amount_usd;	
							}elseif ($item->booked_date){
								$total["requested"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["data"]["requested"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["data"]["requested"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"]["requested"] += $item->sales_amount_usd;	
							}else{
								$total["no_booked"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["data"]["no_booked"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["data"]["no_booked"] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"]["no_booked"] += $item->sales_amount_usd;	
							}
						}else $data_no_mapping[] = clone $item;
					}else $data_no_mapping[] = clone $item;
				}else $data_no_mapping[] = clone $item;
			}
		}
		
		print_r($total); echo "<br/><br/>";
		
		/* rows debugging */
		foreach($rows as $dpt => $dpt_item){
			echo $dpt."<br/>";
			print_r($dpt_item["data"]);
			echo "<br/><br/>";
			
			foreach($dpt_item["coms"] as $com => $com_item){
				echo $dpt." >>> ".$com."<br/>";
				print_r($com_item["data"]);
				echo "<br/><br/>";	
				
				foreach($com_item["divs"] as $div => $div_item){
					echo $dpt." >>> ".$com." >>> ".$div."<br/>";
					print_r($div_item["data"]);
					echo "<br/><br/>";
				}	
			}
		}
		
		echo "-----------------------------<br/><br/>";
		
		/* data_no_mapping debugging */
		foreach($data_no_mapping as $item){
			print_r($item);
			echo "<br/><br/>";
		}
		
		
		
		
		
		
		$data["overflow"] = "";
		$data["main"] = "dashboard/order_status";
		
		//$this->load->view('layout_dashboard', $data);
	}

}

