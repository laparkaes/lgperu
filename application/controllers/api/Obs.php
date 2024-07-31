<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
		$this->divisions = ["HA", "HE", "BS"];
		$this->division_map = [
			"HA" => ["REF", "COOK", "W/M", "CDT", "RAC", "SAC", "A/C"],
			"HE" => ["TV", "AV"],
			"BS" => ["MNT", "PC", "DS", "SGN", "CTV"],
		];
		$this->division_map_inv = [];
		foreach($this->division_map as $div => $divisions) foreach($divisions as $cat) $this->division_map_inv[$cat] = $div;
		
		$this->categories = ["REF", "COOK", "W/M", "CDT", "A/C", "RAC", "SAC", "TV", "AV", "MNT", "PC", "DS", "SGN", "CTV"];
		$this->category_map = [
			"REF" => ["REF"],
			"COOK" => ["MWO", "O", "CVT"],
			"W/M" => ["W/M"],
			"CDT" => ["CDT"],
			"A/C" => ["A/C"],
			"RAC" => ["RAC"],
			"SAC" => ["SAC"],
			"TV" => ["LCD", "LTV"],
			"AV" => ["AUD", "CAV"],
			"MNT" => ["MNT"],
			"PC" => ["PC"],
			"DS" => ["DS"],
			"SGN" => ["SGN"],
			"CTV" => ["CTV"],
		];
		$this->category_map_inv = [];
		foreach($this->category_map as $cat => $categories) foreach($categories as $c) $this->category_map_inv[$c] = $cat;
		
		$this->dash_company = ["HA" => "H&A", "HE" => "HE", "BS" => "BS"];
		$this->dash_division = [
			"REF" => "REF", 
			"COOK" => "Cooking", 
			"W/M" => "W/M", 
			"CDT" => "CDT", 
			"A/C" => "Chiller", 
			"RAC" => "RAC", 
			"SAC" => "SAC", 
			"TV" => "LTV", 
			"AV" => "AV", 
			"MNT" => "MNT", 
			"PC" => "PC", 
			"DS" => "DS", 
			"SGN" => "Signage", 
			"CTV" => "Commercial TV",
		];
	}
	
	public function view_maker_obs_sales(){
		/* 
		stdClass Object ( [sales_order_id] => 15484 [bill_to_name] => B2B2C [ship_to_name] => B2B2C [model] => WT21VV6.ASSGLGP [order_no] => 1000447875 [line_no] => 1.1 [order_type] => REGULAR_OMD_PR [line_status] => Booked [hold_flag] => Y [ready_to_pick] => N [pick_released] => N [instock_flag] => Y [ordered_qty] => 1 [unit_selling_price] => 1770.38 [sales_amount] => 1770.38 [tax_amount] => 318.67 [charge_amount] => 0 [line_total] => 2089.05 [list_price] => 1770.38 [original_list_price] => 1770.38 [dc_rate] => 0 [currency] => PEN [dfi_applicable] => Y [aai_applicable] => Y [cancel_qty] => [booked_date] => 2024-07-08 [scheduled_cancel_date] => [expire_date] => [req_arrival_date_from] => 2024-07-09 [req_arrival_date_to] => 2024-07-09 [req_ship_date] => 2024-07-08 [shipment_date] => [close_date] => [line_type] => REGULAR_M_OMD_PR_L [customer_name] => One time_OBS [bill_to] => PE008292002B [customer_department] => LGEPR [ship_to] => B2B2C-S [store_no] => [price_condition] => [payment_term] => N0010FSN1708 [customer_po_no] => ORDER_129001042836 [customer_po_date] => 2024-07-08 [invoice_no] => [invoice_line_no] => [invoice_date] => [sales_person] => PINILLOS SEMINARIO, JUAN IGNACIO [pricing_group] => [buying_group] => [inventory_org] => N4E [sub_inventory] => GS-ESALE [shipping_method] => 000001_APM_T_DOM_SITE [shipment_priority] => Standard [order_source] => OMD_LGOBS [order_status] => Booked [order_category] => ORDER [quote_date] => [quote_expire_date] => [project_code] => PR22C00000000 [comm_submission_no] => [plp_submission_no] => [bpm_request_no] => [consumer_name] => Nilo Alex Chirinos Leon [receiver_name] => Nilo Alex Chirinos Leon [consumer_phone_no] => [consumermobile_no] => [receiver_phone_no] => [receiver_mobile_no] => [receiver_address1] => [receiver_address2] => [receiver_address3] => [receiver_city] => Lima/Lima/Villa El Salvador [receiver_city_desc] => Lima/Lima/Villa El Salvador [receiver_county] => [receiver_postal_code] => 0142 [receiver_state] => 15 [receiver_province] => [receiver_country] => PE [item_division] => DFZ [product_level1_name] => Washing Machine [product_level2_name] => Clothes Washer [product_level3_name] => Clothes Washer_Top Loader [product_level4_name] => Clothes Washer_Turbo Drum [product_level4_code] => WMWLTLTA [model_category] => W/M [item_type_desctiption] => Merchandise [item_weight] => 52.57 [item_cbm] => 0.57673 [sales_channel_high] => Default Parent Sales Channel [sales_channel_low] => OBS [ship_group] => [back_order_hold] => N [credit_hold] => N [overdue_hold] => Y [customer_hold] => Y [payterm_term_hold] => N [fp_hold] => N [minimum_hold] => N [future_hold] => N [reserve_hold] => N [manual_hold] => N [auto_pending_hold] => N [sa_hold] => N [form_hold] => N [bank_collateral_hold] => N [insurance_hold] => N [partial_flag] => Y [load_hold_flag] => [inventory_reserved] => N [pick_release_qty] => [long_multi_flag] => [so_sa_mapping] => N [picking_remark] => [shipping_remark] => 10234795 Nilo Alex Chirinos Leon [create_employee_name] => GERP SYSTEM USER, [create_date] => 2024-07-08 [dls_interface] => [edi_customer_remark] => [sales_recognition_method] => [billing_type] => [lt_day] => [carrier_code] => [delivery_number] => [manifest_grn_no] => [warehouse_job_no] => [customer_rad] => 2024-07-09 00:00:00 [others_out_reason] => [ship_set_name] => [promising_txn_status] => [promised_mad] => [promised_arrival_date] => [promised_ship_date] => [initial_promised_arrival_date] => [accounting_unit] => [acd_original_warehouse] => 0 [acd_original_wh_type] => [cnjp] => [nota_no] => One time_OBS [nota_date] => [so_status2] => [sbp_tax_include] => 0 [sbp_tax_exclude] => 2089.05 [rrp_tax_include] => 1770.38 [rrp_tax_exclude] => 2849 [so_fap_flag] => 2 [so_fap_slot_date] => 0000-00-00 )
		
		stdClass Object ( 
			[bill_to] => PE008292002B 
			[bill_to_name] => B2B2C 
			[order_no] => 1000447875 
			[line_no] => 1.1 
			[line_status] => Booked 
			[order_status] => Booked 
			[order_category] => ORDER 
			[model_category] => W/M 
			[model] => WT21VV6.ASSGLGP 
			[ordered_qty] => 1 
			[currency] => PEN 
			[unit_selling_price] => 1770.38 
			[sales_amount] => 1770.38 
			[tax_amount] => 318.67 
			[charge_amount] => 0 
			[line_total] => 2089.05 
			[create_date] => 2024-07-08 
			[booked_date] => 2024-07-08
			[req_arrival_date_to] => 2024-07-09 
			[shipment_date] => 
			[close_date] => 
			[receiver_city] => Lima/Lima/Villa El Salvador 
			[item_type_desctiption] => Merchandise 
			[item_division] => DFZ 
			[product_level1_name] => Washing Machine 
			[product_level2_name] => Clothes Washer 
			[product_level3_name] => Clothes Washer_Top Loader 
			[product_level4_name] => Clothes Washer_Turbo Drum 
			[customer_department] => LGEPR 
			[inventory_org] => N4E 
		*/
		
		/*
		$status = $this->gen_m->only("obs_gerp_sales_order", "line_status");
		foreach($status as $item){
			echo $item->line_status;
			echo "<br/>";
		}
		echo "<br/><br/>";
		*/
		
		$s = ["bill_to", "bill_to_name", "order_no", "line_no", "line_status", "order_status", "order_category", "model_category", "model", "ordered_qty", "currency", "unit_selling_price", "sales_amount", "tax_amount", "charge_amount", "line_total", "create_date", "booked_date", "req_arrival_date_to", "shipment_date", "close_date", "receiver_city", "item_type_desctiption", "item_division", "product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name", "customer_department", "inventory_org"];
		$w = ["line_status !=" => "Cancelled", "inventory_org" => "N4E"];
		$l = [];
		$w_in = [];
		$orders = [["create_date", "desc"], ["order_no", "desc"], ["line_no", "asc"]];
		
		$sales = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s, $w, $l, $w_in, $orders);
		
		echo $this->db->last_query();
		echo "<br/><br/>";
		
		echo count($sales)."<br/><br/>";
		
		foreach($sales as $item){
			print_r($item);
			echo "<br/><br/>";
		}
	}
	
	public function view_maker_obs_magento(){
		/* 
		stdClass Object ( [obs_magento_id] => 1 [magento_id] => 129001028516 [grand_total_base] => 2049 [grand_total_purchased] => 2049 [shipping_address] => pasaje tayco 161 huaman,Trujillo,La Libertad,130111 [shipping_and_handling] => 0 [customer_name] => Clara Quezada Fernandez [sku] => PE.GT37SGP.APZGLPR [level_1_code] => PE.GT37SGP.APZGLPR: RF [level_2_code] => PE.GT37SGP.APZGLPR: RFTM [level_3_code] => PE.GT37SGP.APZGLPR: RFTMTM [level_4_code] => PE.GT37SGP.APZGLPR: RFTMTMLA [gerp_type] => omd [gerp_order_no] => 1000444191 [warehouse_code] => N4E-GS-ESALE [sku_price] => 2,049.00 [local_time] => 2024-04-30 20:17:00 [company_name_through_vipkey] => [vipkey] => [pre_order] => [error_code] => [price_source] => [coupon_code] => [coupon_rule] => [discount_amount] => 0 [devices] => PC [knout_status] => Accept [status] => complete [customer_group] => NOT LOGGED IN [payment_method] => mercadopago_global_credit_card [error_status] => [opt_in_status] => [purchase_date] => 2024-05-01 02:17:00 [gerp_selling_price] => PE.GT37SGP.APZGLPR: 1736.440000 [ip_address] => 173.222.250.173 [sale_channel] => Magento [is_export_order_to_gerp] => 1 [sku_without_prefix] => GT37SGP.APZGLPR [sku_without_prefix_and_suffix] => GT37SGP [qty_ordered] => 1 [zipcode] => 130111 [department] => La Libertad [province] => Trujillo [updated] => 2024-06-25 12:25:09 [registered] => 2024-06-03 17:17:26 ) 
		
		stdClass Object ( 
			[magento_id] => 129001028516 
			[customer_name] => Clara Quezada Fernandez 
			[gerp_order_no] => 1000444191 
			[local_time] => 2024-04-30 20:17:00 
			[company_name_through_vipkey] => 
			[vipkey] => 
			[coupon_code] => 
			[coupon_rule] => 
			[devices] => PC 
			[status] => complete 
			[customer_group] => NOT LOGGED IN 
			[payment_method] => mercadopago_global_credit_card 
			[ip_address] => 173.222.250.173 
			[sale_channel] => Magento 
			[department] => La Libertad 
			[province] => Trujillo 
		*/
		
		$s = ["magento_id as purchase_no", "gerp_order_no", "local_time", "company_name_through_vipkey", "vipkey", "coupon_code", "coupon_rule", "devices", "customer_group", "payment_method", "ip_address", "sale_channel", "department", "province","customer_name"];
		$w = [];//"gerp_order_no >" => 0
		$l = [];
		$w_in = [];
		$orders = [["local_time", "desc"]];
		
		$magentos = $this->gen_m->filter_select("obs_magento", false, $s, $w, $l, $w_in, $orders);
		
		echo $this->db->last_query();
		echo "<br/><br/>";
		
		echo count($magentos)."<br/><br/>";
		
		foreach($magentos as $item){
			print_r($item);
			echo "<br/><br/>";
		}
	}
	
	public function test(){
		$today = date("Y-m-d");
		
		//$from_sales = date("Y-01-01");
		$from_sales = date("Y-m-01", strtotime("-1 year", strtotime($today)));
		$from_magento = date("Y-m-01", strtotime("-1 month", strtotime($from_sales)));
		
		//set magento list by key = [gerp order no]
		$magentos_list = [];
		$magentos_list["structure"] = $this->gen_m->structure("v_obs_magento");
		$magentos = $this->gen_m->filter("v_obs_magento", false, ["local_time >= " => $from_magento." 00:00:00", "gerp_order_no >" => 0]);//echo count($magentos)."<br/><br/>";
		foreach($magentos as $item) $magentos_list[$item->gerp_order_no] = $item;
		
		$f_dates = [];
		
		$sales = $this->gen_m->filter("v_obs_sales_order", false, ["create_date >= " => $from_sales]);//echo count($sales)."<br/><br/>";
		foreach($sales as $item){
			if (array_key_exists($item->order_no, $magentos_list)) $magento_aux = $magentos_list[$item->order_no];
			else{
				$magento_aux = $magentos_list["structure"];
				$magento_aux->local_time = $item->create_date." 00:00:00";
			}
			
			foreach($magento_aux as $key => $val) $item->$key = $val;
			
			$f_dates[] = $item->create_date;
			
			print_r($item); echo "<br/><br/>";
		}
		
		$f_dates = array_unique($f_dates); sort($f_dates);
		print_r($f_dates); echo "<br/><br/>";
		
	}
	
	/*
	public function get_exchange_rate(){
		//llamasys/local_api/get_exchange_rate?key=lgepr
		
		$key = $this->input->get("key");
		$res = [];
		
		if ($key === "lgepr") $res = ["exchange_rate_ttm" => round($this->my_func->get_exchange_rate_month_ttm(date("Y-m-d")), 2)];
		else $res = ["msg" => "Key error."];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function sales(){
		//llamasys/local_api/get_obs_sales?key=lgepr
		
		if ($this->input->get("key") === "lgepr") $res = ["gerp_iods" => $this->my_func->get_gerp_iod(date("Y-m-01"), date("Y-m-t"))];
		else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function divisions(){
		//llamasys/local_api/get_division?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["division" => "H&A", "order" => "a"], 
				["division" => "HE", "order" => "b"], 
				["division" => "BS", "order" => "c"],
			];
		}
		else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function categories(){
		//llamasys/local_api/get_category?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$res = [
				["division" => "H&A", "categry" => "REF", "order" => "a"], 
				["division" => "H&A", "categry" => "Cooking", "order" => "b"], 
				["division" => "H&A", "categry" => "W/M", "order" => "c"], 
				["division" => "H&A", "categry" => "RAC", "order" => "d"], 
				["division" => "H&A", "categry" => "SAC", "order" => "e"], 
				["division" => "H&A", "categry" => "Chiller", "order" => "f"], 
				["division" => "HE", "categry" => "TV", "order" => "g"], 
				["division" => "HE", "categry" => "AV", "order" => "h"],
				["division" => "BS", "categry" => "MNT", "order" => "i"], 
				["division" => "BS", "categry" => "Signage", "order" => "j"], 
				["division" => "BS", "categry" => "Commercial TV", "order" => "k"],
			];
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

	public function get_obs_ml_month(){
		//llamasys/local_api/get_obs_ml_month?key=lgepr
		
		if ($this->input->get("key") === "lgepr"){
			$d = date("Y-m-d");
			
			$mls = $this->gen_m->filter("obs_most_likely", false, ["year" => date("Y", strtotime($d)), "month" => date("m", strtotime($d)), "category !=" => null]);
			
			$res = ["mls" => $mls];
		}else $res = ["msg" => "Error"];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}

	*/
}
