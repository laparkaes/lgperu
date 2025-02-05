<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Tax_daily_book extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		// $w = ["updated >=" => date("Y-m-d", strtotime("-3 months"))];
		// $o = [["updated", "desc"], ["model_description", "asc"], ["model", "asc"]];
		
		$data = [
			"tax"	=> $this->gen_m->filter("tax_daily_book", false, null, null, null, null, 5000),
			"main" 		=> "data_upload/tax_daily_book/index",
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
		$suffixes = ['_CM', '_PEN', '_PEN_CM', '_1_CM', '_IGVND', '-R', '-I', '_Reversa1', '_FSE']; // Sufijos a eliminar al final
		
		if (is_array($code)) {
			// Si $code es un array, convertirlo en una cadena (opcional, según lo que quieras hacer)
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
			
			// Si el dato inicio por IBT|PP|CN|EV
			elseif (is_string($cleanedCode) && preg_match('/^(IBT|PP|CN|EV)/', $cleanedCode)) {
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
				elseif(count($parts) == 4 || count($parts) == 6){
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
					if ($parts[1] === 'RM213'){
						$row["type_voucher"] = '00';
						$row["serie_voucher"] = '0000';
						$row["number_voucher"] = $parts[1].$parts[2];
					}
					elseif (strpos($parts[1], 'F') === 0 || strpos($parts[1], 'E') === 0){
						$row["type_voucher"] = '01';
						$row["serie_voucher"] = $parts[1];
						$row["number_voucher"] = $parts[2];
					}
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
			elseif (preg_match('/^(HQ|SPG|KR|CORE|CS|SVC|EMI)/', $cleanedCode)) {
				$row["type_voucher"] = '91';         // Primera columna nueva
				$row["serie_voucher"] = '0000';       // Segunda columna nueva
				$row["number_voucher"] = strval($cleanedCode); // Tercera columna nueva (valor original tal cual)
				//continue;
			}
			
			// Si el valor original comienza por 5000011
			elseif (strpos($cleanedCode, '5000011') === 0) {
				$row["type_voucher"] = '91';         // Primera columna nueva
				$row["serie_voucher"] = '0000';       // Segunda columna nueva
				$row["number_voucher"] = strval($cleanedCode); // Tercera columna nueva (valor original tal cual)
				//continue;
			}

			// Si el código comienza con "Bxxxx"
			elseif (preg_match('/^B\w+/', $cleanedCode, $matches)) {
					$firstGroup = '03';                         // Primer grupo fijo
					$secondGroup = $matches[0];                 // "Bxxxx"
					$thirdGroup = strval($parts[1] ?? '');  // El que sigue  BXXX
					// Escribir los valores en nuevas columnas
					$row["type_voucher"] = $firstGroup;
					$row["serie_voucher"] = $secondGroup;
					$row["number_voucher"] = $thirdGroup;
					//continue;
			} 

			//Si el primer grupo es "50" o "53"
			elseif ($parts[0] === strval('50') || $parts[0] === strval('53')) {
					$parts[0] = $this->type_verify($parts[0]);
					$row["type_voucher"] = $parts[0];    // Primera columna nueva
					$row["serie_voucher"] = $parts[1];       // Segunda columna nueva
					$row["number_voucher"] = strval($parts[4] ?? '');    // Tercera columna nueva
					//continue;
			}
					
			elseif ($parts[0] === "91"){  // Primer grupo 91
					if (isset($parts[1]) && count($parts) == 2) {
							$row["type_voucher"] = $parts[0];    // Primera columna nueva
							$row["serie_voucher"] = '0000';       // Segunda columna nueva
							$row["number_voucher"] = strval($parts[1]);    // Tercera columna nueva
							//continue;
					} 
					elseif(count($parts) > 2) {
							$row["type_voucher"] = '91';                          // Primera columna nueva
							$row["serie_voucher"] = '0000';                        // Segunda columna nueva
							$row["number_voucher"] = implode('', array_slice($parts, 1)); // Concatenar los demás grupos como tercer valor
							//continue;
					} 
			}
			elseif(strpos($parts[0], '00F001') === 0){
				$row["type_voucher"] = '01';    // Primera columna nueva
				$row["serie_voucher"] = $parts[0];       // Segunda columna nueva
				$row["number_voucher"] = $parts[1];    // Tercera columna nueva
			}
			elseif(strpos($parts[0], 'DT') === 0){
				$row["type_voucher"] = $parts[1];    // Primera columna nueva
				$row["serie_voucher"] = $parts[2];       // Segunda columna nueva
				$row["number_voucher"] = $parts[3];    // Tercera columna nueva
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
					$row["type_voucher"] = $parts[0] ?? ''; // Nueva columna 1
					$row["serie_voucher"] = $parts[1] ?? ''; // Nueva columna 2
					$row["number_voucher"] = $parts[2] ?? ''; // Nueva columna 3
				}
			}
			elseif (strpos($parts[0], '001')===0){
				$row["type_voucher"] = '91';    // Primera columna nueva
				$row["serie_voucher"] = '0000';       // Segunda columna nueva
				$row["number_voucher"] = $parts[0].$parts[1].$parts[2];
			}
			
			else{
				$parts[0] = $this->type_verify($parts[0]);
				$row["type_voucher"] = $parts[0] ?? ''; // Nueva columna 1
				$row["serie_voucher"] = $parts[1] ?? ''; // Nueva columna 2
				$row["number_voucher"] = $parts[2] ?? ''; // Nueva columna 3
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
						"net_accounted_debit"			=> $values[16],	
						"accounted_debit"				=> $values[17],	
						"accounted_credit"				=> $values[18],	
						"entered_credit"				=> $values[19],	
						"description_ar_comments"		=> $values[20],	
						"journal_category"				=> $values[21],	
						"gl_Batch_name"					=> $values[22],	
						"journal_source"				=> $values[23],	
						"gl_document_seq_number"		=> $values[24],	
						"ap_ar_source"					=> $values[25],	
						"gl_journal_name"				=> $values[26],	
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

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			//print_r($msg); return;
			return $msg;			
		}else return "Error: Header validation failed.";
	}
	// Operaciones para invoice_number
	public function process_row($batch_data){	
		$batch_insert = [];
		
		// Obtener los je_id únicos de los datos entrantes
		$existing_je_ids_result = $this->gen_m->filter_select('tax_daily_book', false, 'je_id');
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
				//print_r($row_modified); echo '<br>'; echo '<br>'; echo '<br>';
				//$this->gen_m->insert("tax_daily_book", $row_modified);	
				$batch_insert[] = $row_modified;
				//return $batch_insert;
			}
		}
		if (!empty($batch_insert)) {
			$this->gen_m->insert_m("tax_daily_book", $batch_insert);
			//$batch_insert = [];
		}
		
	}
	// Generar excel
	public function generate_excel() {
		// Aumentar memoria y tiempo de ejecución
		ini_set('memory_limit', -1);
		set_time_limit(0);

		// Ruta de la plantilla de Excel
		$template_path = './template/tax_daily_book_template.xlsx';

		if (!file_exists($template_path)) {
			echo "Error: No se encontró la plantilla de Excel.";
			return;
		}

		// Cargar la plantilla
		$spreadsheet = IOFactory::load($template_path);
		$sheet = $spreadsheet->getActiveSheet();

		// Recuperar las fechas desde el formulario
		$from_date = $this->input->post('effective_from');
		$to_date = $this->input->post('effective_to');

		if (!$from_date || !$to_date) {
			echo "Error: Fechas no proporcionadas";
			return;
		}

		// Filtrar por fechas
		$where = [
			'effective_date >= ' => $from_date,
			'effective_date <= ' => $to_date
		];

		// Definir columnas
		$numeric_columns = ["net_entered_debit", "entered_debit", "entered_credit", 
							"net_accounted_debit", "accounted_debit", "accounted_credit"];
		$date_columns = ['effective_date', 'posted_date', 'transaction_date', 'tax_date'];

		// Definir las columnas específicas donde se deben escribir ciertos datos
		$columnMap = [
			"type_voucher" => 'CJ',
			"serie_voucher" => 'CK',
			"number_voucher" => 'CL'
		];

		// Obtener datos con paginación usando Generator
		$row_num = 6; // Iniciar desde la fila 6 (debajo de los encabezados)
		$batchData = [];
		$batchSize = 5000; // Ajusta el tamaño según la memoria disponible

		foreach ($this->fetch_large_data('tax_daily_book', $where) as $row) {
			if ($row->accounting_unit !== 'EPG' && $row->accounting_unit !== 'INT') {
				$dataRow = [];
				foreach ($row as $key => $value) {
					if (!in_array($key, ['daily_book_id', 'je_id', 'updated'])) {
						if (in_array($key, $numeric_columns)) {
							$dataRow[] = (float) $value ?: 0;
						} elseif (in_array($key, $date_columns)) {
							$dataRow[] = date('d-m-Y', strtotime($value));
						} elseif (!isset($columnMap[$key])) {
							$dataRow[] = $value;
						}
					}
				}

				// Escribir los datos numéricos y de fecha en bloque
				$sheet->fromArray([$dataRow], null, "E$row_num");

				// Escribir valores específicos en CJ, CK y CL en la fila correcta
				foreach ($columnMap as $key => $col) {
					if (isset($row->$key)) {
						$sheet->setCellValueExplicit("{$col}{$row_num}", $row->$key, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
					}
				}

				$row_num++;

				if (count($batchData) >= $batchSize) {
					$batchData = []; // Resetear el buffer
				}
			}
		}

		// Guardar en un archivo temporal para evitar consumo de memoria
		$temp_file = tempnam(sys_get_temp_dir(), 'excel');
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->setPreCalculateFormulas(false); // Desactiva cálculos innecesarios
		$writer->save($temp_file);

		// Descargar el archivo sin cargarlo en memoria
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="tax_daily_book.xlsx"');
		header('Cache-Control: max-age=0');
		readfile($temp_file);
		unlink($temp_file);
		
		// Depuración: Mostrar uso de memoria
		//error_log('Memoria usada: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");
	}




	public function fetch_large_data($table, $where) {
		$this->db->select('*')->from($table)->where($where);
		$query = $this->db->get();
		
		foreach ($query->result() as $row) {
			yield $row; // Devuelve fila a fila sin cargar todo en memoria
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
