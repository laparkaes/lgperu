<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Tax_daily_book extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){		
		$data = [
			"tax"	=> $this->gen_m->filter("tax_daily_book", false, null, null, null, null, 1000),
			"main" 		=> "module/tax_daily_book/index",
		];
		
		$this->load->view('layout', $data);
	}
	// Conversion fechas
	public function date_convert_9($original_date){
		if (!empty($original_date)) {
        // Verificar si el valor es numérico
			if (is_numeric($original_date)) {
				// Convertir el número de serie de Excel a una fecha en formato Y-m-d
				$fecha_convertida = Date::excelToDateTimeObject($original_date)->format('Y-m-d');
			} else {
				if (preg_match('/^\d{2}-[A-Z]{3}-\d{2}$/', $original_date)) {
                // Caso 28-OCT-21
                $fecha_convertida = $this->my_func->date_convert_4($original_date);
				}elseif (preg_match('/^\d{2}-[A-Z]{3}-\d{4}$/', $original_date)) {
					// Caso 28-OCT-2021 -> Convertir manualmente
					$fecha_convertida = $this->my_func->date_convert_5($original_date);
				}else {
					// Si no coincide con ninguno, mantener el valor original o manejar error
					$fecha_convertida = $original_date;
				} 
			}
			$original_date = $fecha_convertida;
		}
		return $original_date;
	}	
	// Limpiar los datos
	public function cleanCode($code) {
		// Definir los prefijos y sufijos a eliminar
		$code = trim($code);
		$prefixes = ['O_']; // Prefijos a eliminar al inicio
		$suffixes = ['_CM', '_PEN', '_PEN_CM', '_1_CM', '_IGVND', '-R', '-I', '_Reversa1', '_FSE', '_IRNODOM']; // Sufijos a eliminar al final
		
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
	
	public function type_verify($part1){
		//$code = '001';
		$part1 = ltrim($part1, '0'); // Elimina ceros iniciales
		if (strlen($part1) === 1) {
			$part1 = '0' . $part1; // Añade un cero si es un solo dígito
		}
		return $part1;
	}
	
	public function save_var($parts){
		$row["type_voucher"] = $parts[0] ?? ''; // Nueva columna 1
		$row["serie_voucher"] = $parts[1] ?? ''; // Nueva columna 2
		$row["number_voucher"] = $parts[2] ?? ''; // Nueva columna 3
	}
	// Impresion de ceros
	public function print_zero(&$row){	
		$row["type_voucher"] = '00';       // Primera columna nueva
		$row["serie_voucher"] = '0000';     // Segunda columna nueva
		$row["number_voucher"] = '00000000';
	}
	
	public function test(){
		//load excel file
		$spreadsheet = IOFactory::load("./upload/tax_daily_book.xlsx");
		//$spreadsheet = IOFactory::createReader('Xlsx')->setReadDataOnly(true)->load("./upload/tax_daily_book.xlsx");
		$sheet = $spreadsheet->getActiveSheet();		
	}
	
	// Realizar cálculos sobre los datos del Excel
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
	// Asiganar valor de cada celda a row
	public function row_values($values, $updated){
		$row = [
						"legal_entity" 					=> $values[0],
						"period_name" 					=> $values[1],
						"effective_date" 				=> $values[2],
						"posted_date" 					=> $values[3],
						"accounting_unit" 				=> $values[4],
						"department"					=> $values[5],
						"department_name"				=> $values[6],
						"account" 						=> $values[7],
						"account_name" 					=> $values[8],
						"project" 						=> $values[9],
						"affiliate"						=> $values[10],
						"temporary1"					=> $values[11],
						"temporary2"					=> $values[12],
						"currency"						=> $values[13],
						"net_entered_debit"				=> $values[14],
						"entered_debit"					=> $values[15],
						"entered_credit"				=> $values[16],	
						"net_accounted_debit"			=> $values[17],	
						"accounted_debit"				=> $values[18],	
						"accounted_credit"				=> $values[19],	
						"description_ar_comments"		=> $values[20],	
						"journal_source"				=> $values[21],	
						"journal_category"				=> $values[22],	
						"gl_batch_name"					=> $values[23],	
						"gl_journal_name"				=> $values[24],	
						"gl_document_seq_number"		=> $values[25],	
						"ap_ar_source"					=> $values[26],	
						"line_type"						=> $values[27],
						"ap_ar_batch_name"				=> $values[28],
						"invoice_number"				=> $values[29],
						"transaction_number"			=> $values[30],
						"transaction_date"				=> $values[31],
						"check_number"					=> $values[32],
						"receipt_number"				=> $values[33],
						"vendor_customer"				=> $values[34],
						"bank_name"						=> $values[35],
						"bank_account_number"			=> $values[36],
						"business_number"				=> $values[37],
						"tax_payer_id"					=> $values[38],
						"subledger_document_seq_number"	=> $values[39],
						"tax_date"						=> $values[40],
						"tax_code"						=> $values[41],
						"tax_rate"						=> $values[42],
						"created_by"					=> $values[43],
						"create_user_name"				=> $values[44],
						"dff_context"					=> $values[45],
						"dff1"							=> $values[46],
						"dff2"							=> $values[47],
						"dff3"							=> $values[48],
						"dff4"							=> $values[49],
						"dff5"							=> $values[50],
						"dff6"							=> $values[51],
						"dff7"							=> $values[52],
						"dff8"							=> $values[53],
						"dff9"							=> $values[54],
						"dff10"							=> $values[55],
						"dff11"							=> $values[56],
						"dff12"							=> $values[57],
						"dff13"							=> $values[58],
						"dff14"							=> $values[59],
						"dff15"							=> $values[60],
						"dff16"							=> $values[61],
						"dff17"							=> $values[62],
						"dff18"							=> $values[63],
						"dff19"							=> $values[64],
						"dff20"							=> $values[65],
						"lease_no"						=> $values[66],
						"asset_number"					=> $values[67],
						"org_id"						=> $values[68],
						"link_id"						=> $values[69],
						"je_header_id"					=> $values[70],
						"je_line_number"				=> $values[71],
						"je_id"							=> $values[70]. '_' .$values[71],
						"type_voucher"					=> "",
						"serie_voucher"					=> "",
						"number_voucher"				=> "",
						"updated"						=> $updated,
					];
		return $row;
	}
	// Procesar los valores de las celdas
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/tax_daily_book.xlsx");
		//$spreadsheet = IOFactory::createReader('Xlsx')->setReadDataOnly(true)->load("./upload/tax_daily_book.xlsx");
		$sheet = $spreadsheet->getActiveSheet();
				
		$header = [
			"Legal Entity", "Period Name", "Effective Date", "Posted Date", "Accounting Unit",
			"Department", "Department Name", "Account", "Account Name", "Project", "Affiliate",
			"Temporary1", "Temporary2", "Currency", "Net Entered Debit", "Entered Debit", "Entered Credit",
			"Net Accounted Debit", "Accounted Debit", "Accounted Credit", "Description/AR Comments",
			"Journal Source", "Journal Category", "GL Batch Name", "GL Journal Name", "GL Document Seq Number", 
			"AP/AR Source", "Line Type", "AP/AR Batch Name", "Invoice Number", "Transaction Number",
			"Transaction Date", "Check Number", "Receipt Number", "Vendor/Customer", "Bank Name",
			"Bank Account Number", "Business Number", "Tax Payer ID", "Subledger Document Seq Number",
			"Tax Date", "Tax Code", "Tax Rate", "Created By", "Create User Name", "DFF_Context", 
			"DFF1", "DFF2", "DFF3", "DFF4", "DFF5", "DFF6", "DFF7", "DFF8", "DFF9", "DFF10",
			"DFF11", "DFF12", "DFF13", "DFF14", "DFF15", "DFF16", "DFF17", "DFF18", "DFF19",
			"DFF20", "Lease No.", "Asset Number", "ORG_ID", "LINK_ID", "JE_HEADER_ID", "JE_LINE_NUMBER",
		];

		//header validation
		$is_ok = true;
		// foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;
		$values = [];
		$temp = [];
		
		//print_r($is_ok); echo("<br>"); return;
		if ($is_ok){
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			$batch_size = 5000; // Tamaño del lote para inserción
			$batch_data = [];
			$batch_insert = [];
			$temp = [];
			//$row = [];
			
			for($i = 5; $i < $max_row; $i++){
				$cellValue = trim($sheet->getCell('A'.$i)->getValue());							
				$values = explode('!', $cellValue);
				
				if (count($values) == 72){
					$batch_data[] = $this->row_values($values, $updated);

				}
				else{
					// Verifica si hay un valor anterior para concatenar
					if (!empty($temp)) {

						$val_sum = count($temp) + count($values);
						
						if ($val_sum >= 73){
							
							array_shift($values);
							$concatenatedValue = array_merge($temp, $values);
							$row = $this->row_values($concatenatedValue, $updated);
							$batch_data[] = $row;
						}
						else{
							$temp = array_pad($temp, 72, NULL);
							$values = array_pad($values, 72, NULL);	
							$row_temp = $this->row_values($temp, $updated);
							$row = $this->row_values($values, $updated);
							
							$batch_data[] = $row_temp;
							$batch_data[] = $row;
							
						}
						//unset($temp);
						$temp = [];
					}else{ $temp = $values;
					}
				}
				if (count($batch_data) >= $batch_size) {

					$this->process_row($batch_data);
					$batch_data = [];
				}

			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				//print_r($batch_data); echo '<br>'; echo '<br>'; echo '<br>';
				$this->process_row($batch_data);

				$batch_data = [];
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
			//print_r($msg); return;
			return $msg;			
		}else return "Error: Header validation failed.";
	}
	// Operaciones para invoice_number
	public function process_row($batch_data){	
		$batch_insert = [];
		
		// Obtener los je_id únicos de los datos entrantes
		$existing_je_ids_result = $this->gen_m->filter_select('tax_daily_book', false, 'je_id', ['period_name' => $batch_data[0]['period_name']]);
		$existing_je_ids = array_column($existing_je_ids_result, 'je_id');
		
		//print_r($existing_je_ids); echo '<br>'; echo '<br>'; return;
		

		
		foreach($batch_data as $index=>$row){
			
			//Validacion je_id
			if (!in_array($row['je_id'], $existing_je_ids)) {
			//if (!($this->gen_m->filter('tax_daily_book', false, ['je_id' => $row['je_id']]))) {
				
				$row_modified = $this->performCalculations($row);
				//je_id NULL
				if ($row_modified["je_header_id"] === NULL && $row_modified["je_line_number"]===NULL){
					$row_modified["je_id"] = NULL;
				}
				
				//Date convert
				$row_modified["effective_date"] = $this->date_convert_9($row_modified["effective_date"]);
				$row_modified["posted_date"] = $this->date_convert_9($row_modified["posted_date"]);
				$row_modified["transaction_date"] = $this->date_convert_9($row_modified["transaction_date"]);
				$row_modified["tax_date"] = $this->date_convert_9($row_modified["tax_date"]);	
				$batch_insert[] = $row_modified;
				//return $batch_insert;
			}
		}
		if (!empty($batch_insert)) {
			$this->gen_m->insert_m("tax_daily_book", $batch_insert);
			//$batch_insert = [];
		}
		
	}
	
	public function number_document($from_date, $to_date) {
		set_time_limit(0);
		ini_set("memory_limit", -1);
		// Función para limpiar números (deja solo dígitos)
		function clean_number($number) {
			return preg_replace('/\D/', '', $number); // Elimina todo excepto dígitos
		}

		// Condición filtrando registros válidos
		$w_daily = "vendor_customer NOT LIKE 'PR%' 
					AND vendor_customer NOT LIKE 'GCC%'
					AND accounting_unit NOT LIKE 'EPG'
					AND accounting_unit NOT LIKE 'INT'
					AND effective_date BETWEEN '$from_date' AND '$to_date'";

		// Obtener vendor_customer y je_id en una sola consulta
		$vendor_data = $this->gen_m->filter_select("tax_daily_book", false, ['vendor_customer', 'je_id'], $w_daily);

		if (empty($vendor_data)) {
			return [];
		}
		$vendor_chars = [];
		// Extraer valores únicos de vendor_customer según la condición
		$vendor_chars = ['numbers' => [], 'letters' => [], 'pe' => []];
		foreach ($vendor_data as $v) {
			$vc = $v->vendor_customer;
			

			if (ctype_digit(substr($vc, 0, 1))) {
				//$vendor_chars['numbers'][] = strtok($vc, ' '); // Extraer hasta el primer espacio
				$cleaned_number =  clean_number(strtok($vc, ' '));  // Elimina todo excepto dígitos
				$vendor_chars['numbers'][] = $cleaned_number; 
				//print_r($cleaned_number); echo '<br>';
			} elseif (stripos($vc, 'PE') === 0) {
				$vendor_chars['pe'][] = substr($vc, 0, 8); // Extraer primeros 8 caracteres
				
			} else {
				$vendor_chars['letters'][] = substr($vc, 0, 8); // Extraer primeros 8 caracteres
			}
		}
		//print_r($vendor_chars['pe']); echo '<br>'; return;
		// Remover duplicados
		$vendor_chars['numbers'] = array_unique($vendor_chars['numbers']);
		$vendor_chars['letters'] = array_unique($vendor_chars['letters']);
		$vendor_chars['pe'] = array_unique($vendor_chars['pe']);

		// Consultar en ar_mdms para valores que comienzan con letra (excepto PE)
		$biz_map = [];
		if (!empty($vendor_chars['letters'])) {
			$w_letters = "master_id IN ('" . implode("','", $vendor_chars['letters']) . "') 
						  OR bp_code IN ('" . implode("','", $vendor_chars['letters']) . "')";
			
			$biz_numbers_letters = $this->gen_m->filter_select("ar_mdms", false, ['master_id', 'bp_code']);
			foreach ($biz_numbers_letters as $biz) {
				$key = $biz->master_id ?: $biz->bp_code;
				$biz_map[$key] = true; // Solo se usa para verificar existencia
			}
		}

		// Consultar en ar_mdms para valores que comienzan con número
		$biz_numbers_map = [];
		if (!empty($vendor_chars['numbers'])) {
			$clean_numbers = array_map('clean_number', $vendor_chars['numbers']); // Limpiar todos los números
			$w_numbers = "biz_registration_no IN ('" . implode("','", $clean_numbers) . "')";
			//$w_numbers = "biz_registration_no IN ('" . implode("','", $vendor_chars['numbers']) . "')";
			$biz_numbers_numbers = $this->gen_m->filter_select("ar_mdms", false, ['biz_registration_no']);
			$biz_numbers_map = array_column($biz_numbers_numbers, 'biz_registration_no', 'biz_registration_no');
		}

		// Consultar en ar_mdms para valores que comienzan con "PE"
		$biz_pe_map = [];
		if (!empty($vendor_chars['pe'])) {
			$w_pe = "master_id IN ('" . implode("','", $vendor_chars['pe']) . "') 
					OR bp_code IN ('" . implode("','", $vendor_chars['pe']) . "')";
						  
			$biz_pe_numbers = $this->gen_m->filter_select("ar_mdms", false, ['master_id', 'bp_code', 'biz_registration_no'], $w_pe);
			//$w_pe = "biz_registration_no IN ('" . implode("','", $vendor_chars['pe']) . "')";
			//$biz_pe_numbers = $this->gen_m->filter_select("ar_mdms", false, ['biz_registration_no']);
			foreach ($biz_pe_numbers as $biz) {
				$key = $biz->master_id ?: $biz->bp_code;
				$biz_map[$key] = !empty($biz->biz_registration_no) ? substr($biz->biz_registration_no, 0, 11) : "Pendiente"; // Solo se usa para verificar existencia
			}
			//$biz_pe_map = array_column($biz_pe_numbers, 'biz_registration_no', 'biz_registration_no');
		}

		// Construir el array final con los datos optimizados
		return array_map(function ($vendor) use ($biz_map, $biz_numbers_map) {
			$vc = $vendor->vendor_customer;

			if (ctype_digit(substr($vc, 0, 1))) {
				$vendor_char = clean_number(strtok($vc, ' ')); // Extraer hasta el primer espacio
				$ci_value = $biz_numbers_map[$vendor_char] ?? "00000000000"; // Buscar en biz_registration_no
			} elseif (stripos($vc, 'PE') === 0) {
				$vendor_char = substr($vc, 0, 8);
				$ci_value = $biz_map[$vendor_char] ?? "00000000000"; // Si PE existe en ar_mdms, usarlo, si no, "00000000"
			} else {
				$vendor_char = substr($vc, 0, 8);
				$ci_value = isset($biz_map[$vendor_char]) ? $vendor_char : "00000000000"; // Si existe en ar_mdms, usar el extraído
			}

			return [$vendor->je_id, $ci_value, $vendor_char];
		}, $vendor_data);
	}
	
	public function correlative_count($je_line_number) {
		$length = strlen($je_line_number);

		if ($length == 1) {
			return "M0000" . $je_line_number;
		} elseif ($length == 2) {
			return "M000" . $je_line_number;
		} elseif ($length == 3) {
			return "M00" . $je_line_number;
		} elseif ($length == 4) {
			return "M0" . $je_line_number;
		} else {
			return "M" . $je_line_number;
		}
	}
	
	public function fill_pcge($row, $pcge_map){
		//$batchSpecialData = [];
		$batchSpecialData_pcge = 0;
		$batchSpecialData_pcge_decripcion = 0;

			if (isset($pcge_map[$row->account])) {
				// Si el número de factura existe en la BD, usar el customer_vat_no
				$batchSpecialData_pcge = $pcge_map[$row->account][0];
				$batchSpecialData_pcge_decripcion = $pcge_map[$row->account][1];
			} 
				
		
		
		return [$batchSpecialData_pcge, $batchSpecialData_pcge_decripcion];		
	}
	
	public function print_ruc($sheet, $row, $row_num, $vendor_char_map, $invoice_map) {
		
		if (isset($row->vendor_customer) && preg_match('/^(PE|GCC|PR)/', $row->vendor_customer)) {
			if (isset($vendor_char_map[$row->je_id])) {
			//if (isset($vendor_char_map[$row->invoice_number])) {
				// Si el número de factura existe en la BD, usar el customer_vat_no
				$batchSpecialData = ["CI" . $row_num, !empty($vendor_char_map[$row->je_id]) ?$vendor_char_map[$row->je_id] : "Pendiente"];
			} 
			else {
				// Si no, usar el valor predeterminado o el del vendor_char_map
				
				$batchSpecialData = ["CI" . $row_num, !empty($invoice_map[$row->invoice_number]) ? $invoice_map[$row->invoice_number] : "Pendiente"];
				// $batchSpecialData = ["CI" . $row_num, (strpos($vendor_char_map[$row->je_id], 'M') === 0)  ? 'Pendiente' : $vendor_char_map[$row->je_id]];
			}
		}
 
		else {
			$batchSpecialData = ["CI" . $row_num, $vendor_char_map[$row->je_id] ?? "00000000000"];
						
		}				
		if($row->type_voucher === '00' && $row->serie_voucher === '0000' && $row->number_voucher === '00000000'){
			$batchSpecialData = ["CI" . $row_num, '00000000000'];
		}
		// Aplicar estilo condicional para la celda "Pendiente"
		if(strpos($batchSpecialData[1], 'M') === 0){
			$batchSpecialData = ["CI" . $row_num, "Pendiente"];
		}
		if ($batchSpecialData[1] === "Pendiente") {
			$sheet->getStyle('CI'.$row_num)->getFill()->setFillType(Fill::FILL_SOLID)
				  ->getStartColor()->setARGB('FFDE59'); // Amarillo
		}
		
		
		return $batchSpecialData;
	}

	
	public function generate_excel() {
		ini_set('memory_limit', -1);
		set_time_limit(0);

		 // **2. Cargar plantilla**
		$loadStart = microtime(true);
		$template_path = './template/tax_daily_book_template.xlsx';
		if (!file_exists($template_path)) {
			echo "Error: No se encontró la plantilla de Excel.";
			return;
		}

		$spreadsheet = IOFactory::load($template_path);
		$sheet = $spreadsheet->getActiveSheet();
		$loadEnd = microtime(true);
		
		// **3. Obtener datos de entrada**
		$dataStart = microtime(true);
		$from_date = $this->input->post('effective_from');
		$to_date = $this->input->post('effective_to');
		if (!$from_date || !$to_date) {
			echo "Error: Fechas no proporcionadas";
			return;
		}

		$where = [
			'effective_date >=' => $from_date,
			'effective_date <=' => $to_date
		];
	
		// $where = 
			// "'effective_date' BETWEEN {$from_date} AND {$to_date}
			// AND 'accounting_unit' NOT LIKE 'EPG'
			// AND 'accounting_unit' NOT LIKE 'INT'"
		// ;
		
		$dataEnd = microtime(true);
		 
		// **4. Extraer información adicional**
		$bizStart = microtime(true);
		
		$numeric_columns = ["net_entered_debit", "entered_debit", "entered_credit", 
							"net_accounted_debit", "accounted_debit", "accounted_credit"];
		$date_columns = ['effective_date', 'posted_date', 'transaction_date', 'tax_date'];
		
		$numericColumns = array_flip($numeric_columns);
		$dateColumns = array_flip($date_columns);
	
		$columnMap = ["type_voucher" => 'CJ', "serie_voucher" => 'CK', "number_voucher" => 'CL'];


		
		
		$vendor_char_map = [];
		foreach ($this->number_document($from_date, $to_date) as $biz) {
			$vendor_char_map[$biz[0]] = $biz[1]; // [je_id] => vendor_char extraído
		}
		
		//Array banks Account SCOTIA, BCP, CITI, INTER, BBVA, NACION
		$banks_account = ['PEN' =>['SCOTIA'=>'000-0099058', 'BCP'=>'193-1705267-0-18', 'CITI'=>'000-2898004', 'INTER'=>'200-3006258334', 'BBVA'=>'0011-0910-0100073657-77', 'NACION'=>'00-005-177405'], 
		'USD'=>['SCOTIA'=>'01-283-103-0288-25', 'BCP'=>'193-1020580-1-98', 'CITI'=>'000-2898136', 'INTER'=>'200-3006258376', 'BBVA'=>'0011-0910-0100060709-71']];
		
		$banks_account_code = ['SCOTIA'=>'09', 'BCP'=>'02', 'CITI'=>'07', 'INTER'=>'03', 'BBVA'=>'02', 'NACION'=>'18'];
		// Obtener los valores de number_document (solo una vez)
		$biz_map = [];

		
		
		$ruc_tax_register = $this->gen_m->filter_select("tax_purchase_register", false, ['invoice_number', 'customer_vat_no']);
		$invoice_map = [];
		foreach ($ruc_tax_register as $record) {
			$invoice_map[$record->invoice_number] = $record->customer_vat_no;
		}
		
		$pcge_data = $this->gen_m->filter_select('lgepr_tax_pcge', false, ['account', 'pcge', 'pcge_decripcion']);
		$pcge_map = [];
		foreach ($pcge_data as $item_pcge) {
			$pcge_map[$item_pcge->account] = [$item_pcge->pcge, $item_pcge->pcge_decripcion];			
		}
		
		$bank_code_data = $this->gen_m->filter_select('ar_bank_code', false, ['bank_name', 'date_operation', 'number_operation', 'total_amount']);
		$bank_code_map = [];

		foreach ($bank_code_data as $item_bank_code) {
			$date = $item_bank_code->date_operation;
			
			// Si la fecha aún no está en el array, inicialízala como un array vacío
			if (!isset($bank_code_map[$date])) {
				$bank_code_map[$date] = [];
			}

			// Agrega los datos al array correspondiente a la fecha
			$bank_code_map[$date][] = [
				'bank_name' => $item_bank_code->bank_name,
				'number_operation' => $item_bank_code->number_operation,
				'total_amount' => $item_bank_code->total_amount
			];
		}
		//print_r($bank_code_map); return;
		$bizEnd = microtime(true);
		//print_r($pcge_map); echo '<br>'; return;
		// **5. Obtener datos en lotes**
		$fetchStart = microtime(true);
		$batchSize = 5000;
		$batchData = [];
		$batchSpecialData = [];
		$row_num = 6;
		$sum_net_accounted_debit_pos = 0;
		$sum_net_accounted_debit_ne = 0;
		$data_start = microtime(true);
		$allData = $this->fetch_large_data('tax_daily_book', $where, $batchSize);
		$data_end = microtime(true);
		foreach ($allData as $row) {
		//foreach ($this->fetch_large_data('tax_daily_book', $where, $batchSize) as $row) {
			if ($row->accounting_unit !== 'EPG' && $row->accounting_unit !== 'INT') {
				$dataRow = [];
				foreach ($row as $key => $value) {
					if (!in_array($key, ['je_id'])) {
						if (isset($numericColumns[$key])) {
							$dataRow[] = (float) $value ?: 0;
						} elseif (isset($dateColumns[$key])) {
							if($value !== '0000-00-00' && $value !== null){
								$dataRow[] = date('d/m/Y', strtotime($value));
								
							}else{
								$dataRow[] = '';
							}
						} elseif (!isset($columnMap[$key])) {
							$dataRow[] = $value;
						}
					}
				}

				$batchData[] = $dataRow;
				
				// Columna B
				$batchSpecialData[] = ["B" . $row_num, $row_num-5 ?? "00000000000"];
				
				// Columna C y D: pcge 2022
				[$batchSpecialData_pcge, $batchSpecialData_pcge_decripcion] = $this -> fill_pcge($row, $pcge_map);
				$batchSpecialData[] = ["C" . $row_num, $batchSpecialData_pcge ?? "Pendiente"];
				$batchSpecialData[] = ["D" . $row_num, $batchSpecialData_pcge_decripcion ?? "Pendiente"];
				//$batchSpecialData[] = ["C" . $row_num, $row-> ?? "00000000"];
				
				
				
				
				// Columna CI RUC $row, $row_num, $vendor_char_map
				$batchSpecialData[] = $this -> print_ruc($sheet, $row, $row_num, $vendor_char_map, $invoice_map);

				
				//$batchSpecialData[] = ["CI" . $row_num, $vendor_char_map[$row->je_id] ?? "00000000"];				
				// Agregar datos de las columnas especiales
				foreach ($columnMap as $key => $col) {
					if (isset($row->$key)) {						
						$batchSpecialData[] = [$col . $row_num, $row->$key];
					}
				}
				
				// Se agrega valores a la columna periodo "CA"
				$batchSpecialData[] = ["CA" . $row_num, str_replace('-', '', $row->period_name) . '00'];

				// Valores para CUO - columna CB
				$formulaData_cb = "=BW$row_num";
				$batchSpecialData[] = ["CB" . $row_num, $formulaData_cb ?? "-"];
				
				// Numero correlativo del asiento contable indentificado "CC"
				
				$formulaData_cc = "=IF(LEN(BX$row_num)=1,CONCATENATE(\"M0000\",BX$row_num),IF(LEN(BX$row_num)=2,CONCATENATE(\"M000\",BX$row_num),IF(LEN(BX$row_num)=3,CONCATENATE(\"M00\",BX$row_num),IF(LEN(BX$row_num)=4,CONCATENATE(\"M0\",BX$row_num),CONCATENATE(\"M\",BX$row_num)))))";
				
				//$correlative_count = $this->correlative_count($row->je_line_number);
				//$batchSpecialData[] = ["CC" . $row_num, $correlative_count ?? "-"];
				$batchSpecialData[] = ["CC" . $row_num, $formulaData_cc ?? "-"];
				
				// Llenado de columna CD: codigo de la cuenta contable
				$formulaData_cd = "=C$row_num";
				$batchSpecialData[] = ["CD" . $row_num, $formulaData_cd ?? "-"];
				
				// Se agrega valores para la columna "CG" tipo de moneda de origen
				$batchSpecialData[] = ["CG" . $row_num, $row->currency ?? "-"];
				
				// Se agrega fecha effective_date a columna CO
				$formulaData_co = "=G$row_num";
				$batchSpecialData[] = ["CO" . $row_num, $formulaData_co ?? "-"];
				
				// Se agrega valores para la columna "CP" basado en los valores de Description/AR Comments
				$formulaData_cp = "=LEFT(IF(+Y$row_num=\"\",D$row_num,Y$row_num),200)";
				$batchSpecialData[] = ["CP" . $row_num, $formulaData_cp ?? ""];
				
				// $batchSpecialData[] = ["CP" . $row_num, !empty($row->description_ar_comments) ? substr($row->description_ar_comments, 0, 200) : substr($batchSpecialData_pcge_decripcion, 0, 200)];
				
				// Movimientos del debe - columna "CR"
				$formulaData_cr = "=IF(V$row_num>=0,V$row_num,0)";
				$batchSpecialData[] = ["CR" . $row_num, $formulaData_cr ?? 0];
				// $batchSpecialData[] = ["CR" . $row_num, ($row->net_accounted_debit > 0) ? (float)$row->net_accounted_debit : 0];
				
				// Movimientos de haber - columna "CS"
				$formulaData_cs = "=IF(V$row_num<=0,-(V$row_num),0)";
				$batchSpecialData[] = ["CS" . $row_num, $formulaData_cs ?? 0];
				// $batchSpecialData[] = ["CS" . $row_num, ($row->net_accounted_debit < 0) ? (float)($row->net_accounted_debit*-1) : 0];	
				
				//print_r($batchSpecialData[5][1]); echo '<br>'; echo '<br>'; echo '<br>';
				//$formulaData_cs_sum_pos = "=SUM(CR$row_num:CR161262)"
				
				//$sum_net_accounted_debit_pos = $sum_net_accounted_debit_pos + (($row->net_accounted_debit > 0) ? $row->net_accounted_debit : 0);
				//$sum_net_accounted_debit_ne = $sum_net_accounted_debit_ne + (($row->net_accounted_debit < 0) ? ($row->net_accounted_debit*-1) : 0); 
				
				// Rellenado de columna CU
				$batchSpecialData[] = ["CU" . $row_num, 1];	
				
				// Columnas CW y CX
				if (strpos($batchSpecialData_pcge, '10') === 0){
					
					if(!empty($row->bank_name)){
						$parts = explode('_', $row->bank_name, 3);
						$bank_name = isset($parts[1]) ? $parts[1] : '';
						if($row->currency ==='PEN'){
							// Rellenado columna CX código de la cuenta bancaria del contribuyente
							$batchSpecialData[] = ["CX" . $row_num, $banks_account['PEN'][$bank_name] ?? ""]; 
							// Rellenado de columna CW código de la entidad financiera
							$batchSpecialData[] = ["CW" . $row_num, $banks_account_code[$bank_name] ?? ""];
						}
						elseif($row->currency==='USD'){
							// Rellenado columna CX código de la cuenta bancaria del contribuyente
							$batchSpecialData[] = ["CX" . $row_num, $banks_account['USD'][$bank_name] ?? ""];
							// Rellenado de columna CW código de la entidad financiera
							$batchSpecialData[] = ["CW" . $row_num, $banks_account_code[$bank_name] ?? ""];
						}
					}
					// Rellenado de columna CZ
					if($row->dff_context === 'AR_COMMON'){
						$batchSpecialData[] = ["CZ" . $row_num, "LG ELECTRONICS PERU S.A." ?? ""];
					}
					elseif($row->dff_context !== 'AR_COMMON'){
						if(empty($row->vendor_customer) && !empty($row->bank_name)){
							$razon_social = ['SCOTIA'=>'SCOTIABANK PERU SAA', 'BCP'=>'BANCO DE CREDITO DEL PERU', 'CITI'=>'CITIBANK DEL PERU S.A.', 'INTER'=>'BANCO INTERNACIONAL DEL PERU-INTERBANK', 'BBVA'=>'BANCO BBVA PERU', 'NACION'=>'VARIOS'];
							$batchSpecialData[] = ["CZ" . $row_num, $razon_social[$bank_name] ?? ""];
						}
						else{
							if (strpos($row->vendor_customer, 'GCC') !== 0){
								if (preg_match(	'/ESPR_([^\/]+\/)(.*?)_PE/', $row->vendor_customer, $matches)) {
									//return trim($matches[1]); // Retorna la parte deseada
									$batchSpecialData[] = ["CZ" . $row_num, trim($matches[2]) ?? ""];
								}
								elseif (preg_match(	'/ESPR_([^_]+)_PE/', $row->vendor_customer, $matches)) {
									$batchSpecialData[] = ["CZ" . $row_num, trim($matches[1]) ?? ""];
								}
							}
						}
					}
					
					//Rellando de columna DA
					if (isset($bank_code_map[$row->effective_date])) {
						foreach($bank_code_map[$row->effective_date] as $item_date){
							if($bank_name ===  $item_date['bank_name'] && $row->net_entered_debit == $item_date['total_amount']){
								$batchSpecialData[] = ["DA" . $row_num, $item_date['number_operation'] ?? ""];
								break;
							}
						}
					}
					
					//Rellenado de columna DY
					if(!empty($banks_account_code[$bank_name])){
						$batchSpecialData[] = ["CY" . $row_num, '003' ?? ""];
					}
				}
				
				
				// Rellenado columna DC												
				 $formulaData = "=CONCATENATE(CA$row_num,\"|\",CB$row_num,\"|\",CC$row_num,\"|\",CD$row_num,\"|\",CE$row_num,\"|\",CF$row_num,\"|\",CG$row_num,\"|\",CH$row_num,\"|\",LEFT(CI$row_num,15),\"|\",CJ$row_num,\"|\",CK$row_num,\"|\",CL$row_num,\"|\",CM$row_num,\"|\",CN$row_num,\"|\",TEXT(CO$row_num,\"DD/MM/YYYY\"),\"|\",CP$row_num,\"|\",CQ$row_num,\"|\",IF(CR$row_num>0,CR$row_num,\"0.00\"),\"|\",IF(CS$row_num>0,CS$row_num,\"0.00\"),\"|\",CT$row_num,\"|\",CU$row_num,\"|\",CV$row_num,\"|\",CW$row_num,\"|\",CX$row_num,\"|\",CY$row_num,\"|\",CZ$row_num,\"|\",DA$row_num)";
				 
				
				// Aplicar la fórmula a la celda en la columna DC
				//print_r($formulaData);
				$batchSpecialData[] = ["DC" . $row_num, $formulaData ?? ""];
				
				$row_num++;

				if (count($batchData) >= $batchSize) {
					$this->writeBatchToSheet($sheet, $batchData, $batchSpecialData, $row_num);
					$batchData = [];
					$batchSpecialData = [];
				}
			}
		}
		
		$fetchEnd = microtime(true);
		
		// **6. Escribir en el Excel**
		$writeStart = microtime(true);
		// Escribir datos restantes
		if (!empty($batchData)) {
			$this->writeBatchToSheet($sheet, $batchData, $batchSpecialData, $row_num);
		}
		
		// Insertar las sumas acumuladas en la fila final + 1
		$sheet->setCellValue("CR" . ($row_num+1), "=SUM(CR6:CR$row_num)");
		$sheet->setCellValue("CS" . ($row_num+1), "=SUM(CS6:CS$row_num)");
		
		// $sheet->setCellValue("CR" . ($row_num+1), $sum_net_accounted_debit_pos);
		// $sheet->setCellValue("CS" . ($row_num+1), $sum_net_accounted_debit_ne);

		$writeEnd = microtime(true);
		log_message('info', "Tiempo de carga de plantilla: " . ($loadEnd - $loadStart) . " segundos");
		log_message('info', "Tiempo de obtención de datos de entrada: " . ($dataEnd - $dataStart) . " segundos");
		log_message('info', "Tiempo de extracción de información adicional: " . ($bizEnd - $bizStart) . " segundos");
		log_message('info', "Tiempo de solicitud a DB: " . ($data_end - $data_start) . " segundos");
		log_message('info', "Tiempo de obtención de datos en lotes: " . ($fetchEnd - $fetchStart) . " segundos");
		log_message('info', "Tiempo de escritura en Excel: " . ($writeEnd - $writeStart) . " segundos");

		// Guardar y descargar archivo
		$saveStart = microtime(true);
		$this->downloadSpreadsheet($spreadsheet, "tax_daily_book.xlsx");
		$saveEnd = microtime(true);
		log_message('info', "Tiempo de guardado y descarga: " . ($saveEnd - $saveStart) . " segundos");
	}

	
	public function writeBatchToSheet($sheet, &$batchData, &$batchSpecialData, $row_num) {
		$startRow = $row_num - count($batchData);
		$sheet->fromArray($batchData, null, "E$startRow");

		// Usar batch processing para columnas especiales
		$sheet->getStyle("B$startRow:B$row_num")->getNumberFormat()->setFormatCode('@'); // Forzar formato de texto
		$sheet->getStyle("CA$startRow:CA$row_num")->getNumberFormat()->setFormatCode('@'); // Formato de texto

		foreach ($batchSpecialData as [$cell, $value]) {
			if (is_numeric($value) && strlen($value) > 10) {
				$sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			}elseif(preg_match('/^(CR|CS)\d+$/', $cell) && is_numeric($value)){
				$sheet->setCellValue($cell, (float) $value); // Inserta como número
			}else {
				$sheet->setCellValue($cell, $value);
			}
		}
	}


	
	public function downloadSpreadsheet($spreadsheet, $filename) {		

		// Configurar cabeceras de respuesta	
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		
		
		$tempFile = tempnam(sys_get_temp_dir(), 'excel');	
		// Escribir directamente al output
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		//$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		
		$writer->setPreCalculateFormulas(false);
		$writer->setUseDiskCaching(true); // Activa el uso de caché en disco

		$writer->save($tempFile);
		//$writer->save('php://output'); // Enviar directamente sin archivo temporal
		readfile($tempFile);
		
		// Limpiar la memoria
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);
		// Recolectar ciclos de basura
		gc_collect_cycles();
	
		ob_end_clean();

	}

	public function fetch_large_data($table, $where, $batchSize = 5000) {
		$offset = 0;
		// $this->db->select('*')->from($table)->where($where);
		// $query = $this->db->get();
		$columns = ["legal_entity", "period_name","effective_date", "posted_date", "accounting_unit", "department", "department_name","account", "account_name","project", "affiliate", "temporary1", "temporary2", "currency", "net_entered_debit", "entered_debit", "entered_credit","net_accounted_debit","accounted_debit","accounted_credit","description_ar_comments","journal_source","journal_category","gl_batch_name","gl_journal_name","gl_document_seq_number","ap_ar_source","line_type","ap_ar_batch_name","invoice_number","transaction_number","transaction_date","check_number","receipt_number","vendor_customer","bank_name","bank_account_number","business_number","tax_payer_id","subledger_document_seq_number","tax_date","tax_code","tax_rate","created_by","create_user_name","dff_context","dff1","dff2","dff3","dff4","dff5","dff6","dff7","dff8","dff9","dff10","dff11","dff12","dff13","dff14","dff15","dff16","dff17","dff18","dff19","dff20","lease_no","asset_number","org_id","link_id","je_header_id","je_line_number","je_id","type_voucher","serie_voucher","number_voucher"];
		
		// while (true) {
			// $query = $this->db->select($columns)
							  // ->from($table)
							  // ->limit($batchSize, $offset)
							  // ->where($where)
							  // ->order_by('effective_date', 'ASC')
							  // ->get();
			
			// if ($query->num_rows() === 0) {
				// break;
			// }

			// yield from array_map(fn($row) => $row, $query->result());

			// $offset += $batchSize;
			// $query->free_result();
		// }

		while (true) {
			$query = $this->db->select($columns)
							  ->from($table)
							  ->limit($batchSize, $offset)
							  ->where($where)
							  ->order_by('effective_date', 'ASC')							  
							  ->get();

			if ($query->num_rows() === 0) {
				break; // No hay más datos, salir del bucle
			}

			foreach ($query->result() as $row) {
				yield $row; // Devuelve fila por fila sin cargar todo en memoria
			}

			// Avanzar al siguiente bloque
			$offset += $batchSize;

			// Liberar memoria
			$query->free_result();
		}
	}
	public function export_to_excel() {
		
		// Llamamos a la función que genera el archivo Excel
		$this->generate_excel();
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 200000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'tax_daily_book.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		$response = ["type" => $type, "msg" => $msg];

		//error_log(json_encode($response)); // Agregar esto para depurar
		
		echo json_encode($response);
	}

}