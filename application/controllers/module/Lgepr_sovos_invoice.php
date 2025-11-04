<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lgepr_sovos_invoice extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
		$this->load->helper(['url', 'form']);
		$this->load->library('form_validation');
	}
	
	public function index($data_override = []){
		$raw_documents = ["01	- Factura", "03	- Boleta", "07	- Nota de Crédito", "08	- Nota de Débito", "09	- Guía de Remisión Remitente", "20	- Comprobante de Retención", "40 - Comprobante de Percepción"];
	
		$documents = [];
        foreach ($raw_documents as $doc_str) {
            $raw_code = trim(substr($doc_str, 0, 2));
            
            $code = (int) $raw_code; 

            $documents[$code] = $doc_str;
        }

		$default_data = [
            "documents"     => $documents,
            "title"         => 'Interfaz de API SOBOS',
            "result"        => null,
            "status_code"   => null,
            "pdf_link"      => null,
            "xml_link"      => null,
            "main"			=> "module/lgepr_sovos_invoice/index",
        ];
		$data = array_merge($default_data, $data_override);
		$this->load->view('layout', $data);
	}
	
	const ZIP_SERVER_PATH = 'C:/xampp/htdocs/llamasys/upload_file/Lgepr/sovos_zip/';
	// Access Data
	private $WSDL_URL = "https://ereceipt-pe-s02.sovos.com/axis2/services/Online?wsdl";
	private $SOVOS_URL = "https://ereceipt-pe-s02.sovos.com/Facturacion/services/OnlineRecoveryService";
	private $API_RUC = "20375755344";
	private $API_LOGIN = "adminppl";
	private $API_CLAVE = "abc123";
	private $SOAP_EXECUTION_ENDPOINT = "https://ereceipt-pe-s02.sovos.com/axis2/services/Online.OnlineHttpSoap12Endpoint/";
	
	public function process_request(){
        ini_set('memory_limit', '2G');
		set_time_limit(0);
		
		$doc_type = $this->input->post('document_type', TRUE);
		$doc_number = $this->input->post('document_number', TRUE);
        
		if (empty($doc_type) || empty($doc_number)) {
			return $this->index();
		}

		$pdf_link = null;
		$xml_link = null;
		$http_code = 500;
		$response_message = 'Iniciando búsqueda de PDF y XML...';
		$request_xml = '';
		$response_xml = '';
        
        try {
			$pdf_result = $this->_execute_online_recovery($doc_type, $doc_number, '2');
			
			$xml_result = $this->_execute_online_recovery($doc_type, $doc_number, '1');
			
			$request_xml = $pdf_result['request_xml'];
			$response_xml = $pdf_result['response_xml'];

            if ($pdf_result['success']) {
                $pdf_link = $pdf_result['download_link'];
				log_message('info', 'PDF link: ' . $pdf_link);
            }
            if ($xml_result['success']) {
                $xml_link = $xml_result['download_link'];
				log_message('info', 'XML link: ' . $xml_link);
            }
            
            if ($pdf_link || $xml_link) {
                $http_code = 200;
                $response_message = 'Document(s) found. Use the buttons to download.';
            } elseif ($pdf_result['http_code'] === 200 && $xml_result['http_code'] === 200) {
                $http_code = 404;
                $response_message = "Document not found or SOVOS error: {$pdf_result['msj_error']}";
            } else {
                $http_code = $pdf_result['http_code'] === 503 ? 503 : 500;
                $response_message = "Error crítico. PDF: {$pdf_result['msj_error']} | XML: {$xml_result['msj_error']}";
            }

        } catch (\Exception $e) {
            $http_code = 503;
            $response_message = "CRITICAL ERROR: " . $e->getMessage();
        }
        log_message('info', 'Status code: '. $http_code);
        $data_to_view = [
            "pdf_link"      => $pdf_link,
            "xml_link"      => $xml_link,
            "status_code"   => $http_code,
            "request_xml"   => $request_xml,
            "response_xml"  => $response_xml,
            "debug_message" => $response_message,
			"selected_doc_type" => $doc_type,
			"selected_doc_number" => $doc_number
        ];

        $this->index($data_to_view);
    }
	
	private function _execute_online_recovery($doc_type, $doc_number, $tipo_retorno) {
        
        $soap_message = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ws="http://ws.online.asp.core.paperless.cl">
           <soap:Header/>
           <soap:Body>
              <ws:OnlineRecovery>
                 <ws:ruc>' . htmlspecialchars($this->API_RUC) . '</ws:ruc>
                 <ws:login>' . htmlspecialchars($this->API_LOGIN) . '</ws:login>
                 <ws:clave>' . htmlspecialchars($this->API_CLAVE) . '</ws:clave>
                 <ws:tipoDoc>' . htmlspecialchars($doc_type) . '</ws:tipoDoc>
                 <ws:folio>' . htmlspecialchars($doc_number) . '</ws:folio>
                 <ws:tipoRetorno>' . htmlspecialchars($tipo_retorno) . '</ws:tipoRetorno>
              </ws:OnlineRecovery>
           </soap:Body>
        </soap:Envelope>';

        $options = [
            'trace'          => 1, 'exceptions'  => 1, 'soap_version' => SOAP_1_2,
            'cache_wsdl'     => WSDL_CACHE_NONE,
            'stream_context' => stream_context_create(['ssl' => [
                'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true 
            ]]),
            'location' => $this->SOAP_EXECUTION_ENDPOINT,
            'uri' => "http://ws.online.asp.core.paperless.cl",
        ];

        $download_link = null;
        $http_code = 500;
        $xml_response_string = '';
        $cod_error = '99';
        $msj_error = 'Fallo de conexión o formato de XML.';
        $success = false;

        try {
            $client = new SoapClient(null, $options); 
            $xml_response_string = $client->__doRequest(
                $soap_message, 
                $this->SOAP_EXECUTION_ENDPOINT, 
                '', 
                SOAP_1_2 
            );
            $http_code = 200;
            
            $soap_response_obj = simplexml_load_string($xml_response_string); 
            
			if ($soap_response_obj === FALSE) {
				$msj_error = 'ERROR: The server response was not valid XML (or was empty).';
				$http_code = 500;
				return [
					'success'       => FALSE,
					'msj_error'     => $msj_error,
					'http_code'     => $http_code,
					'request_xml'   => $soap_message,
					'response_xml'  => $xml_response_string
				];
			}

            $soap_response_obj->registerXPathNamespace('soapenv', "http://www.w3.org/2003/05/soap-envelope");
            $soap_response_obj->registerXPathNamespace('ns', "http://ws.online.asp.core.paperless.cl");
            
            $return_nodes = $soap_response_obj->xpath('//ns:OnlineRecoveryResponse/ns:return');
            $return_node = count($return_nodes) > 0 ? $return_nodes[0] : null;

            if ($return_node) {
                $inner_xml_string = (string)$return_node; 
                $decoded_inner_xml_string = html_entity_decode($inner_xml_string, ENT_QUOTES, 'UTF-8');
                $final_xml_string = str_replace('&', '&amp;', $decoded_inner_xml_string);
                $inner_xml_obj = simplexml_load_string($final_xml_string);
                
                if ($inner_xml_obj) {
                    $cod_error = (string)$inner_xml_obj->Codigo;
                    $msj_error = (string)$inner_xml_obj->Mensaje;

                    if ($cod_error === '0') {
                        $download_link = $msj_error;
                        $success = true;
                    }
                } else {
                     $msj_error = 'ERROR: No se pudo parsear el XML de la respuesta de negocio.';
                }
            } else {
                 $msj_error = 'ERROR: No se encontró el nodo de retorno.';
            }

        } catch (\Exception $e) {
            $http_code = 503; 
            $msj_error = "CRITICAL ERROR: " . $e->getMessage();
        }
        
        return [
            'success'       => $success,
            'download_link' => $download_link,
            'msj_error'     => $msj_error,
            'http_code'     => $http_code,
            'request_xml'   => $soap_message,
            'response_xml'  => $xml_response_string
        ];
    }

	public function process_massive_request() {
		ini_set('memory_limit', '2G');
		set_time_limit(0);

		$doc_type = $this->input->post('document_type_massive', TRUE);
		$doc_start = $this->input->post('document_number_start', TRUE);
		$doc_end = $this->input->post('document_number_end', TRUE);

		$data_to_view = [
			'massive_status_code' => 400,
			'massive_debug_message' => "Missing required range inputs (Type, Start, or End document).",
			'massive_errors' => []
		];

		if (empty($doc_type) || empty($doc_start) || empty($doc_end)) {
			return $this->index($data_to_view);
		}

		if (!preg_match('/^([A-Za-z0-9-]+)-(\d+)$/', $doc_start, $match_start) ||
			!preg_match('/^([A-Za-z0-9-]+)-(\d+)$/', $doc_end, $match_end) ||
			$match_start[1] !== $match_end[1] || 
			(int)$match_start[2] >= (int)$match_end[2]) {
			
			$data_to_view['massive_debug_message'] = "Invalid document number format. Expected format: PREFIX-NUMBER (e.g., T101-00023350).";
			return $this->index($data_to_view);
		}

		$prefix = $match_start[1];
		$number_start = (int)$match_start[2];
		$number_end = (int)$match_end[2];
		$number_length = strlen($match_start[2]); 
		$zip_id = uniqid('sovos_batch_', true);
		$temp_dir = self::ZIP_SERVER_PATH;
		
		if (!is_dir($temp_dir)) {
			mkdir($temp_dir, 0777, true);
		}
		
		$zip_file_path = $temp_dir . 'documents_' . $zip_id . '.zip';
		$total_processed = 0;
		$total_found = 0;
		$errors = [];
		
		$zip_file_name = 'documents_' . $zip_id . '.zip';
		$zip_file_path = $temp_dir . $zip_file_name;
	
		try {
			$zip = new ZipArchive;
			if ($zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
				throw new \Exception("No se pudo crear el archivo ZIP.");
			}

			for ($i = $number_start; $i <= $number_end; $i++) {
				$total_processed++;
				$current_number_padded = str_pad($i, $number_length, '0', STR_PAD_LEFT);
				$current_doc_number = $prefix . '-' . $current_number_padded;

				$pdf_result = $this->_execute_online_recovery($doc_type, $current_doc_number, '2');
				
				$xml_result = $this->_execute_online_recovery($doc_type, $current_doc_number, '1');

				$found_pdf = $pdf_result['success'] ?? false;
				$found_xml = $xml_result['success'] ?? false;
				
				$pdf_url = $pdf_result['download_link'] ?? '';
				$xml_url = $xml_result['download_link'] ?? '';

				if ($found_pdf) {
					$pdf_content = $this->_fetch_file_content_from_url($pdf_url);
					if ($pdf_content !== false) {
						$total_found++;
						$zip->addFromString("{$current_doc_number}.pdf", $pdf_content);
					} else {
						$found_pdf = false; 
					}
				}
				
				if ($found_xml) {
					$xml_content = $this->_fetch_file_content_from_url($xml_url);
					if ($xml_content !== false) {
						$total_found++;
						$zip->addFromString("{$current_doc_number}.xml", $xml_content);
					} else {
						$found_xml = false;
					}
				}
				if (!$found_pdf && !$found_xml) {
					$errors[] = $current_doc_number;
				}
			}
			
			$zip->close();
			
			//$zip_url = site_url('./upload_file/Lgepr/sovos_zip/' . $zip_file_name);
			$zip_url = site_url('module/lgepr_sovos_invoice/download_zip_and_delete/' . $zip_file_name);
			$http_code = $total_found > 0 ? 200 : 404;

			if ($http_code === 200) {
				$final_message = "Massive process completed. Documents found: {$total_found} / Processed: {$total_processed}.";
			} else {
				$final_message = "Massive process completed. No documents found in the specified range.";
			}

			$data_to_view['massive_zip_link'] = $zip_url;
			$data_to_view['massive_status_code'] = $http_code;
			$data_to_view['massive_debug_message'] = $final_message;
			$data_to_view['massive_errors'] = $errors;

		} catch (\Exception $e) {
			$data_to_view['massive_status_code'] = 500;
			$data_to_view['massive_debug_message'] = "CRITICAL ERROR during processing: " . $e->getMessage();
		}
		
		$this->index($data_to_view);
	}

	public function download_zip_and_delete($zip_file_name) {
    
		$zip_file_name = basename($zip_file_name);
		$zip_file_path = self::ZIP_SERVER_PATH . $zip_file_name; 
		
		log_message('info', 'Download initiated for: '. $zip_file_name);
		log_message('info', 'Path used for borrado: '. $zip_file_path);
		
		if (!file_exists($zip_file_path)) {
			log_message('error', 'Error 404: File not found at path: ' . $zip_file_path);
			show_404();
			return;
		}

		@ini_set('error_reporting', E_ALL & ~E_NOTICE);
		@apache_setenv('no-gzip', 1);
		@ini_set('zlib.output_compression', 'Off');
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . $zip_file_name . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($zip_file_path));

		if (ob_get_level() > 0) {
			ob_end_clean(); 
		}

		readfile($zip_file_path);

		if (file_exists($zip_file_path)) {
			@flush(); 
			
			@chmod($zip_file_path, 0777); 
			
			if (@unlink($zip_file_path)) {
				 log_message('info', 'SUCCESS: ZIP file deleted successfully: ' . $zip_file_path);
			} else {
				 log_message('error', 'FAILURE: Failed to delete ZIP file (OS Lock/Permissions after transfer).');
			}
		}
		
		exit;
	}
	
	private function _fetch_file_content_from_url($url) {
		if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		$content = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		
		if ($http_code === 200 && $content !== false && !empty($content)) {
			return $content;
		}
		
		log_message('error', "Failed to download file from URL: {$url}. HTTP Code: {$http_code}. Error: {$error}");

		return false;
	}
	
	public function download_zip($zip_id) {
		$zip_id = basename($zip_id); 
		
		$temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zip_id . DIRECTORY_SEPARATOR;
		$zip_file_path = $temp_dir . 'documents_' . $zip_id . '.zip';
		
		if (!file_exists($zip_file_path)) {
			show_404();
		}

		$this->load->helper('download');
		
		$data = file_get_contents($zip_file_path);
		force_download('sovos_batch_' . $zip_id . '.zip', $data);

		unlink($zip_file_path);
		rmdir($temp_dir); 
	}
}