<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
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
		$g = [
			"eta", 
			"master_bl", 
			"house_bl", 
			"invoice", 
			"carrier_line", 
			"carrier_name", 
			"current_vessel", 
			"shipper", 
			"incoterms", 
			"ctn_size", 
			"container", 
			"company", 
			"division", 
			"product", 
			"transshipment", 
			"transshipment_op", 
			"transshipment_route", 
			"transshipment_loc", 
			"transshipment_vessel", 
			"port_departure", 
			"port_terminal", 
			"atd", 
			"eta_initial", 
			"ata", 
			"picked_up", 
			"wh_arrival", 
			"return_due", 
			"returned", 
		];
		$containers = $this->gen_m->only_multi("lgepr_container", $g, $w, $g);
		
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
		
		$dem_row = [
			"2d"	=> 0,
			"1d"		=> 0,
			"0d"		=> 0,
			"overdue"	=> 0,
			"total"		=> 0,
		];
		
		$demurrage = [
			"Total" => $dem_row,
		];
		
		$det_row = [
			"99d"	=> 0,
			"21d"	=> 0,
			"14d"	=> 0,
			"7d"	=> 0,
			"3d"	=> 0,
			"0d"		=> 0,
			"overdue"	=> 0,
			"total"		=> 0,
		];
		
		$detention = [
			"Total" => $det_row,
		];
		
		
		
		$remind = [
			"dem" => $dem_row,
			"det" => [
				"6_10_days"	=> 0,
				"1_5_day"	=> 0,
				"0_day"	=> 0,
				"overdue"	=> 0,
			],
		];
		
		//$port = "DPW + APM";
		
		foreach($containers as $i => $item){
			$item->dem_range = $item->det_range = "";
			$item->dem_period = date("Y-m", strtotime($item->ata ? $item->ata : $today));
			$item->det_period = date("Y-m", strtotime($item->returned ? $item->returned : $today));
			
			if (($now < strtotime($item->eta) and (!$item->ata))) unset($containers[$i]);
			else{
				if ($item->no_data) $no_data_qty++;
				
				//demurrage
				if (!$item->port_terminal) $item->port_terminal = "_blank";
				
				if (!$item->picked_up){
					if (!array_key_exists($item->port_terminal, $demurrage)) $demurrage[$item->port_terminal] = $dem_row;
						
					$demurrage[$item->port_terminal]["total"]++;
					$demurrage["Total"]["total"]++;
					
					if ($item->dem_days){
						$item->dem_range = "Overdue";
						$demurrage[$item->port_terminal]["overdue"]++;
						$demurrage["Total"]["overdue"]++;
					}else switch($item->dem_reminds){
						case 2: 
							$item->dem_range = "2 days";
							$demurrage[$item->port_terminal]["2d"]++;
							$demurrage["Total"]["2d"]++;
							break;
						case 1:
							$item->dem_range = "1 day";
							$demurrage[$item->port_terminal]["1d"]++;
							$demurrage["Total"]["1d"]++;
							break;
						case 0:
							$item->dem_range = "0 day";
							$demurrage[$item->port_terminal]["0d"]++;
							$demurrage["Total"]["0d"]++;
							break;
					}
				}
				
				//detention remind
				if (!$item->returned){
					if (!$item->carrier_line) $item->carrier_line = "_blank";
					if (!array_key_exists($item->carrier_line, $detention)) $detention[$item->carrier_line] = $det_row;
					
					$detention[$item->carrier_line]["total"]++;
					$detention["Total"]["total"]++;
					
					if ($item->det_days){
						$item->det_range = "Overdue";
						$detention[$item->carrier_line]["overdue"]++;
						$detention["Total"]["overdue"]++;
					}else switch(true){
						case $item->det_reminds == 0: 
							$item->det_range = "0 day";
							$detention[$item->carrier_line]["0d"]++;
							$detention["Total"]["0d"]++;
							break;
						case $item->det_reminds <= 3: 
							$item->det_range = "~3 days";
							$detention[$item->carrier_line]["3d"]++;
							$detention["Total"]["3d"]++;
							break;
						case $item->det_reminds <= 7:
							$item->det_range = "~7 days";
							$detention[$item->carrier_line]["7d"]++;
							$detention["Total"]["7d"]++;
							break;
						case $item->det_reminds <= 14:
							$item->det_range = "~14 days";
							$detention[$item->carrier_line]["14d"]++;
							$detention["Total"]["14d"]++;
							break;
						case $item->det_reminds <= 21:
							$item->det_range = "~21 days";
							$detention[$item->carrier_line]["21d"]++;
							$detention["Total"]["21d"]++;
							break;
						default:
							$item->det_range = "21 days~";
							$detention[$item->carrier_line]["99d"]++;
							$detention["Total"]["99d"]++;
							break;
					}
				}
			}
		}
		
		uasort($detention, function($a, $b) {
			return $b['total'] <=> $a['total'];
		});
		
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		$sheet->setTitle('rawdata');

		$row = 1;

		//header 1
		$sheet->setCellValueByColumnAndRow(1, $row, "Master bl");
		$sheet->setCellValueByColumnAndRow(2, $row, "House bl");
		$sheet->setCellValueByColumnAndRow(3, $row, "Invoice");
		$sheet->setCellValueByColumnAndRow(4, $row, "Carrier line");
		$sheet->setCellValueByColumnAndRow(5, $row, "Carrier name");
		$sheet->setCellValueByColumnAndRow(6, $row, "Current vessel");
		$sheet->setCellValueByColumnAndRow(7, $row, "Shipper");
		$sheet->setCellValueByColumnAndRow(8, $row, "Incoterms");
		$sheet->setCellValueByColumnAndRow(9, $row, "CTN size");
		$sheet->setCellValueByColumnAndRow(10, $row, "Container");
		$sheet->setCellValueByColumnAndRow(11, $row, "Company");
		$sheet->setCellValueByColumnAndRow(12, $row, "Division");
		$sheet->setCellValueByColumnAndRow(13, $row, "Product");
		$sheet->setCellValueByColumnAndRow(14, $row, "Transshipment");
		$sheet->setCellValueByColumnAndRow(15, $row, "T/S optional");
		$sheet->setCellValueByColumnAndRow(16, $row, "T/S route");
		$sheet->setCellValueByColumnAndRow(17, $row, "T/S port");
		$sheet->setCellValueByColumnAndRow(18, $row, "T/S vessel");
		$sheet->setCellValueByColumnAndRow(19, $row, "Port departure");
		$sheet->setCellValueByColumnAndRow(20, $row, "Port terminal");
		$sheet->setCellValueByColumnAndRow(21, $row, "ATD");
		$sheet->setCellValueByColumnAndRow(22, $row, "ETA initial");
		$sheet->setCellValueByColumnAndRow(23, $row, "ETA");
		$sheet->setCellValueByColumnAndRow(24, $row, "ATA");
		$sheet->setCellValueByColumnAndRow(25, $row, "Picked up");
		$sheet->setCellValueByColumnAndRow(26, $row, "Warehouse");
		$sheet->setCellValueByColumnAndRow(27, $row, "Return due");
		$sheet->setCellValueByColumnAndRow(28, $row, "Returned");
		$sheet->setCellValueByColumnAndRow(29, $row, 'Demurrage');
		$sheet->setCellValueByColumnAndRow(32, $row, 'Detention');
		$sheet->setCellValueByColumnAndRow(35, $row, 'Amount (USD)');
		
		//header 2
		$row++;
		$sheet->setCellValueByColumnAndRow(29, $row, 'Range');
		$sheet->setCellValueByColumnAndRow(30, $row, 'Freedays');
		$sheet->setCellValueByColumnAndRow(31, $row, 'Overdays');
		$sheet->setCellValueByColumnAndRow(32, $row, 'Range');
		$sheet->setCellValueByColumnAndRow(33, $row, 'Freedays');
		$sheet->setCellValueByColumnAndRow(34, $row, 'Overdays');
		$sheet->setCellValueByColumnAndRow(35, $row, 'DEM+DET');
		
		//rawdatas
		$row++;
		foreach($containers as $item){
			
			$sheet->setCellValueByColumnAndRow(1, $row, $item->master_bl);
			$sheet->setCellValueByColumnAndRow(2, $row, $item->house_bl);
			$sheet->setCellValueByColumnAndRow(3, $row, $item->invoice);
			$sheet->setCellValueByColumnAndRow(4, $row, $item->carrier_line);
			$sheet->setCellValueByColumnAndRow(5, $row, $item->carrier_name);
			$sheet->setCellValueByColumnAndRow(6, $row, $item->current_vessel);
			$sheet->setCellValueByColumnAndRow(7, $row, $item->shipper);
			$sheet->setCellValueByColumnAndRow(8, $row, $item->incoterms);
			$sheet->setCellValueByColumnAndRow(9, $row, $item->ctn_size);
			$sheet->setCellValueByColumnAndRow(10, $row, $item->container);
			$sheet->setCellValueByColumnAndRow(11, $row, $item->company);
			$sheet->setCellValueByColumnAndRow(12, $row, $item->division);
			$sheet->setCellValueByColumnAndRow(13, $row, $item->product);
			$sheet->setCellValueByColumnAndRow(14, $row, $item->transshipment);
			$sheet->setCellValueByColumnAndRow(15, $row, $item->transshipment_op);
			$sheet->setCellValueByColumnAndRow(16, $row, $item->transshipment_route);
			$sheet->setCellValueByColumnAndRow(17, $row, $item->transshipment_loc);
			$sheet->setCellValueByColumnAndRow(18, $row, $item->transshipment_vessel);
			$sheet->setCellValueByColumnAndRow(19, $row, $item->port_departure);
			$sheet->setCellValueByColumnAndRow(20, $row, $item->port_terminal);
			$sheet->setCellValueByColumnAndRow(21, $row, $item->atd);
			$sheet->setCellValueByColumnAndRow(22, $row, $item->eta_initial);
			$sheet->setCellValueByColumnAndRow(23, $row, $item->eta);
			$sheet->setCellValueByColumnAndRow(24, $row, $item->ata);
			$sheet->setCellValueByColumnAndRow(25, $row, $item->picked_up);
			$sheet->setCellValueByColumnAndRow(26, $row, $item->wh_arrival);
			$sheet->setCellValueByColumnAndRow(27, $row, $item->return_due);
			$sheet->setCellValueByColumnAndRow(28, $row, $item->returned);
			$sheet->setCellValueByColumnAndRow(29, $row, $item->dem_range);
			$sheet->setCellValueByColumnAndRow(30, $row, $item->dem_reminds);
			$sheet->setCellValueByColumnAndRow(31, $row, $item->dem_days);
			$sheet->setCellValueByColumnAndRow(32, $row, $item->det_range);
			$sheet->setCellValueByColumnAndRow(33, $row, $item->det_reminds);
			$sheet->setCellValueByColumnAndRow(34, $row, $item->det_days);
			$sheet->setCellValueByColumnAndRow(35, $row, 180 * ($item->dem_days + $item->det_days));
			
			$row++;
		}
		
		//merge cells
		for ($i = 1; $i <= 28; $i++) {
			$col = Coordinate::stringFromColumnIndex($i); // A ~ AB
			$sheet->mergeCells("{$col}1:{$col}2");
		}
		$sheet->mergeCells('AC1:AE1');
		$sheet->mergeCells('AF1:AH1');
		
		//set width
		for ($colIndex = 1; $colIndex <= 37; $colIndex++) {
			$colLetter = Coordinate::stringFromColumnIndex($colIndex);
			$sheet->getColumnDimension($colLetter)->setWidth(20);
		}
		
		$sheet->getColumnDimension('D')->setWidth(15);
		$sheet->getColumnDimension('H')->setWidth(15);
		$sheet->getColumnDimension('I')->setWidth(15);
		$sheet->getColumnDimension('K')->setWidth(15);
		$sheet->getColumnDimension('L')->setWidth(15);
		$sheet->getColumnDimension('N')->setWidth(15);
		$sheet->getColumnDimension('O')->setWidth(15);
		$sheet->getColumnDimension('P')->setWidth(15);
		$sheet->getColumnDimension('Q')->setWidth(15);
		$sheet->getColumnDimension('S')->setWidth(15);
		$sheet->getColumnDimension('T')->setWidth(15);
		$sheet->getColumnDimension('U')->setWidth(15);
		$sheet->getColumnDimension('V')->setWidth(15);
		$sheet->getColumnDimension('W')->setWidth(15);
		$sheet->getColumnDimension('X')->setWidth(15);
		$sheet->getColumnDimension('Y')->setWidth(15);
		$sheet->getColumnDimension('Z')->setWidth(15);
		$sheet->getColumnDimension('AA')->setWidth(15);
		$sheet->getColumnDimension('AB')->setWidth(15);
		$sheet->getColumnDimension('AK')->setWidth(15);
		$sheet->getColumnDimension('AI')->setWidth(15);
		
		$sheet->getColumnDimension('AC')->setWidth(10);
		$sheet->getColumnDimension('AD')->setWidth(10);
		$sheet->getColumnDimension('AE')->setWidth(10);
		$sheet->getColumnDimension('AF')->setWidth(10);
		$sheet->getColumnDimension('AG')->setWidth(10);
		$sheet->getColumnDimension('AH')->setWidth(10);
		
		//header style
		$range = 'A1:AK2';
		
		//header align center & middle
		$sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

		//header font bold
		$sheet->getStyle($range)->getFont()->setBold(true);
		
		//save file
		$writer = new Xlsx($spreadsheet);
		$filePath = 'report/custom_container_aging_report.xlsx';
		$writer->save($filePath);
		
		$data = [
			"eta_from"		=> $eta_from,
			"eta_to"		=> $eta_to,
			"today"			=> $today,
			"no_data_qty"	=> $no_data_qty,
			"remind"		=> $remind,
			"demurrage"		=> $demurrage,
			"detention"		=> $detention,
		];
		
		$to = [
			/* CFO */ "wonshik.woo@lge.com", "mariela.carbajal@lge.com", "bion.hwang@lge.com", "mario.rosazza@lge.com", "enrique.salazar@lge.com", "angella.castro@lge.com", "melisa.carbajal@lge.com",
			"juan.gonzales@lge.com", "nicolas.nigro@lgepartner.com", "georgio.park@lge.com", "ricardo.alvarez@lge.com",
			/* DP */ "renato.freundt@lge.com", "dario.vargas@lge.com", "wagner.rojas@lge.com", "victorj.sanchez@lge.com", "mauricio.meza@lge.com", 
			/* CEO, PM */ "andre.cho@lge.com", "raul.oh@lge.com", "minaalicia.park@lge.com", "muhyun.han@lge.com", "sanguk.jeong@lge.com", "rony.cortez@lge.com", "seongmin1.lee@lge.com", "patrick.lee@lge.com", "patricia.pandolfi@lge.com", 
		];
		$to = ["georgio.park@lge.com", "nicolas.nigro@lgepartner.com"];
		
		$subject = "[Custom] Container aging auto-report";
		$content = $this->load->view('email/custom_container_aging', $data, true);
		
		echo $this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $content, $filePath);
		echo $content;
		echo "<br/><br/><br/>////////////////////// Aging report sent. ////////////////////// ";
		
		if (file_exists($filePath)) unlink($filePath);
	}
	
}
