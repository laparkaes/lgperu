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
		
		$pr_exclude = ['PR009297', // Wonshik Woo
					   'PR009182', // Cho.Hyun(Andre)
					   'PR009230', // Seongmin Lee	
					   'PR009113', // Oh Sangjun
					   'PR009226', // Sangkyu Lee
					   'PR100004', // KIM JE HEON
					   'PR009329'  // Han Muhyun
					   ]; //exclude PR
		
		$fecha_actual = date("Y-m-d");
		// Inciar con fecha actual para ejecucion de codigo diaria
		//$fecha_actual = '2025-04-15';
		$attendance_summ = $this->gen_m->filter('v_hr_attendance_summary', false, ['work_date' => $fecha_actual]);
		foreach($attendance_summ as $item) $att_summ[$item->pr] = $item;
		
		$att_sch = [];
		$attendance_sch = $this->gen_m->filter('hr_schedule', false, null, null, null, [['date_from', 'ASC']]);
		foreach($attendance_sch as $i=>$item) $att_sch[$item->pr] = $item;
		
		
		$employee_number = $this->gen_m->filter_select('hr_employee', false, ['employee_number', 'ep_mail', 'name'], ['working'=>1], null, null, [['employee_number', 'ASC']] );		
		foreach($employee_number as $item) $emp_hr_info[$item->employee_number] = $item;
		//echo "<pre>"; print_r($att_summ); echo "</pre>";
		
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

		$this->send_email($list_tardiness, $list_no_work);
		
	}
	
	public function calcularDiferenciaTiempo($first_access, $work_start) {
		
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
		//llamasys/module/hr_email_alarm/process

		$current_date = date("Ymd"); // Obtiene la fecha actual en formato YYYYMMDD
		// Obtener los datos de la base de datos con la condición dada
		
		$count = 1;
		$current_day = date('Ymd');
		$info_total = [];
		foreach($list_tardiness as $item){
			$delay = $this->calcularDiferenciaTiempo($item['first_access'], $item['work_start']);
			$info_total[$item['pr']][] = $item;
			$to = $item['ep_mail'] . '@lge.com';

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
		
		$to = 'carlos.mego@lge.com'; 
		//$cc = '';
		$subject = "[LGEPR_HR] Reporte de asistencias {$current_day}";
		// Enviar el correo electrónico
		$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
		
	}
	
}
