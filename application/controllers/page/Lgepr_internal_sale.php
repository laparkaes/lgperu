<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr_internal_sale extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
        $this->load->model('Internal_sale_model', 'int_sale');
        $this->load->library(['upload', 'image_lib']);
	}
		
	public function index(){
		/*
		$w = [
			"registered >=" => "2024-11-01 00:00:00",
		];
		$records = $this->gen_m->filter("pi_listening", false, $w, null, null, [["dptTo" , "asc"]]);
		
		$data = [
			"records" => $records,
			"main" => "module/pi_listening/index",
		];
		
		$this->load->view('layout', $data);
		*/
		
		echo '<a href="'.base_url().'page/lgepr_internal_sale/create">go</a>';
	}
	
	public function create(){
		
		$data["overflow"] = "scroll";
		$data["main"] = "page/lgepr_internal_sale/create";
		
		$this->load->view('layout_dashboard', $data);
	}
	
	public function insert(){
		$data = $this->input->post();
		
		$errors = $this->data_validation($data, $_FILES['images']);
		
		if (!$errors){
			$data["created_at"] = date('Y-m-d H:i:s');
			$product_id = $this->int_sale->insert_product($data);

			// 이미지 업로드 처리
			list($images, $upload_errors) = $this->upload_multiple_images($_FILES['images'], $product_id);

			// 이미지 경로를 DB에 저장
			if (!empty($images)) {
				$this->int_sale->insert_images($images);
			}
			
			if (!empty($upload_errors)) $errors = array_merge($errors, $upload_errors);
			else redirect('page/lgepr_internal_sale');
		}
		
		if ($errors) redirect('page/lgepr_internal_sale/create');
	}
	
	private function data_validation($data, $files){
		
		print_r($data);
		echo "<br/><br/><br/>";
		print_r($files);
		
		return ["aaaa"];
	}
	
	private function upload_multiple_images($files, $product_id)
	{
		$upload_path = './upload/internal_sale_images/';

		// 업로드 폴더 생성
		if (!is_dir($upload_path)) {
			mkdir($upload_path, 0755, TRUE);
		}

		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = 'jpg|png|jpeg';
		//$config['max_size'] = 2048; // 2MB 제한

		$upload_errors = [];
		$uploaded_images = [];
		$this->load->library('upload');

		foreach ($files['name'] as $key => $image) {
			if ($files['size'][$key]){
				$_FILES['single_image']['name'] = $files['name'][$key];
				$_FILES['single_image']['type'] = $files['type'][$key];
				$_FILES['single_image']['tmp_name'] = $files['tmp_name'][$key];
				$_FILES['single_image']['error'] = $files['error'][$key];
				$_FILES['single_image']['size'] = $files['size'][$key];

				$config['upload_path'] = $upload_path;
				$this->upload->initialize($config);

				if ($this->upload->do_upload('single_image')) {
					$upload_data = $this->upload->data();
					$original_path = $upload_data['full_path'];

					// 새로운 파일명 생성
					$new_filename = $product_id . '_' . $key . $upload_data['file_ext'];
					$new_filepath = $upload_path . $new_filename;

					if (rename($original_path, $new_filepath)) {
						// 리사이즈 처리 (필요한 경우)
						if ($upload_data['image_width'] > 1200) {
							$this->resize_image($new_filepath, 1200);
						}

						$uploaded_images[] = [
							'product_id' => $product_id,
							'image_path' => $new_filename,
						];
					} else {
						// 업로드 실패 시 오류 메시지 저장
						$upload_errors[] = "Failed to rename file: " . $upload_data['file_name'];
					}
				}
			}
		}

		return [$uploaded_images, $upload_errors];
	}

	private function resize_image($path, $width)
	{
		$config['image_library'] = 'gd2';
		$config['source_image'] = $path;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $width;

		$this->image_lib->initialize($config);

		if (!$this->image_lib->resize()) {
			echo $this->image_lib->display_errors();
		}

		$this->image_lib->clear();
	}
	
}
