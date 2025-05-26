<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_direct_dispatch extends CI_Controller {

	public function __construct(){
		parent::__construct();
		//if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}

	public function index(){
		
		$containers_model = [];//model list by container
		$model_containers = [];//containers by model
		
		$w = ["eta >=" => date("Y-m-01"), "eta <=" => date("Y-m-t")];
		$containers = $this->gen_m->filter("custom_container", false, $w, null, null, [["eta", "asc"], ["sa_no", "asc"], ["sa_line_no", "asc"]]);
		foreach($containers as $ctn){
			//insert model to container
			if (!array_key_exists($ctn->container, $containers_model)) $containers_model[$ctn->container] = [];
			
			$containers_model[$ctn->container][] = [
				"sa_no" 		=> $ctn->sa_no,
				"sa_line_no" 	=> $ctn->sa_line_no,
				"container" 	=> $ctn->container,
				"eta" 			=> $ctn->eta,
				"ata" 			=> $ctn->ata,
				"model" 		=> $ctn->model,
				"qty" 			=> $ctn->qty,
			];
			
			//insert container to model
			if (!array_key_exists($ctn->model, $model_containers)) $model_containers[$ctn->model] = [];
			
			$model_containers[$ctn->model][] = [
				"sa_no" 		=> $ctn->sa_no,
				"sa_line_no" 	=> $ctn->sa_line_no,
				"container" 	=> $ctn->container,
				"eta" 			=> $ctn->eta,
				"ata" 			=> $ctn->ata,
				"model" 		=> $ctn->model,
				"qty" 			=> $ctn->qty,
			];
		}
		
		//load all sales orders to evalulate direct dispatch from port to customer
		$sales_orders = $this->gen_m->all("lgepr_sales_order", [["booked_date", "asc"], ["order_no", "asc"], ["line_no", "asc"]], null, null, false);
		foreach($sales_orders as $so){
			//print_r($so);
			echo $so->order_no." _ ".$so->line_no." _ ".$so->dash_company." _ ".$so->dash_division." _ ".$so->model." _ ".$so->ordered_qty." _ ".$so->ship_to_name." _ ".$so->booked_date." _ ".$so->req_arrival_date_to;
			echo "<br/><br/>";
		}
		
		
		
	
		foreach($containers_model as $container => $models){
			echo $container."<br/><br/>";
			
			foreach($models as $item){
				print_r($item);
				echo "<br/>";
			}
			echo "<br/>==========================================<br/><br/>";
		}
		
		foreach($model_containers as $model => $ctns){
			echo $model."<br/><br/>";
			
			foreach($ctns as $ctn){
				print_r($ctn);
				echo "<br/>";
			}
			echo "<br/>==========================================<br/><br/>";
		}
		
		
		
		return;
		
		$data = [
			"po_templates" => $this->gen_m->filter("scm_purchase_order_template", true, ["valid" => true], null, null, [["template", "asc"]]),
			"ship_tos" => $this->gen_m->filter("scm_ship_to", false, null, null, null, [["bill_to_name", "asc"], ["address", "asc"]]),
			"main" => "module/scm_purchase_order/index",
		];
		
		$this->load->view('layout', $data);
	}

}
