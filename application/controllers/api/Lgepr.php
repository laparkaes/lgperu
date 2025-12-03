<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->load->model('general_espr_model', 'gen_e');
	}
	
	public function get_month(){
		//llamasys/api/lgepr/get_month?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$year = date("Y");
			$months = [];

			for ($i = 1; $i <= 12; $i++) {
				$monthString = sprintf("%s-%02d", $year, $i);
				$months[] = $monthString;
			}
			
			//$o = [["create_date", "desc"], ["req_arrival_date_to", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			
			$res = $months;
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_company(){
		//llamasys/api/lgepr/get_company?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["company" => "HS", "seq" => "a"],
				["company" => "MS", "seq" => "b"],
				["company" => "ES", "seq" => "c"],
			];
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_division(){
		//llamasys/local_api/get_division?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["company" => "HS",	"division" => "REF",			"seq" => "a"],
				["company" => "HS",	"division" => "Cooking",		"seq" => "b"],
				["company" => "HS",	"division" => "Dishwasher",		"seq" => "c"],
				["company" => "HS",	"division" => "W/M",			"seq" => "d"],
				
				["company" => "MS",	"division" => "LTV",			"seq" => "e"],
				["company" => "MS",	"division" => "Audio",			"seq" => "f"],
				["company" => "MS",	"division" => "MNT",			"seq" => "g"],
				["company" => "MS",	"division" => "DS",				"seq" => "h"],
				["company" => "MS",	"division" => "PC",				"seq" => "i"],
				["company" => "MS",	"division" => "MNT Signage",	"seq" => "j"],
				["company" => "MS",	"division" => "LED Signage",	"seq" => "k"],
				["company" => "MS",	"division" => "Commercial TV",	"seq" => "l"],
				
				["company" => "ES",	"division" => "RAC",		"seq" => "m"],
				["company" => "ES",	"division" => "SAC",		"seq" => "n"],
				["company" => "ES",	"division" => "Chiller",	"seq" => "o"],
			];
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

	public function get_most_likely(){
		//llamasys/api/lgepr/get_most_likely?key=lgepr
		
		$last = $this->gen_m->filter("lgepr_most_likely", false, ["subsidiary !=" => null], null, null, [["year", "desc"], ["month", "desc"]], 1, 0)[0];
		
		if ($this->input->get("key") === "lgepr") $res = $this->gen_m->filter("obs_most_likely", false, ["year" => $last->year, "month" => $last->month]);
		else $res = ["Key error"];
		
		if (!$res) $res = ["No this month ML data in database."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_closed_order(){
		//llamasys/api/lgepr/get_closed_order?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [];
			$w = ["closed_date >=" => date("Y-m-01"), "order_no NOT LIKE" => "2000%", 'bill_to_name NOT LIKE' => "LGE%"];
			//$w = ["closed_date >=" => date("2025-08-01"), "order_no NOT LIKE" => "2000%", 'bill_to_name NOT LIKE' => "LGE%"];
			$o = [["closed_date", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			$sd_rate = [];
			$sd_data = $this->gen_m->filter('lgepr_sales_deduction', false);
			foreach ($sd_data as $item_sd){
				if ($item_sd->country === 'PR') $customer_department = 'LGEPR';
				else $customer_department = 'Branch';
				$division = $item_sd->division;
				$sd_rate[$customer_department][$division][$item_sd->yyyy . "_" . $item_sd->mm] = $item_sd->sd_rate; // Para branch priorizar sd_rate de PY			
			}
			$data = $this->gen_m->filter("lgepr_closed_order", false, $w, null, null, $o);
			foreach ($data as $item){
				$cloned_item = clone $item;
				$date = $cloned_item->closed_date;
				$aux_date = explode("-", $date);
				$closed_period = (int)$aux_date[0] . "_" . (int)$aux_date[1];
				if (isset($sd_rate[$cloned_item->customer_department][$cloned_item->dash_division][$closed_period])){
					if($cloned_item->customer_name === 'OBS_Marketplace_3P' || $cloned_item->customer_name === 'One time_OBS' || $cloned_item->customer_name === 'One time_Employee') {
						$cloned_item->sales_deduction = 0;
						$cloned_item->sd_order_amount_usd = $cloned_item->order_amount_usd;
					} else {
						$cloned_item->sales_deduction = $sd_rate[$cloned_item->customer_department][$cloned_item->dash_division][$closed_period]*100;
						$cloned_item->sd_order_amount_usd = $cloned_item->order_amount_usd * (1 - $sd_rate[$cloned_item->customer_department][$cloned_item->dash_division][$closed_period]);
					}
				} else {
					$cloned_item->sales_deduction = "";
					$cloned_item->sd_order_amount_usd = '';
				}
				$res[] = clone $cloned_item;
			}
				
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
		
	}
	
	public function get_sales_order(){
			//llamasys/api/lgepr/get_sales_order?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [];
			$sd_rate = [];
			$sd_data = $this->gen_m->filter('lgepr_sales_deduction', false);
			foreach ($sd_data as $item_sd){
				if ($item_sd->country === 'PR') $customer_department = 'LGEPR';
				else $customer_department = 'Branch';
				$division = $item_sd->division;
				$sd_rate[$customer_department][$division][$item_sd->yyyy . "_" . $item_sd->mm] = $item_sd->sd_rate; // Para branch priorizar sd_rate de PY			
			}
			
			$o = [["create_date", "desc"], ["req_arrival_date_to", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			$data = $this->gen_m->filter("lgepr_sales_order", false, ['so_status NOT LIKE' => 'CANCELLED', 'bill_to_name NOT LIKE' => "LGE%", "order_no NOT LIKE" => "2000%"], null, null, $o);
			foreach ($data as $item){
				$cloned_item = clone $item;
				$date = $cloned_item->create_date;
				$aux_date = explode("-", $date);
				$sales_period = (int)$aux_date[0] . "_" . (int)$aux_date[1];
				if (isset($sd_rate[$cloned_item->customer_department][$cloned_item->dash_division][$sales_period])){
					if($cloned_item->customer_name === 'OBS_Marketplace_3P' || $cloned_item->customer_name === 'One time_OBS' || $cloned_item->customer_name === 'One time_Employee') {
						$cloned_item->sales_deduction = 0;
						$cloned_item->sd_order_amount_usd = $cloned_item->sales_amount_usd;
					} else{
						$cloned_item->sales_deduction = $sd_rate[$cloned_item->customer_department][$cloned_item->dash_division][$sales_period]*100;
						$cloned_item->sd_order_amount_usd = $cloned_item->sales_amount_usd * (1 - $sd_rate[$cloned_item->customer_department][$cloned_item->dash_division][$sales_period]);				
					}
				} else {
					$cloned_item->sales_deduction = "";
					$cloned_item->sd_order_amount_usd = $cloned_item->sales_amount_usd;
				}
				
				// new code about to change status
				if ($cloned_item->hold_flag === 'N' && $cloned_item->appointment_date !== NULL){
					$new_status2 = 'POR CITA';
				}elseif ($cloned_item->hold_flag === 'N' && $cloned_item->appointment_date === NULL){
					$new_status2 = 'SIN DISTRIBUCION';
				} elseif ($cloned_item->om_line_status === 'CON CITA' && $cloned_item->hold_flag === 'Y' && $cloned_item->appointment_date >= Date('Y-m-d')){
					$new_status2 = 'HOLD - CON CITA';
				} elseif (($cloned_item->hold_flag === 'Y' && $cloned_item->appointment_date !== NULL) || $cloned_item->om_line_status === 'POR LIBERAR DE CREDITOS'||($cloned_item->om_line_status === 'SIN LINEA DE CREDITOS' && 	$cloned_item->credit_hold === 'N')){
					$new_status2 = 'HOLD - POR CITA';
				} elseif ($cloned_item->om_line_status === 'SIN STOCK' || $cloned_item->back_order_hold === 'Y'){
					$new_status2 = 'BACK';
				} elseif ($cloned_item->om_line_status === 'SIN DISTRIBUCION' || $cloned_item->back_order_hold === 'N'){
					$new_status2 = 'BACK';
				} elseif ($cloned_item->shipment_date !== NULL || ($cloned_item->om_line_status === 'CON CITA' && $cloned_item->appointment_date >= Date('Y-m-d'))){
					$new_status2 = 'CON CITA';
				} else $new_status2 = NULL;
				
				//new status 3
				if ($new_status2 === 'CON CITA' || $cloned_item->order_category === 'RETURN' || $cloned_item->order_source === 'OMV_GSFS' || $cloned_item->order_source === 'OMD_LGOBS'){
					$new_status3 = 'OPEN+HOLD With Appointment';
				} elseif ($new_status2 === 'POR CITA' && $cloned_item->om_line_status === 'POR CONFIRMAR CITA'){
					$new_status3 = 'By Customer Confirm';
				} elseif ($new_status2 === 'POR CITA' && $cloned_item->om_line_status === 'POR SOLICITAR CITA'){
					$new_status3 = 'To Request Appointment';
				} elseif ($new_status2 === 'SIN DISTRIBUCION' || $new_status2 === 'BACK'){
					$new_status3 = 'Without Allocation';
				} elseif ($cloned_item->shipment_date !== NULL){
					$new_status3 = 'Pick';
				} else $new_status3 = 'To Review';
					
				$cloned_item->om_line_status = $new_status3;
				$res[] = clone $cloned_item;
			}
		} else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);		
	}
	
	public function get_sales_projection(){
		//llamasys/api/lgepr/get_sales_projection?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$from = date("Y-m-1");
			$res = [];
			
			$c_orders = $this->gen_m->filter("lgepr_closed_order", false, ["inventory_org" => "N4M", "order_date >=" => $from]);
			foreach($c_orders as $item){
				//print_r($item);
				$item->type = "Closed";
				$item->last_purchase_date = $item->order_date;
				$item->amount_usd = $item->order_amount_usd;
				$item->qty = $item->order_qty;
				
				$res[] = clone $item;
			}
			
			$s_orders = $this->gen_m->filter("lgepr_sales_order", false, ["inventory_org" => "N4M", "create_date >=" => $from]);
			foreach($s_orders as $item){
				$item->type = "Sales";
				$item->ref_date = $item->create_date;
				$item->amount_usd = $item->sales_amount_usd;
				$item->qty = $item->ordered_qty;
				
				$res[] = clone $item;
			}
		}else $res = ["Key error"];
		
		//foreach($res as $item){ print_r($item); echo "<br/><br/>"; }
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_most_likely2(){
		//llamasys/api/lgepr/get_most_likely2?key=lgepr
		
		$data_ml = $this->gen_m->filter("lgepr_most_likely", false);
		
		
		if ($this->input->get("key") === "lgepr") {
			
			foreach($data_ml as $item){
				// Clona el objeto para evitar modificar el original
				$cloned_item = clone $item;
				
				// Columnas a excluir
				$exclude_columns = ['updated'];

				 // Itera a través de las propiedades del objeto
				foreach ($cloned_item as $key => $value) {
					// Verifica si la columna está en la lista de exclusión
					if (in_array($key, $exclude_columns)) {
						// Elimina la columna del objeto clonado
						unset($cloned_item->$key);
					} else {
						// Verifica si el valor es numérico y es 0
						if (is_numeric($value) && $value == 0) {
							// Reemplaza 0 con una cadena vacía
							$cloned_item->$key = "";
						}
					}
					$cloned_item->month = $cloned_item->yyyy . "-" . $cloned_item->mm;
				}
				//echo '<pre>'; print_r($item);
				$res[] = clone $cloned_item;
				//$res['month'] = $cloned_item->yyyy . "-" . $cloned_item->mm;
			}
		}
		
		
		
		// $res = $this->gen_m->filter("obs_most_likely", false, ["year" => $last->year, "month" => $last->month]);
		else $res = ["Key error"];
		
		//if (!$res) $res = ["No this month ML data in database."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_sales_deduction(){
			//llamasys/api/lgepr/get_sales_deduction?key=lgepr		
			
		
		if ($this->input->get("key") === "lgepr") {
			
			$data_sd = $this->gen_m->filter("lgepr_sales_deduction", false);
			
			foreach($data_sd as $item){
				// Clona el objeto para evitar modificar el original
				$cloned_item = clone $item;
				
				// Columnas a excluir
				$exclude_columns = ['sales_deduction_id', 'updated'];

				// Itera a través de las propiedades del objeto
				foreach ($cloned_item as $key => $value) {
					// Verifica si la columna está en la lista de exclusión
					if (in_array($key, $exclude_columns)) {
						// Elimina la columna del objeto clonado
						unset($cloned_item->$key);
					} else {
						// Verifica si el valor es numérico y es 0
						if (is_numeric($value) && $value == 0 && $key !== 'sd_rate') {
							// Reemplaza 0 con una cadena vacía
							$cloned_item->$key = "";
							//$cloned_item->sd_rate = 0;
						}
						elseif ($value === NULL){
							$cloned_item->$key = "";
						}
					}
					$cloned_item->month = $cloned_item->yyyy . "-" . $cloned_item->mm;
				}
				//echo '<pre>'; print_r($item);
				$res[] = clone $cloned_item;
				//$res['month'] = $cloned_item->yyyy . "-" . $cloned_item->mm;
			}
		}
		
		
		
		// $res = $this->gen_m->filter("obs_most_likely", false, ["year" => $last->year, "month" => $last->month]);
		else $res = ["Key error"];
		
		//if (!$res) $res = ["No this month ML data in database."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_NGSI(){
		//llamasys/api/lgepr/get_NGSI?key=lgepr
		
		$data_ngsi = $this->gen_m->filter("ngsi_inventory", false);
		
		if ($this->input->get("key") === "lgepr") {
			
			foreach($data_ngsi as $item){
				// Clona el objeto para evitar modificar el original
				$cloned_item = clone $item;
				
				$exclude_columns = ['remark', 'updated'];

				foreach ($cloned_item as $key => $value) {
					if (in_array($key, $exclude_columns)) {
						unset($cloned_item->$key);
					} else {
						if (is_numeric($value) && $value == 0) {
							$cloned_item->$key = "";
						}
					}
				}
				//echo '<pre>'; print_r($item);
				$res[] = clone $cloned_item;
			}
		}
		
		
		
		// $res = $this->gen_m->filter("obs_most_likely", false, ["year" => $last->year, "month" => $last->month]);
		else $res = ["Key error"];
		
		//if (!$res) $res = ["No this month ML data in database."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_cbm_inventory(){
		// APM: N4M, N4E
		// KLO: N4S
		if ($this->input->get("key") === "lgepr"){
			
			$klo_wh = ['N4S'];
			$apm_wh = ['N4M', 'N4E'];
			$start_month = Date('Y-m-01');
			$date_object_start = new DateTime($start_month);
			$date_object_start->modify('first day of -3 months');
			$start_pre_month = $date_object_start->format('Y-m-d');
			$today = Date('Y-m-d'); //current date

			$dayOfWeek = date('N', strtotime($today));

			if ($dayOfWeek == 1) {
				$yesterday = date('Y-m-d', strtotime($today . ' -2 days'));
			} else {
				$yesterday = date('Y-m-d', strtotime($today . ' -1 day'));
			}

			$from_current_inventory = '';
			$stock_cbm = [];
			$stock = $this->gen_m->filter('v_cbm_history_lastload', false, ['on_hand_cbm !=' => 0, 'updated >=' => $start_pre_month . ' 00:00:00'], null, null, [['updated', 'asc']]);
			
			foreach($stock as $item_stock){
				$type = 'Current Inventory';
				$dates = explode("-", $item_stock->updated);
				$year = $dates[0];
				$month = $dates[1];
				$day_hour = $dates[2];
				$time_aux = explode(" ", $day_hour);
				$day = $time_aux[0];
				
				$key = $type . "-". $item_stock->updated . "-" . $item_stock->org . "-" . $item_stock->model . "-" . $item_stock->dash_company . "-" . $item_stock->dash_division;

				if(!isset($data_cbm[$key])){
					$data_cbm[$key] = [
										"type" 				=> $type,
										"date"				=> explode(" ", $item_stock->updated)[0],
										"year" 				=> $year,
										"month" 			=> $month,
										"day" 				=> $day,
										"dash_company" 		=> $item_stock->dash_company,
										"dash_division" 	=> $item_stock->dash_division,
										"model" 			=> $item_stock->model,
										"warehouse"			=> in_array($item_stock->org, $klo_wh) ? 'KLO' : (in_array($item_stock->org, $apm_wh) ? 'APM' : ''),
										"org" 				=> $item_stock->org,
										"qty"				=> 0,
										"container"			=> null,
										"cbm" 				=> 0,
										"updated" 			=> $item_stock->updated
										];
				}
				$data_cbm[$key]["qty"] += $item_stock->on_hand;
				$data_cbm[$key]["cbm"] += $item_stock->on_hand_cbm;				
			}
			// Calculate ML
			if (date('m') == 1 || date('m') === '01'){
				$current_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => 1]);
				$past_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y')-1, 'mm' => 12]);
				$future_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => 2]);
			} elseif (date('m') == 12 || date('m') === '12'){
				$current_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => date('m')]);
				$past_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => date('m')-1]);
				$future_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y')+1, 'mm' => 1]);
			} else {
				$current_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => date('m')]);
				$past_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => date('m')-1]);
				$future_ml = $this->gen_m->filter_select('lgepr_most_likely', false, ['ml_actual'], ['yyyy' => date('Y'), 'mm' => date('m')+1]);
			}

			$current_ml_sum = 0;
			foreach ($current_ml as $item) $current_ml_sum += $item->ml_actual;
			$past_ml_sum = 0;
			foreach ($past_ml as $item) $past_ml_sum += $item->ml_actual;
			$future_ml_sum = 0;
			if (empty($future_ml) || $future_ml === '') {
				$future_ml_sum = $current_ml_sum;
			} else {
				foreach ($future_ml as $item) $future_ml_sum += $item->ml_actual;
			}
			
			$start_month = Date('Y-m-01');
			$first_week = date('Y-m-22');

			$dayOfMonth = date('d', strtotime($today));
			$lastMonthMaxDays = date('t', strtotime('first day of last month'));

			if ($dayOfMonth > $lastMonthMaxDays) {
				$todayLastMonth = date('Y-m-d', strtotime('last day of last month'));
			} else {
				$todayLastMonth = date('Y-m-d', strtotime('-1 month', strtotime($today)));
			}

			$dayOfMonthYesterday = date('d', strtotime($yesterday));

			if ($dayOfMonthYesterday > $lastMonthMaxDays) {
				$yesterdayLastMonth = date('Y-m-d', strtotime('last day of last month'));
			} else {
				$yesterdayLastMonth = date('Y-m-d', strtotime('-1 month', strtotime($yesterday)));
			}
			$firstDayLastMonth = date('Y-m-d', strtotime('first day of last month'));
			
			// Sales
			$cbm_total = 0;
			$cbm_total_f = 0;		
			$closed = $this->gen_m->filter_select('lgepr_closed_order', false, ['dash_company', 'dash_division', 'model', 'inventory_org', 'order_qty', 'closed_date', 'item_cbm', 'updated_at'],['item_cbm !=' => 0, 'closed_date >=' => $firstDayLastMonth, 'closed_date <=' => $first_week]);
			
			foreach ($closed as $item) {
				$type = 'Sales';
				$key = $item->closed_date . "_" . $item->model . "_" . $item->dash_company . "_" . $item->dash_division . "_" . $item->inventory_org;
				
				if ($item->closed_date < $start_month){
					$cbm_total += $item->item_cbm;
				} else $cbm_total_f += $item->item_cbm;
								
				$dates = explode("-", $item->closed_date);
				$year = $dates[0];
				$month = $dates[1];
				$day = $dates[2];
				
				if ($item->closed_date === $yesterdayLastMonth || $item->closed_date === $todayLastMonth){
					if (!isset($data_cbm[$key])) {
						$data_cbm[$key] = [
									"type" 				=> $type,
									"date"				=> date('Y-m-d', strtotime($item->closed_date . ' +1 month')),
									"year" 				=> $year,
									"month" 			=> ($month == 12 || $month === '12') ? 1 : $month + 1,
									"day" 				=> $day,
									"dash_company"		=> $item->dash_company,
									"dash_division" 	=> $item->dash_division,
									"model" 			=> $item->model,
									"warehouse"			=> in_array($item->inventory_org, $klo_wh) ? 'KLO' : (in_array($item->inventory_org, $apm_wh) ? 'APM' : ''),
									"org" 				=> $item->inventory_org,
									"qty"				=> 0,
									"container"			=> '',
									"cbm" 				=> 0,
									"updated" 			=> $item->updated_at
						];
					}
				} elseif ($item->closed_date > $todayLastMonth && $item->closed_date < $start_month){
					if (!isset($data_cbm[$key])) {
						$data_cbm[$key] = [
									"type" 				=> $type,
									"date"				=> date('Y-m-d', strtotime($item->closed_date . ' +1 month')),
									"year" 				=> ($month == 12 || $month === '12') ? $year + 1 : $year,
									"month" 			=> ($month == 12 || $month === '12') ? 1 : $month + 1,
									"day" 				=> $day,
									"dash_company"		=> $item->dash_company,
									"dash_division" 	=> $item->dash_division,
									"model" 			=> $item->model,
									"warehouse"			=> in_array($item->inventory_org, $klo_wh) ? 'KLO' : (in_array($item->inventory_org, $apm_wh) ? 'APM' : ''),
									"org" 				=> $item->inventory_org,
									"qty"				=> 0,
									"container"			=> '',
									"cbm" 				=> 0,
									"updated" 			=> $item->updated_at
						];
					}
					$data_cbm[$key]['cbm'] += $item->item_cbm;
					$data_cbm[$key]['qty'] += $item->order_qty;
				}elseif ($item->closed_date >= $start_month){
					if (!isset($data_cbm[$key])) {
						$data_cbm[$key] = [
									"type" 				=> $type,
									"date"				=> date('Y-m-d', strtotime($item->closed_date . ' +1 month')),
									"year" 				=> ($month == 12 || $month === '12') ? $year + 1 : $year,
									"month" 			=> ($month == 12 || $month === '12') ? 1 : $month + 1,
									"day" 				=> $day,
									"dash_company"		=> $item->dash_company,
									"dash_division" 	=> $item->dash_division,
									"model" 			=> $item->model,
									"warehouse"			=> in_array($item->inventory_org, $klo_wh) ? 'KLO' : (in_array($item->inventory_org, $apm_wh) ? 'APM' : ''),
									"org" 				=> $item->inventory_org,
									"qty"				=> 0,
									"container"			=> '',
									"cbm" 				=> 0,
									"updated" 			=> $item->updated_at
						];
					}
					$data_cbm[$key]['cbm'] += $item->item_cbm;
					$data_cbm[$key]['qty'] += $item->order_qty;
				} else continue;
			}

			$var_ml = ($cbm_total/$past_ml_sum) * $current_ml_sum; // ML proportional with current dates and past dates
			$var_fml = ($cbm_total_f/$current_ml_sum) * $future_ml_sum; // ML proportional with future dates and current dates

			foreach ($data_cbm as $key => &$item) {
				if ($item['date'] <= date('Y-m-t')){ // Comparative between changed dates
					if ($item['type'] === 'Sales') $item['cbm'] = number_format((($item['cbm'] / $cbm_total) * $var_ml) * -1, 4); // Current date
				} else {
					if ($item['type'] === 'Sales') $item['cbm'] = number_format((($item['cbm'] / $cbm_total_f) * $var_fml) * -1, 4); // Future week
				}
			}

			// Real Sales
			$sales = $this->gen_m->filter('lgepr_sales_order', false, ['cbm !=' => 0, 'appointment_date >=' => $yesterday, 'appointment_date !=' => null], null, null, [['appointment_date', 'asc']]);
			
			foreach($sales as $item_sales){
				$type = 'Real Sales';
				$dates = explode("-", $item_sales->appointment_date);
				$year = $dates[0];
				$month = $dates[1];
				$day = $dates[2];
				
				$key = $type . "-". $item_sales->appointment_date . "-" . $item_sales->inventory_org . "-" . $item_sales->model . "-" . $item_sales->dash_company . "-" . $item_sales->dash_division;
					
				if((new DateTime($item_sales->appointment_date))->format('Y-m-d') === $yesterday || (new DateTime($item_sales->appointment_date))->format('Y-m-d') === $today){
					if(!isset($data_cbm[$key])){
						$data_cbm[$key] = [
											"type" 				=> $type,
											"date"				=> $item_sales->appointment_date,
											"year" 				=> $year,
											"month" 			=> $month,
											"day" 				=> $day,
											"dash_company" 		=> $item_sales->dash_company,
											"dash_division" 	=> $item_sales->dash_division,
											"model" 			=> $item_sales->model,
											"warehouse"			=> in_array($item_sales->inventory_org, $klo_wh) ? 'KLO' : (in_array($item_sales->inventory_org, $apm_wh) ? 'APM' : ''),
											"org" 				=> $item_sales->inventory_org,
											"qty"				=> 0,
											"container"			=> null,
											"cbm" 				=> 0,
											"updated" 			=> $item_sales->updated_at
											];
					}
				}
				elseif((new DateTime($item_sales->appointment_date))->format('Y-m-d') > $today){
					if(!isset($data_cbm[$key])){
						$data_cbm[$key] = [
											"type" 				=> $type,
											"date"				=> $item_sales->appointment_date,
											"year" 				=> $year,
											"month" 			=> $month,
											"day" 				=> $day,
											"dash_company" 		=> $item_sales->dash_company,
											"dash_division" 	=> $item_sales->dash_division, 
											"model" 			=> $item_sales->model,
											"warehouse"			=> in_array($item_sales->inventory_org, $klo_wh) ? 'KLO' : (in_array($item_sales->inventory_org, $apm_wh) ? 'APM' : ''), 
											"org" 				=> $item_sales->inventory_org,
											"qty"				=> 0,
											"container"			=> null,
											"cbm" 				=> 0,
											"updated" 			=> $item_sales->updated_at
											];
					}
					if(!isset($data_cbm[$key]["qty"])) $data_cbm[$key]["qty"] = 0;
					if(!isset($data_cbm[$key]["cbm"])) $data_cbm[$key]["cbm"] = 0;
					$data_cbm[$key]["qty"] += $item_sales->ordered_qty;
					$data_cbm[$key]["cbm"] += $item_sales->cbm * -1;		
				}		
			}
			
			// Container
			$container_cbm = [];
			$container = $this->gen_m->filter('lgepr_container', false, ['cbm !=' => 0, 'eta >=' => $yesterday, 'eta <=' => date('Y-m-t')], null, null, [['eta', 'asc']]);
			foreach($container as $item_container){
				$type = 'Arrival';
				$dates = explode("-", $item_container->eta);
				$year = $dates[0];
				$month = $dates[1];
				$day = $dates[2];
				
				$key = $type . "-". $item_container->eta . "-" . $item_container->organization . "-" . $item_container->model . "-" . $item_container->company . "-" . $item_container->division;

				if((new DateTime($item_container->eta))->format('Y-m-d') === $yesterday || (new DateTime($item_container->eta))->format('Y-m-d') === $today){
					if(!isset($data_cbm[$key])){
						$data_cbm[$key] = [
							"type" 				=> $type, 
							"date"				=> $item_container->eta,
							"year" 				=> $year, 
							"month" 			=> $month, 
							"day" 				=> $day, 
							"dash_company"		=> $item_container->company,
							"dash_division" 	=> $item_container->division,
							"model" 			=> $item_container->model,
							"warehouse"			=> in_array($item_container->organization, $klo_wh) ? 'KLO' : (in_array($item_container->organization, $apm_wh) ? 'APM' : ''), 
							"org" 				=> $item_container->organization,
							"qty"				=> 0,
							"container"			=> $item_container->container,
							"cbm" 				=> 0,
							"updated" 			=> $item_container->updated_at
							];
					}
				}
				elseif((new DateTime($item_container->eta))->format('Y-m-d') > $today){
					if(!isset($data_cbm[$key])){
						$data_cbm[$key] = [
								"type" 				=> $type,
								"date"				=> $item_container->eta,
								"year" 				=> $year,
								"month" 			=> $month,
								"day" 				=> $day,
								"dash_company"		=> $item_container->company,
								"dash_division" 	=> $item_container->division,
								"model" 			=> $item_container->model,
								"warehouse"			=> in_array($item_container->organization, $klo_wh) ? 'KLO' : (in_array($item_container->organization, $apm_wh) ? 'APM' : ''),
								"org" 				=> $item_container->organization,
								"qty"				=> 0,
								"container"			=> $item_container->container,
								"cbm" 				=> 0,
								"updated" 			=> $item_container->updated_at
								];
					}
					$data_cbm[$key]["qty"] += $item_container->qty;
					$data_cbm[$key]["cbm"] += $item_container->cbm;
				}
			}
				
			$res = [];
			foreach ($data_cbm as $item){
				$res[] = $item;
			}
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
		}
	
	public function get_monthly_closed_order(){
		//llamasys/api/lgepr/get_monthly_closed_order?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				"LGEPR_HS_REF" 				=> ["seq" => "1a", "department" => "LGEPR", "company" => "HS", "division" => "REF",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_HS_Cooking" 			=> ["seq" => "1b", "department" => "LGEPR", "company" => "HS", "division" => "Cooking",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_HS_Dishwasher" 		=> ["seq" => "1c", "department" => "LGEPR", "company" => "HS", "division" => "Dishwasher",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_HS_W/M" 				=> ["seq" => "1d", "department" => "LGEPR", "company" => "HS", "division" => "W/M",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"LGEPR_MS_LTV" 				=> ["seq" => "1e", "department" => "LGEPR", "company" => "MS", "division" => "LTV",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_Audio" 			=> ["seq" => "1f", "department" => "LGEPR", "company" => "MS", "division" => "Audio",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_MNT" 				=> ["seq" => "1g", "department" => "LGEPR", "company" => "MS", "division" => "MNT",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_DS" 				=> ["seq" => "1h", "department" => "LGEPR", "company" => "MS", "division" => "DS",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_PC" 				=> ["seq" => "1i", "department" => "LGEPR", "company" => "MS", "division" => "PC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_MNT Signage" 		=> ["seq" => "1j", "department" => "LGEPR", "company" => "MS", "division" => "MNT Signage",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_MS_Commercial TV" 	=> ["seq" => "1k", "department" => "LGEPR", "company" => "MS", "division" => "Commercial TV",	"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"LGEPR_ES_RAC" 				=> ["seq" => "1l", "department" => "LGEPR", "company" => "ES", "division" => "RAC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_ES_SAC" 				=> ["seq" => "1m", "department" => "LGEPR", "company" => "ES", "division" => "SAC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"LGEPR_ES_Chiller" 			=> ["seq" => "1n", "department" => "LGEPR", "company" => "ES", "division" => "Chiller",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"Branch_HS_REF" 			=> ["seq" => "2a", "department" => "Branch", "company" => "HS", "division" => "REF",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_HS_Cooking" 		=> ["seq" => "2b", "department" => "Branch", "company" => "HS", "division" => "Cooking",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_HS_Dishwasher" 		=> ["seq" => "2c", "department" => "Branch", "company" => "HS", "division" => "Dishwasher",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_HS_W/M" 			=> ["seq" => "2d", "department" => "Branch", "company" => "HS", "division" => "W/M",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"Branch_MS_LTV" 			=> ["seq" => "2e", "department" => "Branch", "company" => "MS", "division" => "LTV",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_Audio" 			=> ["seq" => "2f", "department" => "Branch", "company" => "MS", "division" => "Audio",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_MNT" 			=> ["seq" => "2g", "department" => "Branch", "company" => "MS", "division" => "MNT",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_DS" 				=> ["seq" => "2h", "department" => "Branch", "company" => "MS", "division" => "DS",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_PC" 				=> ["seq" => "2i", "department" => "Branch", "company" => "MS", "division" => "PC",				"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_MNT Signage" 	=> ["seq" => "2j", "department" => "Branch", "company" => "MS", "division" => "MNT Signage",	"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_MS_Commercial TV" 	=> ["seq" => "2k", "department" => "Branch", "company" => "MS", "division" => "Commercial TV",	"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				
				"Branch_ES_RAC" 			=> ["seq" => "2l", "department" => "Branch", "company" => "ES", "division" => "RAC",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_ES_SAC" 			=> ["seq" => "2m", "department" => "Branch", "company" => "ES", "division" => "SAC",			"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
				"Branch_ES_Chiller" 		=> ["seq" => "2n", "department" => "Branch", "company" => "ES", "division" => "Chiller",		"Total" => 0, "Sales" => 0, "Return" => 0, "Reinvoice" => 0],
			];
			
			//$w = ["month" => date("2024-11")];
			$w = ["month" => date("Y-m")];
			
			$monthly = $this->gen_m->filter("v_lgepr_monthly_closed_order", false, $w);
			foreach($monthly as $item){
				$res[$item->customer_department."_".$item->dash_company."_".$item->dash_division][$item->category] += round($item->total_order_amount_usd, 2);
			}
		}else $res = ["Key error"];
		
		foreach($res as $key => $item){
			$res[$key]["Total"] = $res[$key]["Sales"] + $res[$key]["Return"];
			//print_r($item); echo "<br/>";
		}
		
		//foreach($res as $key => $item){print_r($item); echo "<br/>";}
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function test(){
		//show all tables of DB
		//$tables = $this->gen_e->get_tables(); echo "<br/><br/><br/>";
		
		//$data = $this->gen_e->filter_array("BI2_TARGET_V", false, [], null, null, [], 1000, 0);
		
		$w = [
			//"YYYY" => 2025,
			//"Subsidiary !=" => "Branch",
		];
		
		$w_in = [
			//["field" => "DATA_TYPE", "values" => ["Sales", "Sales Deduction", "SD Rate"]],
			//["field" => "DATA_TYPE", "values" => ["SD Rate"]],
		];
		
		$o = [
			["YYYY", "desc"],
			["Subsidiary", "asc"],
			["Country", "asc"],
			["DIVISION", "asc"],
			["DATA_TYPE", "asc"],
		];
		
		$data = $this->gen_e->filter_array("M_PLAN_TTL", false, $w, null, $w_in, $o, 1000, 0);
		
		$arr = [
			"LGEPR" => [
				"Peru" => [],
			],
			"Branch" => [
				"Paraguay" => [],
				"Uruguay" => [],
				"Bolivia" => [],
			],
		];
		
		echo "<table>";
		echo "<tr>";
		foreach($data[0] as $key => $item) echo "<td>".$key."</td>";
		echo "</tr>";
		
		$stru = $this->gen_m->structure("lgepr_most_likely");
		unset($stru->most_likely_id);
		
		foreach($data as $item){
			echo "<tr>";
			foreach($item as $key => $d) echo "<td>".$d."</td>";
			echo "</tr>";
			
			if (!array_key_exists($item["DIVISION"], $arr[$item["Subsidiary"]][$item["Country"]])) $arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]] = [];
			if (!array_key_exists($item["YYYY"], $arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]])){
				$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]] = [];
				for($i = 1; $i <= 12; $i++){
					$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i] = clone $stru;
				
					$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i]->country = $item["Country"];
					$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i]->subsidiary = $item["Subsidiary"];
					//$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i]->company = ;
					$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i]->division = $item["DIVISION"];
					$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i]->year = $item["YYYY"];
					$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$i]->month = $i;	
				}
			}
			
			if ($item["DATA_TYPE"] === "BP"){
				
				
				$arr[$item["Subsidiary"]][$item["Country"]][$item["DIVISION"]][$item["YYYY"]][$m]->bp = $val;
			}
		}
		
		
		echo "</table>";
		
		echo "<br/><br/>";
		
		print_r($arr);
		
		
		/*
		foreach($data as $item){
			//foreach($item as $key => $d) echo $key." ============> ".$d."<br/>";
			print_R($item);
			echo "<br/><br/>";
		}
		*/
		
		//T_CLOSED_ORDER
		//T_OPEN_ORDER

	}

	public function get_tracking_dispatch(){
		//llamasys/api/lgepr/get_tracking_dispatch?key=lgepr		
		// APM: N4M, N4E
		// KLO: N4S	
		if ($this->input->get("key") !== "lgepr") {
			http_response_code(401);
			echo json_encode(["error" => "Key error"]);
			return;
		}
		
		$from = date('Y-m-d', strtotime('-5 days'));
		
		$trackingData = $this->gen_m->filter("scm_tracking_dispatch", false, ['date >=' => $from]);
		
		
		if (empty($trackingData)) {
			header('Content-Type: application/json');
			echo json_encode([]);
			return;
		}

		$pickOrders = array_unique(array_column($trackingData, 'pick_order'));		
		$models = array_unique(array_column($trackingData, 'model'));
		
		$w_in_clause_shipping = [['field' => 'pick_no', 'values' => $pickOrders]];		
		$shippingData = @$this->gen_m->filter('scm_shipping_status', false, null, null, $w_in_clause_shipping);
		
		$order_nos = array_column($shippingData, 'order_no');
		$line_nos = array_column($shippingData, 'line_no');
		
		if (!empty($order_nos) && !empty($line_nos)){ // Validate data
			$w_in_clause_sales = [['field' => 'order_no', 'values' => $order_nos], ['field' => 'line_no', 'values' => $line_nos]];
			$salesData = $this->gen_m->filter('lgepr_sales_order', false, null, null, $w_in_clause_sales);
		
			$w_in_clause_closed = [['field' => 'model', 'values' => $models], ['field' => 'order_no', 'values' => $order_nos], ['field' => 'line_no', 'values' => $line_nos]];
			$closedData = $this->gen_m->filter('lgepr_closed_order', false, null, null, $w_in_clause_closed);
		} else {
			$salesData = [];
			$closedData = [];
		}
		
		$sales =  $this->gen_m->filter_select('lgepr_sales_order', false, ['model', 'dash_company', 'dash_division']);
		$sales_dash = [];
		foreach($sales as $item){
			$sales_dash[$item->model] = $item;
		}
		
		$closed =  $this->gen_m->filter_select('lgepr_closed_order', false, ['model', 'dash_company', 'dash_division']);
		$closed_dash = [];
		foreach($closed as $item){
			$closed_dash[$item->model] = $item;
		}

		$shippingMap = [];
		foreach ($shippingData as $shipItem) {
			$shippingMap[$shipItem->pick_no . "_" . $shipItem->model . "_" . $shipItem->order_qty] = $shipItem;
		}

		$salesMap = [];
		foreach ($salesData as $salesItem) {
			$salesMap[$salesItem->order_no . '-' . $salesItem->line_no] = $salesItem;
		}

		$closedMap = [];
		foreach ($closedData as $closedItem) {
			$closedMap[$closedItem->order_no . '-' . $closedItem->line_no] = $closedItem;
		}

		$closedMapByModel = [];
		foreach ($closedData as $closedItem) {
			$closedMapByModel[$closedItem->model] = $closedItem;
		}

		$res = [];
		foreach ($trackingData as $item) {
			$cloned_item = clone $item;
			
			$shippingItem = $shippingMap[$cloned_item->pick_order . "_" . $cloned_item->model . "_" . $cloned_item->qty] ?? null;

			if ($shippingItem) {
				$key = $shippingItem->order_no . '-' . $shippingItem->line_no;
				
				$salesItem = $salesMap[$key] ?? null;
				if ($salesItem) {
					$cloned_item->dash_company = $salesItem->dash_company;
					$cloned_item->dash_division = $salesItem->dash_division;
					$cloned_item->order_no = $shippingItem->order_no;
					$cloned_item->line_no = $shippingItem->line_no;
					$cloned_item->dash_amount_usd = $salesItem->sales_amount_usd;
				} else {
					$closedItem = $closedMap[$key] ?? null;
					if ($closedItem) {
						$cloned_item->dash_company = $closedItem->dash_company ?? '';
						$cloned_item->dash_division = $closedItem->dash_division ?? '';
						$cloned_item->order_no = $shippingItem->order_no;
						$cloned_item->line_no = $shippingItem->line_no;
						$cloned_item->dash_amount_usd = $closedItem->order_amount_usd ?? '';
					} else {
						$cloned_item->dash_company = '';
						$cloned_item->dash_division = '';
						$cloned_item->order_no = $shippingItem->order_no;
						$cloned_item->line_no = $shippingItem->line_no;
						$cloned_item->dash_amount_usd = '';
					}
				}
			} elseif (isset($sales_dash[$cloned_item->model])) {
				$cloned_item->dash_company = $sales_dash[$cloned_item->model]->dash_company ?? '';
				$cloned_item->dash_division = $sales_dash[$cloned_item->model]->dash_division ?? '';
				$cloned_item->order_no = '';
				$cloned_item->line_no = '';
				$cloned_item->dash_amount_usd = '';
			} elseif (isset($closed_dash[$cloned_item->model])) {
				$cloned_item->dash_company = $closed_dash[$cloned_item->model]->dash_company ?? '';
				$cloned_item->dash_division = $closed_dash[$cloned_item->model]->dash_division ?? '';
				$cloned_item->order_no = '';
				$cloned_item->line_no = '';
				$cloned_item->dash_amount_usd = '';
			} else {
				 $cloned_item->dash_company = '';
				 $cloned_item->dash_division = '';
				 $cloned_item->order_no = '';
				 $cloned_item->line_no = '';
				 $cloned_item->dash_amount_usd = '';			
			}
			$res[] = $cloned_item;
		}

		header('Content-Type: application/json');
		echo json_encode($res);
	}

	public function get_ap_report() {
		//llamasys/api/lgepr/get_ap_report?key=lgepr
		if ($this->input->get("key") === "lgepr") {
			$res = [];
			$data = $this->gen_m->filter('ap_report', false, null, null, null, [['week', 'asc']]);
			
			$week_number = date('W'); // Current week
			$current_day_of_week = date('N');
			
			foreach ($data as $item){
				$cloned_item = clone $item;
				
				if ($cloned_item->week < $week_number) $cloned_item->status = 'Paid';
				else $cloned_item->status = 'Pending Payment';
				
				unset($cloned_item->id);
				unset($cloned_item->key_ap);
				unset($cloned_item->last_updated);
				$res[] = clone $cloned_item;
			}
		} else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_ar_detail() {
		//llamasys/api/lgepr/get_ar_detail?key=lgepr
		if ($this->input->get("key") === "lgepr") {
			$res = []; $key_list = [];
			$data = $this->gen_m->filter('ar_detail', false, null, null, null, [['last_updated', 'desc']]);
			$exchange = [];
			$exchange_rate = $this->gen_m->filter('exchange_rate', false, ['currency' => 'PEN']);
			foreach ($exchange_rate as $item) $exchange[$item->date_apply] = $item;

			foreach ($data as $item){
				$cloned_item = clone $item;
				
				$current_date = new DateTime();
				$current_date->modify('-1 day');
				$before_date = $current_date->format('Y-m-d');
				
				if (isset($exchange[$before_date])) {
					if ($cloned_item->currency === 'PEN') {
						$cloned_item->original_amount_entered_curr = number_format($cloned_item->original_amount_entered_curr / $exchange[$before_date]->sell, 2);
						$cloned_item->offset = number_format($cloned_item->offset / $exchange[$before_date]->sell, 2);
						$cloned_item->cash_receipt = number_format($cloned_item->cash_receipt / $exchange[$before_date]->sell, 2);
						$cloned_item->on_account = number_format($cloned_item->on_account / $exchange[$before_date]->sell, 2);
						$cloned_item->note_to_cash = number_format($cloned_item->note_to_cash / $exchange[$before_date]->sell, 2);
						$cloned_item->cash_discount = number_format($cloned_item->cash_discount / $exchange[$before_date]->sell, 2);
						$cloned_item->other_expense = number_format($cloned_item->other_expense / $exchange[$before_date]->sell, 2);
						$cloned_item->note = number_format($cloned_item->note / $exchange[$before_date]->sell, 2);
						$cloned_item->note_balance = number_format($cloned_item->note_balance / $exchange[$before_date]->sell, 2);
						$cloned_item->balance_total = number_format($cloned_item->balance_total / $exchange[$before_date]->sell, 2);
						$cloned_item->currency = 'USD';
					}
				} else {
					if ($cloned_item->currency === 'PEN') {
						$cloned_item->original_amount_entered_curr = 'Exchange Rate no updated (Report PI)';
						$cloned_item->offset = 'Exchange Rate no updated (Report PI)';
						$cloned_item->cash_receipt = 'Exchange Rate no updated (Report PI)';
						$cloned_item->on_account = 'Exchange Rate no updated (Report PI)';
						$cloned_item->note_to_cash = 'Exchange Rate no updated (Report PI)';
						$cloned_item->cash_discount = 'Exchange Rate no updated (Report PI)';
						$cloned_item->other_expense = 'Exchange Rate no updated (Report PI)';
						$cloned_item->note ='Exchange Rate no updated (Report PI)';
						$cloned_item->note_balance = 'Exchange Rate no updated (Report PI)';
						$cloned_item->balance_total = 'Exchange Rate no updated (Report PI)';
					}
				}
				
				unset($cloned_item->id);
				unset($cloned_item->last_updated);
				$res[] = clone $cloned_item;
			}
		} else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

	public function get_ar_aging() {
		//llamasys/api/lgepr/get_ar_detail?key=lgepr
		if ($this->input->get("key") === "lgepr") {
			$res = [];
			$data = $this->gen_m->filter('ar_aging', false, null, null, null, [['last_updated', 'desc']]);

			foreach ($data as $item){
				$cloned_item = clone $item;
				
				unset($cloned_item->id);
				unset($cloned_item->key_aging);
				unset($cloned_item->last_updated);
				$res[] = clone $cloned_item;
			}
		} else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

	public function get_ar_cash_report() { // gl date
		//llamasys/api/lgepr/get_ar_detail?key=lgepr
		if ($this->input->get("key") === "lgepr") {
			$res = [];
			$data = $this->gen_m->filter('ar_cash_report', false, null, null, null, [['last_updated', 'desc']]);
			
			$exchange = [];
			$exchange_rate = $this->gen_m->filter('exchange_rate', false, ['currency' => 'PEN']);
			foreach ($exchange_rate as $item) $exchange[$item->date_apply] = $item;
			
			foreach ($data as $item){
				$cloned_item = clone $item;
				
				if (isset($exchange[$cloned_item->gl_date])) {
					if ($cloned_item->deposit_currency === 'PEN') {
						$cloned_item->alloc_amount = number_format($cloned_item->alloc_amount / $exchange[$cloned_item->gl_date]->sell, 2);
						$cloned_item->deposit_amount = number_format($cloned_item->deposit_amount / $exchange[$cloned_item->gl_date]->sell, 2);
						$cloned_item->deposit_currency = 'USD';
					}
				} 
				
				unset($cloned_item->id);
				unset($cloned_item->key_cash_report);
				unset($cloned_item->last_updated);
				$res[] = clone $cloned_item;
			}
		} else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
}
