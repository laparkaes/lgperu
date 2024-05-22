<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Espr_file extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}

	public function index(){
		$data = [
			"main" => "scm/espr_file/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function set_coi($filename, $sheetname, $filename_coi){
		$header = ["Category","AU","Bill To Name","Ship To Name","Model","Order Qty","Unit List  Price","Unit Selling  Price","Total Amount (PEN)","Total Amount","Order Amount (PEN)","Order Amount","Line Charge Amount","Header Charge Amount","Tax Amount","DC Amount","DC Rate","Currency","Book Currency","Inventory Org.","Sub- Inventory","Sales Person","Pricing Group","Buying Group","Territory","Customer Code","Customer Name","Customer Department","Product Level1 Name","Product Level2 Name","Product Level3 Name","Product Level4 Name","Model Category","Item Type Desctiption","Item Weight","Item CBM","Order Date","Shipment Date","LT Days","Closed Date","AAI Flag","HQ AU","Bill To Code","Ship To Code","Ship To Country","Ship To City","Ship To  State","Ship To Zip Code","Payment Term","Sales Channel","Order Source","Order Type","Order No.","Line No.","Line  Type","Invoice No.","Customer PO No.","Project Code","Comm. Submission No.","Product Level4","Price Grade","Consumer Name","Receiver Name","Receiver Country","Receiver Postal Code","Receiver City","Receiver State","Receiver Province","Receiver Address1","Receiver Address2","Receiver Address3","Install Store Code","Install Type","Install Date","Fapiao No.","Fapiao Date","CNPJ","Nota Date","ACD W/H Code","ACD W/H Type","Net Price","Interest Amt","Original List Pirce","PLP  Submission No","Price Condition","Nota Fiscal Serie No","Shipping Method"];
		
		$spreadsheet = IOFactory::load($filename);
		$sheet = $spreadsheet->getSheetByName($sheetname);
		
		$spreadsheet_coi = IOFactory::load($filename_coi);
		$sheet_coi = $spreadsheet_coi->getActiveSheet();
		
		$max_row = $sheet_coi->getHighestRow();
		$max_col = $sheet_coi->getHighestColumn();
		
		//load header and compare to continue work
		$rows = $sheet_coi->rangeToArray("A1:{$max_col}1")[0];
		if ($this->my_func->header_compare($header, $rows)){
			//header work
			$rows = array_merge($rows, ["Column1", "", "Fixed Order Date", "Fixed Ship Date", "", "Fixed Closed Date"]);
			foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), 1)->setValue($val);
			
			//index of array to remove commas
			$nums = [5,6,7,8,9,10,11,12,13,14,15,82];
			
			for($row = 2; $row <= $max_row; $row++){
				$rows = $sheet_coi->rangeToArray("A{$row}:{$max_col}{$row}")[0];
				
				//dates convert to 20240422 format
				$rows = array_merge($rows, ["", "", str_replace("-", "", $this->my_func->date_convert($rows[36])), str_replace("-", "", $this->my_func->date_convert($rows[37])), "", str_replace("-", "", $this->my_func->date_convert($rows[39]))]);
				
				//remove commas of numbers
				foreach($nums as $n) $rows[$n] = str_replace(",", "", $rows[$n]);
				
				//write to merged file
				foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), $row)->setValue($val);
			}
		}else $sheet->getCellByColumnAndRow(1, 1)->setValue("Wrong file.");
		
		$writer = new Xlsx($spreadsheet);
		$writer->save("./upload/scm_so_espr.xlsx");
	}
	
	public function set_soi($filename, $sheetname, $filename_coi){
		$header = ["Bill To Name","Ship To Name","Model","Order No.","Line No.","Order Type","Line Status","Hold Flag","Ready To Pick","Pick Released","Instock Flag","Order Qty","Unit Selling Price","Sales Amount","Tax Amount","Charge Amount","Line Total","List Price","Original List Price","DC Rate","Currency","DFI Applicable","AAI Applicable","Cancel Qty","Booked Date","Scheduled Cancel Date","Cancel Date","Expire Date","Req. Arrival Date From","Req. Arrival Date To","Req. Ship Date","Shipment Date","Close Date","Line Type","Customer Name","Bill To","Department","Ship To","Ship To Full Name","Store No","Price Condition","Payment Term","Customer PO No.","Customer Po Date","Invoice No.","Invoice Line No.","Invoice Date","Sales Person","Pricing Group","Buying Group","Territory Code","Inventory Org.","Sub- Inventory","Shipping Method","Shipment Priority","Order Source","Order Status","Order Category","Quote Date","Quote Expire Date","Project Code","Comm. Submission No.","PLP Submission No.","BPM Request No.","Consumer Name","Consumer Phone No","Consumer Mobile NO","Receiver Name","Receiver Phone No","Receiver Mobile NO","Receiver Address1","Receiver Address2","Receiver Address3","Receiver City","Receiver City Desc","Receiver County","Receiver Postal code","Receiver State","Receiver Province","Receiver Country","Item Division","PL1 Name","PL2 Name","PL3 Name","PL4 Name","Product Level4 Code","Model Category","Item Type","Item Weight","Item CBM","Sales Channel (High)","Sales Channel (Low)","Ship Group","Back Order Hold","Credit Hold","Overdue Hold","Customer Hold","Payterm Term Hold","FP Hold","Minimum Hold","Future Hold","Reserve Hold","Manual Hold","Auto Pending Hold","S/A Hold","Form Hold","Bank Collateral Hold","Insurance Hold","Partial Flag","Load Hold Flag","Inventory Reserved","Pick Release Qty","Long & Multi Flag","SO-SA Mapping","Picking Remark","Shipping Remark","Create Employee Name","Create Date","Order Date","Expected Arrival Date","Fixed Arrival Date","DLS Interface","Sales Recognition Method","Billing Type","LT DAY","EDI Customer Remark","Carrier Code","Delivery Number","Manifest/ GRN No","Warehouse Job No","Customer RAD","Others Out Reason","Ship Set Name","Promising Txn Status","Promised MAD","Promised Arrival Date","Appointment Date","Promised Ship Date","Initial Promised Arrival Date","Accounting Unit","RAD Unmeet Reason","Install Type","Install Date","ACD Original Warehouse","ACD Original W/H Type","Customer Model","Customer Model Desc","CNPJ","Nota No","Nota Date","Net Price","Interest Amt","SO Status(2)","Back Order Reason","SBP Tax Include","SBP Tax Exclude","RRP Tax Include","RRP Tax Exclude","SO FAP Flag","SO FAP Slot Date","Model  Profit Level","APMS NO","Scheduled Back Date","Customer PO Type","","Revised RSD","Revised RAD From","Revised RAD To","Pick Cancel Manual Hold",];

		$spreadsheet = IOFactory::load($filename);
		$sheet = $spreadsheet->getSheetByName($sheetname);
		
		$spreadsheet_soi = IOFactory::load($filename_coi);
		$sheet_soi = $spreadsheet_soi->getActiveSheet();
		
		$max_row = $sheet_soi->getHighestRow();
		$max_col = $sheet_soi->getHighestColumn();
		
		$rows = $sheet_soi->rangeToArray("A1:{$max_col}1")[0];
		if ($this->my_func->header_compare($header, $rows)){
			//header work
			$rows = array_merge($rows, ["Column1", "Fixed PO Date", "Fixed Create Date", "Fixed RAD Date", "Fixed Ship Date"]);
			foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), 1)->setValue($val);
			
			//index of array to remove commas
			$nums = [11,12,13,14,15,16,17,18,23,154,155,156,157];
			
			for($row = 2; $row <= $max_row; $row++){
				$rows = $sheet_soi->rangeToArray("A{$row}:{$max_col}{$row}")[0];
				
				//dates convert to 20240422 format
				$rows = array_merge($rows, ["", str_replace("-", "", $this->my_func->date_convert($rows[43])), str_replace("-", "", $this->my_func->date_convert($rows[117])), str_replace("-", "", $this->my_func->date_convert_2($rows[130])), str_replace("-", "", $this->my_func->date_convert($rows[31]))]);
				
				//remove commas of numbers
				foreach($nums as $n) $rows[$n] = str_replace(",", "", $rows[$n]);
				
				//write to merged file
				foreach($rows as $i => $val) $sheet->getCellByColumnAndRow(($i + 1), $row)->setValue($val);
			}
		}else $sheet->getCellByColumnAndRow(1, 1)->setValue("Wrong file.");
		
		$writer = new Xlsx($spreadsheet);
		$writer->save("./upload/scm_so_espr.xlsx");
	}
	
	public function merge_order_inquiry(){
		$type = "error"; $msg = $url = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
			$start_time = microtime(true);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
			];
			$this->load->library('upload', $config);

			$name_coi = $name_soi1 = $name_soi2 = "";
			
			$config['file_name'] = 'scm_so_coi';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_coi')){
				$data = $this->upload->data();
				$name_coi = $data['orig_name'];
			}
			
			$config['file_name'] = 'scm_so_soi1';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_soi1')){
				$data = $this->upload->data();
				$name_soi1 = $data['orig_name'];
			}
			
			$config['file_name'] = 'scm_so_soi2';
			$this->upload->initialize($config);
			if ($this->upload->do_upload('file_soi2')){
				$data = $this->upload->data();
				$name_soi2 = $data['orig_name'];
			}
			
			if ($name_coi and $name_soi1 and $name_soi2){
				//filepath
				$filepath = "./upload/scm_so_espr.xlsx";
				
				//start to create excel file
				$spreadsheet = new Spreadsheet();
				$spreadsheet->removeSheetByIndex(0);
				
				//worksheets setting
				$spreadsheet->addSheet(new Worksheet($spreadsheet, 'Closed'));
				$spreadsheet->addSheet(new Worksheet($spreadsheet, 'SOI 1'));
				$spreadsheet->addSheet(new Worksheet($spreadsheet, 'SOI 2'));
				
				$writer = new Xlsx($spreadsheet);
				$writer->save($filepath);
				
				//copy COI content to excel file
				$this->set_coi($filepath, 'Closed', './upload/'.$name_coi);
				
				//copy SOI 1 content to excel file
				$this->set_soi($filepath, 'SOI 1', './upload/'.$name_soi1);
				
				//copy SOI 2 content to excel file
				$this->set_soi($filepath, 'SOI 2', './upload/'.$name_soi2);
				
				$type = "success";
				$msg = "Merged file download will be started. (".number_format(microtime(true) - $start_time, 2)." sec)";
				$url = base_url()."upload/scm_so_espr.xlsx";
			}else $msg = "Select all files: COI, SOI 1 and SOI 2.";
		}else{
			$msg = "Your session is finished.";
			$url = base_url();
		}
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
}
