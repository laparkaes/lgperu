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
		
		$w = ["rma_no" => $row["rma_no"], "rma_line_no" => $row["rma_line_no"]];
		if ($this->gen_m->filter("scm_goodset_return", false, $w)) $this->gen_m->update("scm_goodset_return", $w, $row);
		
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
				
				//print_r($row); echo "<br/><br/>";
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
	
}
