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
	
	// Access Data
	private $WSDL_URL = "https://ereceipt-pe-s02.sovos.com/axis2/services/Online?wsdl";
	private $SOVOS_URL = "https://ereceipt-pe-s02.sovos.com/Facturacion/services/OnlineRecoveryService";
	private $API_RUC = "20375755344";
	private $API_LOGIN = "adminppl";
	private $API_CLAVE = "abc123";
	private $SOAP_EXECUTION_ENDPOINT = "https://ereceipt-pe-s02.sovos.com/axis2/services/Online.OnlineHttpSoap12Endpoint/";
	
	public function process_request(){
        
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
			// 1. Llamada para PDF (tipo_retorno = 2)
			$pdf_result = $this->_execute_online_recovery($doc_type, $doc_number, '2');
			
			// 2. Llamada para XML (tipo_retorno = 1)
			$xml_result = $this->_execute_online_recovery($doc_type, $doc_number, '1');
			
            // 3. Consolidación de Resultados
			$request_xml = $pdf_result['request_xml'];
			$response_xml = $pdf_result['response_xml'];

            if ($pdf_result['success']) {
                $pdf_link = $pdf_result['download_link'];
            }
            if ($xml_result['success']) {
                 $xml_link = $xml_result['download_link'];
            }
            
            // 4. Determinar Estado Final (Para la interfaz)
            if ($pdf_link || $xml_link) {
                $http_code = 200; // Éxito si al menos uno se encontró
                $response_message = 'Documento(s) encontrado(s). Use los botones para descargar.';
            } elseif ($pdf_result['http_code'] === 200 && $xml_result['http_code'] === 200) {
                // Ambos fallaron lógicamente (Error 404 de negocio)
                $http_code = 404;
                $response_message = "Documento no encontrado o error SOVOS: {$pdf_result['msj_error']}";
            } else {
                // Error de conexión o servidor
                $http_code = $pdf_result['http_code'] === 503 ? 503 : 500;
                $response_message = "Error crítico. PDF: {$pdf_result['msj_error']} | XML: {$xml_result['msj_error']}";
            }

        } catch (\Exception $e) {
            $http_code = 503;
            $response_message = "CRITICAL ERROR: " . $e->getMessage();
        }
        
        $data_to_view = [
            "pdf_link"      => $pdf_link,
            "xml_link"      => $xml_link,
            "status_code"   => $http_code,
            "request_xml"   => $request_xml,
            "response_xml"  => $response_xml,
            "debug_message" => $response_message
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
            
            // --- Lógica de Parseo de XML (La misma que ya funcionaba) ---
            $soap_response_obj = simplexml_load_string($xml_response_string); 
            
            // Asegúrate de usar los namespaces correctos
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
}