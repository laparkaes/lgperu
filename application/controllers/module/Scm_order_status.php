<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_order_status extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		//$this->load->model('general_espr_model', 'gen_e');
	}
	
	public function index(){
		
		$f = [
			"f_company"		=> $this->input->get("f_company"),
			"f_division"	=> $this->input->get("f_division"),
			"f_om_status"	=> $this->input->get("f_om_status"),
			"f_so_status"	=> $this->input->get("f_so_status"),
			"f_customer"	=> $this->input->get("f_customer"),
		];
		
		//return;
		
		$w = ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"];
		$l = [];
		$o = [["bill_to_name", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		if ($f){
			if ($f["f_company"]) $w["dash_company"] = $f["f_company"];
			if ($f["f_division"]) $w["dash_division"] = $f["f_division"];
			if ($f["f_om_status"]) $w["om_line_status"] = $f["f_om_status"];
			if ($f["f_so_status"]) $w["so_status"] = $f["f_so_status"];
			
			if ($f["f_customer"]) $l[] = ["field" => "bill_to_name", "values" => [$f["f_customer"]]];
		}
		
		$sales = $this->gen_m->filter("lgepr_sales_order", false, $w, $l, null, $o);
		
		$om_status_list = [
			"CLOSED", 
			"PICK", 
			"CON CITA", 
			"POR CONFIRMAR CITA", 
			"POR SOLICITAR CITA", 
			"REFACTURACION", 
			"REGULARIZACION", 
			"POR CANCELAR PEDIDO", 
			"SIN DISTRIBUCION", 
			"SIN LINEA DE CREDITO", 
			"SIN STOCK", 
		];
		
		$so_status_list = $this->gen_m->only("lgepr_sales_order", "so_status", ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"]);
		$company_list = $this->gen_m->only("lgepr_sales_order", "dash_company", ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"]);
		$division_list = $this->gen_m->only("lgepr_sales_order", "dash_division", ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"]);
		
		$data = [
			"filter"		 => $f,
			"company_list"	 => $company_list,
			"division_list"	 => $division_list,
			"om_status_list" => $om_status_list,
			"so_status_list" => $so_status_list,
			"sales"			 => $sales,
			"main" 			 => "module/scm_order_status/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function load_sales_order(){
		$order_id = $this->input->post("order_id");
		$order = $this->gen_m->unique("lgepr_sales_order", "sales_order_id", $order_id, false);
		
		if ($order->om_appointment){
			$order->om_appointment_date = date("Y-m-d", strtotime($order->om_appointment));
			$order->om_appointment_time_hh = date("h", strtotime($order->om_appointment));
			$order->om_appointment_time_mm = date("m", strtotime($order->om_appointment));	
		}else{
			$order->om_appointment_date = null;
			$order->om_appointment_time_hh = null;
			$order->om_appointment_time_mm = null;
		}
		
		//echo $order_id; echo "<br/><br/>"; print_r($order);
		
		header('Content-Type: application/json');
		echo json_encode($order);
	}
	
	public function om_update(){
		$data = $this->input->post();
		
		$appointment_aux = "";
		if ($data["om_appointment_date"]){
			$appointment_aux = $appointment_aux.$data["om_appointment_date"];
			if ($data["om_appointment_time_hh"]){
				$appointment_aux = $appointment_aux." ".$data["om_appointment_time_hh"];
				if ($data["om_appointment_time_mm"]){
					$appointment_aux = $appointment_aux.":".$data["om_appointment_time_mm"].":00";
				}else $appointment_aux = $appointment_aux." :00:00";
			}else $appointment_aux = $appointment_aux." 00:00:00";
		}
		
		$om = [
			"om_line_status" 	=> $data["om_line_status"],
			"om_appointment" 	=> $appointment_aux === "" ? null : $appointment_aux,
			"om_appointment_remark" => $data["om_appointment_remark"],
			"om_updated_at" 	=> date("Y-m-d H:i:s"),
		];
		
		if ($this->gen_m->update("lgepr_sales_order", ["sales_order_id" => $data["order_id"]], $om)){
			$type = "success";
			$msg = "Appointment information has been updated.";
		}else{
			$type = "danger";
			$msg = "An error occured. Try again.";
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function download_report(){
		//make excel file
		$spreadsheet = new Spreadsheet();

		//edit sheet name to 'summary'
		$summarySheet = $spreadsheet->getActiveSheet();
		$summarySheet->setTitle('summary');

		/********************
		Container
		********************/

		//add 'container' sheet
		$container_sheet = $spreadsheet->createSheet();
		$container_sheet->setTitle('container');

		//make order sheet content
		$w = ["eta >=" => date("Y-m-d", strtotime("-1 month", strtotime(date("Y-m-1"))))];
		$l = [];
		$o = [["eta", "desc"], ["ata", "desc"], ["sa_no", "asc"], ["sa_line_no", "asc"]];
		
		$containers = $this->gen_m->filter("lgepr_container", false, $w, $l, null, $o);
		if ($containers){
			$ctns = [];
			
			//make header
			$header = [];
			foreach($containers[0] as $key => $val) $header[] = strtoupper(str_replace("_", " ", $key));
			
			$ctns[] = $header;//add to array
			
			//make content
			foreach($containers as $item){
				$row = [];
				foreach($item as $key => $val) $row[] = $val;
				
				$ctns[] = $row;
			}
			
			$this->write_excel($container_sheet, $ctns);	
		}

		/********************
		Shipping Status
		********************/

		//add 'shipping' sheet
		$shipping_status_sheet = $spreadsheet->createSheet();
		$shipping_status_sheet->setTitle('shipping status');
		
		//make order sheet content
		$w = ["status !=" => "Shipped(Closed)"];
		$l = [];
		$o = [["bill_to_name", "asc"], ["pick_no", "asc"], ["seq", "asc"]];
		
		$shipping = $this->gen_m->filter("scm_shipping_status", false, $w, $l, null, $o);
		if ($shipping){
			//shipping need to get amount for each pick and split date and time for appointment
			foreach($shipping as $item){
				$item->order_no = trim($item->order_no);
				$item->line_no = trim($item->line_no);
				
				$item->order_amount_usd = 0;
				$item->dash_company = null;
				$item->dash_division = null;
				
				$order = $this->gen_m->filter("lgepr_sales_order", false, ["order_no" => $item->order_no, "line_no" => $item->line_no]);
				if ($order){
					$item->order_amount_usd = $order[0]->sales_amount_usd;
					$item->dash_company = $order[0]->dash_company;
					$item->dash_division = $order[0]->dash_division;
				}else{
					$order = $this->gen_m->filter("lgepr_closed_order", false, ["order_no" => $item->order_no, "line_no" => $item->line_no]);
					if ($order){
						$item->order_amount_usd = $order[0]->order_amount_usd;
						$item->dash_company = $order[0]->dash_company;
						$item->dash_division = $order[0]->dash_division;
					}
				}
				
				$item->appointment_date = date("Y-m-d", strtotime($item->to_ship));
				$item->appointment_time = date("H:i", strtotime($item->to_ship));
			}

			$shipping_status = [];
			
			//make header
			$header = [];
			foreach($shipping[0] as $key => $val) $header[] = strtoupper(str_replace("_", " ", $key));
			
			$shipping_status[] = $header;//add to array
			
			//make content
			foreach($shipping as $item){
				$row = [];
				foreach($item as $key => $val) $row[] = $val;
				
				$shipping_status[] = $row;
			}
			
			$this->write_excel($shipping_status_sheet, $shipping_status);	
		}

		/********************
		Sales Order
		********************/

		//add 'sales order' sheet
		$sales_order_sheet = $spreadsheet->createSheet();
		$sales_order_sheet->setTitle('sales order');

		//make order sheet content
		$w = ["line_status !=" => null, "so_status !=" => "CANCELLED"];
		$l = [];
		$o = [["bill_to_name", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		$sales = $this->gen_m->filter("lgepr_sales_order", false, $w, $l, null, $o);
		if ($sales){
			$sales_orders = [];
			
			//make header
			$header = [];
			foreach($sales[0] as $key => $val) $header[] = strtoupper(str_replace("_", " ", $key));
			
			$sales_orders[] = $header;//add to array
			
			//make content
			foreach($sales as $item){
				$row = [];
				foreach($item as $key => $val) $row[] = $val;
				
				$sales_orders[] = $row;
			}
			
			$this->write_excel($sales_order_sheet, $sales_orders);	
		}
		
		/********************
		Closed Order
		********************/

		//add 'closed order' sheet
		$closed_order_sheet = $spreadsheet->createSheet();
		$closed_order_sheet->setTitle('closed order');
		
		//make order sheet content
		$from = date("Y-m-01 00:00:00");
		$to = date("Y-m-t 23:59:59");
		
		$w = ["closed_date >=" => $from, "closed_date <=" => $to];
		$l = [];
		$o = [["bill_to_name", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		$sales = $this->gen_m->filter("lgepr_closed_order", false, $w, $l, null, $o);
		if ($sales){
			$closed_orders = [];
			
			//make header
			$header = [];
			foreach($sales[0] as $key => $val) $header[] = strtoupper(str_replace("_", " ", $key));
			
			$closed_orders[] = $header;//add to array
			
			//make content
			foreach($sales as $item){
				$row = [];
				foreach($item as $key => $val) $row[] = $val;
				
				$closed_orders[] = $row;
			}
			
			$this->write_excel($closed_order_sheet, $closed_orders);	
		}
		
		/********************
		Updated at
		********************/

		//add 'last update' sheet
		$update_time_sheet = $spreadsheet->createSheet();
		$update_time_sheet->setTitle('last update');
		
		$ctn = $this->gen_m->filter("lgepr_container", false, null, null, null, [["updated_at" , "desc"]], 1);//container
		$ss_n4m = $this->gen_m->filter("scm_shipping_status", false, ["inventory_org" => "N4M"], null, null, [["updated" , "desc"]], 1);//shipping status N4M
		$ss_n4j = $this->gen_m->filter("scm_shipping_status", false, ["inventory_org" => "N4J"], null, null, [["updated" , "desc"]], 1);//shipping status N4J
		$ss_n4e = $this->gen_m->filter("scm_shipping_status", false, ["inventory_org" => "N4E"], null, null, [["updated" , "desc"]], 1);//shipping status N4E
		$ss_n4s = $this->gen_m->filter("scm_shipping_status", false, ["inventory_org" => "N4S"], null, null, [["updated" , "desc"]], 1);//shipping status N4S
		$co = $this->gen_m->filter("lgepr_closed_order", false, null, null, null, [["updated_at" , "desc"]], 1);//closed order
		$so = $this->gen_m->filter("lgepr_sales_order", false, null, null, null, [["updated_at" , "desc"]], 1);//sales order
		
		//print_r($ss); echo "<br/><br/>";
		//print_r($co); echo "<br/><br/>";
		//print_r($so); echo "<br/><br/>";
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 1, "Last update");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 3, "Container");
		$update_time_sheet->setCellValueByColumnAndRow(2, 3, $ctn ? $ctn[0]->updated_at : "");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 4, "Shipping Status (N4M)");
		$update_time_sheet->setCellValueByColumnAndRow(2, 4, $ss_n4m ? $ss_n4m[0]->updated : "");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 5, "Shipping Status (N4J)");
		$update_time_sheet->setCellValueByColumnAndRow(2, 5, $ss_n4j ? $ss_n4j[0]->updated : "");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 6, "Shipping Status (N4E)");
		$update_time_sheet->setCellValueByColumnAndRow(2, 6, $ss_n4e ? $ss_n4e[0]->updated : "");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 7, "Shipping Status (N4S)");
		$update_time_sheet->setCellValueByColumnAndRow(2, 7, $ss_n4s ? $ss_n4s[0]->updated : "");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 8, "Closed Order");
		$update_time_sheet->setCellValueByColumnAndRow(2, 8, $co ? $co[0]->updated_at : "");
		
		$update_time_sheet->setCellValueByColumnAndRow(1, 9, "Sales Order");
		$update_time_sheet->setCellValueByColumnAndRow(2, 9, $so ? $so[0]->updated_at: "");
		
		
		//save excel file
		$writer = new Xlsx($spreadsheet);
		$filePath = 'report/oi_shipping_and_order.xlsx';
		$writer->save($filePath);
		
		echo "Report has been generated.";
		echo "<br/><br/>";
		echo "You can close this tab now.";
		echo "<br/><br/>";
		echo '<a href="'.$filePath.'"><button>Download Report</button></a>';
		echo '<button onclick="window.close();">Close This Tab</button>';
		
		redirect($filePath);
	}
	
	private function write_excel($sheet, $data){
		foreach($data as $row_n => $row){
			foreach($row as $col_n => $col){
				//$sheet->setCellValueByColumnAndRow($col_n + 1, $row_n + 1, $col);//with sales order id
				$sheet->setCellValueByColumnAndRow($col_n, $row_n + 1, $col);//without sales order id
			}	
		}
	}
	
	public function make_report(){
		$f = $this->input->post();
		
		$w = ["order_category" => "ORDER", "line_status !=" => null, "so_status !=" => "CANCELLED"];
		$l = [];
		$o = [["bill_to_name", "asc"], ["order_no", "asc"], ["line_no", "asc"]];
		
		if ($f){
			if ($f["f_company"]) $w["dash_company"] = $f["f_company"];
			if ($f["f_division"]) $w["dash_division"] = $f["f_division"];
			if ($f["f_om_status"]) $w["om_line_status"] = $f["f_om_status"];
			if ($f["f_so_status"]) $w["so_status"] = $f["f_so_status"];
			
			if ($f["f_customer"]) $l[] = ["field" => "bill_to_name", "values" => [$f["f_customer"]]];
		}
		
		$sales = $this->gen_m->filter("lgepr_sales_order", false, $w, $l, null, $o);
		
		print_r($sales);
	}
	
	public function espr(){
		
		/*
		T_OPEN_STATUS
		T_OPEN_ORDER
		*/
		
		ini_set('sqlsrv.ClientBufferMaxKBSize', 100000); // 20MB
		
		
		$order_status = $this->gen_e->only("T_OPEN_STATUS", "ESTATUS");
		$open_order_status = $this->gen_e->all("T_OPEN_STATUS", [["ESTATUS", "asc"]]);
		$open_order = $this->gen_e->all("T_OPEN_ORDER");
		
		foreach($order_status as $item){echo $item["ESTATUS"]."<br/>";} echo "<br/><br/><br/>";
		
		echo count($open_order_status)."<br/><br/><br/>";
		
		echo count($open_order)."<br/><br/><br/>";
		
		foreach($open_order_status as $item){
			print_r($item); echo "<br/>";
			
			if ($item["ESTATUS"]) $this->gen_m->update("lgepr_sales_order", ["order_no" => $item["SO NO"], "line_no" => $item["SO Line NO"]], ["om_line_status" => $item["ESTATUS"]]);
		}
		
		echo "<br/>//////////////////////////////////////////////<br/><br/>";
		
		foreach($open_order as $k => $item){
			//print_r($item); echo "<br/>";
			echo $item["SO Line Status Code3"]." ||| ".$item["SO NO"]." ||| ".$item["SO Line NO"]." ||| ".$item["Sales Order Amount (USD,Tax Exclude)"]."<br/>";
			
			//echo "<br/>";
		}
		
		
		
		return;
		
	}
	
}
