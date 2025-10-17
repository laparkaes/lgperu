<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Ar_carta_fianza extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){	
		$data = [
			"stocks"	=> $this->gen_m->filter("ar_carta_fianza", false, null, null, null, "", 100),
			"main" 		=> "data_upload/ar_carta_fianza/index",
		];
		
		$this->load->view('layout', $data);
	}
		
	public function process(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//delete all rows lgepr_stock 
		$this->gen_m->truncate("ar_carta_fianza");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/ar_carta_fianza.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);

		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('F1')->getValue()),
			trim($sheet->getCell('G1')->getValue()),
			trim($sheet->getCell('H1')->getValue()),
			trim($sheet->getCell('I1')->getValue())
		];

		$header = ["Customer Code", "Customer Name", "Start Date", "End Date", "Currecy Code", "Credit Amount"];
		
		//header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

		
		
		if ($is_ok){
			$updated = date("Y-m-d");
			$email_sent = 0;
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 100;

			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"customer_code" 							=> trim($sheet->getCell('A'.$i)->getValue()),
					"customer_name" 							=> trim($sheet->getCell('B'.$i)->getValue()),
					"start_date" 								=> trim($sheet->getCell('F'.$i)->getValue()),
					"end_date" 									=> trim($sheet->getCell('G'.$i)->getValue()),
					"currecy_code" 								=> trim($sheet->getCell('H'.$i)->getValue()),
					"credit_amount"								=> trim($sheet->getCell('I'.$i)->getValue()),
					"total_credit_amount" 						=> trim($sheet->getCell('K'.$i)->getValue()),
					"collateral_name" 							=> trim($sheet->getCell('L'.$i)->getValue()),
					"collateral_type_name"						=> trim($sheet->getCell('M'.$i)->getValue()),
					"collateral_owner_name"						=> trim($sheet->getCell('U'.$i)->getValue()),
					"due_date_maturity_date"					=> trim($sheet->getCell('X'.$i)->getValue()),
					"status"									=> trim($sheet->getCell('Y'.$i)->getValue()),
					"providor"									=> trim($sheet->getCell('AI'.$i)->getValue()),		
					"comment_text"								=> trim($sheet->getCell('AJ'.$i)->getValue()),
					"updated"									=> $updated,
				];
					
				$batch_data[]=$row;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("ar_carta_fianza", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
			}

			if (!empty($batch_data)) {
				$this->gen_m->insert_m("ar_carta_fianza", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			$this->db->trans_complete();
			return $msg;		
		}else return "";
		//$this->update_model_category();
	}
	
	public function send_email() {	
	//llamasys/data_upload/ar_carta_fianza/send_email
    // Obtener la fecha actual
    $today = new DateTime();

	$current_date = date("Ymd"); // Obtiene la fecha actual en formato YYYYMMDD
    // Obtener los datos de la base de datos con la condición dada
    $values_carta = $this->gen_m->filter('ar_carta_fianza', false, ['collateral_name' => 'Securities', 'credit_amount >' => 0]);
	
		if (!empty($values_carta)) {
			// Inicializar arrays para almacenar los datos
			$cartas_15_dias = [];
			$cartas_7_dias = [];				
			foreach ($values_carta as $cliente) {
				// Verificar que tiene una fecha válida
				if (!empty($cliente->due_date_maturity_date)) {
					$fecha_vencimiento = DateTime::createFromFormat('Y-m-d', $cliente->due_date_maturity_date);
					if ($fecha_vencimiento) {
						// Calcular la diferencia de días
						$diferencia = $today->diff($fecha_vencimiento)->days;
						
						// Si la diferencia es exactamente menor o igual a 15 días, enviar correo
						if ($diferencia == 15) {
							$cartas_15_dias[] = $cliente;
						}elseif($diferencia == 7){
							$cartas_7_dias[] = $cliente;
						}	
					}
				}
			}
			
			// Combinar los datos si hay cartas con ambas diferencias
			if (!empty($cartas_15_dias) || !empty($cartas_7_dias)) {
				$to = ["juan.depaz@lge.com", "diana.sanchez@lge.com", "madeleine.correa@lge.com", "paola.avalos@lge.com", "enrique.salazar@lge.com", "georgio.park@lge.com", "roberto.kawano@lge.com", "ricardo.alvarez@lge.com"];

				$subject = "[LGEPR AR] Vencimiento Carta Fianza {$current_date}";

				$data = [
					'cartas_15_dias' => $cartas_15_dias,
					'cartas_7_dias' => $cartas_7_dias
				];
				
				$message = $this->load->view('email/hr_email_alert', $data, true);

				$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
			}
		}
	}


	public function update(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 90000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'ar_carta_fianza.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->process();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
}
