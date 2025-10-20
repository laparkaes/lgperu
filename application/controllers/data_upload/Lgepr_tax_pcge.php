<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Lgepr_tax_pcge extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"pcge"	=> $this->gen_m->filter("lgepr_tax_pcge", false, null, null, null, "", 1000),
			"main" 		=> "data_upload/lgepr_tax_pcge/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function export_excel(){
		
		$data = $this->gen_m->filter('lgepr_tax_pcge', false);
		$template_path = './template/lgepr_tax_pcge_template.xlsx';
		
		try {
            $spreadsheet = IOFactory::load($template_path);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('Error loading the Excel template: ' . $e->getMessage());
        }
		$sheet = $spreadsheet->getActiveSheet();
		
		$start_row = 3;
        $current_row = $start_row;
		
        if ($data) {
            foreach ($data as $row) {            
                $sheet->setCellValue('A' . $current_row, $row->from_period); 			// Column A: From Period                              
                $sheet->setCellValue('B' . $current_row, $row->to_period); 				// Column B: To Period                          
                $sheet->setCellValue('C' . $current_row, $row->accounting_unit); 		// Column C: Accounting Unit
				$sheet->setCellValue('D' . $current_row, $row->accounting_unit_desc); 	// Column D: Accounting Unit Desc
				$sheet->setCellValue('E' . $current_row, $row->account); 				// Column E: Account
				$sheet->setCellValue('F' . $current_row, $row->account_desc); 			// Column F: Account Desc
				$sheet->setCellValue('G' . $current_row, $row->pcge); 					// Column G: PCGE
				$sheet->setCellValue('H' . $current_row, $row->pcge_decripcion); 		// Column H: PCGE Description

                $current_row++;
            }
        }
		
		$filename = "PCGE_" . date('Y-m') . ".xlsx";
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
	
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//delete all rows lgepr_tax_pcge
		$this->gen_m->truncate("lgepr_tax_pcge");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/lgepr_tax_pcge.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

		//excel file header validation
		$h = [
			trim($sheet->getCell('A2')->getValue()),
			trim($sheet->getCell('B2')->getValue()),
			trim($sheet->getCell('C2')->getValue()),
			trim($sheet->getCell('D2')->getValue()),
			trim($sheet->getCell('E2')->getValue()),
			trim($sheet->getCell('F2')->getValue()),
			trim($sheet->getCell('G2')->getValue()),
			trim($sheet->getCell('H2')->getValue()),
		];
		
		$header = ["From Period", "To Period", "Accounting Unit", "Accounting Unit Desc", "Account", "Account Desc", "PCGE", "PCGE Decripción"];

		// //header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;		
		
		if ($is_ok){
			$updated = date("Y-m-d");
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 200;

			for($i = 3; $i <= $max_row; $i++){
				$row = [
					"from_period" 				=> trim($sheet->getCell('A'.$i)->getValue()),
					"to_period" 				=> trim($sheet->getCell('B'.$i)->getValue()),
					"accounting_unit" 			=> trim($sheet->getCell('C'.$i)->getValue()),
					"accounting_unit_desc" 		=> trim($sheet->getCell('D'.$i)->getValue()),
					"account" 					=> trim($sheet->getCell('E'.$i)->getValue()),
					"account_desc" 				=> trim($sheet->getCell('F'.$i)->getValue()),
					"pcge"						=> trim($sheet->getCell('G'.$i)->getValue()),
					"pcge_decripcion"			=> trim($sheet->getCell('H'.$i)->getValue()),
					"updated"					=> $updated,
				];
				
				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("lgepr_tax_pcge", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("lgepr_tax_pcge", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			//$this->db->trans_complete();
			return $msg;			
		}else return "";
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
				'file_name'		=> 'lgepr_tax_pcge.xlsx',
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
