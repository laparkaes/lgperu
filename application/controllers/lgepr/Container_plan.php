<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Container_plan extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		
		/* container company, division debugging
		$com_div = $this->gen_m->only_multi("lgepr_container", ["company", "division"], null, ["company", "division"]);
		foreach($com_div as $item){
			print_r($item);
			echo "<br/>";
		}
		*/
		
		$f_com = $this->input->get("f_company");
		$f_step = $this->input->get("f_ctn_step");
		$f_ctn = $this->input->get("f_container");
		
		$w = [
			"eta >=" 	=> date('Y-m-01', strtotime('-2 months')),
		];
		
		if ($f_com) $w["company"] = $f_com;
		
		switch($f_step){
			case "port": 
				$w["ata !="] = null; 
				//$w["picked_up"] = null; 
				//$w["wh_arrival"] = null; 
				break;
			case "temp_wh": 
				$w["picked_up !="] = null; 
				//$w["wh_arrival"] = null; 
				break;
			case "3pl": 
				$w["wh_arrival !="] = null; 
				break;
		}
		
		$l = [
			["field" => "container", "values" => [$f_ctn]],
		];
		
		$o = [
			["eta", "desc"], 
			["container", "asc"],
			["company", "asc"],
			["division", "asc"],
			["model", "asc"],
		];
		
		$containers = $this->gen_m->filter("lgepr_container", false, $w, $l, null, $o);
		
		$data = [
			"f_com"			=> $f_com,
			"f_step"		=> $f_step,
			"f_ctn"			=> $f_ctn,
			"containers"	=> $containers,
			"overflow"		=> "scroll",
			"main"			=> "lgepr/container_plan",
		];
		
		$this->load->view('layout_dashboard', $data);
	}
}
