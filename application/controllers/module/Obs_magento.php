<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Obs_magento extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$data = [
			"sales_updated"	=> $this->gen_m->filter("obs_magento", false, null, null, null, [["updated", "desc"]], 1, 0)[0],
			"sales_first"	=> $this->gen_m->filter("obs_magento", false, null, null, null, [["local_time", "asc"]], 1, 0)[0],
			"sales_last" 	=> $this->gen_m->filter("obs_magento", false, null, null, null, [["local_time", "desc"]], 1, 0)[0],
			"main" 			=> "module/obs_magento/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	private function process($filename = "obs_magento.csv"){
		set_time_limit(0);
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/".$filename);
		$sheet = $spreadsheet->getActiveSheet();
		
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue()),
			trim($sheet->getCell('D1')->getValue()),
			trim($sheet->getCell('E1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue()),
			trim($sheet->getCell('J1')->getValue()),
			trim($sheet->getCell('K1')->getValue()),
			trim($sheet->getCell('L1')->getValue()),
			trim($sheet->getCell('M1')->getValue()),
		];
		
		//magento report header
		//$h_magento = ["ID", "Grand Total (Base)", "Grand Total (Purchased)", "Shipping Address", "Shipping and Handling", "Customer name", "SKU", "Level 1 Code", "Level 2 Code", "Level 3 Code", "Level 4 Code", "GERP Type", "GERP Order #"];
		$h_magento = [
			"ID", 
			"Grand Total (Base)", 
			"Grand Total (Purchased)", 
			"Shipping Address", 
			"Shipping and Handling", 
			"Customer name", 
			"SKU", 
			"Level 1 Code", 
			"Level 2 Code", 
			"Level 3 Code", 
			"Level 4 Code", 
			"GERP Type", 
			"GERP Order #", 
		];

		//header validation
		$is_magento = true;
		foreach($h as $i => $h_i) if ($h_i !== $h_magento[$i]) $is_magento = false;
		
		$result = [];
		
		if ($is_magento){
			$max_row = $sheet->getHighestRow();
			
			//result types
			$qty_insert = $qty_update = $qty_fail = 0;
			
			//define now
			$now = date('Y-m-d H:i:s', time());
			
			//db fields
			$vars = [
				"magento_id", 
				"grand_total_base", 
				"grand_total_purchased", 
				"shipping_address", 
				"shipping_and_handling", 
				"customer_name", 
				"sku", 
				"level_1_code", 
				"level_2_code", 
				"level_3_code", 
				"level_4_code", 
				"gerp_type", 
				"gerp_order_no", 
				"warehouse_code", 
				"sku_price", 
				"gerp_selling_price", 
				"local_time", 
				"company_name_through_vipkey", 
				"vipkey", 
				"pre_order", 
				"error_code", 
				"ip_address", 
				"price_source", 
				"coupon_code", 
				"coupon_rule", 
				"discount_amount", 
				"sale_channel", 
				"devices", 
				"knout_status", 
				"status", 
				"customer_group", 
				"payment_method", 
				"error_status", 
				"opt_in_status", 
				"is_export_order_to_gerp", 
				"purchase_date", 
				"sku_without_prefix", 
				"sku_without_prefix_and_suffix", 
				"qty_ordered", 
			];
			
			for($i = 2; $i < $max_row; $i++){
				$row = [];
				foreach($vars as $var_i => $var) $row[$var] = str_replace("N/A", null, $sheet->getCellByColumnAndRow(($var_i + 1), $i)->getValue());
				
				//unique gerp_order_no
				$row["gerp_order_no"] = explode("\n", $row["gerp_order_no"])[0];
				
				//line change char working
				$row["sku"] = str_replace(", \n", "**", $row["sku"]);
				$row["warehouse_code"] = str_replace("\n", "**", $row["warehouse_code"]);
				$row["sku_price"] = str_replace("\n", "**", $row["sku_price"]);
				$row["sku_without_prefix"] = str_replace("\n", "**", $row["sku_without_prefix"]);
				$row["sku_without_prefix_and_suffix"] = str_replace("\n", "**", $row["sku_without_prefix_and_suffix"]);
				
				//comma working
				$row["gerp_selling_price"] = str_replace(",", "**", $row["gerp_selling_price"]);
				
				//address working
				$address_aux = explode(",", $row["shipping_address"]);
				$row["zipcode"] = $address_aux[count($address_aux)-1];
				$row["department"] = $address_aux[count($address_aux)-2];
				$row["province"] = $address_aux[count($address_aux)-3];
				
				$magento = $this->gen_m->unique("obs_magento", "magento_id", $row["magento_id"], false);
				if ($magento){
					$row["updated"] = $now;
					
					if ($this->gen_m->update("obs_magento", ["obs_magento_id" => $magento->obs_magento_id], $row)) $qty_update++;
					else $qty_fail++;
				}else{
					$row["registered"] = $row["updated"] = $now;
					
					if ($this->gen_m->insert("obs_magento", $row)) $qty_insert++;
					else $qty_fail++;
				}
				
				//item processing
				/*
				if ($row){
					$aux_lvl = [];
					if ($row["level_1_code"]) $aux_lvl[] = $row["level_1_code"];
					if ($row["level_2_code"]) $aux_lvl[] = $row["level_2_code"];
					if ($row["level_3_code"]) $aux_lvl[] = $row["level_3_code"];
					if ($row["level_4_code"]) $aux_lvl[] = $row["level_4_code"];
					
					//echo $row["magento_id"]."<br/><br/><br/>";
					//print_r($aux_lvl[0]); echo "<br/><br/><br/>";
					
					$aux_lvl_code = explode(",", $aux_lvl[0]);//PE.LP1419IVSM.SSR0: AC,PE.VM182C9.NKR1: AC,PE.VM182C9.SSR1: AC,PE.VM182C9.USR1: AC,PE.VR182H9.NKR1: AC,PE.VR182H9.SSR1: AC,PE.VR182H9.USR1: AC
					$aux_sku_price = explode("**", $row["sku_price"]);//4,049.10**0.00**0.00**1,979.10**5,218.20**0.00**0.00
					
					//print_r($row["level_3_code"]); echo "<br/><br/><br/>";
					//print_r($aux_sku_price); echo "<br/><br/><br/>";
					
					$to = count($aux_lvl_code);
					for($aux_i = 0; $aux_i < $to; $aux_i++){
						$div_lvl_code = explode(": ", $aux_lvl_code[$aux_i]);//[0]: sku, [1]: model_category
						
						$item = [
							"magento_id"		=> $row["magento_id"],
							"status"			=> $row["status"],
							"local_time"		=> $row["local_time"],
							"model_category"	=> substr($div_lvl_code[1], 0, 2),
							"sku" 				=> str_replace("PE.", "", $div_lvl_code[0]),
							"amount"			=> str_replace(",", "", $aux_sku_price[$aux_i]),
						];
						
						//print_r($item); echo "<br/><br/><br/>";
						
						$item_rec = $this->gen_m->filter("obs_magento_item", false, ["magento_id" => $item["magento_id"], "sku" => $item["sku"]]);
						//print_r($item_rec); echo "<br/>======================<br/><br/>";
						if ($item_rec) $this->gen_m->update("obs_magento_item", ["obs_magento_item_id" => $item_rec[0]->obs_magento_item_id], $item);
						else $this->gen_m->insert("obs_magento_item", $item);
						
						//print_r($item); echo "<br/><br/><br/>";
					}
					
					//$row["obs_magento_id"]
					//$row["status"]
					//$row["local_time"]
					//$row["level_1_code"]
					//$row["gerp_selling_price"] => IMPORTANT!!! data no match because of discount and zero sku price values => individual order qty imposible to calculate
					
				}
				*/
				//print_r($row); 
				//foreach($row as $key => $r) echo $key." >>>> ".json_encode($r)."<br/>";
				//echo "<br/>======================<br/><br/>";
				
				//if ($i > 50) break;
			}
			
			if ($qty_insert > 0) $result[] = number_format($qty_insert)." inserted";
			if ($qty_update > 0) $result[] = number_format($qty_update)." updated";
			if ($qty_fail > 0) $result[] = number_format($qty_fail)." failed";
		}
		
		return $result ? "OBS magento report process result:<br/><br/>".implode(",", $result) : null;
	}
	
	public function test(){
		echo $this->process();
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
			$start_time = microtime(true);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 10000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'obs_magento.csv',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process();
				if ($msg){
					$type = "success";
					$msg = "OBS Mangento data has been uploaded.";
				}else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
