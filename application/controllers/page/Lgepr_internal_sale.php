<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr_internal_sale extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		
        $this->load->model('Internal_sale_model');
        $this->load->helper(['form', 'url']);
        $this->load->library(['form_validation', 'upload']);
	}
		
	public function index(){
		
		$w = [
			"registered >=" => "2024-11-01 00:00:00",
		];
		$records = $this->gen_m->filter("pi_listening", false, $w, null, null, [["dptTo" , "asc"]]);
		
		$data = [
			"records" => $records,
			"main" => "module/pi_listening/index",
		];
		
		$this->load->view('layout', $data);
	}
	
	public function create()
	{
		$this->form_validation->set_rules('category', 'Category', 'required');
		$this->form_validation->set_rules('model', 'Model', 'required');
		$this->form_validation->set_rules('grade', 'Grade', 'required');
		$this->form_validation->set_rules('created_at', 'Created At', 'required');
		$this->form_validation->set_rules('end_date', 'End Date', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('page/lgepr_internal_sale/create', ['errors' => $this->form_validation->error_array()]);
		} else {
			// 데이터 저장
			$data = [
				'category' => $this->input->post('category'),
				'model' => $this->input->post('model'),
				'grade' => $this->input->post('grade'),
				'created_at' => $this->input->post('created_at'),
				'end_date' => $this->input->post('end_date')
			];

			$product_id = $this->Product_model->insert_product($data);

			// 이미지 업로드
			$images = $this->upload_individual_images($product_id);

			// 이미지 경로를 DB에 저장
			if (!empty($images)) {
				$this->Product_model->insert_images($images);
			}

			redirect('products/success');
		}
	}

	private function upload_individual_images($product_id)
	{
		$upload_path = './upload/internal_sale_images/';

		// 업로드 폴더 생성
		if (!is_dir($upload_path)) {
			mkdir($upload_path, 0755, TRUE);
		}

		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = 'jpg|png|jpeg';
		$config['max_size'] = 2048; // 2MB 제한

		$uploaded_images = [];

		for ($i = 1; $i <= 5; $i++) {
			$file_key = "image_$i";

			if (!empty($_FILES[$file_key]['name'])) {
				$this->load->library('upload', $config);

				if ($this->upload->do_upload($file_key)) {
					$upload_data = $this->upload->data();
					$original_path = $upload_data['full_path'];

					// 리사이즈 처리
					if ($upload_data['image_width'] > 1200) {
						$this->resize_image($original_path, 1200);
					}

					$uploaded_images[] = [
						'product_id' => $product_id,
						'image_path' => 'internal_sale_images/' . $upload_data['file_name']
					];
				}
			}
		}

		return $uploaded_images;
	}

	private function resize_image($path, $width)
	{
		$config['image_library'] = 'gd2';
		$config['source_image'] = $path;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $width;

		$this->load->library('image_lib', $config);

		if (!$this->image_lib->resize()) {
			echo $this->image_lib->display_errors();
		}

		$this->image_lib->clear();
	}
	
}
