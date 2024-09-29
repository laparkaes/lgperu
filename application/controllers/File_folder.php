<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File_folder extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$this->load->helper('directory');
		$this->load->view("file_folder/index", ["files" => directory_map('./upload_file/')]);
	}
	
	public function upload(){
		$config = [
			'upload_path'	=> './upload_file/',
			'allowed_types'	=> '*',
			//'max_size'		=> 10000,
			'overwrite'		=> False,
			//'file_name'		=> 'scm_order_file',
		];
		$this->load->library('upload', $config);
			
		if ($this->upload->do_upload('filename')) $this->session->set_flashdata('success_msg', 'File has been uploaded.');
		else $this->session->set_flashdata('error_msg', str_replace("p>", "div>", $this->upload->display_errors()));
		
		echo "end";
	}
	
	public function del($f){
		$this->load->helper('file');
		
		$file_path = "./upload_file/".base64_decode($f);
		if (file_exists($file_path)) {
            if (unlink($file_path)) $this->session->set_flashdata('success_msg', 'File has been removed.');
            else $this->session->set_flashdata('error_msg', 'Failed.');
        }else $this->session->set_flashdata('error_msg', 'File no exists.');
		
		redirect("./file_folder");
	}
}
