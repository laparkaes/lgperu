<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Scm_goodset_return extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		//$this->load->model('general_espr_model', 'gen_e');
	}
	
	public function index(){
		
		$o = [["entry_date", "desc"], ["approval_date", "desc"], ["receiving_date", "desc"]];
		
		$data = [
			"goodset_returns"	=> $this->gen_m->filter("scm_goodset_return", false, null, null, null, $o, 5000),
			"main" 			=> "data_upload/scm_goodset_return/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function process(){
		ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$start_time = microtime(true);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/scm_goodset_return.xls");
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('R1')->getValue()),
		];
		
		//sales order header
		$h_gerp = ["RMA Type", "Order  Type", "RMA No", "RMA  Line No", "Status", "Entry Amt"];
		
		//header validation
		$is_gerp = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_gerp[$i]) $is_gerp = false;
		
		if ($is_gerp){
			$max_row = $sheet->getHighestRow();
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			$rows = $order_lines = [];
			$records = 0;
		
			for($i = 2; $i <= $max_row; $i++){
				$row = [
				
					'rma_type'=> trim($sheet->getCell('A'.$i)->getValue()),
					'order_type'=> trim($sheet->getCell('B'.$i)->getValue()),
					'rma_no'=> trim($sheet->getCell('C'.$i)->getValue()),
					'rma_line_no'=> trim($sheet->getCell('D'.$i)->getValue()),
					'status'=> trim($sheet->getCell('E'.$i)->getValue()),
					'approval_status'=> trim($sheet->getCell('F'.$i)->getValue()),
					'bill_to_name'=> trim($sheet->getCell('G'.$i)->getValue()),
					'bill_to_code'=> trim($sheet->getCell('H'.$i)->getValue()),
					'ship_to_name'=> trim($sheet->getCell('I'.$i)->getValue()),
					'ship_to_code'=> trim($sheet->getCell('J'.$i)->getValue()),
					'currency'=> trim($sheet->getCell('K'.$i)->getValue()),
					'charge'=> trim($sheet->getCell('L'.$i)->getValue()),
					'surcharge'=> trim($sheet->getCell('M'.$i)->getValue()),
					'return_reason'=> trim($sheet->getCell('N'.$i)->getValue()),
					'model'=> trim($sheet->getCell('O'.$i)->getValue()),
					'price'=> trim($sheet->getCell('P'.$i)->getValue()),
					'entry_qty'=> trim($sheet->getCell('Q'.$i)->getValue()),
					'entry_amt'=> trim($sheet->getCell('R'.$i)->getValue()),
					'approval_qty'=> trim($sheet->getCell('S'.$i)->getValue()),
					'receiving_qty'=> trim($sheet->getCell('T'.$i)->getValue()),
					'original_salesperson'=> trim($sheet->getCell('U'.$i)->getValue()),
					'rma_salesperson'=> trim($sheet->getCell('V'.$i)->getValue()),
					'sales_rep'=> trim($sheet->getCell('W'.$i)->getValue()),
					'warehouse'=> trim($sheet->getCell('X'.$i)->getValue()),
					'subinventory'=> trim($sheet->getCell('Y'.$i)->getValue()),
					'payment_term'=> trim($sheet->getCell('Z'.$i)->getValue()),
					'sales_invoice_no'=> trim($sheet->getCell('AA'.$i)->getValue()),
					'sales_invoice_date'=> trim($sheet->getCell('AB'.$i)->getValue()),
					'reference_no'=> trim($sheet->getCell('AC'.$i)->getValue()),
					'sales_order_no'=> trim($sheet->getCell('AD'.$i)->getValue()),
					'cust_po_no'=> trim($sheet->getCell('AE'.$i)->getValue()),
					'rma_invoice_no'=> trim($sheet->getCell('AF'.$i)->getValue()),
					'product_level1'=> trim($sheet->getCell('AG'.$i)->getValue()),
					'product_level2'=> trim($sheet->getCell('AH'.$i)->getValue()),
					'store_no'=> trim($sheet->getCell('AI'.$i)->getValue()),
					'created_emp_name'=> trim($sheet->getCell('AJ'.$i)->getValue()),
					'modify_emp_name'=> trim($sheet->getCell('AK'.$i)->getValue()),
					'cancel_date'=> trim($sheet->getCell('AL'.$i)->getValue()),
					'cancel_reason'=> trim($sheet->getCell('AM'.$i)->getValue()),
					'cancel_comments'=> trim($sheet->getCell('AN'.$i)->getValue()),
					'bpm_submission_no'=> trim($sheet->getCell('AO'.$i)->getValue()),
					'old_rma_no'=> trim($sheet->getCell('AP'.$i)->getValue()),
					'return_remark1'=> trim($sheet->getCell('AQ'.$i)->getValue()),
					'return_remark2'=> trim($sheet->getCell('AR'.$i)->getValue()),
					'receiving_remark'=> trim($sheet->getCell('AS'.$i)->getValue()),
					'asn_i/f_flag'=> trim($sheet->getCell('AT'.$i)->getValue()),
					'entry_date'=> trim($sheet->getCell('AU'.$i)->getValue()),
					'approval_date'=> trim($sheet->getCell('AV'.$i)->getValue()),
					'receiving_date'=> trim($sheet->getCell('AW'.$i)->getValue()),
					'header_charge_amt'=> trim($sheet->getCell('AX'.$i)->getValue()),
					'header_surcharge_amt'=> trim($sheet->getCell('AY'.$i)->getValue()),
					'nota_fiscal_serie_no'=> trim($sheet->getCell('AZ'.$i)->getValue()),
					'rnp_date'=> trim($sheet->getCell('BA'.$i)->getValue()),
					'rnp_no'=> trim($sheet->getCell('BB'.$i)->getValue()),
					'updated_at' => $now
				];
				
				//comma remove
				if ($row["price"]) $row["price"] = str_replace(",", "", $row["price"]);
				if ($row["entry_amt"]) $row["entry_amt"] = str_replace(",", "", $row["entry_amt"]);
				if ($row["entry_qty"]) $row["entry_qty"] = str_replace(",", "", $row["entry_qty"]);
				if ($row["approval_qty"]) $row["approval_qty"] = str_replace(",", "", $row["approval_qty"]);
				if ($row["receiving_qty"]) $row["receiving_qty"] = str_replace(",", "", $row["receiving_qty"]);
				
				//date convert: dd/mm/yyyy > yyyy-mm-dd
				if ($row["sales_invoice_date"]) $row["sales_invoice_date"] = $this->my_func->date_convert($row["sales_invoice_date"]);
				if ($row["entry_date"]) $row["entry_date"] = $this->my_func->date_convert($row["entry_date"]);
				if ($row["approval_date"]) $row["approval_date"] = $this->my_func->date_convert($row["approval_date"]);
				if ($row["receiving_date"]) $row["receiving_date"] = $this->my_func->date_convert($row["receiving_date"]);
				if ($row["rnp_date"]) $row["rnp_date"] = $this->my_func->date_convert($row["rnp_date"]);
				
				//insert or update record
				$w = ["rma_no" => $row["rma_no"], "rma_line_no" => $row["rma_line_no"]];
				if ($this->gen_m->filter("scm_goodset_return", false, $w)) $this->gen_m->update("scm_goodset_return", $w, $row);
				else $this->gen_m->insert("scm_goodset_return", $row);
				
				$records++;
				
				print_r($row); echo "<br/><br/>";
			}
			
			$msg = number_format($records)." record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";
		}else $msg = "File template error. Please check upload file.";
		
		//return $msg;
		echo $msg;
		echo "<br/><br/>";
		echo 'You can close this tab now.<br/><br/><button onclick="window.close();">Close This Tab</button>';
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
				'file_name'		=> 'scm_goodset_return.xls',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = "File upload completed successfully.<br/>A new tab will open to process the DB operations.<br/><br/>Please do not close new tab.";
				$type = "success";
				/*
				$msg = $this->process();//delete & insert
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
				*/
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function test(){
		
		
        $base_e_documents_path = FCPATH . 'eDocuments/'; // Ruta base a la carpeta eDocuments
        $current_year = date('Y'); // Obtener el año actual dinámicamente

        // Subcarpetas específicas donde buscar los XML
        $target_subfolders = ['FACTURA_ELECTRONICA', 'NOTA_DE_CREDITO'];

		$original_invoice_no = "";
		$search_value = "hola";
		
        //foreach ($invoice_data_to_search as $original_invoice_no => $search_value) {
            $found_delivery_note = ''; // Reset para cada factura
            $xml_found_and_processed = false; // Flag para saber si ya encontramos y procesamos el XML para esta factura

            if (empty($search_value)) {
                log_message('info', 'Skipping file search for empty/null processed invoice_no for original: ' . $original_invoice_no);
                $xml_found_and_processed = true; // No hay nada que buscar, consideramos "procesado"
            } else {
                // Iterar sobre cada mes (01 a 12)
                for ($month = 1; $month <= 12; $month++) {
                    if ($xml_found_and_processed) break; // Salir del bucle de meses si ya se encontró el XML

                    $month_padded = str_pad($month, 2, '0', STR_PAD_LEFT); // Formato MM (ej. 01, 02)
                    $month_folder_path = $base_e_documents_path . $current_year . '/' . $month_padded . '/';

                    // Verificar si la carpeta del mes existe antes de buscar dentro
                    if (!is_dir($month_folder_path)) {
                        log_message('debug', 'Monthly folder not found: ' . $month_folder_path);
                        continue; // Pasar al siguiente mes
                    }
					
                    // Iterar sobre las subcarpetas objetivo (FACTURA_ELECTRONICA, NOTA_DE_CREDITO)
                    foreach ($target_subfolders as $subfolder) {
                        if ($xml_found_and_processed) break; // Salir del bucle de subcarpetas si ya se encontró el XML

                        $target_path = $month_folder_path . $subfolder . '/';
                        $expected_file_path = $target_path . $search_value . '.xml';

                        log_message('debug', 'Attempting to find XML at: ' . $expected_file_path);

                        if (file_exists($expected_file_path)) {
                            libxml_use_internal_errors(true); // Habilitar manejo interno de errores XML
                            $xml = simplexml_load_file($expected_file_path);

                            if ($xml === false) {
                                $xml_errors = libxml_get_errors();
                                $error_msg = "XML parsing error for file {$expected_file_path}: ";
                                foreach ($xml_errors as $error) {
                                    $error_msg .= $error->message . " ";
                                }
                                log_message('error', $error_msg);
                                libxml_clear_errors();
                            } else {
                                // --- Lógica para buscar el GuiaRem dentro del XML (TU CÓDIGO EXISTENTE Y FUNCIONAL) ---
                                $ext_namespace_uri = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
                                $xml->registerXPathNamespace('ext', $ext_namespace_uri);
                                
                                $default_namespaces = $xml->getDocNamespaces(true);
                                $default_namespace_uri = isset($default_namespaces['']) ? $default_namespaces[''] : null;
                                $guia_rem_nodes = []; // Inicializar array para resultados
                                $xpath_query_used = ''; // Para fines de log

                                if ($default_namespace_uri) {
                                    $xml->registerXPathNamespace('def', $default_namespace_uri);
                                    $xpath_query_used = '//ext:UBLExtension/ext:ExtensionContent/def:CustomText/def:Text[@name="GuiaRem"]';
                                    $guia_rem_nodes = $xml->xpath($xpath_query_used);

                                    if (empty($guia_rem_nodes)) {
                                        $xpath_query_used = '//ext:UBLExtension/ext:ExtensionContent/CustomText/Text[@name="GuiaRem"]';
                                        $guia_rem_nodes = $xml->xpath($xpath_query_used);
                                    }
                                } else {
                                    $xpath_query_used = '//ext:UBLExtension/ext:ExtensionContent/CustomText/Text[@name="GuiaRem"]';
                                    $guia_rem_nodes = $xml->xpath($xpath_query_used);
                                }
                                
                                if (!empty($guia_rem_nodes)) {
                                    $found_delivery_note = (string)$guia_rem_nodes[0];
                                    log_message('info', 'Found delivery note: ' . $found_delivery_note . ' for invoice: ' . $original_invoice_no . ' in ' . $expected_file_path);
                                    $xml_found_and_processed = true; // Marcar como encontrado y procesado
                                } else {
                                    log_message('warning', 'Delivery note (GuiaRem) not found in XML: ' . $expected_file_path . ' using XPath: ' . $xpath_query_used);
                                }
                            }
                            libxml_clear_errors(); // Siempre limpiar errores después de procesar
                        } else {
                            log_message('debug', 'XML file NOT FOUND for ' . $original_invoice_no . ' in ' . $expected_file_path);
                        }
                    } // Fin del foreach ($target_subfolders)
                } // Fin del for ($month)
            }
            
			echo $found_delivery_note;
			
            // Actualizar la base de datos con el valor encontrado (o vacío si no se encontró)
           // $this->gen_m->update("scm_gre_calculate", ['invoice_no' => $original_invoice_no, 'user_pr' => $emp_number], ['delivery_note' => $found_delivery_note]);
        //}
		
	}
	
}
