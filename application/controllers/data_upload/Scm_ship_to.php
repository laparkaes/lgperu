<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Scm_ship_to extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){

		$tracking = $this->gen_m->filter("scm_ship_to2", false);
		$data = [
			"tracking"		=> $tracking,
			"count_tracking" => count($tracking),
			"main" 			=> "data_upload/scm_ship_to/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function process($filename = "scm_ship_to.xlsx", $debug = false){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		
		$data = $this->gen_m->filter_select('Scm_ship_to2', false, ['ship_to_key']);

		$list_key = [];
		foreach ($data as $item) $list_key[] = $item->ship_to_key;
		$list_key = array_filter($list_key);

		//$this->gen_m->truncate("scm_tracking_dispatch");
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows_og = [];
		$rows_req = [];
		$ship_list = [];
		$updated = date("Y-m-d H:i:s");
		$max_row = $sheet->getHighestRow();
		for($i = 3; $i <= $max_row; $i++){
			$aux = explode(' ', $sheet->getCell('C'.$i)->getValue(), 2);
			//echo '<pre>'; print_r($aux);
			$row = [
				"bill_to_name" 				=> trim($sheet->getCell('A'.$i)->getValue()) ?? '',
				"ship_to_name" 				=> trim($sheet->getCell('B'.$i)->getValue()) ?? null,
				"bill_to_code"				=> trim($sheet->getCell('AJ'.$i)->getValue()) ?? null,				
				"ship_to_code" 				=> trim($sheet->getCell('AL'.$i)->getValue()) ?? null,			
				"ship_to_full_name" 		=> trim($sheet->getCell('AM'.$i)->getValue()) ?? null,
				"updated"					=> $updated,
			];
			
			$row['ship_to_key'] = $row['bill_to_name'] . "_" . $row['bill_to_code'];
			
			//echo '<pre>'; print_r($row);
			$rows[$row['ship_to_key']] = $row;
			// if (in_array($row['ship_to_key'], $list_key)) {
				// $rows_req[$row['ship_to_key']] = $row;
			// }
			// else{
				// // if (!isset($ship_list[$row['ship_to_key']])){
					// // $ship_list[$row['ship_to_key']] = $row;
				// // }
				// $rows_og[$row['ship_to_key']] = $row;
			// }			
		}
		
		//foreach ($data_og as $item) $
		
		foreach ($rows as $index => $item) $ship_list[] = $item;
		
		foreach ($ship_list as $item) {
			if (in_array($item['ship_to_key'], $list_key)) $rows_req[] = $item;
			else $rows_og[] = $item;				
		}
		//if (in_array($item['ship_to_key'], $rows)) $rows_req[] = ;
		//echo '<pre>'; print_r($rows_req);
		$rows_split_eq = array_chunk($rows_req, 50);
		foreach($rows_split_eq as $items) $this->gen_m->update_multi("scm_ship_to2", $items, 'ship_to_key');
		
		$rows_split = array_chunk($rows_og, 50);
		foreach($rows_split as $items) $this->gen_m->insert_m('scm_ship_to2', $items);
		
		return "Stock update has been finished. (".$updated.")";
	}
	
	public function process_ship_to ($filename = "scm_ship_to.xlsx", $debug = false) {
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		
		$data = $this->gen_m->filter_select('scm_ship_to2', false, ['ship_to_key']);

		$list_key = [];
		foreach ($data as $item) $list_key[] = $item->ship_to_key;
		$list_key = array_filter($list_key);

		//$this->gen_m->truncate("scm_tracking_dispatch");
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		$rows_og = [];
		$rows_req = [];
		$ship_list = [];
		$updated = date("Y-m-d H:i:s");
		$max_row = $sheet->getHighestRow();
		for($i = 3; $i <= $max_row; $i++){
			$aux = explode(' ', $sheet->getCell('C'.$i)->getValue(), 2);
			//echo '<pre>'; print_r($aux);
			$row = [
				"bill_to_code" 				=> trim($sheet->getCell('H'.$i)->getValue()) ?? null,
				"bill_to_name" 				=> trim($sheet->getCell('I'.$i)->getValue()) ?? null,			
				"ship_to_code" 				=> trim($sheet->getCell('J'.$i)->getValue()) ?? null,			
				"address" 					=> trim($sheet->getCell('N'.$i)->getValue()) ?? null,
				"warehouse"					=> trim($sheet->getCell('R'.$i)->getValue()) ?? null,
				//"shipping_remark"			=> trim($sheet->getCell('AS'.$i)->getValue()) ?? null,
				"updated"					=> $updated,
			];
			
			$row['address'] = rtrim($row['address'], ",");
			// $aux_shipping = explode('//', $row['shipping_remark'], 2);
			// $row['shipping_remark'] = $aux_shipping[0];
			$row['ship_to_key'] = $row['bill_to_code'] . "_" . $row['ship_to_code'];
			
			//echo '<pre>'; print_r($row);
			$rows[$row['ship_to_key']] = $row;			
		}
		
		//foreach ($data_og as $item) $
		
		foreach ($rows as $index => $item) $ship_list[] = $item;
		
		foreach ($ship_list as $item) {
			if (in_array($item['ship_to_key'], $list_key)) $rows_req[] = $item;
			else $rows_og[] = $item;				
		}
		//if (in_array($item['ship_to_key'], $rows)) $rows_req[] = ;
		//echo '<pre>'; print_r($rows_req);
		$rows_split_eq = array_chunk($rows_req, 50);
		foreach($rows_split_eq as $items) $this->gen_m->update_multi("scm_ship_to2", $items, 'ship_to_key');
		
		$rows_split = array_chunk($rows_og, 50);
		foreach($rows_split as $items) $this->gen_m->insert_m('scm_ship_to2', $items);
		
		return "Stock update has been finished. (".$updated.")";
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
				'file_name'		=> 'scm_ship_to.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process_ship_to();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
}
