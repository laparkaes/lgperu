<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_shipping_status extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"shipping_status"	=> $this->gen_m->filter("scm_shipping_status", false, null, null, null, [], 1000, ""),
			"main" 				=> "data_upload/scm_shipping_status/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function convert_date($date_input) {
		$excel_epoch_start_timestamp = strtotime('1899-12-30');
		
		if (empty($date_input)){
			return null;
		}
		
		elseif (is_numeric($date_input) && floatval($date_input) > 1) {
			$excel_date_days = floor(floatval($date_input));
			$excel_time_fraction = floatval($date_input) - $excel_date_days;

			$timestamp = $excel_epoch_start_timestamp + ($excel_date_days * 86400) + ($excel_time_fraction * 86400);

			$date_object = new DateTime();
			$date_object->setTimestamp($timestamp);

			return $date_object->format('Y-m-d H:i:s');
		} else{

            $possible_formats = [
                'd-M-Y H:i:s', // "25-JUN-2025 15:00:00"
                'd M Y H:i:s', // "03 JUN 2025 08:00"
                'Y-m-d H:i:s', // Para formatos ya estándar
                'd/m/Y H:i:s', // Otro formato común
                'd-m-Y H:i:s', // Otro formato común
            ];

            foreach ($possible_formats as $format) {
                // Intenta crear el objeto DateTime con la cadena normalizada
                $date_object = DateTime::createFromFormat($format, $date_input);

                if ($date_object !== false) {
					return $date_object->format('Y-m-d H:i:s');
                }
            }
        }

		return null;
	}
		
	public function test(){
		$key_pick = [];
		$key_picks = $this->gen_m->filter_select('scm_shipping_status', false, ['key_pick']);
		foreach ($key_picks as $item) $key_pick[] = $item->key_pick;
		$key_pick = array_values(array_unique($key_pick));
		echo '<pre>'; print_r($key_pick);
	}
	
	public function process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		//delete all rows scm_shipping_status 
		//$this->gen_m->truncate("scm_shipping_status");
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/scm_shipping_status.xls");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation		
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue())
		];
		//echo '<pre>'; print_r($h);
		//sales order header
		$header = ["Order No", "Line No", "Pick No", "Ship Set", "Seq", "Customer PO"];
		 
		//echo '<pre>'; print_r($header);
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
		
		if ($is_ok){
			$max_row = $sheet->getHighestRow();
			$batch_size = 1000;
			$batch_data = [];
			$batch_data_eq = [];
			//define now
			$now = date('Y-m-d H:i:s');
			
			$key_pick = [];
			$key_picks = $this->gen_m->filter_select('scm_shipping_status', false, ['key_pick']);
			foreach ($key_picks as $item) $key_pick[] = $item->key_pick;
			$key_pick = array_values(array_unique($key_pick));
			//echo '<pre>'; print_r($key_pick);
			
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					'order_no' 				=> trim($sheet->getCell('A'.$i)->getValue()),
					'line_no' 				=> trim($sheet->getCell('B'.$i)->getValue()),
					'pick_no' 				=> trim($sheet->getCell('C'.$i)->getValue()),
					'seq' 					=> trim($sheet->getCell('E'.$i)->getValue()),
					'key_pick' 				=> trim($sheet->getCell('C'.$i)->getValue()). "_" . trim($sheet->getCell('E'.$i)->getValue()),
					'ship_set' 				=> trim($sheet->getCell('D'.$i)->getValue()),
					'customer_po' 			=> trim($sheet->getCell('F'.$i)->getValue()),
					'order_type' 			=> trim($sheet->getCell('G'.$i)->getValue()),
					'bill_to_code' 			=> trim($sheet->getCell('H'.$i)->getValue()),
					'bill_to_name'			=> trim($sheet->getCell('I'.$i)->getValue()),
					'code' 					=> trim($sheet->getCell('J'.$i)->getValue()),
					'name' 					=> trim($sheet->getCell('K'.$i)->getValue()),
					'route' 				=> trim($sheet->getCell('L'.$i)->getValue()),
					'tel'					=> trim($sheet->getCell('M'.$i)->getValue()),
					'address' 				=> trim($sheet->getCell('N'.$i)->getValue()),
					'postal_code' 			=> trim($sheet->getCell('O'.$i)->getValue()),
					'state'					=> trim($sheet->getCell('P'.$i)->getValue()),
					'city' 					=> trim($sheet->getCell('Q'.$i)->getValue()),
					'inventory_org' 		=> trim($sheet->getCell('R'.$i)->getValue()),
					'sub_inventory' 		=> trim($sheet->getCell('S'.$i)->getValue()),
					'prod_gr2'				=> trim($sheet->getCell('T'.$i)->getValue()),
					'model_category'		=> trim($sheet->getCell('U'.$i)->getValue()),
					'model'					=> trim($sheet->getCell('V'.$i)->getValue()),
					'status'				=> trim($sheet->getCell('W'.$i)->getValue()),
					'order_qty' 			=> trim($sheet->getCell('X'.$i)->getValue()),
					'requested_qty' 		=> trim($sheet->getCell('Y'.$i)->getValue()),
					'pick_release_qty' 		=> trim($sheet->getCell('Z'.$i)->getValue()),
					'picked_qty'			=> trim($sheet->getCell('AA'.$i)->getValue()),
					'shipped_qty' 			=> trim($sheet->getCell('AB'.$i)->getValue()),
					'total_volume' 			=> trim($sheet->getCell('AC'.$i)->getValue()),
					'total_weight' 			=> trim($sheet->getCell('AD'.$i)->getValue()),
					'palletization' 		=> trim($sheet->getCell('AE'.$i)->getValue()),
					'shpt_priority'			=> trim($sheet->getCell('AF'.$i)->getValue()),
					'order_date' 			=> trim($sheet->getCell('AG'.$i)->getValue()),
					'from' 					=> trim($sheet->getCell('AH'.$i)->getValue()),
					'to' 					=> trim($sheet->getCell('AI'.$i)->getValue()),
					'req_ship_date_from' 	=> trim($sheet->getCell('AJ'.$i)->getValue()),
					'from_ship' 			=> trim($sheet->getCell('AK'.$i)->getValue()),
					'to_ship' 				=> trim($sheet->getCell('AL'.$i)->getValue()),
					'delivery_no'			=> trim($sheet->getCell('BQ'.$i)->getValue()),
					'updated' 				=> $now,
				];
				
				//echo '<pre>'; print_r($row);
				//apply trim
				$row["order_no"] = trim($row["order_no"]);
				$row["line_no"] = trim($row["line_no"]);	
				
				$row["order_date"] = $this->convert_date($row["order_date"]);
				$row["from"] = $this->convert_date($row["from"]);
				$row["to"] = $this->convert_date($row["to"]);
				$row["req_ship_date_from"] = $this->convert_date($row["req_ship_date_from"]);
				$row["from_ship"] = $this->convert_date($row["from_ship"]);
				$row["to_ship"] = $this->convert_date($row["to_ship"]);
				
				if (substr($row['delivery_no'], 0, 1) !== "T"){
					$aux = trim($sheet->getCell('BS'.$i)->getValue());
					if (substr($aux, 0, 1) === "T") $row['delivery_no'] = $aux;
					else $row['delivery_no'] = null;
				}

				if (in_array($row['key_pick'], $key_pick)){
					$batch_data_eq[] = $row; 
				}elseif(!in_array($row['key_pick'], $key_pick)) $batch_data[] = $row;
				
				//$batch_data[] = $row;
				
				if (count($batch_data) >= $batch_size) {
					//echo '<pre>'; print_r($batch_data);
					$this->gen_m->insert_m("scm_shipping_status", $batch_data);
					$batch_data = [];
				}
				if (count($batch_data_eq) >= $batch_size) {
					//echo '<pre>'; print_r($batch_data_eq);
					$this->gen_m->update_multi("scm_shipping_status", $batch_data_eq, 'key_pick');
					$batch_data_eq = [];
				}
			}
			
			//echo '<pre>'; print_r($batch_data);
			
			if (!empty($batch_data)) {
				//echo '<pre>'; print_r($batch_data);
				$this->gen_m->insert_m("scm_shipping_status", $batch_data);
				$batch_data = [];
			}
			if (!empty($batch_data_eq)) {
				//echo '<pre>'; print_r($batch_data_eq); return;
				$this->gen_m->update_multi("scm_shipping_status", $batch_data_eq, 'key_pick');
				$batch_data_eq = [];
			}
			
			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
			//print_r($msg); return;
			return $msg;
		} else return "";
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
				'file_name'		=> 'scm_shipping_status.xls',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
