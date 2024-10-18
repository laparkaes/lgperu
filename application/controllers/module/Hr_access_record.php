<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Hr_access_record extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"main" => "module/Hr_access_record/index", 
		];
		
		$this->load->view('layout', $data);
	}
	
	public function upload_access(){
		$type = "error"; $msg = null; $inserted = 0;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'hr_attendance',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
			/*
			$sheet->setCellValue('B1', 'Upload Result');
			$sheet->getStyle('B')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('B')->getFill()->getStartColor()->setARGB('FFFF00');
			
			$sheet->setCellValue('C1', 'Upload Time');
			$sheet->getStyle('C')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('C')->getFill()->getStartColor()->setARGB('FFFF00');
			*/
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();

			$rows = [];
            for ($j = 2; $j <= $highestRow; $j++){
				//datas are separated in columns
				$row = [
					trim($sheet->getCell('A'.$j)->getValue()),
					trim($sheet->getCell('B'.$j)->getValue()),
					trim($sheet->getCell('C'.$j)->getValue()),
					trim($sheet->getCell('D'.$j)->getValue()),
					trim($sheet->getCell('E'.$j)->getValue()),
					trim($sheet->getCell('F'.$j)->getValue()),
					trim($sheet->getCell('G'.$j)->getValue()),
				];
				
				if ($row[5]){
					$aux = explode("(", str_replace(")", "", $row[5]));
					//print_r($aux); echo "<br/>";
					
					$rows[] = [
						"pr" => $aux[0],
						"name" => (array_key_exists(1, $aux) ? $aux[1] : ""),
						"access" => $row[0],
					];
					
				}
            }
			
			if ($rows){
				$this->gen_m->delete("hr_attendance", ["access >=" => $rows[count($rows)-1]["access"], "access <=" => $rows[0]["access"]]);
				$inserted = $this->gen_m->insert_m("hr_attendance", $rows); 
			}
			
			$type = "success";
			$msg = number_format($inserted)." rows inserted.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	public function upload_schedule(){
		$type = "error"; $msg = null; $inserted = 0;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx|csv',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'hr_schedule',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('attach')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();

			$rows = [];
            for ($j = 2; $j <= $highestRow; $j++){
				//load a row from excel
				$schedule = explode(" - ", trim($sheet->getCell('C'.$j)->getValue()));
				
				$row = [
					"pr" => trim($sheet->getCell('A'.$j)->getValue()),
					"name" => trim($sheet->getCell('B'.$j)->getValue()),
					"date_from" => date("Y-m-d", strtotime(trim($sheet->getCell('D'.$j)->getFormattedValue()))),
				];
				
				$row["work_start"] = $schedule[0];
				$row["work_end"] = $schedule[1];
				
				if (!$this->gen_m->filter("hr_schedule", false, $row)) $this->gen_m->insert("hr_schedule", $row);
            }
			
			$type = "success";
			$msg = "Employees work schedule has been updated.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
