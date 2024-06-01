<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Local_api extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function test(){
		$order_items = $this->gen_m->all("order_item");
		
		//print_r($order_items);
		header('Content-Type: application/json');
		echo json_encode(["order_items" => $order_items]);
	}
	
	public function get_sales_order(){
		//http://localhost/llamasys/local_api/get_sales_order?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"order_date >=" => $f,
				"order_date <=" => $t,
			];
			
			$res = $this->gen_m->filter("dash_sales_order_inquiry", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_closed_order(){
		//http://localhost/llamasys/local_api/get_closed_order?key=lgepr&f=2024-01-01&t=2024-12-31
		
		$res = [];
		
		$key = $this->input->get("key");
		$f = $this->input->get("f");
		$t = $this->input->get("t");
		
		if ($f and $t and ($key === "lgepr")){
			$filter = [
				"order_date >=" => $f,
				"order_date <=" => $t,
			];
			
			$res = $this->gen_m->filter("dash_closed_order_inquiry", false, $filter);
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
}
