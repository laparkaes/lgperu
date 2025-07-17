<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Scm_container_reception extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){

		$tracking = $this->gen_m->filter("scm_container_reception", false);
		$data = [
			"tracking"		=> $tracking,
			"count_tracking" => count($tracking),
			"main" 			=> "data_upload/scm_container_reception/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function date_convert_dd_mm_yyyy($date) {
		if (is_numeric($date)) {
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		// Si no es un número, intentamos convertir con la lógica de fecha en formato dd/mm/yyyy
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
	
	public function process_container($filename = "scm_container_reception.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		
		$data = $this->gen_m->filter_select('scm_container_reception', false, ['tracking_key']);

		$track = [];
		foreach ($data as $item) $track[] = $item->tracking_key;
		$track = array_filter($track);

		//$this->gen_m->truncate("scm_tracking_dispatch");
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows_og = [];
		$rows_req = [];
			
		$updated = date("Y-m-d H:i:s");
		$max_row = $sheet->getHighestRow();
		for($i = 3; $i <= $max_row; $i++){
			$aux = explode(' ', $sheet->getCell('C'.$i)->getValue(), 2);
			//echo '<pre>'; print_r($aux);
			$row = [
				"_3pl"						=> $aux[0] === 'MAERSK' ? 'APM' : 'KLO',
				"received_container" 		=> trim($sheet->getCell('A'.$i)->getValue()) ?? '',
				"customer" 					=> trim($sheet->getCell('B'.$i)->getValue()) ?? null,
				"destination"				=> trim($sheet->getCell('C'.$i)->getValue()) ?? null,				
				"discharge_location" 		=> trim($sheet->getCell('D'.$i)->getValue()) ?? null,			
				"arrival_date" 				=> !empty(trim($sheet->getCell('E'.$i)->getValue())) ? trim($sheet->getCell('E'.$i)->getValue()) : null,
				"arrival_time" 				=> !empty(trim($sheet->getCell('F'.$i)->getValue())) ? trim($sheet->getCell('F'.$i)->getValue()) : null,
				"order_no"					=> trim($sheet->getCell('G'.$i)->getCalculatedValue()) ?? null,			
				"line"						=> trim($sheet->getCell('H'.$i)->getCalculatedValue()) ?? null,			
				"return_terminal"			=> trim($sheet->getCell('I'.$i)->getCalculatedValue()) ?? null,
				"empty_delivered" 			=> trim($sheet->getCell('J'.$i)->getValue()) ?? null,
				"placa" 					=> trim($sheet->getCell('K'.$i)->getValue()) ?? null,
				"updated"					=> $updated,
			];
			
			//echo '<pre>'; print_r($row);
			// Convert Dates
			$row["arrival_date"] = $this->date_convert_dd_mm_yyyy($row["arrival_date"]);
			$row["arrival_time"] = $this->convert_hour($row["arrival_time"]);
			
			if ($row["arrival_date"] !== null && $row["placa"] !== null && $row["received_container"] !== null && $row["order_no"] !== null){
				$row["tracking_key"] = $row["_3pl"] . "_" . $row["arrival_date"] . "_" . $row["placa"] . "_" . $row["received_container"] . "_" . $row["order_no"];
			} else continue;
			
			if (in_array($row["tracking_key"], $track)) $rows_req[] = $row;
			elseif (!in_array($row["tracking_key"], $track)) $rows_og[] = $row; 
		}
		
		$rows_split_eq = array_chunk($rows_req, 50);
		foreach($rows_split_eq as $items) $this->gen_m->update_multi("scm_container_reception", $items, 'tracking_key');
		
		$rows_split = array_chunk($rows_og, 50);
		foreach($rows_split as $items) $this->gen_m->insert_m('scm_container_reception', $items);
		
		return "Stock update has been finished. (".$updated.")";
	}
	
	public function upload_container(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'scm_container_reception.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_container();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
