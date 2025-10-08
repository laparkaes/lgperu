<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr_order extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		$orders = $this->gen_m->filter("lgepr_order", false, null, null, null, [["order_date", "desc"], ["order_line", "asc"]], 1000);
		$header = $orders[0];
		
		echo "<table>";
		
		echo "<tr style='position: sticky;top: 0;'>";
		foreach($header as $k => $h) echo "<td style='background-color: white;'>".$k."</td>";
		echo "</tr>";
		
		foreach($orders as $item){
			echo "<tr>";
			foreach($item as $h) echo "<td>".$h."</td>";
			echo "</tr>";	
		}
		
		echo "</table>";
		
	}
	
}
