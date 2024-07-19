<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_niubiz extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
	}
	
	public function index(){
		//load library or make instance of My_niubiz class
		$this->load->library('my_niubiz');
		
		///////////////////////////////////////////////////////////////// Step 1: make access token
		$accessToken = $this->my_niubiz->getAccessToken();
		if ($accessToken["success"]){
			echo "Access token result:<br/>";
			print_R($accessToken);
			echo "<br/><br/>=====================<br/><br/>";
		}else{
			echo $accessToken["msg"];
			return;
		}
		
		///////////////////////////////////////////////////////////////// Step 2: make session token
		$session_token_data = [
			'channel' => 'web',
			'antifraud' => [
				//you define own business values here
				'merchantDefineData' => [
						'orderNo' => '01234567890',
						'myField1' => 'myValue1',
						'myField2' => 'myValue2',
						'myField3' => 'myValue3'
				],
				'clientIp' => '24.212.107.30'
			],
			'dataMap' => [
				'cardholderCity' => 'Lima',
				'cardholderCountry' => 'PE',
				'cardholderAddress' => 'Av Jose Pardo 831',
				'cardholderPostalCode' => '12345',
				'cardholderState' => 'LIM',
				'cardholderPhoneNumber' => '987654321'
			],
			'amount' => 123234.34
		];
		
		//You can make session token generation error uncommenting below examples
		//Bad request or no autorized 	=> $accessToken["accessToken"] = "1111111";
		//Used access token 			=> $accessToken["accessToken"] = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIwTWR3R0R6RjQ1YS1SbWs3bkhwc2lNYUJweFJQRjNzekEtNW1HWFllMThvIn0.eyJleHAiOjE3MjE0MzQ2NzEsImlhdCI6MTcyMTQzMTA3MSwianRpIjoiNmQyYjNjNjgtOGQ5My00MTFhLTg5YzAtNmVlNjNiYjU2ZDI4IiwiaXNzIjoiaHR0cHM6Ly9hY2Nlc3MuaW50dm50LmNvbS9hdXRoL3JlYWxtcy9vbmxpbmUtYXBpcyIsInN1YiI6IjQyNjg5NzZlLWVhOWEtNDI0Yi04YWEwLTY5ZWYwMjA5NTJkZSIsInR5cCI6IkJlYXJlciIsImF6cCI6ImFwcC1tdWx0aXJlZ2lvbiIsInNlc3Npb25fc3RhdGUiOiI3Yzg3YTRjZS1hOGY2LTQyNjMtODc5My04YWE4MDNhNTI2YmMiLCJhY3IiOiIxIiwic2NvcGUiOiJhd3MuY29nbml0by5zaWduaW4udXNlci5hZG1pbiIsImdyb3VwcyI6W10sInVzZXJuYW1lIjoiaW50ZWdyYWNpb25lc0BuaXViaXouY29tLnBlIn0.igIk_sFvENScwjqgfe7BUs_BlExZQdSSshtA6Hd0lY-yUBJHg7Grzll0lHwsp57DV2W9e3NgzRG2sLJKp_X9N8iMWSoaYPT8nlQnkuQvgOajjTjZBg9W-81oWqszwgiW7wwxz2Km75PoowUFPvSgwOkqqZfwy6jeqlcr4q04MTpKpALOa89I-3eRkYi9PHAsmm7HMSfaVTAcNX2eo6rn4UHMCy-_dKy3RkFnvdBoTLmKCASE-gk0GBwLwlBGsrxd4ZRwDpAZ0StCK46GHetkRjL0U1cfrh-ga-v4yaD2Wcc2NOLfZoKOfa_dXlnnIBqtCKQjDalB3vH1vqQl6Jk3bw";
		$sessionToken = $this->my_niubiz->getSessionToken($accessToken["accessToken"], $session_token_data);
		if ($sessionToken["success"]){
			echo "Session token result:<br/>"; 
			print_R($sessionToken); 
			echo "<br/><br/>=====================<br/><br/>";
		}else{
			echo $sessionToken["errorCode"]." - ".$sessionToken["errorMessage"];
			return;
		}
		
		///////////////////////////////////////////////////////////////// Step 3: request autorization
		
		
		//$this->load->view("module/obs_niubiz/index");
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
		$clientIp = '24.252.107.29';//Client IP that you have to set
		
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
				'merchantDefineData' => [//you can add any business values here. Here i use orderNo
					'orderNo' => $orderNo,
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
		
		$result = $this->my_niubiz->get_session_token($session_token_data);
		
		//return with order number or magento id to redirection after payment
		$result["orderNo"] = $orderNo;
		$result["amount"] = $amount;
		$result["merchantId"] = $this->my_niubiz->get_merchantId();
		
		header('Content-Type: application/json');
		echo json_encode($result);
	}
}
