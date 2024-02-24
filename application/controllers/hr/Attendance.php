<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Attendance extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->load->model('subsidiary_model', 'sub_m');
		$this->load->model('organization_model', 'org_m');
		$this->load->model('employee_model', 'emp_m');
		$this->load->model('vacation_model', 'vac_m');
		$this->load->model('office_model', 'off_m');
		$this->load->model('working_hour_model', 'whour_m');
		$this->load->model('attendance_model', 'att_m');
		$this->nav_menu = ["hr", "attendance"];
	}

	public function index(){
		$headers = [];//save days and week days for table header
		$employees = $this->emp_m->all([["name", "asc"]]);
		
		
		$month = date("F");
		
		$start = new DateTime(date('Y-m-01'));
		$last = new DateTime(date('Y-m-t'));
		
		$interval = new DateInterval('P1D');
		$now = clone $start;
		while ($now <= $last) {
			$headers[] = ["day" => $now->format('d'), "w_day" => $now->format('D')];
			$date = $now->format('Y-m-d');
			
			//echo $now->format('Y-m-d D') . "<br>";//요일 전체 표시하려면 l
			
			$now->add($interval);
		}
		
		print_r($headers);
		
		$data = [
			"main" => "hr/attendance/index",
		];
		//$this->load->view('layout', $data);
	}
	
	public function upload_device_file(){
		$type = "error"; $msg = null;
		
		$config = [
			'upload_path'	=> './upload/',
			'allowed_types'	=> 'xls|xlsx',
			'max_size'		=> 10000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'attendance',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('md_uff_file')){
			$data = $this->upload->data();
			$file_path = $data['full_path'];

			$spreadsheet = IOFactory::load($file_path);
			$sheet = $spreadsheet->getActiveSheet();
			
			$sheet->setCellValue('B1', 'Upload Result');
			$sheet->getStyle('B')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('B')->getFill()->getStartColor()->setARGB('FFFF00');
			
			$sheet->setCellValue('C1', 'Upload Time');
			$sheet->getStyle('C')->getFill()->setFillType(Fill::FILL_SOLID);
			$sheet->getStyle('C')->getFill()->getStartColor()->setARGB('FFFF00');
			
            $highestRow = $sheet->getHighestRow();
            //$highestColumn = $sheet->getHighestColumn();

			$new_rec = $upd_rec = [];
			$atts = [];
			$count = 0;
            for ($row = 2; $row <= $highestRow; $row++){
				$row_now = explode(",", trim($sheet->getCell('A'.$row)->getValue()));
				if ($row_now[5]){
					$aux = explode("(", str_replace(")", "", $row_now[5]));
					if (array_key_exists(1, $aux)){
						$date_split = explode(" ", $row_now[0]);
						
						/*
						$row_now[0]: check date
						$date_split[0]: check day
						$date_split[1]: check time
						$aux[0]: employee_number
						$aux[1]: name
						*/
						
						if (!array_key_exists($aux[0], $atts))
							$atts[$aux[0]] = ["name" => "", "check" => []];
						
						if (!array_key_exists($date_split[0], $atts[$aux[0]]["check"])) 
							$atts[$aux[0]]["check"][$date_split[0]] = [];
						
						$atts[$aux[0]]["check"][$date_split[0]][] = $date_split[1];
						$atts[$aux[0]]["name"] = $aux[1];
					}
				}
            }
			
			foreach($atts as $emp_num => $emp){
				$emp_rec = $this->emp_m->unique("employee_number", $emp_num);
				if (!$emp_rec){
					$this->emp_m->insert(["employee_number" => $emp_num, "name" => $emp["name"]]);
					$emp_rec = $this->emp_m->unique("employee_number", $emp_num);
				}
				
				$checks = $emp["check"];
				foreach($checks as $day => $times){
					if ($times){
						sort($times);
						
						$f = ["employee_id" => $emp_rec->employee_id, "date" => $day];
						$att_data = [
							"employee_id" => $emp_rec->employee_id,
							"date" => $day,
							"enter_time" => $times[0],
							"leave_time" => $times[count($times) - 1],
						];
						
						$att_rec = $this->gen_m->filter("attendance", true, $f);
						//echo $this->db->last_query();
						if ($att_rec){
							$att_data["attendance_id"] = $att_rec[0]->attendance_id;
							$upd_rec[] = $att_data;
						}else $new_rec[] = $att_data;
					}
				}
			}
			
			$new_qty = ($new_rec) ? $this->att_m->insert_m($new_rec) : 0;
			$upd_qty = ($upd_rec) ? $this->att_m->update_m($upd_rec) : 0;
			
			$writer = new Xlsx($spreadsheet);
			$writer->save($file_path);
			
			$type = "success";
			$msg = "Check-in time upload result: ".number_format($new_qty)." new and ".number_format($upd_qty)." updated.";
		}else{
			$error = array('error' => $this->upload->display_errors());
			$msg = str_replace("p>", "div>", $this->upload->display_errors());
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
