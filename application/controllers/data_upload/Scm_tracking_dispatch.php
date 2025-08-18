<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Scm_tracking_dispatch extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$tracking = $this->gen_m->filter("scm_tracking_dispatch", false);
		$data = [
			"tracking"		=> $tracking,
			"count_tracking" => count($tracking),
			"main" 			=> "data_upload/scm_tracking_dispatch/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function date_convert_dd_mm_yyyy($date) {
		if (is_numeric($date)) {
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
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
                $date_object = DateTime::createFromFormat($format, $date_input);

                if ($date_object !== false) {
                    return $date_object->format('Y-m-d H:i:s');
                }
            }
        }

		return null;
	}
	
	public function convert_hour($date_input) {
        if (!is_numeric($date_input) || is_null($date_input) || $date_input === '') {
            return null;
        }

        $date_input = (float) $date_input;
		
        if ($date_input < 0 || $date_input >= 1) {
            return null;
        }

        $total_seconds_in_day = $date_input * 24 * 60 * 60; // 24 horas * 60 minutos * 60 segundos

        // Extraer horas, minutos y segundos
        $hours = floor($total_seconds_in_day / 3600); // 3600 segundos en una hora
        $minutes = floor(($total_seconds_in_day % 3600) / 60); // Segundos restantes después de las horas, divididos por 60
        $seconds = floor($total_seconds_in_day % 60); // Segundos restantes después de los minutos

        $time_formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        return $time_formatted;
	}
	
	public function convertToTimeFormat($excel_input){
        $excel_epoch_start_timestamp = strtotime('1899-12-30');
		
		if (is_null($excel_input) || (is_string($excel_input) && trim($excel_input) === '')) {
			return null;
		}
       
        $input_as_string = (string) $excel_input;
        $dateTimeObj = null;

		if (is_numeric($excel_input)) {
			$excel_serial_value = floatval($excel_input);
			$excel_time_fraction = fmod($excel_serial_value, 1);

            $total_seconds_in_day = fmod($excel_serial_value, 1) * 24 * 60 * 60;

            $dateTimeObj = new DateTime('1970-01-01');
            $dateTimeObj->setTimestamp($dateTimeObj->getTimestamp() + round($total_seconds_in_day));
            
            if ($dateTimeObj) {
                return $dateTimeObj->format('H:i:s');
            }
        }

        $time_only_formats = [
            'H:i:s A', // "12:00:00 PM", "08:00:00 AM"
            'h:i:s A', // "08:00:00 AM" (formato con h minúsculo para 12 horas)
            'H:i A',   // "12:00 PM", "08:00 AM"
            'h:i A',   // "08:00 AM"
            'H:i:s',   // "12:00:00" (24 horas)
            'H:i',     // "12:00" (24 horas)
            'g:i A',   // "8:00 AM" (sin ceros iniciales en hora)
            'g:i:s A', // "8:00:00 AM" (sin ceros iniciales en hora)
        ];

        foreach ($time_only_formats as $format) {
            $tempDateTimeObj = DateTime::createFromFormat($format, $input_as_string);
            
            // Validar que se haya parseado correctamente y que la cadena original coincida con el formato
            $formatted_back = $tempDateTimeObj ? $tempDateTimeObj->format($format) : '';
            if (
                $tempDateTimeObj !== false &&
                (
                    (strpos($format, 'A') !== false && strtolower($formatted_back) === strtolower($input_as_string)) ||
                    (strpos($format, 'A') === false && $formatted_back === $input_as_string)
                )
            ) {
                $dateTimeObj = $tempDateTimeObj;
                break;
            }
        }

        if (!$dateTimeObj) { 
            $input_normalized = str_ireplace(
                ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC',
                 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
                ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
                 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                $input_as_string
            );

            $possible_datetime_formats = [
                'n/j/Y g:i:s A', // "7/10/2025 8:00:00 AM"
                'n/j/Y G:i:s',   // "7/10/2025 8:00:00"
                'Y-m-d H:i:s',
                'd-M-Y H:i:s',
                'd M Y H:i:s',
                'd/m/Y H:i:s',
                'd-m-Y H:i:s',
                'Y-m-d',
                'd/m/Y',
                'd-m-Y'
            ];

            foreach ($possible_datetime_formats as $format) {
                $tempDateTimeObj = DateTime::createFromFormat($format, $input_normalized);
                if ($tempDateTimeObj !== false && $tempDateTimeObj->format($format) === $input_normalized) {
                    $dateTimeObj = $tempDateTimeObj;
                    break;
                }
            }
        }

        if ($dateTimeObj) {
            return $dateTimeObj->format('H:i:s');
        }

        return null;
	}
	
	public function process_tracking_klo($filename = "scm_tracking_klo.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		
		$klo_data = $this->gen_m->filter_select('scm_tracking_dispatch', false, ['tracking_key'], ['_3pl' => 'KLO']);

		$klo_track = [];
		foreach ($klo_data as $item) $klo_track[] = $item->tracking_key;
		$klo_track = array_filter($klo_track);

		//$this->gen_m->truncate("scm_tracking_dispatch");
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows_og = [];
		$rows_req = [];
			
		$updated = date("Y-m-d H:i:s");
		$max_row = $sheet->getHighestRow();
		for($i = 3; $i <= $max_row; $i++){
			$row = [
				"_3pl"						=> 'KLO',
				"date" 						=> trim($sheet->getCell('A'.$i)->getValue()) ?? '',
				"actual_load_date" 			=> !empty(trim($sheet->getCell('B'.$i)->getValue())) ? trim($sheet->getCell('B'.$i)->getValue()) : null,
				"transport"					=> trim($sheet->getCell('C'.$i)->getValue()) ?? null,
				"placa" 					=> trim($sheet->getCell('D'.$i)->getValue()) ?? null,
				"movil" 					=> trim($sheet->getCell('E'.$i)->getValue()) ?? null,
				"ut_load_arrival_time" 		=> !empty(trim($sheet->getCell('F'.$i)->getValue())) ? trim($sheet->getCell('F'.$i)->getValue()) : null,
				"actual_load_time"			=> !empty(trim($sheet->getCell('G'.$i)->getValue())) ? trim($sheet->getCell('G'.$i)->getValue()) : null,
				"load_end_time"				=> !empty(trim($sheet->getCell('H'.$i)->getValue())) ? trim($sheet->getCell('H'.$i)->getValue()) : null,			
				"load_status"				=> trim($sheet->getCell('I'.$i)->getValue()) ?? null,
				"container_district" 		=> trim($sheet->getCell('J'.$i)->getValue()) ?? null,
				"customer" 					=> trim($sheet->getCell('K'.$i)->getValue()) ?? null,
				"address" 					=> trim($sheet->getCell('L'.$i)->getValue()) ?? null,
				"pick_order"				=> trim($sheet->getCell('M'.$i)->getValue()) ?? null,
				"service_type"				=> trim($sheet->getCell('N'.$i)->getValue()) ?? null,				
				"district"					=> trim($sheet->getCell('O'.$i)->getValue()) ?? null,			
				"b2c_zone"					=> trim($sheet->getCell('P'.$i)->getValue()) ?? null,
				"ot_per_point" 				=> trim($sheet->getCell('Q'.$i)->getValue()) ?? null,
				"purchase_order" 			=> trim($sheet->getCell('R'.$i)->getValue()) ?? null,
				"guide" 					=> trim($sheet->getCell('S'.$i)->getCalculatedValue()) ?? null,
				"model"						=> trim($sheet->getCell('T'.$i)->getValue()) ?? null,
				"qty"						=> trim($sheet->getCell('U'.$i)->getValue()) ?? null,				
				"cbm"						=> trim($sheet->getCell('V'.$i)->getValue()) ?? null,				
				"cbm_per_unit"				=> trim($sheet->getCell('W'.$i)->getCalculatedValue()) ?? null,			
				"rejected_qty"				=> trim($sheet->getCell('X'.$i)->getValue()) ?? null,
				"rejected_cbm" 				=> trim($sheet->getCell('Y'.$i)->getCalculatedValue()) ?? null,
				"delivered_cbm" 			=> trim($sheet->getCell('Z'.$i)->getCalculatedValue()) ?? null,
				"observation" 				=> trim($sheet->getCell('AA'.$i)->getValue()) ?? null,
				"client_appointment"		=> trim($sheet->getCell('AB'.$i)->getValue()) ?? null,
				"to_appointment"			=> trim($sheet->getCell('AC'.$i)->getValue()) ?? null,			
				"arrival_time"				=> trim($sheet->getCell('AD'.$i)->getValue()) ?? null,			
				"download_time"				=> trim($sheet->getCell('AE'.$i)->getValue()) ?? null,
				"completion_time" 			=> !empty(trim($sheet->getCell('AF'.$i)->getValue())) ? trim($sheet->getCell('AF'.$i)->getValue()) : null,
				"service_completion_time"	=> !empty(trim($sheet->getCell('AG'.$i)->getValue())) ? trim($sheet->getCell('AG'.$i)->getValue()) : null,
				"waiting_time" 				=> trim($sheet->getCell('AH'.$i)->getValue()) ?? null,
				"status"					=> trim($sheet->getCell('AI'.$i)->getValue()) ?? null,
				"status_2"					=> trim($sheet->getCell('AJ'.$i)->getValue()) ?? null,
				"observations"				=> trim($sheet->getCell('AK'.$i)->getValue()) ?? null,
				"otd"						=> trim($sheet->getCell('AL'.$i)->getValue()) ?? null,
				"updated"					=> $updated,
			];
			
			// Convert Dates
			$row["date"] = $this->date_convert_dd_mm_yyyy($row["date"]);	
			$row["client_appointment"] = $this->convertToTimeFormat($row["client_appointment"]);
			$row["to_appointment"] = $this->convertToTimeFormat($row["to_appointment"]);
			// $row["arrival_time"] = $this->convertToTimeFormat($row["arrival_time"]);
			// $row["download_time"] = $this->convertToTimeFormat($row["download_time"]);
			// $row["completion_time"] = $this->convertToTimeFormat($row["completion_time"]);
			// $row["service_completion_time"] = $this->convertToTimeFormat($row["service_completion_time"]);
			// $row["waiting_time"] = $this->convertToTimeFormat($row["waiting_time"]);
			
			if(strpos($row['status'], 'CANCELADO') !== false){
				//contiene
				$row["arrival_time"] = null;
				$row["download_time"] = null;
				$row["completion_time"] = null;
				$row["service_completion_time"] = null;
				$row["waiting_time"] = null;
				$row["cbm_per_unit"] = null;			
				$row["rejected_qty"] = null;
				$row["rejected_cbm"] = null;
				$row["delivered_cbm"] = null;
			} else{
				$row["arrival_time"] = $this->convertToTimeFormat($row["arrival_time"]);
				$row["download_time"] = $this->convertToTimeFormat($row["download_time"]);
				$row["completion_time"] = $this->convertToTimeFormat($row["completion_time"]);
				$row["service_completion_time"] = $this->convertToTimeFormat($row["service_completion_time"]);
				$row["waiting_time"] = $this->convertToTimeFormat($row["waiting_time"]);
			}
			
			// if ($row["date"] !== null && $row["placa"] !== null && $row["guide"] !== null && $row["model"] !== null && $row["qty"] !== null && $row["cbm"] !== null){
				// $row["tracking_key"] = $row["date"] . "_" . $row["placa"] . "_" . $row["guide"] . "_" . $row["model"] . "_" . $row["qty"] . "_" . $row["cbm"];
			// } else continue;
			
			if ($row["date"] !== null && $row["placa"] !== null && $row["model"] !== null && $row["qty"] !== null && $row["cbm"] !== null){
				$row["tracking_key"] = $row["date"] . "_" . $row["placa"] . "_" . $row["model"] . "_" . $row["qty"] . "_" . $row["cbm"];
			} else continue;
			
			if (in_array($row["tracking_key"], $klo_track)) $rows_req[] = $row;
			elseif(!in_array($row["tracking_key"], $klo_track)) $rows_og[] = $row; 
		}
		
		$rows_split_eq = array_chunk($rows_req, 50);
		foreach($rows_split_eq as $items) $this->gen_m->update_multi("scm_tracking_dispatch", $items, 'tracking_key');
		
		$rows_split = array_chunk($rows_og, 50);
		foreach($rows_split as $items) $this->gen_m->insert_m('scm_tracking_dispatch', $items);
		
		return "Stock update has been finished. (".$updated.")";
	}
	
	public function process_tracking_apm($filename = "scm_tracking_apm.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		//$this->gen_m->truncate("scm_tracking_dispatch");
		
		$apm_data = $this->gen_m->filter_select('scm_tracking_dispatch', false, ['tracking_key'], ['_3pl' => 'APM']);

		$apm_track = [];
		foreach ($apm_data as $item) $apm_track[] = $item->tracking_key;
		$apm_track = array_filter($apm_track);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows_og = [];
		$rows_req = [];
			
		$updated = date("Y-m-d H:i:s");
		$max_row = $sheet->getHighestRow();
		for($i = 3; $i <= $max_row; $i++){
			$row = [
				"_3pl"						=> 'APM',
				"date" 						=> trim($sheet->getCell('A'.$i)->getValue()) ?? '',
				"actual_load_date" 			=> !empty(trim($sheet->getCell('B'.$i)->getValue())) ? trim($sheet->getCell('B'.$i)->getValue()) : null,
				"transport"					=> trim($sheet->getCell('C'.$i)->getValue()) ?? null,
				"placa" 					=> trim($sheet->getCell('D'.$i)->getValue()) ?? null,
				"movil" 					=> trim($sheet->getCell('E'.$i)->getValue()) ?? null,
				"ut_load_arrival_time" 		=> !empty(trim($sheet->getCell('F'.$i)->getValue())) ? trim($sheet->getCell('F'.$i)->getValue()) : null,
				"actual_load_time"			=> !empty(trim($sheet->getCell('G'.$i)->getValue())) ? trim($sheet->getCell('G'.$i)->getValue()) : null,
				"load_end_time"				=> !empty(trim($sheet->getCell('H'.$i)->getValue())) ? trim($sheet->getCell('H'.$i)->getValue()) : null,			
				"load_status"				=> trim($sheet->getCell('I'.$i)->getValue()) ?? null,
				"container_district" 		=> trim($sheet->getCell('J'.$i)->getValue()) ?? null,
				"customer" 					=> trim($sheet->getCell('K'.$i)->getValue()) ?? null,
				"address" 					=> trim($sheet->getCell('L'.$i)->getValue()) ?? null,
				"pick_order"				=> trim($sheet->getCell('M'.$i)->getValue()) ?? null,
				"service_type"				=> trim($sheet->getCell('P'.$i)->getValue()) ?? null,	 // Column P	
				"district"					=> trim($sheet->getCell('O'.$i)->getValue()) ?? null,	 		
				"b2c_zone"					=> null,
				"ot_per_point" 				=> trim($sheet->getCell('Q'.$i)->getValue()) ?? null,
				"purchase_order" 			=> trim($sheet->getCell('R'.$i)->getValue()) ?? null,
				"guide" 					=> trim($sheet->getCell('S'.$i)->getCalculatedValue()) ?? null,
				"model"						=> trim($sheet->getCell('T'.$i)->getValue()) ?? null,
				"qty"						=> trim($sheet->getCell('U'.$i)->getValue()) ?? null,				
				"cbm"						=> trim($sheet->getCell('V'.$i)->getValue()) ?? null,				
				"cbm_per_unit"				=> trim($sheet->getCell('W'.$i)->getCalculatedValue()) ?? null,			
				"rejected_qty"				=> trim($sheet->getCell('X'.$i)->getValue()) ?? null,
				"rejected_cbm" 				=> trim($sheet->getCell('Y'.$i)->getCalculatedValue()) ?? null,
				"delivered_cbm" 			=> trim($sheet->getCell('Z'.$i)->getCalculatedValue()) ?? null,
				"observation" 				=> trim($sheet->getCell('AA'.$i)->getValue()) ?? null,
				"client_appointment"		=> trim($sheet->getCell('AB'.$i)->getValue()) ?? null,
				"to_appointment"			=> trim($sheet->getCell('AC'.$i)->getValue()) ?? null,			
				"arrival_time"				=> trim($sheet->getCell('AD'.$i)->getValue()) ?? null,			
				"download_time"				=> trim($sheet->getCell('AE'.$i)->getValue()) ?? null,
				"completion_time" 			=> !empty(trim($sheet->getCell('AF'.$i)->getValue())) ? trim($sheet->getCell('AF'.$i)->getValue()) : null,
				"service_completion_time"	=> !empty(trim($sheet->getCell('AG'.$i)->getValue())) ? trim($sheet->getCell('AG'.$i)->getValue()) : null,
				"waiting_time" 				=> trim($sheet->getCell('AH'.$i)->getValue()) ?? null,
				"status"					=> trim($sheet->getCell('AI'.$i)->getValue()) ?? null,
				"status_2"					=> trim($sheet->getCell('AJ'.$i)->getValue()) ?? null,
				"observations"				=> trim($sheet->getCell('AK'.$i)->getValue()) ?? null,
				"otd"						=> trim($sheet->getCell('AL'.$i)->getValue()) ?? null,
				"updated"					=> $updated,
			];
			
			
			// Convert Dates
			$row["date"] = $this->date_convert_dd_mm_yyyy($row["date"]);			
			$row["client_appointment"] = $this->convertToTimeFormat($row["client_appointment"]);
			$row["to_appointment"] = $this->convertToTimeFormat($row["to_appointment"]);
			
			
			if(strpos($row['status'], 'CANCELADO') !== false){
				//contiene
				$row["arrival_time"] = null;
				$row["download_time"] = null;
				$row["completion_time"] = null;
				$row["service_completion_time"] = null;
				$row["waiting_time"] = null;
				$row["cbm_per_unit"] = null;			
				$row["rejected_qty"] = null;
				$row["rejected_cbm"] = null;
				$row["delivered_cbm"] = null;
			} else{
				$row["arrival_time"] = $this->convertToTimeFormat($row["arrival_time"]);
				$row["download_time"] = $this->convertToTimeFormat($row["download_time"]);
				$row["completion_time"] = $this->convertToTimeFormat($row["completion_time"]);
				$row["service_completion_time"] = $this->convertToTimeFormat($row["service_completion_time"]);
				$row["waiting_time"] = $this->convertToTimeFormat($row["waiting_time"]);
			}
			//echo '<pre>'; print_r($row);
			// if ($row["date"] !== null && $row["placa"] !== null && $row["guide"] !== null && $row["model"] !== null && $row["qty"] !== null && $row["cbm"] !== null){
				// $row["tracking_key"] = $row["date"] . "_" . $row["placa"] . "_" . $row["guide"] . "_" . $row["model"] . "_" . $row["qty"] . "_" . $row["cbm"];
			// } else continue;

			if ($row["date"] !== null && $row["placa"] !== null && $row["model"] !== null && $row["qty"] !== null && $row["cbm"] !== null){
				$row["tracking_key"] = $row["date"] . "_" . $row["placa"] . "_" . $row["model"] . "_" . $row["qty"] . "_" . $row["cbm"];
			} else continue;
			
			if (in_array($row["tracking_key"], $apm_track)) $rows_req[] = $row;
			elseif(!in_array($row["tracking_key"], $apm_track)) $rows_og[] = $row; 
			
		}
		//echo '<pre>'; print_r($rows_req);
		$rows_split_eq = array_chunk($rows_req, 5);
		foreach($rows_split_eq as $items) $this->gen_m->update_multi("scm_tracking_dispatch", $items, 'tracking_key');
		
		$rows_split = array_chunk($rows_og, 5);
		foreach($rows_split as $items) $this->gen_m->insert_m('scm_tracking_dispatch', $items);
		
		return "Stock update has been finished. (".$updated.")";
	}
	
	public function upload_tracking_klo(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'scm_tracking_klo.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_tracking_klo();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function upload_tracking_apm(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'scm_tracking_apm.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_tracking_apm();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
