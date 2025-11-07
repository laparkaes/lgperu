<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;

class Ar_cash_back extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$dates = $this->get_period();
		$data = [
			"dates"		=> $dates,
			"stocks"	=> $this->gen_m->filter("ar_bank_code", false, null, null, null, "", 100),
			"main" 		=> "data_upload/ar_cash_back/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function get_period(){
		$current_date = date('Y-m');
		$current_month = (int)date('m');
		$period = [];
		for ($i=$current_month; $i>0; $i--){
			if ($i < 10) $period[] = date('Y') . "-" . '0' . $i;
			else $period[] = date('Y') . "-" . $i; 
		}
		
		if ($current_month == 1){
			$period[] = ((int)date('Y')-1) . '-12';
		}
		return $period;
	}
	
	public function get_unique_values($tablename, $column_name) {
		$current_year = date('Y');
		$previous_year = $current_year - 1;
		
		$start_period = $previous_year . '-10';
		$end_period = $current_year . '-12';

		$this->db->distinct()->select($column_name);

		$this->db->where($column_name . ' IS NOT NULL', NULL, FALSE);

		$this->db->where("$column_name BETWEEN '$start_period' AND '$end_period'");

		$this->db->order_by($column_name, 'DESC');
	
		return $this->db->get($tablename)->result_array();
	}

	public function date_convert_mm_dd_yyyy($date) {
		if (is_numeric($date)) {
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		$aux = explode("/", $date);
		if (count($aux) == 3) {
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}
		
		return null;
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
	
	public function convert_date($excel_date) {
		if (is_numeric($excel_date)) {
			$unix_date = ($excel_date - 25569) * 86400;
			$date_object = new DateTime("@$unix_date");
			$date_object->setTimezone(new DateTimeZone('UTC'));
			return $date_object->format('Y-m-d');
		}
		
		$date_formats = ['d-M-y', 'd-M-Y', 'm/d/Y', 'Y-m-d'];
		foreach ($date_formats as $format) {
			$date_object = DateTime::createFromFormat($format, $excel_date);
			if ($date_object) {
				return $date_object->format('Y-m-d');
			}
		}
		return null;
	}

	public function cleanCode($code) {
		// Definir los prefijos y sufijos a eliminar
		$code = trim($code);
		$prefixes = ['O_']; // Prefijos a eliminar al inicio
		$suffixes = ['_CM', '_PEN', '_PEN_CM', '_1_CM', '_IGVND', '-R', '-I', '_Reversa1', '_FSE', '_IRNODOM'];
		
		if (is_array($code)) {
			// Si $code es un array, convertirlo en una cadena
			$code = implode(',', $code);
		}

		// Eliminar prefijos si existen
		foreach ($prefixes as $prefix) {
			if (strpos($code, $prefix) === 0) {
				$code = substr($code, strlen($prefix));
				break; // Salir después de encontrar y eliminar un prefijo
			}
		}
	
		
		 // Eliminar sufijos si existen
		foreach ($suffixes as $suffix) {
			if (substr($code, -strlen($suffix)) === $suffix) {
				$code = substr($code, 0, -strlen($suffix));
				break; // Salir después de encontrar y eliminar un sufijo
			} 
		}
		
		 // Retornar el código limpio
		return $code;
	}
	
	public function print_zero(&$row){	
		$row["type_voucher"] = '00';       // Primera columna nueva
		$row["serie_voucher"] = '0000';     // Segunda columna nueva
		$row["number_voucher"] = '00000000';
	}
	
	private function performCalculations($row) {

			$originalCode = $row["invoice_number"];
			
			// Limpiar el código con la función personalizada

			$cleanedCode = $this->cleanCode($row["invoice_number"]);

			// Asegurarse de que $row sea un array
			$cleanedCode = rtrim($cleanedCode);
			
			// Dividir el código limpio en tres partes usando explode
			$parts = $cleanedCode ? preg_split('/[_-]/', (string)$cleanedCode)  : [];

			if (empty($cleanedCode)) {
				$this->print_zero($row);
			}
			
			// Si el dato inicio por IBT|PP|EV
			elseif (is_string($cleanedCode) && preg_match('/^(IBT|PP|EV)/', $cleanedCode)) {
				$this->print_zero($row);
			}
			elseif($parts[0] === '20002'){
				$this->print_zero($row);
			}
			elseif($parts[0] === '30042024'){
				$row["type_voucher"] = '00';
				$row["serie_voucher"] = '0000';
				$row["number_voucher"] = $parts[0];
			}
			// Si el primer grupo extraído es "00"
			elseif (!empty($parts) && $parts[0] === '00') {
				if(count($parts) == 2 && $parts[1] !== 'BENEFICIOTRAINNING'){
					// if(strlen($parts[1]) == 19){
					if(strpos($parts[1], '/') !== false) {
						$part1_explode = explode('/', $parts[1]);
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $part1_explode[0];
					}else{	
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $parts[1];
					}
				}
				elseif(count($parts) == 4 || count($parts) == 6 || count($parts) == 7){
					$flag = 0;
					foreach ($parts as $part) {
						if (strpos($part, '/') !== false) {
							// Si el grupo contiene "/", imprimir el grupo
							$flag = 1;
							break;
						}
					}
					
					if($flag == 1){
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $parts[1];
					}
					elseif($parts[1] !== '580' && $flag != 1 && $parts[1] !== 'PR2019010013'){
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = implode('', array_slice($parts, 1));
					}	
					elseif($parts[1] === '580' && $flag != 1){
						$this->print_zero($row);
					}
					elseif($parts[1] === 'PR2019010013' && $flag != 1){
						$this->print_zero($row);
					}
					elseif(count($parts) == 4 && strpos($parts[2], '/') !== false) {
						//$part1_explode = explode('/', $parts[1]);
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $parts[1];
					}
				}
				elseif($parts[1] === 'CRTR002'){
					$row["type_voucher"] = '00';
					$row["serie_voucher"] = '0000';
					$row["number_voucher"] = $parts[2];
				}
				elseif($parts[1] === 'DSCTOEXCEL'){
					$this->print_zero($row);
				}
				
				elseif($parts[1] === 'PR2019010013'){
					$this->print_zero($row);
				}
				elseif (count($parts) == 5){
					if($parts[1] === '580'){
						$this->print_zero($row);
					}
					else{
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = implode('', array_slice($parts, 1));
					}						
				}
				elseif ($parts[1] === 'B001'){
					$row["type_voucher"] = '03';
					$row["serie_voucher"] = $parts[1];
					$row["number_voucher"] = $parts[2];
				}
				elseif(count($parts) == 3){
					if(strpos($parts[1], '/') !== false) {
						$part1_explode = explode('/', $parts[1]);
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $part1_explode[0];
					}
					elseif ($parts[1] === 'RM213'){
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $parts[1].$parts[2];
					}
					elseif (strpos($parts[1], 'F') === 0 || strpos($parts[1], 'E') === 0){
						if(strlen($parts[1]) < 4){
							$firstChar = $parts[1][0];  // Obtiene el primer carácter
							$rest = substr($parts[1], 1);  // Obtiene el resto de la cadena sin el primer carácter
							$parts[1] = $firstChar . '0' . $rest;  // Une el primer carácter, el '0' y el resto
							$row["type_voucher"] = '01';
							$row["serie_voucher"] = $parts[1];
							$row["number_voucher"] = $parts[2];
						}
						else{
							$row["type_voucher"] = '01';
							$row["serie_voucher"] = $parts[1];
							$row["number_voucher"] = $parts[2];
						}
					}
					else{
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = $parts[1];
						$row["number_voucher"] = $parts[2];
					}
				}
				elseif(strpos($parts[1], 'PMV') === 0 || strpos($parts[1], '4') === 0){
					$row["type_voucher"] = '00';
					$row["serie_voucher"] = '0000';
					$row["number_voucher"] = implode('', array_slice($parts, 1));
				}
				else{
					$this->print_zero($row);
					//continue;
				}
			}
			elseif(strpos($cleanedCode, 'F') === 0){
				if (count($parts)>1){
			//elseif($parts[0] === 'F101' || $parts[0] === 'F011'){
					$row["type_voucher"] = '01';
					$row["serie_voucher"] = $parts[0];
					$row["number_voucher"] = $parts[1];
				}else{
					$row["type_voucher"] = '01';
					$row["serie_voucher"] = $parts[0];
					$row["number_voucher"] = '00000000';
				}

			}
			elseif ($parts[0] === '41975545' || $parts[0] === '304' || $parts[0] === '70502032' || $parts[0] === '42457549'){
				$row["type_voucher"] = '00';
				$row["serie_voucher"] = '0000';
				$row["number_voucher"] = implode('', array_slice($parts, 0));
			}				
		
			//Si el valor original comienza por HQ, SPG, KR, CORE, CS
			elseif (preg_match('/^(HQ|SPG|KR|CORE|CS|SVC|EMI|CN)/', $cleanedCode)) {
				$row["type_voucher"] = '91';         // Primera columna nueva
				$row["serie_voucher"] = '0000';       // Segunda columna nueva
				$row["number_voucher"] = strval($cleanedCode); // Tercera columna nueva (valor original tal cual)
				//continue;
			}
			
			// Si el valor original comienza por 5000011
			elseif (strpos($cleanedCode, '50000') === 0) {
				$row["type_voucher"] = '91';         // Primera columna nueva
				$row["serie_voucher"] = '0000';       // Segunda columna nueva
				$row["number_voucher"] = strval($cleanedCode); // Tercera columna nueva (valor original tal cual)
				//continue;
			}

			// Si el código comienza con "Bxxxx"
			elseif (preg_match('/^B\w+/', $cleanedCode, $matches)) {
				if(count($parts)==1){
					$this->print_zero($row);
				}
				else{
					$firstGroup = '03';                         // Primer grupo fijo
					$secondGroup = $matches[0];                 // "Bxxxx"
					$thirdGroup = strval($parts[1] ?? '');  // El que sigue  BXXX
					// Escribir los valores en nuevas columnas
					$row["type_voucher"] = $firstGroup;
					$row["serie_voucher"] = $secondGroup;
					$row["number_voucher"] = $thirdGroup;
					//continue;
				}
			} 

			//Si el primer grupo es "53"
			elseif ($parts[0] === strval('53')) {
					$parts[0] = $this->type_verify($parts[0]);
					$row["type_voucher"] = $parts[0];    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = strval($parts[4] ?? '');    // Tercera columna nueva
					//continue;
			}
			//Si el primer grupo es "50"
			elseif($parts[0] === strval('50')){
				if($parts[3]==='10'){
					$parts[0] = $this->type_verify($parts[0]);
					$row["type_voucher"] = $parts[0];    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = strval($parts[4] ?? '');    // Tercera columna nueva
				}
				elseif($parts[3]==='28'){
					$parts[0] = $this->type_verify($parts[0]);
					$row["type_voucher"] = '53';    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = strval($parts[4] ?? '');    // Tercera columna nueva
				}
				else{
					$parts[0] = $this->type_verify($parts[0]);
					$row["type_voucher"] = '50';    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = strval($parts[4] ?? '');    // Tercera columna nueva
				}
			}				
			elseif ($parts[0] === "91"){  // Primer grupo 91
					if (isset($parts[1]) && count($parts) == 2) {
							$row["type_voucher"] = $parts[0];    // Primera columna nueva
							$row["serie_voucher"] = '0000';       // Segunda columna nueva
							$row["number_voucher"] = strval($parts[1]);    // Tercera columna nueva
							//continue;
					} 
					elseif(count($parts) > 2 && count($parts) != 3) {
						if(strpos($parts[1], "T") === 0 || strpos($parts[1], "P") === 0){
							$row["type_voucher"] = '91';                          // Primera columna nueva
							$row["serie_voucher"] = '0000';                        // Segunda columna nueva
							$row["number_voucher"] = $parts[1]; // Concatenar los demás grupos como tercer valor
						}
						else{
							$row["type_voucher"] = '91';                          // Primera columna nueva
							$row["serie_voucher"] = '0000';                        // Segunda columna nueva
							$row["number_voucher"] = implode('', array_slice($parts, 1)); // Concatenar los demás grupos como tercer valor
						} 
					}
					elseif(count($parts) == 3){
						if(strpos($parts[1], "T") === 0 || strpos($parts[1], "P") === 0){
							$row["type_voucher"] = '91';                          // Primera columna nueva
							$row["serie_voucher"] = '0000';                        // Segunda columna nueva
							$row["number_voucher"] = $parts[1]; // Concatenar los demás grupos como tercer valor
						}
						elseif(strpos($parts[2], "L") === 0){
							$row["type_voucher"] = '91';                          // Primera columna nueva
							$row["serie_voucher"] = '0000';                        // Segunda columna nueva
							$row["number_voucher"] = $parts[1].$parts[2]; // Concatenar los demás grupos como tercer valor
						}
						else{
							$row["type_voucher"] = '91';                          // Primera columna nueva
							$row["serie_voucher"] = '0000';                        // Segunda columna nueva
							$row["number_voucher"] = $parts[2]; // Concatenar los demás grupos como tercer valor
						}
					}
			}
			elseif(strpos($parts[0], '00F001') === 0){
				$row["type_voucher"] = '01';    // Primera columna nueva
				$row["serie_voucher"] = $parts[0];       // Segunda columna nueva
				$row["number_voucher"] = $parts[1];    // Tercera columna nueva
			}
			elseif(strpos($parts[0], 'DT') === 0){
				if(count($parts) == 4){
					$row["type_voucher"] = $parts[1];    // Primera columna nueva
					$row["serie_voucher"] = $parts[2];       // Segunda columna nueva
					$row["number_voucher"] = $parts[3];    // Tercera columna nueva
				}
				else{
					$row["type_voucher"] ='01';    // Primera columna nueva
					$row["serie_voucher"] = $parts[2];       // Segunda columna nueva
					$row["number_voucher"] = $parts[3];    // Tercera columna nueva
				}
			}
			elseif($parts[0] === "01"){  //Primero grupo 01 y en grupo de 2
				if(count($parts) == 2){
					$row["type_voucher"] = '00';    // Primera columna nueva
					$row["serie_voucher"] = '0000';       // Segunda columna nueva
					$row["number_voucher"] = $parts[1];    // Tercera columna nueva
					//continue;
				}
				elseif(count($parts) == 5){
					$row["type_voucher"] = '00';    // Primera columna nueva
					$row["serie_voucher"] = '0000';       // Segunda columna nueva
					$row["number_voucher"] = implode('', array_slice($parts, 1));    // Tercera columna nueva
				}
				elseif(count($parts) ==  4 && $parts[3] === 'TAX'){ //Si termina en TAX
					$row["type_voucher"] = '01';    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = $parts[2];    // Tercera columna nueva
				}
				else{
					 // $this->save_var($parts);
					if (strlen($parts[1]) > 4 && $parts[1][1] === '0') {
						$parts[1] = $parts[1][0].$parts[1][1].$parts[1][3].$parts[1][4];
						$row["type_voucher"] = $parts[0] ?? ''; // Nueva columna 1
						$row["serie_voucher"] =  $parts[1] ?? ''; // Nueva columna 2
						$row["number_voucher"] = $parts[2] ?? ''; // Nueva columna 3		 
					}
					else{
						$row["type_voucher"] = $parts[0] ?? ''; // Nueva columna 1
						$row["serie_voucher"] =$parts[1] ?? ''; // Nueva columna 2
						$row["number_voucher"] = $parts[2] ?? ''; // Nueva columna 3
					}
				}
			}
			elseif (strpos($parts[0], '001')===0){
				if(count($parts)==1){
					$row["type_voucher"] = '91';    // Primera columna nueva
					$row["serie_voucher"] = '0000';       // Segunda columna nueva
					$row["number_voucher"] = $parts[0];
				}
				elseif(count($parts) == 2){
					$row["type_voucher"] = '91';    // Primera columna nueva
					$row["serie_voucher"] = '0000';       // Segunda columna nueva
					$row["number_voucher"] = $parts[0].$parts[1];
				}
				else{
					$row["type_voucher"] = '91';    // Primera columna nueva
					$row["serie_voucher"] = '0000';       // Segunda columna nueva
					$row["number_voucher"] = $parts[0].$parts[1].$parts[2];
				}
				//print_r($parts); echo '<br>'; echo '<br>'; echo '<br>';
			}
			elseif($parts[0]==='03'){
				$row["type_voucher"] = $parts[0];    // Primera columna nueva
				$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
				$row["number_voucher"] = $parts[2];
			}
			elseif($parts[0]==='03' || $parts[0]==='05' || $parts[0]==='07' || $parts[0]==='08' || $parts[0]==='42' || $parts[0]==='30' || $parts[0]==='14'|| $parts[0]==='10'){
				if($parts[0]==='05'){
					if(strpos($parts[1], '/') !== false) {
						$part1_explode = explode('/', $parts[1]);
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $part1_explode[0];
					}
					else{
						$row["type_voucher"] = $parts[0];    // Primera columna nueva
						$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
						$row["number_voucher"] = $parts[2];
					}
				}
				else{
					$row["type_voucher"] = $parts[0];    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = $parts[2];
				}
			}

			else{
				$this->print_zero($row);
				// $parts[0] = $this->type_verify($parts[0]);
				// $row["type_voucher"] = $parts[0] ?? ''; // Nueva columna 1
				// $row["serie_voucher"] = $parts[1] ?? ''; // Nueva columna 2
				// $row["number_voucher"] = $parts[2] ?? ''; // Nueva columna 3
			}
		if (in_array($row["type_voucher"], ["01", "07", "08", "03"]) && strlen($row["number_voucher"]) > 8) {
			$row["number_voucher"] = substr($row["number_voucher"], -8);
		}
		if (strpos($row["serie_voucher"], "S") === 0) {
			$row["type_voucher"] = "14";
		}
		return $row;
	}
		
	public function update_daily_book($data, $period, $data_group) {
		$now = date('Y-m-d H:i:s');
		$je_ids = [];
		$list_update = [];
		$list_update_group = [];
		$list_origin = [];
		
		// Filter to get daily book data
		$s = ['period_name', 'effective_date', 'invoice_number', 'net_entered_debit', 'accounting_unit', 'transaction_number', 'je_id', 'type_voucher', 'serie_voucher', 'number_voucher', 'currency', 'account', 'vendor_customer', 'account_name'];
		$w = ['period_name' => $period];
		$w_in = [
			[
				"field" => "accounting_unit NOT",
				"values" => ["EPG", "INT"]
			]
		];
		$daily_book = $this->gen_m->filter_select('tax_daily_book', false, $s, $w, null, $w_in); 
		foreach ($daily_book as $item_d) {
			$key = $item_d->effective_date . '_' . $item_d->net_entered_debit . '_' . $item_d->currency;
			if (isset($data[$key]) && ($item_d->invoice_number === null || $item_d->invoice_number === '')) {
				if(!empty($item_d->transaction_number) || $item_d->transaction_number !== '') {
					foreach($data[$key] as $item_key) {
						if ($item_key['transaction_number'] === $item_d->transaction_number) {
							$list_update[] = ['type_voucher' => $item_key['type_voucher'], 'serie_voucher' => $item_key['serie_voucher'], 'number_voucher' => $item_key['number_voucher'],'je_id' => $item_d->je_id, 'cash_back_updated' => $now];				
						} else continue;
					}
				}
				elseif (empty($item_d->transaction_number) || $item_d->transaction_number === ''){
					foreach($data[$key] as $item_key) {
						if ($item_key['transaction_number'] === '' || empty($item_key['transaction_number'])) {
							$list_update[] = ['type_voucher' => $item_key['type_voucher'], 'serie_voucher' => $item_key['serie_voucher'], 'number_voucher' => $item_key['number_voucher'],'je_id' => $item_d->je_id, 'cash_back_updated' => $now];
						} else continue;
					}
				}
			}

			 // Group batch numbers values
			if ($item_d->account_name === 'Foreign Currency Deposit_Ordinary' || $item_d->account_name === 'Deposit_Ordinary'){
				$key_group = $item_d->currency . "_" . $item_d->effective_date . "_" . $item_d->net_entered_debit;
				if (isset($data_group[$key_group])){
					foreach ($data_group[$key_group] as &$item) {
						if ($item['net_entered_debit'] == $item_d->net_entered_debit && $item['apply_date'] === $item_d->effective_date){
							if ($this->similar_customer($item_d->vendor_customer,  $item['customer'], 60)){ // Find similar customer name
								$list_update_group[] = ['type_voucher' => 'VARIOS', 'serie_voucher' => 'VARIOS', 'number_voucher' => 'VARIOS', 'je_id' => $item_d->je_id, 'cash_back_updated' => $now];
							}
						} else continue;
					}
				}
			}
			
			if (count($list_update) > 500){
				$this->gen_m->update_multi('tax_daily_book', $list_update, 'je_id');
				$list_update = [];
			}
			if (count($list_update_group) > 500){
				$this->gen_m->update_multi('tax_daily_book', $list_update_group, 'je_id');
				$list_update_group = [];
			}
		}
		
		if (!empty($list_update)) {
			$this->gen_m->update_multi('tax_daily_book', $list_update, 'je_id');
			$count = count($list_update) ?? 0;
		}
		if (!empty($list_update_group)) {
			$this->gen_m->update_multi('tax_daily_book', $list_update_group, 'je_id');
			$count_group = count($list_update_group) ?? 0;
		}
		if (empty($list_update_group) && empty($list_update)){
			return 0;
		}
		return $count + $count_group;
	}
	
	public function process_uploaded_file() {
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		$file_path = './upload/ar_cash_back.xlsx';
		$period = $this->input->post('date_period');

		try {
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
		} catch (\Exception $e) {
			return "Error: El archivo subido no es un formato de hoja de cálculo válido o está corrupto. Intenta con un archivo .xlsx, .xls o .csv válido.";
		}
		
		$sheet = $spreadsheet->getActiveSheet();

		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue())
		];
		//echo '<pre>'; print_r($h); return;
		$header = ["Customer Code", "Customer Name", "Cheque/Note No", "Class", "Invoice No", "Reference No"];
		$is_ok = true;
		foreach ($h as $i => $h_i) {
			if ($h_i !== $header[$i]) {
				$is_ok = false;
				break;
			}
		}

		if (!$is_ok) {
			return "Error: El formato del archivo o las cabeceras no son correctas.";
		} else {
			$msg = $this->process_mapping($sheet, $start_time, $period);
			return $msg;
		}
	}

	public function process_mapping($sheet, $start_time, $period) {
		$max_row = $sheet->getHighestRow();
		
		$batch_size = 1000;
		$data = [];
		$data_batch = [];
		$date_batch = [];
		$data_group = [];
		$count_group = [];

		$now = date('Y-m-d H:i:s'); // Current Date and hour
		
		for($i = 2; $i <= $max_row; $i++){
			$row = [
				'customer_code' 		=> trim($sheet->getCell('A'.$i)->getValue()),
				'customer_name' 		=> trim($sheet->getCell('B'.$i)->getValue()),
				'note_no' 				=> trim($sheet->getCell('C'.$i)->getValue()),
				'class' 				=> trim($sheet->getCell('D'.$i)->getValue()),
				'invoice_number' 		=> trim($sheet->getCell('E'.$i)->getValue()),
				'reference_no' 			=> trim($sheet->getCell('F'.$i)->getValue()),
				'reason_code' 			=> trim($sheet->getCell('G'.$i)->getValue()),
				'allowance_code' 		=> trim($sheet->getCell('H'.$i)->getValue()),
				'discount_amount' 		=> trim($sheet->getCell('I'.$i)->getValue()),
				'acc_used'				=> trim($sheet->getCell('J'.$i)->getValue()),
				'chargback_created' 	=> trim($sheet->getCell('K'.$i)->getValue()),
				'acc_created' 			=> trim($sheet->getCell('L'.$i)->getValue()),
				'currency' 				=> trim($sheet->getCell('M'.$i)->getValue()),
				'allowance_amount'		=> trim($sheet->getCell('N'.$i)->getValue()),
				'applied_amount' 		=> trim($sheet->getCell('O'.$i)->getValue()),
				'apply_by' 				=> trim($sheet->getCell('P'.$i)->getValue()),
				'collector'				=> trim($sheet->getCell('Q'.$i)->getValue()),
				'trx_number' 			=> trim($sheet->getCell('R'.$i)->getValue()),
				'trx_date' 				=> trim($sheet->getCell('S'.$i)->getValue()),
				'due_date' 				=> trim($sheet->getCell('T'.$i)->getValue()),
				'clearing_dept'			=> trim($sheet->getCell('U'.$i)->getValue()),
				'comments'				=> trim($sheet->getCell('V'.$i)->getValue()),
				'apply_date'			=> trim($sheet->getCell('W'.$i)->getValue()),
				'batch_no'				=> trim($sheet->getCell('X'.$i)->getValue()),
				'updated' 				=> $now,
			];
			
			
			//if (empty($row['invoice_number'])) continue;
			if (empty($row['apply_date'])) continue;
			if ($row['applied_amount'] == 0 && $row['chargback_created'] == 0) continue;
			$row['apply_date'] = $this->convert_date($row['apply_date']);
			
			if ($period === substr($row['apply_date'], 0, 7)){
				$new_data = $this->performCalculations($row);
				
				if (!empty($new_data['invoice_number']) || $new_data['invoice_number'] !== ''){
					$new_data['applied_amount'] = number_format((float)$new_data['applied_amount'], 2, '.', '');
					$data[$new_data['apply_date'] . '_' . $new_data['applied_amount'] . '_' . $new_data['currency']][] = [
																						'apply_date' 			=> $new_data['apply_date'],
																						'batch_no'				=> $new_data['batch_no'],
																						'currency'				=> $new_data['currency'],
																						'net_entered_debit' 	=> $new_data['applied_amount'],
																						'transaction_number' 	=> $new_data['trx_number'],
																						'invoice_number' 		=> $new_data['invoice_number'],
																						'type_voucher' 			=> $new_data['type_voucher'],
																						'serie_voucher' 		=> $new_data['serie_voucher'],
																						'number_voucher' 		=> $new_data['number_voucher']];
				}
				
				// Group by Batch no from Batch Inquiry				
				if (!isset($data_batch[$new_data['batch_no'] . "_" . $new_data['apply_date']])) {
					$data_batch[$new_data['batch_no'] . "_" . $new_data['apply_date']] = [
															'customer'				=> $new_data['customer_name'],
															'apply_date' 			=> $new_data['apply_date'],
															'batch_no'				=> $new_data['batch_no'],
															'currency'				=> $new_data['currency'],
															'net_entered_debit' 	=> 0 ];
				}
				
				$data_batch[$new_data['batch_no'] . "_" . $new_data['apply_date']]['net_entered_debit'] += $new_data['applied_amount'] + $new_data['chargback_created'];
				
				if(!isset($count_group[$new_data['batch_no'] . "_" . $new_data['apply_date']])) $count_group[$new_data['batch_no'] . "_" . $new_data['apply_date']] = 0;
				$count_group[$new_data['batch_no'] . "_" . $new_data['apply_date']] += 1;
				
			} else continue;
		}
		
		foreach ($data_batch as $item) {
			if ($count_group[$item['batch_no'] . "_" . $item['apply_date']] > 1 && $item['net_entered_debit'] != 0) { // Validate number matched rows greater than 1 row
				$key_batch = $item['apply_date'] . "_" . $item['net_entered_debit'] . "_" . $item['currency']; // key for data
				if (!isset($data[$key_batch])) $data_group[$item['currency'] . "_" . $item['apply_date'] . "_" . $item['net_entered_debit']][] = $item; // To avoid rows includes in $data
				else continue;
			} else continue;
		}

		if (empty($data)) {
			$error_msg = "Error: The file contained no valid rows for the selected period ($period) or was empty.";
			return $error_msg;
		}
		$count = $this->update_daily_book($data, $period, $data_group);
		$msg = $count . " records updated in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		return $msg;
	}
	
	public function clean_customer_txt($text) {
		$text = mb_strtolower($text, 'UTF-8');
		$text = preg_replace('/[0-9]+/', '', $text);
		$text = preg_replace('/\s(s\.?a\.?c\.?|e\.?i\.?r\.?l\.?|s\.?r\.?l\.?|s\.?a\.?)/', '', $text);
		$text = preg_replace('/[^a-zñáéíóú\s]/', '', $text);
		$text = trim(preg_replace('/\s+/', ' ', $text));

		return $text;
	}

	public function similar_customer($daily_customer, $batch_customer, $threshold = 70) {
		$daily_customer_clean = $this->clean_customer_txt($daily_customer);
		$batch_customer_clean = $this->clean_customer_txt($batch_customer);

		if (empty($daily_customer_clean) || empty($batch_customer_clean)) {
			return false;
		}

		$similitud_chars = similar_text($daily_customer_clean, $batch_customer_clean, $porcentage);

		if ($porcentage >= $threshold) {
			return true;
		} else {
			return false;
		}
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
				'file_name'		=> 'ar_cash_back.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_uploaded_file();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
