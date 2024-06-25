<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Product extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$lines = $this->gen_m->all("product_line", [["line", "asc"]]);
		$lines_arr = [];
		$lines_arr[-1] = null;
		foreach($lines as $line) $lines_arr[$line->line_id] = $line;
		
		$categories = $this->gen_m->all("product_category", [["category", "asc"]]);
		$categories_arr = [];
		foreach($categories as $cat) $categories_arr[$cat->category_id] = $cat;
		
		$data = [
			"lines" => $lines,
			"lines_arr" => $lines_arr,
			"categories" => $categories,
			"categories_arr" => $categories_arr,
			"products" => $this->gen_m->all("product", [["updated", "desc"], ["model", "desc"]]),
			"main" => "module/product/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function assign_category(){
		echo "Auto category assign based on one record of same product line.<br/><br/>";
		
		$line_ids = [];
		
		$aux = $this->gen_m->get_group("product", ["line_id >" => 0, "category_id >" => 0], ["line_id", "category_id"]);
		foreach($aux as $a){
			if (!array_key_exists($a->line_id, $line_ids)) $line_ids[$a->line_id] = [];
			$line_ids[$a->line_id][] = $a->category_id;
		}
		
		foreach($line_ids as $line_id => $cat_ids)
			if (count($cat_ids) == 1)
				$this->gen_m->update("product", ["line_id" => $line_id], ["category_id" => $cat_ids[0]]);
			
		echo "Fin!";
	}
	
	public function create(){
		$data = [
			"lines" => $this->gen_m->all("product_line", [["line", "asc"]]),
			"categories" => $this->gen_m->all("product_category", [["category", "asc"]]),
			"main" => "module/product/create",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function edit($product_id){
		$data = [
			"product"		=> $this->gen_m->unique("product", "product_id", "product_id"),
			"lines"			=> $this->gen_m->all("product_line", [["line", "asc"]]),
			"categories"	=> $this->gen_m->all("product_category", [["category", "asc"]]),
			"main" 			=> "module/product/edit",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function test(){
		$prods = [
			"AK-W240DCA0.ADGTLAT",
			"ACAH045LETB.AAAAAAA",
			"USC9S.DGBRPV",
			"49VL5G-A.AWF",
			"27KC3PK-C.AWFQ",
			"22MR410-B.AWFQ",
			"24MS500-B.AWF",
			"GS66SXN.APZGLPR",
			"GS66SDP.APZGLPR",
			"GS66SXT.AMCGLPR",
			"OLED77G4PSA.AWF",
			"50QNED80TSA.AWF",
			"55NANO80TSA.AWF",
			"50UT7300PSA.AWFQ",
			"OLED83C4PSA.AWF",
			"65NANO80TSA.AWF",
			"65UT7300PSA.AWFQ",
			"50NANO80TSA.AWH",
			"55NANO80TSA.AWH",
			"OLED77Z3PSA.AWF",
			"WT17BV6T.ABMGLGP",
			"DF10BVC2S6.BBLGLGP",
			"WT17DV6T.ASFGLGP",
			"WT19BV6T.ABMGLGP",
			"WT19DV6T.ASFGLGP",
			"49UM5N-E.AWF",
			"22SM3G-B.AWF",
			"75QNED90TSA.AWF",
			"55UT7300PSA.AWFQ",
			"WT16BVTB.ABMGLGP",
			"TS1605NS.ASFGLGP",
			"WD12VVC3S6C.ASSGLGP",
			"PL2.DPERLLK",
			"55UK6200PSA.AWF",
			"75QNED85SQA.AWF",
			"AN-MR19BA.AWP",
			"75QNED80SQA.AWF",
			"TRIPOAUD19.PRO",
			"PL2S.DPERLLK",
		];
		
		foreach($prods as $p){
			$s = ["model", "item_division", "product_level1_name", "product_level2_name", "product_level3_name", "product_level4_name", "product_level4_code", "model_category"];
			$s_ = ["model", "item_division", "pl1_name as product_level1_name", "pl2_name as product_level2_name", "pl3_name as product_level3_name", "pl4_name as product_level4_name", "product_level4_code", "model_category"];
			$w = ["model" => $p];
			
			echo $p."<br/>";
			$so = $this->gen_m->filter_select("obs_gerp_sales_order", false, $s, $w, null, null, [["create_date", "desc"]], 1, 0);
			if (!$so) $so = $this->gen_m->filter_select("dash_sales_order_inquiry", false, $s_, $w, null, null, [["create_date", "desc"]], 1, 0);
			
			if ($so){
				$lvl4 = $this->gen_m->filter("product_line", false, ["line" => $so[0]->product_level4_name]);
				if ($lvl4){
					print_r($lvl4); echo "<br/>";
				}
				echo $so[0]->product_level4_name."<br/>";
				
				print_r($so); echo "<br/><br/>";
			}else echo "Manual insert.";  echo "<br/><br/>";
			
			
		}
	}
}
