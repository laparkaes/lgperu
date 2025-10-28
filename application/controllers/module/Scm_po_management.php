<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scm_po_management extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('upload'); 
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
		
	public function index(){
		$customer_list = ['IMPORTACIONES RUBI S.A.', 'SAGA FALABELLA S.A.', 'CONECTA RETAIL S.A.', 'REPRESENTACIONES VARGAS S.A.', 'INTEGRA RETAIL S.A.C.', 'TIENDAS PERUANAS S.A. - [OECHSLE]', 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA - [PLAZA VEA]', 'HOMECENTERS PERUANOS S.A. - [PROMART]', 'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.', 'HIPERMERCADOS TOTTUS S.A.', 'TIENDAS DEL MEJORAMIENTO DEL HOGAR S.A. - [SODIMAC]', 'ESTILOS S.R.L.', 'CENCOSUD RETAIL PERU S.A. - [METRO]', 'COMERCIAL COUNTRY S.A.' , 'ELECTROHOGAR YATACO E.I.R.L.', 'IMPORTACIONES HIRAOKA S.A.', 'TIENDAS CHANCAFE S.A.C.', 'CORPORACION EFAMEINSA E INGENIERIA S.A.', 'OPEN INVESTMENTS S.A.C.', 'SERFAC S.A.C.'];
		
		$po_list = [];
		$po_data = $this->gen_m->filter('po_register', false, ['created >=' => Date('Y-m-01')], null, $w_in = null, $orders = [['created', 'DESC'], ['po_number', 'ASC'], ['line_no', 'ASC']]);
		foreach ($po_data as $item) $po_list[$item->po_number][] = $item;
		
		sort($customer_list);
		$status_list = ['Confirmed', 'Requested', 'Registered', 'Sent'];
		$data = [
			"po_data"	=> $po_list,
			"status"	=> $status_list,
			"customers" => $customer_list,
			"history"	=> $po_data,
			"overflow" 	=> "hidden",
			"main" 		=> "module/scm_po_management/index",
		];
		
		$this->update_po_lines();
		$this->load->view('layout', $data);
	}
	
	public function register_data() {
		// Form values
		$registrator = $this->input->post('registrator', TRUE);
		$ep_mail = $this->input->post('ep_mail', TRUE);
		$customer_name = $this->input->post("customer_name");
		$po_numbers = $this->input->post("po_numbers_form");
		$file_names_form = $this->input->post("file_names_form");
		$remark = $this->input->post("remark");

		if (empty($po_numbers)) {
			echo json_encode(['status' => 'error', 'message' => 'Please attach at least one file.']);
			return;
		}
		
		$files = $_FILES['attachment'];
		$uploaded_files_data = [];
		$email_attachments = [];
		$processed_files = [];

		// Mapear los archivos subidos para un acceso rápido por nombre
		$uploaded_files_map = [];
		$file_count = count($files['name']);
		for ($i = 0; $i < $file_count; $i++) {
			$filename = $files['name'][$i];
			$uploaded_files_map[$filename] = [
				'name'     => $files['name'][$i],
				'type'     => $files['type'][$i],
				'tmp_name' => $files['tmp_name'][$i],
				'error'    => $files['error'][$i],
				'size'     => $files['size'][$i],
			];
		}

		for ($i = 0; $i < count($po_numbers); $i++) {
			$po_number = $po_numbers[$i];
			$original_filename = $file_names_form[$i];

			if (isset($uploaded_files_map[$original_filename])) {
				$file_data = $uploaded_files_map[$original_filename];

				$data = [
					'po_number'     => $po_number,
					'line'          => 1,
					'registrator'   => $registrator,
					'ep_mail'       => $ep_mail,
					'po_file'       => $original_filename,
					'customer_name' => $customer_name,
					'created'       => Date('Y-m-d H:i:s'),
					'status'        => 'Sent',
					'remark'        => $remark
				];
				$this->gen_m->insert('po_register', $data);

				if (!in_array($original_filename, $processed_files)) {
					$_FILES['userfile'] = $file_data;
					
					$config['upload_path']   = './upload/';
					$config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx|zip|xls|xlsx|txt';
					$config['max_size']      = 20000;
					$config['file_name']     = $original_filename;

					$this->upload->initialize($config);

					if ($this->upload->do_upload('userfile')) {
						$upload_data = $this->upload->data();
						$uploaded_files_data[] = [
							'name' => $upload_data['orig_name'],
							'path' => $upload_data['full_path'],
							'po_number' => $po_number
						];
						$email_attachments[] = $upload_data['full_path'];
						$processed_files[] = $original_filename;
					} else {
						$error = $this->upload->display_errors();
						log_message('error', 'Upload error: ' . $error);
						echo json_encode(['status' => 'error', 'message' => 'File upload failed: ' . strip_tags($error)]);
						return;
					}
				}
			}
		}
		
		foreach ($uploaded_files_map as $original_filename => $file_data) {
			if (!in_array($original_filename, $processed_files)) {
				$is_txt = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION)) === 'txt';
				
				if ($is_txt) {
					// Obtener el PO del archivo para verificar si pertenece a un PO registrado
					$file_parts = explode('-', pathinfo($original_filename, PATHINFO_FILENAME));
					$po_match = end($file_parts);
					
					if (in_array($po_match, $po_numbers)) {
						// Si el archivo .txt complementario no se ha movido, lo movemos ahora
						$target_path = './upload/' . $original_filename;
						
						if (move_uploaded_file($file_data['tmp_name'], $target_path)) {
							$email_attachments[] = $target_path;
							$processed_files[] = $original_filename;
						}
					}
				}
			}
		}

		$po_msg_list = [];
		foreach ($uploaded_files_data as $data) {
			$po_msg_list[] = $data['po_number'];
		}

		$to = 'lbernaldo.js@lgepartner.com';
		$subject = "[{$customer_name}] Registro de Orden de Compra" . " " . Date("Ymd");
		$created = Date('Y-m-d H:i:s');
		
		$po_msg = implode(", #", $po_msg_list);

		$message_content = "
		<html>
		<body>
			<p>Estimado/a(s),</p>
			<p>Se ha registrado una nueva orden de compra con los siguientes detalles:</p>
			<table style='width: 60%; border-collapse: collapse; margin-left: 0;'>
				<tr>
					<td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>PO Registrados:</td>
					<td style='border: 1px solid #ddd; padding: 8px;'>#" . $po_msg . "</td>
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
					<td style='border: 1px solid #ddd; padding: 8px;'>" . nl2br($remark) . "</td>
				</tr>
			</table>";

		$message_content .= "<p>Se adjuntan las ordenes de compra correspondientes.</p>";
		$message_content .= "<p>Saludos cordiales.</p></body></html>";
		$mail = $ep_mail . "@lge.com";
		$this->my_func->send_email($mail, $to, $subject, $message_content, $email_attachments);

		// Limpiar archivos temporales
		foreach ($email_attachments as $path) {
			if (file_exists($path)) {
				unlink($path);
			}
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
	
	public function check_multiple_po_exists() {
		$po_numbers_from_form = $this->input->post('po_numbers');
		$po_numbers_array = json_decode($po_numbers_from_form, true);

		$duplicates_in_form = array_diff_assoc($po_numbers_array, array_unique($po_numbers_array));
		if (!empty($duplicates_in_form)) {
			echo json_encode(['exists' => array_values($duplicates_in_form)]);
			return;
		}

		$this->db->select('po_number');
		$this->db->where_in('po_number', $po_numbers_array);
		$query = $this->db->get('po_register');

		$existing_pos_in_db = [];
		foreach ($query->result() as $row) {
			$existing_pos_in_db[] = $row->po_number;
		}

		echo json_encode(['exists' => $existing_pos_in_db]);
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

        $filter = ['id' => $record_id];
        
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
		
		$original_record = $this->db->get_where('po_register', ['id' => $record_id])->row();
	
		if (is_null($original_record->line)) {
			$this->db->where('id', $record_id);
			$this->db->update('po_register', ['line' => 1]);
		}
		
		$this->db->select_max('line');
		$this->db->where('po_number', $po_number);
		$query = $this->db->get('po_register');
		$last_line = $query->row()->line;

		$new_line = $last_line + 1;
		
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
			$status = 'Sent';
			$data = ['gerp' => null, 'status' => $status];
			
		} elseif ($state_date == 2){ // Request Date
			$status = 'Registered';
			$data = ['appointment_request	' => null, 'status' => $status];			
		} elseif ($state_date == 3){ // Appointment Date
			$status = 'Requested';
			$data = ['appointment_confirmed	' => null, 'status' => $status];		
		}
		
		
		//print_r([$record_id, $state_date]);
		if ($this->gen_m->update('po_register', ['id' => $record_id],$data)) {
			echo json_encode(['status' => 'success', 'message' => 'Date removed successfully.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to remove date.']);
		}
	
	}

	public function extract_po() { // No actualizado (po management actualizado logica de extraccion)
		if (!empty($_FILES['attachment']['name'])) {
			$files_data = []; // Array para almacenar los objetos de archivo
			$txt_processed_pos = [];
			
			// Mapear los archivos y procesar la lógica de POs
			foreach ($_FILES['attachment']['name'] as $index => $file_name) {
				$po_number = null;
				$is_txt = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) === 'txt';
				
				if ($is_txt) {
					// Lógica para archivos TXT
					$file_parts = explode('-', pathinfo($file_name, PATHINFO_FILENAME));
					$po_number = end($file_parts);
					
					// Si este PO de TXT ya ha sido procesado, lo ignoramos para la tabla
					if (in_array($po_number, $txt_processed_pos)) {
						continue;
					}
					$txt_processed_pos[] = $po_number;
				} else {
					// Lógica para otros archivos
					if (preg_match('/[a-zA-Z]?\d{5,}(?:[_\-][a-zA-Z0-9]+)?/', $file_name, $poMatch)) {
						$po_number = $poMatch[0];
					}
				}
				
				// Si el PO es null o se detecta, agregamos el objeto
				$files_data[] = [
					'name' => $file_name,
					'po_number' => $po_number
				];
			}
			
			echo json_encode(['status' => 'success', 'files_data' => $files_data]);
			return;
		}

		echo json_encode(['status' => 'error', 'message' => 'No files provided.']);
	}

	public function update_po_lines() {
		$data_po = $this->gen_m->filter('po_register', false, ['line' => 1]);
		
		if (empty($data_po)) {
			//echo "Don't find POs in po_register.";
			return;
		}
		
		$po_first_data = [];
		foreach ($data_po as $item) $po_first_data[$item->po_number] = $item;
		//echo '<pre>'; print_r($po_first_data);
		$default_values = [];
		foreach ($data_po as $item){
			$default_values[$item->po_number] = [
							'po_number' 				=> $item->po_number,
							'line'						=> 1,
							'model'						=> null,
							'qty'						=> null,
							'amount_usd'				=> null,
							'order_no'					=> $item->order_no ?? null,
							'line_no'					=> $item->line_no ?? null,
							'registrator'				=> $item->registrator,
							'ep_mail'					=> $item->ep_mail,
							'po_file'					=> $item->po_file ?? null,
							'customer_name'				=> $item->customer_name,
							'created'					=> $item->created,
							'gerp'						=> null,
							'appointment_request'		=> null,
							'appointment_confirmed'		=> null,
							'status'					=> 'Sent',
							'remark'					=> $item->remark ?? null,
							'remark_appointment'		=> $item->remark_appointment ?? null,
			];
		}
		
		$customer_po_list = array_unique(array_column($data_po, 'po_number'));
				
		$w_in_clause = [
			[
				'field' => 'customer_po_no', 
				'values' => $customer_po_list
			]
		];

		$sales_columns = ['customer_po_no', 'model', 'ordered_qty', 'order_no', 'line_no', 'sales_amount_usd'];
		$sales_orders = [['order_no', 'ASC'], ['line_no', 'ASC']];
		$sales_data = $this->gen_m->filter_select('lgepr_sales_order', false, $sales_columns, null, null, $w_in_clause, $sales_orders);

		$closed_columns = ['customer_po_no', 'model', 'order_qty', 'order_no', 'line_no', 'order_amount_usd'];
		$closed_data = $this->gen_m->filter_select('lgepr_closed_order', false, $closed_columns, null, null, $w_in_clause, $sales_orders);

		$list_data = [];
		
		$all_data = array_merge($sales_data, $closed_data);

		if ($all_data) {
			foreach($all_data as $item) {
				$list_data[$item->customer_po_no][] = $item;
			}
		}	
		
		$data_multi = [];
				
		foreach ($customer_po_list as $po_number){
			if(!empty($list_data[$po_number])){
				$list_data_ =  $list_data[$po_number];
			} else continue;
			
			$line = 1;
			if ($list_data_){
				foreach ($list_data_ as $index => $item) {
					$default_values[$po_number]['model'] = $item->model;
					$default_values[$po_number]['qty'] = $item->ordered_qty ?? $item->order_qty;
					$default_values[$po_number]['amount_usd'] = $item->sales_amount_usd ?? $item->order_amount_usd;
					$default_values[$po_number]['line'] = $line;
					$default_values[$po_number]['order_no'] = $item->order_no;
					$default_values[$po_number]['line_no'] = $item->line_no;
					$data = $default_values[$po_number];
					
					if ($this->gen_m->filter_select('po_register', false, ['order_no', 'line_no'], ['order_no' =>  $item->order_no, 'line_no' => $item->line_no])) continue;
					else {
						if ($index == 0 && empty($po_first_data[$po_number]->model) && empty($po_first_data[$po_number]->order_no)){ // First insert data from form							
							$this->gen_m->update('po_register', ['po_number' => $po_number, 'line' => 1], $data);
						} else {
							$data_multi[] = $data;
						}
						$line += 1;
					}
				}
			}
		}
		
		if ($data_multi) $this->gen_m->insert_m('po_register', $data_multi);
	}	

}
