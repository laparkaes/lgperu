<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
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
	
	private function filter_maker($v_name, $field, $mapping = null){
		//array to save items
		$list = [];
		
		//load unique values from DB and save in list
		$records = $this->gen_m->only($v_name, $field);
		foreach($records as $item) $list[] = $item->$field;
		
		$list = array_filter($list);
		
		//print_r($list); echo "<br/>";
		
		//change values if mapping exists
		if ($mapping) foreach($list as $i => $item) $list[$i] = $mapping[$item];
		
		//unique items
		$list = array_filter(array_unique($list));
		
		//sort list asc
		sort($list);
		
		return $list;
	}
	
	public function dashboard_debug(){
		//dates definition
		$today = date("Y-m-d");
		
		$from_sales = date("Y-m-01", strtotime("-1 year", strtotime($today)));
		$from_magento = date("Y-m-01", strtotime("-1 month", strtotime($from_sales)));
		
		//filter datas
		$f_year = $f_month = $f_date = [];
		
		$dates = $this->gen_m->only("v_obs_sales_order", "create_date", ["create_date >= " => $from_sales]);
		foreach($dates as $item){
			$f_year[] = date("Y", strtotime($item->create_date));
			$f_month[] = date("Y-m", strtotime($item->create_date));
			$f_date[] = $item->create_date;
		}
		
		$f_year = array_unique($f_year);
		$f_month = array_unique($f_month);
		$f_date = array_unique($f_date);
		
		sort($f_year);
		sort($f_month);
		sort($f_date);
		
		//mapping datas
		$m_bill_to_name = [
			"B2B2C" => "D2B2C",
			"B2C" => "D2C",
			"B2E" => "D2E",
			"B2P" => "D2P",
			"One time_Boleta" => "D2C",
		];
		
		//( [0] => Awaiting Fulfillment [1] => Awaiting Return [2] => Awaiting Shipping [3] => Booked [4] => Closed [5] => Pending pre-billing acceptance )
		$m_line_status = [
			"Awaiting Fulfillment" => "Booked",
			"Awaiting Return" => "Returning",
			"Awaiting Shipping" => "Shipping",
			"Booked" => "Booked",
			"Closed" => "Closed",
			"Pending pre-billing acceptance" => "Billing",
		];
		
		//( [1] => A/C [2] => AUD [3] => CAV [4] => CTV [5] => CVT [6] => LCD [7] => LTV [8] => MNT [9] => MWO [10] => O [11] => PC [12] => RAC [13] => REF [14] => SAC [15] => SGN [16] => W/M ) + DS
		$m_division = [
			"" => "",//PTO case
			"A/C" => "Chilller",
			"AUD" => "Audio",
			"CAV" => "Audio",
			"CTV" => "Commercial TV",
			"CVT" => "Cooking",
			"DS" => "DS",
			"LCD" => "LTV",
			"LTV" => "LTV",
			"MNT" => "MNT",
			"MWO" => "Cooking",
			"O" => "Cooking",
			"PC" => "PC",
			"RAC" => "RAC",
			"REF" => "REF",
			"SAC" => "SAC",
			"SGN" => "MNT Signage",
			"W/M" => "W/M",
		];
		
		$m_company = [
			"" => "",//PTO case
			"REF" => "H&A",
			"Cooking" => "H&A",
			"W/M" => "H&A",
			"RAC" => "H&A",
			"SAC" => "H&A",
			"Chilller" => "H&A",
			"LTV" => "HE",
			"Audio" => "HE",
			"MNT" => "BS",
			"PC" => "BS",
			"DS" => "BS",
			"MNT Signage" => "BS",
			"Commercial TV" => "BS",
		];
		
		//exchage rates
		$exchange_rates = [];
		foreach($f_month as $item){
			$now = strtotime($item);
			$f = date("Y-m-01", $now);
			$t = date("Y-m-t", $now);
			$er = $this->gen_m->avg("exchange_rate", "avg", ["date >=" => $f, "date <=" => $t, "currency" => "PEN"]);
			
			if ($er->avg) $last_er = round($er->avg, 2);
			$exchange_rates[$item] = $er->avg ? round($er->avg, 2) : $last_er;
		}
		
		//rawdatas
		
		$magentos_list = [];
		$magentos_list["structure"] = $this->gen_m->structure("v_obs_magento");
		$magentos = $this->gen_m->filter("v_obs_magento", false, ["local_time >= " => $from_magento." 00:00:00", "gerp_order_no >" => 0]);//echo count($magentos)."<br/><br/>";
		foreach($magentos as $item) $magentos_list[$item->gerp_order_no] = $item;
		
		$sales = $this->gen_m->filter("v_obs_sales_order", false, ["create_date >= " => $from_sales]);//echo count($sales)."<br/><br/>";
		foreach($sales as $i => $item){
			if (array_key_exists($item->order_no, $magentos_list)) $magento_aux = $magentos_list[$item->order_no];
			else{
				$magento_aux = $magentos_list["structure"];
				$magento_aux->local_time = $item->create_date." 00:00:00";
			}
			
			foreach($magento_aux as $key => $val) $item->$key = $val;
		
			//usd sales amount
			$item->exchange_rate = $exchange_rates[date("Y-m", strtotime($item->create_date))];
			$item->sales_amount_usd = round($item->sales_amount / $item->exchange_rate, 2);
		
			//set up values by mapping array
			$item->bill_to_name = $m_bill_to_name[$item->bill_to_name];
			$item->line_status = $m_line_status[$item->line_status];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			
			//iod reference
			$iod_date = $item->close_date ? $item->close_date : $item->req_arrival_date_to;
			$iod_diff = $this->my_func->diff_month($iod_date, $today);
			
			if ((strtotime($iod_date) < strtotime($today))) $iod_diff = -$iod_diff;
			
			switch(true){
				case (strtotime($iod_date) > strtotime($today)): $item->iod_ref = "M+".$iod_diff; break;
				case (strtotime($iod_date) < strtotime($today)): $item->iod_ref = "M".$iod_diff; break;
				default: $item->iod_ref = "M"; break;
			}
			
			//chart reference
			if ($item->close_date){
				$item->chart_ref = "Closed";
				$item->chart_date = $item->close_date;
			}else{
				$item->chart_ref = "Reserved";
				$item->chart_date = $item->req_arrival_date_to;
			}
			
			//etc setting
			if ($item->department === "Prov. Const. Del Callao") $item->department = "Callao";
		}
		
		foreach($sales as $item){
			print_r($item);
			echo "<br/><br/>";
		}
	}
	
	public function dashboard(){
		//llamasys/api/obs/dashboard?key=lgepr
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//access validation
		if ($this->input->get("key") !== "lgepr"){
			echo "No access.";
			return;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//dates definition
		$today = date("Y-m-d");
		
		$from_sales = date("Y-m-01", strtotime("-1 year", strtotime($today)));
		$from_magento = date("Y-m-01", strtotime("-1 month", strtotime($from_sales)));
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//view datas
		$v_companies = [
			["order" => 1, "company" => "H&A"],
			["order" => 2, "company" => "HE"],
			["order" => 3, "company" => "BS"],
		];
		
		//Array ( [1] => Chilller [2] => Audio [4] => Commercial TV [5] => Cooking [6] => LTV [8] => MNT [11] => PC [12] => RAC [13] => REF [14] => SAC [15] => MNT Signage [16] => W/M ) 
		$v_divisions = [
			["order" => 1, "company" => "H&A", "division" => "REF"],
			["order" => 2, "company" => "H&A", "division" => "Cooking"],
			["order" => 3, "company" => "H&A", "division" => "W/M"],
			["order" => 4, "company" => "H&A", "division" => "RAC"],
			["order" => 5, "company" => "H&A", "division" => "SAC"],
			["order" => 6, "company" => "H&A", "division" => "Chilller"],
			["order" => 7, "company" => "HE", "division" => "LTV"],
			["order" => 8, "company" => "HE", "division" => "Audio"],
			["order" => 9, "company" => "BS", "division" => "MNT"],
			["order" => 10, "company" => "BS", "division" => "PC"],
			["order" => 11, "company" => "BS", "division" => "DS"],
			["order" => 12, "company" => "BS", "division" => "MNT Signage"],
			["order" => 13, "company" => "BS", "division" => "Commercial TV"],
		];
		
		//Array ( [0] => Billing [1] => Booked [2] => Closed [3] => Returning [4] => Shipping )
		$v_status = [
			["order" => 1, "status" => "Booked"],
			["order" => 2, "status" => "Shipping"],
			["order" => 3, "status" => "Billing"],
			["order" => 4, "status" => "Closed"],
			["order" => 5, "status" => "Returning"],
		];
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//mapping datas
		$m_bill_to_name = [
			"B2B2C" => "D2B2C",
			"B2C" => "D2C",
			"B2E" => "D2E",
			"B2P" => "D2P",
			"One time_Boleta" => "D2C",
		];
		
		//( [0] => Awaiting Fulfillment [1] => Awaiting Return [2] => Awaiting Shipping [3] => Booked [4] => Closed [5] => Pending pre-billing acceptance )
		$m_line_status = [
			"Awaiting Fulfillment" => "Booked",
			"Awaiting Return" => "Returning",
			"Awaiting Shipping" => "Shipping",
			"Booked" => "Booked",
			"Closed" => "Closed",
			"Pending pre-billing acceptance" => "Billing",
		];
		
		//( [1] => A/C [2] => AUD [3] => CAV [4] => CTV [5] => CVT [6] => LCD [7] => LTV [8] => MNT [9] => MWO [10] => O [11] => PC [12] => RAC [13] => REF [14] => SAC [15] => SGN [16] => W/M ) + DS
		$m_division = [
			"" => "",//PTO case
			"A/C" => "Chilller",
			"AUD" => "Audio",
			"CAV" => "Audio",
			"CTV" => "Commercial TV",
			"CVT" => "Cooking",
			"DS" => "DS",
			"LCD" => "LTV",
			"LTV" => "LTV",
			"MNT" => "MNT",
			"MWO" => "Cooking",
			"O" => "Cooking",
			"PC" => "PC",
			"RAC" => "RAC",
			"REF" => "REF",
			"SAC" => "SAC",
			"SGN" => "MNT Signage",
			"W/M" => "W/M",
		];
		
		$m_company = [
			"" => "",//PTO case
			"REF" => "H&A",
			"Cooking" => "H&A",
			"W/M" => "H&A",
			"RAC" => "H&A",
			"SAC" => "H&A",
			"Chilller" => "H&A",
			"LTV" => "HE",
			"Audio" => "HE",
			"MNT" => "BS",
			"PC" => "BS",
			"DS" => "BS",
			"MNT Signage" => "BS",
			"Commercial TV" => "BS",
		];
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//filter datas
		$f_year = $f_month = $f_date = [];
		
		$dates = $this->gen_m->only("v_obs_sales_order", "create_date", ["create_date >= " => $from_sales]);
		foreach($dates as $item){
			$f_year[] = date("Y", strtotime($item->create_date));
			$f_month[] = date("Y-m", strtotime($item->create_date));
			$f_date[] = $item->create_date;
		}
		
		$f_year = array_unique($f_year);
		$f_month = array_unique($f_month);
		$f_date = array_unique($f_date);
		
		sort($f_year);
		sort($f_month);
		sort($f_date);
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//exchage rates
		$exchange_rates = [];
		foreach($f_month as $item){
			$now = strtotime($item);
			$f = date("Y-m-01", $now);
			$t = date("Y-m-t", $now);
			$er = $this->gen_m->avg("exchange_rate", "avg", ["date >=" => $f, "date <=" => $t, "currency" => "PEN"]);
			
			if ($er->avg) $last_er = round($er->avg, 2);
			$exchange_rates[$item] = $er->avg ? round($er->avg, 2) : $last_er;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//rawdatas
		
		$magentos_list = [];
		$magentos_list["structure"] = $this->gen_m->structure("v_obs_magento");
		$magentos = $this->gen_m->filter("v_obs_magento", false, ["local_time >= " => $from_magento." 00:00:00", "gerp_order_no >" => 0]);//echo count($magentos)."<br/><br/>";
		foreach($magentos as $item) $magentos_list[$item->gerp_order_no] = $item;
		
		$sales = $this->gen_m->filter("v_obs_sales_order", false, ["create_date >= " => $from_sales]);//echo count($sales)."<br/><br/>";
		foreach($sales as $i => $item){
			if (array_key_exists($item->order_no, $magentos_list)) $magento_aux = $magentos_list[$item->order_no];
			else{
				$magento_aux = $magentos_list["structure"];
				$magento_aux->local_time = $item->create_date." 00:00:00";
			}
			
			foreach($magento_aux as $key => $val) $item->$key = $val;
		
			//usd sales amount
			$item->exchange_rate = $exchange_rates[date("Y-m", strtotime($item->create_date))];
			$item->sales_amount_usd = round($item->sales_amount / $item->exchange_rate, 2);
		
			//set up values by mapping array
			$item->bill_to_name = $m_bill_to_name[$item->bill_to_name];
			$item->line_status = $m_line_status[$item->line_status];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			
			//iod reference
			$iod_date = $item->close_date ? $item->close_date : $item->req_arrival_date_to;
			$iod_diff = $this->my_func->diff_month($iod_date, $today);
			
			if ((strtotime($iod_date) < strtotime($today))) $iod_diff = -$iod_diff;
			
			switch(true){
				case (strtotime($iod_date) > strtotime($today)): $item->iod_ref = "M+".$iod_diff; break;
				case (strtotime($iod_date) < strtotime($today)): $item->iod_ref = "M".$iod_diff; break;
				default: $item->iod_ref = "M"; break;
			}
			
			//chart reference
			if ($item->close_date){
				$item->chart_ref = "Closed";
				$item->chart_date = $item->close_date;
			}else{
				$item->chart_ref = "Reserved";
				$item->chart_date = $item->req_arrival_date_to;
			}
			
			//etc setting
			if ($item->department === "Prov. Const. Del Callao") $item->department = "Callao";
		}
		
		$response = [
			"v_companies"		=> $v_companies,
			"v_divisions"		=> $v_divisions,
			"v_status"			=> $v_status,
			"f_bill_to_name"	=> $this->filter_maker("v_obs_sales_order", "bill_to_name", $m_bill_to_name),
			"f_line_status"		=> $this->filter_maker("v_obs_sales_order", "line_status", $m_line_status),
			"f_order_category"	=> $this->filter_maker("v_obs_sales_order", "order_category"),
			"f_division"		=> $this->filter_maker("v_obs_sales_order", "model_category", $m_division),
			"f_subsidiary"		=> $this->filter_maker("v_obs_sales_order", "customer_department"),
			"f_inventory"		=> $this->filter_maker("v_obs_sales_order", "inventory_org"),
			"f_year"			=> $f_year,
			"f_month"			=> $f_month,
			"f_date"			=> $f_date,
			"exchange_rates"	=> $exchange_rates,
			"sales"				=> $sales,
		];
		
		header('Content-Type: application/json');
		echo json_encode($response);
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//API data structure
		/*
		[bill_to] => PE008292001B 
		[bill_to_name] => D2C 
		[order_no] => 1000447719 
		[line_no] => 3.1 
		[line_status] => Billing 
		[order_status] => Booked 
		[order_category] => ORDER 
		[model_category] => LTV 
		[model] => 65QNED85TSA.AWF 
		[ordered_qty] => 1 
		[currency] => PEN 
		[unit_selling_price] => 3219.49 
		[sales_amount] => 3219.49 
		[tax_amount] => 579.51 
		[charge_amount] => 0 
		[line_total] => 3799 
		[create_date] => 2024-07-04 
		[booked_date] => 2024-07-04 
		[req_arrival_date_to] => 2024-07-08 
		[shipment_date] => 2024-07-05 
		[close_date] => 
		[receiver_city] => La Libertad/Trujillo/Victor Larco Herrera 
		[item_type_desctiption] => Merchandise 
		[item_division] => GLZ 
		[product_level1_name] => TV 
		[product_level2_name] => LED LCD TV 
		[product_level3_name] => LED LCD TV 65 
		[product_level4_name] => LED LCD TV 65 (UD) 
		[customer_department] => LGEPR 
		[inventory_org] => N4E 
		[purchase_no] => 129001041853 
		[gerp_order_no] => 1000447719 
		[local_time] => 2024-07-04 11:48:37 
		[company_name_through_vipkey] => 
		[vipkey] => 
		[coupon_code] => 
		[coupon_rule] => 
		[devices] => Mobile 
		[customer_group] => B2C 
		[payment_method] => mercadopago_global_credit_card 
		[ip_address] => 200.60.190.206 
		[sale_channel] => Magento 
		[department] => La Libertad 
		[province] => Trujillo 
		[customer_name] => Francisco Alberto Escudero Casquino 
		
		[exchange_rate] => 3.78 
		[sales_amount_usd] => 851.72 
		[division] => LTV 
		[company] => HE 
		[ref_iod] => M-1 
		[ref_chart] => Reserved 
		[ref_date] => 2024-07-08
		*/
	}
	
	public function magento(){
		//llamasys/api/obs/magento?key=lgepr&from=2022-01-01
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//access validation
		if ($this->input->get("key") !== "lgepr"){
			echo "No access.";
			return;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//magento records load
		$magentos = $this->gen_m->filter("obs_magento", false, ["local_time >= " => $this->input->get("from")." 00:00:00"]);
		
		header('Content-Type: application/json');
		echo json_encode($magentos);
	}
	
	public function gerp_sales_order(){
		//llamasys/api/obs/gerp_sales_order?key=lgepr&from=2022-01-01
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//access validation
		if ($this->input->get("key") !== "lgepr"){
			echo "No access.";
			return;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		//gerp sales order records load
		$sales = $this->gen_m->filter("obs_gerp_sales_order", false, ["create_date >= " => $this->input->get("from")]);
		
		header('Content-Type: application/json');
		echo json_encode($sales);
	}
	
	public function nsp_n(){
		//llamasys/api/obs/nsp?key=lgepr
		
		//access validation
		if ($this->input->get("key") !== "lgepr"){
			echo "No access.";
			return;
		}
		
		//mapping datas
		$m_division = [
			"" => "",//PTO case
			"A/C" => "Chilller",
			"AUD" => "Audio",
			"CAV" => "Audio",
			"CTV" => "Commercial TV",
			"CVT" => "Cooking",
			"DS" => "DS",
			"LCD" => "LTV",
			"LTV" => "LTV",
			"MNT" => "MNT",
			"MWO" => "Cooking",
			"O" => "Cooking",
			"PC" => "PC",
			"RAC" => "RAC",
			"REF" => "REF",
			"SAC" => "SAC",
			"SGN" => "MNT Signage",
			"W/M" => "W/M",
		];
		
		$m_company = [
			"" => "",//PTO case
			"REF" => "H&A",
			"Cooking" => "H&A",
			"W/M" => "H&A",
			"RAC" => "H&A",
			"SAC" => "H&A",
			"Chilller" => "H&A",
			"LTV" => "HE",
			"Audio" => "HE",
			"MNT" => "BS",
			"PC" => "BS",
			"DS" => "BS",
			"MNT Signage" => "BS",
			"Commercial TV" => "BS",
		];
		
		$m_bill_to = [
			"B2C" => "D2C",
			"B2B2C" => "D2B2C",
			"B2P" => "ETC",
			"B2E" => "ETC",
			"One time_Boleta" => "ETC",
		];
		
		//filters
		$today = date("Y-m-d");
		//$today = "2024-06-13";
		
		$from = date("Y-m-01", strtotime($today));
		$to = date("Y-m-t", strtotime($today));
		
		//aux array setting
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount >" => 0];
		$models_unique = $this->gen_m->only_multi("v_obs_sales_order", ["model_category", "model"], $w);
		
		$m_model = [];
		foreach($models_unique as $item){
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			
			$key = $item->company."_".$item->division;
			if (!array_key_exists($key, $m_model)) $m_model[$key] = [];
			$m_model[$key][] = $item->model;
			
			//print_r($item); echo "<br/>";
		}
		
		/*
		foreach($m_model as $key => $models){
			echo $key."<br/>";
			print_r($models);
			echo "<br/><br/>";
		}
		echo "<br/>";
		*/
		
		$v_companies = [
			["order" => 1, "company" => "H&A"],
			["order" => 2, "company" => "HE"],
			["order" => 3, "company" => "BS"],
		];
		
		//Array ( [1] => Chilller [2] => Audio [4] => Commercial TV [5] => Cooking [6] => LTV [8] => MNT [11] => PC [12] => RAC [13] => REF [14] => SAC [15] => MNT Signage [16] => W/M ) 
		$v_divisions = [
			["order" => 1, "company" => "H&A", "division" => "REF"],
			["order" => 2, "company" => "H&A", "division" => "Cooking"],
			["order" => 3, "company" => "H&A", "division" => "W/M"],
			["order" => 4, "company" => "H&A", "division" => "RAC"],
			["order" => 5, "company" => "H&A", "division" => "SAC"],
			["order" => 6, "company" => "H&A", "division" => "Chilller"],
			["order" => 7, "company" => "HE", "division" => "LTV"],
			["order" => 8, "company" => "HE", "division" => "Audio"],
			["order" => 9, "company" => "BS", "division" => "MNT"],
			["order" => 10, "company" => "BS", "division" => "PC"],
			["order" => 11, "company" => "BS", "division" => "DS"],
			["order" => 12, "company" => "BS", "division" => "MNT Signage"],
			["order" => 13, "company" => "BS", "division" => "Commercial TV"],
		];
		
		$v_models = [];
		
		$bill_tos = ["D2C", "D2B2C", "ETC"];
		$descriptions = ["Amt", "Qty", "NSP"];
		
		$v_bill_tos = [];
		$v_descriptions = [];
		
		$i = $j = 1;
		
		foreach($v_companies as $com_i => $com){
			$v_companies[$com_i]["key"] = $com["company"];
			
			foreach($v_divisions as $div_i => $div){
				if ($com["company"] === $div["company"]){
					$key_div = $div["company"]."_".$div["division"];
					$v_divisions[$div_i]["key"] = $div["company"]."_".$div["division"];
					
					$models = array_key_exists($key_div, $m_model) ? $models = $m_model[$key_div] : $models = [];
					foreach($models as $model){
						$key_model = $com["company"]."_".$div["division"]."_".$model;
						$v_models[$key_model] = ["company" => $com["company"], "division" => $div["division"], "model" => $model, "key" => $key_model, "amount" => 0];
						
						foreach($bill_tos as $bill_to){
							$v_bill_tos[] = ["order" => $i, "company" => $com["company"], "division" => $div["division"], "model" => $model, "bill_to" => $bill_to, "key" => $com["company"]."_".$div["division"]."_".$model."_".$bill_to];
							$i++;
							
							foreach($descriptions as $description){
								$v_descriptions[] = ["order" => $j, "company" => $com["company"], "division" => $div["division"], "model" => $model, "bill_to" => $bill_to, "desc" => $description, "key_bill_to" => $com["company"]."_".$div["division"]."_".$model."_".$bill_to, "key" => $com["company"]."_".$div["division"]."_".$model."_".$bill_to."_".$description];
								$j++;
							}
						}
					}
				}
				
			}
		}
		
		$datas = [];
		$rawdatas = $this->gen_m->only_multi("v_obs_sales_order", ["model_category", "model", "bill_to_name", "close_date", "sum(sales_amount) as sales_amount", "sum(ordered_qty) as ordered_qty"], $w, ["model_category", "model", "close_date"]);
		foreach($rawdatas as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->division = $m_division[$item->model_category];
			$item->company = $m_company[$item->division];
			$item->sales_amount = round($item->sales_amount, 2);
			$item->nsp = round($item->sales_amount / $item->ordered_qty, 2);
			
			$item->key_com = $item->company;
			$item->key_div = $item->company.'_'.$item->division;
			$item->key_model = $item->company.'_'.$item->division.'_'.$item->model;
			$item->key_bill_to = $item->company.'_'.$item->division.'_'.$item->model.'_'.$item->bill_to_name;
			
			//$datas[] = ["desc" => "Amt", "val" => $item->sales_amount, "date" => $item->close_date, "key_div" => $item->key_div, "key_com" => $item->key_com, "key_model" => $item->key_model, "key_bill_to" => $item->key_bill_to];
			//$datas[] = ["desc" => "Qty", "val" => $item->ordered_qty, "date" => $item->close_date, "key_div" => $item->key_div, "key_com" => $item->key_com, "key_model" => $item->key_model, "key_bill_to" => $item->key_bill_to];
			//$datas[] = ["desc" => "NSP", "val" => $item->nsp, "date" => $item->close_date, "key_div" => $item->key_div, "key_com" => $item->key_com, "key_model" => $item->key_model, "key_bill_to" => $item->key_bill_to];
			
			$datas[] = ["desc" => "Amt", "val" => $item->sales_amount, "date" => $item->close_date, "key" => $item->key_com];
			$datas[] = ["desc" => "Amt", "val" => $item->sales_amount, "date" => $item->close_date, "key" => $item->key_div];
			$datas[] = ["desc" => "Amt", "val" => $item->sales_amount, "date" => $item->close_date, "key" => $item->key_model];
			$datas[] = ["desc" => "Amt", "val" => $item->sales_amount, "date" => $item->close_date, "key" => $item->key_bill_to];
			
			$datas[] = ["desc" => "Qty", "val" => $item->ordered_qty, "date" => $item->close_date, "key" => $item->key_com];
			$datas[] = ["desc" => "Qty", "val" => $item->ordered_qty, "date" => $item->close_date, "key" => $item->key_div];
			$datas[] = ["desc" => "Qty", "val" => $item->ordered_qty, "date" => $item->close_date, "key" => $item->key_model];
			$datas[] = ["desc" => "Qty", "val" => $item->ordered_qty, "date" => $item->close_date, "key" => $item->key_bill_to];
			
			$datas[] = ["desc" => "NSP", "val" => $item->nsp, "date" => $item->close_date, "key" => $item->key_com];
			$datas[] = ["desc" => "NSP", "val" => $item->nsp, "date" => $item->close_date, "key" => $item->key_div];
			$datas[] = ["desc" => "NSP", "val" => $item->nsp, "date" => $item->close_date, "key" => $item->key_model];
			$datas[] = ["desc" => "NSP", "val" => $item->nsp, "date" => $item->close_date, "key" => $item->key_bill_to];
			
			//adding sales amount to model view array
			$v_models[$item->key_model]["amount"] += $item->sales_amount;
			
			//print_r($item); echo "<br/><br/>";
		}
		
		//model order by amount
		usort($v_models, function($a, $b){ return $a["amount"] < $b["amount"]; });
		foreach($v_models as $i => $item) $v_models[$i]["order"] = $i + 1;
		
		/*
		foreach($datas as $item){
			print_r($item);
			echo "<br/>";
		}
		echo "<br/>";
		
		foreach($v_companies as $item){
			print_r($item);
			echo "<br/>";
		}
		echo "<br/>";
		
		foreach($v_divisions as $item){
			print_r($item);
			echo "<br/>";
		}
		echo "<br/>";
		
		foreach($v_models as $key => $item){
			echo $key." >>> ";
			print_r($item);
			echo "<br/>";
		}
		echo "<br/>";
		
		foreach($v_bill_tos as $item){
			print_r($item);
			echo "<br/>";
		}
		echo "<br/>";
		
		foreach($v_descriptions as $item){
			print_r($item);
			echo "<br/>";
		}
		echo "<br/>";
		*/
		
		$res = [
			"datas" => $datas,
			"rawdatas" => $rawdatas,
			"v_companies" => $v_companies,
			"v_divisions" => $v_divisions,
			"v_models" => $v_models,
			"v_bill_tos" => $v_bill_tos,
			"v_descriptions" => $v_descriptions,
			"nsp_dates" => $this->my_func->dates_between($from, $to),
		];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
	
	public function nsp_v6(){//20240904
		//llamasys/api/obs/nsp?key=lgepr&request=summary/sale/date
		
		//access validation
		if ($this->input->get("key") !== "lgepr"){
			echo "No access.";
			return;
		}
		
		$v_companies = [
			["order" => 1, "company" => "H&A"],
			["order" => 2, "company" => "HE"],
			["order" => 3, "company" => "BS"],
		];
		
		//Array ( [1] => Chilller [2] => Audio [4] => Commercial TV [5] => Cooking [6] => LTV [8] => MNT [11] => PC [12] => RAC [13] => REF [14] => SAC [15] => MNT Signage [16] => W/M ) 
		$v_divisions = [
			["order" => 1, "company" => "H&A", "division" => "REF"],
			["order" => 2, "company" => "H&A", "division" => "Cooking"],
			["order" => 3, "company" => "H&A", "division" => "W/M"],
			["order" => 4, "company" => "H&A", "division" => "RAC"],
			["order" => 5, "company" => "H&A", "division" => "SAC"],
			["order" => 6, "company" => "H&A", "division" => "Chilller"],
			["order" => 7, "company" => "HE", "division" => "LTV"],
			["order" => 8, "company" => "HE", "division" => "Audio"],
			["order" => 9, "company" => "BS", "division" => "MNT"],
			["order" => 10, "company" => "BS", "division" => "PC"],
			["order" => 11, "company" => "BS", "division" => "DS"],
			["order" => 12, "company" => "BS", "division" => "MNT Signage"],
			["order" => 13, "company" => "BS", "division" => "Commercial TV"],
		];
		
		//D2C, D2B2C, ETC
		$v_bill_tos = [
			["order" => 1, "bill_to" => "D2C"],
			["order" => 2, "bill_to" => "D2B2C"],
			["order" => 3, "bill_to" => "ETC"],
		];
		
		$m_division = [
			"" => "",//PTO case
			"A/C" => "Chilller",
			"AUD" => "Audio",
			"CAV" => "Audio",
			"CTV" => "Commercial TV",
			"CVT" => "Cooking",
			"DS" => "DS",
			"LCD" => "LTV",
			"LTV" => "LTV",
			"MNT" => "MNT",
			"MWO" => "Cooking",
			"O" => "Cooking",
			"PC" => "PC",
			"RAC" => "RAC",
			"REF" => "REF",
			"SAC" => "SAC",
			"SGN" => "MNT Signage",
			"W/M" => "W/M",
		];
		
		$m_company = [
			"" => "",//PTO case
			"REF" => "H&A",
			"Cooking" => "H&A",
			"W/M" => "H&A",
			"RAC" => "H&A",
			"SAC" => "H&A",
			"Chilller" => "H&A",
			"LTV" => "HE",
			"Audio" => "HE",
			"MNT" => "BS",
			"PC" => "BS",
			"DS" => "BS",
			"MNT Signage" => "BS",
			"Commercial TV" => "BS",
		];
		
		$m_bill_to = [
			"B2C" => "D2C",
			"B2B2C" => "D2B2C",
			"B2P" => "ETC",
			"B2E" => "ETC",
			"One time_Boleta" => "ETC",
		];
		
		//filters
		$today = date("Y-m-d");
		$today = "2024-06-13";
		
		$from = date("Y-m-01", strtotime($today));
		$to = date("Y-m-t", strtotime($today));
		
		
		//aux array setting
		$w = ["close_date >= " => $from, "close_date <= " => $to, "sales_amount >" => 0];
		
		$models_unique = $this->gen_m->only_multi("v_obs_sales_order", ["model_category", "model"], $w);
		
		$dates = $this->my_func->dates_between($from, $to);
		
		$data = [];
		
		foreach($v_companies as $item) $data[$item["company"]] = [];
		foreach($v_divisions as $item) $data[$item["company"]][$item["division"]] = [];
		foreach($models_unique as $item){
			$item->model_category = $m_division[$item->model_category];
			$item->company = $m_company[$item->model_category];
			$data[$item->company][$item->model_category][$item->model] = [];
			foreach($dates as $item_date){
				$data[$item->company][$item->model_category][$item->model][$item_date] = [];
				foreach($v_bill_tos as $item_bt){
					$data[$item->company][$item->model_category][$item->model][$item_date][$item_bt["bill_to"]] = null;
				}
			}
		}
		
		//obs sales
		$sales = $this->gen_m->only_multi("v_obs_sales_order", ["model_category", "model", "bill_to_name", "close_date", "sum(sales_amount) as sales_amount", "sum(ordered_qty) as ordered_qty"], $w, ["model_category", "model", "close_date"]);
		foreach($sales as $item){
			$item->bill_to_name = $m_bill_to[$item->bill_to_name];
			$item->model_category = $m_division[$item->model_category];
			$item->company = $m_company[$item->model_category];
			$item->sales_amount = round($item->sales_amount, 2);
			$item->sale_unit = round($item->sales_amount / $item->ordered_qty, 2);
			
			$data[$item->company][$item->model_category][$item->model][$item->close_date][$item->bill_to_name] = $item;
		}
		
		/////////////////////////////////////////need to set B2C avg sale for each date here
		
		$r_model = $r_model_key = $r_key_desc = $r_amt = $r_qty = $r_nsp = [];
		
		foreach($data as $com => $divs){
			foreach($divs as $div => $models){
				foreach($models as $model => $bill_tos){
					
					$total_amount = $total_qty = 0;
					foreach($bill_tos as $s_date => $sale_dates){
						foreach($sale_dates as $bill_to => $s_items){
							if ($s_items){
								
								$r_amt[] = ["key_desc" => $model."_".$bill_to."_Amt", "desc" => "Amt", "model" => $model, "bill_to" => $bill_to, "date" => $s_date, "amount" => $s_items->sales_amount];
								$r_qty[] = ["key_desc" => $model."_".$bill_to."_Qty", "desc" => "Qty", "model" => $model, "bill_to" => $bill_to, "date" => $s_date, "qty" => $s_items->ordered_qty];
								$r_nsp[] = ["key_desc" => $model."_".$bill_to."_NSP", "desc" => "NSP", "model" => $model, "bill_to" => $bill_to, "date" => $s_date, "nsp" => $s_items->sale_unit];
								
								$total_amount += $s_items->sales_amount;
								$total_qty += $s_items->ordered_qty;
								
								//echo $com." ".$div." ".$model." ".$bill_to." ".$s_date." /// ".$s_items->sale_unit." /// ".$s_items->sales_amount." * ".$s_items->ordered_qty."<br/>";
							}
						}
					}
					
					if ($total_amount){
						//need to be object. order will be assigned later
						$result_model = new stdClass;
						$result_model->company = $com;
						$result_model->division = $div;
						$result_model->model = $model;
						$result_model->total_amount = round($total_amount, 2);
						$result_model->total_qty = $total_qty;
						
						$r_model[] = clone $result_model;	
					}
					
					//echo "===================================================<br/><br/>";
				}
			}
		}
		
		//sort and assign print order en power BI
		usort($r_model, function($a, $b) {
			return ($a->total_amount < $b->total_amount);
		});
		
		
		$arr_desc = ["Amt", "Qty", "NSP"];
		foreach($r_model as $i => $item){
			$item->order = $i + 1;
			
			foreach($v_bill_tos as $item_bt){
				$key_aux = $item->model."_".$item_bt["bill_to"];
				$r_model_key[] = ["model" => $item->model, "bill_to" => $item_bt["bill_to"], "key" => $key_aux];
				
				foreach($arr_desc as $desc) $r_key_desc[] = ["key" => $key_aux, "key_desc" => $key_aux."_".$desc, "desc" => $desc];
			}
		}
		
		/*
		foreach($r_model as $i => $item){ print_r($item); echo "<br/>"; }
		echo "===================================================<br/><br/>";
		foreach($r_amt as $i => $item){ print_r($item); echo "<br/>"; }
		echo "===================================================<br/><br/>";
		foreach($r_qty as $i => $item){ print_r($item); echo "<br/>"; }
		echo "===================================================<br/><br/>";
		foreach($r_nsp as $i => $item){ print_r($item); echo "<br/>"; }
		*/
		
		$res = [
			"model" => $r_model,
			"key" => $r_model_key,
			"key_desc" => $r_key_desc,
			"amount" => $r_amt,
			"qty" => $r_qty,
			"nsp" => $r_nsp,
			"nsp_dates" => $this->my_func->dates_between($from, $to),
		];
		
		header('Content-Type: application/json');
		echo json_encode($res);
	}
}
