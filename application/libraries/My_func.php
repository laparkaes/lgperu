<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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
	
	public function header_compare($h1, $h2){
		$res = true;
		
		$h1_qty = count($h1);
		$h2_qty = count($h2);
		
		if ($h1_qty == $h2_qty){
			for($i = 0; $i < $h1_qty; $i++) $res = ($res and (trim($h1[$i]) === trim($h2[$i])));
		}else $res = false;
		
		return $res;
	}
	
	public function arr_trim($arr){
		$new = [];
		foreach($arr as $val) $new[] = trim($val);
		return $new;
	}
	
	public function get_random_float($min, $max, $precision = 2) {//get random float value between min and max
		$factor = pow(10, $precision);
		$randomInt = mt_rand($min * $factor, $max * $factor);
		return $randomInt / $factor;
	}
	
	//date format handler
	public function date_convert($date){//dd/mm/yyyy > yyyy-mm-dd
		$aux = explode("/", $date);
		if (count($aux) > 2) return $aux[2]."-".$aux[1]."-".$aux[0];
		else return null;
	}
	
	public function date_convert_2($date){//yyyy/mm/dd hh:mm:ss > yyyy-mm-dd
		return str_replace("/", "-", explode(" ", $date)[0]);
	}
	
	public function date_convert_3($date_str){//yyyymmdd > yyyy-mm-dd
		$date = DateTime::createFromFormat('Ymd', $date_str);
		return $date->format('Y-m-d');
	}
	
	public function date_convert_4($original_date){//28-OCT-21 > 2021-10-28
		if ($original_date){
			$date = DateTime::createFromFormat('d-M-y', trim($original_date));
			return $date->format('Y-m-d');
		}else return null;
	}
	
	public function date_convert_5($original_date){//28-OCT-2021 > 2021-10-28
		if ($original_date){
			$date = DateTime::createFromFormat('d-M-Y', trim($original_date));
			return $date->format('Y-m-d');
		}else return null;
	}
	
	public function date_convert_6($date_str){//ddmmyyyy > yyyy-mm-dd
		$date = DateTime::createFromFormat('dmY', $date_str);
		return $date->format('Y-m-d');
	}
	
	public function dates_between($startDate, $endDate){
		$start = new DateTime($startDate);
		$end = new DateTime($endDate);
		$end = $end->modify('+1 day'); // 마지막 날짜를 포함하기 위해 하루를 더함

		$interval = new DateInterval('P1D'); // 1일 간격
		$dateRange = new DatePeriod($start, $interval, $end);

		$dates = [];
		foreach ($dateRange as $date) {
			$dates[] = $date->format('Y-m-d');
		}

		return $dates;
	}
	
	public function last_working_date($dateString = "2024-01-01"){
		$date = new DateTime($dateString);
		$dayOfWeek = $date->format('N');
		
		if ($dayOfWeek > 5) {
			$daysToSubtract = $dayOfWeek - 5;
			$date->sub(new DateInterval("P{$daysToSubtract}D"));
		}

		return $date->format('Y-m-d');
	}
	//date format handler - end
	
	public function get_dates_by_week($week, $year){
		$dateTime = new DateTime();
		$dateTime->setISODate($year, $week);//1 week: from monday ~ sunday
		
		//need from sunday ~ saturday
		$startDate = date("Y-m-d", strtotime("-1 day", strtotime($dateTime->format('Y-m-d'))));
		if ((string)$year !== date("Y", strtotime($startDate))) $startDate = $year."-01-01";
		
		$dateTime->modify('+6 days');//add one week in days
		$endDate = date("Y-m-d", strtotime("-1 day", strtotime($dateTime->format('Y-m-d'))));
		if ((string)$year !== date("Y", strtotime($endDate))) $endDate = $year."-12-31";
		
		return (($startDate === $year."-01-01") and ($endDate === $year."-12-31")) ? null : [$startDate, $endDate];
	}
	
	public function get_week_by_date($date){
		$year = date("Y", strtotime($date));
		$week = 1;
		
		while (true){
			$res = $this->get_dates_by_week($week, $year);
			if (strtotime($res[1]) < strtotime($date)) $week++; else break;
		}
		
		return ["week" => $week, "dates" => $res];
	}
	
	/* public function get_record($tablename, $data){
		$record = $this->CI->gen_m->filter($tablename, true, $data);
		if (!$record){
			$this->CI->gen_m->insert($tablename, $data);
			$record = $this->CI->gen_m->filter($tablename, true, $data);
		}
		
		return $record[0];
	}*/
	
	//exchange rate handler
	public function get_exchange_rate_month_ttm($date){
		$from = date("Y-m-01", strtotime($date));
		$to = date("Y-m-t", strtotime($date));
		
		//echo $from." ".$to;
		
		$exchange_rate = $this->CI->gen_m->avg("exchange_rate", "avg", ["date >=" => $from, "date <=" => $to])->avg;
		if (!$exchange_rate){
			$last_ex = $this->CI->gen_m->filter("exchange_rate", false, ["currency" => "USD"], null, null, [["date", "desc"]], 1, 0);
			$exchange_rate = round($last_ex[0]->avg, 2);
		}
		
		return $exchange_rate;
	}
	
	public function get_exchange_rate_usd($date, $currency = "USD"){
		$sbs = $this->load_exchange_rate_sbs($date);
		
		if (($sbs !== "No data") and ($sbs !== null)){
			//print_r($sbs); echo "<br/>";
			
			/*
			$buy = $this->get_random_float(3.51, 3.91);
			$sell = $this->get_random_float($buy + 0.03, $buy + 0.3);
			*/
			
			$buy = (float)str_replace(",", ".", $sbs["valor_compra"]);
			$sell = (float)str_replace(",", ".", $sbs["valor_venta"]);
			$avg = ($buy + $sell) / 2;
			
			return ["date" => $date, "currency" => $currency, "buy" => $buy, "sell" => $sell, "avg" => $avg];	
		}return null;
	}
	
	public function load_exchange_rate_sbs($date = null){
		if ($date) $date = date("dmY", strtotime($date));
		else $date = date("dmY");
		
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoibGdlIiwic3ViIjoibGdlIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjpbIk1hbmFnZXIiLCJTdXBlcnZpc29yIl0sIm5iZiI6MTcxODgxOTgzOSwiZXhwIjoxNzUwMzU1ODM5LCJpc3MiOiJodHRwOi8vand0YXV0aHpzcnYuYXp1cmV3ZWJzaXRlcy5uZXQiLCJhdWQiOiIwOTkxNTNjMjYyNTE0OWJjOGVjYjNlODVlMDNmMDAyMiJ9.1ejIUlAPbq8FhggDzJIhXkYrRCMli1ghC8OI2PETwZc';
		
		$ch = curl_init();
		$url = 'http://serviciosweb.sbs.gob.pe/api/tipocambio/'.$date.'/02';
		$headers = [
			'Accept: application/json',
			'Authorization: Bearer '.$token,
		];
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);//max waiting time 5 secs

		$response = curl_exec($ch);
		
		curl_close($ch);

		return json_decode($response, true);
	}
	//exchange rate handler - end
	
	public function generate_excel_report($filename, $title, $header, $rows){
		$url = "";
		
		if ($rows){
			$row_now = 1;
			//foreach($rows as $r){ print_r($r); echo "<br/>"; }
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			
			if ($title){
				//set report parameters
				$sheet->setCellValueByColumnAndRow(1, $row_now, $title);
				
				$row_now++;
				$sheet->setCellValueByColumnAndRow(1, $row_now, "Date");
				$sheet->setCellValueByColumnAndRow(2, $row_now, date('Y-m-d H:i:s'));
				
				$row_now = $row_now + 2;
			}
			
			
			//set header
			foreach($header as $i => $h) $sheet->setCellValueByColumnAndRow(($i + 1), $row_now, $h);
			
			//set rows
			$row_from = $row_now + 1;
			foreach($rows as $j => $row) 
				foreach($row as $i => $r) 
					$sheet->getCellByColumnAndRow(($i + 1), $row_from + $j)->setValue($r);
					//$sheet->getCellByColumnAndRow(($i + 1), $row_from + $j)->setValueExplicit($r, DataType::TYPE_STRING);
			
			//save excel file to a temporary directory
			$file_path = './upload/';
			$writer = new Xlsx($spreadsheet);
			$writer->save($file_path.$filename);
			
			//file url
			$url = base_url()."upload/".$filename;
		}
		
		return $url;
	}

	//gerp sales order merged with magento data
	public function get_gerp_iod($from, $to){
		//set db fields
		$s_g = ["create_date", "close_date", "customer_department", "line_status", "order_category", "order_no", "line_no", "model_category", "model", "product_level1_name","product_level4_name", "product_level4_code", "item_type_desctiption", "currency", "unit_selling_price", "ordered_qty", "sales_amount", "bill_to_name"];
		
		//load all this month records
		$w_g = ["inventory_org" => "N4E", "create_date >=" => $from, "create_date <=" => $to, "line_status !=" => "Cancelled", "customer_department !=" => "Branch"];
		$gerps = $this->CI->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g, null, null, [["create_date", "desc"], ["close_date", "desc"]]);
		
		//load closed on this month
		$w_g_ = ["inventory_org" => "N4E", "create_date <" => $from, "close_date >=" => $from, "close_date <=" => $to, "line_status !=" => "Cancelled", "customer_department !=" => "Branch"];
		$w_in_ = [["field" => "line_status", "values" => ["Awaiting Fulfillment", "Awaiting Shipping", "Booked", "Pending pre-billing acceptance", "Closed"]]];
		$gerps_ = $this->CI->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g_, null, $w_in_, [["create_date", "desc"], ["close_date", "desc"]]);
		
		//load no closed orders
		$w_g__ = ["inventory_org" => "N4E", "create_date <" => $from, "line_status !=" => "Cancelled", "customer_department !=" => "Branch"];
		$w_in__ = [["field" => "line_status", "values" => ["Awaiting Fulfillment", "Awaiting Shipping", "Booked", "Pending pre-billing acceptance"]]];
		$gerps__ = $this->CI->gen_m->filter_select("obs_gerp_sales_order", false, $s_g, $w_g__, null, $w_in__, [["create_date", "desc"], ["close_date", "desc"]]);
		
		//merge gerp records
		$rows = [];
		
		$gerps = array_merge($gerps, $gerps_, $gerps__);
		if ($gerps){
			$date_min = date('Y-m-d', strtotime('-1 week', strtotime($gerps[count($gerps)-1]->create_date)));
			$date_max = date('Y-m-d', strtotime('+1 week', strtotime($gerps[0]->create_date)));
			
			$s_m = [
				"gerp_order_no", 
				"local_time", 
				"customer_name", 
				"company_name_through_vipkey", 
				"vipkey", 
				"coupon_code", 
				"coupon_rule", 
				"discount_amount", 
				"devices", 
				"knout_status", 
				"customer_group", 
				"payment_method", 
				"purchase_date", 
				"ip_address", 
				"zipcode", 
				"department", 
				"province", 
			];
			$w_m = ["gerp_order_no !=" => "", "local_time >=" => $date_min." 00:00:00", "local_time <=" => $date_max." 23:59:59"];
			$magentos = $this->CI->gen_m->filter_select("obs_magento", false, $s_m, $w_m);
			
			$magentos_arr = [];
			foreach($magentos as $m) $magentos_arr[$m->gerp_order_no] = $m;
			
			$m_blank = [
				"local_time" => null, 
				"customer_name" => null, 
				"company_name_through_vipkey" => null, 
				"vipkey" => null, 
				"coupon_code" => null, 
				"coupon_rule" => null, 
				"discount_amount" => null, 
				"devices" => null, 
				"knout_status" => null, 
				"customer_group" => null, 
				"payment_method" => null, 
				"purchase_date" => null, 
				"ip_address" => null, 
				"zipcode" => null, 
				"department" => null, 
				"province" => null, 
			];
			
			$time_from = strtotime($from);
			$time_to = strtotime($to);
			$er_ttm = round($this->CI->my_func->get_exchange_rate_month_ttm(date("Y-m-d", strtotime($to))), 2);
			
			$divisions = ["HA", "HE", "BS"];
			$division_map = [
				"HA" => ["REF", "COOK", "W/M", "RAC", "SAC", "A/C"],
				"HE" => ["TV", "AV"],
				"BS" => ["MNT", "PC", "DS", "SGN", "CTV"],
			];
			$division_map_inv = [];
			foreach($division_map as $div => $divisions) foreach($divisions as $cat) $division_map_inv[$cat] = $div;
			
			$categories = ["REF", "COOK", "W/M", "A/C", "RAC", "SAC", "TV", "AV", "MNT", "PC", "DS", "SGN", "CTV"];
			$category_map = [
				"REF" => ["REF"],
				"COOK" => ["MWO", "O", "CVT"],
				"W/M" => ["W/M"],
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
			$category_map_inv = [];
			foreach($category_map as $cat => $categories) foreach($categories as $c) $category_map_inv[$c] = $cat;
			
			foreach($gerps as $g){
				$create_time = strtotime($g->create_date);
				$close_time = strtotime($g->close_date);
				
				$g->line_no = "'".$g->line_no;
				$g->sales_amount_usd = ($g->currency == "USD") ? $g->sales_amount : round($g->sales_amount / $er_ttm, 2);
				$g->dash_division = $g->model_category ? $category_map_inv[$g->model_category] : null;
				$g->dash_company = $g->dash_division ? $division_map_inv[$g->dash_division] : null;
				$g->dash_status = $g->line_status === "Closed" ? "Closed" : "Reserved";
				$g->dash_week = $g->close_date ? "W".$this->get_week_by_date($g->close_date)["week"] : "Reserved";
				
				if ($g->line_status === "Closed"){
					if ($create_time < $time_from) $g->delivery = "M-1";
					elseif ($close_time <= $time_to) $g->delivery = "M";
					else $g->delivery = "M+1";
				}else $g->delivery = "M+1";
				
				$g_arr = [];
				foreach($g as $key => $val) $g_arr[$key] = $val;
				
				if (array_key_exists($g->order_no, $magentos_arr)){
					$m_arr = [];
					foreach($magentos_arr[$g->order_no] as $key => $val) $m_arr[$key] = $val;
					
					$rows[] = array_merge($g_arr, $m_arr);
				}else $rows[] = array_merge($g_arr, $m_blank);
			}
		}
		
		//sort by date asc
		usort($rows, function($a, $b) {
			if ($a["create_date"] === $b["create_date"]) return (strtotime($a["local_time"]) < strtotime($b["local_time"]));
			else return (strtotime($a["create_date"]) < strtotime($b["create_date"]));
		});
		
		//foreach($rows as $row){print_r($row); echo "<br/><br/>";}
		
		return json_decode(json_encode($rows), false);
	}
}