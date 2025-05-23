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
			"ready" => 0,
			"appointment" => 0,
			"entered" => 0,
			"in_transit" => 0,
			"no_stock" => 0,
			"hold" => 0,
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
			"ready" => 0,
			"appointment" => 0,
			"entered" => 0,
			"in_transit" => 0,
			"no_stock" => 0,
			"hold" => 0,
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
		
		//no sales deduction
		$dpt_no_deductions = ["Branch"];//Branch
		foreach($dpt_no_deductions as $dpt){
			foreach($rows[$dpt]["coms"] as $com => $item_com){
				foreach($item_com["divs"] as $div => $item_div){
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual"] = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual_original"];
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["sales_deduction"] = 0;
				}
				
				$rows[$dpt]["coms"][$com]["data"]["actual"] = $rows[$dpt]["coms"][$com]["data"]["actual_original"];
				$rows[$dpt]["coms"][$com]["data"]["sales_deduction"] = 0;
			}
				
			$rows[$dpt]["data"]["actual"] = $rows[$dpt]["data"]["actual_original"];
			$rows[$dpt]["data"]["sales_deduction"] = 0;
		}
		
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
							/* by status 
							Line Status	Order		Description		Detail			Remark
							Awaiting Receipt		1				In Transit	
							Entered					2				Entered	
							Booked					3				Entered			Book w/out Appoiment date
							Appoiment				4				Appoiment		Book w/ Appoiment date
							Awaiting Fulfillment	5				Picking			Pick instruction
							Awaiting Shipping		6				Shipping		Pick confirm
							Pending pre-billing acceptance	7		Shipped			Ship confirm
							Awaiting Return			8				Shipped
							*/
							switch($item->line_status){
								case "Awaiting Receipt"		: $status_type = "in_transit"; break;
								case "Entered"				: $status_type = "entered"; break;
								case "Booked"				: $status_type = $item->appointment_date ? "entered" : "appointment"; break;
								case "Awaiting Fulfillment"	: $status_type = "ready"; break;
								case "Awaiting Shipping"	: $status_type = "ready"; break;
								case "Pending pre-billing acceptance": $status_type = "shipped"; break;
								case "Awaiting Return"		: $status_type = "shipped"; break;
							}
							
							/* by in stock & hold flag
							hold_flag		'Y'	> hold
							instock_flag	'N'	> no_stock
							*/
							if ($item->hold_flag === "Y") $status_type = "hold";
							if ($item->instock_flag === "N") $status_type = "no_stock";
							
							$total[$status_type] += $item->sales_amount_usd;
							$rows[$item->customer_department]["data"][$status_type] += $item->sales_amount_usd;
							$rows[$item->customer_department]["coms"][$item->dash_company]["data"][$status_type] += $item->sales_amount_usd;
							$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"][$status_type] += $item->sales_amount_usd;
							
							if (($status_type !== "no_stock") and ($status_type !== "hold")){
								$status_type = "expected";
								$total[$status_type] += $item->sales_amount_usd;
								$rows[$item->customer_department]["data"][$status_type] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["data"][$status_type] += $item->sales_amount_usd;
								$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"][$status_type] += $item->sales_amount_usd;
							}
						}else $data_no_mapping[] = clone $item;
					}else $data_no_mapping[] = clone $item;
				}else $data_no_mapping[] = clone $item;
			}
		}
		
		//setting most likely
		$mls = $this->gen_m->filter("lgepr_most_likely", false, ["yyyy" => date("Y", strtotime($d)), "mm" => date("m", strtotime($d))]);
		foreach($mls as $item){
			//department identify by country
			$dpt = $item->country === "PR" ? "LGEPR" : "Branch";
			
			$item->monthly_report = (float) $item->monthly_report;
			if ($item->monthly_report){
				$type = "monthly_report";
				$total[$type] += $item->monthly_report;
				$rows[$dpt]["data"][$type] += $item->monthly_report;
				$rows[$dpt]["coms"][$item->company]["data"][$type] += $item->monthly_report;
				$rows[$dpt]["coms"][$item->company]["divs"][$item->division]["data"][$type] += $item->monthly_report;	
			}
			
			$item->ml = (float) $item->ml;
			if ($item->ml){
				$type = "ml";
				$total[$type] += $item->ml;
				$rows[$dpt]["data"][$type] += $item->ml;
				$rows[$dpt]["coms"][$item->company]["data"][$type] += $item->ml;
				$rows[$dpt]["coms"][$item->company]["divs"][$item->division]["data"][$type] += $item->ml;	
			}
			
			$item->ml_actual = (float) $item->ml_actual;
			if ($item->ml_actual){
				$type = "ml_actual";
				$total[$type] += $item->ml_actual;
				$rows[$dpt]["data"][$type] += $item->ml_actual;
				$rows[$dpt]["coms"][$item->company]["data"][$type] += $item->ml_actual;
				$rows[$dpt]["coms"][$item->company]["divs"][$item->division]["data"][$type] += $item->ml_actual;	
			}
			
			//echo $dpt." ";
			//print_r($item); echo "<br/><br/>";
		}
		
		//setting progress parameters
		$total["actual_per"] = $total["ml_actual"] > 0 ? $total["actual"] / $total["ml_actual"] : 1;
		$total["sales_projection"] = $total["actual"] + $total["expected"];
		$total["sales_projection_per"] = $total["ml_actual"] > 0 ? $total["sales_projection"] / $total["ml_actual"] : 1;
		$total["po_needs"] = $total["ml_actual"] - $total["actual"];
		if ($total["po_needs"] < 0) $total["po_needs"] = 0;
		
		foreach($rows as $dpt => $dpt_item){
			$rows[$dpt]["data"]["actual_per"] = $rows[$dpt]["data"]["ml_actual"] > 0 ? $rows[$dpt]["data"]["actual"] / $rows[$dpt]["data"]["ml_actual"] : 1;
			$rows[$dpt]["data"]["sales_projection"] = $rows[$dpt]["data"]["actual"] + $rows[$dpt]["data"]["expected"];
			$rows[$dpt]["data"]["sales_projection_per"] = $rows[$dpt]["data"]["ml_actual"] > 0 ? $rows[$dpt]["data"]["sales_projection"] / $rows[$dpt]["data"]["ml_actual"] : 1;
			$rows[$dpt]["data"]["po_needs"] = $rows[$dpt]["data"]["ml_actual"] - $rows[$dpt]["data"]["actual"];
			if ($rows[$dpt]["data"]["po_needs"] < 0) $rows[$dpt]["data"]["po_needs"] = 0;
			
			foreach($dpt_item["coms"] as $com => $com_item){
				$rows[$dpt]["coms"][$com]["data"]["actual_per"] = $rows[$dpt]["coms"][$com]["data"]["ml_actual"] > 0 ? $rows[$dpt]["coms"][$com]["data"]["actual"] / $rows[$dpt]["coms"][$com]["data"]["ml_actual"] : 1;
				$rows[$dpt]["coms"][$com]["data"]["sales_projection"] = $rows[$dpt]["coms"][$com]["data"]["actual"] + $rows[$dpt]["coms"][$com]["data"]["expected"];
				$rows[$dpt]["coms"][$com]["data"]["sales_projection_per"] = $rows[$dpt]["coms"][$com]["data"]["ml_actual"] > 0 ? $rows[$dpt]["coms"][$com]["data"]["sales_projection"] / $rows[$dpt]["coms"][$com]["data"]["ml_actual"] : 1;
				$rows[$dpt]["coms"][$com]["data"]["po_needs"] = $rows[$dpt]["coms"][$com]["data"]["ml_actual"] - $rows[$dpt]["coms"][$com]["data"]["actual"];
				if ($rows[$dpt]["coms"][$com]["data"]["po_needs"] < 0) $rows[$dpt]["coms"][$com]["data"]["po_needs"] = 0;
				
				foreach($com_item["divs"] as $div => $div_item){
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual_per"] = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["ml_actual"] > 0 ? $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual"] / $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["ml_actual"] : 1;
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["sales_projection"] = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual"] + $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["expected"];
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["sales_projection_per"] = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["ml_actual"] > 0 ? $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["sales_projection"] / $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["ml_actual"] : 1;
					$rows[$dpt]["coms"][$com]["divs"][$div]["data"]["po_needs"] = $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["ml_actual"] - $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["actual"];
					if ($rows[$dpt]["coms"][$com]["divs"][$div]["data"]["po_needs"] < 0) $rows[$dpt]["coms"][$com]["divs"][$div]["data"]["po_needs"] = 0;
				}
			}
		}
		
		//set period
		$periods = [];
		$jan = date("Y-01"); 
		$now = date("Y-m");
		while(strtotime($now) >= strtotime($jan)){
			$periods[] = $now;
			$now = date("Y-m", strtotime($now." -1 month"));
		}
		
		/* total & rows debugging 
		print_r($total); echo "<br/><br/>";
		
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
		
		// data_no_mapping debugging
		foreach($data_no_mapping as $item){
			print_r($item);
			echo "<br/><br/>";
		}
		*/
		
		$data["period"] = $d;
		$data["periods"] = $periods;
		$data["total"] = $total;
		$data["rows"] = $rows;
		$data["overflow"] = "";
		$data["main"] = "dash/order_status";
		
		$this->load->view('layout_dashboard', $data);
	}

}

