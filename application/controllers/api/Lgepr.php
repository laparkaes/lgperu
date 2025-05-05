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
			//$w = ["closed_date >=" => date("2024-12-01")];
			$w = ["closed_date >=" => date("Y-01-01")];
			$o = [["closed_date", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			
			$res = $this->gen_m->filter("lgepr_closed_order", false, $w, null, null, $o);
			foreach($res as $item){
				$item->month = date("Y-m", strtotime($item->closed_date));
			}
		}else $res = ["Key error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function get_sales_order(){
		//llamasys/api/lgepr/get_closed_order?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$o = [["create_date", "desc"], ["req_arrival_date_to", "desc"], ["order_no", "desc"], ["line_no", "desc"]];
			
			$res = $this->gen_m->filter("lgepr_sales_order", false, null, null, null, $o);
			foreach($res as $item){
				//set last day of month if request arrival date is past
				if (strtotime($item->req_arrival_date_to) < strtotime(date("Y-m-01"))) $item->req_arrival_date_to = date("Y-m-t");
				
				if ($item->shipment_date) $item->month = date("Y-m", strtotime($item->shipment_date));
				elseif ($item->appointment_date) $item->month = date("Y-m", strtotime($item->appointment_date));
				elseif ($item->req_arrival_date_to) $item->month = date("Y-m", strtotime($item->req_arrival_date_to));
				elseif ($item->booked_date) $item->month = date("Y-m", strtotime($item->booked_date));
				else $item->month = date("Y-m", strtotime($item->create_date));
			}
		}else $res = ["Key error"];
		
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
	
	public function get_inventory_CBM(){
		//llamasys/api/lgepr/get_inventory_CBM?key=lgepr

		if ($this->input->get("key") === "lgepr") {

			$data = $this->gen_m->filter("lgepr_inv_cbm", false);
			$res = [];
			$res_detailed = []; // Array para el resultado detallado por producto
			$daily_totals = [];
			$inventory_codes = ['N4M', 'N4S', 'N4J', 'N4D'];
			$global_totals_last_day = [
											'total_cbm' => 0,
											'balance' 	=> 0,
											'in'		=> 0,
											'out'		=> 0,
											'last_day' 	=> null,
										];

			// Inicializar el array de totales diarios por código
			foreach ($inventory_codes as $code) {
				$daily_totals[$code] = [];
			}

			// Función para procesar los días por métrica
			$process_metric = function ($days_array) {
				$processed = [];
				$has_any_data = false;
				$last_day = 0;

				// Find the last day with any data
				foreach (array_keys($days_array) as $day) {
					$last_day = max($last_day, $day);
					$has_any_data = true;
				}

				if ($has_any_data) {
					for ($d = 1; $d <= $last_day; $d++) {
						$day_str = sprintf("%02d", $d);
						$processed[$day_str] = $days_array[$day_str] ?? 0;
					}
				}
				return $processed;
			};

			// Procesamiento para el resultado detallado y acumulación de totales
			foreach($data as $item){
				$base_key = $item->company . "_" . $item->division . "_" . $item->model . "_" . $item->model_gross_cbm . "_" . $item->inventory_org_code . "_" . $item->subinventory_code . "_" . $item->begining_qty;

				if (!isset($grouped_data[$base_key])) {
					$grouped_data[$base_key] = [
						"company"           => $item->company,
						"division"          => $item->division,
						"model"             => $item->model,
						"modelGrossCBM"     => floatval($item->model_gross_cbm),
						"inventoryOrgCode"  => $item->inventory_org_code,
						"subinventory"      => $item->subinventory_code,
						"qty"               => intval($item->begining_qty),
						"total_cbm_days"    => [],
						"balance_days"      => [],
						"in_days"           => [],
						"out_days"          => [],
					];
				}

				for ($day = 1; $day <= 31; $day++) {
					$day_str = sprintf("%02d", $day);
					$total_cbm_key = "total_cbm_day" . $day;
					$balance_key = "balance_day" . $day;
					$in_key = "in_day" . $day;
					$out_key = "out_day" . $day;

					if (isset($item->$total_cbm_key)) {
						$grouped_data[$base_key]["total_cbm_days"][$day_str] = floatval($item->$total_cbm_key);
						if (in_array($item->inventory_org_code, $inventory_codes)) {
							$daily_totals[$item->inventory_org_code][$day_str]['total_cbm'] = ($daily_totals[$item->inventory_org_code][$day_str]['total_cbm'] ?? 0) + floatval($item->$total_cbm_key);
						}
					}
					if (isset($item->$balance_key)) {
						$grouped_data[$base_key]["balance_days"][$day_str] = floatval($item->$balance_key);
						if (in_array($item->inventory_org_code, $inventory_codes)) {
							$daily_totals[$item->inventory_org_code][$day_str]['balance'] = ($daily_totals[$item->inventory_org_code][$day_str]['balance'] ?? 0) + floatval($item->$balance_key);
						}
					}
					if (isset($item->$in_key)) {
						$grouped_data[$base_key]["in_days"][$day_str] = floatval($item->$in_key);
						if (in_array($item->inventory_org_code, $inventory_codes)) {
							$daily_totals[$item->inventory_org_code][$day_str]['in'] = ($daily_totals[$item->inventory_org_code][$day_str]['in'] ?? 0) + floatval($item->$in_key);
						}
					}
					if (isset($item->$out_key)) {
						$grouped_data[$base_key]["out_days"][$day_str] = floatval($item->$out_key);
						if (in_array($item->inventory_org_code, $inventory_codes)) {
							$daily_totals[$item->inventory_org_code][$day_str]['out'] = ($daily_totals[$item->inventory_org_code][$day_str]['out'] ?? 0) + floatval($item->$out_key);
						}
					}
				}

				$base_info = [
					"company"           => $grouped_data[$base_key]["company"],
					"division"          => $grouped_data[$base_key]["division"],
					"model"             => $grouped_data[$base_key]["model"],
					"modelGrossCBM"     => $grouped_data[$base_key]["modelGrossCBM"],
					"inventoryOrgCode"  => $grouped_data[$base_key]["inventoryOrgCode"],
					"subinventory"      => $grouped_data[$base_key]["subinventory"],
					"qty"               => intval($item->begining_qty),
				];

				if (!empty($grouped_data[$base_key]["total_cbm_days"])) {
					$res_detailed[] = $base_info + ["metric" => "total_cbm", "days" => $process_metric($grouped_data[$base_key]["total_cbm_days"])];
				}
				if (!empty($grouped_data[$base_key]["balance_days"])) {
					$res_detailed[] = $base_info + ["metric" => "balance", "days" => $process_metric($grouped_data[$base_key]["balance_days"])];
				}
				if (!empty($grouped_data[$base_key]["in_days"])) {
					$res_detailed[] = $base_info + ["metric" => "in", "days" => $process_metric($grouped_data[$base_key]["in_days"])];
				}
				if (!empty($grouped_data[$base_key]["out_days"])) {
					$res_detailed[] = $base_info + ["metric" => "out", "days" => $process_metric($grouped_data[$base_key]["out_days"])];
				}
			}

			$res = $res_detailed; 

			$global_last_day = 0;
			foreach ($inventory_codes as $code) {
				$last_day = 0;
				$last_day_data = [
					'total_cbm' => 0,
					'balance' => 0,
					'in' => 0,
					'out' => 0,
				];

				if (!empty($daily_totals[$code])) {
					$days_with_data = array_keys($daily_totals[$code]);
					$last_day = max($days_with_data);
					$last_day_data = $daily_totals[$code][$last_day];

					// Accumulate global totals for the last day of each code
					$global_totals_last_day['total_cbm'] += $last_day_data['total_cbm'];
					$global_totals_last_day['balance'] += $last_day_data['balance'];
					$global_totals_last_day['in'] += $last_day_data['in'];
					$global_totals_last_day['out'] += $last_day_data['out'];
					$global_last_day = max($global_last_day, $last_day);
				}

				$res[] = [
					'inventoryOrgCode' => $code,
					'day' => $last_day ? sprintf("%02d", $last_day) : null,
					'total_cbm' => $last_day_data['total_cbm'],
					'balance' => $last_day_data['balance'],
					'in' => $last_day_data['in'],
					'out' => $last_day_data['out'],
				];
			}
		} else {
			$res = ["Key error"];
		}

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
}
