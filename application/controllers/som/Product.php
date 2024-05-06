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
			"main" => "som/product/index",
		];
		
		$this->load->view('layout', $data);
	}
}
