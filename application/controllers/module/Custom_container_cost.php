<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Custom_container_cost extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"container_cost"	=> $this->gen_m->filter("custom_container_cost", false, null, null, null, [['last_updated', 'desc']], 100),
			"main" 		=> "module/custom_container_cost/index",
		];
		
		$this->load->view('layout', $data);
	}
		
	public function convert_date($excel_date) {
		if (empty($excel_date)) {
			return null;
		}

		if (is_numeric($excel_date)) {		
			$excel_base = 25569;
			$seconds_in_day = 86400;

			$date_part = floor($excel_date);
			$time_part = $excel_date - $date_part;
			
			$is_only_time = ($excel_date < 1 && $date_part == 0);
			
			if ($is_only_time) {
				$output_format = 'h:i:s A'; 
				$unix_date = 0;
			} elseif ($time_part == 0) {
				$output_format = 'Y-m-d';
				$unix_date = ($date_part - $excel_base) * $seconds_in_day;
			} else {
				$output_format = 'Y-m-d h:i:s'; 
				$unix_date = ($date_part - $excel_base) * $seconds_in_day;
			}
			$time_seconds = round($time_part * $seconds_in_day);
			$total_unix_timestamp = $unix_date + $time_seconds;

			$date_object = new DateTime("@$total_unix_timestamp");
			$date_object->setTimezone(new DateTimeZone('UTC'));
			
			return $date_object->format($output_format);
		}

		$date_formats = ['d-M-y', 'd-M-Y', 'm/d/Y', 'Y-m-d', 'Y-m-d h:i:s A'];
		foreach ($date_formats as $format) {
			$date_object = DateTime::createFromFormat($format, $excel_date);
			if ($date_object && $date_object->format($format) === $excel_date) {
				
				// Si el formato de texto incluye hora, lo devolvemos en 12h con AM/PM
				if (strpos($format, 'h') !== false || strpos($format, 'H') !== false) {
					 return $date_object->format('Y-m-d h:i:s A');
				}
				// Si solo es fecha, devolvemos Y-m-d
				return $date_object->format('Y-m-d');
			}
		}
		
		return null;
	}

	public function process(){ // ok
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/custom_container_cost.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

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
		];

		$header = ["Invoice Num", "House BL No", "Ship Method", "Price Terms", "Expense Class", "Expense Item", "Currency", "Expense Frn Aomunt"]; // Expense Frn Amount validar si es amount o aomunt, House BL No -> House Bl No
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
		
		$key_list = [];
		$container_cost = $this->gen_m->filter_select('custom_container_cost', false, 'container_key');
		foreach($container_cost as $item) $key_list[] = $item->container_key;
		
		if ($is_ok){
			$updated = date("Y-m-d H:i:s");
			$max_row = $sheet->getHighestRow();
			$inserted_rows = 0; $updated_rows = 0;
			$batch_data =[]; $batch_data_update = [];
			$batch_size = 200;
			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"invoice_num" 				=> trim($sheet->getCell('A'.$i)->getValue()),
					"house_bl_no" 				=> trim($sheet->getCell('B'.$i)->getValue()),
					"ship_method" 				=> trim($sheet->getCell('C'.$i)->getValue()),
					"price_terms" 				=> trim($sheet->getCell('D'.$i)->getValue()),
					"expense_class" 			=> trim($sheet->getCell('E'.$i)->getValue()),
					"expense_item"				=> trim($sheet->getCell('F'.$i)->getValue()),
					"currency" 					=> trim($sheet->getCell('G'.$i)->getValue()),
					"expense_frn_amount" 		=> trim($sheet->getCell('H'.$i)->getValue()),
					"expense_loc_amount"		=> trim($sheet->getCell('I'.$i)->getValue()),
					"vat"						=> trim($sheet->getCell('J'.$i)->getValue()),
					"partner"					=> trim($sheet->getCell('K'.$i)->getValue()),
					"supplier"					=> trim($sheet->getCell('L'.$i)->getValue()),
					"status"					=> trim($sheet->getCell('M'.$i)->getValue()),		
					"container_no"				=> trim($sheet->getCell('N'.$i)->getValue()),
					"shipping_date"				=> trim($sheet->getCell('O'.$i)->getValue()),
					"invoice_date"				=> trim($sheet->getCell('P'.$i)->getValue()),
					"confirm_date"				=> trim($sheet->getCell('Q'.$i)->getValue()),
					"last_updated"				=> $updated,
				];
				
				$row['container_key'] = $row['invoice_num'] . "_" . $row['house_bl_no'] . "_" . $row['currency'] . "_" . $row['container_no'] . "_" . $row['expense_frn_amount'] . "_" . $row['expense_loc_amount'] . "_" . $row['vat'];
				
				$row['shipping_date'] = $this->convert_date($row['shipping_date']);
				$row['invoice_date'] = $this->convert_date($row['invoice_date']);
				$row['confirm_date'] = $this->convert_date($row['confirm_date']);
				
				//if ($row['container_no'] == 1 || $row['container_no'] === '01') continue;
				if (!in_array($row['container_key'], $key_list)) $batch_data[] = $row;
				else $batch_data_update[] = $row;;
				
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("custom_container_cost", $batch_data);
					$inserted_rows += count($batch_data);
					$batch_data = [];
					unset($batch_data);
				}
				if(count($batch_data_update)>=$batch_size){
					$this->gen_m->update_multi("custom_container_cost", $batch_data_update, 'container_key');
					$updated_rows += count($batch_data_update);
					$batch_data_update = [];
					unset($batch_data_update);
				}
			}

			if (!empty($batch_data)) {
				$this->gen_m->insert_m("custom_container_cost", $batch_data);
				$inserted_rows += count($batch_data);
				$batch_data = [];
				unset($batch_data);
			}
			if (!empty($batch_data_update)) {
				$this->gen_m->update_multi("custom_container_cost", $batch_data_update, 'container_key');
				$updated_rows += count($batch_data_update);
				$batch_data_update = [];
				unset($batch_data_update);
			}
			
			$msg = $inserted_rows . " rows inserted.<br>";
			$msg .= $updated_rows . " rows updated.<br>";
			$msg .= "Total time: " . number_Format(microtime(true) - $start_time, 2) . " secs.";
			$this->db->trans_complete();
			return $msg;		
		}else return "";
	}
	
	public function get_customs_agent_fee($expense_frn_amount, $expense_loc_amount, $currency){
		if ($currency === 'USD'){
			if ($expense_frn_amount != 0) $expense = $expense_frn_amount;
			else $expense = $expense_loc_amount;
		} elseif ($currency === 'PEN'){
			if ($expense_frn_amount != 0) $expense = $expense_frn_amount / 3.5;
			else $expense = $expense_loc_amount / 3.5;
		}
		return $expense;
	}
	
	private function calculate_filter_dates($range_option, $start_date, $end_date) {
		$to_date = date('Y-m-t'); 
		$from_date = null;

		switch ($range_option) {
			case '3_months':
				$from_date = date('Y-m-01', strtotime('-2 months')); 
				break;
				
			case '6_months':
				$from_date = date('Y-m-01', strtotime('-5 months'));
				break;
				
			case '12_months':
				$from_date = date('Y-m-01', strtotime('-11 months'));
				break;
				
			case 'custom':
				if (!empty($start_date) && !empty($end_date)) {
					$from_date = $start_date;
					$to_date = date('Y-m-t', strtotime($end_date));
				}
				break;
				
			case '':
			default:
				$from_date = null;
				$to_date = null;
				break;
		}

		return [
			'from' => $from_date,
			'to' => $to_date
		];
	}
	
	public function export_excel(){
		$range = $this->input->get('range');
		$start_date_user = $this->input->get('start');
		$end_date_user = $this->input->get('end');
		
		if ($range !== 'all_records') {
			$dates = $this->calculate_filter_dates($range, $start_date_user, $end_date_user);
			$from_date = $dates['from'];
			$to_date = $dates['to'];
		}		

		// Map to save variables from container_cost data
		$data_map = [];
		$house_bl_custom_agent_fee_list = [];

		$containers  = [];
		$data_container_cost = $this->gen_m->filter('custom_container_cost', false, null, null ,null, [['confirm_date', 'asc'], ['house_bl_no', 'asc']]);
		foreach ($data_container_cost as &$item) {
			$item->house_bl_no = str_replace('.', '', $item->house_bl_no);
			if (!empty($item->container_no)) $containers[$item->container_no][] = $item;
			
			if(!isset($house_bl_custom_agent_fee_list[$item->house_bl_no])) $house_bl_custom_agent_fee_list[$item->house_bl_no] = 0;
			if ($item->expense_item === 'Customs Agent fee') {
				$house_bl_custom_agent_fee_list[$item->house_bl_no] += $this->get_customs_agent_fee($item->expense_frn_amount, $item->expense_loc_amount, $item->currency);
			}
		}
		
		if ($range !== 'all_records') $container_data = $this->gen_m->filter('custom_release_container', false, ['date >=' => $from_date, 'date <=' => $to_date], null ,null, [['date', 'asc'], ['house_bl_number', 'asc'], ['container_number', 'asc']]);	
		else $container_data = $this->gen_m->filter('custom_release_container', false, null, null ,null, [['date', 'asc'], ['house_bl_number', 'asc'], ['container_number', 'asc']]);
		
		$containers_list = []; $house_bl_list = [];
		$bl_containers_count = []; //Count container per House BL
		foreach ($container_data as $item_co){
			if (!isset($bl_containers_count[$item_co->house_bl_number])) $bl_containers_count[$item_co->house_bl_number] = 0;
			$bl_containers_count[$item_co->house_bl_number] += 1;
			$containers_list[$item_co->container_number] = $item_co->house_bl_number;
			$house_bl_list[$item_co->house_bl_number][] = $item_co->container_number;
		}
		$containers_with_data = []; // Variable to save container with data in realease container file
		foreach ($container_data as $item) {
			if (!isset($data_map[$item->container_number])) {
				$data_map[$item->container_number] = [
					'month' => '', 'cnt' => '', 'house_bl_no' => '', 'container_no' => '','customs_agent_fee' => null, 'discharging_port' => null, 'inspection_related_work' => null, 'itc' => null, 'empty_cntr_return' => null, 'gate_in' => null,
					'admin_cy' => null, 'admin_maritime_agent' => null, 'checking_seal_number' => null, 'container_repair' => null, 'courier' => null, 'discharging_air' => null, 'discharging_warehouse' => null, 'documentation' => null, 'etc' => null,
					'freight' => null, 'handling_charge' => null, 'itc_air' => null, 'itc_lcl' => null, 'lift_on' => null, 'stamp' => null, 'ctn_qty' => null, 'completed' => '', 'customs_agent_fee_div_total' => null, 
					'discharging_port_div_total' => null, 'inspection_related_work_div_total' => null, 'itc_div_total' => 0, 'empty_cntr_return_div_total' => 0, 'gate_in_div_total' => 0, 'total_regular' => 0, 'total_optional' => 0,
					'customs_agent_fee_accrual' => 0, 'discharging_port_accrual' => 0, 'inspection_related_work_accrual' => 0, 'itc_accrual' => 0, 'empty_cntr_return_accrual' => 0, 'gate_in_accrual' => 0, 'total_accrual' => 0
				];
			}
			// Month Variable
			if (!empty($item->date)) { 
				$object_date = new DateTime($item->date);
				$data_map[$item->container_number]['month'] = $object_date->format('n');
			} else { 
				$data_map[$item->container_number]['month'] = '-';
			}
			// LCL
			if ($item->container_number === 'LCL') {
				$data_map[$item->container_number]['cnt'] = 'N';
				//continue;
			} else $data_map[$item->container_number]['cnt'] = 'Y';
			
			$data_map[$item->container_number]['container_no'] = $item->container_number;
			$data_map[$item->container_number]['house_bl_no'] = $item->house_bl_number;
			$data_map[$item->container_number]['customs_agent_fee'] = $house_bl_custom_agent_fee_list[$item->house_bl_number] ?? 0;
			$data_map[$item->container_number]['ctn_qty'] += (int)$item->validation;
			if (isset($containers[$item->container_number])) {				
				foreach ($containers[$item->container_number] as $item_r) {
					if (isset($bl_containers_count[$item_r->house_bl_no])) $div = $bl_containers_count[$item_r->house_bl_no];
					else continue;
					
					
					//if ($item_r->expense_item === 'Customs Agent fee') $data_map[$item_r->container_no]['customs_agent_fee'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency);
					if ($item_r->expense_item === 'Discharging(Port)') $data_map[$item_r->container_no]['discharging_port'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Inspection related work') $data_map[$item_r->container_no]['inspection_related_work']+= $this->get_customs_agent_fee($item_r->expense_frn_amount,$item_r->expense_loc_amount,$item_r->currency)/$div;
					elseif ($item_r->expense_item === 'ITC') $data_map[$item_r->container_no]['itc'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Empty Cntr Return') $data_map[$item_r->container_no]['empty_cntr_return'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Gate In') $data_map[$item_r->container_no]['gate_in'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Admin (CY)') $data_map[$item_r->container_no]['admin_cy'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Admin (Maritime Agent)') $data_map[$item_r->container_no]['admin_maritime_agent'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Checking Seal Number') $data_map[$item_r->container_no]['checking_seal_number'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'CONTAINER REPAIR') $data_map[$item_r->container_no]['container_repair'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'COURIER') $data_map[$item_r->container_no]['courier'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Discharging(Air)') $data_map[$item_r->container_no]['discharging_air'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Discharging(Warehouse)') $data_map[$item_r->container_no]['discharging_warehouse'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Documentation') $data_map[$item_r->container_no]['documentation'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'ETC') $data_map[$item_r->container_no]['etc'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Freight') $data_map[$item_r->container_no]['freight'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Handling Charge') $data_map[$item_r->container_no]['handling_charge'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'ITC(Air)') $data_map[$item_r->container_no]['itc_air'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'ITC_LCL') $data_map[$item_r->container_no]['itc_lcl'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'LIFT ON') $data_map[$item_r->container_no]['lift_on'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					elseif ($item_r->expense_item === 'Stamp')  $data_map[$item_r->container_no]['stamp'] += $this->get_customs_agent_fee($item_r->expense_frn_amount, $item_r->expense_loc_amount, $item_r->currency)/$div;
					$containers_with_data[$item_r->container_no] = $item_r->house_bl_no;
				}
			}

		}

		foreach ($containers_with_data as $key => &$container) {
			foreach ($house_bl_list[$container] as &$item_container) {
				if ($item_container !== $key) {				
					$data_map[$item_container]['customs_agent_fee'] = 0;
					$data_map[$item_container]['discharging_port'] = $data_map[$key]['discharging_port'];
					$data_map[$item_container]['inspection_related_work'] = $data_map[$key]['inspection_related_work'];
					$data_map[$item_container]['itc'] = $data_map[$key]['itc'];
					$data_map[$item_container]['empty_cntr_return'] = $data_map[$key]['empty_cntr_return'];
					$data_map[$item_container]['gate_in'] = $data_map[$key]['gate_in'];
					$data_map[$item_container]['admin_cy'] = $data_map[$key]['admin_cy'];
					$data_map[$item_container]['admin_maritime_agent'] = $data_map[$key]['admin_maritime_agent'];
					$data_map[$item_container]['checking_seal_number'] = $data_map[$key]['checking_seal_number'];
					$data_map[$item_container]['container_repair'] = $data_map[$key]['container_repair'];
					$data_map[$item_container]['courier'] = $data_map[$key]['courier'];
					$data_map[$item_container]['discharging_air'] = $data_map[$key]['discharging_air'];
					$data_map[$item_container]['discharging_warehouse'] = $data_map[$key]['discharging_warehouse'];
					$data_map[$item_container]['documentation'] = $data_map[$key]['documentation'];
					$data_map[$item_container]['etc'] = $data_map[$key]['etc'];
					$data_map[$item_container]['freight'] = $data_map[$key]['freight'];
					$data_map[$item_container]['handling_charge'] = $data_map[$key]['handling_charge'];
					$data_map[$item_container]['itc_air'] = $data_map[$key]['itc_air'];
					$data_map[$item_container]['itc_lcl'] = $data_map[$key]['itc_lcl'];
					$data_map[$item_container]['lift_on'] = $data_map[$key]['lift_on'];
					$data_map[$item_container]['stamp'] = $data_map[$key]['stamp'];
					$data_map[$item_container]['customs_agent_fee_accrual'] = 'Provisionado'; // Change to 0 
				}
			}
		}

		// div total container	
		foreach ($data_map as $key => &$item) {
			//$item['ctn_qty'] = $count_container[$key];
			$item['customs_agent_fee_div_total'] = ($item['ctn_qty'] == 0) ? 0 : $item['customs_agent_fee'] / $item['ctn_qty'];
			$item['discharging_port_div_total'] = ($item['ctn_qty'] == 0) ? 0 : $item['discharging_port'] / $item['ctn_qty'];
			$item['inspection_related_work_div_total'] = ($item['ctn_qty'] == 0) ? 0 : $item['inspection_related_work'] / $item['ctn_qty'];
			$item['itc_div_total'] = ($item['ctn_qty'] == 0) ? 0 : $item['itc'] / $item['ctn_qty'];
			$item['empty_cntr_return_div_total'] = ($item['ctn_qty'] == 0) ? 0 : $item['empty_cntr_return'] / $item['ctn_qty'];
			$item['gate_in_div_total'] = ($item['ctn_qty'] == 0) ? 0 : $item['gate_in'] / $item['ctn_qty'];
		
			if ($item['ctn_qty'] != 0 && $item['customs_agent_fee_div_total'] != 0 && $item['discharging_port_div_total'] != 0 && $item['inspection_related_work_div_total'] != 0 && $item['itc_div_total'] != 0 && $item['empty_cntr_return_div_total'] != 0 && $item['gate_in_div_total'] != 0) {
				$item['completed'] = 'Y';
			} else $item['completed'] = 'N';
			
			$item['total_regular'] = $item['customs_agent_fee_div_total'] + $item['discharging_port_div_total'] + $item['inspection_related_work_div_total'] + $item['itc_div_total'] + $item['empty_cntr_return_div_total'] + $item['gate_in_div_total'];
			$item['total_optional'] = $item['container_repair'] + $item['courier'] + $item['discharging_air'] + $item['discharging_warehouse'] + $item['documentation'] + $item['etc'] + $item['freight'] + $item['handling_charge'] +  
			$item['itc_air'] +  $item['itc_lcl'] +  $item['lift_on'] + $item['stamp'];
			
			// ACCRUAL
			if ($item['customs_agent_fee_accrual'] === 'Provisionado') {
				$item['customs_agent_fee_accrual'] = 0;
			} elseif ($item['customs_agent_fee'] == 0 || $item['customs_agent_fee'] === ''){
				$item['customs_agent_fee_accrual'] = 84;
			} else $item['customs_agent_fee_accrual'] = 0;
			//$item['customs_agent_fee_accrual'] = ($item['customs_agent_fee'] == 0 || $item['customs_agent_fee'] === '') ? 84 : 0;
			$item['discharging_port_accrual'] = ($item['discharging_port'] == 0 || $item['discharging_port'] === '') ? $item['ctn_qty'] * 290 : 0;
			$item['inspection_related_work_accrual'] = ($item['inspection_related_work'] == 0 || $item['inspection_related_work'] === '') ? $item['ctn_qty'] * 28 : 0;
			$item['itc_accrual'] = ($item['itc'] == 0 || $item['itc'] === '') ? $item['ctn_qty'] * 358 : 0;
			$item['empty_cntr_return_accrual'] = ($item['empty_cntr_return'] == 0 || $item['empty_cntr_return'] === '') ? $item['ctn_qty'] * 130 : 0;
			$item['gate_in_accrual'] = ($item['gate_in'] == 0 || $item['gate_in'] === '') ? $item['ctn_qty'] * 230 : 0;
			$item['total_accrual'] = $item['customs_agent_fee_accrual'] + $item['discharging_port_accrual'] + $item['inspection_related_work_accrual'] + $item['itc_accrual'] + $item['empty_cntr_return_accrual'] + $item['gate_in_accrual'];
		}
		
		$data = [];
		foreach ($data_map as $item) $data[] = $item;
		//echo '<pre>'; print_r($data_map); return;

		$template_path = './template/custom_container_cost_template.xlsx';
		
		try {
            $spreadsheet = IOFactory::load($template_path);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('Error loading the Excel template: ' . $e->getMessage());
        }
		$sheet = $spreadsheet->getActiveSheet();
		
		$start_row = 5;
        $current_row = $start_row;
		
        if ($data) { // data ->data_map
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $current_row, $row['month']); 																														// Column A: month                           
                $sheet->setCellValue('B' . $current_row, $row['cnt']); 																															// Column B: cnt
                $sheet->setCellValue('C' . $current_row, $row['house_bl_no']); 																													// Column C: house_bl_no
				$sheet->setCellValue('D' . $current_row, $row['container_no']); 																												// Column D: container_no
				$sheet->setCellValue('E' . $current_row, ($row['customs_agent_fee'] != 0) ? number_format($row['customs_agent_fee'], 2, '.', '') : '-');										// Column E: customs_agent_fee
				$sheet->setCellValue('F' . $current_row, ($row['discharging_port'] != 0) ? number_format($row['discharging_port'], 2, '.', '') : '-');											// Column F: discharging_port
				$sheet->setCellValue('G' . $current_row, ($row['inspection_related_work'] != 0) ? number_format($row['inspection_related_work'], 2, '.', '') : '-'); 							// Column G: inspection_related_work
				$sheet->setCellValue('H' . $current_row, ($row['itc'] != 0) ? number_format($row['itc'], 2, '.', '') : '-'); 																	// Column H: itc
				$sheet->setCellValue('I' . $current_row, ($row['empty_cntr_return'] != 0) ? number_format($row['empty_cntr_return'], 2, '.', '') : '-');										// Column I: empty_cntr_return
				$sheet->setCellValue('J' . $current_row, ($row['gate_in'] != 0) ? number_format($row['gate_in'], 2, '.', '') : '-');															// Column J: gate_in
				$sheet->setCellValue('K' . $current_row, ($row['admin_cy'] != 0) ? number_format($row['admin_cy'], 2, '.', '') : '-');															// Column K: admin_cy		
				$sheet->setCellValue('L' . $current_row, ($row['admin_maritime_agent'] != 0) ? number_format($row['admin_maritime_agent'], 2, '.', '') : '-'); 									// Column L: admin_maritime_agent		
				$sheet->setCellValue('M' . $current_row, ($row['checking_seal_number'] != 0) ? number_format($row['checking_seal_number'], 2, '.', '') : '-'); 									// Column M: checking_seal_number		
				$sheet->setCellValue('N' . $current_row, ($row['container_repair'] != 0) ? number_format($row['container_repair'], 2, '.', '') : '-'); 											// Column N: container_repair		
				$sheet->setCellValue('O' . $current_row, ($row['courier'] != 0) ? number_format($row['courier'], 2, '.', '') : '-'); 															// Column O: courier		
				$sheet->setCellValue('P' . $current_row, ($row['discharging_air'] != 0) ? number_format($row['discharging_air'], 2, '.', '') : '-');											// Column P: discharging_air 
				$sheet->setCellValue('Q' . $current_row, ($row['discharging_warehouse'] != 0) ? number_format($row['discharging_warehouse'], 2, '.', '') : '-');								// Column Q: discharging_warehouse
				$sheet->setCellValue('R' . $current_row, ($row['documentation'] != 0) ? number_format($row['documentation'], 2, '.', '') : '-'); 												// Column R: documentation
				$sheet->setCellValue('S' . $current_row, ($row['etc'] != 0) ? number_format($row['etc'], 2, '.', '') : '-');																	// Column S: etc
				$sheet->setCellValue('T' . $current_row, ($row['freight'] != 0) ? number_format($row['freight'], 2, '.', '') : '-'); 															// Column T: freight
				$sheet->setCellValue('U' . $current_row, ($row['handling_charge'] != 0) ? number_format($row['handling_charge'], 2, '.', '') : '-');											// Column U: handling_charge
				$sheet->setCellValue('V' . $current_row, ($row['itc_air'] != 0) ? number_format($row['itc_air'], 2, '.', '') : '-'); 															// Column V: itc_air
				$sheet->setCellValue('W' . $current_row, ($row['itc_lcl'] != 0) ? number_format($row['itc_lcl'], 2, '.', '') : '-'); 															// Column W: itc_lcl
				$sheet->setCellValue('X' . $current_row, ($row['lift_on'] != 0) ? number_format($row['lift_on'], 2, '.', '') : '-'); 															// Column X: lift_on
				$sheet->setCellValue('Y' . $current_row, ($row['stamp'] != 0) ? number_format($row['stamp'], 2, '.', '') : '-');																// Column Y: stamp
				$sheet->setCellValue('Z' . $current_row, $row['ctn_qty']); 																														// Column Z: ctn_qty
				$sheet->setCellValue('AA' . $current_row, $row['completed']); 																													// Column AA: completed
				$sheet->setCellValue('AB' . $current_row,  ($row['customs_agent_fee_div_total'] != 0) ? number_format($row['customs_agent_fee_div_total'], 2, '.', '') : '-'); 					// Column AB: customs_agent_fee_div_total
				$sheet->setCellValue('AC' . $current_row,  ($row['discharging_port_div_total'] != 0) ? number_format($row['discharging_port_div_total'], 2, '.', '') : '-'); 					// Column AC: discharging_port_div_total
				$sheet->setCellValue('AD' . $current_row,  ($row['inspection_related_work_div_total'] != 0) ? number_format($row['inspection_related_work_div_total'], 2, '.', '') : '-'); 		// Column AD: inspection_related_work_div_total
				$sheet->setCellValue('AE' . $current_row,  ($row['itc_div_total'] != 0) ? number_format($row['itc_div_total'], 2, '.', '') : '-'); 												// Column AE: itc_div_total
				$sheet->setCellValue('AF' . $current_row,  ($row['empty_cntr_return_div_total'] != 0) ? number_format($row['empty_cntr_return_div_total'], 2, '.', '') : '-'); 					// Column AF: empty_cntr_return_div_total
				$sheet->setCellValue('AG' . $current_row,  ($row['gate_in_div_total'] != 0) ? number_format($row['gate_in_div_total'], 2, '.', '') : '-'); 										// Column AG: gate_in_div_total
				$sheet->setCellValue('AH' . $current_row,  ($row['total_regular'] != 0) ? number_format($row['total_regular'], 2, '.', '') : '-'); 												// Column AH: total_regular
				$sheet->setCellValue('AI' . $current_row,  ($row['total_optional'] != 0) ? number_format($row['total_optional'], 2, '.', '') : '-'); 											// Column AI: total_optional
				$sheet->setCellValue('AJ' . $current_row,  ($row['customs_agent_fee_accrual'] != 0) ? number_format($row['customs_agent_fee_accrual'], 2, '.', '') : '-'); 						// Column AJ: customs_agent_fee_accrual
				$sheet->setCellValue('AK' . $current_row,  ($row['discharging_port_accrual'] != 0) ? number_format($row['discharging_port_accrual'], 2, '.', '') : '-'); 						// Column AK: discharging_port_accrual
				$sheet->setCellValue('AL' . $current_row,  ($row['inspection_related_work_accrual'] != 0) ? number_format($row['inspection_related_work_accrual'], 2, '.', '') : '-'); 			// Column AL: inspection_related_work_accrual
				$sheet->setCellValue('AM' . $current_row,  ($row['itc_accrual'] != 0) ? number_format($row['itc_accrual'], 2, '.', '') : '-'); 													// Column AM: itc_accrual
				$sheet->setCellValue('AN' . $current_row,  ($row['empty_cntr_return_accrual'] != 0) ? number_format($row['empty_cntr_return_accrual'], 2, '.', '') : '-'); 						// Column AN: empty_cntr_return_accrual		
				$sheet->setCellValue('AO' . $current_row,  ($row['gate_in_accrual'] != 0) ? number_format($row['gate_in_accrual'], 2, '.', '') : '-'); 											// Column AO: gate_in_accrual
				$sheet->setCellValue('AP' . $current_row,  ($row['total_accrual'] != 0) ? number_format($row['total_accrual'], 2, '.', '') : '-'); 												// Column AP: total_accrual
                                                			
				
				$sheet->getStyle('Z' . $current_row)
					  ->getFill()
					  ->setFillType(Fill::FILL_SOLID)
					  ->getStartColor()
					  ->setRGB('FFF2CC');
					  
				 $current_row++; 
            }
        }
		
		$filename = "REGISTRO " . date('Y') . " TOTAL " . date('Ymd') . ".xlsx";
		ob_end_clean(); //Clean buffer	
			
        // Header configuration for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');			
     			
        $writer = new Xlsx($spreadsheet); 	

        // Save output directly to the browser 
        $writer->save('php://output');	
        exit;
	}
		
	public function upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'custom_container_cost.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach_primary')){
				$msg = $this->process();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
		
	public function process_release_container(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/custom_release_container.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

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
		];

		$header = ["WK", "PUERTO", "NUMERO FILE LG", "# ORDEN AGENCIA", "Transport Mode", "Mode", "Nave", "Vencimiento descarga"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
		
		$key_list = [];
		$container_release = $this->gen_m->filter_select('custom_release_container', false, 'key_release_container');
		foreach($container_release as $item) $key_list[] = $item->key_release_container;

		if ($is_ok){
			$updated = date("Y-m-d H:i:s");
			$max_row = $sheet->getHighestRow();
			$inserted_rows = 0; $updated_rows = 0;
			$batch_data =[]; $batch_data_update = [];
			$batch_size = 300;
			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"wk"						=> trim($sheet->getCell('A'.$i)->getValue()),
					"port"						=> trim($sheet->getCell('B'.$i)->getValue()),
					"num_file_lg"				=> trim($sheet->getCell('C'.$i)->getValue()),
					"num_agency_order"			=> trim($sheet->getCell('D'.$i)->getValue()),
					"transport_mode"			=> trim($sheet->getCell('E'.$i)->getValue()),
					"mode"						=> trim($sheet->getCell('F'.$i)->getValue()),
					"nave"						=> trim($sheet->getCell('G'.$i)->getValue()),
					"laytime_expiration"		=> trim($sheet->getCell('H'.$i)->getValue()),
					"carrier_name"				=> trim($sheet->getCell('I'.$i)->getValue()),
					"bl_number"					=> trim($sheet->getCell('J'.$i)->getValue()),
					"house_bl_number" 			=> trim($sheet->getCell('K'.$i)->getValue()),
					"container_number" 			=> trim($sheet->getCell('L'.$i)->getValue()),
					"product" 					=> trim($sheet->getCell('M'.$i)->getValue()),
					"model" 					=> trim($sheet->getCell('N'.$i)->getValue()),
					"qty" 						=> trim($sheet->getCell('O'.$i)->getValue()),
					"m3" 						=> trim($sheet->getCell('P'.$i)->getValue()),
					"date" 						=> trim($sheet->getCell('Q'.$i)->getValue()),
					"appointment_port" 			=> trim($sheet->getCell('R'.$i)->getValue()),
					"destiny" 					=> trim($sheet->getCell('S'.$i)->getValue()),
					"warehouse_arrival" 		=> trim($sheet->getCell('T'.$i)->getValue()),
					"transport" 				=> trim($sheet->getCell('U'.$i)->getValue()),
					"validation" 				=> trim($sheet->getCell('V'.$i)->getValue()),
					"comments"					=> trim($sheet->getCell('W'.$i)->getValue()),
					"last_updated"				=> $updated,
				];
				
				if (empty($row['num_file_lg']) && empty($row['num_agency_order']) && empty($row['nave']) && empty($row['house_bl_number'])) continue;
				$row['key_release_container'] = $row['wk'] . "_" . $row['mode'] . "_" . $row['nave'] . "_" . $row['laytime_expiration'] . "_" . $row['house_bl_number'] . "_" . $row['container_number'] . "_" . $row['model'] . "_" . $row['date'];
				
				$row['laytime_expiration'] = $this->convert_date($row['laytime_expiration']);
				$row['date'] = $this->convert_date($row['date']);
				$row['appointment_port'] = $this->convert_date($row['appointment_port']);
				$row['warehouse_arrival'] = $this->convert_date($row['warehouse_arrival']);
				
				//echo '<pre>'; print_r($row);

				if (!in_array($row['key_release_container'], $key_list)) $batch_data[] = $row;
				else $batch_data_update[] = $row;;
				
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("custom_release_container", $batch_data);
					$inserted_rows += count($batch_data);
					$batch_data = [];
					unset($batch_data);
				}
				if(count($batch_data_update)>=$batch_size){
					$this->gen_m->update_multi("custom_release_container", $batch_data_update, 'key_release_container');
					$updated_rows += count($batch_data_update);
					$batch_data_update = [];
					unset($batch_data_update);
				}
			}
			
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("custom_release_container", $batch_data);
				$inserted_rows += count($batch_data);
				$batch_data = [];
				unset($batch_data);
			}
			if (!empty($batch_data_update)) {
				$this->gen_m->update_multi("custom_release_container", $batch_data_update, 'key_release_container');
				$updated_rows += count($batch_data_update);
				$batch_data_update = [];
				unset($batch_data_update);
			}
			
			$msg = $inserted_rows . " rows inserted.<br>";
			$msg .= $updated_rows . " rows updated.<br>";
			$msg .= "Total time: " . number_Format(microtime(true) - $start_time, 2) . " secs.";
			$this->db->trans_complete();
			return $msg;		
		}else return "";
	}
	
	public function upload_release_container(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'custom_release_container.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach_secondary')){
				$msg = $this->process_release_container();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
