<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Utility_func extends CI_Controller {

	public function __construct(){
		parent::__construct();
		//if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');

		set_time_limit(0);
	}
	
	public function cloud_pc_shutdown_email(){
		//llamasys/utility_func/cloud_pc_shutdown_email
		
		$to = "lg-lgepr@lge.com";
		//$to = "georgio.park@lge.com";
		
		$subject = "[Comunicado PI] Apague Cloud PC antes de retirar de la oficina !!!";
		$content = $this->load->view('email/cloud_pc_shutdown_email', null, true);
		
		echo $this->my_func->send_email("comunicacionpi@lge.com", $to, $subject, $content);
	}
	
	public function container_aging_report(){
		//llamasys/utility_func/container_aging_report
		
		$no_data_qty = 0;
		$now = time();
		$today = date("Y-m-d");
		$summary = [];
		
		$eta_from = $this->input->get("eta_from"); if (!$eta_from) $eta_from = date('Y-m-01', strtotime('-2 months'));
		$eta_to = $this->input->get("eta_to"); if (!$eta_to) $eta_to = date("Y-m-t");
		
		$w = ["eta >=" => $eta_from, "eta <=" => $eta_to,];
		$o = [["eta", "desc"], ["sa_no", "asc"], ["sa_line_no", "asc"], ["container", "asc"]];
		$g = ["eta", "carrier_line", "container", "company", "division", "ata", "picked_up", "wh_arrival", "return_due", "returned"];
		$containers = $this->gen_m->only_multi("custom_container", $g, $w, $g);
		
		foreach($containers as $item){
			$is_no_data = false;
			$item->dem_reminds = $item->det_reminds = $item->dem_days = $item->det_days = $item->no_data = 0;
			
			if ($item->ata and $item->picked_up){
				$days = $this->my_func->day_counter($item->ata, $item->picked_up) - 1;
				if ($days > 2){
					$item->dem_days = $days - 2;
				}
			}else{
				$ata = $item->ata ? $item->ata : $item->eta;
				$picked_up = $item->picked_up ? $item->picked_up : $today;

				if (strtotime($ata) <= strtotime($picked_up)){
					$days = $this->my_func->day_counter($ata, $picked_up) - 1;
					if ($days > 2){
						$item->dem_days = $days - 2;
					}else $item->dem_reminds = 2 - $this->my_func->day_counter($ata, $today) + 1;
				}
				
				$is_no_data = true;
			}
			
			if ($item->returned and $item->return_due){
				if (strtotime($item->return_due) < strtotime($item->returned)){
					$item->det_days = $this->my_func->day_counter($item->returned, $item->return_due) - 1;
				}
			}else{
				$returned = $item->returned ? $item->returned : $today;
				$return_due = $item->return_due ? $item->return_due : date('Y-m-d', strtotime('+25 days', strtotime($item->eta)));
				
				if (strtotime($return_due) < strtotime($returned)){
					$item->det_days = $this->my_func->day_counter($returned, $return_due) - 1;
				}else $item->det_reminds = $this->my_func->day_counter($returned, $return_due) - 1;
				
				$is_no_data = true;
			}
			
			if ($is_no_data) $item->no_data = true;
			
			$summary[] = clone $item;
		}
		
		//$containers = $this->set_containers($containers);
		$containers = array_reverse($summary);
		
		$remind = [
			"dem" => [
				"2_days"	=> 0,
				"1_day"		=> 0,
				"0_day"		=> 0,
				"issuing"	=> 0,
			],
			"det" => [
				"6_10_days"	=> 0,
				"1_5_day"	=> 0,
				"0_day"	=> 0,
				"issuing"	=> 0,
			],
		];
		
		$issued = [
			date('Y-m', strtotime('-2 months')) => ["dem" => ["qty" => 0, "days" => 0, "amount" => 0], "det" => ["qty" => 0, "days" => 0, "amount" => 0]],
			date('Y-m', strtotime('-1 months')) => ["dem" => ["qty" => 0, "days" => 0, "amount" => 0], "det" => ["qty" => 0, "days" => 0, "amount" => 0]],
			date('Y-m') 						=> ["dem" => ["qty" => 0, "days" => 0, "amount" => 0], "det" => ["qty" => 0, "days" => 0, "amount" => 0]],
		];
		
		foreach($containers as $i => $item){
			
			$item->dem_period = date("Y-m", strtotime($item->ata ? $item->ata : $today));
			$item->det_period = date("Y-m", strtotime($item->returned ? $item->returned : $today));
			
			if (($now < strtotime($item->eta) and (!$item->ata))) unset($containers[$i]);
			else{
				if ($item->no_data) $no_data_qty++;
				
				//demurrage remind
				if (!$item->ata){
					if ($item->dem_days) $remind["dem"]["issuing"]++;
					else switch($item->dem_reminds){
						case 2: $remind["dem"]["2_days"]++; break;
						case 1: $remind["dem"]["1_day"]++; break;
						case 0: $remind["dem"]["0_day"]++; break;
					}
				}
				
				//detention remind
				if (!$item->returned){
					if ($item->det_days) $remind["det"]["issuing"]++;
					else switch(true){
						case $item->det_reminds == 0: $remind["det"]["0_day"]++; break;
						case $item->det_reminds <= 5: $remind["det"]["1_5_day"]++; break;
						case $item->det_reminds <= 10: $remind["det"]["6_10_days"]++; break;
					}
				}
				
				//demurrage issued
				if ($item->dem_days > 0){
					if (!array_key_exists($item->dem_period, $issued)) $issued[$item->dem_period] = ["dem" => ["qty" => 0, "days" => 0, "amount" => 0], "det" => ["qty" => 0, "days" => 0, "amount" => 0]];
					
					$issued[$item->dem_period]["dem"]["qty"]++;
					$issued[$item->dem_period]["dem"]["days"] += $item->dem_days;
					$issued[$item->dem_period]["dem"]["amount"] += $item->dem_days * 180;
				}
				
				//detention issued
				if ($item->det_days > 0){
					//echo $item->det_period." "; print_r($item); echo "<br/>";
					if (!array_key_exists($item->det_period, $issued)) $issued[$item->det_period] = ["dem" => ["qty" => 0, "days" => 0, "amount" => 0], "det" => ["qty" => 0, "days" => 0, "amount" => 0]];
					
					$issued[$item->det_period]["det"]["qty"]++;
					$issued[$item->det_period]["det"]["days"] += $item->det_days;
					$issued[$item->det_period]["det"]["amount"] += $item->det_days * 180;
				}
			}
		}
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$row = 1;

		//header
		$sheet->setCellValue('A'.$row, 'Company');
		$sheet->setCellValue('B'.$row, 'Division');
		$sheet->setCellValue('C'.$row, 'Container');
		$sheet->setCellValue('D'.$row, 'ETA');
		$sheet->setCellValue('E'.$row, 'ATA');
		$sheet->setCellValue('F'.$row, 'Picked up');
		$sheet->setCellValue('G'.$row, 'Warehouse');
		$sheet->setCellValue('H'.$row, 'Returned');
		$sheet->setCellValue('I'.$row, 'Return due');
		$sheet->setCellValue('J'.$row, 'DEM remind');
		$sheet->setCellValue('K'.$row, 'DEM days');
		$sheet->setCellValue('L'.$row, 'DET remind');
		$sheet->setCellValue('M'.$row, 'DET days');
		$sheet->setCellValue('N'.$row, 'Total Amount');

		$row++;

		//rows
		foreach($containers as $item){
			$sheet->setCellValue('A'.$row, $item->company);
			$sheet->setCellValue('B'.$row, $item->division);
			$sheet->setCellValue('C'.$row, $item->container);
			$sheet->setCellValue('D'.$row, $item->eta);
			$sheet->setCellValue('E'.$row, $item->ata);
			$sheet->setCellValue('F'.$row, $item->picked_up);
			$sheet->setCellValue('G'.$row, $item->wh_arrival);
			$sheet->setCellValue('H'.$row, $item->returned);
			$sheet->setCellValue('I'.$row, $item->return_due);
			$sheet->setCellValue('J'.$row, $item->dem_reminds);
			$sheet->setCellValue('K'.$row, $item->dem_days);
			$sheet->setCellValue('L'.$row, $item->det_reminds);
			$sheet->setCellValue('M'.$row, $item->det_days);
			$sheet->setCellValue('N'.$row, 180 * ($item->dem_days + $item->det_days));
			
			$row++;
		}

		// 파일 저장
		$writer = new Xlsx($spreadsheet);
		$filePath = 'report/custom_container_aging_report.xlsx';
		$writer->save($filePath);
		
		$data = [
			"eta_from"		=> $eta_from,
			"eta_to"		=> $eta_to,
			"today"			=> $today,
			"no_data_qty"	=> $no_data_qty,
			"remind"		=> $remind,
			"issued"		=> $issued,
			"containers"	=> $containers,
		];
		
		$to = ["georgio.park@lge.com"];
		
		$subject = "[Custom] Container aging auto-report.";
		$content = $this->load->view('email/custom_container_aging', $data, true);
		
		echo $this->my_func->send_email("georgio.park@lge.com", $to, $subject, $content, $filePath);
		echo "Aging report sent.";
	}
	
}
