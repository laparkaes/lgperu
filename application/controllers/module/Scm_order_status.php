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
	
	public function date_convert($date) {
		if (is_numeric($date)) {
			// Si es un número (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número
		$aux = explode("/", $date);
		if (count($aux) == 3) {
			// Verificamos que la fecha esté en formato mm/dd/yyyy
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}
		
		// Si la fecha no está en un formato esperado, devolvemos null
		return null;
	}
	
	public function upload_update(){
		$type = "error"; $msg = "";
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> '*',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'scm_update_status',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$type = "success";
			$msg = $this->update_status();
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function update_status(){ // update status in same controlle of shipping status
		ini_set('memory_limit', -1);
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/scm_update_status.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A2')->getValue()),
			trim($sheet->getCell('B2')->getValue()),
			trim($sheet->getCell('C2')->getValue()),
			trim($sheet->getCell('D2')->getValue()),
			trim($sheet->getCell('E2')->getValue()),
			trim($sheet->getCell('F2')->getValue()),
			trim($sheet->getCell('G2')->getValue()),
		];
		
		//sales order header
		$header = ["DIVISION", "MODELO", "Bill To Name", "Customer PO No.", "Order No.", "Line No.", "Order Qty"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			$batch_size = 200;
			$batch_data = [];
			$row_counter = 0;
			$batch_data_eq = [];
			//define now
			$now = date('Y-m-d H:i:s');

			for($i = 3; $i <= $max_row; $i++){
				$row = [
					'model' 			=> trim($sheet->getCell('B'.$i)->getValue()),
					'bill_to_name' 		=> trim($sheet->getCell('C'.$i)->getValue()),
					'customer_po' 		=> trim($sheet->getCell('D'.$i)->getValue()),
					'order_no' 			=> trim($sheet->getCell('E'.$i)->getValue()),
					'line_no' 			=> trim($sheet->getCell('F'.$i)->getValue()),
					'order_qty'			=> trim($sheet->getCell('G'.$i)->getValue()),
					'inventory_org'		=> trim($sheet->getCell('J'.$i)->getValue()),
					'sub_inventory'		=> trim($sheet->getCell('K'.$i)->getValue()),
					'om_line_status'	=> trim($sheet->getCell('N'.$i)->getValue()),
					'om_appointment'	=> trim($sheet->getCell('O'.$i)->getValue()),
					"om_updated_at" 	=> date("Y-m-d H:i:s"),				
				];
				
				if (empty($row['model']) && empty($row['bill_to_name']) && empty($row['customer_po']) && empty($row['order_no']) && empty($row['line_no'])) continue;
				
				// om_appointment -> timestamp format
				$row['om_appointment'] = $this->date_convert($row['om_appointment']);
				if (empty($row['om_appointment'])){
					$row['om_appointment'] = null;
				} else $row['om_appointment'] = $row['om_appointment'] . " 00:00:00";
				
				//$row['om_appointment'] = $row['om_appointment'] . " 00:00:00";
				$order_line = $row['order_no'] . "_". $row['line_no'];
				$batch_data[] = ['order_line' => $order_line, 'om_line_status' => $row['om_line_status'], 'om_appointment' => $row['om_appointment'], 'om_updated_at' => $row['om_updated_at']];
			}	
			
			//echo '<pre>'; print_r($batch_data);
			$row_counter = count($batch_data);
			
			// update sales order table
			$this->gen_m->update_multi('lgepr_sales_order', $batch_data, 'order_line'); 
			
		} else $msg = "Wrong file.";
		
		$msg = "Finished.<br/><br/>Records: ".number_format($row_counter)."<br/>Time: ".number_Format(microtime(true) - $start_time, 2)." secs";
		
		return $msg;
	}
}
