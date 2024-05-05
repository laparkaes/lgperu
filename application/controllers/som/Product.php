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
		//421
		
		$this->gen_m->update("product", ["product_id" => 21], ["category_id" => null, "updated" => date('Y-m-d H:i:s', time())]);
		
		$products = $this->gen_m->all("product", [["updated", "desc"]]);
		print_r($products);
		
		$data = [
			"purchase_order_temps" => $this->gen_m->all("product", [["category_id", "asc"], ["model", "asc"]]),
			"main" => "som/product/index",
		];
		
		//$this->load->view('layout', $data);
	}
}
