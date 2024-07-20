<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_niubiz extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
	}
	
	public function index(){
		///////////////////////////////////////////////////////////////// Step 0: transaction basic data
		$clientIp = '24.212.107.30';
		
		$card = [
			//'cardNumber' => '4551708161768059',//with installment
			'cardNumber' => '4474118355632240',//without installment
			'expirationMonth' => 3,
			'expirationYear' => 2028,
			'cvv2' => '111'
		];
		
		$cardHolder = [
			'firstName' => 'Pedro',
			'lastName' => 'Galdamez',
			'phoneNumber' => '012223333',
			'email' => 'integraciones@niubiz.com.pe',
		];//client
		
		$order = [
			'purchaseNumber' => '01234567890',//order number or magento id in our case
			'currency' => 'PEN',
			'amount' => 1234.23
		];
		
		$shipping = [
			'cardholderCity' => 'Lima',
			'cardholderCountry' => 'PE',
			'cardholderAddress' => 'Av Jose Pardo 831',
			'cardholderPostalCode' => '12345',
			'cardholderState' => 'LIM',
			'cardholderPhoneNumber' => '987654321'
		];
		
		//load library or make instance of My_niubiz class
		$this->load->library('my_niubiz');
		
		///////////////////////////////////////////////////////////////// Step 1: session token
		$data = [
			'channel' => 'web',
			'antifraud' => [
				//you define own business values here
				'merchantDefineData' => [
					'purchaseNumber' => $order['purchaseNumber'],
					'myField1' => 'myValue1',
					'myField2' => 'myValue2',
					'myField3' => 'myValue3'
				],
				'clientIp' => $clientIp,
			],
			'dataMap' => $shipping,
			'amount' => $order['amount']
		];
		
		//You can make session token generation error uncommenting below examples
		//Bad request or no autorized 	=> $accessToken["accessToken"] = "1111111";
		//Used access token 			=> $accessToken["accessToken"] = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIwTWR3R0R6RjQ1YS1SbWs3bkhwc2lNYUJweFJQRjNzekEtNW1HWFllMThvIn0.eyJleHAiOjE3MjE0MzQ2NzEsImlhdCI6MTcyMTQzMTA3MSwianRpIjoiNmQyYjNjNjgtOGQ5My00MTFhLTg5YzAtNmVlNjNiYjU2ZDI4IiwiaXNzIjoiaHR0cHM6Ly9hY2Nlc3MuaW50dm50LmNvbS9hdXRoL3JlYWxtcy9vbmxpbmUtYXBpcyIsInN1YiI6IjQyNjg5NzZlLWVhOWEtNDI0Yi04YWEwLTY5ZWYwMjA5NTJkZSIsInR5cCI6IkJlYXJlciIsImF6cCI6ImFwcC1tdWx0aXJlZ2lvbiIsInNlc3Npb25fc3RhdGUiOiI3Yzg3YTRjZS1hOGY2LTQyNjMtODc5My04YWE4MDNhNTI2YmMiLCJhY3IiOiIxIiwic2NvcGUiOiJhd3MuY29nbml0by5zaWduaW4udXNlci5hZG1pbiIsImdyb3VwcyI6W10sInVzZXJuYW1lIjoiaW50ZWdyYWNpb25lc0BuaXViaXouY29tLnBlIn0.igIk_sFvENScwjqgfe7BUs_BlExZQdSSshtA6Hd0lY-yUBJHg7Grzll0lHwsp57DV2W9e3NgzRG2sLJKp_X9N8iMWSoaYPT8nlQnkuQvgOajjTjZBg9W-81oWqszwgiW7wwxz2Km75PoowUFPvSgwOkqqZfwy6jeqlcr4q04MTpKpALOa89I-3eRkYi9PHAsmm7HMSfaVTAcNX2eo6rn4UHMCy-_dKy3RkFnvdBoTLmKCASE-gk0GBwLwlBGsrxd4ZRwDpAZ0StCK46GHetkRjL0U1cfrh-ga-v4yaD2Wcc2NOLfZoKOfa_dXlnnIBqtCKQjDalB3vH1vqQl6Jk3bw";
		$sessionToken = $this->my_niubiz->getSessionToken($data);
		if ($sessionToken["success"]){
			echo "Session token result:<br/>"; 
			print_R($sessionToken); 
			echo "<br/><br/>=====================<br/><br/>";
		}else{
			echo $sessionToken["errorCode"]." - ".$sessionToken["errorMessage"];
			return;
		}
		
		///////////////////////////////////////////////////////////////// Step 2: antifraud
		$data = [
			'channel' => 'web',
			'order' => $order,
			'card' => $card,
			'cardHolder' => $cardHolder,
			'clientIp' => $clientIp,
		];
		
		$antifraud = $this->my_niubiz->antifraud($data);
		if ($antifraud["success"]){
			echo "Antifraud result:<br/>"; 
			print_R($antifraud);
			echo "<br/><br/>=====================<br/><br/>";
		}else{
			echo $antifraud["errorCode"]." - ".$antifraud["errorMessage"];
			return;
		}
		
		///////////////////////////////////////////////////////////////// Step 3: autorization
		$order['tokenId'] = $antifraud['token']->tokenId;
		//$order['externalTransactionId'] = $order['purchaseNumber']."-".$order['purchaseNumber'];
		$order['installment'] = 12;
		
		$data = [
			'channel' => 'web',
			'captureType' => 'manual',
			'countable' => true,
			'order' => $order,
			'card' => $card,
			'cardHolder' => $cardHolder,
		];
		
		$autorization = $this->my_niubiz->authorization($data);
		if ($autorization["success"]){
			echo "Autorization result:<br/>"; 
			print_R($autorization); 
			echo "<br/><br/>=====================<br/><br/>";
		}else{
			echo $autorization["errorCode"]." - ".$autorization["errorMessage"];
			return;
		}
		
		//$this->load->view("module/obs_niubiz/index");
	}
	
}
