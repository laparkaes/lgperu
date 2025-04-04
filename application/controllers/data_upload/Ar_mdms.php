<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

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
	
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);

		//delete all rows lgepr_stock 
		$this->gen_m->truncate("ar_mdms");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/ar_mdms.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);
		//print_r($sheet); echo '<br>'; echo '<br>'; echo '<br>'; return;
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
		//print_r($h);
		// //magento report header
		$header = ["Class ID", "Master ID", "BP Code", "BP Group[ID]", "BP Group[NAME]", "Partner Type[ID]", "Partner Type[NAME]", "Country[ID]"];
		
		// //header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

		
		
		if ($is_ok){
			// Obtener datos desde la fila 6 en adelante en un solo paso
			//$dataArray = $sheet->toArray(null, true, true, true);
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 1000;
			// Procesar datos desde la fila 6 en adelante
			// for ($i = 6; $i < 100; $i++) {
				// if (!isset($dataArray[$i])) {
					// continue;
				// }
			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 6; $i <= $max_row; $i++){
				$row = [
					"class_id" 										=> trim($sheet->getCell('A'.$i)->getValue()),
					"master_id" 									=> trim($sheet->getCell('B'.$i)->getValue()),
					"bp_code" 										=> trim($sheet->getCell('C'.$i)->getValue()),
					"bp_group_id" 									=> trim($sheet->getCell('D'.$i)->getValue()),
					"bp_group_name" 								=> trim($sheet->getCell('E'.$i)->getCalculatedValue()),
					"partner_type_id"								=> trim($sheet->getCell('F'.$i)->getValue()),
					"partner_type_name" 							=> trim($sheet->getCell('G'.$i)->getCalculatedValue()),
					"country_id" 									=> trim($sheet->getCell('H'.$i)->getValue()),
					"country_name"									=> trim($sheet->getCell('I'.$i)->getValue()),
					"non_vat_no_id"									=> trim($sheet->getCell('J'.$i)->getValue()),
					"non_vat_no_name"								=> trim($sheet->getCell('K'.$i)->getCalculatedValue()),
					"corporation_registration_number"				=> trim($sheet->getCell('L'.$i)->getValue()),
					"hq_vat_biz_registration_no"					=> trim($sheet->getCell('M'.$i)->getValue()),		
					"name_local"									=> trim($sheet->getCell('N'.$i)->getValue()),
					"name_eng"										=> trim($sheet->getCell('O'.$i)->getValue()),
					"trading_partner_affiliate_branch_id"			=> trim($sheet->getCell('P'.$i)->getValue()),
					"short_name_local"								=> trim($sheet->getCell('Q'.$i)->getValue()),
					"trading_partner_affiliate_branch_name"			=> trim($sheet->getCell('R'.$i)->getValue()),
					"type_of_business"								=> trim($sheet->getCell('S'.$i)->getValue()),
					"tel_country_number"							=> trim($sheet->getCell('T'.$i)->getValue()),
					"business_registration_certificate"				=> trim($sheet->getCell('U'.$i)->getValue()),
					"biz_registration_no"							=> trim($sheet->getCell('V'.$i)->getValue()),
					"sub_biz_reg_no_branch_code"					=> trim($sheet->getCell('W'.$i)->getValue()),
					"address_zipcode"								=> trim($sheet->getCell('X'.$i)->getValue()),
					"address_seq"									=> trim($sheet->getCell('Y'.$i)->getValue()),
					"address_countrycode"							=> trim($sheet->getCell('Z'.$i)->getValue()),
					"address_road_addr1"							=> trim($sheet->getCell('AA'.$i)->getValue()),
					"address_road_addr2"							=> trim($sheet->getCell('AB'.$i)->getValue()),
					"address_road_addr3"							=> trim($sheet->getCell('AC'.$i)->getValue()),
					"address_road_addr4"							=> trim($sheet->getCell('AD'.$i)->getValue()),
					"address_global_addr1"							=> trim($sheet->getCell('AE'.$i)->getValue()),
					"address_global_addr2"							=> trim($sheet->getCell('AF'.$i)->getValue()),
					"address_global_addr3"							=> trim($sheet->getCell('AG'.$i)->getValue()),
					"address_global_addr4"							=> trim($sheet->getCell('AH'.$i)->getValue()),
					"city"											=> trim($sheet->getCell('AI'.$i)->getValue()),
					"state_id"										=> trim($sheet->getCell('AJ'.$i)->getValue()),
					"state_name"									=> trim($sheet->getCell('AK'.$i)->getValue()),
					"local_full_address"							=> trim($sheet->getCell('AL'.$i)->getValue()),	
					"county"										=> trim($sheet->getCell('AM'.$i)->getValue()),
					"tax_payer_id"									=> trim($sheet->getCell('AN'.$i)->getValue()),
					"kpp_taxpayer_registration_reason_code"			=> trim($sheet->getCell('AO'.$i)->getValue()),
					"branch_no"										=> trim($sheet->getCell('AP'.$i)->getValue()),
					"supplier_status_id"							=> trim($sheet->getCell('AQ'.$i)->getValue()),
					"supplier_status_name"							=> trim($sheet->getCell('AR'.$i)->getCalculatedValue()),
					"nations_in_electronic_lg_id"					=> trim($sheet->getCell('AS'.$i)->getValue()),
					"nations_in_electronic_lg_name"					=> trim($sheet->getCell('AT'.$i)->getValue()),
					"lg_business_factory_id"						=> trim($sheet->getCell('AU'.$i)->getValue()),
					"lg_business_factory_name"						=> trim($sheet->getCell('AV'.$i)->getValue()),
					"biz_register_no_type_id"						=> trim($sheet->getCell('AW'.$i)->getValue()),
					"biz_register_no_type_name"						=> trim($sheet->getCell('AX'.$i)->getCalculatedValue()),
					"business_form"									=> trim($sheet->getCell('AY'.$i)->getValue()),
					"start_date_active"								=> trim($sheet->getCell('AZ'.$i)->getValue()),
					"end_date_active"								=> trim($sheet->getCell('BA'.$i)->getValue()) ?: NULL,
					"status_id"										=> trim($sheet->getCell('BB'.$i)->getValue()),
					"status_name"									=> trim($sheet->getCell('BC'.$i)->getCalculatedValue()),
					"updated"										=> $updated,
				];
				
				// Manejo de valores vacios end_date_ative
					
				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("ar_mdms", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
				//$this->gen_m->insert("ar_mdms", $row);
				//$this->update_model_category();
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				//print_r($batch_data); echo '<br>'; echo '<br>'; echo '<br>';
				$this->gen_m->insert_m("ar_mdms", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			//print_r($msg); return;
			$this->db->trans_complete();
			return $msg;
			//$this->update_model_category();
			//return "Stock update has been finished. (".$updated.")";
			
		}else return "";
		//$this->update_model_category();
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
