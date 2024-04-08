<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Purchase_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->color_rgb = [
			"green" => "198754",
			"red" => "dc3545",
		];
	}
	
	public function sku_imp(){
		$customers = $this->gen_m->filter("customer", true, null, [["field" => "customer", "values" => ["hiraoka"]]]);
		
		//[category, sku, model]
		$skus = '[["AUDIO", "128738", "XG7QGR.DPERLLK"], ["AUDIO", "128739", "XG9QBK.DPERLLK"], ["AUDIO", "128380", "S75Q.DPERLLK"], ["AUDIO", "129300", "XL7S.DPERLLK"], ["AUDIO", "129470", "XL5S.DPERLLK"], ["AUDIO", "131768", "OK99M.DPERLLK"], ["AUDIO", "131767", "CK43N.DPERLLK"], ["AUDIO", "131769", "XL9T.DPERLLK"], ["AUDIO", "131770", "XG8T.DPERLLK"], ["AUDIO", "130630", "SH7Q.DPERLLK"], ["AUDIO", "130434", "SQC1.DPERLLK"], ["AUDIO", "113669", "SK1.APERLLK"], ["AUDIO", "130207", "SC9S.DPERLLK"], ["AUDIO", "130120", "RNC9.DPERLLK"], ["AUDIO", "130118", "RNC5.DPERLLK"], ["AUDIO", "130119", "RNC7.DPERLLK"], ["AUDIO", "128734", "XO3QBK.DPERLLK"], ["AUDIO", "128735", "XG5QBK.DPERLLK"], ["AUDIO", "128736", "XG5QGR.DPERLLK"], ["AUDIO", "128737", "XG7QBK.DPERLLK"], ["HOOD", "126477", "HCEZ3605S2.CSTGLPR"], ["LCD TV ACC.", "130726", "MR23GN.AWP"], ["LTV", "129654", "OLED77C3PSA.AWF"], ["LTV", "129655", "OLED77G3PSA.AWF"], ["LTV", "129656", "OLED83C3PSA.AWF"], ["LTV", "129648", "OLED55B3PSA.AWF"], ["LTV", "129651", "OLED65B3PSA.AWF"], ["LTV", "129652", "OLED65C3PSA.AWF"], ["LTV", "129647", "OLED48C3PSA.AWF"], ["LTV", "129794", "70NANO77SRA.AWF"], ["LTV", "129795", "75NANO77SRA.AWF"], ["LTV", "127050", "OLED83C2PSA.AWF"], ["LTV", "128998", "42LX3QPSA.AWF"], ["LTV", "128053", "43UQ7500PSF.AWF"], ["LTV", "128054", "50UQ7500PSF.AWF"], ["LTV", "128055", "55UP7760PSB.AWF"], ["LTV", "128056", "60UQ8050PSB.AWF"], ["LTV", "128058", "OLED55B2PSA.AWF"], ["LTV", "129785", "50UR8750PSA.AWF"], ["LTV", "129786", "55UR8750PSA.AWF"], ["LTV", "129787", "55QNED80SRA.AWF"], ["LTV", "129788", "65QNED80SRA.AWF"], ["LTV", "131722", "65QNED75SRA.AWF"], ["LTV", "131725", "75QNED90SQA.AWF"], ["LTV", "131724", "75QNED99SQA.AWF"], ["LTV", "131031", "65QNED90SQA.AWF"], ["LTV", "131729", "75NANO77SRA.AWFZ"], ["LTV", "131723", "55QNED75SRA.AWF"], ["LTV", "131030", "65QNED85SRA.AWF"], ["LTV", "131727", "27LX5QKNA.AWF"], ["LTV", "131730", "86NANO77SRA.AWFZ"], ["LTV", "131728", "65NANO77SRA.AWFZ"], ["LTV", "131726", "86QNED99SPA.AWF"], ["LTV", "129790", "86QNED80SRA.AWF"], ["LTV", "131029", "32LQ600BPSA.AWFQ"], ["LTV", "130419", "65UR7300PSA.AWFQ"], ["LTV", "130194", "43UR8750PSA.AWF"], ["LTV", "130195", "65UR8750PSA.AWF"], ["LTV", "129793", "65NANO77SRA.AWF"], ["LTV", "130192", "50UR7300PSA.AWFQ"], ["LTV", "130193", "55UR7300PSA.AWFQ"], ["LTV", "129649", "OLED55C3PSA.AWF"], ["LTV", "129650", "OLED55G3PSA.AWF"], ["LTV", "129653", "OLED65G3PSA.AWF"], ["LTV", "129789", "75QNED80SRA.AWF"], ["LTV", "129791", "50NANO77SRA.AWF"], ["LTV", "129792", "55NANO77SRA.AWF"], ["LTV", "129796", "86NANO77SRA.AWF"], ["LTV", "128999", "86QNED90SQA.AWF"], ["LTV", "128997", "65UQ7950PSB.AWF"], ["LTV", "128996", "55UQ7950PSB.AWF"], ["LTV", "128908", "OLED48A2PSA.AWF"], ["LTV", "128740", "55LX1QPSA.AWF"], ["LTV", "128741", "55QNED7SSQA.AWF"], ["LTV", "128753", "55QNED85SQA.AWF"], ["LTV", "128754", "65QNED85SQA.AWF"], ["LTV", "128756", "86QNED85SQA.AWF"], ["LTV", "127917", "86QNED80SQA.AWF"], ["LTV", "128381", "75UP7760PSB.AWF"], ["LTV", "128382", "65QNED7SSQA.AWF"], ["LTV", "128383", "75QNED7SSQA.AWF"], ["LTV", "128057", "65UP7760PSB.AWF"], ["LTV", "128059", "OLED77Z2PSA.AWF"], ["LTV", "127034", "65NANO80SQA.AWF"], ["LTV", "127047", "OLED65G2PSA.AWF"], ["LTV", "127048", "OLED77C2PSA.AWF"], ["LTV", "127032", "55NANO80SQA.AWF"], ["LTV", "127042", "OLED48C2PSA.AWF"], ["LTV", "127028", "50UQ8050PSB.AWF"], ["LTV", "127030", "50NANO75SQA.AWF"], ["LTV", "127031", "55NANO75SQA.AWF"], ["LTV", "132578", "75UR8750PSA.AWF"], ["LTV", "127916", "65UQ8050PSB.AWF"], ["LTV", "126501", "32LQ630BPSA.AWF"], ["LTV", "126502", "43UP7500PSF.AWF"], ["LTV", "127029", "55UQ8050PSB.AWF"], ["LTV", "127033", "65NANO75SQA.AWF"], ["LTV", "127037", "86NANO75SQA.AWF"], ["LTV", "127038", "55QNED80SQA.AWF"], ["LTV", "127039", "65QNED80SQA.AWF"], ["LTV", "127040", "75QNED80SQA.AWF"], ["LTV", "127043", "OLED55A2PSA.AWF"], ["LTV", "127045", "OLED65A2PSA.AWF"], ["LTV", "127046", "OLED65C2PSA.AWF"], ["LTV", "127049", "OLED77G2PSA.AWF"], ["LTV", "127044", "OLED55C2PSA.AWF"], ["LTV", "127035", "70NANO75SQA.AWF"], ["LTV", "127036", "75NANO75SQA.AWF"], ["MNT", "130468", "24MQ400-B.AWF"], ["MNT", "131700", "32GP750-B.AWF"], ["MNT", "131046", "32MP60G-B.AWF"], ["MNT", "131049", "27GR75Q-B.AWF"], ["MNT", "130571", "27MQ400-B.AWF"], ["MNT", "130321", "24GN65R-B.AWF"], ["MNT", "130322", "32GN55R-B.AWF"], ["MNT", "130324", "34WQ680-W.AWF"], ["MNT", "130323", "27UK580-B.AWF"], ["MNT", "130325", "27GN65R-B.AWF"], ["MNT", "128937", "26WQ500-B.AWF"], ["MNT", "128914", "29WQ600-W.AWF"], ["MNT", "128813", "32GN50R-B.AWF"], ["MNT", "128811", "24GN60R-B.AWF"], ["MNT", "128812", "27GN60R-B.AWF"], ["MNT", "128377", "27MP400-B.AWF"], ["MNT", "128378", "34WQ60C-B.AWF"], ["MNT", "132596", "27MS500-B.AWF"], ["MNT", "132231", "27MP60G-B.AWF"], ["MNT", "132232", "32UR550-B.AWF"], ["MNT", "123798", "29WP60G-B.AWF"], ["MNT", "126566", "24GN600-B.AWF"], ["MNT", "126567", "27GP750-B.AWF"], ["MNT", "132595", "34GP63A-B.AWF"], ["MWO", "131013", "MH7032JAS.BBKGLPR"], ["MWO", "131014", "MS2032GAS.BBKGLPR"], ["OVEN", "132167", "LRGZ5253S.CSTGLPR"], ["OVEN", "132166", "LRGZ5255S.CSTGLPR"], ["OVEN", "129720", "LRGL5841S.BSTGLPR"], ["OVEN", "129721", "LRGL5843S.BSTGLPR"], ["OVEN", "129722", "LRGL5845S.BSTGLPR"], ["OVEN", "129723", "LRGL5847S.BSTGLPR"], ["PC", "127155", "24V50N-G.AJ59B4"], ["PC", "127157", "27V70N-G.AH79B4"], ["PC", "128814", "16Z90Q-G.AH76B4"], ["PC", "127707", "16MQ70.ASDB4"], ["PC", "127661", "16Z90Q-G.AJ56B4"], ["PC", "127663", "17Z90Q-G.AH76B4"], ["PC", "127664", "17Z90Q-G.AH78B4"], ["PC", "127662", "16Z90Q-G.AH78B4"], ["REF", "129166", "LS51BPP.AHSGLPR"], ["REF", "130136", "GT31WPP.APZGLPR"], ["REF", "130658", "GT39SGP1.APZGLPR"], ["REF", "130550", "GM78SXT.AMCGLPR"], ["REF", "130551", "GT57BPSX.ASTGLPR"], ["REF", "130133", "GT39AGD1.ABLGLPR"], ["REF", "130134", "GT33BPP.APZGLPR"], ["REF", "130135", "GT33WPP.APZGLPR"], ["REF", "130200", "GT51SGD.ABLGLPR"], ["REF", "129719", "GT31BPP.APZGLPR"], ["REF", "129431", "GT26BPP.APZGLPR"], ["REF", "128666", "GT24BPP.APZGLPR"], ["REF", "125911", "GT39AGD.ABLGLPR"], ["REF", "131342", "GS51MPD.AHBGLPR"], ["REF", "125908", "GB46TGT.AMCGLPR"], ["REF", "125903", "LS66SDP.APZGLPR"], ["REF", "125910", "GB41BPP.APZGLPR"], ["REF", "125904", "LS66SPP.APZGLPR"], ["REF", "125901", "LS66SXT.AMCGLPR"], ["REF", "125902", "LS66SXN.APZGLPR"], ["REF", "125905", "LS66SPG.ADSGLPR"], ["REF", "125912", "GT39SGP.APZGLPR"], ["REF", "125909", "GB41WGT.AMCGLPR"], ["REF", "125913", "GT37SGP.APZGLPR"], ["WM", "125914", "WT3BM.BBLGLGP"], ["WM", "127838", "WD11WVC3S6.ABWGLGP"], ["WM", "127839", "WD9PVC4S6.APTGLGP"], ["WM", "130656", "WD15BG2S.ABLGLGP"], ["WM", "130554", "WT13WPBK.ABWGLGP"], ["WM", "130555", "WK14BS6.APBGLGP"], ["WM", "130138", "WT25PBVS6.APBGLGP"], ["WM", "130139", "WT23PBVS6.APBGLGP"], ["WM", "130553", "WT13DPBK.ASFGLGP"], ["WM", "130137", "WT21VV6.ASSGLGP"], ["WM", "129527", "WT19BV6.ABMGLGP"], ["WM", "129724", "WK22GGS6.AGGGLGP"], ["WM", "129725", "WT21PBV6.APBGLGP"], ["WM", "129726", "WT17DV6.ASFGLGP"], ["WM", "129727", "WT19DV6.ASFGLGP"], ["WM", "129728", "WT17BV6.ABMGLGP"], ["WM", "129432", "WD22BV2S6R.ABLGLGP"], ["WM", "129168", "WD22VV2S6R.ASSGLGP"], ["WM", "129167", "WD20VV2S6R.ASSGLGP"], ["WM", "127665", "WT16BPB.ABMGLGP"], ["WM", "127837", "WT19BPB.ABMGLGP"], ["WM", "128667", "WD11PVC3S6.APTGLGP"], ["WM", "127840", "WD15WG2SP.ABWGLGP"], ["WM", "127259", "WK22BS6.ABLGLGP"], ["WM", "127260", "WK22WS6.ABWGLGP"], ["WM", "125916", "WD2100PM.APTGLGP"], ["WM", "125918", "WD2100WM.ABWGLGP"], ["WM", "125917", "WD2100BM.ABLGLGP"], ["WM", "125915", "WD100CV.BSSGLGP"]]';
		$skus = json_decode($skus);
		
		foreach($skus as $s){
			$prod = $this->gen_m->unique("product", "model", $s[2]);
			if (!$prod){
				if ($s[0] === "MNT") $cat = 7;
				elseif ($s[0] === "PC") $cat = 8;
				else $cat = null;
				
				if ($cat){
					$row = ["category_id" => $cat, "model" => $s[2]];
					if (!$this->gen_m->filter("product", true, $row)) $this->gen_m->insert("product", $row);
					
					$prod = $this->gen_m->unique("product", "model", $s[2]);
				}else{
					print_r($s); echo "<br/>";
				}
			}
			
			foreach($customers as $c){
				$row = ["product_id" => $prod->product_id, "customer_id" => $c->customer_id, "sku" => $s[1]];
				if (!$this->gen_m->filter("product_sku", true, $row)) $this->gen_m->insert("product_sku", $row);
			}
		}
		
		echo "Fin";
	}
	
	private function hiraoka_pre($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(explode(" ", $rows_input[5])[4]);
		
		$aux = explode("/", trim($rows_input[14]));
		$issue_date = $aux[2].$aux[1].$aux[0];
		
		$aux = explode("/", trim($rows_input[15]));
		$arrival_date = $aux[2].$aux[1].$aux[0];
		
		$currency = "PEN";
		
		foreach($rows_input as $r){
			$aux = array_values(array_filter(explode(" ", $r)));
			
			if (count($aux) > 6) if (is_numeric($aux[0])){
				//get last position of number and extract total amount
				$aux_text = trim($aux[5]);
				preg_match('/[a-z]+/i', $aux_text, $matches, PREG_OFFSET_CAPTURE);
				
				$total = substr($aux_text, 0, $matches[0][1]);
				$sku = trim($aux[1]);
				$qty = trim($aux[3]);
				$unit_price = trim($aux[4]);
				
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				$model = ($prod) ? $prod->model : "";
				
				$rows[] = [
					$po_num,//Customer PO No.
					$ship_to->ship_to_code,//Ship To
					$currency,//Currency
					$arrival_date,//Request Arrival Date(YYYYMMDD)
					$model,//Model
					$qty,//Quantity
					str_replace(",", "", $unit_price),//Unit Selling Price
					null,//Warehouse
					null,//Payterm
					null,//Shipping Remark
					null,//Invoice Remark
					null,//Customer RAD(YYYYMMDD)
					$issue_date,//Customer PO Date(YYYYMMDD)
					null,//H Flag
					null,//OP Code
					null,//Country
					null,//Postal Code
					null,//Address1
					null,//Address2
					null,//Address3
					null,//Address4
					null,//City
					null,//State
					null,//Province
					null,//County
					$ship_to->customer->customer,//Consumer Name
					null,//Consumer Phone No.
					null,//Receiver Name
					null,//Receiver Phone No.
					null,//Freight Charge
					null,//Freight Term
					null,//Price Condition
					null,//Picking Remark
					null,//Shipping Method
				];
			}
		}
		
		return $rows;
	}
	
	public function hiraoka_sku($rows_input, $ship_to){
		$rows = [];
		
		$po_num = trim(explode(" ", $rows_input[5])[4]);
		
		$aux = explode("/", trim($rows_input[14]));
		$issue_date = $aux[2].$aux[1].$aux[0];
		
		$aux = explode("/", trim($rows_input[15]));
		$arrival_date = $aux[2].$aux[1].$aux[0];
		
		$currency = "PEN";
		
		$prod_num = 1;
		foreach($rows_input as $i => $r){
			$aux = array_values(array_filter(explode(" ", $r)));
			if (count($aux) > 6) if (is_numeric($aux[0])){
				//get last position of number and extract total amount
				$aux_text = trim($aux[4]);
				preg_match('/[a-z]+/i', $aux_text, $matches, PREG_OFFSET_CAPTURE);
				
				$total = substr($aux_text, 0, $matches[0][1]);
				$sku = substr(trim($aux[0]), strlen((string)$prod_num));//need to work with sku = [num][sku] => need to extract num value
				$qty = trim($aux[2]);
				$unit_price = trim($aux[3]);
				
				$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
				$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
				$model = ($prod) ? $prod->model : "No SKU: ".$sku;
				
				$rows[] = [
					$po_num,//Customer PO No.
					$ship_to->ship_to_code,//Ship To
					$currency,//Currency
					$arrival_date,//Request Arrival Date(YYYYMMDD)
					$model,//Model
					$qty,//Quantity
					str_replace(",", "", $unit_price),//Unit Selling Price
					null,//Warehouse
					null,//Payterm
					null,//Shipping Remark
					null,//Invoice Remark
					null,//Customer RAD(YYYYMMDD)
					$issue_date,//Customer PO Date(YYYYMMDD)
					null,//H Flag
					null,//OP Code
					null,//Country
					null,//Postal Code
					null,//Address1
					null,//Address2
					null,//Address3
					null,//Address4
					null,//City
					null,//State
					null,//Province
					null,//County
					$ship_to->customer->customer,//Consumer Name
					null,//Consumer Phone No.
					null,//Receiver Name
					null,//Receiver Phone No.
					null,//Freight Charge
					null,//Freight Term
					null,//Price Condition
					null,//Picking Remark
					null,//Shipping Method
				];
				
				$prod_num++;
			}
		}
		
		return $rows;
	}
	
	public function conecta_excel($filename, $ship_to){
		$rows = [];
		
		$spreadsheet = IOFactory::load($filename);
		$sheet = $spreadsheet->getActiveSheet();
		
		$max_row = $sheet->getHighestRow();
		
		for ($row = 2; $row <= $max_row; $row++){
			$aux = explode("-", trim($sheet->getCell('G'.$row)->getValue()));
			$issue_date = $aux[0].$aux[1].$aux[2];
			
			$aux = explode("-", trim($sheet->getCell('H'.$row)->getValue()));
			$arrival_date = $aux[0].$aux[1].$aux[2];
			
			$sku = trim($sheet->getCell('K'.$row)->getValue());
			$prod_sku = $this->gen_m->unique("product_sku", "sku", $sku);
			$prod = ($prod_sku) ? $this->gen_m->unique("product", "product_id", $prod_sku->product_id) : null;
			$model = ($prod) ? $prod->model : "No SKU: ".$sku;
			
			$rows[] = [
				trim($sheet->getCell('A'.$row)->getValue()),//Customer PO No.
				$ship_to->ship_to_code,//Ship To
				trim($sheet->getCell('I'.$row)->getValue()),//Currency
				$arrival_date,//Request Arrival Date(YYYYMMDD)
				$model,//Model
				trim($sheet->getCell('U'.$row)->getValue()),//Quantity
				trim($sheet->getCell('R'.$row)->getValue()),//Unit Selling Price
				null,//Warehouse
				null,//Payterm
				null,//Shipping Remark
				null,//Invoice Remark
				null,//Customer RAD(YYYYMMDD)
				$issue_date,//Customer PO Date(YYYYMMDD)
				null,//H Flag
				null,//OP Code
				null,//Country
				null,//Postal Code
				null,//Address1
				null,//Address2
				null,//Address3
				null,//Address4
				null,//City
				null,//State
				null,//Province
				null,//County
				$ship_to->customer->customer,//Consumer Name
				null,//Consumer Phone No.
				null,//Receiver Name
				null,//Receiver Phone No.
				null,//Freight Charge
				null,//Freight Term
				null,//Price Condition
				null,//Picking Remark
				null,//Shipping Method
			];
		}
		
		return $rows;
	}
	
	private function pdf_to_excel($filename, $po_template, $ship_to){
		$url = ""; $rows = [];
		
		$this->load->library('my_pdf');
		$rows = $this->my_pdf->to_text($filename);
		
		switch($po_template->code){
			case "hiraoka_pre": $rows = $this->hiraoka_pre($rows, $ship_to); break;
			case "hiraoka_sku": $rows = $this->hiraoka_sku($rows, $ship_to); break;
		}
		
		if ($rows){
			$header = [
				"Customer PO No.",
				"Ship To",
				"Currency",
				"Request Arrival Date(YYYYMMDD)",
				"Model",
				"Quantity",
				"Unit Selling Price",
				"Warehouse",
				"Payterm",
				"Shipping Remark",
				"Invoice Remark",
				"Customer RAD(YYYYMMDD)",
				"Customer PO Date(YYYYMMDD)",
				"H Flag",
				"OP Code",
				"Country",
				"Postal Code",
				"Address1",
				"Address2",
				"Address3",
				"Address4",
				"City",
				"State",
				"Province",
				"County",
				"Consumer Name",
				"Consumer Phone No.",
				"Receiver Name",
				"Receiver Phone No.",
				"Freight Charge",
				"Freight Term",
				"Price Condition",
				"Picking Remark",
				"Shipping Method",
			];
			
			//make excel without title
			$url = $this->my_func->generate_excel_report("scm_po.xlsx", null, $header, $rows);
		}
		
		return $url;
	}
	
	private function excel_to_excel($filename, $po_template, $ship_to){
		$url = ""; $rows = [];
		
		switch($po_template->code){
			case "conecta_excel": $rows = $this->conecta_excel($filename, $ship_to); break;
		}
		
		if ($rows){
			$header = [
				"Customer PO No.",
				"Ship To",
				"Currency",
				"Request Arrival Date(YYYYMMDD)",
				"Model",
				"Quantity",
				"Unit Selling Price",
				"Warehouse",
				"Payterm",
				"Shipping Remark",
				"Invoice Remark",
				"Customer RAD(YYYYMMDD)",
				"Customer PO Date(YYYYMMDD)",
				"H Flag",
				"OP Code",
				"Country",
				"Postal Code",
				"Address1",
				"Address2",
				"Address3",
				"Address4",
				"City",
				"State",
				"Province",
				"County",
				"Consumer Name",
				"Consumer Phone No.",
				"Receiver Name",
				"Receiver Phone No.",
				"Freight Charge",
				"Freight Term",
				"Price Condition",
				"Picking Remark",
				"Shipping Method",
			];
			
			//make excel without title
			$url = $this->my_func->generate_excel_report("scm_po.xlsx", null, $header, $rows);
		}
		
		return $url;
	}
	
	public function test(){
		/*
		$filename = './test_files/scm/hiraoka_sku/hiraoka_sku2.pdf';
		$po_template = $this->gen_m->unique("purchase_order_template", "template_id", 2);//hiraoka sku
		$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", 1);//hiraoka
		$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
		
		echo $this->pdf_to_excel($filename, $po_template, $ship_to);
		*/
		$filename = './test_files/scm/conecta/conecta2.xls';
		$po_template = $this->gen_m->unique("purchase_order_template", "template_id", 3);//conecta excel
		$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", 3);//conecta
		$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
		
		echo $this->excel_to_excel($filename, $po_template, $ship_to);
	}
	
	public function convert_po(){
		$type = "error"; $msg = $url = "";
		
		$config = [
			'upload_path'	=> './upload/scm/',
			'allowed_types'	=> 'pdf|xls|xlsx|csv',
			'max_size'		=> 20000,
			'overwrite'		=> TRUE,
			'file_name'		=> 'po_file',
		];
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('po_file')){
			$result = $this->upload->data();
			//print_r($result);
			/*
			Array
			(
				[file_name] => po_file.pdf
				[file_type] => application/pdf
				[file_path] => C:/xampp_lg/htdocs/llamasys/upload/scm/
				[full_path] => C:/xampp_lg/htdocs/llamasys/upload/scm/po_file.pdf
				[raw_name] => po_file
				[orig_name] => po_file.pdf
				[client_name] => test_hiraoka5.pdf
				[file_ext] => .pdf
				[file_size] => 106.61
				[is_image] => 
				[image_width] => 
				[image_height] => 
				[image_type] => 
				[image_size_str] => 
			)
			*/
			
			$po_file = './upload/scm/po_file'.$result["file_ext"];
			$po_template = $this->gen_m->unique("purchase_order_template", "template_id", $this->input->post("po_template"));
			$ship_to = $this->gen_m->unique("customer_ship_to", "ship_to_id", $this->input->post("ship_to"));
			
			if ($po_template and $ship_to){
				$ship_to->customer = $this->gen_m->unique("customer", "customer_id", $ship_to->customer_id);
				
				switch($result["file_ext"]){
					case ".pdf": $url = $this->pdf_to_excel($po_file, $po_template, $ship_to); break;
					case ".xlsx": $url = $this->excel_to_excel($po_file, $po_template, $ship_to); break;
					case ".xls": $url = $this->excel_to_excel($po_file, $po_template, $ship_to); break;
					case ".csv": $url = $this->excel_to_excel($po_file, $po_template, $ship_to); break;
				}
				
				if ($url){
					$type = "success";
					$msg = "PO conversion is completed.";
				}else $msg = "An error occurred. Please try again.";	
			}else $msg = "You must select PO template and customer ship to.";
		}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg, "url" => $url]);
	}
	
	public function index(){
		$ship_tos = $this->gen_m->all("customer_ship_to", [["ship_to_code", "asc"], ["address", "asc"]]);
		foreach($ship_tos as $s){
			$cus = $this->gen_m->unique("customer", "customer_id", $s->customer_id);
			$s->op = $cus->customer." ** ".$cus->bill_to_code." ** ".$s->ship_to_code." ** ".$s->address;
		}
		
		usort($ship_tos, function($a, $b) {
			return strcmp($a->op, $b->op);
		});
		
		$data = [
			"purchase_order_temps" => $this->gen_m->all("purchase_order_template", [["template", "asc"]]),
			"ship_tos" => $ship_tos,
			"main" => "scm/purchase_order/index",
		];
		
		$this->load->view('layout', $data);
	}
}
