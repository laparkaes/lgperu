<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Po_register extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('upload'); 
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
		
	public function index(){
		$customer_list = ['IMPORTACIONES RUBI S.A.', 'SAGA FALABELLA S.A.', 'CONECTA RETAIL S.A.', 'REPRESENTACIONES VARGAS S.A.', 'INTEGRA RETAIL S.A.C.', 'TIENDAS PERUANAS S.A. (OECHSLE)', 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA (PLAZA VEA)', 'HOMECENTERS PERUANOS S.A. (PROMART)', 'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.', 'HIPERMERCADOS TOTTUS S.A.', 'IMPORTACIONES HIRAOKA S.A.C.', 'SODIMAC', 'ESTILOS S.R.L.', 'METRO'];
		$po_data = $this->gen_m->filter('po_register', false, null, null, $w_in = null, $orders = [['created', 'DESC'], ['po_number', 'ASC'], ['line', 'ASC']]);
		sort($customer_list);
		$status_list = ['Confirmed', 'Requested', 'Registered', 'Sent'];
		$data = [
			"status"	=> $status_list,
			"customers" => $customer_list,
			"history"	=> $po_data,
			"overflow" 	=> "hidden",
			"main" 		=> "page/po_register/index",
		];
		
		$this->load->view('layout_dashboard', $data);
	}

	public function register_data() {
        // Obtener los valores de los campos de texto
        $registrator = $this->input->post('registrator', TRUE); // TRUE para sanitizar el input
        $ep_mail = $this->input->post('ep_mail', TRUE);
		$customer_name = $this->input->post("customer_name");
		$po_number = $this->input->post("po_number");
		$remark = $this->input->post("remark");
		
        // Configuración para la subida del archivo
        $config['upload_path']   = './upload/';
        $config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx|zip|xls|xlsx|txt';
        $config['max_size']      = 20000;
        $config['file_name']     = $_FILES['attachment']['name']; // Conservar el nombre original
			
		$file_path = null;
		$file_name = null;
		
		if (!empty($_FILES['attachment']['name'])) {
			$this->upload->initialize($config);
			if ($this->upload->do_upload('attachment')) {
				$file_data = $this->upload->data();
				$file_path = $file_data['full_path'];
				$file_name = $file_data['orig_name'];
			} else {
				// Manejar error si la subida falla (ej. tamaño o tipo incorrecto)
				$error = $this->upload->display_errors();
				log_message('error', 'Upload error: ' . $error);
				// Puedes redirigir a una página de error o mostrar un mensaje
				redirect('page/po_register', 'refresh'); // Refresh
				return;
			}
		}
        //$this->upload->initialize($config); // Inicializar la librería de subida con la configuración
		
		$data = [
				'po_number' 	=> $po_number,
				'line'			=> 1,
				'registrator'	=> $registrator,
				'ep_mail' 		=> $ep_mail,
				'po_file' 		=> $file_name,
				'customer_name' => $customer_name,
				'created' 		=> Date('Y-m-d H:i:s'),
				'status'		=> 'Sent',
				'remark'		=> $remark
		];
		
		$this->gen_m->insert('po_register', $data);
		 
		
		// --- Generar el HTML del correo ---
		$to = ['mariela.carbajal@lge.com', 'elizabeth.sampe@lge.com', 'patricia.rivas@lge.com', $ep_mail . "@lge.com"];
		// Definir el asunto
		$subject = "[{$customer_name}] Registro de Orden de Compra: #" . $po_number . " " . Date("Ymd");

		$created = Date('Y-m-d H:i:s');

		$message_content = "
		<html>
		<body>
			<p>Estimado/a(s),</p>
			<p>Se ha registrado una nueva orden de compra con los siguientes detalles:</p>
			<table style='width: 60%; border-collapse: collapse; margin-left: 0;'>
				<tr>
					<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>Numero de Orden:</td>
					<td style='border: 1px solid #ddd; padding: 8px;'>#" . $po_number . "</td>
				</tr>
				<tr>
					<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>Cliente:</td>
					<td style='border: 1px solid #ddd; padding: 8px;'>" . $customer_name . "</td>
				</tr>
				<tr>
					<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>Fecha de Registro:</td>
					<td style='border: 1px solid #ddd; padding: 8px;'>" . $created . "</td>
				</tr>
				<tr>
					<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>Registrador:</td>
					<td style='border: 1px solid #ddd; padding: 8px;'>" . $registrator . "</td>
				</tr>
				<tr>
					<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>Comentarios:</td>
					<td style='border: 1px solid #ddd; padding: 8px;'>" . $remark . "</td>
				</tr>
			</table>";

		if (!empty($file_name)) {
			$message_content .= "<p>Se adjunta la orden de compra correspondiente.</p>";
		} else {
			$message_content .= "<p>No se adjunto ningun archivo.</p>";
		}
		$message_content .= "<p>Saludos cordiales.</p></body></html>";
		$mail = $ep_mail . "@lge.com";
		$this->my_func->send_email($mail, $to, $subject, $message_content, $file_path);

		if (!empty($file_path) && file_exists($file_path)) {
			unlink($file_path);
		}
		echo json_encode(['status' => 'success', 'message' => 'PO has been registered successfully.']);
    }
	
	public function check_po_exists(){
		$po_number = $this->input->post("po_number");
		
		$list_po = [];
		$data_po = $this->gen_m->filter_select('po_register', false, 'po_number');
		foreach ($data_po as $item) $list_po[] = $item->po_number;
		$list_po = array_values(array_unique($list_po));
		
		if (in_array($po_number, $list_po)) {
			echo json_encode(['exists' => true]);
		} else{
			echo json_encode(['exists' => false]);
		}
	}
	
	public function update_status(){
		$record_id = $this->input->post('record_id', TRUE);
        $field = $this->input->post('field', TRUE);
        
        if (!$record_id || !$field) {
            $response = ['status' => 'error', 'message' => 'Invalid data provided.'];
            echo json_encode($response);
            return;
        }
        
        $status_to_update = '';
        $db_field = '';

        if ($field === 'gerp') {
            $status_to_update = 'Registered';
            $db_field = 'gerp';
        } else if ($field === 'requested') {
            $status_to_update = 'Requested';
            $db_field = 'appointment_request';
        } else if ($field === 'confirmed') {
            $status_to_update = 'Confirmed';
            $db_field = 'appointment_confirmed';
        } else {
            $response = ['status' => 'error', 'message' => 'Invalid field provided.'];
            echo json_encode($response);
            return;
        }
        
        $update_data = [
            'status' => $status_to_update,
            $db_field => Date('Y-m-d H:i:s')
        ];

        // Definir el filtro para la función de actualización
        $filter = ['id' => $record_id];
        
        // Llamar a tu función de actualización personalizada
        $success = $this->gen_m->update('po_register', $filter, $update_data);
        
        if ($success) {
            $response = [
                'status' => 'success',
                'message' => 'Record updated successfully!',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to update the record.'];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
	}

	public function add_new_line() {
		$po_number = $this->input->post('po_number', TRUE);
		$record_id = (int)$this->input->post('record_id', TRUE);
		
		// Obtener los datos de la fila original
		$original_record = $this->db->get_where('po_register', ['id' => $record_id])->row();
	
		// Paso 1: Actualizar la fila original
		if (is_null($original_record->line)) {
			$this->db->where('id', $record_id);
			$this->db->update('po_register', ['line' => 1]);
		}
		
		// Paso 2: Obtener la última línea y crear la nueva
		$this->db->select_max('line');
		$this->db->where('po_number', $po_number);
		$query = $this->db->get('po_register');
		$last_line = $query->row()->line;

		$new_line = $last_line + 1;
		
		// Obtener los datos de la fila original para la nueva inserción
		$last_record = $this->db->get_where('po_register', ['id' => $record_id])->row();

		if ($last_record) {
			$new_data = [
				'po_number' => $last_record->po_number,
				'line' => $new_line,
				'registrator' => $last_record->registrator,
				'ep_mail' => $last_record->ep_mail,
				'po_file' => $last_record->po_file,
				'customer_name' => $last_record->customer_name,
				'created' => $last_record->created,
				'gerp' => null,
				'appointment_request' => null,
				'appointment_confirmed' => null,
				'status' => 'Sent',
			];
			
			$this->db->insert('po_register', $new_data);
			
			if ($this->db->affected_rows() > 0) {
				$new_record_id = $this->db->insert_id();
				$new_record = $this->db->get_where('po_register', ['id' => $new_record_id])->row();
				
				header('Content-Type: application/json');
				echo json_encode(['status' => 'success', 'message' => 'New line added.', 'new_record' => $new_record]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'error', 'message' => 'Failed to add a new line.']);
			}
		} else {
			header('Content-Type: application/json');
			echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
		}
	}
	
	public function delete_row (){
		$record_id = (int)$this->input->post('record_id', TRUE);
		$po_number = $this->input->post("po_number", TRUE);
		
		$data = $this->gen_m->filter_select('po_register', false, ['line'], ['po_number' => $po_number, 'id' => $record_id]);
		$current_line = $data[0]->line;
		
		//$line = $this->input->post("po_number");
		if ($record_id){
			if ($this->gen_m->delete('po_register', ['id' => $record_id])) {				
				$new_data = $this->gen_m->filter_select('po_register', false, ['line', 'po_number', 'id'], ['po_number' => $po_number, 'line >' => $current_line], null, null, [['line', 'DESC']]);
				foreach ($new_data as $item){
					$data_updated = [
										'line' => $item->line - 1,
								];
					$this->gen_m->update('po_register', ['po_number' => $po_number, 'line' => $item->line, 'id' => $item->id], $data_updated);
				}
				header('Content-Type: application/json');
				echo json_encode(['status' => 'success', 'message' => 'Current row removed.']);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'error', 'message' => 'Failed to remove the row.']);
			}
		} else {
			header('Content-Type: application/json');
			echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
		}
	}

	public function save_remark() {
		$record_id = $this->input->post('record_id');
		$remark = $this->input->post('remark');
		$data = ['remark_appointment' => $remark];

		if ($this->gen_m->update('po_register', ['id' => $record_id],$data)) {
			echo json_encode(['status' => 'success', 'message' => 'Remark saved successfully.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to save remark.']);
		}
	}
	
	public function remove_table_remark(){
		$record_id = $this->input->post('record_id');
		$remark = $this->input->post('remark');
		$data = ['remark_appointment' => $remark];

		if ($this->gen_m->update('po_register', ['id' => $record_id],$data)) {
			echo json_encode(['status' => 'success', 'message' => 'Remark saved successfully.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to save remark.']);
		}
	}
	
	public function remove_dates(){
		$record_id = $this->input->post('record_id');
		$state_date = $this->input->post('state_date');
		
		
		if ($state_date == 1) { // GERP Date
			$data = ['gerp' => null];
		} elseif ($state_date == 2){ // Request Date
			$data = ['appointment_request	' => null];
		} elseif ($state_date == 3){ // Appointment Date
			$data = ['appointment_confirmed	' => null];
		}
		
		
		//print_r([$record_id, $state_date]);
		if ($this->gen_m->update('po_register', ['id' => $record_id],$data)) {
			echo json_encode(['status' => 'success', 'message' => 'Date removed successfully.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to remove date.']);
		}
	
	}
}
