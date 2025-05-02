<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Svc_control_adm extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}

    public function index(){
		$distritos = array("Ancón", "Ate", "Barranco", "Carabayllo", "Cercado de Lima", "Chaclacayo", "Chorrillos", "Cieneguilla", "Comas", "El Agustino", "Independencia", "Jesus Maria", "La Molina", "La Victoria", "Lince", "Los Olivos", "Lurin", "Magdalena del Mar", "Miraflores", "Pachacamac", "Pueblo Libre", "Puente Piedra", "Rímac", "San Borja", "San Isidro", "San Juan de Lurigancho", "San Juan de Miraflores", "San Luis", "San Martin de Porres", "San Miguel", "Santa Anita", "Santa Maria del Mar", "Santiago de Surco", "Surquillo", "Villa El Salvador", "Villa Maria del Triunfo");
		
		$dia_semana = array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
		$service_type = array('Mantenimiento REF', 'Mantenimiento WM');
		$status = array('Registered', 'Assigned', 'Finished', 'Postponed');
		//$months = array('Enero','Febrero','Marzo','Abril', 'Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		$info = $this->gen_m->filter('svc_control', false, ['admin_register_date' => null], null, null, [['register_date', 'desc'], ['service_date', 'desc']]);
		$technical = ['Luis', 'Pedro', 'Carlos', 'Marcos'];
        $data = [
			"state" => $distritos,
			"day" => $dia_semana,
			"status" => $status,
			"info" => $info,
			"technical" => $technical,
			"service_type" => $service_type,
            "main" => "module/svc_control_adm/index",
        ];

        $this->load->view('layout', $data);
    }
	
	public function obtener_tipos_servicio_por_fecha() {
        $fecha_cita = $this->input->get('fecha');
		//print_r($fecha_cita);
        if ($fecha_cita) {
            $tipos_servicio = $this->get_unique_service_types_by_date($fecha_cita);
            echo json_encode($tipos_servicio);
        } else {
            echo json_encode([]);
        }
    }
	
	public function get_unique_service_types_by_date($fecha_cita) {
        $this->db->select('DISTINCT(service_type)');
        $this->db->where('service_date', $fecha_cita);
        $this->db->where('admin_register_date IS NOT NULL');
        $this->db->order_by('service_type');
        $query = $this->db->get('svc_control');

        if ($query->num_rows() > 0) {
            return array_column($query->result_array(), 'service_type');
        } else {
            return [];
        }
    }
	
	public function verificar_pendientes() {
        if ($this->input->post()) {
            $service_date = $this->input->post('service_date');
            $service_type = $this->input->post('service_type');

            $pendientes = $this->obtener_pendientes($service_date, $service_type);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($pendientes));
        } else {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Solicitud incorrecta']));
        }
    }
	
	public function obtener_pendientes() { 
        if ($this->input->post()) {
            $service_date = $this->input->post('service_date');
            $service_type = $this->input->post('service_type');

            $pendientes = $this->_obtener_pendientes_por_fecha_tipo($service_date, $service_type);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($pendientes));
        } else {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Solicitud incorrecta']));
        }
    }

    private function _obtener_pendientes_por_fecha_tipo($service_date, $service_type) {
        $this->db->where('admin_register_date IS NULL', null, false);
        $this->db->where('service_date', $service_date);
        $this->db->where('service_type', $service_type);
		//$this->db->order_by('available_job DESC');
        $query = $this->db->get('svc_control');
        return $query->result();
    }
	
	public function actualizar_pendientes_cupo() {
        //if ($this->input->post()) {
            $data = json_decode(file_get_contents("php://input"), true);
			//print_r($data);
            if (isset($data['serviceDate'], $data['serviceType'], $data['newTotalJob'], $data['difference'])) {
                $service_date = $data['serviceDate'];
                $service_type = $data['serviceType'];
                $new_total_job = $data['newTotalJob'];
                $difference = $data['difference'];
				
                $where = [
                    'admin_register_date' => null,
                    'service_date' => $service_date,
                    'service_type' => $service_type
                ];

                $registros_actualizados = $this->actualizar_cupo_pendientes($where, $new_total_job, $difference);

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true, 'registros_actualizados' => $registros_actualizados]));

            } else {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode(['error' => 'Datos de entrada incompletos']));
            }
    }
	
    public function actualizar_cupo_pendientes($where, $new_total_job, $difference) {
        $this->db->where($where);
        $this->db->set('total_job', $new_total_job);
        $this->db->set('available_job', 'available_job + ' . $difference, false);
        $this->db->update('svc_control');
        return $this->db->affected_rows();
    }
		
    public function obtener_cupo() {
        $fecha_cita = $this->input->get('fecha');
        $tipo_servicio = $this->input->get('tipo');

        if ($fecha_cita && $tipo_servicio) {
            $cupo = $this->get_total_job_by_date_and_type($fecha_cita, $tipo_servicio);
            echo json_encode(['total_job' => $cupo]);
        } else {
            echo json_encode(['total_job' => null]);
        }
    }
	
    public function get_total_job_by_date_and_type($fecha_cita, $tipo_servicio) {
        $this->db->select('total_job');
        $this->db->where('service_date', $fecha_cita);
        $this->db->where('service_type', $tipo_servicio);
        $this->db->where('admin_register_date IS NOT NULL');
        $query = $this->db->get('svc_control', 1); // Limitar a 1 resultado

        if ($query->num_rows() == 1) {
            return $query->row()->total_job;
        } else {
            return null;
        }
    }
	
    public function guardar_opciones() {
        $data = json_decode($this->input->raw_input_stream, TRUE);

        if (isset($data['appointmentDate']) && isset($data['serviceType']) && isset($data['totalJob'])) {
            $fecha_cita = $data['appointmentDate'];
            $tipo_servicio = $data['serviceType'];
            $nuevo_cupo = $data['totalJob'];

            $where = [
                'service_date' => $fecha_cita, 
                'service_type' => $tipo_servicio,
                'admin_register_date IS NOT NULL' => null 
            ];

            $update_data = [
                'service_date' => $fecha_cita, // Actualizar la fecha de cita
                'total_job' => $nuevo_cupo,
				'available_job' => $nuevo_cupo,
				'admin_register_date' => date('Y-m-d H:i:s')
            ];

            $this->db->where($where);
            $this->db->update('svc_control', $update_data);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        }
    }
	
	public function obtener_valores_unicos() {
        $groupType = $this->input->post('groupType');
        $column = '';
        
        switch ($groupType) {
            case 'Servicio':
                $column = 'service_type';
                break;
            case 'Distrito':
                $column = 'district';
                break;
            case 'Tecnico':
                $column = 'technical';
                break;
            default:
                echo json_encode([]);
                return;
        }
        
        $this->db->distinct();
        $this->db->select($column);
		$this->db->where($column .' IS NOT NULL');
        $query = $this->db->get('svc_control');
        
        $uniqueValues = [];
        foreach ($query->result() as $row) {
            $uniqueValues[] = $row->$column;
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($uniqueValues));
    }
		
	public function resumen_semana() {
        $service_date = $this->input->post('service_date');
        $group_by = $this->input->post('group_by'); // Recibe el tipo de grupo
        $selected_unique = $this->input->post('selected_unique'); // Recibe el valor único

        $day_of_week = date('N', strtotime($service_date));
        $monday_date = date('Y-m-d', strtotime('-' . ($day_of_week - 1) . ' days', strtotime($service_date)));
        $saturday_date = date('Y-m-d', strtotime('+5 days', strtotime($monday_date)));

        $this->db->where('service_date >=', $monday_date);
        $this->db->where('service_date <=', $saturday_date);
        $this->db->where('admin_register_date IS NULL');
		
		if($group_by === 'servicio'){
			$filter_group = 'service_type';
		}
		elseif($group_by === 'distrito'){
			$filter_group = 'district';
		}
		elseif($group_by === 'tecnico'){
			$filter_group = 'technical';
		}
		
        if ($group_by && $selected_unique) {
            $this->db->where($filter_group, $selected_unique); // Filtrar por el valor único
        }

        $query = $this->db->get('svc_control');
        $data = $query->result();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

	public function update_register_v1() {
		$data = $this->input->post();
		$old_service_date = $this->input->post('old_service_date');

		if ($old_service_date !== $data['service_date']) {
			// Lógica para el service_date antiguo
			$old_date_records = $this->db->where('service_date', $old_service_date)
										 ->where('service_type', $data['service_type'])
										 ->where('admin_register_date IS NULL', null, false)
										 ->get('svc_control')
										 ->result();

			$is_only_old_record = false;
			if ($old_date_records) {
				// Verificar si el registro actual es el único con la fecha antigua
				$count_old_records = count($old_date_records);
				if ($count_old_records === 1 && isset($data['service_id'])) {
					if ($old_date_records[0]->svc_control_id == $data['service_id']) {
						$is_only_old_record = true;
					}
				}

				if (!$is_only_old_record) {
					foreach ($old_date_records as $record) {
						$this->db->set('available_job', 'available_job + 1', FALSE)
								 ->where('svc_control_id', $record->svc_control_id)
								 ->update('svc_control');
					}
				}
			}

			// Lógica para el nuevo service_date
			// Condición 1: admin_register_date es nulo
			$new_date_null_admin_records = $this->db->where('service_date', $data['service_date'])
													->where('service_type', $data['service_type'])
													->where('admin_register_date IS NULL', null, false)
													->order_by('register_date', 'DESC')
													->get('svc_control')
													->row();

			if ($new_date_null_admin_records) {
				if ($new_date_null_admin_records->available_job > 0) {
					$data['total_job'] = $new_date_null_admin_records->total_job;
					$data['available_job'] = $new_date_null_admin_records->available_job - 1;
				} else {
					echo json_encode(['success' => false, 'message' => 'No hay disponibilidad para la nueva fecha de cita.']);
					return;
				}
			} else {
				// Condición 2: admin_register_date no es nulo
				$new_date_not_null_admin_record = $this->db->where('service_date', $data['service_date'])
														 ->where('service_type', $data['service_type'])
														 ->where('admin_register_date IS NOT NULL', null, false)
														 ->get('svc_control')
														 ->row();

				if ($new_date_not_null_admin_record) {
					$data['total_job'] = $new_date_not_null_admin_record->total_job;
					$data['available_job'] = $new_date_not_null_admin_record->available_job - 1;
				} else {
					// Se mantienen los valores por defecto en caso no se cumpla la condicion
				}
			}
		}

		// Manejo del técnico
		if (!empty($data['technicalInput']) && $data['create_technical'] == 'on') {
			$data['technical'] = $data['technicalInput'];
		}
		
		// Verificar si el checkbox está marcado
		if (isset($data['create_technical']) && $data['create_technical'] === 'on' && !empty($data['technicalInput'])) {
			// Checkbox marcado y technicalInput no está vacío, usa technicalInput
			$data['technical'] = $data['technicalInput'];
			unset($data['technicalInput']);
			unset($data['create_technical']);
			unset($data['old_service_date']);
			$this->db->where('svc_control_id', $data['service_id']);
			unset($data['service_id']);
			
			$this->db->update('svc_control', $data);
		} else {
			// Checkbox no marcado o technicalInput vacío, usa technical
			$this->db->where('svc_control_id', $data['service_id']);
			unset($data['service_id']);
			unset($data['technicalInput']);
			unset($data['old_service_date']);
			$this->db->update('svc_control', $data);
		}
		
		//unset($data['technicalInput']);
		//unset($data['create_technical']);
		//unset($data['old_service_date']); 

		//$this->db->where('svc_control_id', $data['service_id']);
		//unset($data['service_id']);

		//$this->db->update('svc_control', $data);

		echo json_encode(['success' => true]);
	}
	
	public function update_register() {
		$data = $this->input->post();
		$old_service_date = $this->input->post('old_service_date');
		$old_service_type = $this->input->post('old_service_type');
		$old_id = $this->input->post('id');
		
		// Lógica para el campo técnico basada en el checkbox
		if (isset($data['create_technical']) && $data['create_technical'] === 'on' && !empty($data['technicalInput'])) {
			$data['technical'] = $data['technicalInput'];
			unset($data['technicalInput']);
			unset($data['create_technical']);
		} else {
			unset($data['technicalInput']);
			unset($data['create_technical']);
		}

		if ($old_service_date !== $data['service_date']) {
			
			//if($old_service_type === $data['service_type']){
				$old_job_values = $this->gen_m->filter_select('svc_control', false, ['total_job', 'available_job'], ['admin_register_date' => null, 'service_type' => $old_service_type, 'service_date' => $old_service_date, 'svc_control_id' => $old_id], null, null, [['available_job', 'ASC']]);
				
				// Cambios para bloque previo
				$old_data =  $this->gen_m->filter('svc_control', false, ['admin_register_date' => null, 'service_type' => $old_service_type, 'service_date' => $old_service_date, 'available_job <' => $old_job_values[0]->available_job], null, null, [['available_job', 'ASC']]);
				
				if (!empty($old_data)){
					foreach($old_data as $item){
						$item->available_job = $item->available_job + 1;
						$this->gen_m->update('svc_control', ['svc_control_id' => $item->svc_control_id], $item);
					}
				}
				
				//print_r($old_data);
				// Cambios para bloque nuevo
				$new_data = $this->gen_m->filter('svc_control', false, ['admin_register_date' => null, 'service_type' => $data['service_type'], 'service_date' => $data['service_date']], null, null, [['available_job', 'ASC']]);
				//print_r($new_data); return;
				if (!empty($new_data)){
					if($new_data[0]->available_job > 0){
						$data['total_job'] = $new_data[0]->total_job;
						$data['available_job'] = $new_data[0]->available_job - 1;
						$data['register_date'] = date('Y-m-d H:i:s');
					}
					else{
						echo json_encode(['success' => false, 'message' => 'No hay disponibilidad para el tipo de servicio seleccionado.']);
						return;
					}
				}
				else{
					$base_data = $this->gen_m->filter_select('svc_control', false, ['total_job', 'available_job'], ['admin_register_date !=' => null, 'service_type' => $data['service_type'], 'service_date' => $data['service_date']]);
					$data['total_job'] = $base_data[0]->total_job;
					$data['available_job'] = $base_data[0]->available_job - 1;
					$data['register_date'] = date('Y-m-d H:i:s');
				}
		} else {
			if ($old_service_type === $data['service_type']){
				$temp_data = $data;
				unset($temp_data['service_id']);
				unset($temp_data['old_service_date']);
				unset($temp_data['old_service_type']);
				unset($temp_data['id']);
				$this->db->where('svc_control_id', $data['service_id']);
				$this->db->update('svc_control', $temp_data);
				echo json_encode(['success' => true]);
				return;
			}
			else{
				$job_values = $this->gen_m->filter_select('svc_control', false, ['total_job', 'available_job'], ['admin_register_date' => null, 'service_type' => $data['service_type'], 'service_date' => $data['service_date']], null, null, [['available_job', 'ASC']]);
				//print_r($job_values); return;
				if(!empty($job_values)){
					if ($job_values[0]->available_job > 0){
						$data['total_job'] = $job_values[0]->total_job;
						$data['available_job'] = $job_values[0]->available_job - 1;
					} else {
						echo json_encode(['success' => false, 'message' => 'No hay disponibilidad para el tipo de servicio seleccionado.']);
						return;
					}
					
				}
				else{
					$job_values = $this->gen_m->filter_select('svc_control', false, ['total_job', 'available_job'], ['admin_register_date !=' => null, 'service_type' => $data['service_type'], 'service_date' => $data['service_date']]);
					//print_r($job_values); return;
					if ($job_values[0]->available_job > 0){
						$data['total_job'] = $job_values[0]->total_job;
						$data['available_job'] = $job_values[0]->available_job - 1;		
					} else {
						echo json_encode(['success' => false, 'message' => 'No hay disponibilidad para el tipo de servicio seleccionado.']);
						return;
					}
				}
			}
		}

		unset($data['old_service_date']);
		unset($data['old_service_type']);
		unset($data['id']);
		$this->db->where('svc_control_id', $data['service_id']);
		unset($data['service_id']);

		$this->db->update('svc_control', $data);

		echo json_encode(['success' => true]);
	}
	
	public function delete_register() {
        if ($this->input->post('id')) {
            $id_to_delete = $this->input->post('id');
			$service_date = $this->input->post('old_service_date');
			$service_type = $this->input->post('oldServiceType');

			$current_available_job = $this->gen_m->filter_select('svc_control', false, 'available_job', ['svc_control_id' => $id_to_delete]);
			

			$data = $this->gen_m->filter('svc_control', false, ['admin_register_date' => NULL, 'service_type' => $service_type, 'service_date' => $service_date, 'available_job <' => $current_available_job[0]->available_job]);

			
			if (!empty($data)){
				foreach($data as $item){
					$item->available_job = $item->available_job + 1;
					$this->gen_m->update('svc_control', ['svc_control_id' => $item->svc_control_id], $item);
				}
			}

			$where = ['svc_control_id' => $id_to_delete]; 
			$deleted = $this->gen_m->delete('svc_control', $where);
			

            if ($deleted) {
                echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el registro.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de registro no proporcionado.']);
        }
    }
	
    public function obtener_disponibilidad() {
		$service_date = $this->input->post('service_date');

		$this->db->where('service_date', $service_date);
		$this->db->order_by('available_job ASC');

		$query = $this->db->get('svc_control');

		if ($query->num_rows() > 0) {
			$row = $query->row();
			$available_jobs = $row->available_job;
		} else {
			$available_jobs = null;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('available_jobs' => $available_jobs)));
	}	

    public function date_convert_mm_dd_yyyy($date) {
		if (is_numeric($date)) {
			// Si es un número, es probable que sea una fecha de Excel (número de días desde 1900-01-01)
			$date = DateTime::createFromFormat('U', ($date - 25569) * 86400);
			return $date->format('Y-m-d');
		}

		$aux = explode("/", $date);
		if (count($aux) == 3) {
			// Verificamos que la fecha esté en formato mm/dd/yyyy
			return $aux[2]."-".$aux[0]."-".$aux[1]; // yyyy-mm-dd
		}

		return null;
	}

	public function upload_service_control(){
		set_time_limit(0);
		ini_set("memory_limit", -1);
		
		$start_time = microtime(true);
		
		//delete all rows lgepr_stock 
		//$this->gen_m->truncate("ar_carta_fianza");
		
		//load excel file
		$spreadsheet = IOFactory::load("./upload/svc_control.xlsx");
		$sheet = $spreadsheet->getActiveSheet(0);
		//excel file header validation
		$h = [
			trim($sheet->getCell('A1')->getValue()),
			trim($sheet->getCell('B1')->getValue()),
			trim($sheet->getCell('C1')->getValue())
		];

		$header = ["Dia de servicio (yyyy-mm-dd)", "Cupos totales en el día", "Tipo de Servicio"];
		
		// header validation
		$is_ok = true;
		foreach($h as $i => $h_i) if ($h_i !== $header[$i]) $is_ok = false;

		
		
		if ($is_ok){
			$updated = date('Y-m-d H:i:s');
			$max_row = $sheet->getHighestRow();
			$batch_data =[];
			$batch_size = 10;
			
			
			
			// Iniciar transacción para mejorar rendimiento
			$this->db->trans_start();
			for($i = 2; $i <= $max_row; $i++){
				$row = [
					"service_date" 				=> trim($sheet->getCell('A'.$i)->getValue()),
					"total_job" 				=> trim($sheet->getCell('B'.$i)->getValue()),
					"service_type" 				=> trim($sheet->getCell('C'.$i)->getValue()),
					"status"					=> 'Registered',
					"admin_register_date"		=> $updated
				];
				
				$row["service_date"] = $this->date_convert_mm_dd_yyyy($row["service_date"]);
						
				$row["available_job"] = $row["total_job"];
	
				
				//print_r($batch_entries); echo '<br>';
				
				$batch_data[] = $row;

				//$batch_data[]=$batch_entries;
				if(count($batch_data)>=$batch_size){
					$this->gen_m->insert_m("svc_control", $batch_data);
					$batch_data = [];
					unset($batch_data);
				}
;
			}
			// Insertar cualquier dato restante en el lote
			if (!empty($batch_data)) {
				$this->gen_m->insert_m("svc_control", $batch_data);
				$batch_data = [];
				unset($batch_data);
			}

			$msg = " record uploaded in ".number_Format(microtime(true) - $start_time, 2)." secs.";;
			$this->db->trans_complete();
			return $msg;			
		}else return "";
	}
	
	public function upload(){
		$type = "error"; $msg = "";
		
		if ($this->session->userdata('logged_in')){
			set_time_limit(0);
		
			$config = [
				'upload_path'	=> './upload/',
				'allowed_types'	=> '*',
				'max_size'		=> 200000,
				'overwrite'		=> TRUE,
				'file_name'		=> 'svc_control.xlsx',
			];
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('attach')){
				$msg = $this->upload_service_control();
				
				if ($msg) $type = "success";
				else $msg = "Wrong file.";
			}else $msg = str_replace("p>", "div>", $this->upload->display_errors());
		}else $msg = "Your session is finished.";
		
		header('Content-Type: application/json');
		$response = ["type" => $type, "msg" => $msg];

		//error_log(json_encode($response)); // Agregar esto para depurar
		
		echo json_encode($response);
	}
}