<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Hr_email_alarm extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
	public function index(){
		$data = [
			"main" => "email/email_tardiness",
		];
		
		$this->load->view('layout', $data);
	}
		
	public function process(){	
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		$pr_exclude = ['PR009182', // Cho.Hyun(Andre)
					   'PR009297', // Wonshik Woo
					   'PR009226', // Sangkyu Lee
					   'PR100004', // KIM JE HEON
					   'PR009113', // Oh Sangjun
					   'PR009329', // Han Muhyun
					   'PR009230', // Seongmin Lee
					   'PR100017', // Ga yeon Park
					   'PR009224', // Sang Uk Jeong
					   'PR008210' // Byung Seok Hwang
					   //Add more exceptions
					   ]; //exclude PR
		
		$fecha_actual = date("Y-m-d");
		// Inciar con fecha actual para ejecucion de codigo diaria
		//$fecha_actual = '2025-04-11';
		$attendance_summ = $this->gen_m->filter('v_hr_attendance_summary', false, ['work_date' => $fecha_actual]); // Validar que haya data en el dia.
		if (empty($attendance_summ)) {
			$this->send_issue_email();
			return;
		}
		foreach($attendance_summ as $item) $att_summ[$item->pr] = $item;
		
		$att_sch = [];
		$attendance_sch = $this->gen_m->filter('hr_schedule', false, null, null, null, [['date_from', 'ASC']]);
		foreach($attendance_sch as $i=>$item) $att_sch[$item->pr] = $item;
		
		$employee_number = $this->gen_m->filter_select('hr_employee', false, ['employee_number', 'ep_mail', 'name'], ['working'=>1, 'ep_mail !=' => null, 'ep_mail !=' => ''], null, null, [['employee_number', 'ASC']] );		
		foreach($employee_number as $item) $emp_hr_info[$item->employee_number] = $item;
		
		//PR unicos
		$employee_unique_n_exclude = [];
		$employee_unique = [];
		foreach ($employee_number as $item) {
			//print_r($item); echo '<br>';
			if (!in_array($item->employee_number, $employee_unique)) {
				if(!in_array($item->employee_number, $pr_exclude)){
					$employee_unique[] = $item->employee_number;
				}
			}
		}
		
		//Lista de absentismos
		$att_exc = [];
		$att_exception = $this->gen_m->filter_select('hr_attendance_exception', false, ['pr', 'exc_date', 'type', 'remark'], ['exc_date' => $fecha_actual]);
		foreach($att_exception as $item) $att_exc[$item->pr] = $item;
		if (isset($att_exc['LGEPR'])) {
			if ($att_exc['LGEPR']->type === 'H') return; // Not send notification on holidays
		}
		
		$list_tardiness = [];
		$list_no_work = [];
		
		
		foreach($employee_unique as $emp_number){
			if(isset($att_sch[$emp_number])){
				
				if (isset($att_summ[$emp_number])) {
					$att_summ[$emp_number]->first_access = (DateTime::createFromFormat('Y-m-d H:i:s', $att_summ[$emp_number]->first_access))->format('H:i:s');
					
					if($att_summ[$emp_number]->first_access >= $att_sch[$emp_number]->work_start){
						
						if (isset($att_exc[$emp_number]) && $att_exc[$emp_number]->type !== 'NEF' && $att_exc[$emp_number]->type !== 'EF' && $att_exc[$emp_number]->type !== 'H'){
							continue;
						}
						else{
							$list_tardiness[$emp_number] = ['pr'=>$emp_number, 'ep_mail'=>$emp_hr_info[$emp_number]->ep_mail, 'name'=>$emp_hr_info[$emp_number]->name, 'work_start'=>$att_sch[$emp_number]->work_start, 'first_access'=>$att_summ[$emp_number]->first_access];
						}
					}
					elseif(empty($att_summ[$emp_number]->first_access)){
						$list_no_work[$emp_number] = [$emp_number, 'ep_mail'=>$emp_hr_info[$emp_number]->ep_mail, 'name'=>$emp_hr_info[$emp_number]->name, 'work_start'=>$att_sch[$emp_number]->work_start, "first_access" => "No registrada"]; 
					}
				}
				else {
					$list_no_work[$emp_number] = ['pr'=>$emp_number, 'ep_mail'=>$emp_hr_info[$emp_number]->ep_mail, 'name'=>$emp_hr_info[$emp_number]->name, 'work_start'=>$att_sch[$emp_number]->work_start, "first_access" => "No registrada"];
				}
			}
		}
		//echo "<pre>"; print_r($list_no_work); echo "</pre>";
		//echo "<pre>"; print_r($list_tardiness); echo "</pre>";
		$this->send_email($list_tardiness, $list_no_work);
		
	}
	
	public function send_issue_email() {
		$date_formatted = date('Ymd');
		
		$subject = "[LGEPR] Registros de asistencias vacios - " . $date_formatted;
		$message = $this->generate_warning_html_message();
		$to = ['ricardo.alvarez@lge.com', 'roberto.kawano@lge.com'];
		$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
	}
	
	private function generate_warning_html_message() {	

		$date_formatted_display = date('Y-m-d');
		
		$html = '
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Alerta de Integridad de Datos</title>
			<style>
				body {
					font-family: \'Arial\', sans-serif;
					margin: 0;
					padding: 0;
					background-color: #f4f7fa;
				}
				.email-container {
					width: 100%;
					max-width: 1000px;
					margin: 0 auto;
					background-color: #ffffff;
					border-radius: 8px;
					padding: 20px;
					box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
				}
				.header {
					background-color: #f0ece4;
					color: #333;
					padding: 15px;
					text-align: center;
					border-radius: 8px 8px 0 0;
				}
				.header h1 {
					font-size: 24px;
					margin: 0;
				}
				.header img {
					width: 50px;
					margin-bottom: 10px;
				}
				.content {
					margin-top: 20px;
					font-size: 16px;
					color: #333;
				}
				.content p {
					line-height: 1.6;
				}
				.highlight-date {
					color: #333; 
					font-weight: bold;
					font-size: 1.1em;
					padding: 5px 10px;
					background-color: #f0ece4; 
					border-radius: 4px;
					border: 1px dashed #ffc107;
					display: inline-block; 
				}
				.highlight {
					color: #007BFF;
					font-weight: bold;
				}
				.table-container {
					margin-top: 20px;
					width: 100%;
					border-collapse: collapse;
					font-size: 14px;
				}
				.table-container th, .table-container td {
					border: 1px solid #ddd;
					padding: 8px;
					text-align: center;
				}
				.table-container th {
					background-color: #f2f2f2;
					color: #333;
				}
				.table-container tr:nth-child(even) {
					background-color: #f9f9f9;
				}
				.footer {
					margin-top: 30px;
					font-size: 12px;
					color: #777;
					text-align: center;
				}
				.footer p {
					margin: 5px 0;
				}
				.footer a {
					color: #007BFF;
					text-decoration: none;
				}
				.icon {
					width: 20px;
					height: 20px;
					margin-right: 8px;
					vertical-align: middle;
				}
			</style>
		</head>

		<body>

			<div class="email-container">
				<div class="header">
					<img src="https://www.lg.com/content/dam/lge/common/logo/logo-lg-100-44.svg" alt="Logo"> <h1>Registro de asistencia no encontrado</h1>
				</div>

				<div class="content">
					<p>Se detecto un problema al intentar enviar las notificaciones de asistencias</p>
					
					<p style="text-align: center;">
						<span class="highlight-date">
							Fecha del Problema: ' . htmlspecialchars($date_formatted_display) . '
						</span>
					</p>
					
					<div class="alert-box">
						<strong>Motivo:</strong> No se encontraron registros de marcaciones validos o la data esta vacia.
					</div>
					
					<p>Revisar la correcta subida de la data de asistencias al Llamasys.</p>
				
				</div>

				<div class="footer">
					<p>Este es un mensaje automatico del Sistema de Notificaciones RPA. Por favor, no responder a este correo.</p>
				</div>
			</div>

		</body>
		</html>';
		
		return $html;
	}

	private function calcularDiferenciaTiempo($first_access, $work_start) {
		
		if(DateTime::createFromFormat('H:i:s', $first_access) !== false){
			// Convertir las cadenas de tiempo a objetos DateTime
			$first_access_dt = DateTime::createFromFormat("H:i:s", $first_access);
			$work_start_dt = DateTime::createFromFormat("H:i:s", $work_start);

			// Calcular la diferencia
			$diff = $first_access_dt->diff($work_start_dt);

			// Obtener la diferencia en horas y minutos
			$horas = $diff->h;
			$minutos = $diff->i;
			$segundos = $diff->s;
			// Formatear el resultado
			$resultado = "";
			if ($horas > 0) {
				$resultado .= $horas . "h ";
			}
			$resultado .= $minutos . "min" .  " ". $segundos ."s";
		}
		else $resultado = '-';

		return $resultado;
	}
	
	public function send_email($list_tardiness, $list_no_work) {	
		//llamasys/module/email_alarm/send_email

		$current_date = date("Ymd"); // Obtiene la fecha actual en formato YYYYMMDD
		// Obtener los datos de la base de datos con la condición dada
		
		$count = 1;
		$current_day = date('Ymd');
		$info_total = [];
		foreach($list_tardiness as $item){
			$delay = $this->calcularDiferenciaTiempo($item['first_access'], $item['work_start']);
			$info_total[$item['pr']][] = $item;
			
			$to = $item['ep_mail'] . '@lge.com';
			//$cc = '';
			$subject = "[LGEPR_HR] Notificacion de tardanza {$current_day}";
			$data = [
					'info_pr' => $item,
					'current_day' => $current_day,
					'current_day_format' => date('d/m/Y'),
					'delay' => $delay
					];
			// Cargar la vista con los datos combinados
			$message = $this->load->view('email/email_tardiness', $data, true);

			// Enviar el correo electrónico
			$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
		}
		
		foreach($list_no_work as $item){
			$info_total[$item['pr']][] = $item;
			$to = $item['ep_mail'] . '@lge.com';
			//$cc = '';
			$subject = "[LGEPR_HR] Notificacion marcacion no registrada {$current_day}";
			$data = [
					'info_pr' => $item,
					'current_day_format' => date('d/m/Y'),
					'current_day' => $current_day,
					];
			// Cargar la vista con los datos combinados
			$message = $this->load->view('email/email_absence', $data, true);

			// Enviar el correo electrónico
			$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
		}
		
		// Correo acumulativo para RRHH
		$delay_total = [];
		foreach($info_total as $item){
			$delay_total[$item[0]['pr']] = $this->calcularDiferenciaTiempo($item[0]['first_access'], $item[0]['work_start']);
		}
		$data = [
					'info' => $info_total,
					'current_day' => $current_day,
					'current_day_format' => date('d/m/Y'),
					'delay' => $delay_total
					];
		// Cargar la vista con los datos combinados
		$message = $this->load->view('email/email_report', $data, true);				
		$to = 'carlos.mego@lge.com'; //RRHHH
		//$cc = '';
		$subject = "[LGEPR_HR] Reporte de asistencias {$current_day}";
		// Enviar el correo electrónico
		$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
		
	}
	
}
