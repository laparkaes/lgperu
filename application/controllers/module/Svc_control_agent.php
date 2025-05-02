<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Svc_control_agent extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
	
    public function index(){
		
		$distritos = array("Ancón", "Ate", "Barranco", "Breña", "Carabayllo", "Cercado de Lima", "Chaclacayo", "Chorrillos", "Cieneguilla", "Comas", "El Agustino", "Independencia", "Jesús María", "La Molina", "La Victoria", "Lince", "Los Olivos", "Lurigancho-Chosica", "Lurín", "Magdalena del Mar", "Miraflores", "Pachacámac", "Pucusana", "Pueblo Libre", "Puente Piedra", "Punta Hermosa", "Punta Negra", "Rímac", "San Bartolo", "San Borja", "San Isidro", "San Juan de Lurigancho", "San Juan de Miraflores", "San Luis", "San Martín de Porres", "San Miguel", "Santa Anita", "Santa María del Mar", "Santa Rosa", "Santiago de Surco", "Surquillo", "Villa El Salvador", "Villa María del Triunfo");
		
		$dia_semana = array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
		//$tecnicos = ['Pedro', 'Luis', 'Marcos', 'Omar'];
		$service_type = array('Mantenimiento REF', 'Mantenimiento WM');
		$info = $this->gen_m->filter('svc_control', false);
        $data = [
			"state" => $distritos,
			"day" => $dia_semana,
			"service_type" => $service_type,
			"info" => $info,
            "main" => "module/svc_control_agent/index",
        ];

        $this->load->view('layout', $data);
    }
	
	public function send_email($data) {
	
		$current_date = date("Ymd"); // Obtiene la fecha actual en formato YYYYMMDD

		$current_day = date("Y-m-d H:i:s"); // Obtiene la fecha actual en formato YYYY-MM-DD
	
		// Correo automatico para svc
		$data = [
				"data" => $data,
				"current_day" => $current_day,
				];
		
		// Cargar la vista con los datos combinados
		$message = $this->load->view('email/svc_email_report', $data, true);
		$to = ["jose.sarmiento@lge.com", "susanae.jacinto@lge.com", "ricardo.alvarez@lge.com"]; //SVC

		//$cc = '';
		$subject = "[LGEPR_SVC] Notificacion registro de servicios {$current_date}";
		// Enviar el correo electrónico
		$this->my_func->send_email("rpa.espr@lgepartner.com", $to, $subject, $message);
		
	}
	
	public function upload_svc_data(){

		// Obtener los datos del formulario
		$data = array(
			//'technical' 		=> $technical_input,
			'service_type' 		=> $this->input->post('inputService'),
			'service_code' 		=> $this->input->post('inputServiceCode'),
			'register_date' 	=> date('Y-m-d H:i:s'),
			'service_date' 		=> $this->input->post('inputCita'),
			'day' 				=> $this->input->post('inputDay'),
			'client_name' 		=> ucwords($this->input->post('inputClientName')),
			'mobile_number' 	=> $this->input->post('inputMobile'),		
			'total_job' 		=> $this->input->post('inputTotalJobs'),
			'available_job' 	=> $this->input->post('inputAvailable')-1,			
			'city'				=> ucwords($this->input->post('inputCity')),
			'district' 			=> $this->input->post('inputDistrict'),			
			'status' 			=> 'Registered',
			'service_comment'   => $this->input->post('inputComment') ?? ''
		);

		$insert_id = $this->gen_m->insert("svc_control", $data);
		
		// send email		
		$this->send_email($data);
		
        if ($insert_id) {
            // Success
            echo json_encode(["success" => true, "message" => "Data inserted successfully."]);
        } else {
            // Failure
            echo json_encode(["success" => false, "message" => "Failed to insert data."]);
        }
	}
	
	public function check_availability() {
        $date = $this->input->post('date');
		$service = $this->input->post('service');
		$this->db->where('service_date', $date);
		$this->db->where('service_type', $service);
		$this->db->order_by('available_job', 'ASC'); // Ordenar por register_date descendente
		$query = $this->db->get('svc_control');

		$num_rows = $query->num_rows();
		//$available = ($num_rows === 1); // Si no hay filas, está disponible
		
		 if ($num_rows > 0) {
			$row = $query->row();
			$service_type = $row->service_type;
			$total_job = $row->total_job;
			$available_job = $row->available_job;
			$available = true; // Indica que se encontró una cita
		} else {
			$service_type = null;
			$total_job = null; // No se encontró cita
			$available_job = null;
			$available = false; // Indica que no se encontró una cita
		}
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('available' => $available, 'service_type' => $service_type, 'total_job' => $total_job, 'available_job' => $available_job)));
    }
	
	public function update_register(){
		
		$data = json_decode(file_get_contents('php://input'), true);
		$data['updated'] = date('Y-m-d H:i:s');
		//print_r($data);
        if ($data) {
            $id = $data['svc_control_id'];
            unset($data['svc_control_id']); // Eliminar el ID del array de datos
			//print_r($data);
            $result = $this->gen_m->update('svc_control',array('svc_control_id' => $id), $data);

            if ($result) {
				$response = array('success' => true, 'message' => 'Registro actualizado correctamente.');
			} else {
				$response = array('success' => false, 'message' => 'Error al actualizar el registro.');
			}
		} else {
			$response = array('success' => false, 'message' => 'Datos inválidos.');
		}

		header('Content-Type: application/json');
		echo json_encode($response);

	}		
}