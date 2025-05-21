<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Scm_direct_dispatch extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}

	public function index(){
		
		
		return;
		
		$data = [
			"po_templates" => $this->gen_m->filter("scm_purchase_order_template", true, ["valid" => true], null, null, [["template", "asc"]]),
			"ship_tos" => $this->gen_m->filter("scm_ship_to", false, null, null, null, [["bill_to_name", "asc"], ["address", "asc"]]),
			"main" => "module/scm_purchase_order/index",
		];
		
		$this->load->view('layout', $data);
	}

}
