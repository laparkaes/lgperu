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
			"actual" => 0,//actual
			"actual_per" => 0,//actual %
			"expected" => 0,//expected
			"shipped" => 0,
			"picking" => 0,
			"appointment" => 0,
			"customer" => 0,
			"requested" => 0,
			"reviewing" => 0,
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
		
		$rows = $this->get_dash_company_division(false);
		$data_no_mapping = [];
		
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
			
			if (array_key_exists($item->customer_department, $rows)) {
				if (array_key_exists($item->dash_company, $rows[$item->customer_department]["coms"])) {
					if (array_key_exists($item->dash_division, $rows[$item->customer_department]["coms"][$item->dash_company]["divs"])) {
						$rows[$item->customer_department]["data"]["actual"] += $item->order_amount_usd;
						$rows[$item->customer_department]["coms"][$item->dash_company]["data"]["actual"] += $item->order_amount_usd;
						$rows[$item->customer_department]["coms"][$item->dash_company]["divs"][$item->dash_division]["data"]["actual"] += $item->order_amount_usd;
					}else $data_no_mapping[] = clone $item;
				}else $data_no_mapping[] = clone $item;
			}else $data_no_mapping[] = clone $item;
		}
		
		
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
		
		/* data_no_mapping debugging */
		print_r($data_no_mapping);
		
		
		
		
		
		
		$data["overflow"] = "";
		$data["main"] = "dashboard/order_status";
		
		//$this->load->view('layout_dashboard', $data);
	}

}
