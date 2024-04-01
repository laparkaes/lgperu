<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class My_func{
	
	public function __construct(){
		$this->CI = &get_instance();
	}
	
	public function set_page($page, $qty){
		$pages = [];
		if ($qty){
			$last = floor($qty / 30); if ($qty % 30) $last++;
			if (3 < $page) $pages[] = [1, "<<", "outline-primary"];
			if (3 < $page) $pages[] = [$page-3, "...", "outline-primary"];
			if (2 < $page) $pages[] = [$page-2, $page-2, "outline-primary"];
			if (1 < $page) $pages[] = [$page-1, $page-1, "outline-primary"];
			$pages[] = [$page, $page, "primary"];
			if ($page+1 <= $last) $pages[] = [$page+1, $page+1, "outline-primary"];
			if ($page+2 <= $last) $pages[] = [$page+2, $page+2, "outline-primary"];
			if ($page+3 <= $last) $pages[] = [$page+3, "...", "outline-primary"];
			if ($page+3 <= $last) $pages[] = [$last, ">>", "outline-primary"];
		}
		
		return $pages;
	}
	
	public function day_counter($start, $end){
		$date1 = new DateTime($start);
		$date2 = new DateTime($end);

		$interval = $date1->diff($date2);
		return $interval->days;
	}
	
	public function generate_excel_report($filename, $title, $header, $rows){
		$url = "";
		
		if ($rows){
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			
			//set report parameters
			$sheet->setCellValueByColumnAndRow(1, 1, $title);
			$sheet->setCellValueByColumnAndRow(1, 2, "Date");
			$sheet->setCellValueByColumnAndRow(2, 2, date('Y-m-d H:i:s'));
			
			//set header
			foreach($header as $i => $h) $sheet->setCellValueByColumnAndRow(($i + 1), 4, $h);
			
			//set rows
			$row_from = 5;
			foreach($rows as $j => $row) foreach($row as $i => $r) $sheet->setCellValueByColumnAndRow(($i + 1), $row_from + $j, $r);
			
			//save excel file to a temporary directory
			$file_path = './upload/report/';
			$writer = new Xlsx($spreadsheet);
			$writer->save($file_path.$filename);
			
			//file url
			$url = base_url()."upload/report/".$filename;	
		}
		
		return $url;
	}
}