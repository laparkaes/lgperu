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
		$containers = $this->gen_m->filter("lgepr_container", false, $w, null, null, [["eta", "asc"], ["sa_no", "asc"], ["sa_line_no", "asc"]]);
		foreach($containers as $ctn){
			
			$appointment = date("Y-m-d", strtotime(($ctn->ata ? $ctn->ata : $ctn->eta)." +3 days"));
			
			//insert model to container
			if (!array_key_exists($ctn->container, $containers_model)) $containers_model[$ctn->container] = [];
			
			$containers_model[$ctn->container][] = [
				"sa_line" 		=> $ctn->sa_no."_".$ctn->sa_line_no,
				"container" 	=> $ctn->container,
				"eta" 			=> $ctn->eta,
				"ata" 			=> $ctn->ata,
				"appointment"	=> $appointment,
				"model" 		=> $ctn->model,
				"qty" 			=> $ctn->qty,
			];
			
			//insert container to model
			if (!array_key_exists($ctn->model, $model_containers)) $model_containers[$ctn->model] = [];
			
			$model_containers[$ctn->model][] = [
				"sa_line" 		=> $ctn->sa_no."_".$ctn->sa_line_no,
				"container" 	=> $ctn->container,
				"eta" 			=> $ctn->eta,
				"ata" 			=> $ctn->ata,
				"appointment"	=> $appointment,
				"model" 		=> $ctn->model,
				"qty" 			=> $ctn->qty,
			];
		}
		
		$possible_so = 0;
		
		//load all sales orders to evalulate direct dispatch from port to customer
		$sales_orders = $this->gen_m->all("lgepr_sales_order", [["booked_date", "asc"], ["order_no", "asc"], ["line_no", "asc"]], null, null, false);
		foreach($sales_orders as $so){
			//print_r($so);
			if (!$so->booked_date) $so->booked_date = $so->create_date;
			
			if ((!in_array($so->ship_to_name, ["B2E", "B2C"]))){
				
				$best_options = $second_options = $multi_model = $missmatch_qty = $out_date = $etc_ctns = [];
				$containers = []; if (array_key_exists($so->model, $model_containers)) $containers = $model_containers[$so->model];
				foreach($containers as $ctn){
					
					/*
					best options	: multiple qty
					second options	: no multiple qty and only model in ctn
					multi models	: multi models in ctn
					out of date		: ctn eta/ata out of SO date (booked ~ req arrival)
					etc ctns		: other ctns
					*/
					
					if ((strtotime($so->booked_date) <= strtotime($ctn["appointment"])) and (strtotime($so->req_arrival_date_to) >= strtotime($ctn["appointment"]))){
						if (count($containers_model[$ctn["container"]]) == 1){
							if ($so->ordered_qty % $ctn["qty"] == 0) $best_options[] = $ctn;
							else $second_options[] = $ctn;
						}else $multi_model[] = $ctn;
					}else $out_date[] = $ctn;
					
					
					/*
					print_r($ctn);
					echo "<br/>";
					
					foreach($containers_model[$ctn['container']] as $ctn_models){
						echo "---- ";
						print_r($ctn_models);
						echo "<br/>";
					}
					*/	
				}
				
				if ($best_options){
					
					$possible_so++;
					
					echo $so->order_no." _ ".$so->line_no." _ ".$so->dash_company." _ ".$so->dash_division." _ ".$so->model." _ ".$so->ordered_qty." _ ".$so->ship_to_name." _ ".$so->booked_date." _ ".$so->req_arrival_date_to;
					echo "<br/>";
					
					echo "<br/>Posible ctn =======================<br/>";
					foreach($best_options as $ctn){
						print_r($ctn);
						echo "Models: ".count($containers_model[$ctn["container"]]);
						echo "<br/>";
					}
					echo "==============================<br/>";
					
					if ($multi_model){
						echo "<br/>Multi models =======================<br/>";
						foreach($multi_model as $ctn){
							print_r($ctn);
							echo "Models: ".count($containers_model[$ctn["container"]]);
							echo "<br/>";
						}
						echo "==============================<br/>";
					}
					
					if ($missmatch_qty){
						echo "<br/>Qty missmatch =======================<br/>";
						foreach($missmatch_qty as $ctn){
							print_r($ctn);
							echo "Models: ".count($containers_model[$ctn["container"]]);
							echo "<br/>";
						}
						echo "==============================<br/>";
					}
					
					if ($out_date){
						echo "<br/>Out of date =======================<br/>";
						foreach($out_date as $ctn){
							print_r($ctn);
							echo "Models: ".count($containers_model[$ctn["container"]]);
							echo "<br/>";
						}
						echo "==============================<br/>";
					}
					
					if ($etc_ctns){
						echo "<br/>Other ctns =======================<br/>";
						foreach($etc_ctns as $ctn){
							print_r($ctn);
							echo "Models: ".count($containers_model[$ctn["container"]]);
							echo "<br/>";
						}
						echo "==============================<br/>";
					}
					
					echo "<br/><br/>";
				}
			}
		}
		
		echo $possible_so;
		
		return;
		
	
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
