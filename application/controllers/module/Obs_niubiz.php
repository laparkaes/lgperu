<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_niubiz extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
	}
	
	public function index(){
		$this->load->view("module/obs_niubiz/index");
	}
	
	public function get_session_key(){
		$this->load->library('my_niubiz');
		
		$data = $this->input->post();
		/*
		[totalAmount] => 14797.01
		[firstName] => Hoon Woo
		[lastName] => Kim
		[telephone] => 992533096
		[additionalTelephone] => 993322119
		[documentType] => CE
		[documentNumber] => 000765823
		[streetAddress] => Av. Republica de Panama 4077 Dpto 2305
		[region] => 3165
		[city] => 37956
		[district] => 540285
		[postcode] => 150141
		[typeOfResidence] => Departamento con ascensor
		[references] => cruce entre Av Tomas Marsano y Av Republica de Panama
		[country] => PE
		*/
		
		/* Used in 'antifraud' array */
		$orderNo = '1234567890';//load from somewhere if you need. this is optional
		$magentoId = '0987654321';//load from somewhere if you need. this is optional
		$clientIp = '24.252.107.29';//Client IP
		
		/* Used in 'dataMap' array */
		$cardholderCity = "Lima";//set city based on $data["city"]
		$cardholderCountry = "PE";//you can use $data["country"]
		$cardholderAddress = $data["streetAddress"];//customer billing address. I am using $data["streetAddress"]
		$cardholderPostalCode = $data["postcode"];//you can use $data["postcode"]
		$cardholderState = "LIM";//set city based on $data["region"]
		$cardholderPhoneNumber = $data["telephone"];//you can use $data["telephone"]
		
		/* Used for 'amount' key value */
		$amount = $data["totalAmount"];//you can use $data["totalAmount"]
		
		$session_token_data = [
			'channel' => 'web',//default
			'antifraud' => [
				'merchantDefineData' => [//you can add any business values here. Ex] i am adding orderNo and magentoId
					'orderNo' => $orderNo,
					'magentoId' => $magentoId,
					'anyKey1' => 'anyValue1',
					'anyKey2' => 'anyValue2',
					'anyKey3' => 'anyValue3',
				],
				'clientIp' => $clientIp
			],
			'dataMap' => [
				'cardholderCity' => $cardholderCity,
				'cardholderCountry' => $cardholderCountry,
				'cardholderAddress' => $cardholderAddress,
				'cardholderPostalCode' => $cardholderPostalCode,
				'cardholderState' => $cardholderState,
				'cardholderPhoneNumber' => $cardholderPhoneNumber
			],
			'amount' => $amount//Transaction amount
		];
		
		$result = $this->my_niubiz->get_session_key($session_token_data);
		
		//return with order number or magento id to redirection after payment
		$result["orderNo"] = $orderNo;
		$result["magentoId"] = $magentoId;
		$result["amount"] = $amount;
		$result["merchant_id"] = $this->my_niubiz->get_merchant_id();
		
		header('Content-Type: application/json');
		echo json_encode($result);
	}
}
