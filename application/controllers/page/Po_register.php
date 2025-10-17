<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpMimeMailParser\Parser as EmlParser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class Po_register extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('upload'); 
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
		
	public function index(){
		$customer_list = ['IMPORTACIONES RUBI S.A.', 'SAGA FALABELLA S.A.', 'CONECTA RETAIL S.A.', 'REPRESENTACIONES VARGAS S.A.', 'INTEGRA RETAIL S.A.C.', 'TIENDAS PERUANAS S.A. - [OECHSLE]', 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA - [PLAZA VEA]', 'HOMECENTERS PERUANOS S.A. - [PROMART]', 'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.', 'HIPERMERCADOS TOTTUS S.A.', 'TIENDAS DEL MEJORAMIENTO DEL HOGAR S.A. - [SODIMAC]', 'ESTILOS S.R.L.', 'CENCOSUD RETAIL PERU S.A. - [METRO]', 'COMERCIAL COUNTRY S.A.' , 'ELECTROHOGAR YATACO E.I.R.L.', 'IMPORTACIONES HIRAOKA S.A.', 'TIENDAS CHANCAFE S.A.C.', 'CORPORACION EFAMEINSA E INGENIERIA S.A.', 'OPEN INVESTMENTS S.A.C.', 'SERFAC S.A.C.'];
		
		$customer_ac_list = ['UEZU COMERCIAL SOCIEDAD ANONIMA CERRADA - UEZU COMERCIAL SAC', 'COMERCIAL REFRIGERACION PERU S.A.C.', 'TECNICAV SERVICE E.I.R.L.', 'COINREFRI AIR S.A.C.', 'INVERSIONES GENERALES TECNICAS S.A', 'CONSORCIO INTI PUNKU', 'HANURI SOCIEDAD ANONIMA CERRADA', 'COLDSMART PERU S.A.C.', 'SUMINISTROS E INVERSIONES DEL PERU E.I.R.L.', 'INGENIEROS FRIOTEMP S.A.C.', 'AMASDCLIMA PERU S.R.L.', 'INVERSIONES LUNI S.A.C.', 'GASUE S.A.C.', 'COMERCIAL FRIONORTE E.I.R.L.', 'PROTERM PERU S.A.C.', 'PCR SERVICIOS GENERALES E.I.R.L.', 'IREDI INTERNATIONAL S R L', 'GREENCORP PERU S.A.C.', 'JUAN PABLO MORI E.I.R.L.', 'SOLUCIONES MULTITECNICAS S.A.C.', 'TODOCLIMA PERU S.A.C.', 'FRIOSYSTEM PERU S.R.L.', 'GRUPO VICTORIA SERVICIOS ESPECIALIZADOS S.A.C.', 'REPRESENTACIONES MONTERO S.R.L.', 'SERVICLIMA INTERNATIONAL E.I.R.L.', 'GRUPO MERPES PERU S.A.C.', 'TIENDAS CHANCAFE S.A.C.', 'REPRESENTACIONES VARGAS S.A.', 'COMERCIAL IMPORTADORA J. MORA', 'F Y P INVERSIONES S.A.C.', 'IMPORTACIONES MONTERO E.I.R.L.', 'ASOCIACION PERUANA DE LA IGLESIA DE JESUCRISTO DE LOS SANTOS DE LOS ULTIMOS DIAS', 'MEGA SHOP FRIO SOC. COM. RESPONS. LTDA.', 'COLD MACHINES S.A.C', 'SONEPAR PERU S.A.C.', 'L Y Z INVESTMENT S.A.C', 'AIR COOL SYSTEM SM S.A.C.', 'VMH INGENIEROS S.A.C.', 'TGESTIONA SERVICIOS GLOBALES S.A.C.', 'ALSI PERU S.A.C.', 'ASCENSORES S.A.', 'MOTOREX S A', 'THERMOFIRE S.A.C.', 'MORA PERU CORPORATION S.R.L.', 'ALSI BMS PERU S.A.C.', 'GOLDEN ENGINEERING PERU S.A.', 'TEKKO PERU S.A.C.', 'PRIMELINES S.A.C.', 'REFRIGERACION TROPIC FRIO E.I.R.L.', 'ASESORIA, INGENIERIA Y REPRESENTACIONES S.A.C.', 'ABB S.A.', 'ALSI HVACR PERU E.I.R.L.', 'COLD IMPORT S.A.'];
		
		$po_list = [];
		$po_data = $this->gen_m->filter('po_register', false, ['created >=' => Date('Y-m-01')], null, $w_in = null, $orders = [['created', 'DESC'], ['po_number', 'ASC'], ['line_no', 'ASC']]);
		foreach ($po_data as $item) $po_list[$item->po_number][] = $item;		
		
		//echo '<pre>'; print_r($po_list); return;
		
		sort($customer_list);
		sort($customer_ac_list);
		$status_list = ['Confirmed', 'Requested', 'Registered', 'Sent'];
		$data = [
			"customers_ac"  => $customer_ac_list,
			"status"		=> $status_list,
			"customers" 	=> $customer_list,
			"history"		=> $po_list,
			"overflow" 		=> "scroll",
			"main" 			=> "page/po_register/index",
		];
		$this->update_po_lines();
		$this->load->view('layout_dashboard', $data);
	}
	
	public function process_eml_format($original_eml_files, $uploaded_files_map){
		$eml_was_uploaded = !empty($original_eml_files);
		$eml_attachments_for_email = [];
		$eml_processed_files = [];

		if ($eml_was_uploaded) {
			foreach ($original_eml_files as $original_eml_file) {
				if (isset($uploaded_files_map[$original_eml_file])) {
					$file_data = $uploaded_files_map[$original_eml_file];
					try {
						$parser = new Parser();
						$parser->setPath($file_data['tmp_name']);
						
						$html_message_body = $parser->getMessageBody('html');
						$eml_attachments_data = $parser->getAttachments();

						foreach ($eml_attachments_data as $eml_attachment) {
							$eml_file_name = $eml_attachment->getFilename();
							$eml_attachment_extension = strtolower(pathinfo($eml_file_name, PATHINFO_EXTENSION));

							// Ignorar imágenes incrustadas para la BD
							if (in_array($eml_attachment_extension, ['png', 'jpg', 'jpeg', 'gif'])) {
								continue;
							}

							$eml_po_number = '-';
							if (preg_match('/[a-zA-Z]?\d{5,}(?:[_\-][a-zA-Z0-9]+)?/', $eml_file_name, $poMatch)) {
								$eml_po_number = $poMatch[0];
							}
							
							if (!empty($eml_po_number) || !empty($eml_file_name)) {
								$target_path = './upload/' . $eml_file_name;
								file_put_contents($target_path, $eml_attachment->getContent());
								$eml_attachments_for_email[] = $target_path;
								$eml_processed_files[] = $eml_file_name;

								// Agrupar por PO para la inserción
								if (!isset($po_to_files_map[$eml_po_number])) {
									$po_to_files_map[$eml_po_number] = [];
								}
								$po_to_files_map[$eml_po_number][] = $eml_file_name;
							}
						}
						
						// Usar el cuerpo del EML para el mensaje principal
						$message_content = $html_message_body;

					} catch (Exception $e) {
						log_message('error', 'EML parsing error: ' . $e->getMessage());
						echo json_encode(['status' => 'error', 'message' => 'EML file could not be processed.']);
						return;
					}
				}
			}
		}
	}
	
	public function _process_special_customers($registrator, $ep_mail, $customer_name, $remark, $po_source_ac, $final_cc_list) {	// Special customers
		// 1. Extraer los POs del remark
		$po_numbers_from_remark = [];
		$separators = '/,\s*|\r?\n/u'; 

		// 2. Divide la cadena por esos separadores, omitiendo las entradas vacías.
		$po_array = preg_split($separators, $po_source_ac, -1, PREG_SPLIT_NO_EMPTY);
		// 3. Limpia cada PO de posibles espacios en blanco restantes.
		$po_numbers_from_remark = array_map('trim', $po_array);

		if (empty($po_numbers_from_remark)) {
			return json_encode(['status' => 'error', 'message' => 'No PO numbers detected in the remark field.']);
		}

		// 2. Insertar cada PO en la tabla po_register
		foreach ($po_numbers_from_remark as $po) {
			$data = [
				'po_number'     => $po,
				'line'          => 1,
				'registrator'   => $registrator,
				'ep_mail'       => $ep_mail,
				'po_file'       => '',
				'customer_name' => $customer_name,
				'created'       => Date('Y-m-d H:i:s'),
				'status'        => 'Sent',
				'remark'        => $remark
			];
			$this->gen_m->insert('po_register', $data);
		}

		// 3. Preparar y enviar el correo de resumen manual
		$subject = "[{$customer_name}] Registro de Orden de Compra" . " " . Date("Ymd");
		$created = Date('Y-m-d H:i:s');
		$po_msg = implode(", #", $po_numbers_from_remark);

		$message_content = "
		<html>
		<body>
			<p>Estimado/a(s),</p>
			<p>Se han registrado las siguientes ordenes de compra:</p>
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
					<td style='border: 1px solid #ddd; padding: 8px;'>" . $remark . "</td>
				</tr>
			</table>
		</body>
		</html>";

		$from_email = $ep_mail . '@lge.com';
		// Se utiliza la función send_email para el envío sin adjuntos
		$to = ['mariela.carbajal@lge.com', 'elizabeth.sampe@lge.com', 'patricia.rivas@lge.com', $ep_mail . "@lge.com"];
		if ($final_cc_list){
			$final_recipient_list = array_merge($to, $final_cc_list);
		} else $final_recipient_list = $to;
		$this->my_func->send_email_po($from_email, $final_recipient_list, $subject, $message_content, []); 
		
		return json_encode(['status' => 'success', 'message' => 'PO has been registered successfully.']);
	}
		
	public function _map_uploaded_files($files) { // Método auxiliar para mapear archivos subidos
		$map = [];
		$file_count = count($files['name']);
		for ($i = 0; $i < $file_count; $i++) {
			$filename = $files['name'][$i];
			$map[$filename] = [
				'name'      => $files['name'][$i],
				'type'      => $files['type'][$i],
				'tmp_name'  => $files['tmp_name'][$i],
				'error'     => $files['error'][$i],
				'size'      => $files['size'][$i],
			];
		}
		return $map;
	}

	public function _process_eml_files($original_eml_files, $uploaded_files_map, $po_to_files_map) { // Procesamiento de archivos .EML y corrección del problema de PNG/GIF en la BD
		$eml_temp_paths = [];
		$subject = '';
		$message_content = '';

		foreach ($original_eml_files as $original_eml_file) {
			if (isset($uploaded_files_map[$original_eml_file])) {
				$file_data = $uploaded_files_map[$original_eml_file];
				try {
					$parser = new EmlParser();
					$parser->setPath($file_data['tmp_name']);
					
					$subject = $parser->getHeader('subject');
					$message_content = $parser->getMessageBody('html');

					$eml_attachments_data = $parser->getAttachments();
					
					foreach ($eml_attachments_data as $eml_attachment) {
						$eml_file_name = $eml_attachment->getFilename();
						$eml_attachment_extension = strtolower(pathinfo($eml_file_name, PATHINFO_EXTENSION));
						
						// 1. VERIFICAR si el archivo ya fue asignado por el usuario
						$fill_map_by_user = false;
						foreach ($po_to_files_map as $filenames) {
							if (in_array($eml_file_name, $filenames)) {
								$fill_map_by_user = true;
								break;
							}
						}
						if (!$fill_map_by_user) {
							$is_inline_image = in_array($eml_attachment_extension, ['png', 'jpg', 'jpeg', 'gif']);

							$eml_po_number = '';
							if (!$is_inline_image) {
								if (preg_match('/[a-zA-Z]?\d{5,}(?:[_\-][a-zA-Z0-9]+)?/', $eml_file_name, $poMatch)) {
									$eml_po_number = $poMatch[0];
								}
							}
							
							$target_path = './upload/' . $eml_file_name;
							file_put_contents($target_path, $eml_attachment->getContent());
							$eml_temp_paths[] = $target_path;

							if (!$is_inline_image && !empty($eml_po_number)) {
								if (!isset($po_to_files_map[$eml_po_number])) {
									$po_to_files_map[$eml_po_number] = [];
								}
								$po_to_files_map[$eml_po_number][] = $eml_file_name;
							}
						}
					}

				} catch (Exception $e) {
					log_message('error', 'EML parsing error: ' . $e->getMessage());
					return ['error' => json_encode(['status' => 'error', 'message' => 'EML file could not be processed.'])];
				}
			}
		}

		return [
			'po_to_files_map' => $po_to_files_map,
			'eml_temp_paths' => $eml_temp_paths,
			'subject' => $subject,
			'message_content' => $message_content,
		];
	}

	public function _process_other_files($po_numbers_form, $file_names_form, $uploaded_files_map, $original_eml_files, $po_to_files_map) { // Procesamiento de otros archivos (NO-EML)
		$other_temp_paths = [];
		
		foreach ($po_numbers_form as $key => $po_number) {
			$files_string = $file_names_form[$key];
			$associated_filenames = $files_string;
			$associated_filenames = explode(', ', $files_string);
			
			foreach ($associated_filenames as $original_filename) {
				if (in_array($original_filename, $original_eml_files)) {
					continue;
				}

				if (isset($uploaded_files_map[$original_filename])) {
					$file_data = $uploaded_files_map[$original_filename];
					
					$_FILES['userfile'] = $file_data;
					$config['upload_path']   = './upload/';
					$config['allowed_types'] = '*';
					$config['max_size']      = 20000;
					$config['file_name']     = $original_filename;
					$this->upload->initialize($config);

					if ($this->upload->do_upload('userfile')) {
						$upload_data = $this->upload->data();
						
						if (!isset($po_to_files_map[$po_number])) {
							$po_to_files_map[$po_number] = [];
						}
						$po_to_files_map[$po_number][] = $original_filename;
						
						$other_temp_paths[] = $upload_data['full_path'];
					} else {
						$error = $this->upload->display_errors();
						log_message('error', 'Upload error: ' . $error);
						return ['error' => json_encode(['status' => 'error', 'message' => 'File upload failed: ' . strip_tags($error)])];
					}
				}
			}
		}
		
		return [
			'po_to_files_map' => $po_to_files_map,
			'other_temp_paths' => $other_temp_paths,
		];
	}

	public function _insert_pos_to_db($po_to_files_map, $registrator, $ep_mail, $customer_name, $remark) { // Inserción en la base de datos
		foreach ($po_to_files_map as $po_number => $files_for_this_po) {
			$data = [
				'po_number'     => $po_number,
				'line'          => 1,
				'registrator'   => $registrator,
				'ep_mail'       => $ep_mail,
				'po_file'       => implode(', ', $files_for_this_po),
				'customer_name' => $customer_name,
				'created'       => Date('Y-m-d H:i:s'),
				'status'        => 'Sent',
				'remark'        => $remark
			];
			$this->gen_m->insert('po_register', $data);
		}
	}
	
	public function _send_and_cleanup($eml_was_processed, $po_to_files_map, $all_temp_paths, $registrator, $ep_mail, $customer_name, $remark, $subject_to_send, $message_content, $final_cc_list) {
    
		$from_email = $ep_mail . '@lge.com';
		$attachments_to_send = $all_temp_paths;

		if (!$eml_was_processed) {
			$subject_to_send = "[{$customer_name}] Registro de Orden de Compra" . " " . Date("Ymd");
			$created = Date('Y-m-d H:i:s');
			$po_list_for_summary = array_keys($po_to_files_map);
			$po_msg = implode(", #", array_unique($po_list_for_summary));

			$message_content = "
			<html><body>
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
				</table>
				<p>Se adjuntan las ordenes de compra correspondientes.</p>
			</body></html>";
		}
		
		$to = ['mariela.carbajal@lge.com', 'elizabeth.sampe@lge.com', 'patricia.rivas@lge.com', $ep_mail . "@lge.com"];
		if ($final_cc_list){
			$final_recipient_list = array_merge($to, $final_cc_list);
		} else $final_recipient_list = $to;
		log_message('info', 'emails: ' . $final_recipient_list);
		$email_sent = $this->my_func->send_email_po($from_email, $final_recipient_list, $subject_to_send, $message_content, $attachments_to_send);

		// Limpiar archivos temporales
		foreach ($all_temp_paths as $path) {
			if (file_exists($path)) {
				unlink($path);
			}
		}
		
		if (!$email_sent) {
			log_message('error', 'Email failed to send.');
			return json_encode([
				'status' => 'warning', 
				'message' => 'PO registrado, pero el correo de notificación falló. Revisar logs.'
			]);
		}
		return json_encode(['status' => 'success', 'message' => 'PO has been registered successfully.']);
	}

	public function register_data() {
		ob_start();
		$po_to_files_map = [];
		$all_temp_paths = [];
		$subject_to_send = '';
		$message_content = '';
		$final_cc_list = [];
		$eml_was_processed = false;
		// $email_map = [
			// 'HS' => 'angel.coral@lge.com',
			// 'MS' => 'dario.vargas@lge.com',
			// 'IT' => 'renato.freundt@lge.com',
			// 'ES' => 'victorj.sanchez@lge.com' 
		// ];
		
		$email_map = [
			'HS' => 'example@lge.com',
			'MS' => 'example1@lge.com',
			'IT' => 'example2@lge.com',
			'ES' => 'example3@lge.com' 
		];
		// Obtener datos del formulario
		$registrator = $this->input->post('registrator', TRUE);
		$ep_mail = $this->input->post('ep_mail', TRUE);
		$customer_name = $this->input->post("customer_name");
		$remark = $this->input->post("remark");
		$original_eml_files = (array) $this->input->post("original_eml_files");
		$files = $_FILES['attachment'] ?? '';
		$uploaded_files_map = $this->_map_uploaded_files($files);
		$cc_emails = $this->input->post('cc_emails');
		$po_source_ac = $this->input->post('po_source_ac') ?? ''; // PO Numver Special Customers
		
		if ($cc_emails) {
			foreach ($cc_emails as $cc_email) {
				if (isset($email_map[$cc_email])) {
					$final_cc_list[] = $email_map[$cc_email];
				}
			}
		}

		$files_exist = !empty($_FILES['attachment']['name'][0]);
		$po_numbers_form = (array) $this->input->post('po_numbers_form');
		$file_names_form = (array) $this->input->post('file_names_form');
		
		foreach ($file_names_form as $index => $filename) {
			$po_number_by_user = $po_numbers_form[$index] ?? null; 
			
			if ($po_number_by_user) {
				if (!isset($po_to_files_map[$po_number_by_user])) {
					$po_to_files_map[$po_number_by_user] = [];
				}
				$po_to_files_map[$po_number_by_user][] = $filename;
			}
		}

		// 1. Manejar clientes especiales (Lógica Manual)
		$special_customers = ['SAGA FALABELLA S.A.', 'HIPERMERCADOS TOTTUS S.A.', 'TIENDAS DEL MEJORAMIENTO DEL HOGAR S.A. - [SODIMAC]'];
		if (in_array($customer_name, $special_customers)) {
			$response_json = $this->_process_special_customers($registrator, $ep_mail, $customer_name, $remark, $po_source_ac, $final_cc_list);
			ob_end_clean();
			echo $response_json; 
			return;
		}
		
		// 2. Procesar archivos .EML
		if (!empty($original_eml_files)) {
			$eml_was_processed = true;
			$result = $this->_process_eml_files($original_eml_files, $uploaded_files_map, $po_to_files_map);
			
			if (isset($result['error'])) {
				return $result['error'];
			}
			
			// Asignar los valores extraídos del EML
			$po_to_files_map = $result['po_to_files_map'];
			$all_temp_paths = array_merge($all_temp_paths, $result['eml_temp_paths']);
			$subject_to_send = $result['subject'];
			$message_content = $result['message_content'];
		}

		// 3. Procesar otros archivos (Adjuntos normales)
		$result = $this->_process_other_files(
			$this->input->post("po_numbers_form"), 
			$this->input->post("file_names_form"), 
			$uploaded_files_map, 
			$original_eml_files, 
			$po_to_files_map
		);

		if (isset($result['error'])) {
			echo $result['error'];
			return;
		}
		
		$po_to_files_map = $result['po_to_files_map'];
		$all_temp_paths = array_merge($all_temp_paths, $result['other_temp_paths']);

		// 4. Inserción Final en BD
		$this->_insert_pos_to_db($po_to_files_map, $registrator, $ep_mail, $customer_name, $remark);

		// 5. Envío de Correo y Limpieza
		$response_json  = $this->_send_and_cleanup(
			$eml_was_processed, $po_to_files_map, $all_temp_paths, 
			$registrator, $ep_mail, $customer_name, $remark, 
			$subject_to_send, $message_content, $final_cc_list
		);
		ob_end_clean();
		echo $response_json ;
	}
		
	public function email_parser() { // NOT INCLUDE THIS FUNCTION
		$file_path = 'C:\Users\lbernaldo.js\Documents\LG\Purchase Order Files\RE clientes_20250904_1439.eml';

		$parser = new Parser();
		$parser->setPath($file_path);

		// Obtener los datos del correo
		$subject = $parser->getHeader('subject');
		$from = $parser->getAddresses('from');
		$attachments = $parser->getAttachments();

		// Obtener y decodificar el cuerpo del mensaje en formato HTML
		$html_body = $parser->getMessageBody('html');

		// Si no hay cuerpo HTML, intenta obtener el de texto plano
		if (empty($html_body)) {
			$text_body = $parser->getMessageBody('text');
			echo "--- Contenido del correo (texto plano) ---" . PHP_EOL;
			echo $text_body . PHP_EOL;
		} else {
			echo "--- Contenido del correo (HTML) ---" . PHP_EOL;
			echo $html_body . PHP_EOL;
		}

		echo "Asunto: " . $subject . PHP_EOL;
		echo "De: " . $from[0]['address'] . PHP_EOL;
		
		foreach ($attachments as $attachment) {
			$filename = $attachment->getFilename();
			$fileContent = $attachment->getContent();
			// Ejemplo de cómo guardar el archivo adjunto
			file_put_contents("C:/Users/lbernaldo.js/Documents/LG/PO Register/" . $filename, $fileContent);
			echo "Archivo adjunto guardado: " . $filename . PHP_EOL;
		}
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

	public function extract_po() {
		// Verificar si se han subido archivos
		if (empty($_FILES['attachment']['name'])) {
			echo json_encode(['status' => 'error', 'message' => 'No files provided.']);
			return;
		}

		$files_by_po = [];
		$customer_name = $this->input->post('customer_name', TRUE);
		$file_count = count($_FILES['attachment']['name']);
		for ($i = 0; $i < $file_count; $i++) {
			$file_name = $_FILES['attachment']['name'][$i];
			$tmp_name = $_FILES['attachment']['tmp_name'][$i];
			$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

			$po_number = null;

			// Lógica para archivos .eml
			if ($extension === 'eml') {
				try {
					// Utiliza file_get_contents para leer el contenido del archivo
					$eml_content = file_get_contents($tmp_name);
					if ($eml_content === false) {
						throw new Exception('Could not read EML file content.');
					}
					
					$parser = new EmlParser();
					$parser->setText($eml_content);
					
					$attachments = $parser->getAttachments();
					
					foreach ($attachments as $attachment) {
						$attachment_name = $attachment->getFilename();
						$attachment_extension = strtolower(pathinfo($attachment_name, PATHINFO_EXTENSION));
						log_message('info', 'attachments: ' . $attachment_name);
						// No saltes los archivos, solo los ignora si son imágenes
						if (in_array($attachment_extension, ['png', 'jpg', 'jpeg', 'gif'])) {
							continue;
						}				
						
					// 3. Crear un archivo temporal con el contenido del adjunto
						// Esto simula que el usuario cargó este archivo directamente
						$temp_file_path = sys_get_temp_dir() . '/' . basename($attachment_name);
						if (file_put_contents($temp_file_path, $attachment_content) === false) {
							throw new Exception("Could not save attachment '{$attachment_name}' to temp file.");
						}

						// 4. Determinar si el adjunto interno necesita parseo (Excel/PDF/TXT, etc.)
						if (in_array($attachment_extension, ['xlsx', 'xls', 'pdf', 'txt'])) {

							$eml_po_number = null;
							if (preg_match('/([a-zA-Z0-9][a-zA-Z0-9_-]{4,})/u', $attachment_name, $poMatch)) {
								$eml_po_number = $poMatch[0];
							}
							if (!isset($files_by_po[$eml_po_number])) {
								$files_by_po[$eml_po_number] = [];
							}
							// Almacena el *path* temporal del adjunto extraído del EML
							$files_by_po[$eml_po_number][] = $temp_file_path; 

						} else {
							// Si es un archivo que no procesamos (ej: .zip), simplemente loguear y continuar
							log_message('info', 'Ignoring non-parsable attachment: ' . $attachment_name);
						}
						
					}
				} catch (Exception $e) {
					log_message('error', 'EML parsing error: ' . $e->getMessage());
					echo json_encode(['status' => 'error', 'message' => 'EML file could not be processed.']);
					return;
				}	
			} 
			else if ($extension === 'txt') { // Lógica para archivos .txt (usando explode como solicitaste)
				$file_parts = explode('-', pathinfo($file_name, PATHINFO_FILENAME));
				$po_number = end($file_parts);
				
				// Agrupa los archivos .txt
				if ($po_number) {
					 if (!isset($files_by_po[$po_number])) {
						$files_by_po[$po_number] = [];
					}
					$files_by_po[$po_number][] = $file_name;
				}
			}
			elseif(in_array($extension,['xls', 'xlsx'])){
				$po_numbers_from_excel = $this->_extract_po_from_excel_file($file_name, $tmp_name, $customer_name);
				
				// **1. Manejar el Fallback (si la extracción avanzada falló)**
				if (empty($po_numbers_from_excel)) {
					// Asignar el valor de ingreso manual ('-') si no se encontró nada
					$po_numbers_to_map = ['-'];
				} else {
					$po_numbers_to_map = $po_numbers_from_excel;
				}
				
				// **2. Mapear cada PO al archivo en el array final ($files_by_po)**
				// Esto crea una fila por cada PO encontrado para el mismo archivo.
				foreach ($po_numbers_to_map as $po) {
					// Agrupa los archivos
					if (!isset($files_by_po[$po])) {
						$files_by_po[$po] = [];
					}
					$files_by_po[$po][] = $file_name;
				}
			} 
			elseif ($extension === 'pdf'){
				// Llama a la lógica avanzada de extracción con fallback para PDF
				$po_numbers_from_pdf = $this->_extract_po_from_pdf_file($file_name, $tmp_name, $customer_name);

				// Manejar el Fallback (si la extracción avanzada falló)
				if (empty($po_numbers_from_pdf)) {
					 $po_numbers_to_map = ['-']; // Si no hay POs, usa '-' para ingreso manual
				} else {
					$po_numbers_to_map = $po_numbers_from_pdf;
				}

				// Mapear cada PO al archivo en el array final ($files_by_po)
				foreach ($po_numbers_to_map as $po) {
					if (!isset($files_by_po[$po])) {
						$files_by_po[$po] = [];
					}
					$files_by_po[$po][] = $file_name;
				}
			} else {
				//$po_number = null;
				if (preg_match('/[a-zA-Z]?\d{5,}(?:[_\-][a-zA-Z0-9]+)?/', $file_name, $poMatch)) {
					$po_number = $poMatch[0];
				} else $po_number = '-';
				
				// Agrupa los otros archivos
				if ($po_number) {
					if (!isset($files_by_po[$po_number])) {
						$files_by_po[$po_number] = [];
					}
					$files_by_po[$po_number][] = $file_name;
				}
			}
		}
		
		// Preparar el array final para el front-end
		$files_data = [];
		foreach ($files_by_po as $po_number => $files) {
			$files_data[] = [
				'name' => implode(', ', $files), // Unir todos los nombres de archivo en una sola cadena
				'po_number' => $po_number,
				'original_files' => $files // Pasar el array de nombres de archivo originales
			];
		}
		
		echo json_encode(['status' => 'success', 'files_data' => $files_data]);
	}
		
	private function _extract_po_from_filename($file_name, $extension = null) { // extract PO from filename
		// Si la extensión no se pasa explícitamente, la determinamos
		if (is_null($extension)) {
			$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		}

		if ($extension === 'txt') {
			// Lógica específica para .txt: Se asume que el PO es la última parte del nombre 
			// separada por guiones ('-').
			$file_parts = explode('-', pathinfo($file_name, PATHINFO_FILENAME));
			$po_number = end($file_parts);
		} else {
			// Lógica genérica: Buscar el patrón de PO en el nombre del archivo
			// El patrón busca 5 o más dígitos, opcionalmente precedidos por letras 
			// y con sufijos alfanuméricos opcionales (ej: 12345, A12345, 12345-A, 12345_B)
			
			$po_regex = '/[a-zA-Z]?\d{5,}(?:[_\-][a-zA-Z0-9]+)?/';
			
			if (preg_match($po_regex, $file_name, $poMatch)) {
				$po_number = $poMatch[0];
			} else {
				// Si no se encuentra ningún patrón, se devuelve el guion para ingreso manual
				$po_number = '-';
			}
		}
		
		// Devolvemos el PO encontrado o el guion.
		return $po_number;
	}

	// ************************** Extract excel PO ******************************
	
	public function _extract_po_from_excel_file($file_name, $tmp_name, $customer_name){
		$po_numbers = [];
		try {
			$reader = IOFactory::createReaderForFile($tmp_name);
        
			$spreadsheet = $reader->load($tmp_name);
			$worksheet = $spreadsheet->getActiveSheet();
			
			$po_numbers = $this->_get_excel_pos_by_customer($customer_name, $worksheet);
			
			if (!empty($po_numbers)) {
				return $po_numbers;
			}

		} catch (\Exception $e) {
			log_message('error', 'Spreadsheet reading error for file: ' . $file_name . '. Error: ' . $e->getMessage());
		}

		$po_from_filename = $this->_extract_po_from_filename($file_name);
		
		if ($po_from_filename === '-') {
			return ['-'];
		}
		
		return [$po_from_filename];
	}
	
	private function _extract_pos_from_column($worksheet, $column, $start_row) {
		$po_list = [];
		$row = $start_row;
		
		while (true) {
			$cell_coordinate = $column . $row;
			$cell_value = $worksheet->getCell($cell_coordinate)->getValue();

			if (empty($cell_value)) {
				break;
			}

			if (is_string($cell_value) || is_numeric($cell_value)) {
				$value = trim((string)$cell_value);
				
				if (!empty($value)) {
					$po_list[] = $value;
				}
			}

			$row++;
			if ($row > 1000) { // Límite de 1000 filas
				log_message('warning', 'Exceeded 1000 rows while extracting data from column ' . $column . '. Stopped at row ' . $row);
				break;
			}
		}

		// 4. Unicidad: Devolver solo los valores únicos
		return array_unique($po_list);
	}

	private function _get_excel_pos_by_customer($customer_name, $worksheet) {
		$po_list = [];
		
		switch (strtoupper($customer_name)) {
			case 'SUPERMERCADOS PERUANOS SOCIEDAD ANONIMA (PLAZA VEA)':
				$po_list = $this->_extract_pos_from_column($worksheet, 'B', 2); // po variables
				break;
			case 'TIENDAS PERUANAS S.A. (OECHSLE)': // po unico
				$cell_coordinate = 'B7';
				$cell_value = $worksheet->getCell($cell_coordinate)->getValue();
				
				if (is_string($cell_value) || is_numeric($cell_value)) {
					$value = trim((string)$cell_value);
					
					if (!empty($value)) {
						$po_list[] = $value;
					}
				}
				break;
			case 'HOMECENTERS PERUANOS S.A. (PROMART)':
				$cell_coordinate = 'B7';
				$cell_value = $worksheet->getCell($cell_coordinate)->getValue();

				if (is_string($cell_value) || is_numeric($cell_value)) {
					$value = trim((string)$cell_value);
					
					if (!empty($value)) {
						$po_list[] = $value;
					}
				}
				break;
			case 'COMERCIAL COUNTRY S.A.':
				$cell_coordinate = 'B17';
				$cell_value = $worksheet->getCell($cell_coordinate)->getValue();

				if (is_string($cell_value) || is_numeric($cell_value)) {
					$value = trim((string)$cell_value);
					
					if (!empty($value)) {
						$po_list[] = $value;
					}
				}
				break;
			case 'ELECTROHOGAR YATACO E.I.R.L.':
				$cell_coordinate = 'B17';
				$cell_value = $worksheet->getCell($cell_coordinate)->getValue();

				if (is_string($cell_value) || is_numeric($cell_value)) {
					$value = trim((string)$cell_value);
					
					if (!empty($value)) {
						$po_list[] = $value;
					}
				}
				break;
		}

		return array_unique($po_list);
	}
	
	// **************************************************************************
	
	// ************************** Extract pdf PO ********************************
	private function _extract_po_from_pdf_file($file_name, $tmp_name, $customer_name) {
		$po_numbers = [];

		try {
			$parser = new PdfParser();
			$pdf = $parser->parseFile($tmp_name);
			
			$pages = $pdf->getPages();
			
			// Iteramos sobre cada objeto Page
			foreach ($pages as $page) {
				$page_text = $page->getText();
				
				$page_pos = $this->_get_pdf_pos_by_customer($customer_name, $page_text);
				
				// Acumulamos los POs encontrados en la página
				$po_numbers = array_merge($po_numbers, $page_pos);
			}

			if (!empty($po_numbers)) {
				// Si se encontraron POs, los retornamos y evitamos el fallback.
				return array_unique($po_numbers);
			}

		} catch (\Exception $e) {
			// En caso de error de lectura del PDF, registramos y pasamos al fallback.
			log_message('error', 'PDF parsing error for file: ' . $file_name . '. Error: ' . $e->getMessage());
		}

		// 2. FALLBACK 1: EXTRACCIÓN POR NOMBRE DEL ARCHIVO
		$po_from_filename = $this->_extract_po_from_filename($file_name);
		
		if ($po_from_filename === '-') {
			// 3. FALLBACK 2: INGRESO MANUAL ('-')
			return ['-'];
		}
		
		// Si se encontró algo en el nombre, lo devolvemos como un array.
		return [$po_from_filename];
	}
	
	private function _get_pdf_pos_by_customer($customer_name, $full_pdf_text) {
		$po_list = [];
		$customer_key = strtoupper($customer_name);
		//log_message('info', 'CUSTOMER: ' . $customer_key);
		
		$full_pdf_text = $this->clean_text($full_pdf_text);
		//log_message('info', 'TEXT: ' . $full_pdf_text);
		switch ($customer_key) {
			
			case 'CONECTA RETAIL S.A.':
				$regex = '/Ped\.\s+Nacional\s+N°\s*.*?(\d{10})/is';
				
				if (preg_match_all($regex, $full_pdf_text, $matches)) {		
					$po_list = $matches[1];
				}
				break;
				
			case 'INTEGRA RETAIL S.A.C.':
				$regex = '/Observaciones para el Proveedor\s*.*?(\d{7})/is';
				
				if (preg_match_all($regex, $full_pdf_text, $matches)) {
					$po_list = $matches[1];
				}
				break;
			case 'IMPORTACIONES RUBI S.A.':
				$regex = '/ORDEN\s+DE\s+COMPRA\s*.*?(\d{7})/is';
				
				if (preg_match_all($regex, $full_pdf_text, $matches)) { 
					$po_list = $matches[1];
				}
				break;
			case 'ESTILOS S.R.L.':
				$regex = '/# L (\d+)/';
				
				if (preg_match_all($regex, $full_pdf_text, $matches)) { 
					$po_list = $matches[1];
				}
				break;
			case 'IMPORTACIONES HIRAOKA S.A.':
				$regex = '/# (\d+)/';
				
				if (preg_match_all($regex, $full_pdf_text, $matches)) { 
					$po_list = $matches[1];
				}
				break;
			case 'CENCOSUD RETAIL PERU S.A. - [METRO]':
				$regex = '/Número (\d+)/';
				
				if (preg_match_all($regex, $full_pdf_text, $matches)) { 
					$po_list = $matches[1];
				}
				break;
				
		}

		return array_unique($po_list);
	}
	
	private function clean_text($text) {
		
        $text = preg_replace('/[\p{C}&&[^\n\r\t]]/u', '', $text);
        $text = str_replace(["\r\n", "\r", "\n"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        // Ajuste para unir líneas: solo si es alfanumérico o puntuación común
        $text = preg_replace('/([a-zA-Z0-9.,])\s*\n\s*([a-zA-Z0-9.,])/', '$1 $2', $text);
        $text = trim($text);
        return $text;
    }
	
	// **************************************************************************
	
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
		
		//echo '<pre>'; print_r($customer_po_list);
		
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
		
		// Unir datos de Ventas y Cerrados por customer_po_no
		$all_data = array_merge($sales_data, $closed_data);

		if ($all_data) {
			foreach($all_data as $item) {
				// Asumiendo que ambas tablas tienen el campo 'customer_po_no'
				$list_data[$item->customer_po_no][] = $item;
			}
		}
		
		//echo '<pre>'; print_r($list_data);
		
		
		$data_multi = [];
		//echo '<pre>'; print_r($default_values);
				
		foreach ($customer_po_list as $po_number){
			if(!empty($list_data[$po_number])){
				$list_data_ =  $list_data[$po_number];
			} else continue;
			
			$line = 1;
			if ($list_data_){
				foreach ($list_data_ as $index => $item) {
					//$data = ['po_number' => $po_number, 'model' => $item->model, 'line' => $line,'qty' => ($item->ordered_qty ?? $item->order_qty)];
					$default_values[$po_number]['model'] = $item->model;
					$default_values[$po_number]['qty'] = $item->ordered_qty ?? $item->order_qty;
					$default_values[$po_number]['amount_usd'] = $item->sales_amount_usd ?? $item->order_amount_usd;
					$default_values[$po_number]['line'] = $line;
					$default_values[$po_number]['order_no'] = $item->order_no;
					$default_values[$po_number]['line_no'] = $item->line_no;
					$data = $default_values[$po_number];
					
					if ($this->gen_m->filter_select('po_register', false, ['order_no', 'line_no'], ['order_no' =>  $item->order_no, 'line_no' => $item->line_no])) continue;
					else { //FALTA CONSIDERAR NUEVOS CASOS en caso de nuevos ingresos problemas con line = 1
						//$key = $item->order_no . "_" . $item->line_no . "_" . $item->line;
						if ($index == 0 && empty($po_first_data[$po_number]->model) && empty($po_first_data[$po_number]->order_no)){ // First insert data from form							
							$this->gen_m->update('po_register', ['po_number' => $po_number, 'line' => 1], $data);
							//echo '<pre>'; print_r($data);
						} else {
							$data_multi[] = $data;
						}
						$line += 1;
					}
				}
			}
		}
		
		if ($data_multi) $this->gen_m->insert_m('po_register', $data_multi);
		
		//echo '<pre>'; print_r($data_multi);
	}		
}
