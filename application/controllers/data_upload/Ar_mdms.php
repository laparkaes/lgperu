<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Ar_mdms extends CI_Controller {

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
			"stocks"	=> $this->gen_m->filter("ar_mdms", false, null, null, null, "", 5000),
			"main" 		=> "data_upload/ar_mdms/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function process(){ // ok
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);

		//delete all rows ar_mdms 
		$this->gen_m->truncate("ar_mdms");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/ar_mdms.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

		//excel file header validation
		$h = [
			trim($sheet->getCell('A5')->getValue()),
			trim($sheet->getCell('B5')->getValue()),
			trim($sheet->getCell('C5')->getValue()),
			trim($sheet->getCell('D5')->getValue()),
			trim($sheet->getCell('E5')->getValue()),
			trim($sheet->getCell('F5')->getValue()),
			trim($sheet->getCell('G5')->getValue()),
			trim($sheet->getCell('H5')->getValue()),
		];

		$header = ["Code ID", "LGEDIV[ID]", "LGEDIV[NAME]", "Company(Affiliate) Code[ID]", "Company(Affiliate) Code[NAME]", "Short Name(ENG)", "AU[ID]", "AU[NAME]"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

		if ($is_ok){
			$updated = date("Y-m-d H:i:s");
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 1000;
			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 6; $i <= $max_row; $i++){
				$row = [
					"code_id" 								=> trim($sheet->getCell('A'.$i)->getValue()),
					"lgediv_id" 							=> trim($sheet->getCell('B'.$i)->getValue()),
					"lgediv_name" 							=> trim($sheet->getCell('C'.$i)->getCalculatedValue()),
					"company_affiliate_code_id" 			=> trim($sheet->getCell('D'.$i)->getValue()),
					"company_affiliate_code_name" 			=> trim($sheet->getCell('E'.$i)->getValue()),
					"short_name_eng	"						=> trim($sheet->getCell('F'.$i)->getValue()),
					"au_id" 								=> trim($sheet->getCell('G'.$i)->getValue()),
					"au_name" 								=> trim($sheet->getCell('H'.$i)->getValue()),
					"supplier_code"							=> trim($sheet->getCell('I'.$i)->getValue()),
					"supplier_name_loc"						=> trim($sheet->getCell('J'.$i)->getValue()),
					"supplier_name_eng"						=> trim($sheet->getCell('K'.$i)->getValue()),
					"biz_registration_no"					=> trim($sheet->getCell('L'.$i)->getValue()),
					"domain_type"							=> trim($sheet->getCell('M'.$i)->getValue()),		
					"job_type_id"							=> trim($sheet->getCell('N'.$i)->getValue()),
					"job_type_name"							=> trim($sheet->getCell('O'.$i)->getCalculatedValue()),
					"trade_type_id"							=> trim($sheet->getCell('P'.$i)->getValue()),
					"trade_type_name"						=> trim($sheet->getCell('Q'.$i)->getCalculatedValue()),
					"currency_code_id"						=> trim($sheet->getCell('R'.$i)->getValue()),
					"currency_code_name"					=> trim($sheet->getCell('S'.$i)->getValue()),
					"term_days"								=> trim($sheet->getCell('T'.$i)->getValue()),
					"payment_terms_name"					=> trim($sheet->getCell('U'.$i)->getValue()),
					"hub_use_flag_id"						=> trim($sheet->getCell('V'.$i)->getValue()),
					"hub_use_flag_name"						=> trim($sheet->getCell('W'.$i)->getCalculatedValue()),
					"payterm_type"							=> trim($sheet->getCell('X'.$i)->getValue()),
					"available_period_from"					=> trim($sheet->getCell('Y'.$i)->getValue()),
					"available_period_to"					=> trim($sheet->getCell('Z'.$i)->getValue()),
					"settlement_type_id"					=> trim($sheet->getCell('AA'.$i)->getValue()),
					"settlement_type_name"					=> trim($sheet->getCell('AB'.$i)->getCalculatedValue()),
					"due_counted_point_id"					=> trim($sheet->getCell('AC'.$i)->getValue()),
					"due_counted_point_name"				=> trim($sheet->getCell('AD'.$i)->getCalculatedValue()),
					"prorate_basis_type_id"					=> trim($sheet->getCell('AE'.$i)->getValue()),
					"prorate_basis_type_name"				=> trim($sheet->getCell('AF'.$i)->getCalculatedValue()),
					"payment_group_id"						=> trim($sheet->getCell('AG'.$i)->getValue()),
					"payment_group_name"					=> trim($sheet->getCell('AH'.$i)->getCalculatedValue()),
					"payment_method"						=> trim($sheet->getCell('AI'.$i)->getValue()),
					"collection_redem_at_sight_l_c"			=> trim($sheet->getCell('AJ'.$i)->getValue()),
					"bank_charge_payment_entity_l_c"		=> trim($sheet->getCell('AK'.$i)->getValue()),
					"document_submit_days_l"				=> trim($sheet->getCell('AL'.$i)->getValue()),	
					"usane_l_c_type"						=> trim($sheet->getCell('AM'.$i)->getValue()),
					"usance_l_c_interest_pay_type"			=> trim($sheet->getCell('AN'.$i)->getValue()),
					"usance_l_c_interest_rate"				=> trim($sheet->getCell('AO'.$i)->getValue()),
					"enabled_flag_id"						=> trim($sheet->getCell('AP'.$i)->getValue()),
					"enabled_flag_name"						=> trim($sheet->getCell('AQ'.$i)->getCalculatedValue()),
					"payterm_key"							=> trim($sheet->getCell('AR'.$i)->getValue()),
					"creation_date"							=> trim($sheet->getCell('AS'.$i)->getValue()),
					"creation_user_id"						=> trim($sheet->getCell('AT'.$i)->getValue()),
					"last_update_date"						=> trim($sheet->getCell('AU'.$i)->getValue()),
					"last_updated_by"						=> trim($sheet->getCell('AV'.$i)->getValue()),
					"updated"								=> $updated,
				];

				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("ar_mdms", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("ar_mdms", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			$this->db->trans_complete();
			return $msg;		
		}else return "";
	}
	
	public function export_excel(){
		
		$data = $this->gen_m->filter('ar_mdms', false);
		$template_path = './template/ar_mdms_template.xlsx';
		
		try {
            $spreadsheet = IOFactory::load($template_path);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('Error loading the Excel template: ' . $e->getMessage());
        }
		$sheet = $spreadsheet->getActiveSheet();
		
		$start_row = 6;
        $current_row = $start_row;
		
        if ($data) {
            foreach ($data as $row) {            
                $sheet->setCellValue('A' . $current_row, $row->code_id); 							// Column A: code_id                           
                $sheet->setCellValue('B' . $current_row, $row->lgediv_id); 							// Column B: lgediv_id
                $sheet->setCellValue('C' . $current_row, $row->lgediv_name); 						// Column C: lgediv_name
				$sheet->setCellValue('D' . $current_row, $row->company_affiliate_code_id); 			// Column D: company_affiliate_code_id
				$sheet->setCellValue('E' . $current_row, $row->company_affiliate_code_name); 		// Column E: company_affiliate_code_name
				$sheet->setCellValue('F' . $current_row, $row->short_name_eng); 					// Column F: short_name_eng
				$sheet->setCellValue('G' . $current_row, $row->au_id); 								// Column G: au_id
				$sheet->setCellValue('H' . $current_row, $row->au_name); 							// Column H: au_name
				$sheet->setCellValue('I' . $current_row, $row->supplier_code);						// Column I: supplier_code
				$sheet->setCellValue('J' . $current_row, $row->supplier_name_loc);					// Column J: supplier_name_loc		
				$sheet->setCellValue('K' . $current_row, $row->supplier_name_eng); 					// Column K: supplier_name_eng		
				$sheet->setCellValue('L' . $current_row, $row->biz_registration_no); 				// Column L: biz_registration_no		
				$sheet->setCellValue('M' . $current_row, $row->domain_type); 						// Column M: domain_type		
				$sheet->setCellValue('N' . $current_row, $row->job_type_id); 						// Column N: job_type_id		
				$sheet->setCellValue('O' . $current_row, $row->job_type_name);						// Column O: job_type_name 
				$sheet->setCellValue('P' . $current_row, $row->trade_type_id); 						// Column P: trade_type_id
				$sheet->setCellValue('Q' . $current_row, $row->trade_type_name); 					// Column Q: trade_type_name
				$sheet->setCellValue('R' . $current_row, $row->currency_code_id); 					// Column R: currency_code_id
				$sheet->setCellValue('S' . $current_row, $row->currency_code_name); 				// Column S: currency_code_name
				$sheet->setCellValue('T' . $current_row, $row->term_days); 							// Column T: term_days
				$sheet->setCellValue('U' . $current_row, $row->payment_terms_name); 				// Column U: payment_terms_name
				$sheet->setCellValue('V' . $current_row, $row->hub_use_flag_id); 					// Column V: hub_use_flag_id
				$sheet->setCellValue('W' . $current_row, $row->hub_use_flag_name); 					// Column W: hub_use_flag_name
				$sheet->setCellValue('X' . $current_row, $row->payterm_type);						// Column X: payterm_type
				$sheet->setCellValue('Y' . $current_row, $row->available_period_from); 				// Column Y: available_period_from
				$sheet->setCellValue('Z' . $current_row, $row->available_period_to); 				// Column Z: available_period_to
				$sheet->setCellValue('AA' . $current_row, $row->settlement_type_id); 				// Column AA: settlement_type_id
				$sheet->setCellValue('AB' . $current_row, $row->settlement_type_name); 				// Column AB: settlement_type_name
				$sheet->setCellValue('AC' . $current_row, $row->due_counted_point_id); 				// Column AC: due_counted_point_id
				$sheet->setCellValue('AD' . $current_row, $row->due_counted_point_name); 			// Column AD: due_counted_point_name
				$sheet->setCellValue('AE' . $current_row, $row->prorate_basis_type_id); 			// Column AE: prorate_basis_type_id
				$sheet->setCellValue('AF' . $current_row, $row->prorate_basis_type_name); 			// Column AF: prorate_basis_type_name
				$sheet->setCellValue('AG' . $current_row, $row->payment_group_id); 					// Column AG: payment_group_id
				$sheet->setCellValue('AH' . $current_row, $row->payment_group_name); 				// Column AH: payment_group_name
				$sheet->setCellValue('AI' . $current_row, $row->payment_method); 					// Column AI: payment_method
				$sheet->setCellValue('AJ' . $current_row, $row->collection_redem_at_sight_l_c); 	// Column AJ: collection_redem_at_sight_l_c
				$sheet->setCellValue('AK' . $current_row, $row->bank_charge_payment_entity_l_c); 	// Column AK: bank_charge_payment_entity_l_c
				$sheet->setCellValue('AL' . $current_row, $row->document_submit_days_l); 			// Column AL: document_submit_days_l
				$sheet->setCellValue('AM' . $current_row, $row->usane_l_c_type); 					// Column AM: usane_l_c_type		
				$sheet->setCellValue('AN' . $current_row, $row->usance_l_c_interest_pay_type); 		// Column AN: usance_l_c_interest_pay_type
				$sheet->setCellValue('AO' . $current_row, $row->usance_l_c_interest_rate); 			// Column AO: usance_l_c_interest_rate
				$sheet->setCellValue('AP' . $current_row, $row->enabled_flag_id); 					// Column AP: enabled_flag_id
				$sheet->setCellValue('AQ' . $current_row, $row->enabled_flag_name); 				// Column AQ: enabled_flag_name
				$sheet->setCellValue('AR' . $current_row, $row->payterm_key); 						// Column AR: payterm_key
				$sheet->setCellValue('AS' . $current_row, $row->creation_date); 					// Column AS: creation_date
				$sheet->setCellValue('AT' . $current_row, $row->creation_user_id); 					// Column AT: creation_user_id
				$sheet->setCellValue('AU' . $current_row, $row->last_update_date); 					// Column AU: last_update_date
				$sheet->setCellValue('AV' . $current_row, $row->last_updated_by); 					// Column AV: last_updated_by
		
                $current_row++;                                  			
            }                                                  		
        }                                                        		
		$filename = "MDMS_" . date('Y-m') . ".xlsx";
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
	
	public function update(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ar_mdms.xlsx',
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
