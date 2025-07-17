<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Sa_sell_in_out_promotion extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"main" => "module/sa_sell_in_out_promotion/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function get_last_cost_prom($promotions, $i, $item_p) {
		$cost_prom = 0;

		foreach ($promotions as $index => $item) {
			if ($i == $index) {
				break;
			} elseif (
				(strtotime($item['date_start']) <= strtotime($item_p['date_start'])) &&
				(strtotime($item_p['date_end']) <= strtotime($item['date_end']))
			) {
				if (isset($item['cost_prom']) && $item['cost_prom']) {
					$cost_prom = $item['cost_prom'];
				}
			}
		}

		return $cost_prom;
	}
	
	public function get_init_cost($sell_map, $out_map, $model_calculate) {
		//print_r($out_map); return;
		// Obtener datos de sell-in y sell-out para el modelo actual
		$ins = isset($sell_map[$model_calculate]) ? $sell_map[$model_calculate] : [];
		$outs = isset($out_map[$model_calculate]) ? $out_map[$model_calculate] : [];
		//echo '<pre>'; print_r($outs); return;
		// sell in taken value init
		foreach ($ins as &$item) {
			$item[6] = 0; // Agregar una nueva clave 'taken' (índice 6) e inicializarla en 0
		}

		if ($outs) {
			$stock_init = $outs[0][2] + $outs[0][1]; // stock + sellout_unit
		} else {
			$stock_init = 1;
		}

		$counter = $stock_init; //17
		$considered_qty = $amount = 0;

		foreach ($ins as &$item) {
			if ($counter > 0) {
				if ($item[1] < 0) { // order_qty (índice 1)
					$item[6] = $item[1]; // taken
					$amount += $item[0] * $item[1]; // unit_selling_price * order_qty (índice 0)
					$counter += abs($item[1]);
				} elseif ($counter <= $item[1]) {
					$item[6] = $counter;
					$amount += $item[0] * $counter;
					$counter = 0;
				} else {
					$item[6] = $item[1];
					$amount += $item[0] * $item[1];
					$counter -= $item[1];
				}

				$considered_qty += $item[6];

				if (!$outs) {
					$item[6] = 0;
				}
			}
		}

		if (!$considered_qty) {

			$considered_qty = 1;
		}
		return round($amount / $considered_qty, 2);
	}
	
	public function get_cost_sellin($sell_map, $out_map, $model_calculate, $start_date, $end_date, $stock){
		//print_r($sell_map[$model_calculate][0]); echo '<br>';
		$start_date = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
		$end_date = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
		$list_txn_date = [];
		$stock_list = [];
		if(!empty($out_map[$model_calculate])){
			foreach($out_map[$model_calculate] as $item){
				//$stock_init = $item[2]; 
				$stock_list[] = [$item[0], $item[2]];
				if($item[0] >= $start_date && $item[0] <= $end_date ){
					$list_txn_date[] = [$item[0], $item[2]];
				}
			}
			
			// Buscar la primera aparición del valor buscado
			$indice_encontrado = array_search($list_txn_date[0][1], $stock_list);
			//$stock_pre = $out_map[$model_calculate][2];
			//print_r([$indice_encontrado, $model_calculate]); echo '<br>';
			//$stock1 = !empty($list_txn_date[0][1]) ? $list_txn_date[0][1] : $stock_pre;
			//print_r([$stock1, $model_calculate, $out_map[$model_calculate][0]]);
		}
		// Valor stock coincide con primera fecha, se agarra. sino hay stock buscar en anteriores.
		// 
		
		// if(!empty($sell_map[$model_calculate])){
			// foreach($sell_map[$model_calculate] as $item){
				// if($item[1] != $stock1){
					// $item[1] += $item[1];
				// }
				// print_r($item);
			// }
		// }
		return 1;
	}
	
	public function qty_calculate($out_map, $model_calculate, $start_date, $end_date, $flag_s_qty = 0, $price_promotion){
		//print_r($start_date); echo '<br>'; return;
		// Formatear las fechas al formato YYYY-MM-DD si no lo están
		if (strlen($start_date) === 8) {
			$start_date = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
		}
		if (strlen($end_date) === 8) {
			$end_date = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
		}
		// $start_date = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
		// $end_date = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
		$qty = 0;
		if($flag_s_qty == 0){
			// if(!empty($out_map[$model_calculate])){
				// foreach($out_map[$model_calculate] as $item){
					// //print_r($item); echo '<br>';
					// if($item[0] >= $start_date && $item[0] <= $end_date && $item[3] !== 'PROMO NO APPLIED'){
						// $qty += $item[1];
					// }		
				// }
			// } else $qty = 0;
			
			if (!empty($out_map[$model_calculate])) {
				foreach ($out_map[$model_calculate] as $item) {
					// Verificar si la fecha está dentro del rango
					if ($item[0] >= $start_date && $item[0] <= $end_date) {
						// Buscar la columna promoX_price que coincida con $price_promotion
						$promo_column_index = -1; // Inicializar como no encontrado
						for ($i = 4; $i <= 22; $i += 2) { // Comenzar en 4 (promo1_price), incrementar de 2 en 2
							if (isset($item[$i]) && $item[$i] == $price_promotion) {
								$promo_column_index = $i;
								break; // Detener el bucle al encontrar la coincidencia
							}
						}

						// Si se encontró una columna promoX_price coincidente, verificar el target y sumar qty
						if ($promo_column_index != -1) {
							$target_column_index = $promo_column_index - 1; // La columna target está justo antes de la columna de precio
							if (isset($item[$target_column_index]) && $item[$target_column_index] !== 'PROMO NO APPLIED') {
								$qty += $item[1];
							}
						}
					}
				}
			} else {
				$qty = 0;
			}
		}
		elseif($flag_s_qty == 1){
			if(!empty($out_map[$model_calculate])){
				foreach($out_map[$model_calculate] as $item){
					//print_r($item); echo '<br>';
					if($item[0] >= $start_date && $item[0] <= $end_date){
						$qty += $item[1];
					}		
				}
			} else $qty = 0;
		}
		return $qty;
	}
	
	public function date_convert_mm_dd_yyyy($date) {
    // Intentamos convertir con la lógica del valor numérico (excel date)
		if (is_numeric($date)) {
			// Si es un número, es probable que sea una fecha de Excel (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número, intentamos convertir con la lógica de fecha en formato mm/dd/yyyy
		$aux = explode("/", $date);
		if (count($aux) == 3) {
			// Verificamos que la fecha esté en formato mm/dd/yyyy
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}
		
		// Si la fecha no está en un formato esperado, devolvemos null
		return null;
	}
	
	public function load_calculate($sheet){
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue()),
			trim($sheet->getCell('J1')->getValue()),
			trim($sheet->getCell('K1')->getValue()),
		];
		
		$h_origin = ["Seq", "Company Name", "Division Name", "Promotion No", "Promotion Line No", "Fecha Inicio", "Fecha Fin", "Customer Code", "Modelo", "PVP", "Costo Sellin"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		$max_row = $sheet->getHighestRow();
		$batch_size = 300;
		$rows = $date_arr = [];
		$updated = date("Y-m-d H:i:s");
		//save file records in array
		for ($i = 2; $i < $max_row; $i++) {
			//if ($is_empty_row) continue; // Si la fila está vacía, detenemos el bucle en esta hoja
			$row = [
				"seq" 								=> trim($sheet->getCell('A'.$i)->getValue()),
				"company_name" 						=> trim($sheet->getCell('B'.$i)->getValue()),					
				"division_name" 					=> trim($sheet->getCell('C'.$i)->getValue()),
				"promotion_no" 						=> trim($sheet->getCell('D'.$i)->getValue()),
				"promotion_line_no" 				=> trim($sheet->getCell('E'.$i)->getValue()),
				"start_date" 						=> trim($sheet->getCell('F'.$i)->getValue()),
				"end_date" 							=> trim($sheet->getCell('G'.$i)->getValue()),					
				"customer_code" 					=> trim($sheet->getCell('H'.$i)->getValue()),
				"model" 							=> trim($sheet->getCell('I'.$i)->getValue()),
				"pvp" 								=> trim($sheet->getCell('J'.$i)->getValue()),
				"cost_sellin" 						=> trim($sheet->getCell('K'.$i)->getValue()),
				"price_promotion" 					=> trim($sheet->getCell('L'.$i)->getValue()),
				"new_margin" 						=> trim($sheet->getCell('M'.$i)->getValue()) . '%',
				"cost_promotion" 					=> trim($sheet->getCell('N'.$i)->getValue()),
				"difference" 						=> trim($sheet->getCell('O'.$i)->getValue()),
				"unity" 							=> trim($sheet->getCell('P'.$i)->getValue()),
				"gift" 								=> trim($sheet->getCell('Q'.$i)->getValue()),		
				"upload" 							=> $updated
			];
			
			$batch_data[] = $row;

			// Inserción por lotes
			if (count($batch_data) >= $batch_size) {
				$this->gen_m->insert_m("sa_calculate_promotion", $batch_data);
				$batch_data = [];
			}
		}
		//echo '<pre>'; print_r($batch_data);
		// Insertar los datos restantes en el lote
		if (!empty($batch_data)) {
			$this->gen_m->insert_m("sa_calculate_promotion", $batch_data);
		}
		
		// Finalizar transacción
		$this->db->trans_complete();
	}
	
	public function load_sell_out2($sheet){
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue()),
			trim($sheet->getCell('J1')->getValue()),
			trim($sheet->getCell('K1')->getValue()),
		];
		
		$h_origin = ["CUSTOMER", "ACCT_GTM", "CUSTOMER_MODEL", "MODEL_CODE", "TXN_DATE", "CUST_STORE_CODE", "CUST_STORE_NAME", "SELLOUT_UNIT", "SELLOUT_AMT", "STOCK", "TICKET"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		$max_row = $sheet->getHighestRow();
		$batch_size = 300;
		$rows = $date_arr = [];
		$updated = date("Y-m-d");
		//save file records in array
		for ($i = 2; $i <= $max_row; $i++) {
			//if ($is_empty_row) continue; // Si la fila está vacía, detenemos el bucle en esta hoja
			$row = [
				"customer" 								=> trim($sheet->getCell('A'.$i)->getValue()),
				"acct_gtm" 								=> trim($sheet->getCell('B'.$i)->getValue()),					
				"customer_model" 						=> trim($sheet->getCell('C'.$i)->getValue()),
				"model_code" 							=> trim($sheet->getCell('D'.$i)->getValue()),
				"txn_date" 								=> trim($sheet->getCell('E'.$i)->getValue()),
				"cust_store_code" 						=> trim($sheet->getCell('F'.$i)->getValue()),
				"cust_store_name" 						=> trim($sheet->getCell('G'.$i)->getValue()),					
				"sellout_unit" 							=> trim($sheet->getCell('H'.$i)->getValue()),
				"sellout_amt" 							=> trim($sheet->getCell('I'.$i)->getValue()),
				"stock" 								=> trim($sheet->getCell('J'.$i)->getValue()),
				"ticket" 								=> trim($sheet->getCell('K'.$i)->getValue()),
				"promo1_price" 							=> trim($sheet->getCell('L'.$i)->getValue()),					
				"target1_flag" 							=> trim($sheet->getCell('M'.$i)->getValue()),
				"promo2_price" 							=> trim($sheet->getCell('N'.$i)->getValue()),
				"target2_flag" 							=> trim($sheet->getCell('O'.$i)->getValue()),
				"promo3_price" 							=> trim($sheet->getCell('P'.$i)->getValue()),
				"target3_flag" 							=> trim($sheet->getCell('Q'.$i)->getValue()),					
				"promo4_price" 							=> trim($sheet->getCell('R'.$i)->getValue()),
				"target4_flag" 							=> trim($sheet->getCell('S'.$i)->getValue()),
				"promo5_price" 							=> trim($sheet->getCell('T'.$i)->getValue()),
				"target5_flag" 							=> trim($sheet->getCell('U'.$i)->getValue()),
				"promo6_price" 							=> trim($sheet->getCell('V'.$i)->getValue()),					
				"target6_flag" 							=> trim($sheet->getCell('W'.$i)->getValue()),
				"promo7_price" 							=> trim($sheet->getCell('X'.$i)->getValue()),
				"target7_flag" 							=> trim($sheet->getCell('Y'.$i)->getValue()),
				"promo8_price" 							=> trim($sheet->getCell('Z'.$i)->getValue()),
				"target8_flag" 							=> trim($sheet->getCell('AA'.$i)->getValue()),					
				"promo9_price" 							=> trim($sheet->getCell('AB'.$i)->getValue()),
				"target9_flag" 							=> trim($sheet->getCell('AC'.$i)->getValue()),
				"promo10_price" 						=> trim($sheet->getCell('AD'.$i)->getValue()),
				"target10_flag" 						=> trim($sheet->getCell('AE'.$i)->getValue()),					
				"updated" 								=> $updated
			];
			
			// if(empty($row['division_name']) && empty($row['bill_to_customer_code'])){
				// break;
			// }
			if(!empty($row["customer"]) && !empty($row["acct_gtm"]) && !empty($row["customer_model"])){
				$row["txn_date"] = $this->date_convert_mm_dd_yyyy($row["txn_date"]);
			}
			$batch_data[] = $row;

			// Inserción por lotes
			if (count($batch_data) >= $batch_size) {
				$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
				$batch_data = [];
			}
		}

		// Insertar los datos restantes en el lote
		if (!empty($batch_data)) {
			$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
		}
		
		// Finalizar transacción
		$this->db->trans_complete();
	}
	
	public function load_sell_in($sheet){
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue()),
			trim($sheet->getCell('J1')->getValue()),
			trim($sheet->getCell('K1')->getValue()),
			trim($sheet->getCell('L1')->getValue()),
		];
		
		$h_origin = ["Seq", "Division Name", "Bill To Customer Code", "Item Name", "Sales YYYYMMDD", "Invoice NO", "⁬Week-YYYYWWW", "Transaction Currency Code", "Sales Order Qty", "Unit Selling Price", "Sales Order Amount (Tax Exclude)", "TC"];
		
		$header_validation = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_origin[$i]) $header_validation = false;
		
		$max_row = $sheet->getHighestRow();
		$batch_size = 1000;
		$rows = $date_arr = [];
		$updated = date("Y-m-d");
		//save file records in array
		for ($i = 2; $i <= $max_row; $i++) {
			//if ($is_empty_row) continue; // Si la fila está vacía, detenemos el bucle en esta hoja
			$row = [
				"seq" 									=> trim($sheet->getCell('A'.$i)->getValue()),
				"division_name" 						=> trim($sheet->getCell('B'.$i)->getValue()),					
				"bill_to_customer_code" 				=> trim($sheet->getCell('C'.$i)->getValue()),
				"item_name" 							=> trim($sheet->getCell('D'.$i)->getValue()),
				"sales_YYYYmmdd" 						=> trim($sheet->getCell('E'.$i)->getValue()),
				"invoice_no" 							=> trim($sheet->getCell('F'.$i)->getValue()),
				"week_yyyywww" 							=> trim($sheet->getCell('G'.$i)->getValue()),					
				"transaction_currency_code" 			=> trim($sheet->getCell('H'.$i)->getValue()),
				"sales_order_qty" 						=> trim($sheet->getCell('I'.$i)->getValue()),
				"unit_selling_price" 					=> trim($sheet->getCell('J'.$i)->getValue()),
				"sales_order_amount_tax_exclude" 		=> trim($sheet->getCell('K'.$i)->getValue()),
				"tc" 									=> trim($sheet->getCell('L'.$i)->getValue()),					
				"unit_selling_price_pen" 				=> trim($sheet->getCell('M'.$i)->getValue()),
				"cal_seq1" 								=> trim($sheet->getCell('N'.$i)->getValue()),
				"new_cost1" 							=> trim($sheet->getCell('O'.$i)->getValue()),
				"sellout1" 								=> trim($sheet->getCell('P'.$i)->getValue()),
				"stock1" 								=> trim($sheet->getCell('Q'.$i)->getValue()),					
				"cal_seq2" 								=> trim($sheet->getCell('R'.$i)->getValue()),
				"new_cost2" 							=> trim($sheet->getCell('S'.$i)->getValue()),
				"sellout2" 								=> trim($sheet->getCell('T'.$i)->getValue()),
				"stock2" 								=> trim($sheet->getCell('U'.$i)->getValue()),
				"cal_seq3" 								=> trim($sheet->getCell('V'.$i)->getValue()),					
				"new_cost3" 							=> trim($sheet->getCell('W'.$i)->getValue()),
				"sellout3" 								=> trim($sheet->getCell('X'.$i)->getValue()),
				"stock3" 								=> trim($sheet->getCell('Y'.$i)->getValue()),
				"cal_seq4" 								=> trim($sheet->getCell('Z'.$i)->getValue()),
				"new_cost4" 							=> trim($sheet->getCell('AA'.$i)->getValue()),					
				"sellout4" 								=> trim($sheet->getCell('AB'.$i)->getValue()),
				"stock4" 								=> trim($sheet->getCell('AC'.$i)->getValue()),
				"cal_seq5" 								=> trim($sheet->getCell('AD'.$i)->getValue()),
				"new_cost5" 							=> trim($sheet->getCell('AE'.$i)->getValue()),					
				"sellout5" 								=> trim($sheet->getCell('AF'.$i)->getValue()),
				"stock5" 								=> trim($sheet->getCell('AG'.$i)->getValue()),
				"cal_seq6" 								=> trim($sheet->getCell('AH'.$i)->getValue()),
				"new_cost6" 							=> trim($sheet->getCell('AI'.$i)->getValue()),					
				"sellout6" 								=> trim($sheet->getCell('AJ'.$i)->getValue()),
				"stock6" 								=> trim($sheet->getCell('AK'.$i)->getValue()),
				"cal_seq7" 								=> trim($sheet->getCell('AL'.$i)->getValue()),
				"new_cost7" 							=> trim($sheet->getCell('AM'.$i)->getValue()),					
				"sellout7" 								=> trim($sheet->getCell('AN'.$i)->getValue()),
				"stock7" 								=> trim($sheet->getCell('AO'.$i)->getValue()),
				"cal_seq8" 								=> trim($sheet->getCell('AP'.$i)->getValue()),
				"new_cost8" 							=> trim($sheet->getCell('AQ'.$i)->getValue()),					
				"sellout8" 								=> trim($sheet->getCell('AR'.$i)->getValue()),
				"stock8" 								=> trim($sheet->getCell('AS'.$i)->getValue()),
				"cal_seq9" 								=> trim($sheet->getCell('AT'.$i)->getValue()),
				"new_cost9" 							=> trim($sheet->getCell('AU'.$i)->getValue()),					
				"sellout9" 								=> trim($sheet->getCell('AV'.$i)->getValue()),
				"stock9" 								=> trim($sheet->getCell('AW'.$i)->getValue()),
				"cal_seq10" 							=> trim($sheet->getCell('AX'.$i)->getValue()),
				"new_cost10" 							=> trim($sheet->getCell('AY'.$i)->getValue()),					
				"sellout10" 							=> trim($sheet->getCell('AZ'.$i)->getValue()),
				"stock10" 								=> trim($sheet->getCell('BA'.$i)->getValue()),
				"bill_to_customer_name" 				=> trim($sheet->getCell('BB'.$i)->getValue()),
				"so_no " 								=> trim($sheet->getCell('BC'.$i)->getValue()),					
				"so_type_name" 							=> trim($sheet->getCell('BD'.$i)->getValue()),
				"updated" 								=> $updated
			];
			
			if(empty($row['division_name']) && empty($row['bill_to_customer_code'])){
				break;
			}
			$batch_data[] = $row;

			// Inserción por lotes
			if (count($batch_data) >= $batch_size) {
				$this->gen_m->insert_m("sa_sell_in_promotion", $batch_data);
				$batch_data = [];
			}
		}

		// Insertar los datos restantes en el lote
		if (!empty($batch_data)) {
			$this->gen_m->insert_m("sa_sell_in_promotion", $batch_data);
		}
		
		// Finalizar transacción
		$this->db->trans_complete();

		//return "Records uploaded in " . number_format(microtime(true) - $start_time, 2) . " secs.";
	}
	
	public function calcularStockAg_v1($out_map, $model_calculate, $start_date_conv, $end_date_conv) {
		if (!empty($out_map[$model_calculate])) {
			$fechas_modelo = $out_map[$model_calculate];

			// Verificar si start_date_conv existe con stock != 0
			foreach ($fechas_modelo as $item) {
				if ($item[0] === $start_date_conv) {
					if ($item[2] != 0) {
						return $item[2];
					} else {
						break;
					}
				}
			}

			// Lógica pasado/futuro
			usort($fechas_modelo, function($a, $b) {
				return strtotime($a[0]) - strtotime($b[0]);
			});

			$indice_start_date = -1;
			foreach ($fechas_modelo as $i => $item) {
				if ($item[0] >= $start_date_conv && $item[0] <= $end_date_conv) {
					$indice_start_date = $i;
					break;
				}
			}

			if ($indice_start_date !== -1) {
				// Buscar hacia atrás
				$fecha_pasada = null;
				$stock_pasado = 0;
				$sellout_acumulado_pasado = 0;
				$indice_fecha_pasada = -1;
				for ($i = $indice_start_date - 1; $i >= 0; $i--) {
					if ($fechas_modelo[$i][2] != 0) {
						$fecha_pasada = $fechas_modelo[$i][0];
						$stock_pasado = $fechas_modelo[$i][2];
						$indice_fecha_pasada = $i;
						break;
					}
				}
				if ($indice_fecha_pasada !== -1) {
					for ($i = $indice_start_date - 1; $i > $indice_fecha_pasada; $i--) {
						if ($fechas_modelo[$i][0] !== $fecha_pasada) {
							$sellout_acumulado_pasado += $fechas_modelo[$i][1];
						}
					}
				}
				$stock_ag_pasado = ($fecha_pasada !== null) ? $stock_pasado - $sellout_acumulado_pasado : null;

				// Buscar hacia adelante
				$fecha_futura = null;
				$stock_futuro = 0;
				$sellout_acumulado_futuro = 0;
				$indice_fecha_futura = -1;
				for ($i = $indice_start_date; $i < count($fechas_modelo); $i++) {
					if ($fechas_modelo[$i][2] != 0) {
						$fecha_futura = $fechas_modelo[$i][0];
						$stock_futuro = $fechas_modelo[$i][2];
						$indice_fecha_futura = $i;
						if ($fechas_modelo[$i][0] > $start_date_conv) break;
					}
				}
				if ($indice_fecha_futura !== -1) {
					for ($i = $indice_start_date; $i < $indice_fecha_futura; $i++) {
						$sellout_acumulado_futuro += $fechas_modelo[$i][1];
					}
					// Incluir el sellout de start_date_conv si su stock era 0
					foreach ($fechas_modelo as $item) {
						if ($item[0] === $start_date_conv && $item[2] == 0) {
							$sellout_acumulado_futuro += $item[1];
							break;
						}
					}
				}
				$stock_ag_futuro = ($fecha_futura !== null) ? $stock_futuro + $sellout_acumulado_futuro : null;

				// Selección con prioridad para stock_ag_pasado == 0
				if ($stock_ag_pasado === 0) {
					return 0;
				} elseif ($fecha_pasada !== null && $fecha_futura !== null) {
					$diff_pasado = abs(strtotime($start_date_conv) - strtotime($fecha_pasada));
					$diff_futuro = abs(strtotime($start_date_conv) - strtotime($fecha_futura));

					if ($diff_pasado <= $diff_futuro) {
						return $stock_ag_pasado;
					} else {
						return $stock_ag_futuro;
					}
				} elseif ($fecha_pasada !== null) {
					return $stock_ag_pasado;
				} elseif ($fecha_futura !== null) {
					return $stock_ag_futuro;
				}
			}
		}
		return 0;
	}

	public function calcularStockAg($out_map, $model_calculate, $start_date_conv, $end_date_conv) {
		if (!empty($out_map[$model_calculate])) {
			$fechas_modelo = $out_map[$model_calculate];

			// Verificar si start_date_conv existe con stock != 0
			foreach ($fechas_modelo as $item) {
				if ($item[0] === $start_date_conv) {
					if ($item[2] != 0) {
						return $item[2];
					} else {
						break;
					}
				}
			}

			// Lógica pasado/futuro
			usort($fechas_modelo, function($a, $b) {
				return strtotime($a[0]) - strtotime($b[0]);
			});

			$indice_start_date = -1;
			foreach ($fechas_modelo as $i => $item) {
				if ($item[0] >= $start_date_conv && $item[0] <= $end_date_conv) {
					$indice_start_date = $i;
					break;
				}
			}

			if ($indice_start_date !== -1) {
				// Buscar hacia atrás
				$fecha_pasada = null;
				$stock_pasado = 0;
				$sellout_acumulado_pasado = 0;
				$indice_fecha_pasada = -1;
				$fecha_pasada_comparacion = null; // Nueva variable para la fecha de comparación

				for ($i = $indice_start_date - 1; $i >= 0; $i--) {
					if ($fechas_modelo[$i][2] != 0) {
						$fecha_pasada = $fechas_modelo[$i][0];
						$stock_pasado = $fechas_modelo[$i][2];
						$indice_fecha_pasada = $i;
						break;
					}
					if ($fecha_pasada_comparacion === null) {
						$fecha_pasada_comparacion = $fechas_modelo[$i][0]; // Guardar la primera fecha anterior
					}
				}

				if ($indice_fecha_pasada !== -1) {
					for ($i = $indice_start_date - 1; $i > $indice_fecha_pasada; $i--) {
						if ($fechas_modelo[$i][0] !== $fecha_pasada) {
							$sellout_acumulado_pasado += $fechas_modelo[$i][1];
						}
					}
				}
				$stock_ag_pasado = ($fecha_pasada !== null) ? $stock_pasado - $sellout_acumulado_pasado : null;
				$fecha_pasada_comparacion = ($fecha_pasada_comparacion !== null) ? $fecha_pasada_comparacion : $fecha_pasada;


				// Buscar hacia adelante
				$fecha_futura = null;
				$stock_futuro = 0;
				$sellout_acumulado_futuro = 0;
				$indice_fecha_futura = -1;
				$fecha_futura_comparacion = null; // Nueva variable para la fecha de comparación
				for ($i = $indice_start_date; $i < count($fechas_modelo); $i++) {
					if ($fechas_modelo[$i][2] != 0) {
						$fecha_futura = $fechas_modelo[$i][0];
						$stock_futuro = $fechas_modelo[$i][2];
						$indice_fecha_futura = $i;
						break;
					}
					if ($fecha_futura_comparacion === null && $fechas_modelo[$i][0] != $start_date_conv) {
						$fecha_futura_comparacion = $fechas_modelo[$i][0]; // Guardar la primera fecha después de start_date_conv
					}
				}
				if ($indice_fecha_futura !== -1) {
					for ($i = $indice_start_date; $i < $indice_fecha_futura; $i++) {
							$sellout_acumulado_futuro += $fechas_modelo[$i][1];
					}
					// Sumar sellout de la misma fecha del stock
					for ($i = $indice_fecha_futura; $i < count($fechas_modelo); $i++) {
						if ($fechas_modelo[$i][0] === $fecha_futura) {
							$sellout_acumulado_futuro += $fechas_modelo[$i][1];
						}
					}
				}
				$stock_ag_futuro = ($fecha_futura !== null) ? $stock_futuro + $sellout_acumulado_futuro : null;
				$fecha_futura_comparacion = ($fecha_futura_comparacion !== null) ? $fecha_futura_comparacion : $fecha_futura;


				// Selección con prioridad para stock_ag_pasado == 0
				if ($stock_ag_pasado === 0) {
					return 0;
				} elseif ($fecha_pasada !== null && $fecha_futura !== null) {
					$diff_pasado = abs(strtotime($start_date_conv) - strtotime($fecha_pasada_comparacion));
					$diff_futuro = abs(strtotime($start_date_conv) - strtotime($fecha_futura_comparacion));

					if ($diff_pasado < $diff_futuro) {
						return $stock_ag_pasado;
					} elseif ($diff_futuro < $diff_pasado) {
						return $stock_ag_futuro;
					} else {
						// Misma diferencia, priorizar el mayor stock
						if ($stock_ag_pasado > $stock_ag_futuro) {
							return $stock_ag_pasado;
						} elseif ($stock_ag_futuro > $stock_ag_pasado) {
							return $stock_ag_futuro;
						} else {
							// Stocks iguales, retornar cualquiera
							return $stock_ag_pasado; // o $stock_ag_futuro; da igual
						}
					}
				} elseif ($fecha_pasada !== null) {
					return $stock_ag_pasado;
				} elseif ($fecha_futura !== null) {
					return $stock_ag_futuro;
				}
			}
		}
		return 0;
	}

	public function generate_excel() {
		set_time_limit(0);
		ini_set("memory_limit", -1);
        // Cargar plantilla vacía
        $template_path = './template/sa_promotion_template.xlsx';
        if (!file_exists($template_path)) {
            echo "Error: No se encontró la plantilla de Excel.";
            return;
        }
		$directory = "./upload_file/Sales Admin/";
		$files = scandir($directory);

		// Filtrar archivos que sean Excel (.xlsx o .xls)
		$excel_files = array_filter($files, function ($file) {
			return preg_match('/\.(xlsx|xls)$/i', $file);
		});
		//print_r($directory . $excel_file);
		if (!empty($excel_files)) {
			$excel_file = trim(array_values($excel_files)[0]); // Obtener el primer archivo encontrado
			$original_spreadsheet = IOFactory::load($directory . $excel_file);
			 // Eliminar el archivo después de cargarlo
			unlink($directory . $excel_file); // Borra el archivo de la carpeta
		} else {
			echo json_encode(["type" => "error", "msg" => "No se encontró ningún archivo Excel en la carpeta."]);
			return;
		}
		
        $spreadsheet = IOFactory::load($template_path);

        $sheet_names = ['CALCULATE', 'SELLIN', 'SELLOUT', 'MIX_DETAIL', 'SPMS', 'CLIENTE', 'TP', 'CORREO'];
		
		$sell_in_data = $this->gen_m->filter_select('sa_sell_in_promotion', false, ['item_name', 'unit_selling_price', 'stock1', 'stock2', 'sales_order_qty', 'sales_yyyymmdd', 'invoice_no']);
		$sell_map = [];
		foreach ($sell_in_data as $item) {
			$sell_map[$item->item_name][] = [$item->unit_selling_price, $item->stock1, $item->stock2, $item->sales_order_qty, $item->sales_yyyymmdd, $item->invoice_no];
		}
		// echo '<pre>'; print_r($sell_map); return;
		$sell_out_data = $this->gen_m->filter_select('sa_sell_out_promotion', false, ['model_code', 'txn_date', 'sellout_unit', 'stock', 'target1_flag', 'promo1_price', 'target2_flag', 'promo2_price', 
		'target3_flag', 'promo3_price', 'target4_flag', 'promo4_price', 'target5_flag', 'promo5_price', 'target6_flag', 'promo6_price', 'target7_flag', 'promo7_price', 'target8_flag', 'promo8_price', 
		'target9_flag', 'promo9_price', 'target10_flag', 'promo10_price']);
		$out_map = [];
		foreach ($sell_out_data as $item) {
			$out_map[$item->model_code][] = [$item->txn_date, $item->sellout_unit, $item->stock, $item->target1_flag, $item->promo1_price, $item->target2_flag, $item->promo2_price, $item->target3_flag, $item->promo3_price, $item->target4_flag, $item->promo4_price, $item->target5_flag, $item->promo5_price, $item->target6_flag, $item->promo6_price, $item->target7_flag, $item->promo7_price, $item->target8_flag, $item->promo8_price, $item->target9_flag, $item->promo9_price, $item->target10_flag, $item->promo10_price];
		}
		
		$sell_out_all_data = $this->gen_m->filter('sa_sell_out_promotion', false, null, null, null, [['customer_model', 'asc'], ['txn_date', 'asc']]);
		//echo '<pre>'; print_r($sell_out_all_data); return;
		$stock_global = [];
		$vars_global = [];
		$cost_sellin_data = [];
		$priority = [];
		$model_bundle = [];
		$vars_mix = [];
		$seq_basic_mix = [];
		$star_date_basic = [];
		$end_date_basic = [];
		$start_date_pre_basic = [];
		$end_date_pre_basic = [];
		$basic_ranges = []; // Para almacenar los rangos BASIC por model_calculate
		$additional_counts = []; // Para contar los ADDITIONAL por rango BASIC y model_calculate
		$start_date_add = [];
		$end_date_add = [];
		$ref_and_costprom = [];
		$is_hiraoka = false;
		$model_hiraoka = [];
        foreach ($sheet_names as $sheet_name) {
            $original_sheet = $original_spreadsheet->getSheetByName($sheet_name);
            $new_sheet = $spreadsheet->getSheetByName($sheet_name);

            if ($original_sheet && $new_sheet) {
                $data = $original_sheet->toArray();
				
				$data = array_filter($data, function ($row) {
					return !empty(array_filter($row, function ($cell) {
						return !empty($cell);
					}));
				});
				//echo '<pre>'; print_r($data);
                $new_sheet->fromArray($data);
				
                if ($sheet_name === 'CALCULATE') {
					$previous_range = null;
					$index_model_ocurrence = 0;
                    foreach ($data as $index => $row) {
						
                        if ($index > 0) { // Ignorar cabeceras
							$model_calculate = $row[8];
							//$model_bundle[$model_calculate][] = $row[16];
							$model_bundle = $row[16];

							//$model_global[] = $row[8];
							$flag_additional = 0;
							$start_date = $row[5];
							$price_promotion = $row[11];
							$row[12] = ltrim($row[12], '0');
							$new_sheet->setCellValue('M' . ($index + 1), $row[12]); // Llenar columna K
							$end_date = $row[6];
							$start_date_conv = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
							$end_date_conv = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
							$current_start_date = null; // Inicializar la fecha de inicio actual
							$current_end_date = null;
							//$previous_start_dates = [];
							//$previous_seq = [];
							//$previous_end_dates = [];
							if ($row[7] === 'PE000816001B') $is_hiraoka = true;
							$porcent = $row[12];
							$porcent_ns = str_replace("%", "", $porcent);
							$porcent_num = floatval($porcent_ns) / 100;
							$cost_prom = number_format(($row[11]/1.18)*(1-$porcent_num), 2);
							//echo $porcentaje_num; // Imprime 0.15
							$new_sheet->setCellValue('N' . ($index + 1), $cost_prom); // Llenar columna N
							
							$current_start_date = $start_date_conv;
							$current_end_date = $end_date_conv;
							//$index_ocurrence[$model_calculate] = 0;
							$flag_ocurrence = 0; // basicas
							
							
							// Lógica para BASIC y almacenamiento de rangos BASIC
							// if (!isset($basic_ranges[$model_calculate]) || count($basic_ranges[$model_calculate]) < 2) {
								// $is_first_basic = !isset($basic_ranges[$model_calculate]) || count($basic_ranges[$model_calculate]) == 0;
								// $is_second_basic = isset($basic_ranges[$model_calculate]) && count($basic_ranges[$model_calculate]) == 1;

								// if ($is_first_basic) {
									// $basic_ranges[$model_calculate][] = ['start' => $start_date_conv, 'end' => $end_date_conv];
									// $star_date_basic[$model_calculate] = $start_date_conv;
									// $end_date_basic[$model_calculate] = $end_date_conv;
									// $seq_basic[$model_calculate] = $row[0];
									// $seq_basic_mix[$model_calculate][] = $row[0];
									// $cost_row_13[$model_calculate] = $cost_prom;
									// $priority = "1-BASIC";
									// $cost_sellin = 0;
									// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
									// $new_sheet->setCellValue('AF' . ($index + 1), $row[0]);
									// continue;
								// } elseif ($is_second_basic) {
									// // Lógica para encontrar el segundo BASIC que complementa el primero (requiere análisis del mes)
									// $first_basic_start = new DateTime($basic_ranges[$model_calculate][0]['start']);
									// $first_basic_end = new DateTime($basic_ranges[$model_calculate][0]['end']);
									// $current_start_dt = new DateTime($start_date_conv);
									// $current_end_dt = new DateTime($end_date_conv);

									// $primer_dia_mes = $first_basic_start->format('Y-m-01');
									// $ultimo_dia_mes = $first_basic_start->format('Y-m-t');
									// $dia_despues_primer_basic = $first_basic_end->modify('+1 day')->format('Y-m-d');
									// $dia_antes_primer_basic = $first_basic_start->modify('-1 day')->format('Y-m-d');

									// $completa_adelante = ($current_start_date === $dia_despues_primer_basic && $current_end_date === $ultimo_dia_mes);
									// $completa_atras = ($current_end_date === $dia_antes_primer_basic && $current_start_date === $primer_dia_mes);

									// if ($completa_adelante || $completa_atras) {
										// $basic_ranges[$model_calculate][] = ['start' => $start_date_conv, 'end' => $end_date_conv];
										// $star_date_basic[$model_calculate] = min($star_date_basic[$model_calculate], $start_date_conv);
										// $end_date_basic[$model_calculate] = max($end_date_basic[$model_calculate], $end_date_conv);
										// $seq_basic[$model_calculate] = $seq_basic[$model_calculate] ?? $row[0];
										// $seq_basic_mix[$model_calculate][] = $row[0];
										// $cost_row_13[$model_calculate] = $cost_row_13[$model_calculate] ?? $cost_prom;
										// $priority = "1-BASIC";
										// $cost_sellin = 0;
										// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
										// $new_sheet->setCellValue('AF' . ($index + 1), $row[0]);
										// continue;
									// }
								// }
							// }

							// // Lógica para ADDITIONAL
							// if (isset($basic_ranges[$model_calculate]) && count($basic_ranges[$model_calculate]) == 2) {
								// foreach ($basic_ranges[$model_calculate] as $basic_index => $basic_range) {
									// if ($start_date_conv === $basic_range['start'] && $end_date_conv === $basic_range['end']) {
										// $additional_counts[$model_calculate][$basic_index] = ($additional_counts[$model_calculate][$basic_index] ?? 0) + 1;
										// $priority = "2-ADDITIONAL" . ($additional_counts[$model_calculate][$basic_index] > 1 ? $additional_counts[$model_calculate][$basic_index] : '');
										// $cost_sellin = isset($cost_row_13[$model_calculate]) ? $cost_row_13[$model_calculate] : 0;
										// $ref_line = $seq_basic[$model_calculate] ?? '';
										// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
										// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
										// continue;
									// }
								// }
							// }

							// // Lógica para BUNDLE
							// $start_dt = new DateTime($start_date_conv);
							// $end_dt = new DateTime($end_date_conv);
							// $primer_dia = (int) $start_dt->format('d');
							// $ultimo_dia_mes = (int) $start_dt->format('t');
							// $mes_inicio = $start_dt->format('m');
							// $anio_inicio = $start_dt->format('Y');
							// $mes_fin = $end_dt->format('m');
							// $anio_fin = $end_dt->format('Y');

							// if ($primer_dia === 1 && (int) $end_dt->format('d') === $ultimo_dia_mes && $mes_inicio === $mes_fin && $anio_inicio === $anio_fin) {
								// $priority = "BUNDLE";
								// $cost_sellin = 0;
								// $ref_line = '';
								// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
								// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
								// continue;
							// }

							// // Lógica para MIX
							// if (isset($basic_ranges[$model_calculate]) && count($basic_ranges[$model_calculate]) == 2) {
								// $basic_start_1_dt = new DateTime($basic_ranges[$model_calculate][0]['start']);
								// $basic_end_1_dt = new DateTime($basic_ranges[$model_calculate][0]['end']);
								// $basic_start_2_dt = new DateTime($basic_ranges[$model_calculate][1]['start']);
								// $basic_end_2_dt = new DateTime($basic_ranges[$model_calculate][1]['end']);
								// $current_start_dt = new DateTime($start_date_conv);
								// $current_end_dt = new DateTime($end_date_conv);

								// $start_in_basic1 = ($current_start_dt >= $basic_start_1_dt && $current_start_dt <= $basic_end_1_dt);
								// $end_in_basic2 = ($current_end_dt >= $basic_start_2_dt && $current_end_dt <= $basic_end_2_dt);
								// $start_in_basic2 = ($current_start_dt >= $basic_start_2_dt && $current_start_dt <= $basic_end_2_dt);
								// $end_in_basic1 = ($current_end_dt >= $basic_start_1_dt && $current_end_dt <= $basic_end_1_dt);

								// if (($start_in_basic1 && $end_in_basic2) || ($start_in_basic2 && $end_in_basic1)) {
									// $priority = "3-MIX";
									// $cost_sellin = 0;
									// $ref_line = $seq_basic_mix[$model_calculate][0] ?? '';
									// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
									// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
									// continue;
								// }
							// }
							/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							if(isset($star_date_basic[$model_calculate]) && isset($end_date_basic[$model_calculate]) && $current_start_date >= $star_date_basic[$model_calculate] && $current_start_date <= $end_date_basic[$model_calculate]){
								$flag_ocurrence = 1;
								
								if ($flag_ocurrence == 1){
									$cost_sellin = isset($cost_row_13[$model_calculate]) ? $cost_row_13[$model_calculate] : 0;
									$priority = "2-ADDITIONAL";
									$ref_line = $seq_basic[$model_calculate];
									if (isset($end_date_add[$model_calculate]) && isset($start_date_add[$model_calculate]) && $current_start_date <= $end_date_add[$model_calculate] && $current_start_date >= $start_date_add[$model_calculate]){
										$priority = "2-ADDITIONAL2";
										$cost_sellin = $ref_and_costprom[$model_calculate]["cost_prom"];
										$ref_line = $ref_and_costprom[$model_calculate]["seq"];
									}
									
									$ref_and_costprom[$model_calculate] = ["cost_prom" => $cost_prom, "seq" => $row[0]];
									
									$new_sheet->setCellValue('AE' . ($index + 1), $priority);
									
									
									
									$new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
									
									$start_date_add[$model_calculate] = $current_start_date;
									$end_date_add[$model_calculate] = $current_end_date;
									
									$flag_ocurrence = 0;
								}
								
								
	
							}
							
							elseif(isset($star_date_basic[$model_calculate]) && isset($end_date_basic[$model_calculate]) && isset($start_date_pre_basic[$model_calculate]) && isset($end_date_pre_basic[$model_calculate]) && $current_start_date <= $star_date_basic[$model_calculate] && $current_start_date <= $end_date_basic[$model_calculate] && $current_end_date >= $star_date_basic[$model_calculate]){
								
	
							// Convertir las fechas al formato 'YYYY-MM-DD' para facilitar el manejo
							// $startDateObj = DateTime::createFromFormat('Ymd', $startDate);
							// $endDateObj = DateTime::createFromFormat('Ymd', $endDate);

							// if (!$startDateObj || !$endDateObj) {
								// return "Error: Formato de fecha incorrecto.";
							// }
								$current_start_date = DateTime::createFromFormat('Ymd', $start_date);
								$current_end_date = DateTime::createFromFormat('Ymd', $end_date);

								$ultimoDia = (int) $current_start_date->format('t');
								$diaInicio = (int) $current_start_date->format('d');
								$diaFin = (int) $current_end_date->format('d');
								$mesInicio = $current_start_date->format('m');
								$anioInicio = $current_start_date->format('Y');
								$mesFin = $current_end_date->format('m');
								$anioFin = $current_end_date->format('Y');

								$esRangoCompleto = ($diaInicio === 1 && $diaFin === $ultimoDia && $mesInicio === $mesFin && $anioInicio === $anioFin);


								if ($esRangoCompleto){
									$priority = "BUNDLE";
									$new_sheet->setCellValue('AT' . ($index + 1), '1-BASIC');
									$ref_line = '';
								} else{
									$priority = "3-MIX";
									$ref_line = $seq_basic_mix[$model_calculate][0];
								}
								
								$new_sheet->setCellValue('AE' . ($index + 1), $priority);
								$cost_sellin = 0;
								
								$new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
							}
							elseif($flag_ocurrence == 0){								
								// Convertir las fechas a objetos DateTime para facilitar la manipulación
								$end_date_pre = new DateTime($end_date_conv);
								$start_date_pre = new DateTime($start_date_conv);

								// Obtener el último día del mes de $end_date_conv
								$last_day_of_month = $end_date_pre->format('Y-m-t');

								// Verificar si $end_date_conv es el último día del mes
								if ($end_date_conv === $last_day_of_month) {
									// Calcular la fecha de inicio del mes
									$first_day_of_month = new DateTime($end_date_pre->format('Y-m-01'));

									// Calcular la fecha de fin del primer rango (el día anterior a $start_date_conv)
									$first_range_end = (new DateTime($start_date_conv))->modify('-1 day');

									// Guardar el primer rango
									$start_date_pre_basic[$model_calculate] = $first_day_of_month->format('Y-m-d');
									$end_date_pre_basic[$model_calculate] = $first_range_end->format('Y-m-d');
									
									$star_date_basic[$model_calculate] = $start_date_conv;
									$end_date_basic[$model_calculate] = $end_date_conv;
								} else {
									$star_date_basic[$model_calculate] = $start_date_conv;
									$end_date_basic[$model_calculate] = $end_date_conv;
									// Si $end_date_conv no es el último día del mes, simplemente guardamos el rango original
									//$start_date_basic[$model_calculate] = $start_date_pre->format('Y-m-d');
									//$end_date_basic[$model_calculate] = $end_date_pre->format('Y-m-d');
								}
								
								$seq_basic[$model_calculate] = $row[0];
								$seq_basic_mix[$model_calculate][] = $row[0];
								$cost_row_13[$model_calculate] = $cost_prom;
								$priority = "1-BASIC";
								$cost_sellin = 0;
								$new_sheet->setCellValue('AE' . ($index + 1), $priority);
							}
							// if (){
								
							// } else $cost_row_13[$model_calculate] = $cost_prom;
							/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							// $basic_ranges[$model_calculate] = [];
							// $additional_counts[$model_calculate] = [];
							// $basic_found_count[$model_calculate] = 0;
							// $cost_sellin = 0;
							
							// //foreach (...) { // El foreach más grande que itera sobre los datos

								// //$start_date_conv = ...;
								// //$end_date_conv = ...;
								// //$current_start_date = $start_date_conv;
								// //$current_end_date = $end_date_conv;

								// // 1. Detectar las dos BASIC
								// if (!isset($basic_ranges[$model_calculate]) || count($basic_ranges[$model_calculate]) < 2) {
									// $es_primera_basic = (count($basic_ranges[$model_calculate]) == 0);
									// $es_segunda_basic = (count($basic_ranges[$model_calculate]) == 1);

									// if ($es_primera_basic) {
										// $basic_ranges[$model_calculate][] = ['start' => $start_date_conv, 'end' => $end_date_conv];
										// $priority = "1-BASIC";
										// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
										// $seq_basic[$model_calculate] = $row[0];
										// $ref_line = $seq_basic[$model_calculate];
										// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
										// $cost_sellin = 0;
										// $seq_basic_mix[$model_calculate][] = $row[0]; // Añadido aquí
										// $cost_row_13[$model_calculate] = $cost_prom;
										// $basic_found_count[$model_calculate]++;
										// continue; // Ir a la siguiente iteración
									// } elseif ($es_segunda_basic) {
										// $primer_basic_start = new DateTime($basic_ranges[$model_calculate][0]['start']);
										// $primer_basic_end = new DateTime($basic_ranges[$model_calculate][0]['end']);
										// $mes = $primer_basic_start->format('m');
										// $anio = $primer_basic_start->format('Y');
										// $ultimo_dia = (new DateTime("$anio-$mes-01"))->format('t');
										// $posible_segunda_basic_start = (new DateTime($primer_basic_end))->modify('+1 day')->format('Y-m-d');
										// $posible_segunda_basic_end_mes = (new DateTime("$anio-$mes-$ultimo_dia"))->format('Y-m-d');
										// $posible_segunda_basic_start_mes_inicio = (new DateTime("$anio-$mes-01"))->format('Y-m-d');
										// $posible_segunda_basic_end_primer_basic = (new DateTime($primer_basic_start))->modify('-1 day')->format('Y-m-d');

										// $current_start_dt = new DateTime($start_date_conv);
										// $current_end_dt = new DateTime($end_date_conv);

										// $matches_complementary_forward = ($start_date_conv === $posible_segunda_basic_start && $end_date_conv === $posible_segunda_basic_end_mes);
										// $matches_complementary_backward = ($end_date_conv === $posible_segunda_basic_end_primer_basic && $start_date_conv === $posible_segunda_basic_start_mes_inicio);

										// if ($matches_complementary_forward || $matches_complementary_backward) {
											// $basic_ranges[$model_calculate][] = ['start' => $start_date_conv, 'end' => $end_date_conv];
											// $priority = "1-BASIC";
											// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
											// $seq_basic[$model_calculate] = $row[0];
											// $ref_line = $seq_basic[$model_calculate];
											// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
											// $cost_sellin = 0;
											// $seq_basic_mix[$model_calculate][] = $row[0]; // Añadido aquí
											// $cost_row_13[$model_calculate] = $cost_prom;
											// $basic_found_count[$model_calculate]++;
											// continue; // Ir a la siguiente iteración
										// }
									// }
								// }

								// // 2. Identificar los ADDITIONAL
								// if (isset($basic_ranges[$model_calculate]) && count($basic_ranges[$model_calculate]) == 2) {
									// foreach ($basic_ranges[$model_calculate] as $basic_index => $basic_range) {
										// if ($start_date_conv === $basic_range['start'] && $end_date_conv === $basic_range['end']) {
											// $additional_counts[$model_calculate][$basic_index] = ($additional_counts[$model_calculate][$basic_index] ?? 0) + 1;
											// $priority = "2-ADDITIONAL" . ($additional_counts[$model_calculate][$basic_index] > 1 ? $additional_counts[$model_calculate][$basic_index] : '');
											// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
											// $ref_line = $seq_basic[$model_calculate];
											// $cost_sellin = isset($cost_row_13[$model_calculate]) ? $cost_row_13[$model_calculate] : 0;
											// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
											// continue; // Ir a la siguiente iteración
										// }
									// }
								// }

								// // 3. Identificar los BUNDLE
								// $start_dt = new DateTime($start_date_conv);
								// $end_dt = new DateTime($end_date_conv);
								// $primer_dia = (int) $start_dt->format('d');
								// $ultimo_dia_mes = (int) $start_dt->format('t');
								// $mes_inicio = $start_dt->format('m');
								// $anio_inicio = $start_dt->format('Y');
								// $mes_fin = $end_dt->format('m');
								// $anio_fin = $end_dt->format('Y');

								// if ($primer_dia === 1 && (int) $end_dt->format('d') === $ultimo_dia_mes && $mes_inicio === $mes_fin && $anio_inicio === $anio_fin) {
									// $priority = "BUNDLE";
									// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
									// $ref_line = '';
									// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
									// $cost_sellin = 0;
									// continue; // Ir a la siguiente iteración
								// }

								// // 4. Identificar los MIX
								// if (isset($basic_ranges[$model_calculate]) && count($basic_ranges[$model_calculate]) == 2) {
									// $basic_start_1_dt = new DateTime($basic_ranges[$model_calculate][0]['start']);
									// $basic_end_1_dt = new DateTime($basic_ranges[$model_calculate][0]['end']);
									// $basic_start_2_dt = new DateTime($basic_ranges[$model_calculate][1]['start']);
									// $basic_end_2_dt = new DateTime($basic_ranges[$model_calculate][1]['end']);
									// $current_start_dt = new DateTime($start_date_conv);
									// $current_end_dt = new DateTime($end_date_conv);

									// $start_in_basic1 = ($current_start_dt >= $basic_start_1_dt && $current_start_dt <= $basic_end_1_dt);
									// $end_in_basic2 = ($current_end_dt >= $basic_start_2_dt && $current_end_dt <= $basic_end_2_dt);
									// $start_in_basic2 = ($current_start_dt >= $basic_start_2_dt && $current_start_dt <= $basic_end_2_dt);
									// $end_in_basic1 = ($current_end_dt >= $basic_start_1_dt && $current_end_dt <= $basic_end_1_dt);

									// if (($start_in_basic1 && $end_in_basic2) || ($start_in_basic2 && $end_in_basic1)) {
										// $priority = "3-MIX";
										// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
										// $ref_line = $seq_basic_mix[$model_calculate][0] ?? ''; // Asegurarse de que el array no esté vacío
										// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
										// $cost_sellin = 0;
										// continue; // Ir a la siguiente iteración
									// }
								// }

								// // Si no coincide con ningún caso anterior, podría ser la configuración inicial de las BASIC
								// if (!isset($basic_ranges[$model_calculate]) || count($basic_ranges[$model_calculate]) < 2) {
									// // Lógica para la primera y segunda BASIC (si no se cumple la detección temprana)
									// if (!isset($basic_ranges[$model_calculate]) || count($basic_ranges[$model_calculate]) == 0) {
										// $basic_ranges[$model_calculate][] = ['start' => $start_date_conv, 'end' => $end_date_conv];
										// $priority = "1-BASIC";
										// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
										// $seq_basic[$model_calculate] = $row[0];
										// $ref_line = $seq_basic[$model_calculate];
										// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
										// $cost_sellin = 0;
										// $seq_basic_mix[$model_calculate][] = $row[0]; // Añadido aquí
										// $cost_row_13[$model_calculate] = $cost_prom;
										// $basic_found_count[$model_calculate]++;
									// } elseif (count($basic_ranges[$model_calculate]) == 1 && $basic_found_count[$model_calculate] < 2) {
										// // Lógica para la segunda BASIC (similar a la detección temprana)
										// $primer_basic_start = new DateTime($basic_ranges[$model_calculate][0]['start']);
										// $primer_basic_end = new DateTime($basic_ranges[$model_calculate][0]['end']);
										// // ... (cálculo de la segunda BASIC como antes) ...
										// if ($matches_complementary_forward || $matches_complementary_backward) {
											// $basic_ranges[$model_calculate][] = ['start' => $start_date_conv, 'end' => $end_date_conv];
											// $priority = "1-BASIC";
											// $new_sheet->setCellValue('AE' . ($index + 1), $priority);
											// $seq_basic[$model_calculate] = $row[0];
											// $ref_line = $seq_basic[$model_calculate];
											// $new_sheet->setCellValue('AF' . ($index + 1), $ref_line);
											// $cost_sellin = 0;
											// $seq_basic_mix[$model_calculate][] = $row[0]; // Añadido aquí
											// $cost_row_13[$model_calculate] = $cost_prom;
											// $basic_found_count[$model_calculate]++;
										// }
									// }
								// }
								// $seq_basic_mix[$model_calculate][] = $row[0];
								// $cost_row_13[$model_calculate] = $cost_prom;
							//}

							$previous_start_dates[$model_calculate] = $current_start_date; // Actualizar previous_start_date para el modelo actual
							$previous_end_dates[$model_calculate] = $current_end_date;
							$previous_seq[$model_calculate] = $row[0];
							$previous_row_13[$model_calculate] = $cost_prom; // Actualizar row[13] anterior para el modelo actual
	
                            $new_sheet->setCellValue('K' . ($index + 1), $cost_sellin); // Llenar columna K
							// Formato numérico para la columna K
							$current_row = $index + 1;
							
							// $formula_column_o = "=ROUND(IF(K{$current_row}-N{$current_row}<0,0,K{$current_row}-N{$current_row}),2)";
							// $new_sheet->setCellValue('O' . ($index + 1), $formula_column_o); // Llenar columna O
							// if((float)$cost_sellin != 0){
								// // if(!is_numeric($row[13])){
									// $row[13] = str_replace(',', '', $row[13]);
								// // }
								// $different = (float)$cost_sellin - (float)$row[13];								
								// $different_code = "=K{$current_row} - N{$current_row}";
								// $new_sheet->setCellValue('O' . ($index + 1), $different_code); // Llenar columna O
							// }

							//Calculo de unidades
							$qty = $this->qty_calculate($out_map, $model_calculate, $start_date, $end_date, 0, $price_promotion);
							if($priority === 'BUNDLE'){
								$new_sheet->setCellValue('AV' . ($index + 1), $qty); // Llenar columna AV
								//$qty = 0;
								$new_sheet->setCellValue('P' . ($index + 1), 0); // Llenar columna P
							}
							else{
								$new_sheet->setCellValue('AV' . ($index + 1), ''); // Llenar columna AV
								$new_sheet->setCellValue('P' . ($index + 1), $qty); // Llenar columna P
							}
							//$new_sheet->setCellValue('N' . ($index + 1), round($row[13],2)); // Llenar columna N

							// $porcent = $row[12];
							// $porcent_ns = str_replace("%", "", $porcent);
							// $porcent_num = floatval($porcent_ns) / 100;
							// //echo $porcentaje_num; // Imprime 0.15
							// $new_sheet->setCellValue('N' . ($index + 1), number_format(($row[11]/1.18)*(1-$porcent_num), 2)); // Llenar columna N
							//$new_sheet->getStyle('N' . ($index + 1))->getNumberFormat()->setFormatCode('0.00'); // Formato de 2 decimales
							
							// Calculo Monto Total
							// $formula_monto_total = "=P{$current_row}*O{$current_row}";
							// $new_sheet->setCellValue('V' . ($index + 1), $formula_monto_total); // Llenar columna V
							
							// Calculo NOTE
							if($qty == 0){
								if(!empty($out_map[$model_calculate])){
									$new_sheet->setCellValue('Y' . ($index + 1), 'COST UPDATED BY STOCK'); // Llenar columna Y
								}
								else $new_sheet->setCellValue('Y' . ($index + 1), 'COST UPDATED BY LASTEST SELLIN'); // Llenar columna Y
								
							}
							else $new_sheet->setCellValue('Y' . ($index + 1), ''); // Llenar columna Y
							
							// Calculo FCST UNIT AMT DIFF
							// $formula_fcst_unit_amt_diff = "=O{$current_row}-AY{$current_row}";
							// $new_sheet->setCellValue('Z' . ($index + 1), $formula_fcst_unit_amt_diff); // Llenar columna Z
							
							// Calculo FCST QTY DIFF
							$formula_fcst_qty_diff = "=P{$current_row}-AZ{$current_row}";
							$new_sheet->setCellValue('AA' . ($index + 1), $formula_fcst_qty_diff); // Llenar columna AA
							
							// Calculo FCST AMT DIFF
							// $formula_fcst_amt_diff = "=V{$current_row}-BA{$current_row}";
							// $new_sheet->setCellValue('AB' . ($index + 1), $formula_fcst_amt_diff); // Llenar columna AB
							
							
							// Calculo columna AG - Stock
							$first_stock = 0;
							$count_stock = 0;
							$fechas = [];
							
							// if (!empty($out_map[$model_calculate])) {
								// foreach ($out_map[$model_calculate] as $i => $item) {
									// $flag_stock = 0;
									 
									// if ($item[0] >= $start_date_conv && $item[0] <= $end_date_conv) {
										// $first_stock = $item[2];
										// $flag_stock = 1;

										// $txn_init_start = $start_date_conv;
										// $txn_init = $item[0];
										// break;

									// }
									// else {
										// $flag_stock = 0;
										// $stock_ag = 0;
										
									// }
								// }
								
								// //if ($txn_init !== $txn_init_start){
									// if($flag_stock == 1){
										// foreach ($out_map[$model_calculate] as $item) $fechas[] = [$item[0], $item[2], $item[1], $item[3]]; // TXN_DATE, stock, sell_unit, target1_flag
										// //echo '<pre>'; print_r($fechas); return
										// //print_r($fechas); return;
										// $sum_sell_unit_up = 0;
										// $sum_sell_unit_down = 0;
										// $indice_encontrado_up_down = 0;
										// $indice_encontrado_down_up = 0;
										// $stock_ag_up = 0;
										// $stock_ag_down = 0;
										// // Encontrar indice
										// for ($i = 0; $i < count($fechas); $i++) {
											// if ($fechas[$i][0] === $txn_init) {
												// //$sum_sell_unit += $item[1];
												// $indice_encontrado = $i;
												// //$current_txn_date = $fechas[$i][0];
												// break; // Detenemos el bucle una vez que encontramos la fecha
											// }
										// }	
										
										// //if($txn_init !== $txn_init_start){
											// // Stock arriba hacia abajo
										// $flag_not_found = 0;
										// for ($i = $indice_encontrado; $i >= 0; $i--){
											// // if($i != $indice_encontrado){											
												// // $sum_sell_unit_up = $sum_sell_unit_up + $fechas[$i][2];
											// // }
											// //print_r($sum_sell_unit); return;
											// if(!empty($fechas[$i][1]) && $txn_init !== $fechas[$i][0]){
												// $txn_date_up = $fechas[$i][0];
												// $stock_current_up = $fechas[$i][1];
												// $indice_up = $i;
												// //$stock_ag_up = $fechas[$i][1] - $sum_sell_unit_up;
												// break;
											// }
											// elseif ($i == 0 && empty($fechas[$i][1])){
												// $flag_not_found = 1;
											// }
										// }
										
										// for($i = $indice_up; $i <= $indice_encontrado; $i++){
											// if($txn_init === $fechas[$i][0]){
												// //$txn_date_up = $fechas[$i][0];
												// //$stock_current_up = $fechas[$i][1];
												// //$indice_up = $i;
												
												// $stock_ag_up = $stock_current_up - $sum_sell_unit_up;
												// break;
											// }
										
											// //if($fechas[$i][0] !== $txn_init && $txn_date_up !== $fechas[$i][0] && $fechas[$i][3] !== 'SELLOUT BEFORE PROMO'){
											// if($fechas[$i][0] !== $txn_init && $txn_date_up !== $fechas[$i][0]){
												// //$stock_ag_up = $fechas[$i][1] - $sum_sell_unit_up;
												// $sum_sell_unit_up = $sum_sell_unit_up + $fechas[$i][2];
											// }
										// }	
										// // Stock abajo hacia arriba
										// for ($i = $indice_encontrado; $i < count($fechas); $i++) {
											// // if($i != $indice_encontrado){											
												
											// // }
											// $indice_encontrado_down_up = $indice_encontrado_down_up + $fechas[$i][2];
											// //print_r($sum_sell_unit); return;
											// if(!empty($fechas[$i][1])){
												// $stock_current_down = $fechas[$i][1];
												// $txn_date_down = $fechas[$i][0];
												// $stock_ag_down = $fechas[$i][1] + $indice_encontrado_down_up;
												// break;
											// }
											// else $stock_ag_down = 0;
										// }
										// // if($stock_ag_up <=  $stock_ag_down ){
											// // $stock_ag = $stock_ag_down;
										// // } else $stock_ag = $stock_ag_up;
										
										// // if($stock_current_up <  $stock_current_down){
											// // $stock_ag = $stock_ag_down;
										// // } else $stock_ag = $stock_ag_up;
										
										// // Convertir las cadenas de fecha a objetos DateTime
										// $txn_date_up = new DateTime($txn_date_up);
										// $txn_init = new DateTime($txn_init);
										// $txn_date_down = new DateTime($txn_date_down);

										// // Calcular la diferencia en días
										// $diff_up_init = $txn_init->diff($txn_date_up)->days;
										// $diff_init_down = $txn_date_down->diff($txn_init)->days;

										// // Realizar la validación comparando el número de días de diferencia
										// if ($diff_up_init <= $diff_init_down) {
											// $stock_ag = $stock_ag_up;
										// }elseif ($diff_up_init > $diff_init_down && $stock_ag_up != 0) {
											// $stock_ag = $stock_ag_down;
										// }elseif ($diff_up_init > $diff_init_down && $stock_ag_up == 0) {
											// $stock_ag = $stock_ag_up;
										// }
										
										// if ($flag_not_found == 1){
											// $stock_ag = 0;
										// }
									// // }
									// // else $stock_ag = $first_stock;
									
								// }
							// } else {
								// $stock_ag = 0; // Usar 0
							// }
							
							$stock_ag = $this->calcularStockAg($out_map, $model_calculate, $start_date_conv, $end_date_conv);
							$new_sheet->setCellValue('AG' . ($index + 1), $stock_ag); // Llenar columna AG
							
							$first_stock = 0;
							$count_stock = 0;
							$fechas = [];
							$stock_ag_bundle = 0;
							$qty_bundle = 0;
							//$model_bundle_calculate = $model_bundle[$model_calculate][$index_model_ocurrence[$model_calculate]];
							//if(!empty($model_bundle_calculate) && $model_bundle_calculate != 0){
							if(isset($out_map[$model_bundle])){	
								$qty_bundle = $this->qty_calculate($out_map, $model_bundle, $start_date, $end_date, 1, $price_promotion);
								foreach ($out_map[$model_bundle] as $i => $item) {
									$flag_stock = 0;
									if ($item[0] >= $start_date_conv && $item[0] <= $end_date_conv) {
										$first_stock = $item[2];
										//if($first_stock == 0){
											$flag_stock = 1;
											// if($item[0] === $start_date_conv){
												// $txn_init_start = $item[0];
												// $txn_init = $item[0];
											// }
											// else{
												// $txn_init_start = $start_date_conv;
												// $txn_init = $item[0];
											// }
											$txn_init_start = $start_date_conv;
											$txn_init = $item[0];
											break;
										//}
										// else{
											// if(!empty($item[2])){
												// $stock_ag = $item[2]; 
												// //$count_stock += 1;
												// break;
											// }
										// }
									}
									else {
										$flag_stock = 0;
										$stock_ag_bundle = '';
										
									}
								}
								if($flag_stock == 1){
									$indice_up = 0;
									$txn_date_up = 0;
									$stock_current_up = 0;
									foreach ($out_map[$model_bundle] as $item) $fechas[] = [$item[0], $item[2], $item[1], $item[3]]; // TXN_DATE, stock, sell_unit, target1_flag
									//echo '<pre>'; print_r($fechas); return
									//print_r($fechas); return;
									$sum_sell_unit_up = 0;
									$sum_sell_unit_down = 0;
									$indice_encontrado_up_down = 0;
									$indice_encontrado_down_up = 0;
									$stock_ag_up = 0;
									$stock_ag_down = 0;
									// Encontrar indice
									for ($i = 0; $i < count($fechas); $i++) {
										if ($fechas[$i][0] === $txn_init) {
											//$sum_sell_unit += $item[1];
											$indice_encontrado = $i;
											break; // Detenemos el bucle una vez que encontramos la fecha
										}
									}	
									
									//if($txn_init !== $txn_init_start){
										// Stock arriba hacia abajo
									$flag_not_found = 0;
									for ($i = $indice_encontrado; $i >= 0; $i--){
										// if($i != $indice_encontrado){											
											// $sum_sell_unit_up = $sum_sell_unit_up + $fechas[$i][2];
										// }
										//print_r($sum_sell_unit); return;
										if(!empty($fechas[$i][1]) && $txn_init !== $fechas[$i][0]){
											$txn_date_up = $fechas[$i][0];
											$stock_current_up = $fechas[$i][1];
											$indice_up = $i;
											//$stock_ag_up = $fechas[$i][1] - $sum_sell_unit_up;
											break;
										}
										elseif ($i == 0 && empty($fechas[$i][1])){
											$flag_not_found = 1;
										}
									}
									
									for($i = $indice_up; $i <= $indice_encontrado; $i++){
										if($txn_init === $fechas[$i][0]){
											//$txn_date_up = $fechas[$i][0];
											//$stock_current_up = $fechas[$i][1];
											//$indice_up = $i;
											$stock_ag_up = $stock_current_up - $sum_sell_unit_up;
											break;
										}
									
										//if($fechas[$i][0] !== $txn_init && $txn_date_up !== $fechas[$i][0] && $fechas[$i][3] !== 'SELLOUT BEFORE PROMO'){
										if($fechas[$i][0] !== $txn_init && $txn_date_up !== $fechas[$i][0]){
											//$stock_ag_up = $fechas[$i][1] - $sum_sell_unit_up;
											$sum_sell_unit_up = $sum_sell_unit_up + $fechas[$i][2];
										}
									}	
									// Stock abajo hacia arriba
									for ($i = $indice_encontrado; $i < count($fechas); $i++) {
										// if($i != $indice_encontrado){											
											
										// }
										$indice_encontrado_down_up = $indice_encontrado_down_up + $fechas[$i][2];
										//print_r($sum_sell_unit); return;
										if(!empty($fechas[$i][1])){
											$stock_current_down = $fechas[$i][1];
											$txn_date_down = $fechas[$i][0];
											$stock_ag_down = $fechas[$i][1] + $indice_encontrado_down_up;
											break;
										}
										else $stock_ag_down = 0;
									}
									if($stock_ag_up <=  $stock_ag_down){
										$stock_ag_bundle = $stock_ag_down;
									} else $stock_ag_bundle = $stock_ag_up;
									
									// if($stock_current_up <=  $stock_current_down){
										// $stock_ag = $stock_ag_down;
									// } else $stock_ag = $stock_ag_up;
									
									if ($flag_not_found == 1){
										$stock_ag_bundle = '';
									}
									
								}							
							}
							$new_sheet->setCellValue('AU' . ($index + 1), $stock_ag_bundle); // Llenar columna AU
							//$qty_bundle = $this->qty_calculate($out_map, $model_calculate, $start_date, $end_date, 1);
							//$new_sheet->setCellValue('AI' . ($index + 1), $qty_sellout);
							// Rellenado columna AH
							
							//$sellin_ext = [];
							//foreach($sell_map[$model_calculate] as $i=>$item) $sellin_ext[] = [$item[3], $item[4]];
							$sales_order_qty = 0;
							if (!empty($sell_map[$model_calculate])) {
								foreach($sell_map[$model_calculate] as $i=>$item){
									if($item[4]<=$end_date_conv && $item[4]>=$start_date_conv){
										$sales_order_qty += $item[3];
									};									
								}
							} else $sales_order_qty = 0;
							
							$new_sheet->setCellValue('AH' . ($index + 1), $sales_order_qty);
							//print_r($sellin_ext); return;
							//$sales_yyyymmdd = $sell_map[$model_calculate];
							//$start_date_conv = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
							//$end_date_conv = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
							//$sell_map[$model_calculate]
							
							// Rellenado columna AI
							//$formula_ai = "=P{$current_row}";
							$qty_sellout = $this->qty_calculate($out_map, $model_calculate, $start_date, $end_date, 1, $price_promotion);
							$new_sheet->setCellValue('AI' . ($index + 1), $qty_sellout);
							
							// Rellenado columna AJ
							$return_qty = 0;
							if(!empty($out_map[$model_calculate])){
								foreach($out_map[$model_calculate] as $item){
									if ($item[0] >= $start_date_conv && $item[0] <= $end_date_conv) {
										//print_r($item); echo '<br>'; return;
										if($item[1] < 0){
											$return_qty = $return_qty + $item[1];
										}
									}
								}
							}
							$new_sheet->setCellValue('AJ' . ($index + 1), $return_qty);
							
							// Rellenado columna AK
							$promo_applie_qty = 0;
							if (!empty($out_map[$model_calculate])) {
								foreach($out_map[$model_calculate] as $i=>$item){
									if($item[0]<=$end_date_conv && $item[0]>=$start_date_conv){
										if($item[3] === 'PROMO APPLIED' || $item[3] === 'PROMO APPLIED WEB'){
											$promo_applie_qty = $promo_applie_qty + $item[1];
										}
									}
								}
							}
							else  $promo_applie_qty = 0;
							
							if ($model_bundle !== NULL){
								$new_sheet->setCellValue('AK' . ($index + 1), 0);
								$new_sheet->setCellValue('AO' . ($index + 1), 0);
								
							} else {
								$new_sheet->setCellValue('AK' . ($index + 1), $promo_applie_qty);
								
								// Rellenado columna AO
								$control_not_applied = "=AI{$current_row}-P{$current_row}";
								$new_sheet->setCellValue('AO' . ($index + 1), $control_not_applied);
							}
							
							// Rellenado columna AN
							// $control_over_stock = $qty - $promo_applie_qty;
							// $new_sheet->setCellValue('AN' . ($index + 1), $control_over_stock);
							
							
							
							
							$vars_global[] = ['start_date' => $start_date_conv, 'end_date' => $end_date_conv, 'model' => $model_calculate, 'stock' => $stock_ag, 'qty' => $qty, 'priority' => $priority, 'seq' => $row[0], 'cost_sellin' => $cost_sellin];
							
							// $vars_mix[] = ['model' => $model_calculate, 'seq' => $row[0], 'start_date' => $start_date_conv, 'end_date' => $end_date_conv, 'stock' => $stock_ag, 'qty' => $qty, 'priority' => $priority, 'cost_sellin' => $cost_sellin];
							
							if (!empty($model_bundle)){
								$vars_bundle[] = ['start_date' => $start_date_conv, 'end_date' => $end_date_conv, 'model' => $model_bundle, 'stock' => $stock_ag_bundle, 'qty' => $qty_bundle, 'priority' => $priority, 'seq' => $row[0], 'cost_sellin' => $cost_sellin];
							}
							
							// Aplicar estilo a las columnas O, P, T, U y V
							$columns_black = ['O', 'P', 'T', 'U', 'V', 'W', 'X'];
							foreach ($columns_black as $column) {
								$cell = $new_sheet->getCell($column . ($index + 1));
								$style = $cell->getStyle();

								// Color de fondo negro
								$style->getFill()
									->setFillType(Fill::FILL_SOLID)
									->getStartColor()
									->setARGB(Color::COLOR_BLACK);

								
								// Color de fuente blanco y negrita
								$font = $style->getFont();
								$font->setColor(new Color(Color::COLOR_WHITE));
								$font->setBold(true); // Agregar esta línea para activar la negrita								
							}
							$columns_yellow = ['I', 'L', 'M', 'Q', 'AC', 'AD', 'AL', 'AM', 'AW'];
							foreach ($columns_yellow as $column) {
								$cell = $new_sheet->getCell($column . ($index + 1));
								$style = $cell->getStyle();

								// Color de fondo negro
								$style->getFill()
									->setFillType(Fill::FILL_SOLID)
									->getStartColor()
									->setRGB('FFFF99'); // RGB in hexadecimal: FF=255, 99=153											
							}
							// Aplicar borde a todas las columnas
							$max_column = count($row); // Obtener el número de columnas en la fila
							for ($col_index = 0; $col_index < $max_column; $col_index++) {
								$column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index + 1); // Obtener la letra de la columna
								$cell = $new_sheet->getCell($column . ($index + 1));
								$style = $cell->getStyle();

								// Bordes outline con color RGB (204, 204, 255)
								$style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('ccccccff');
							}
                        }
						//$index_model_ocurrence[$model_calculate]++;
                    }
					//echo '<pre>'; print_r($model_bundle); return;
                }
				
				elseif ($sheet_name === 'SELLOUT'){
					$columns = ['customer', 'acct_gtm', 'customer_model', 'model_code', 'txn_date', 'cust_store_code', 'cust_store_name', 'sellout_unit', 'sellout_amt', 'stock', 'ticket', 'promo1_price', 'target1_flag','promo2_price', 'target2_flag', 'promo3_price', 'target3_flag', 'promo4_price', 'target4_flag', 'promo5_price', 'target5_flag', 'promo6_price', 'target6_flag', 'promo7_price', 'target7_flag', 'promo8_price', 'target8_flag', 'promo9_price', 'target9_flag', 'promo10_price', 'target10_flag'];
					 $column_letter = [];
					 
					for ($i = 0; $i < count($columns); $i++) {
						$letter = '';
						$num = $i;
						while ($num >= 0) {
							$letter = chr(ord('A') + ($num % 26)) . $letter;
							$num = intval($num / 26) - 1;
						}
						$column_letter[] = $letter;
					}

					// Escribir los datos de cada fila desde $sell_out_all_data
					foreach ($sell_out_all_data as $index => $row) {
						foreach ($columns as $col_index => $col_name) {
							$value = isset($row->$col_name) ? $row->$col_name : '';

							// Formatear la columna txn_date
							if ($col_name === 'txn_date' && !empty($value)) {
								$date = new DateTime($value);
								$value = $date->format('Ymd');
							}

							$new_sheet->setCellValue($column_letter[$col_index] . ($index + 2), $value);
						}
					}

				}
				
				elseif ($sheet_name === 'CLIENTE' && $is_hiraoka === true){
					//echo '<pre>'; print_r($vars_global);
					foreach ($data as $index => $row) {
						
						if ($index > 1) {
							$start_date = $row[0];
							$end_date = $row[1];
							$start_date_conv = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
							$end_date_conv = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
							$parts = explode('-', $row[3], 2);
							$second_group = trim($parts[1]); 
							$model = str_replace(['-', ' '], '', $second_group);
							
							$qty = $row[8];
							
							//$model_hiraoka[$model][] = ["qty" => $qty, "start_date" => $start_date_conv, "end_date" => $end_date_conv];
							
							foreach ($vars_global as &$item){
								//$item['hiraoka'] = 0;
								if (strpos($item['model'], $model) !== false){
									if ($item['start_date'] === $start_date_conv && $item['end_date'] === $end_date_conv){
										$item['qty'] = $qty;
										$item['hiraoka'] = 1;
									} 
									
								}
								else continue;
							}
							//$vars_global[] = ['start_date' => $start_date_conv, 'end_date' => $end_date_conv, 'model' => $model_calculate, 'stock' => $stock_ag, 'qty' => $qty, 'priority' => $priority, 'seq' => $row[0], 'cost_sellin' => $cost_sellin];
						}
						
					}
					//echo '<pre>'; print_r($vars_global);
					
				}
				
				//elseif ($sheet_name === 'SELLIN') {
					//echo '<pre>'; print_r($vars_global);
					// $sell_in = [];
					// $model = [];
					// $count_temp = 0;
					// $model_bundle_pre = [];
					// if (!empty($vars_bundle)){
						// foreach($vars_bundle as $item){
							
							// if(in_array($item['model'], $model_bundle_pre)){
								// continue;
							// }
							// else{
								// $vars_global[] = $item;
								// $model_bundle_pre[] = $item['model'];
							// }
						// }
					// }
					// foreach ($data as $index => &$row) {
						// $count_temp = $count_temp + 1;						
                        // if ($index > 0) { // Ignorar cabeceras
							// $row[56] = $count_temp;
							// $sell_in[$row[3]][] = $row;
							
						// }
						
					// }
					// //$model = array_values(array_unique($model_global));

					// //echo '<pre>'; print_r($model); return;
					// // Ordenar cada modelo por [4] (letra) y luego por [5] (fecha), ambos en orden descendente
					// foreach ($sell_in as $model => &$entries) {
						// usort($entries, function ($a, $b) {
							// // Comparar por columna [4] (Z -> A)
							// $col4Comparison = strcmp($b[4], $a[4]);
							// if ($col4Comparison !== 0) {
								// return $col4Comparison;
							// }
							// // Si las letras son iguales, comparar por fecha [5] (Z -> A)
							// return strcmp($b[5], $a[5]);
						// });
					// }
					// unset($entries); // Evita referencias no deseadas en PHP
					
					// //echo '<pre>'; print_r($vars_global);
					// $column_indices_stock = [16, 20, 24, 28, 32, 36, 40, 44, 48, 52];
					// $column_indices_qty = [15, 19, 23, 27, 31, 35, 39, 43, 47, 51];
					// $column_indices_avg = [14, 18, 22, 26, 30, 34, 38, 42, 46, 50];
					// $column_indices_seq = [13, 17, 21, 25, 29, 33, 37, 41, 45, 49];
					// $processed_models_stock = [];
					// $processed_models_qty = [];
					// $processed_models_avg = [];
					// $processed_models_seq = [];
					
					
					// foreach ($vars_global as $vars_global_item) {
						// $current_model = $vars_global_item['model'];
						// $priority = $vars_global_item['priority'];
						// $qty_to_fill = $vars_global_item['qty'];
						// $start_date_str = $vars_global_item['start_date'];
						// $end_date_str = $vars_global_item['end_date'];
						// $seq = $vars_global_item['seq'];

						// if ($priority === '1-BASIC' || $priority === '3-MIX' || $priority === 'BUNDLE') {
							// $stock_to_achieve = $vars_global_item['stock'];
							// $stock_accumulated = 0;
							// $last_processed_index = -1;
							// $start_index = 0;
							// $is_first_occurrence = !isset($processed_models_stock[$current_model]);
							// $first_valid_index_for_calculation = -1;
							// $is_first_occurrence_seq = !isset($processed_models_seq[$current_model]);

							// // --- Determinar el índice de inicio ---
							// if ($is_first_occurrence && isset($sell_in[$current_model])) {
								// $start_date = str_replace('-', '', $start_date_str);
								// $end_date = str_replace('-', '', $end_date_str);

								// foreach ($sell_in[$current_model] as $index => $item) {
									// $sell_in_date = $item[4];
									// if ($sell_in_date >= $start_date && $sell_in_date <= $end_date || $sell_in_date > $end_date) {
										// continue;
									// } else {
										// if ($first_valid_index_for_calculation == -1) {
											// $first_valid_index_for_calculation = $index;
										// }
										// if ($sell_in_date < $start_date) {
											// $start_index = $index;
										// } elseif ($start_index == 0 && $first_valid_index_for_calculation !== -1) {
											// $start_index = $first_valid_index_for_calculation;
										// }
										// break;
									// }
								// }
								// // if ($first_valid_index_for_calculation != -1 && $start_index == 0) {
									// // $start_index = $first_valid_index_for_calculation;
								// // }
							// } elseif (isset($processed_models_stock[$current_model]) && isset($sell_in[$current_model])) {
								// $start_date = str_replace('-', '', $start_date_str);
								// $end_date = str_replace('-', '', $end_date_str);
								// $potential_start_index = $last_processed_index + 1;

								// for ($i = $potential_start_index; $i < count($sell_in[$current_model]); $i++) {
									// $sell_in_date = $sell_in[$current_model][$i][4];
									// if ($sell_in_date < $start_date) {
										// $start_index = $i; // Si la fecha ya supera el rango, empezamos aquí para no procesar más
										// break;
									// }
									// // Si la fecha es anterior a la start_date del nuevo rango, seguimos buscando
								// }
								// // Si no se encontró ningún índice dentro o después del rango, no procesamos más
								// if (!isset($start_index)) {
									// continue;
								// }
							// }

							// // --- Procesamiento del Stock ---
							// $stock_column_index = -1;
							// if (in_array($current_model, array_keys($processed_models_stock))) {
								// $stock_column_index_offset = $processed_models_stock[$current_model];
								// if ($stock_column_index_offset < count($column_indices_stock)) {
									// $stock_column_index = $column_indices_stock[$stock_column_index_offset];
									// $processed_models_stock[$current_model]++;
								// } else {
									// //echo "Advertencia: Se agotaron las columnas de destino para el stock del modelo: " . $current_model . "\n";
									// continue;
								// }
							// } else {
								// $stock_column_index = $column_indices_stock[0];
								// $processed_models_stock[$current_model] = 1;
							// }

							// if ($stock_column_index != -1 && isset($sell_in[$current_model])) {
								// for ($index = $start_index; $index < count($sell_in[$current_model]); $index++) {
									// $sell_in_item = &$sell_in[$current_model][$index];
									// $qty_available = $sell_in_item[8];
									// $remaining_stock = $stock_to_achieve - $stock_accumulated;
									
									// if($stock_to_achieve == 0){
										// $cost_sellin_data[$current_model][] = $sell_in[$current_model][0][9];
									// }
									// if ($remaining_stock <= 0) {										
										// break;
									// }

									// if ($qty_available <= $remaining_stock && $qty_available >= 0) {
										// $sell_in_item[$stock_column_index] = $qty_available;
										// $stock_accumulated += $qty_available;
										// $last_processed_index = $index;
									// } elseif ($remaining_stock > 0 && $qty_available > 0) {
										// $amount_to_take = min($remaining_stock, $qty_available);
										// $sell_in_item[$stock_column_index] = $amount_to_take;
										// $stock_accumulated = $stock_to_achieve;
										// $last_processed_index = $index;
										// break;
									// }
								// }

								// if ($stock_accumulated < $stock_to_achieve) {
									// //echo "Advertencia: No se alcanzó el stock objetivo (" . $stock_to_achieve . ") para el modelo: " . $current_model . " (se acumuló: " . $stock_accumulated . ")\n";
								// }

								// // --- Procesamiento del Qty y Cálculo del Promedio Ponderado ---
								// $qty_column_index = -1;
								// $avg_column_index = -1;
								// if (in_array($current_model, array_keys($processed_models_qty))) {
									// $qty_column_index_offset = $processed_models_qty[$current_model];
									// $avg_column_index_offset = $processed_models_avg[$current_model];
									// if ($qty_column_index_offset < count($column_indices_qty) && $avg_column_index_offset < count($column_indices_avg)) {
										// $qty_column_index = $column_indices_qty[$qty_column_index_offset];
										// $avg_column_index = $column_indices_avg[$avg_column_index_offset];
										// $processed_models_qty[$current_model]++;
										// $processed_models_avg[$current_model]++;
									// } else {
										// //echo "Advertencia: Se agotaron las columnas de destino para el qty/promedio del modelo: " . $current_model . "\n";
										// continue;
									// }
								// } else {
									// $qty_column_index = $column_indices_qty[0];
									// $avg_column_index = $column_indices_avg[0];
									// $processed_models_qty[$current_model] = 1;
									// $processed_models_avg[$current_model] = 1;
								// }

								// if ($qty_column_index != -1 && $avg_column_index != -1 && $last_processed_index != -1) {
									// if ($qty_to_fill == 0) {
										// // Caso especial cuando qty es 0
										// if ($start_index < count($sell_in[$current_model]) && isset($sell_in[$current_model][$start_index][9])) {
											// $first_valid_value_col9 = $sell_in[$current_model][$last_processed_index][9];
											// $cost_sellin_data[$current_model][] = $sell_in[$current_model][$last_processed_index][9];
											// for ($m = 0; $m <= $last_processed_index; $m++) {
												// $sell_in[$current_model][$m][$qty_column_index] = 0;
												// $sell_in[$current_model][$m][$avg_column_index] = $first_valid_value_col9;
											// }
										// } else {
											
											// for ($m = $last_processed_index; $m >= 0; $m--) {
												// $sell_in[$current_model][$m][$qty_column_index] = '';
												// $sell_in[$current_model][$m][$avg_column_index] = 0;
											// }
										// }
									// } else {
										// $qty_accumulated_total = 0;
										// $weighted_sum = 0;
										// for ($k = $last_processed_index; $k >= $start_index; $k--) {
											// $value_in_stock_column = $sell_in[$current_model][$k][$stock_column_index] ?? 0;
											// $remaining_qty = $qty_to_fill - $qty_accumulated_total;

											// if ($remaining_qty <= 0) {
												// break;
											// }

											// if ($value_in_stock_column <= $remaining_qty && $value_in_stock_column > 0) {
												// $sell_in[$current_model][$k][$qty_column_index] = $value_in_stock_column;
												// $weighted_sum += ($sell_in[$current_model][$k][9] ?? 0) * $value_in_stock_column;
												// $qty_accumulated_total += $value_in_stock_column;
											// } elseif ($remaining_qty > 0 && $value_in_stock_column > 0) {
												// $amount_to_take_for_qty = min($remaining_qty, $value_in_stock_column);
												// $sell_in[$current_model][$k][$qty_column_index] = $amount_to_take_for_qty;
												// $weighted_sum += ($sell_in[$current_model][$k][9] ?? 0) * $amount_to_take_for_qty;
												// $qty_accumulated_total += $amount_to_take_for_qty;
												// if ($qty_accumulated_total >= $qty_to_fill) {
													// break;
												// }
											// } else {
												// $sell_in[$current_model][$k][$qty_column_index] = 0;
											// }
										// }

										// for ($l = $start_index; $l < $k; $l++) {
											// $sell_in[$current_model][$l][$qty_column_index] = '';
										// }

										// // Calcular y asignar el promedio ponderado
										// if ($qty_accumulated_total > 0) {
											// $average = $weighted_sum / $qty_accumulated_total;
											// $cost_sellin_data[$current_model][] = $average;
											// for ($m = 0; $m <= $last_processed_index; $m++) {
												// if (isset($sell_in[$current_model][$m][$qty_column_index]) && $sell_in[$current_model][$m][$qty_column_index] >= 0) {
													// $sell_in[$current_model][$m][$avg_column_index] = $average;
													// $sell_in[$current_model][$m][$avg_column_index-1] = $seq;  // Agregar valores de seq
												// }
											// }
										// }										
									// }
								// }
							// }
						// }
					// }
					
					// foreach ($sell_in as $model => $rows) {
						// foreach ($rows as $row_data) {
							// if (isset($row_data[56]) && is_numeric($row_data[56])) {
								// $excel_row_number = (int) $row_data[56];
								// for ($col_index = 13; $col_index < count($row_data)-1; $col_index++) {
									// $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index + 1);
									// //echo '<pre>'; print_r([$column_letter . $excel_row_number, $row_data[$col_index]]);
									// $new_sheet->setCellValue($column_letter . $excel_row_number, $row_data[$col_index]);
								// }
							// } else {
								// //echo "Advertencia: No se encontró o no es numérico el número de fila en el índice [56] para una fila de datos del modelo: " . $model . "\n";
								// // Opcional: Puedes decidir cómo manejar estas filas (omitirlas, escribirlas secuencialmente, etc.)
							// }
						// }
						
					// }
					
					//echo '<pre>'; print_r($vars_global); return;
				//}
			
							
			}
        }
		
	
				
		$calculate_sheet_sellin = $spreadsheet->getSheetByName('SELLIN');
		
		if ($calculate_sheet_sellin) {
				$calculate_data_sellin = $calculate_sheet_sellin->toArray();
				$start_row = 2;
					//echo '<pre>'; print_r($vars_global);
					$sell_in = [];
					$model = [];
					$count_temp = 0;
					$model_bundle_pre = [];
					if (!empty($vars_bundle)){
						foreach($vars_bundle as $item){
							
							if(in_array($item['model'], $model_bundle_pre)){
								continue;
							}
							else{
								$vars_global[] = $item;
								$model_bundle_pre[] = $item['model'];
							}
						}
					}
					foreach ($calculate_data_sellin as $index => &$row) {
						$count_temp = $count_temp + 1;						
						if ($index > 0) { // Ignorar cabeceras
							$row[56] = $count_temp;
							$sell_in[$row[3]][] = $row;
							
						}
						
					}
					//$model = array_values(array_unique($model_global));

					//echo '<pre>'; print_r($model); return;
					// Ordenar cada modelo por [4] (letra) y luego por [5] (fecha), ambos en orden descendente
					foreach ($sell_in as $model => &$entries) {
						usort($entries, function ($a, $b) {
							// Comparar por columna [4] (Z -> A)
							$col4Comparison = strcmp($b[4], $a[4]);
							if ($col4Comparison !== 0) {
								return $col4Comparison;
							}
							// Si las letras son iguales, comparar por fecha [5] (Z -> A)
							return strcmp($b[5], $a[5]);
						});
					}
					unset($entries); // Evita referencias no deseadas en PHP
					
					//echo '<pre>'; print_r($vars_global);
					$column_indices_stock = [16, 20, 24, 28, 32, 36, 40, 44, 48, 52];
					$column_indices_qty = [15, 19, 23, 27, 31, 35, 39, 43, 47, 51];
					$column_indices_avg = [14, 18, 22, 26, 30, 34, 38, 42, 46, 50];
					$column_indices_seq = [13, 17, 21, 25, 29, 33, 37, 41, 45, 49];
					$processed_models_stock = [];
					$processed_models_qty = [];
					$processed_models_avg = [];
					$processed_models_seq = [];
					
					//echo '<pre>'; print_r($vars_global);
					foreach ($vars_global as $vars_global_item) {
						//echo '<pre>'; print_r($vars_global);
						$current_model = $vars_global_item['model'] ?? null;
						$priority = $vars_global_item['priority'] ?? null;
						$qty_to_fill = $vars_global_item['qty'] ?? null;
						$start_date_str = $vars_global_item['start_date'] ?? null;
						$end_date_str = $vars_global_item['end_date'] ?? null;
						$seq = $vars_global_item['seq'] ?? null;
						//echo '<pre>'; print_r($vars_global_item);
						if ($priority === '1-BASIC' || $priority === '3-MIX' || $priority === 'BUNDLE') {
							$stock_to_achieve = $vars_global_item['stock'];
							$stock_accumulated = 0;
							$last_processed_index = -1;
							$start_index = 0;
							$is_first_occurrence = !isset($processed_models_stock[$current_model]);
							
							$first_valid_index_for_calculation = -1;
							$is_first_occurrence_seq = !isset($processed_models_seq[$current_model]);

							// --- Determinar el índice de inicio ---
							if ($is_first_occurrence && isset($sell_in[$current_model])) {
								$start_date = str_replace('-', '', $start_date_str);
								$end_date = str_replace('-', '', $end_date_str);

								foreach ($sell_in[$current_model] as $index => $item) {
									$sell_in_date = $item[4];
									if ($sell_in_date >= $start_date && $sell_in_date <= $end_date || $sell_in_date > $end_date) {
										continue;
									} else {
										if ($first_valid_index_for_calculation == -1) {
											$first_valid_index_for_calculation = $index;
										}
										if ($sell_in_date < $start_date) {
											$start_index = $index;
										} elseif ($start_index == 0 && $first_valid_index_for_calculation !== -1) {
											$start_index = $first_valid_index_for_calculation;
										}
										break;
									}
								}
								// if ($first_valid_index_for_calculation != -1 && $start_index == 0) {
									// $start_index = $first_valid_index_for_calculation;
								// }
							} elseif (isset($processed_models_stock[$current_model]) && isset($sell_in[$current_model])) {
								$start_date = str_replace('-', '', $start_date_str);
								$end_date = str_replace('-', '', $end_date_str);
								$potential_start_index = $last_processed_index + 1;

								for ($i = $potential_start_index; $i < count($sell_in[$current_model]); $i++) {
									$sell_in_date = $sell_in[$current_model][$i][4];
									if ($sell_in_date < $start_date) {
										$start_index = $i; // Si la fecha ya supera el rango, empezamos aquí para no procesar más
										break;
									}
									// Si la fecha es anterior a la start_date del nuevo rango, seguimos buscando
								}
								// Si no se encontró ningún índice dentro o después del rango, no procesamos más
								if (!isset($start_index)) {
									continue;
								}
							}

							// --- Procesamiento del Stock ---
							$stock_column_index = -1;
							if (in_array($current_model, array_keys($processed_models_stock))) {
								$stock_column_index_offset = $processed_models_stock[$current_model];
								if ($stock_column_index_offset < count($column_indices_stock)) {
									$stock_column_index = $column_indices_stock[$stock_column_index_offset];
									$processed_models_stock[$current_model]++;
								} else {
									//echo "Advertencia: Se agotaron las columnas de destino para el stock del modelo: " . $current_model . "\n";
									continue;
								}
							} else {
								$stock_column_index = $column_indices_stock[0];
								$processed_models_stock[$current_model] = 1;
							}

							if ($stock_column_index != -1 && isset($sell_in[$current_model])) {
								for ($index = $start_index; $index < count($sell_in[$current_model]); $index++) {
									$sell_in_item = &$sell_in[$current_model][$index];
									$qty_available = $sell_in_item[8];
									$remaining_stock = $stock_to_achieve - $stock_accumulated;
									
									if($stock_to_achieve == 0){
										$cost_sellin_data[$current_model][] = $sell_in[$current_model][0][9];
									}
									if ($remaining_stock <= 0) {										
										break;
									}

									if ($qty_available <= $remaining_stock && $qty_available >= 0) {
										$sell_in_item[$stock_column_index] = $qty_available;
										$stock_accumulated += $qty_available;
										$last_processed_index = $index;
									} elseif ($remaining_stock > 0 && $qty_available > 0) {
										$amount_to_take = min($remaining_stock, $qty_available);
										$sell_in_item[$stock_column_index] = $amount_to_take;
										$stock_accumulated = $stock_to_achieve;
										$last_processed_index = $index;
										break;
									}
								}

								if ($stock_accumulated < $stock_to_achieve) {
									//echo "Advertencia: No se alcanzó el stock objetivo (" . $stock_to_achieve . ") para el modelo: " . $current_model . " (se acumuló: " . $stock_accumulated . ")\n";
								}

								// --- Procesamiento del Qty y Cálculo del Promedio Ponderado ---
								$qty_column_index = -1;
								$avg_column_index = -1;
								if (in_array($current_model, array_keys($processed_models_qty))) {
									$qty_column_index_offset = $processed_models_qty[$current_model];
									$avg_column_index_offset = $processed_models_avg[$current_model];
									if ($qty_column_index_offset < count($column_indices_qty) && $avg_column_index_offset < count($column_indices_avg)) {
										$qty_column_index = $column_indices_qty[$qty_column_index_offset];
										$avg_column_index = $column_indices_avg[$avg_column_index_offset];
										$processed_models_qty[$current_model]++;
										$processed_models_avg[$current_model]++;
									} else {
										//echo "Advertencia: Se agotaron las columnas de destino para el qty/promedio del modelo: " . $current_model . "\n";
										continue;
									}
								} else {
									$qty_column_index = $column_indices_qty[0];
									$avg_column_index = $column_indices_avg[0];
									$processed_models_qty[$current_model] = 1;
									$processed_models_avg[$current_model] = 1;
								}

								if ($qty_column_index != -1 && $avg_column_index != -1 && $last_processed_index != -1) {
									if ($qty_to_fill == 0) {
										// Caso especial cuando qty es 0
										if ($start_index < count($sell_in[$current_model]) && isset($sell_in[$current_model][$start_index][9])) {
											$first_valid_value_col9 = $sell_in[$current_model][$last_processed_index][9];
											$cost_sellin_data[$current_model][] = $sell_in[$current_model][$last_processed_index][9];
											for ($m = 0; $m <= $last_processed_index; $m++) {
												$sell_in[$current_model][$m][$qty_column_index] = 0;
												$sell_in[$current_model][$m][$avg_column_index] = $first_valid_value_col9;
											}
										} else {
											
											for ($m = $last_processed_index; $m >= 0; $m--) {
												$sell_in[$current_model][$m][$qty_column_index] = '';
												$sell_in[$current_model][$m][$avg_column_index] = 0;
											}
										}
									} else {
										$qty_accumulated_total = 0;
										$weighted_sum = 0;
										for ($k = $last_processed_index; $k >= $start_index; $k--) {
											$value_in_stock_column = $sell_in[$current_model][$k][$stock_column_index] ?? 0;
											$remaining_qty = $qty_to_fill - $qty_accumulated_total;

											if ($remaining_qty <= 0) {
												break;
											}

											if ($value_in_stock_column <= $remaining_qty && $value_in_stock_column > 0) {
												$sell_in[$current_model][$k][$qty_column_index] = $value_in_stock_column;
												$weighted_sum += ($sell_in[$current_model][$k][9] ?? 0) * $value_in_stock_column;
												$qty_accumulated_total += $value_in_stock_column;
											} elseif ($remaining_qty > 0 && $value_in_stock_column > 0) {
												$amount_to_take_for_qty = min($remaining_qty, $value_in_stock_column);
												$sell_in[$current_model][$k][$qty_column_index] = $amount_to_take_for_qty;
												$weighted_sum += ($sell_in[$current_model][$k][9] ?? 0) * $amount_to_take_for_qty;
												$qty_accumulated_total += $amount_to_take_for_qty;
												if ($qty_accumulated_total >= $qty_to_fill) {
													break;
												}
											} else {
												$sell_in[$current_model][$k][$qty_column_index] = 0;
											}
										}

										for ($l = $start_index; $l < $k; $l++) {
											$sell_in[$current_model][$l][$qty_column_index] = '';
										}

										// Calcular y asignar el promedio ponderado
										if ($qty_accumulated_total > 0) {
											$average = $weighted_sum / $qty_accumulated_total;
											$cost_sellin_data[$current_model][] = $average;
											for ($m = 0; $m <= $last_processed_index; $m++) {
												if (isset($sell_in[$current_model][$m][$qty_column_index]) && $sell_in[$current_model][$m][$qty_column_index] >= 0) {
													$sell_in[$current_model][$m][$avg_column_index] = $average;
													$sell_in[$current_model][$m][$avg_column_index-1] = $seq;  // Agregar valores de seq
												}
											}
										}										
									}
								}
							}
						}
					}
					
					foreach ($sell_in as $model => $rows) {
						foreach ($rows as $row_data) {
							if (isset($row_data[56]) && is_numeric($row_data[56])) {
								$excel_row_number = (int) $row_data[56];
								for ($col_index = 13; $col_index < count($row_data)-1; $col_index++) {
									$column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index + 1);
									//echo '<pre>'; print_r([$column_letter . $excel_row_number, $row_data[$col_index]]);
									$calculate_sheet_sellin->setCellValue($column_letter . $excel_row_number, $row_data[$col_index]);
								}
							} else {
								//echo "Advertencia: No se encontró o no es numérico el número de fila en el índice [56] para una fila de datos del modelo: " . $model . "\n";
								// Opcional: Puedes decidir cómo manejar estas filas (omitirlas, escribirlas secuencialmente, etc.)
							}
						}
						
					}
					
					//echo '<pre>'; print_r($vars_global); return;
				}
		
		//echo '<pre>'; print_r($vars_global); return;
		//echo '<pre>'; print_r($cost_sellin_data); return;
		// Segunda pasada por CALCULATE para aplicar la condicional usando $cost_sellin_data
		$calculate_sheet = $spreadsheet->getSheetByName('CALCULATE');
		
		
		if ($calculate_sheet) {
			//echo '<pre>'; print_r($vars_global);
			$diff = 0;
			$calculate_data = $calculate_sheet->toArray();
			$start_row = 2;
			// Índice de la columna del modelo en CALCULATE
			$model_column_index_calculate = 8;
			// Índice de la columna donde quieres escribir cost_sellin en CALCULATE
			$cost_sellin_write_column_index = 10;

			// Crear un array temporal para almacenar la prioridad y cost_sellin por modelo desde $vars_global
			$model_info_vars_global = [];
			foreach ($vars_global as $item) {
				$model_info_vars_global[$item['model']][] = [
					'priority' => $item['priority'],
					'cost_sellin' => $item['cost_sellin'] ?? null,
					'qty' => $item['qty'],
				];
			}
			
			//echo '<pre>'; print_r($cost_sellin_data); return;
			// Array para rastrear la aparición actual del modelo en CALCULATE
			$model_occurrence_in_calculate = [];
			$index_basic = [];
			foreach ($calculate_data as $row_index => $row) {
				if ($row_index < $start_row - 1) continue;
				$model_calculate = $row[$model_column_index_calculate] ?? null;
				$model_bundle = $row[16];
				$price_promotion = $row[11];
				$start_date = $row[5];
				$end_date = $row[6];
				//$priority = $model_info_vars_global[$model_calculate][0]['priority'];
				$start_date_conv = substr($start_date, 0, 4) . "-" . substr($start_date, 4, 2) . "-" . substr($start_date, 6, 2);
				$end_date_conv = substr($end_date, 0, 4) . "-" . substr($end_date, 4, 2) . "-" . substr($end_date, 6, 2);
				
				$porcent = $row[12];
				$porcent_ns = str_replace("%", "", $porcent);
				$porcent_num = floatval($porcent_ns) / 100;
				$cost_prom = number_format(($row[11]/1.18)*(1-$porcent_num), 2);
				$cost_prom = str_replace(',', '', $cost_prom);
				
				// if(isset($cost_sellin_data[$model_bundle])){
					// echo '<pre>'; print_r($cost_sellin_data[$model_bundle]); return;
				// }
				if ($model_calculate) {
					if (!isset($model_occurrence_in_calculate[$model_calculate])) {
						$model_occurrence_in_calculate[$model_calculate] = 0;
					}
					if (!isset($index_basic[$model_calculate])) {
						$index_basic[$model_calculate] = 0;
					}
					$current_occurrence = $model_occurrence_in_calculate[$model_calculate];
					$current_occurrence_basic = $index_basic[$model_calculate];
					//$current_occurrence_basic = $model_occurrence_in_calculate[$model_calculate];

					
					if (isset($model_info_vars_global[$model_calculate][$current_occurrence]['priority'])) {
						$priority_vars_global = $model_info_vars_global[$model_calculate][$current_occurrence]['priority'];
						$cost_sellin_vars_global = $model_info_vars_global[$model_calculate][$current_occurrence]['cost_sellin'];

						if ($priority_vars_global === '2-ADDITIONAL' && $cost_sellin_vars_global !== null) {
							$columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cost_sellin_write_column_index + 1);
							$calculate_sheet->setCellValue($columnIndex . ($row_index + 1), $cost_sellin_vars_global);
							$cost_sellin = $cost_sellin_vars_global;
							if ($cost_sellin >= $cost_prom){
								$diff = (float)$cost_sellin - (float)$cost_prom;
								$diff = number_format($diff,2);
							} else $diff = 0;
							$priority = $model_info_vars_global[$model_calculate][$current_occurrence]['priority'];
							
							$qty = $model_info_vars_global[$model_calculate][$current_occurrence]['qty'];
						} elseif (($priority_vars_global === '1-BASIC' || $priority_vars_global === '3-MIX' || $priority_vars_global === 'BUNDLE') && isset($cost_sellin_data[$model_calculate][$current_occurrence_basic])) {
							$cost_sellin_value = $cost_sellin_data[$model_calculate][$current_occurrence_basic];
							$columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cost_sellin_write_column_index + 1);
							$calculate_sheet->setCellValue($columnIndex . ($row_index + 1), $cost_sellin_value);
							$cost_sellin = $cost_sellin_value;
							
							if ($cost_sellin >= $cost_prom){
								$diff = (float)$cost_sellin - (float)$cost_prom;
								$diff = number_format($diff,2);
							} else $diff = 0;
							
							$priority = $model_info_vars_global[$model_calculate][$current_occurrence]['priority'];
							$qty = $model_info_vars_global[$model_calculate][$current_occurrence]['qty'];
							$index_basic[$model_calculate]++;
						}
						
						
					}
					$model_occurrence_in_calculate[$model_calculate]++;
					
				}
				if(isset($cost_sellin_data[$model_bundle])){
					$cost_sellin_value_bundle = $cost_sellin_data[$model_bundle][0];
					$columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(17 + 1);
					$calculate_sheet->setCellValue($columnIndex . ($row_index + 1), $cost_sellin_value_bundle);
					$columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(19 + 1);
					$calculate_sheet->setCellValue($columnIndex . ($row_index + 1), $cost_sellin_value_bundle);
					
					$columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(44 + 1);
					
					foreach($vars_global as $item){
						if($item['model'] === $model_bundle){
							if($item['qty'] == 0){
								$calculate_sheet->setCellValue($columnIndex . ($row_index + 1), '1B'); // Llenar columna AS
							}else $calculate_sheet->setCellValue($columnIndex . ($row_index + 1), '2B'); // Llenar columna AS
							break;
						}
						
					}
						
				}
				$current_row = $row_index + 1;
				//echo '<pre>'; print_r($vars_global);
				//echo '<pre>'; print_r([$item['model'], $item['qty']]);
				foreach ($vars_global as $item){
								
					if ($item['model'] === $model_calculate && $item['start_date'] === $start_date_conv && $item['end_date'] === $end_date_conv && isset($item['hiraoka']) && $item['hiraoka'] == 1){
						
						$calculate_sheet->setCellValue('P' . ($row_index + 1), $item['qty']); // Llenar columna P
						$calculate_sheet->setCellValue('AL' . ($row_index + 1), $item['qty']); // Llenar columna P
						// $row[]$item['qty'] = $qty;
						// $item['hiraoka'] = 1;
					}

				}
				
				$formula_column_o = "=if(AE{$current_row}=\"BUNDLE\",0, ROUND(IF(K{$current_row}-N{$current_row}<0,0,K{$current_row}-N{$current_row}),2))";
				$calculate_sheet->setCellValue('O' . ($row_index + 1), $formula_column_o); // Llenar columna O
				
				$formula_monto_total = "=P{$current_row}*O{$current_row}";
				$calculate_sheet->setCellValue('V' . ($row_index + 1), $formula_monto_total); // Llenar columna V
				
				// Calculo FCST UNIT AMT DIFF
				$formula_fcst_unit_amt_diff = "=O{$current_row}-AY{$current_row}";
				$calculate_sheet->setCellValue('Z' . ($row_index + 1), $formula_fcst_unit_amt_diff); // Llenar columna Z
				
				// Calculo FCST AMT DIFF
				$formula_fcst_amt_diff = "=V{$current_row}-BA{$current_row}";
				$calculate_sheet->setCellValue('AB' . ($row_index + 1), $formula_fcst_amt_diff); // Llenar columna AB
				
				$vars_mix[$model_calculate][] = ['customer' => $row[7], 'model' => $model_calculate, 'seq' => $row[0], 'start_date' => $start_date_conv, 'end_date' => $end_date_conv,  'qty' => $qty, 'cost_sellin' => $cost_sellin, 'cost_prom' => $cost_prom, 'diff' => $diff, 'priority' => $priority];
				
				//echo '<pre>'; print_r($vars_mix);

			}
			//echo '<pre>'; print_r($vars_mix); return;
		}
        
		$calculate_sheet_mix = $spreadsheet->getSheetByName('MIX_DETAIL');
		$current_excel_row = 3; // Inicialización aquí
		$data_to_insert_mix = [];
		$sumas_por_modelo_seq = [];
		$producto_diff_qty_por_modelo_seq = [];

		if ($calculate_sheet_mix) {
			$calculate_mix_data = $calculate_sheet_mix->toArray();
			$start_row = 2;

			function sortBySeqColumnC($a, $b) {
				return $a[2] <=> $b[2];
			}

			foreach ($vars_mix as $model => &$model_data) {
				$mix_items = array_filter($model_data, function ($item) {
					return isset($item['priority']) && $item['priority'] === '3-MIX';
				});
				$mix_items = array_values($mix_items);

				$basic_items = array_filter($model_data, function ($item) {
					return isset($item['priority']) && $item['priority'] === '1-BASIC';
				});
				$basic_items = array_values($basic_items);

				foreach ($mix_items as $mix_item) {
					foreach ($basic_items as $basic_item) {
						if (isset($mix_item['end_date']) && isset($basic_item['end_date']) && $mix_item['end_date'] !== $basic_item['end_date']) {
							$mix_values = [
								$mix_item['customer'] ?? '',        // A (0)
								$mix_item['model'] ?? '',           // B (1)
								$mix_item['seq'] ?? '',             // C (2)
								$mix_item['start_date'] ?? '',      // D (3)
								$mix_item['end_date'] ?? '',        // E (4)
								$mix_item['qty'] ?? '',             // F (5)
								str_replace(',', '.', $mix_item['cost_sellin'] ?? ''), // G (6)
								str_replace(',', '.', $mix_item['cost_prom'] ?? ''),    // H (7)
								$mix_item['diff'] ?? '',           // I (8)
								$basic_item['seq'] ?? '',           // J (9)
								$basic_item['start_date'] ?? '',    // K (10)
								$basic_item['end_date'] ?? '',      // L (11)
								$basic_item['qty'] ?? '',           // M (12)
								str_replace(',', '.', $basic_item['cost_sellin'] ?? ''), // N (13)
								str_replace(',', '.', $basic_item['cost_prom'] ?? ''),    // O (14)
								$basic_item['diff'] ?? '',          // P (15)
								($mix_item['start_date'] > $basic_item['start_date'] ? $mix_item['start_date'] : $basic_item['start_date']) ?? '', // Q (16)
								($mix_item['end_date'] < $basic_item['end_date'] ? $mix_item['end_date'] : $basic_item['end_date']) ?? '',    // R (17)
								0, // Placeholder para adj_qty (S - 18)
								'', // Placeholder para columna T (19)
								str_replace(',', '.', $basic_item['cost_sellin'] ?? ''), // U (20)
								str_replace(',', '.', $basic_item['cost_prom'] ?? ''),    // V (21)
								(floatval(str_replace(',', '.', $basic_item['cost_sellin'] ?? '0')) - floatval(str_replace(',', '.', $basic_item['cost_prom'] ?? '0'))), // W (22)
								0, // Placeholder para adj_total (X - 23)
							];

							if (method_exists($this, 'qty_calculate')) {
								$adj_qty = $this->qty_calculate($out_map, $mix_item['model'], $mix_values[16], $mix_values[17], 0, $price_promotion);
								$mix_values[18] = $adj_qty;
								$mix_values[23] = $mix_values[22] * $adj_qty;

								$modelo_actual = $mix_item['model'] ?? '';
								$seq_actual = $mix_item['seq'] ?? '';
								$valor_x_actual = $mix_values[23];
								$diff_3mix = floatval($mix_item['diff'] ?? 0);
								$qty_3mix = intval($mix_item['qty'] ?? 0);
								$producto_diff_qty = $diff_3mix * $qty_3mix;

								if (!isset($sumas_por_modelo_seq[$modelo_actual])) {
									$sumas_por_modelo_seq[$modelo_actual] = [];
								}
								if (!isset($sumas_por_modelo_seq[$modelo_actual][$seq_actual])) {
									$sumas_por_modelo_seq[$modelo_actual][$seq_actual] = 0;
								}
								$sumas_por_modelo_seq[$modelo_actual][$seq_actual] += $valor_x_actual;

								if (!isset($producto_diff_qty_por_modelo_seq[$modelo_actual])) {
									$producto_diff_qty_por_modelo_seq[$modelo_actual] = [];
								}
								$producto_diff_qty_por_modelo_seq[$modelo_actual][$seq_actual] = $producto_diff_qty;

							} else {
								error_log("Error: La función qty_calculate no está definida en esta clase.");
							}
							$data_to_insert_mix[] = $mix_values;
						}
					}
				}
			}
			//echo '<pre>'; print_r($sumas_por_modelo_seq); return;
			usort($data_to_insert_mix, 'sortBySeqColumnC');

			foreach ($data_to_insert_mix as $row_data) {
				for ($col = 0; $col < count($row_data); $col++) {
					$columnIndex = Coordinate::stringFromColumnIndex($col + 1);
					$calculate_sheet_mix->setCellValue($columnIndex . $current_excel_row, $row_data[$col]);
				}
				$current_excel_row++;
			}

			$calculate_sheet_cal = $spreadsheet->getSheetByName('CALCULATE');
			if ($calculate_sheet_cal) {
				$calculate_data = $calculate_sheet_cal->toArray();
				$start_row_calculate = 2;
				$current_excel_row_calculate = $start_row_calculate;
				$columna_v_calculate = Coordinate::stringFromColumnIndex(21 + 1); // Columna V
				$columna_x_calculate = Coordinate::stringFromColumnIndex(23 + 1); // Columna X

				foreach ($calculate_data as $row_index => $row) {
					if ($row_index < $start_row_calculate - 1) continue;

					$modelo_calculate = $row[8] ?? ''; // Columna I
					$seq_calculate = $row[0] ?? '';   // Columna A
					//echo '<pre>'; print_r([$modelo_calculate, $seq_calculate]); 
					if ($vars_global[$row_index-1]['priority'] === '3-MIX'){
						//$seq_calculate = $vars_global[$row_index+1]['seq'];
						$suma_guardada_x = $sumas_por_modelo_seq[$modelo_calculate][$seq_calculate] ?? 0;
						$producto_guardado_diff_qty = $producto_diff_qty_por_modelo_seq[$modelo_calculate][$seq_calculate] ?? 0;

						$diferencia_v = $producto_guardado_diff_qty - $suma_guardada_x;
						if ($diferencia_v < 0){
							$calculate_sheet_cal->setCellValue($columna_v_calculate . ($row_index + 1), 0);
						} else $calculate_sheet_cal->setCellValue($columna_v_calculate . ($row_index + 1), $diferencia_v);
						
						$calculate_sheet_cal->setCellValue($columna_x_calculate . ($row_index + 1), $suma_guardada_x);
					}					
				}
			}
		}
		//echo '<pre>'; print_r($vars_global);
		// Guardar y descargar archivo
		// Directorio temporal para guardar los archivos generados
		// $outputDir = FCPATH . './upload_file/Sales Admin/';
		// if (!is_dir($outputDir)) {
			// mkdir($outputDir, 0755, true); // Crea la carpeta si no existe
		// }

		// // Nombre único para el archivo, incluyendo un uniqid() para evitar colisiones
		// //$filename = 'report_promotion_' . date('Ymd_His') . '_' . uniqid() . '.xlsx';
		// $filename = '[Process]' . $excel_file;
		// $filePath = $outputDir . $filename;

		// // --- ESTA PARTE REEMPLAZA TU $writer->save('php://output'); ---
		// // Ahora, el escritor guarda el archivo en el disco.
		// $writer = new Xlsx($spreadsheet);
		// $writer->save($filePath);

		// // Responde al frontend con la URL de descarga
		// echo json_encode([
			// 'type' => 'success',
			// 'msg' => 'Excel file generated successfully!',
			// 'downloadUrl' => base_url('module/sa_sell_in_out_promotion/download_excel/' . $filename)
		// ]);
		
        $this->downloadSpreadsheet($spreadsheet, '[Process] '.$excel_file);
    }
	
	// public function download_excel($filename) {
        // $filePath = FCPATH . './upload_file/Sales Admin/' . $filename;

        // // Verifica si el archivo existe y es legible antes de intentar enviarlo
        // if (file_exists($filePath) && is_readable($filePath)) {
            // // --- ESTAS SON LAS CABECERAS Y LA LÓGICA DE DESCARGA DE TU FUNCIÓN ORIGINAL ---
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment;filename="' . $filename . '"');
            // header('Cache-Control: max-age=0'); // Asegura que el navegador no cachee el archivo
            // header('Pragma: public'); // Necesario para IE
            // header('Expires: 0'); // Necesario para IE
            // header('Content-Length: ' . filesize($filePath)); // Buena práctica: informa el tamaño del archivo

            // // Lee el archivo y lo envía al navegador
            // readfile($filePath);

            // // Opcional: Elimina el archivo del servidor después de que ha sido enviado
            // // Descomenta la siguiente línea si quieres que se elimine automáticamente.
            // // unlink($filePath); 

            // exit; // Termina la ejecución para evitar que se envíe contenido adicional
        // } else {
            // // Si el archivo no existe o no se puede leer, muestra un error 404
            // log_message('error', 'Download file not found or not readable: ' . $filePath);
            // show_404();
        // }
    // }

	
	public function downloadSpreadsheet($spreadsheet, $filename) {		

		// Configurar cabeceras de respuesta	
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');

	}
	
	public function export_excel(){
		$this->generate_excel($spreadsheet);
	}
	
	public function map_table_sell_out($item_out, $stock_sum){
		
		$row_map = [
					'customer'			 => $item_out->customer,
					'acct_gtm'			 => $item_out->acct_gtm,
					'customer_model' 	 => $item_out->customer_model,
					'model_code' 		 => $item_out->model_suffix_code,
					'txn_date' 			 => $item_out->txn_date,
					'cust_store_code'	 => $item_out->cust_store_code,
					'cust_store_name'	 => $item_out->cust_store_name,
					'sellout_unit' 		 => $item_out->sellout_unit,
					'sellout_amt' 		 => $item_out->sellout_amt,					
					'stock'				 => $stock_sum ?? '',
					'ticket'			 => $item_out->ticket,
					"promo1_price" 		 => '',					
					"target1_flag" 		 => '',
					"promo2_price" 		 => '',
					"target2_flag" 		 => '',
					"promo3_price" 		 => '',
					"target3_flag" 		 => '',					
					"promo4_price" 		 => '',
					"target4_flag" 		 => '',
					"promo5_price" 		 => '',
					"target5_flag" 		 => '',
					"promo6_price" 		 => '',					
					"target6_flag" 		 => '',
					"promo7_price" 		 => '',
					"target7_flag" 		 => '',
					"promo8_price" 		 => '',
					"target8_flag" 		 => '',					
					"promo9_price" 		 => '',
					"target9_flag" 		 => '',
					"promo10_price" 	 => '',
					"target10_flag" 	 => '',
					"updated"			 => date("Y-m-d")
				];
		return $row_map;
	}
	
	public function load_sell_out_v1() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field' 	=> 'model_suffix_code',
			'values' 	=> $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model'], ['model' => $model]);
				if (!empty($culculate_price_promotions) && $culculate_price_promotions[0]->model === $model) {
					$price_prom = $culculate_price_promotions[0]->price_promotion;
				}
			}

			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' 			=> $item_out->customer,
							'acct_gtm' 			=> $item_out->acct_gtm,
							'customer_model'	=> $item_out->customer_model,
							'model_code'		=> $item_out->model_suffix_code,
							'txn_date' 			=> $item_out->txn_date,
						];

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}

						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_records[] = $base_data + [
								'cust_store_code' 	=> $item_out->cust_store_code,
								'cust_store_name' 	=> $item_out->cust_store_name,
								'sellout_unit' 		=> $item_out->sellout_unit ?? 0,
								'sellout_amt' 		=> $item_out->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item_out->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag'		=> $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED',
							];
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$current_day_records[] = $first_item_base + [
							'cust_store_code' 		=> null,
							'cust_store_name' 		=> null,
							'sellout_unit' 			=> null,
							'sellout_amt' 			=> null,
							'stock' 				=> $daily_stock_sum,
							'ticket' 				=> null,
							'promo1_price'			=> $price_prom,
							'target1_flag' 			=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
						];
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer' 			=> $item->customer,
									'acct_gtm' 			=> $item->acct_gtm,
									'customer_model'	=> $item->customer_model,
									'model_code' 		=> $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$past_sellout_data[] = [
								'customer' 			=> $item->customer,
								'acct_gtm' 			=> $item->acct_gtm,
								'customer_model'	=> $item->customer_model,
								'model_code' 		=> $item->model_suffix_code,
								'txn_date'			=> $past_date,
								'cust_store_code' 	=> $item->cust_store_code,
								'cust_store_name' 	=> $item->cust_store_name,
								'sellout_unit' 		=> $item->sellout_unit ?? 0,
								'sellout_amt' 		=> $item->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag' 		=> $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO',
							];
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer' 			=> $customer,
					'acct_gtm' 			=> $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'	=> $first_past_item_base['customer_model'] ?? null,
					'model_code' 		=> $model,
					'txn_date' 			=> $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock' 			=> $past_stock_sum,
					'cust_store_code' 	=> null,
					'cust_store_name' 	=> null,
					'sellout_unit' 		=> null,
					'sellout_amt' 		=> null,
					'ticket' 			=> null,
					'promo1_price'		=> $price_prom,
					'target1_flag' 		=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					$a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// Verificar dias previos para modelos de bundles mal mapeado
		// Falta agregar para las demas columnas de la hoja sellout como las colmnas de target1flag  y ver logica para las otras columnas
		// Inserción por lotes
		// Tener en cuenta para los casos de promo no applied cuando ticket es mayor a promo1_price (cuidado, se puede redondear y aun cumplicar con ser menor o sea 400 a 399 masimo 2 numeros mas)
		//$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}
	
	public function load_sell_out_v2() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field' 	=> 'model_suffix_code',
			'values' 	=> $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model'], ['model' => $model]);
				if (!empty($culculate_price_promotions) && $culculate_price_promotions[0]->model === $model) {
					$price_prom = $culculate_price_promotions[0]->price_promotion;
				}
			}

			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' 			=> $item_out->customer,
							'acct_gtm' 			=> $item_out->acct_gtm,
							'customer_model'	=> $item_out->customer_model,
							'model_code'		=> $item_out->model_suffix_code,
							'txn_date' 			=> $item_out->txn_date,
						];

						$target1_flag = $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED';
						if (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false) {
							$target1_flag = 'PROMO APPLIED WEB';
						}
						//  if (isset($item_out->ticket) && isset($price_prom) && is_numeric($item_out->ticket) && is_numeric($price_prom) && $item_out->ticket > ($price_prom + 2)) {
						// 	$target1_flag = 'PROMO NO APPLIED';
						// }
						$cust_store_code = $item_out->cust_store_code;
						if (empty($cust_store_code) || $cust_store_code == 0) {
							$names = explode(" ", $item_out->cust_store_name);
							if (count($names) > 1) {
								$cust_store_code = $names[1];
								if (isset($names[2])){
									$cust_store_code = $cust_store_code . " " . $names[2]; 
								}
							}
						}

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}

						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_records[] = $base_data + [
								'cust_store_code' 	=> $cust_store_code,
								'cust_store_name' 	=> $item_out->cust_store_name,
								'sellout_unit' 		=> $item_out->sellout_unit ?? 0,
								'sellout_amt' 		=> $item_out->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item_out->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag'		=> $target1_flag,
							];
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$current_day_records[] = $first_item_base + [
							'cust_store_code' 		=> null,
							'cust_store_name' 		=> null,
							'sellout_unit' 			=> null,
							'sellout_amt' 			=> null,
							'stock' 				=> $daily_stock_sum,
							'ticket' 				=> null,
							'promo1_price'			=> $price_prom,
							'target1_flag' 			=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
						];
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer' 			=> $item->customer,
									'acct_gtm' 			=> $item->acct_gtm,
									'customer_model'	=> $item->customer_model,
									'model_code' 		=> $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$target1_flag = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
							if (stripos($item->cust_store_name, 'WEB') !== false || stripos($item->cust_store_name, 'web') !== false) {
								$target1_flag = 'PROMO APPLIED WEB';
							}
							// if (isset($item->ticket) && isset($price_prom) && is_numeric($item->ticket) && is_numeric($price_prom) && $item_out->ticket > ($price_prom + 2)) {
							// 	$target1_flag = 'PROMO NO APPLIED';
							// }
							$cust_store_code = $item->cust_store_code;
							if (empty($cust_store_code) || $cust_store_code == 0) {
								$names = explode(" ", $item->cust_store_name);
								if (count($names) > 1) {
									$cust_store_code = $names[1];
								}
							}
							$past_sellout_data[] = [
								'customer' 			=> $item->customer,
								'acct_gtm' 			=> $item->acct_gtm,
								'customer_model'	=> $item->customer_model,
								'model_code' 		=> $item->model_suffix_code,
								'txn_date'			=> $past_date,
								'cust_store_code' 	=> $cust_store_code,
								'cust_store_name' 	=> $item->cust_store_name,
								'sellout_unit' 		=> $item->sellout_unit ?? 0,
								'sellout_amt' 		=> $item->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag' 		=> $target1_flag,
							];
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer' 			=> $customer,
					'acct_gtm' 			=> $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'	=> $first_past_item_base['customer_model'] ?? null,
					'model_code' 		=> $model,
					'txn_date' 			=> $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock' 			=> $past_stock_sum,
					'cust_store_code' 	=> null,
					'cust_store_name' 	=> null,
					'sellout_unit' 		=> null,
					'sellout_amt' 		=> null,
					'ticket' 			=> null,
					'promo1_price'		=> $price_prom,
					'target1_flag' 		=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					$a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// basicas , mixtas, additional, bundle
		$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}
	
	public function load_sell_out_v3() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field' 	=> 'model_suffix_code',
			'values' 	=> $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			//$priority = [];
			$var_prom = [];
			$current_date = '';
			if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'start_date', 'end_date', 'seq'], ['model' => $model]);
				if (!empty($culculate_price_promotions) && $culculate_price_promotions[0]->model === $model) {
					$price_prom = $culculate_price_promotions[0]->price_promotion;
				}
			}
			
			//echo '<pre>'; print_r($var_promo); 
			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];
					
					// if ($organized_data[$model][$current_date]->model === $var_promo[$model]){
						
					// }
					
					
					foreach ($organized_data[$model][$current_date] as $item_out) {
						
						$base_data = [
							'customer' 			=> $item_out->customer,
							'acct_gtm' 			=> $item_out->acct_gtm,
							'customer_model'	=> $item_out->customer_model,
							'model_code'		=> $item_out->model_suffix_code,
							'txn_date' 			=> $item_out->txn_date,
						];

						$target1_flag = $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED';
						if (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false || stripos($item_out->cust_store_name, 'VIRTUAL') !== false) {
							$target1_flag = 'PROMO APPLIED WEB';
						}

						
						$cust_store_code = $item_out->cust_store_code;
						if (empty($cust_store_code) || $cust_store_code == 0) {
							$names = explode(" ", $item_out->cust_store_name);
							if (count($names) > 1) {
								$cust_store_code = $names[1];
							}
						}

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}
						
						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_records[] = $base_data + [
								'cust_store_code' 	=> $cust_store_code,
								'cust_store_name' 	=> $item_out->cust_store_name,
								'sellout_unit' 		=> $item_out->sellout_unit ?? 0,
								'sellout_amt' 		=> $item_out->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item_out->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag'		=> $target1_flag,
								'promo2_price'		=> null,
								'target2_flag'		=> null,
								'promo3_price'		=> null,
								'target3_flag'		=> null,
								'promo4_price'		=> null,
								'target4_flag'		=> null,
								'promo5_price'		=> null,
								'target5_flag'		=> null,
								'promo6_price'		=> null,
								'target6_flag'		=> null,
								'promo7_price'		=> null,
								'target7_flag'		=> null,
								'promo8_price'		=> null,
								'target8_flag'		=> null,
								'promo9_price'		=> null,
								'target9_flag'		=> null,
								'promo10_price'		=> null,
								'target10_flag'		=> null,
							];
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$current_day_records[] = $first_item_base + [
							'cust_store_code' 		=> null,
							'cust_store_name' 		=> null,
							'sellout_unit' 			=> null,
							'sellout_amt' 			=> null,
							'stock' 				=> $daily_stock_sum,
							'ticket' 				=> null,
							'promo1_price'			=> $price_prom,
							'target1_flag' 			=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
							'promo2_price'			=> null,
							'target2_flag'			=> null,
							'promo3_price'			=> null,
							'target3_flag'			=> null,
							'promo4_price'			=> null,
							'target4_flag'			=> null,
							'promo5_price'			=> null,
							'target5_flag'			=> null,
							'promo6_price'			=> null,
							'target6_flag'			=> null,
							'promo7_price'			=> null,
							'target7_flag'			=> null,
							'promo8_price'			=> null,
							'target8_flag'			=> null,
							'promo9_price'			=> null,
							'target9_flag'			=> null,
							'promo10_price'			=> null,
							'target10_flag'			=> null,
						];
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer' 			=> $item->customer,
									'acct_gtm' 			=> $item->acct_gtm,
									'customer_model'	=> $item->customer_model,
									'model_code' 		=> $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$target1_flag = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
							if (stripos($item->cust_store_name, 'WEB') !== false || stripos($item->cust_store_name, 'web') !== false) {
								$target1_flag = 'PROMO APPLIED WEB';
							}
							// if (isset($item->ticket) && isset($price_prom) && is_numeric($item->ticket) && is_numeric($price_prom) && $item_out->ticket > ($price_prom + 2)) {
							// 	$target1_flag = 'PROMO NO APPLIED';
							// }
							$cust_store_code = $item->cust_store_code;
							if (empty($cust_store_code) || $cust_store_code == 0) {
								$names = explode(" ", $item->cust_store_name);
								if (count($names) > 1) {
									$cust_store_code = $names[1];
								}
							}
							$past_sellout_data[] = [
								'customer' 			=> $item->customer,
								'acct_gtm' 			=> $item->acct_gtm,
								'customer_model'	=> $item->customer_model,
								'model_code' 		=> $item->model_suffix_code,
								'txn_date'			=> $past_date,
								'cust_store_code' 	=> $cust_store_code,
								'cust_store_name' 	=> $item->cust_store_name,
								'sellout_unit' 		=> $item->sellout_unit ?? 0,
								'sellout_amt' 		=> $item->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag' 		=> $target1_flag,
								'promo2_price'		=> null,
								'target2_flag'		=> null,
								'promo3_price'		=> null,
								'target3_flag'		=> null,
								'promo4_price'		=> null,
								'target4_flag'		=> null,
								'promo5_price'		=> null,
								'target5_flag'		=> null,
								'promo6_price'		=> null,
								'target6_flag'		=> null,
								'promo7_price'		=> null,
								'target7_flag'		=> null,
								'promo8_price'		=> null,
								'target8_flag'		=> null,
								'promo9_price'		=> null,
								'target9_flag'		=> null,
								'promo10_price'		=> null,
								'target10_flag'		=> null,
							];
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer' 			=> $customer,
					'acct_gtm' 			=> $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'	=> $first_past_item_base['customer_model'] ?? null,
					'model_code' 		=> $model,
					'txn_date' 			=> $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock' 			=> $past_stock_sum,
					'cust_store_code' 	=> null,
					'cust_store_name' 	=> null,
					'sellout_unit' 		=> null,
					'sellout_amt' 		=> null,
					'ticket' 			=> null,
					'promo1_price'		=> $price_prom,
					'target1_flag' 		=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
					'promo2_price'		=> null,
					'target2_flag'		=> null,
					'promo3_price'		=> null,
					'target3_flag'		=> null,
					'promo4_price'		=> null,
					'target4_flag'		=> null,
					'promo5_price'		=> null,
					'target5_flag'		=> null,
					'promo6_price'		=> null,
					'target6_flag'		=> null,
					'promo7_price'		=> null,
					'target7_flag'		=> null,
					'promo8_price'		=> null,
					'target8_flag'		=> null,
					'promo9_price'		=> null,
					'target9_flag'		=> null,
					'promo10_price'		=> null,
					'target10_flag'		=> null,
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					$a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// Verificar dias previos para modelos de bundles mal mapeado
		// Falta agregar para las demas columnas de la hoja sellout como las colmnas de target1flag  y ver logica para las otras columnas
		// Inserción por lotes
		// Tener en cuenta para los casos de promo no applied cuando ticket es mayor a promo1_price (cuidado, se puede redondear y aun cumplicar con ser menor o sea 400 a 399 masimo 2 numeros mas)
		$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}

	public function load_sell_out_v4() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field' 	=> 'model_suffix_code',
			'values' 	=> $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			$promotions_by_type = [
				'basica' => [],
				'adicional' => [],
				'mixta' => [],
				'bundle' => [],
			];

			if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'start_date', 'end_date'], ['model' => $model]);

				if (!empty($culculate_price_promotions)) {
					$basic_ranges = [];
					$first_basic_found = false;
					$second_basic_found = false;

					// Identificar promociones básicas
					foreach ($culculate_price_promotions as $promotion) {
						$mes_inicio = date('Y-m-01', strtotime($promotion->start_date));
						$mes_fin = date('Y-m-t', strtotime($promotion->start_date));

						if (date('d', strtotime($promotion->start_date)) == '01' && !$first_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$first_basic_found = true;
						} elseif (date('d', strtotime($promotion->end_date)) == date('t', strtotime($promotion->start_date)) && !$second_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$second_basic_found = true;
						}
					}

					// Identificar las demás promociones
					foreach ($culculate_price_promotions as $promotion) {
						if (!in_array($promotion, $promotions_by_type['basica'])) {
							$is_additional = false;
							$is_mixta = false;
							$is_bundle = false;
							$mes_inicio_promo = date('Y-m-01', strtotime($promotion->start_date));
							$mes_fin_promo = date('Y-m-t', strtotime($promotion->start_date));

							// Es bundle?
							if ($promotion->start_date == $mes_inicio_promo && $promotion->end_date == $mes_fin_promo) {
								$is_bundle = true;
							} else {
								// Es adicional?
								foreach ($basic_ranges as $range) {
									if (strtotime($promotion->start_date) >= strtotime($range['start']) && strtotime($promotion->end_date) <= strtotime($range['end'])) {
										$is_additional = true;
										break;
									}
								}

								// Es mixta?
								if (!$is_additional && count($basic_ranges) == 2) {
									$start_in_basic1 = strtotime($promotion->start_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[0]['end']);
									$end_in_basic2 = strtotime($promotion->end_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[1]['end']);
									$start_in_basic2 = strtotime($promotion->start_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[1]['end']);
									$end_in_basic1 = strtotime($promotion->end_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[0]['end']);

									if (($start_in_basic1 && $end_in_basic2) || ($start_in_basic2 && $end_in_basic1)) {
										$is_mixta = true;
									}
								}
							}

							if ($is_bundle) {
								$promotions_by_type['bundle'][] = $promotion;
							} elseif ($is_mixta) {
								$promotions_by_type['mixta'][] = $promotion;
							} elseif ($is_additional) {
								$promotions_by_type['adicional'][] = $promotion;
							}
						}
					}
				}
			}

			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' => $item_out->customer,
							'acct_gtm' => $item_out->acct_gtm,
							'customer_model' => $item_out->customer_model,
							'model_code' => $item_out->model_suffix_code,
							'txn_date' => $item_out->txn_date,
						];

						$target_flag_base = $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED';
						if (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false || stripos($item_out->cust_store_name, 'VIRTUAL') !== false) {
							$target_flag_base = 'PROMO APPLIED WEB';
						}

						$cust_store_code = $item_out->cust_store_code;
						if (empty($cust_store_code) || $cust_store_code == 0) {
							$names = explode(" ", $item_out->cust_store_name);
							if (count($names) > 1) {
								$cust_store_code = $names[1];
							}
						}

						// Inicializar arrays para promo prices y target flags
						$promo_prices = array_fill_keys(range(1, 10), null);
						$target_flags = array_fill_keys(range(1, 10), null);

						$promo_index = 1;
						// Primero las básicas
						if (!empty($promotions_by_type['basica'])) {
							$promo_prices[$promo_index] = $promotions_by_type['basica'][0]->price_promotion ?? null;
							$target_flags[$promo_index] = $target_flag_base;
							$promo_index++;
						}

						// Agrupar y tomar un representante de adicional, mixta y bundle por price_promotion
						$other_promotions = array_merge(
							$promotions_by_type['adicional'],
							$promotions_by_type['mixta'],
							$promotions_by_type['bundle']
						);

						$unique_other_promotions = [];
						$seen_prices = [];
						foreach ($other_promotions as $promotion) {
							if (!in_array($promotion->price_promotion, $seen_prices)) {
								$unique_other_promotions[] = $promotion;
								$seen_prices[] = $promotion->price_promotion;
							}
						}

						foreach ($unique_other_promotions as $promotion) {
							if ($promo_index <= 10) {
								$promo_prices[$promo_index] = $promotion->price_promotion;
								$target_flags[$promo_index] = $target_flag_base;
								$promo_index++;
							} else {
								break;
							}
						}

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}

						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_record = $base_data + [
								'cust_store_code' => $cust_store_code,
								'cust_store_name' => $item_out->cust_store_name,
								'sellout_unit' => $item_out->sellout_unit ?? 0,
								'sellout_amt' => $item_out->sellout_amt ?? 0,
								'stock' => null,
								'ticket' => $item_out->ticket,
							];
							// Merge promo prices and target flags
							for ($i = 1; $i <= 10; $i++) {
								$current_day_record['promo' . $i . '_price'] = $promo_prices[$i];
								$current_day_record['target' . $i . '_flag'] = $target_flags[$i];
							}
							$current_day_records[] = $current_day_record;
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$stock_record = $first_item_base + [
							'cust_store_code' => null,
							'cust_store_name' => null,
							'sellout_unit' => null,
							'sellout_amt' => null,
							'stock' => $daily_stock_sum,
							'ticket' => null,
						];
						for ($i = 1; $i <= 10; $i++) {
							$stock_record['promo' . $i . '_price'] = null;
							$stock_record['target' . $i . '_flag'] = ($i == 1) ? ($is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK') : null;
						}
						$current_day_records[] = $stock_record;
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			// ... (resto del código para el retroceso en fechas)



		

			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer' 			=> $item->customer,
									'acct_gtm' 			=> $item->acct_gtm,
									'customer_model'	=> $item->customer_model,
									'model_code' 		=> $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$target1_flag = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
							if (stripos($item->cust_store_name, 'WEB') !== false || stripos($item->cust_store_name, 'web') !== false) {
								$target1_flag = 'PROMO APPLIED WEB';
							}
							// if (isset($item->ticket) && isset($price_prom) && is_numeric($item->ticket) && is_numeric($price_prom) && $item_out->ticket > ($price_prom + 2)) {
							// 	$target1_flag = 'PROMO NO APPLIED';
							// }
							$cust_store_code = $item->cust_store_code;
							if (empty($cust_store_code) || $cust_store_code == 0) {
								$names = explode(" ", $item->cust_store_name);
								if (count($names) > 1) {
									$cust_store_code = $names[1];
								}
							}
							$past_sellout_data[] = [
								'customer' 			=> $item->customer,
								'acct_gtm' 			=> $item->acct_gtm,
								'customer_model'	=> $item->customer_model,
								'model_code' 		=> $item->model_suffix_code,
								'txn_date'			=> $past_date,
								'cust_store_code' 	=> $cust_store_code,
								'cust_store_name' 	=> $item->cust_store_name,
								'sellout_unit' 		=> $item->sellout_unit ?? 0,
								'sellout_amt' 		=> $item->sellout_amt ?? 0,
								'stock' 			=> null,
								'ticket' 			=> $item->ticket,
								'promo1_price'		=> $price_prom,
								'target1_flag' 		=> $target1_flag,
								'promo2_price'		=> null,
								'target2_flag'		=> null,
								'promo3_price'		=> null,
								'target3_flag'		=> null,
								'promo4_price'		=> null,
								'target4_flag'		=> null,
								'promo5_price'		=> null,
								'target5_flag'		=> null,
								'promo6_price'		=> null,
								'target6_flag'		=> null,
								'promo7_price'		=> null,
								'target7_flag'		=> null,
								'promo8_price'		=> null,
								'target8_flag'		=> null,
								'promo9_price'		=> null,
								'target9_flag'		=> null,
								'promo10_price'		=> null,
								'target10_flag'		=> null,
							];
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer' 			=> $customer,
					'acct_gtm' 			=> $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'	=> $first_past_item_base['customer_model'] ?? null,
					'model_code' 		=> $model,
					'txn_date' 			=> $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock' 			=> $past_stock_sum,
					'cust_store_code' 	=> null,
					'cust_store_name' 	=> null,
					'sellout_unit' 		=> null,
					'sellout_amt' 		=> null,
					'ticket' 			=> null,
					'promo1_price'		=> $price_prom,
					'target1_flag' 		=> $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
					'promo2_price'		=> null,
					'target2_flag'		=> null,
					'promo3_price'		=> null,
					'target3_flag'		=> null,
					'promo4_price'		=> null,
					'target4_flag'		=> null,
					'promo5_price'		=> null,
					'target5_flag'		=> null,
					'promo6_price'		=> null,
					'target6_flag'		=> null,
					'promo7_price'		=> null,
					'target7_flag'		=> null,
					'promo8_price'		=> null,
					'target8_flag'		=> null,
					'promo9_price'		=> null,
					'target9_flag'		=> null,
					'promo10_price'		=> null,
					'target10_flag'		=> null,
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					$a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// Verificar dias previos para modelos de bundles mal mapeado
		// Falta agregar para las demas columnas de la hoja sellout como las colmnas de target1flag  y ver logica para las otras columnas
		// Inserción por lotes
		// Tener en cuenta para los casos de promo no applied cuando ticket es mayor a promo1_price (cuidado, se puede redondear y aun cumplicar con ser menor o sea 400 a 399 masimo 2 numeros mas)
		$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}

	public function load_sell_out_v5() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field'     => 'model_suffix_code',
			'values'    => $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			$promotions_by_type = [
				'basica' => [],
				'adicional' => [],
				'mixta' => [],
				'bundle' => [],
			];

			if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'start_date', 'end_date'], ['model' => $model]);

				if (!empty($culculate_price_promotions)) {
					$basic_ranges = [];
					$first_basic_found = false;
					$second_basic_found = false;

					// Identificar promociones básicas
					foreach ($culculate_price_promotions as $promotion) {
						$mes_inicio = date('Y-m-01', strtotime($promotion->start_date));
						$mes_fin = date('Y-m-t', strtotime($promotion->start_date));

						if (date('d', strtotime($promotion->start_date)) == '01' && !$first_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$first_basic_found = true;
						} elseif (date('d', strtotime($promotion->end_date)) == date('t', strtotime($promotion->start_date)) && !$second_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$second_basic_found = true;
						}
					}

					// Identificar las demás promociones
					foreach ($culculate_price_promotions as $promotion) {
						if (!in_array($promotion, $promotions_by_type['basica'])) {
							$is_additional = false;
							$is_mixta = false;
							$is_bundle = false;
							$mes_inicio_promo = date('Y-m-01', strtotime($promotion->start_date));
							$mes_fin_promo = date('Y-m-t', strtotime($promotion->start_date));

							// Es bundle?
							if ($promotion->start_date == $mes_inicio_promo && $promotion->end_date == $mes_fin_promo) {
								$is_bundle = true;
							} else {
								// Es adicional?
								foreach ($basic_ranges as $range) {
									if (strtotime($promotion->start_date) >= strtotime($range['start']) && strtotime($promotion->end_date) <= strtotime($range['end'])) {
										$is_additional = true;
										break;
									}
								}

								// Es mixta?
								if (!$is_additional && count($basic_ranges) == 2) {
									$start_in_basic1 = strtotime($promotion->start_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[0]['end']);
									$end_in_basic2 = strtotime($promotion->end_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[1]['end']);
									$start_in_basic2 = strtotime($promotion->start_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[1]['end']);
									$end_in_basic1 = strtotime($promotion->end_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[0]['end']);

									if (($start_in_basic1 && $end_in_basic2) || ($start_in_basic2 && $end_in_basic1)) {
										$is_mixta = true;
									}
								}
							}

							if ($is_bundle) {
								$promotions_by_type['bundle'][] = $promotion;
							} elseif ($is_mixta) {
								$promotions_by_type['mixta'][] = $promotion;
							} elseif ($is_additional) {
								$promotions_by_type['adicional'][] = $promotion;
							}
						}
					}
				}
			}

			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' => $item_out->customer,
							'acct_gtm' => $item_out->acct_gtm,
							'customer_model' => $item_out->customer_model,
							'model_code' => $item_out->model_suffix_code,
							'txn_date' => $item_out->txn_date,
						];

						$target_flag_base = $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED';
						if (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false || stripos($item_out->cust_store_name, 'VIRTUAL') !== false) {
							$target_flag_base = 'PROMO APPLIED WEB';
						}

						$cust_store_code = $item_out->cust_store_code;
						if (empty($cust_store_code) || $cust_store_code == 0) {
							$names = explode(" ", $item_out->cust_store_name);
							if (count($names) > 1) {
								$cust_store_code = $names[1];
							}
						}

						// Inicializar arrays para promo prices y target flags
						$promo_prices = array_fill_keys(range(1, 10), null);
						$target_flags = array_fill_keys(range(1, 10), null);

						$promo_index = 1;
						// Primero las básicas
						if (!empty($promotions_by_type['basica'])) {
							$promo_prices[$promo_index] = $promotions_by_type['basica'][0]->price_promotion ?? null;
							$target_flags[$promo_index] = $target_flag_base;
							$promo_index++;
						}

						// Llenar para adicional, mixta y bundle
						$adicionales = $promotions_by_type['adicional'];
						$mixtas = $promotions_by_type['mixta'];
						$bundles = $promotions_by_type['bundle'];
						$num_adicionales = count($adicionales);
						$num_mixtas = count($mixtas);
						$num_bundles = count($bundles);
						$max_promos = max($num_adicionales, $num_mixtas, $num_bundles);

						for ($i = 0; $i < $max_promos; $i++) {
							 if ($promo_index <= 10) {
								if (isset($adicionales[$i])) {
									$promo_prices[$promo_index] = $adicionales[$i]->price_promotion;
									 if (isset($item_out->ticket) && isset($adicionales[$i]->price_promotion)) {
										$relacion = round($item_out->ticket / $adicionales[$i]->price_promotion, 2);
										$target_flags[$promo_index] = ($relacion <= 1) ? $target_flag_base : 'PROMO NO APPLIED';
									} else {
										 $target_flags[$promo_index] = $target_flag_base;
									}
									$promo_index++;
								}
							}
							if ($promo_index <= 10) {
								if (isset($mixtas[$i])) {
									 $promo_prices[$promo_index] = $mixtas[$i]->price_promotion;
									  if (isset($item_out->ticket) && isset($mixtas[$i]->price_promotion)) {
											$relacion = round($item_out->ticket / $mixtas[$i]->price_promotion, 2);
											$target_flags[$promo_index] = ($relacion <= 1) ? $target_flag_base : 'PROMO NO APPLIED';
										} else {
											 $target_flags[$promo_index] = $target_flag_base;
										}
									$promo_index++;
								}
							}
							if ($promo_index <= 10) {
								if (isset($bundles[$i])) {
									$promo_prices[$promo_index] = $bundles[$i]->price_promotion;
									 if (isset($item_out->ticket) && isset($bundles[$i]->price_promotion)) {
										$relacion = round($item_out->ticket / $bundles[$i]->price_promotion, 2);
										$target_flags[$promo_index] = ($relacion <= 1) ? $target_flag_base : 'PROMO NO APPLIED';
									} else {
										 $target_flags[$promo_index] = $target_flag_base;
									}
									$promo_index++;
								}
							}
						}

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}

						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_record = $base_data + [
								'cust_store_code' => $cust_store_code,
								'cust_store_name' => $item_out->cust_store_name,
								'sellout_unit' => $item_out->sellout_unit ?? 0,
								'sellout_amt' => $item_out->sellout_amt ?? 0,
								'stock' => null,
								'ticket' => $item_out->ticket,
							];
							// Merge promo prices and target flags
							for ($i = 1; $i <= 10; $i++) {
								$current_day_record['promo' . $i . '_price'] = $promo_prices[$i];
								$current_day_record['target' . $i . '_flag'] = $target_flags[$i];
							}
							$current_day_records[] = $current_day_record;
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$stock_record = $first_item_base + [
							'cust_store_code' => null,
							'cust_store_name' => null,
							'sellout_unit' => null,
							'sellout_amt' => null,
							'stock' => $daily_stock_sum,
							'ticket' => null,
						];
						for ($i = 1; $i <= 10; $i++) {
							$stock_record['promo' . $i . '_price'] = null;
							$stock_record['target' . $i . '_flag'] = ($i == 1) ? ($is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK') : null;
						}
						$current_day_records[] = $stock_record;
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			// ... (resto del código para el retroceso en fechas)




			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer'             => $item->customer,
									'acct_gtm'             => $item->acct_gtm,
									'customer_model'    => $item->customer_model,
									'model_code'         => $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$target1_flag = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
							if (stripos($item->cust_store_name, 'WEB') !== false || stripos($item->cust_store_name, 'web') !== false) {
								$target1_flag = 'PROMO APPLIED WEB';
							}
							$cust_store_code = $item->cust_store_code;
							if (empty($cust_store_code) || $cust_store_code == 0) {
								$names = explode(" ", $item->cust_store_name);
								if (count($names) > 1) {
									$cust_store_code = $names[1];
								}
							}
							$past_sellout_data[] = [
								'customer'             => $item->customer,
								'acct_gtm'             => $item->acct_gtm,
								'customer_model'    => $item->customer_model,
								'model_code'         => $item->model_suffix_code,
								'txn_date'            => $past_date,
								'cust_store_code'     => $cust_store_code,
								'cust_store_name'     => $item->cust_store_name,
								'sellout_unit'         => $item->sellout_unit ?? 0,
								'sellout_amt'         => $item->sellout_amt ?? 0,
								'stock'             => null,
								'ticket'             => $item->ticket,
								'promo1_price'        => $price_prom,
								'target1_flag'         => $target1_flag,
								'promo2_price'        => null,
								'target2_flag'        => null,
								'promo3_price'        => null,
								'target3_flag'        => null,
								'promo4_price'        => null,
								'target4_flag'        => null,
								'promo5_price'        => null,
								'target5_flag'        => null,
								'promo6_price'        => null,
								'target6_flag'        => null,
								'promo7_price'        => null,
								'target7_flag'        => null,
								'promo8_price'        => null,
								'target8_flag'        => null,
								'promo9_price'        => null,
								'target9_flag'        => null,
								'promo10_price'        => null,
								'target10_flag'        => null,
							];
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer'             => $customer,
					'acct_gtm'             => $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'    => $first_past_item_base['customer_model'] ?? null,
					'model_code'         => $model,
					'txn_date'             => $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock'             => $past_stock_sum,
					'cust_store_code'     => null,
					'cust_store_name'     => null,
					'sellout_unit'         => null,
					'sellout_amt'         => null,
					'ticket'             => null,
					'promo1_price'        => $price_prom,
					'target1_flag'         => $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
					'promo2_price'        => null,
					'target2_flag'        => null,
					'promo3_price'        => null,
					'target3_flag'        => null,
					'promo4_price'        => null,
					'target4_flag'        => null,
					'promo5_price'        => null,
					'target5_flag'        => null,
					'promo6_price'        => null,
					'target6_flag'        => null,
					'promo7_price'        => null,
					'target7_flag'        => null,
					'promo8_price'        => null,
					'target8_flag'        => null,
					'promo9_price'        => null,
					'target9_flag'        => null,
					'promo10_price'        => null,
					'target10_flag'        => null,
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					$a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// Verificar dias previos para modelos de bundles mal mapeado
		// Falta agregar para las demas columnas de la hoja sellout como las colmnas de target1flag     y ver logica para las otras columnas
		// Inserción por lotes
		// Tener en cuenta para los casos de promo no applied cuando ticket es mayor a promo1_price (cuidado, se puede redondear y aun cumplicar con ser menor o sea 400 a 399 masimo 2 numeros mas)
		$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}

	public function load_sell_out_v6() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field'     => 'model_suffix_code',
			'values'    => $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			$promotions_by_type = [
				'basica' => [],
				'adicional' => [],
				'mixta' => [],
				'bundle' => [],
			];

			if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'start_date', 'end_date'], ['model' => $model]);

				if (!empty($culculate_price_promotions)) {
					$basic_ranges = [];
					$first_basic_found = false;
					$second_basic_found = false;

					// Identificar promociones básicas
					foreach ($culculate_price_promotions as $promotion) {
						$mes_inicio = date('Y-m-01', strtotime($promotion->start_date));
						$mes_fin = date('Y-m-t', strtotime($promotion->start_date));

						if (date('d', strtotime($promotion->start_date)) == '01' && !$first_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$first_basic_found = true;
						} elseif (date('d', strtotime($promotion->end_date)) == date('t', strtotime($promotion->start_date)) && !$second_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$second_basic_found = true;
						}
					}

					// Identificar las demás promociones
					foreach ($culculate_price_promotions as $promotion) {
						if (!in_array($promotion, $promotions_by_type['basica'])) {
							$is_additional = false;
							$is_mixta = false;
							$is_bundle = false;
							$mes_inicio_promo = date('Y-m-01', strtotime($promotion->start_date));
							$mes_fin_promo = date('Y-m-t', strtotime($promotion->start_date));

							// Es bundle?
							if ($promotion->start_date == $mes_inicio_promo && $promotion->end_date == $mes_fin_promo) {
								$is_bundle = true;
							} else {
								// Es adicional?
								foreach ($basic_ranges as $range) {
									if (strtotime($promotion->start_date) >= strtotime($range['start']) && strtotime($promotion->end_date) <= strtotime($range['end'])) {
										$is_additional = true;
										break;
									}
								}

								// Es mixta?
								if (!$is_additional && count($basic_ranges) == 2) {
									$start_in_basic1 = strtotime($promotion->start_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[0]['end']);
									$end_in_basic2 = strtotime($promotion->end_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[1]['end']);
									$start_in_basic2 = strtotime($promotion->start_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[1]['end']);
									$end_in_basic1 = strtotime($promotion->end_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[0]['end']);

									if (($start_in_basic1 && $end_in_basic2) || ($start_in_basic2 && $end_in_basic1)) {
										$is_mixta = true;
									}
								}
							}

							if ($is_bundle) {
								$promotions_by_type['bundle'][] = $promotion;
							} elseif ($is_mixta) {
								$promotions_by_type['mixta'][] = $promotion;
							} elseif ($is_additional) {
								$promotions_by_type['adicional'][] = $promotion;
							}
						}
					}
				}
			}

			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' => $item_out->customer,
							'acct_gtm' => $item_out->acct_gtm,
							'customer_model' => $item_out->customer_model,
							'model_code' => $item_out->model_suffix_code,
							'txn_date' => $item_out->txn_date,
						];

						$target_flag_base = $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED';
						if (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false || stripos($item_out->cust_store_name, 'VIRTUAL') !== false) {
							$target_flag_base = 'PROMO APPLIED WEB';
						}

						$cust_store_code = $item_out->cust_store_code;
						if (empty($cust_store_code) || $cust_store_code == 0) {
							$names = explode(" ", $item_out->cust_store_name);
							if (count($names) > 1) {
								$cust_store_code = $names[1];
							}
						}

						// Inicializar arrays para promo prices y target flags
						$promo_prices = array_fill_keys(range(1, 10), null);
						$target_flags = array_fill_keys(range(1, 10), null);

						$promo_index = 1;
						// Primero las básicas
						if (!empty($promotions_by_type['basica'])) {
							$promo_prices[$promo_index] = $promotions_by_type['basica'][0]->price_promotion ?? null;
							$target_flags[$promo_index] = $target_flag_base;
							$promo_index++;
						}

						// Agrupar promociones por precio para adicional, mixta y bundle
						$other_promotions = array_merge(
							$promotions_by_type['adicional'],
							$promotions_by_type['mixta'],
							$promotions_by_type['bundle']
						);

						$unique_prices = [];
						$unique_promotions = [];
						foreach ($other_promotions as $promotion) {
							if (!in_array($promotion->price_promotion, $unique_prices)) {
								$unique_prices[] = $promotion->price_promotion;
								$unique_promotions[] = $promotion; // Guarda la primera promo con ese precio
							}
						}

						// Asignar precios y flags
						foreach ($unique_promotions as $promotion) {
							if ($promo_index <= 10) {
								$promo_prices[$promo_index] = $promotion->price_promotion;
								if (isset($item_out->ticket) && isset($promotion->price_promotion)) {
									$relacion = round($item_out->ticket / $promotion->price_promotion, 2);
									$target_flags[$promo_index] = ($relacion <= 1) ? $target_flag_base : 'PROMO NO APPLIED';
								} else {
									$target_flags[$promo_index] = $target_flag_base;
								}
								$promo_index++;
							} else {
								break;
							}
						}

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}

						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_record = $base_data + [
								'cust_store_code' => $cust_store_code,
								'cust_store_name' => $item_out->cust_store_name,
								'sellout_unit' => $item_out->sellout_unit ?? 0,
								'sellout_amt' => $item_out->sellout_amt ?? 0,
								'stock' => null,
								'ticket' => $item_out->ticket,
							];
							// Merge promo prices and target flags
							for ($i = 1; $i <= 10; $i++) {
								$current_day_record['promo' . $i . '_price'] = $promo_prices[$i];
								$current_day_record['target' . $i . '_flag'] = $target_flags[$i];
							}
							$current_day_records[] = $current_day_record;
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$stock_record = $first_item_base + [
							'cust_store_code' => null,
							'cust_store_name' => null,
							'sellout_unit' => null,
							'sellout_amt' => null,
							'stock' => $daily_stock_sum,
							'ticket' => null,
						];
						for ($i = 1; $i <= 10; $i++) {
							$stock_record['promo' . $i . '_price'] = null;
							$stock_record['target' . $i . '_flag'] = ($i == 1) ? ($is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK') : null;
						}
						$current_day_records[] = $stock_record;
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer'             => $item->customer,
									'acct_gtm'             => $item->acct_gtm,
									'customer_model'    => $item->customer_model,
									'model_code'         => $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$target1_flag = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
							if (stripos($item->cust_store_name, 'WEB') !== false || stripos($item->cust_store_name, 'web') !== false) {
								$target1_flag = 'PROMO APPLIED WEB';
							}
							$cust_store_code = $item->cust_store_code;
							if (empty($cust_store_code) || $cust_store_code == 0) {
								$names = explode(" ", $item->cust_store_name);
								if (count($names) > 1) {
									$cust_store_code = $names[1];
								}
							}
							$past_sellout_data[] = [
								'customer'             => $item->customer,
								'acct_gtm'             => $item->acct_gtm,
								'customer_model'    => $item->customer_model,
								'model_code'         => $item->model_suffix_code,
								'txn_date'            => $past_date,
								'cust_store_code'     => $cust_store_code,
								'cust_store_name'     => $item->cust_store_name,
								'sellout_unit'         => $item->sellout_unit ?? 0,
								'sellout_amt'         => $item->sellout_amt ?? 0,
								'stock'             => null,
								'ticket'             => $item->ticket,
								'promo1_price'        => $price_prom,
								'target1_flag'         => $target1_flag,
								'promo2_price'        => null,
								'target2_flag'        => null,
								'promo3_price'        => null,
								'target3_flag'        => null,
								'promo4_price'        => null,
								'target4_flag'        => null,
								'promo5_price'        => null,
								'target5_flag'        => null,
								'promo6_price'        => null,
								'target6_flag'        => null,
								'promo7_price'        => null,
								'target7_flag'        => null,
								'promo8_price'        => null,
								'target8_flag'        => null,
								'promo9_price'        => null,
								'target9_flag'        => null,
								'promo10_price'        => null,
								'target10_flag'        => null,
							];
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer'             => $customer,
					'acct_gtm'             => $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'    => $first_past_item_base['customer_model'] ?? null,
					'model_code'         => $model,
					'txn_date'             => $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock'             => $past_stock_sum,
					'cust_store_code'     => null,
					'cust_store_name'     => null,
					'sellout_unit'         => null,
					'sellout_amt'         => null,
					'ticket'             => null,
					'promo1_price'        => $price_prom,
					'target1_flag'         => $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
					'promo2_price'        => null,
					'target2_flag'        => null,
					'promo3_price'        => null,
					'target3_flag'        => null,
					'promo4_price'        => null,
					'target4_flag'        => null,
					'promo5_price'        => null,
					'target5_flag'        => null,
					'promo6_price'        => null,
					'target6_flag'        => null,
					'promo7_price'        => null,
					'target7_flag'        => null,
					'promo8_price'        => null,
					'target8_flag'        => null,
					'promo9_price'        => null,
					'target9_flag'        => null,
					'promo10_price'        => null,
					'target10_flag'        => null,
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					$a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// Verificar dias previos para modelos de bundles mal mapeado
		// Falta agregar para las demas columnas de la hoja sellout como las colmnas de target1flag     y ver logica para las otras columnas
		// Inserción por lotes
		// Tener en cuenta para los casos de promo no applied cuando ticket es mayor a promo1_price (cuidado, se puede redondear y aun cumplicar con ser menor o sea 400 a 399 masimo 2 numeros mas)
		$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}

	public function load_sell_out() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);

		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);

		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);

		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);

		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];
		$is_orient = false;
		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field'     => 'model_suffix_code',
			'values'    => $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}

		foreach ($all_models as $model) {
			$price_prom = '';
			$is_bundle_model = in_array($model, $models_bundles);
			$promotions_by_type = [
				'basica' => [],
				'adicional' => [],
				'mixta' => [],
				'bundle' => [],
			];

			//if (!$is_bundle_model) {
				$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'start_date', 'end_date'], ['model' => $model]);

				if (!empty($culculate_price_promotions)) {
					$basic_ranges = [];
					$first_basic_found = false;
					$second_basic_found = false;
					
					// Identificar promociones básicas
					foreach ($culculate_price_promotions as $promotion) {
						$mes_inicio = date('Y-m-01', strtotime($promotion->start_date));
						$mes_fin = date('Y-m-t', strtotime($promotion->start_date));

						if (date('d', strtotime($promotion->start_date)) == '01' && !$first_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$first_basic_found = true;
						} elseif (date('d', strtotime($promotion->end_date)) == date('t', strtotime($promotion->start_date)) && !$second_basic_found) {
							$promotions_by_type['basica'][] = $promotion;
							$basic_ranges[] = ['start' => $promotion->start_date, 'end' => $promotion->end_date];
							$second_basic_found = true;
						}
					}
					
					// Identificar las demás promociones
					foreach ($culculate_price_promotions as $promotion) {
						if (!in_array($promotion, $promotions_by_type['basica'])) {
							$is_additional = false;
							$is_mixta = false;
							$is_bundle = false;
							$mes_inicio_promo = date('Y-m-01', strtotime($promotion->start_date));
							$mes_fin_promo = date('Y-m-t', strtotime($promotion->start_date));

							// Es bundle?
							if ($promotion->start_date == $mes_inicio_promo && $promotion->end_date == $mes_fin_promo) {
								$is_bundle = true;
							} else {
								// Es adicional?
								foreach ($basic_ranges as $range) {
									if (strtotime($promotion->start_date) >= strtotime($range['start']) && strtotime($promotion->end_date) <= strtotime($range['end'])) {
										$is_additional = true;
										break;
									}
								}

								// Es mixta?
								if (!$is_additional && count($basic_ranges) == 2) {
									$start_in_basic1 = strtotime($promotion->start_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->start_date) <= strtotime($basic_ranges[0]['end']);
									$end_in_basic2 = strtotime($promotion->end_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[1]['end']);
									$start_in_basic2 = strtotime($promotion->start_date) >= strtotime($basic_ranges[1]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[1]['end']);
									$end_in_basic1 = strtotime($promotion->end_date) >= strtotime($basic_ranges[0]['start']) && strtotime($promotion->end_date) <= strtotime($basic_ranges[0]['end']);

									if (($start_in_basic1 && $end_in_basic2) || ($start_in_basic2 && $end_in_basic1)) {
										$is_mixta = true;
									}
								}
							}

							if ($is_bundle) {
								$promotions_by_type['bundle'][] = $promotion;
							} elseif ($is_mixta) {
								$promotions_by_type['mixta'][] = $promotion;
							} elseif ($is_additional) {
								$promotions_by_type['adicional'][] = $promotion;
							}
						}
					}
				} elseif ($is_bundle_model === true){
					$culculate_price_promotions_bundles = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'start_date', 'end_date'], ['gift' => $model]);
					foreach ($culculate_price_promotions_bundles as $promotion) {
						$promotions_by_type['bundle'][] = $promotion;
					}
					
				}					
				//echo '<pre>'; print_r($promotions_by_type);
			//}

			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$daily_stock_sum = 0;
					$first_item_base = null;
					$has_nonzero_sellout = false;
					$current_day_records = [];

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' => $item_out->customer,
							'acct_gtm' => $item_out->acct_gtm,
							'customer_model' => $item_out->customer_model,
							'model_code' => $item_out->model_suffix_code,
							'txn_date' => $item_out->txn_date,
						];
						
						$target_flag_base = $is_bundle_model ? 'BUNDLE' : 'PROMO APPLIED';
						// if (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false || stripos($item_out->cust_store_name, 'VIRTUAL') !== false) {
							// $target_flag_base = 'PROMO APPLIED WEB';
						// }

						$cust_store_code = $item_out->cust_store_code;
						if (empty($cust_store_code) || $cust_store_code == 0) {
							$names = explode(" ", $item_out->cust_store_name);
							if (count($names) > 1) {
								$cust_store_code = $names[1];
								if (!empty($names[2])){
									$cust_store_code = $cust_store_code . " " . $names[2];
								}
							}
						}

						// Inicializar arrays para promo prices y target flags
						$promo_prices = array_fill_keys(range(1, 10), null);
						$target_flags = array_fill_keys(range(1, 10), null);
						$stores_orient = ['IQUITOS', 'TINGO MARIA'. 'TARAPOTO', 'MOYOBAMBA', 'PUCALLPA', 'YURIMAGUAS', 'JAEN', 'JUANJUI'];
						$promo_index = 1;
						// Primero las básicas
						if (!empty($promotions_by_type['basica'])) {
							$promo_prices[$promo_index] = $promotions_by_type['basica'][0]->price_promotion ?? null;
							// $relacion = isset($item_out->ticket) && isset($promotions_by_type['basica'][0]->price_promotion)
								// ? ($item_out->ticket * 1.15) / $promotions_by_type['basica'][0]->price_promotion
								// : 0; // Default value if ticket or price is missing
							
							foreach ($stores_orient as $store) {
								if (strpos($item_out->cust_store_name, $store) !== false) {
									$is_orient = true;
									break;
								} else $is_orient = false;
							}	
							if ($is_orient === true){
								$relacion = isset($item_out->ticket) && isset($promotions_by_type['basica'][0]->price_promotion) && $item_out->ticket != 0 ? $item_out->sellout_amt <= $promotions_by_type['basica'][0]->price_promotion : 0; // Default value if ticket or price is missing
								$target_flag_base = ($relacion === true) ? 'PROMO APPLIED' : 'PROMO NO APPLIED';
								//echo '<pre>'; print_r([$item_out->model_suffix_code, $item_out->cust_store_name,$item_out->sellout_amt,$item_out->ticket]);
							}else{							
								// $relacion = isset($item_out->ticket) && isset($promotions_by_type['basica'][0]->price_promotion) && $item_out->ticket != 0
									// ? round($promotions_by_type['basica'][0]->price_promotion / ($item_out->ticket), 2)
									// : 0; // Default value if ticket or price is missing
								// $target_flag_base = ($relacion >= 0.98) ? 'PROMO APPLIED' : 'PROMO NO APPLIED';
								
								$relacion = isset($item_out->ticket) && isset($promotions_by_type['basica'][0]->price_promotion) && $item_out->ticket != 0 ? (int)$item_out->ticket <= $promotions_by_type['basica'][0]->price_promotion : 0; // Default value if ticket or price is missing
								$target_flag_base = ($relacion === true) ? 'PROMO APPLIED' : 'PROMO NO APPLIED';
							}
							
							// $relacion = isset($item_out->ticket) && isset($promotions_by_type['basica'][0]->price_promotion) && $item_out->ticket != 0
								// ? (int)$promotions_by_type['basica'][0]->price_promotion >= ($item_out->ticket) 
								// : 0; // Default value if ticket or price is missing
							//$target_flag_base = ($relacion >= 0.98) ? 'PROMO APPLIED' : 'PROMO NO APPLIED';
							
							if ($item_out->ticket == 0) $target_flag_base = 'PROMO APPLIED';
							
							if (($relacion >= 0.98) && (stripos($item_out->cust_store_name, 'WEB') !== false || stripos($item_out->cust_store_name, 'web') !== false || stripos($item_out->cust_store_name, 'b2b2c') !== false || stripos($item_out->cust_store_name, 'VIRTUAL') !== false)) {
								$target_flag_base = 'PROMO APPLIED WEB';
							}
							$target_flags[$promo_index] = $target_flag_base;
							$promo_index++;
							$basic_price = $promotions_by_type['basica'][0]->price_promotion ?? null; // Guardar precio básico
						}

						// Agrupar promociones por precio para adicional, mixta y bundle
						$other_promotions = array_merge(
							$promotions_by_type['adicional'],
							$promotions_by_type['mixta'],
							$promotions_by_type['bundle']
						);

						$unique_prices = [];
						$unique_promotions = [];
						foreach ($other_promotions as $promotion) {
							if (!in_array($promotion->price_promotion, $unique_prices)) {
								$unique_prices[] = $promotion->price_promotion;
								$unique_promotions[] = $promotion; // Guarda la primera promo con ese precio
							}
						}

						// Asignar precios y flags
						
						foreach ($unique_promotions as $promotion) {
							if ($promo_index <= 10) {
								$promo_prices[$promo_index] = $promotion->price_promotion;
								if (isset($item_out->ticket) && isset($promotion->price_promotion) && $item_out->ticket != 0) {
									//$relacion = ($item_out->ticket * 1.15) / $promotion->price_promotion;
									//$relacion = number_format($item_out->ticket / $promotion->price_promotion, 2);
									//$relacion =  $item_out->ticket <= (int)$promotion->price_promotion;
									//$target_flags[$promo_index] = ($relacion >= 0.5) ? $target_flag_base : 'PROMO NO APPLIED';
									
									// $relacion = round($promotion->price_promotion / $item_out->ticket, 2);
									// $target_flags[$promo_index] = ($relacion >= 0.98) ? $target_flag_base : 'PROMO NO APPLIED';
									
									foreach ($stores_orient as $store) {
										if (strpos($item_out->cust_store_name, $store) !== false) {
											$is_orient = true;
											break;
										} else $is_orient = false;
									}	
									if ($is_orient === true){
										$relacion = $item_out->sellout_amt <= $promotion->price_promotion;
										$target_flags[$promo_index] = ($relacion === true) ? $target_flag_base : 'PROMO NO APPLIED';
									}else{							
										$relacion = $promotion->price_promotion >= (int)$item_out->ticket;
										$target_flags[$promo_index] = ($relacion === true) ? $target_flag_base : 'PROMO NO APPLIED';
									}
									
								} else {
									$target_flags[$promo_index] = $target_flag_base;
								}
								$promo_index++;
							} else {
								break;
							}
						}

						if (isset($item_out->stock)) {
							$daily_stock_sum += $item_out->stock;
							if ($first_item_base === null) {
								$first_item_base = $base_data;
							}
						}

						if (isset($item_out->sellout_unit) && $item_out->sellout_unit != 0) {
							$has_nonzero_sellout = true;
							$current_day_record = $base_data + [
								'cust_store_code' => $cust_store_code,
								'cust_store_name' => $item_out->cust_store_name,
								'sellout_unit' => $item_out->sellout_unit ?? 0,
								'sellout_amt' => $item_out->sellout_amt ?? 0,
								'stock' => null,
								'ticket' => $item_out->ticket,
							];
							// Merge promo prices and target flags
							for ($i = 1; $i <= 10; $i++) {
								$current_day_record['promo' . $i . '_price'] = $promo_prices[$i];
								$current_day_record['target' . $i . '_flag'] = $target_flags[$i];
							}
							$current_day_records[] = $current_day_record;
						}
					}

					// Crear el registro de STOCK si la suma es mayor que 0
					if ($daily_stock_sum > 0 && $first_item_base !== null) {
						$stock_record = $first_item_base + [
							'cust_store_code' => null,
							'cust_store_name' => null,
							'sellout_unit' => null,
							'sellout_amt' => null,
							'stock' => $daily_stock_sum,
							'ticket' => null,
						];
						for ($i = 1; $i <= 10; $i++) {
							$stock_record['promo' . $i . '_price'] = ($i == 1) ? $basic_price : null; // Mantener el precio básico en promo1
							$stock_record['target' . $i . '_flag'] = ($i == 1) ? ($is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK') : null;
						}
						$current_day_records[] = $stock_record;
					}
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $current_day_records);
				}
			}

			// ... (resto del código para el retroceso en fechas)
			$past_stock_sum = 0;
			$past_sellout_data = [];
			$found_first_nonzero_stock_date = null;
			$first_past_item_base = null;
			$past_basic_price = null; // Almacenar el precio básico para usarlo en días pasados

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date); $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum_past = 0;
					$has_past_nonzero_sellout_on_date = false;
					$current_past_day_items = $organized_data[$model][$past_date];
					$past_promo_prices = array_fill_keys(range(1, 10), null);
					$past_target_flags = array_fill_keys(range(1, 10), null);
					$past_promo_index = 1;

					// Primero las básicas
					if (!empty($promotions_by_type['basica'])) {
						$past_promo_prices[$past_promo_index] = $promotions_by_type['basica'][0]->price_promotion ?? null;
						$past_target_flags[$past_promo_index] = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
						$past_promo_index++;
						$past_basic_price = $promotions_by_type['basica'][0]->price_promotion ?? null;
					}
					$unique_prices_past = [];
					$unique_promotions_past = [];
					foreach ($other_promotions as $promotion) {
						if (!in_array($promotion->price_promotion, $unique_prices_past)) {
							$unique_prices_past[] = $promotion->price_promotion;
							$unique_promotions_past[] = $promotion; // Guarda la primera promo con ese precio
						}
					}
					foreach ($unique_promotions_past as $promotion) {
						if ($past_promo_index <= 10) {
							$past_promo_prices[$past_promo_index] = $promotion->price_promotion;
							$past_target_flags[$past_promo_index] =  'SELLOUT BEFORE PROMO';
							$past_promo_index++;
						} else {
							break;
						}
					}

					foreach ($current_past_day_items as $item) {
						// Sumar todos los valores de stock para la fecha actual del retroceso
						if (isset($item->stock)) {
							$daily_stock_sum_past += $item->stock;
							if ($first_past_item_base === null) {
								$first_past_item_base = [
									'customer'             => $item->customer,
									'acct_gtm'             => $item->acct_gtm,
									'customer_model'    => $item->customer_model,
									'model_code'         => $item->model_suffix_code,
								];
							}
							if ($item->stock != 0 && $found_first_nonzero_stock_date === null) {
								$found_first_nonzero_stock_date = $past_date;
							}
						}

						// Guardar registros de sellout != 0
						if (isset($item->sellout_unit) && $item->sellout_unit != 0) {
							$has_past_nonzero_sellout_on_date = true;
							$target1_flag = $is_bundle_model ? 'BUNDLE' : 'SELLOUT BEFORE PROMO';
							if (stripos($item->cust_store_name, 'WEB') !== false || stripos($item->cust_store_name, 'web') !== false) {
								$target1_flag = 'PROMO APPLIED WEB';
							}
							$cust_store_code = $item->cust_store_code;
							if (empty($cust_store_code) || $cust_store_code == 0) {
								$names = explode(" ", $item->cust_store_name);
								if (count($names) > 1) {
									$cust_store_code = $names[1];
								}
							}
							$past_sellout_data[] = [
								'customer'             => $item->customer,
								'acct_gtm'             => $item->acct_gtm,
								'customer_model'    => $item->customer_model,
								'model_code'         => $item->model_suffix_code,
								'txn_date'            => $past_date,
								'cust_store_code'     => $cust_store_code,
								'cust_store_name'     => $item->cust_store_name,
								'sellout_unit'         => $item->sellout_unit ?? 0,
								'sellout_amt'         => $item->sellout_amt ?? 0,
								'stock'             => null,
								'ticket'             => $item->ticket,

							];
							 for ($i = 1; $i <= 10; $i++) {
								$past_sellout_data[count($past_sellout_data)-1]['promo' . $i . '_price'] = $past_promo_prices[$i];
								$past_sellout_data[count($past_sellout_data)-1]['target' . $i . '_flag'] = $past_target_flags[$i];
							}
						}
					}
					$past_stock_sum += $daily_stock_sum_past;
					if ($found_first_nonzero_stock_date !== null) {
						break; // Detener el retroceso al encontrar la primera fecha con stock != 0
					}
				}
			}

			// Insertar el registro de STOCK del pasado si se encontró stock != 0
			if ($found_first_nonzero_stock_date !== null && $past_stock_sum > 0 && $first_past_item_base !== null) {
				array_unshift($all_promotion_data_to_insert, [
					'customer'             => $customer,
					'acct_gtm'             => $first_past_item_base['acct_gtm'] ?? null,
					'customer_model'    => $first_past_item_base['customer_model'] ?? null,
					'model_code'         => $model,
					'txn_date'             => $found_first_nonzero_stock_date, // Usar la fecha en la que se encontró el stock != 0
					'stock'             => $past_stock_sum,
					'cust_store_code'     => null,
					'cust_store_name'     => null,
					'sellout_unit'         => null,
					'sellout_amt'         => null,
					'ticket'             => null,
					'promo1_price'        => $past_basic_price, // Usar el precio básico almacenado
					'target1_flag'         => $is_bundle_model ? 'STOCK OF BUNDLE' : 'STOCK',
					'promo2_price'        => null,
					'target2_flag'        => null,
					'promo3_price'        => null,
					'target3_flag'        => null,
					'promo4_price'        => null,
					'target4_flag'        => null,
					'promo5_price'        => null,
					'target5_flag'        => null,
					'promo6_price'        => null,
					'target6_flag'        => null,
					'promo7_price'        => null,
					'target7_flag'        => null,
					'promo8_price'        => null,
					'target8_flag'        => null,
					'promo9_price'        => null,
					'target9_flag'        => null,
					'promo10_price'        => null,
					'target10_flag'        => null,
				]);
			}

			// Agregar los registros de sellout != 0 del pasado
			$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $past_sellout_data);
		}

		// Ordenar el resultado final por modelo y luego por txn_date
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					 $a_is_stock = isset($a['target1_flag']) && (in_array($a['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));
					$b_is_stock = isset($b['target1_flag']) && (in_array($b['target1_flag'], ['STOCK', 'STOCK OF BUNDLE', 'PROMO APPLIED WEB', 'PROMO NO APPLIED']));

					if ($a_is_stock && !$b_is_stock) {
						return -1;
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1;
					} else {
						return 0;
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($all_promotion_data_to_insert);
		$batch_data = $all_promotion_data_to_insert;
		// Verificar dias previos para modelos de bundles mal mapeado
		// Falta agregar para las demas columnas de la hoja sellout como las colmnas de target1flag     y ver logica para las otras columnas
		// Inserción por lotes
		// Tener en cuenta para los casos de promo no applied cuando ticket es mayor a promo1_price (cuidado, se puede redondear y aun cumplicar con ser menor o sea 400 a 399 masimo 2 numeros mas)
		$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
	}
	
	public function load_sell_out_vfuncional() {

		$filter_select_promo = ['start_date', 'end_date', 'customer_code', 'promotion_no', 'model', 'price_promotion', 'gift'];
		$data_calculate = $this->gen_m->filter_select('sa_calculate_promotion', false, $filter_select_promo);
			
		$models = array_unique(array_column($data_calculate, 'model'));
		$models = array_filter($models);
		
		$models_bundles = array_unique(array_column($data_calculate, 'gift'));
		$models_bundles = array_filter($models_bundles);
		// $models = $models + $models_bundles;
		// $models = array_values($models);
		// echo '<pre>'; print_r($models);
		
		$all_models = array_merge($models, $models_bundles);
		$all_models = array_unique($all_models);
		$all_models = array_values($all_models);
		
		if (empty($data_calculate)) {
			echo "No hay datos de cálculo de promoción.";
			return;
		}

		$from_date = date('Y-m-01', strtotime($data_calculate[0]->start_date));
		$to_date = date('Y-m-t', strtotime($data_calculate[0]->start_date));
		$customer = $data_calculate[0]->customer_code;
		$days_to_look_back = 30;
		$past_start_date = date('Y-m-d', strtotime($from_date . ' -' . $days_to_look_back . ' days'));

		$all_promotion_data_to_insert = [];

		// 1. Obtener todos los datos relevantes de sa_sell_out_ para todos los modelos en un rango amplio
		$where_all = [
			'customer' => $customer,
			'txn_date >=' => $past_start_date,
			'txn_date <=' => $to_date,
		];
		$where_in_models = [
			'field' 	=> 'model_suffix_code',
			'values' 	=> $all_models,
		];
		$data_sell_out_all = $this->gen_m->filter('sa_sell_out_', false, $where_all, null, [$where_in_models], [['customer_model', 'asc'], ['txn_date', 'asc']]);

		// 2. Organizar los datos por modelo y fecha para facilitar el procesamiento
		$organized_data = [];
		foreach ($data_sell_out_all as $item) {
			$organized_data[$item->model_suffix_code][$item->txn_date][] = $item;
		}
		$sum_price_prom = [];
		foreach ($all_models as $model) {
			$accumulated_stock = 0;
			$previous_txn_date = null;
			$past_first_stock_date = null;
			$past_first_stock_sum = 0;
			$zero_stock_past_data = [];
			
			$culculate_price_promotions = $this->gen_m->filter_select('sa_calculate_promotion', false, ['price_promotion', 'model', 'gift'], ['model' => $model]);
			$price_prom = '';
			if (!empty($culculate_price_promotions) && $culculate_price_promotions[0]->model === $model) {
				$price_prom = $culculate_price_promotions[0]->price_promotion;
			}
			// foreach ($culculate_price_promotions as $item){
				// $sum_price_prom[$item->model][] = $item->price_promotion;
			// }
			// if (in_array($model, $models_bundles)) $price_prom = '';
			// else $price_prom = $culculate_price_promotions[0]->price_promotion;
			//$price_prom = $culculate_price_promotions[0]->price_promotion;
			// Procesar el periodo actual ($from_date a $to_date)
			for ($current_date = $from_date; $current_date <= $to_date; $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'))) {
				if (isset($organized_data[$model][$current_date])) {
					$stock_record = null; // Para el registro con target1_flag == 'STOCK'
					$non_stock_records = []; // Para los registros sin target1_flag == 'STOCK'
					$daily_stock_sum = 0;
					$first_stock_item_base = null;

					foreach ($organized_data[$model][$current_date] as $item_out) {
						$base_data = [
							'customer' 			=> $item_out->customer,
							'acct_gtm' 			=> $item_out->acct_gtm,
							'customer_model'	=> $item_out->customer_model,
							'model_code'		=> $item_out->model_suffix_code,
							'txn_date' 			=> $item_out->txn_date,
						];

						if (isset($item_out->sellout_unit)) {
							if ($item_out->sellout_unit == 0) {
								$daily_stock_sum += $item_out->stock;
								if ($first_stock_item_base === null) {
									$first_stock_item_base = $base_data;
								}
							} else {
								$non_stock_records[] = $base_data + [								
									'cust_store_code' 	=> $item_out->cust_store_code,
									'cust_store_name' 	=> $item_out->cust_store_name,
									'sellout_unit' 		=> $item_out->sellout_unit ?? 0,
									'sellout_amt' 		=> $item_out->sellout_amt ?? 0,
									'stock' 			=> null,
									'ticket' 			=> $item_out->ticket,
									'promo1_price'		=> $price_prom,
									'target1_flag'		=> 'PROMO APPLIED',
								];
							}
						}
					}

					// Crear el registro para target1_flag == 'STOCK'
					if ($first_stock_item_base !== null) {
						$stock_record = $first_stock_item_base + [						
							'cust_store_code' 		=> null,
							'cust_store_name' 		=> null,
							'sellout_unit' 			=> null,
							'sellout_amt' 			=> null,
							'stock' 				=> $daily_stock_sum,
							'ticket' 				=> null,
							'promo1_price'			=> $price_prom,
							'target1_flag' 			=> 'STOCK',
						];
					}

					// Agregar primero el registro de target1_flag == 'STOCK' si existe
					if ($stock_record !== null) {
						$all_promotion_data_to_insert[] = $stock_record;
					}

					// Agregar todos los demás registros del día
					$all_promotion_data_to_insert = array_merge($all_promotion_data_to_insert, $non_stock_records);
				}
			}

			$past_first_stock_data = null;
			$zero_stock_past_data = [];
			$found_first_stock_day = false;

			// Retroceder día por día
			for ($past_date = date('Y-m-d', strtotime($from_date . ' -1 day')); strtotime($past_date) >= strtotime($past_start_date) && !$found_first_stock_day; $past_date = date('Y-m-d', strtotime($past_date . ' -1 day'))) {
				if (isset($organized_data[$model][$past_date])) {
					$daily_stock_sum = 0;
					$has_nonzero_stock = false;
					$daily_zero_stock = [];

					foreach ($organized_data[$model][$past_date] as $item) {
						if (isset($item->stock)) {
							if ($item->sellout_unit == 0) {
								$daily_stock_sum += $item->stock;
								$has_nonzero_stock = true;
							} else {
								$daily_zero_stock[] = [
									'customer' 			=> $item->customer,
									'acct_gtm' 			=> $item->acct_gtm,
									'customer_model'	=> $item->customer_model,
									'model_code' 		=> $item->model_suffix_code,
									'txn_date'			=> $item->txn_date,									
									'cust_store_code' 	=> $item->cust_store_code,
									'cust_store_name' 	=> $item->cust_store_name,
									'sellout_unit' 		=> $item->sellout_unit ?? 0,
									'sellout_amt' 		=> $item->sellout_amt ?? 0,
									'stock' 			=> null,
									'ticket' 			=> $item->ticket,
									'promo1_price'		=> $price_prom,
									'target1_flag' 		=> 'SELLOUT BEFORE PROMO',
								];
							}
						}
					}

					// Si encontramos stock != 0 en este día, guardamos la suma y detenemos el retroceso
					if ($has_nonzero_stock) {
						$found_first_stock_day = true;
						$past_first_stock_data = [
							'customer' 			=> $customer,
							'acct_gtm' 			=> $organized_data[$model][$past_date][0]->acct_gtm ?? null,
							'customer_model'	=> $organized_data[$model][$past_date][0]->customer_model ?? null,
							'model_code' 		=> $model,
							'txn_date' 			=> $past_date,						
							'cust_store_code' 	=> null,
							'cust_store_name' 	=> null,
							'sellout_unit' 		=> null,
							'sellout_amt' 		=> null,
							'stock' 			=> $daily_stock_sum,
							'ticket' 			=> null,
							'promo1_price'		=> $price_prom,
							'target1_flag' 		=> 'STOCK',
						];
					}

					// Guardamos los registros de stock cero de este día (se guardarán en orden inverso)
					$zero_stock_past_data = array_merge($daily_zero_stock, $zero_stock_past_data);
				}
			}

			// Insertar el primer registro de stock != 0 del pasado (si existe) al principio
			if ($past_first_stock_data) {
				array_unshift($all_promotion_data_to_insert, $past_first_stock_data);
			}

			// Agregar la data de stock cero del pasado (en el orden correcto debido a la concatenación)
			$all_promotion_data_to_insert = array_merge($zero_stock_past_data, $all_promotion_data_to_insert);
		}

		// Ordenar el resultado final por modelo y luego por txn_date (como en el código original)
		usort($all_promotion_data_to_insert, function ($a, $b) {
			if ($a['model_code'] === $b['model_code']) {
				if ($a['txn_date'] === $b['txn_date']) {
					// Ordenar dentro de la misma fecha: target1_flag == 'STOCK' primero
					$a_is_stock = isset($a['target1_flag']) && $a['target1_flag'] === 'STOCK';
					$b_is_stock = isset($b['target1_flag']) && $b['target1_flag'] === 'STOCK';

					if ($a_is_stock && !$b_is_stock) {
						return -1; // a va primero
					} elseif (!$a_is_stock && $b_is_stock) {
						return 1; // b va primero
					} else {
						return 0; // Mantener el orden relativo si ambos son STOCK o ambos no lo son
					}
				}
				return strtotime($a['txn_date']) - strtotime($b['txn_date']);
			}
			return strcmp($a['model_code'], $b['model_code']);
		});
		//echo '<pre>'; print_r($sum_price_prom);
		echo '<pre>'; print_r($all_promotion_data_to_insert); 
		$batch_data = $all_promotion_data_to_insert;

		// Inserción por lotes
		//if (count($batch_data) >= $batch_size) {
			$this->gen_m->insert_m("sa_sell_out_promotion", $batch_data);
			//$batch_data = [];
		//}
	}

	
	public function process($file_ext){	
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//truncate all rows
		$this->gen_m->truncate("sa_calculate_promotion");
		$this->gen_m->truncate("sa_sell_in_promotion");
		$this->gen_m->truncate("sa_sell_out_promotion");
		//load excel file
		$spreadsheet = IOFactory::load("./upload/sa_sell_in_out_promotion." . $file_ext);
		
		// Funcion para carga hoja CALCULATE  a la DB
		$this->load_calculate($spreadsheet->getSheetByName("CALCULATE"));
		
		// Funcion para carga hoja SELLOUT  a la DB
		$this->load_sell_out();
		//$this->load_sell_out($spreadsheet->getSheetByName("SELLOUT"));
		
		// Funcion para cargar hoja SELLIN a la DB
		$this->load_sell_in($spreadsheet->getSheetByName("SELLIN"));
		
		// Funcion para actualziar columnas en verde de la hoja SELOUT
		//$this->update_sell_in();
		
		// Obtener el nombre original del archivo usando $_FILES
		$originalFileName = $_FILES['attach']['name'];
		
		// Construir la ruta del archivo temporal
		$tempFilePath = './upload_file/Sales Admin/' . $originalFileName;

		// Guardar el archivo Excel en la carpeta temporal con el nombre original
		$file_ext = ucfirst($file_ext);
		$writer = IOFactory::createWriter($spreadsheet, $file_ext);
		$writer->save($tempFilePath);

		// Devolver la ruta del archivo temporal y el tiempo de ejecución
		$msg =" record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";

		return $msg;
	}
	
	public function get_uploaded_files() {
		$folder_path = './upload_file/Sales Admin/';
		$files = [];

		if (is_dir($folder_path)) {
			foreach (scandir($folder_path) as $file) {
				if ($file !== "." && $file !== ".." && (pathinfo($file, PATHINFO_EXTENSION) === "xlsx" || pathinfo($file, PATHINFO_EXTENSION) === "xls")) {
					$files[] = $file;
				}
			}
		}

		header('Content-Type: application/json');
		echo json_encode($files);
	}

	public function upload(){
		$type = "error"; $msg = $url = "";
		
		if ($this->session->userdata('logged_in')){
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'sa_sell_in_out_promotion',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$result = $this->upload->data();
				$type = "success";
				if ($result['file_ext'] === '.xlsx') $file_ext = 'xlsx';
				elseif ($result['file_ext'] === '.xls') $file_ext = 'xls';
				$msg = $this->process($file_ext);
				$url = base_url()."upload/sa_sell_in_out_promotion.xlsx";
			}else $msg = "Error occured.";
		}else{
			$msg = "Your session is finished.";
			$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
