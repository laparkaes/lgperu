<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_niubiz extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
	}
	
	public function index(){
		$this->load->library('my_niubiz');
		
		$my_niubiz = $this->my_niubiz;
		
		//generate access token
		$access_token = $my_niubiz->access_token();
		echo "Access token result:<br/>"; print_r($access_token); echo "<br/><br/>";
		
		if ($access_token["success"]){
			//generate session token
			$session_token_data = [
				'channel' => 'pasarela',
				/*
				channel values:
				- web: If they are operations carried out with the web payment button
				- mobile: If they are operations carried out with the app payment library
				- pasarela: If they are operations carried out with the authorization API for the e-commerce product
				- callcenter: If they are operations carried out with the authorization API for the telepayment product
				- recurrent: If they are operations carried out with the authorization API for the scheduled payment product
				*/
				'antifraud' => [
					'merchantDefineData' => [//you can add any business values here. Ex] i am adding orderNo and magentoId
						'orderNo' => '1234567890',
						'magentoId' => '0987654321',
						'anyKey' => 'anyValue',
					],
					'clientIp' => '24.252.107.29'
				],
				'dataMap' => [
					'cardholderCity' => 'Lima',
					'cardholderCountry' => 'PE',
					'cardholderAddress' => 'Av Jose Pardo 831',
					'cardholderPostalCode' => '12345',
					'cardholderState' => 'LIM',
					'cardholderPhoneNumber' => '987654321'
				],
				'amount' => 12.35
			];
			
			$access_token["token"] = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIwTWR3R0R6RjQ1YS1SbWs3bkhwc2lNYUJweFJQRjNzekEtNW1HWFllMThvIn0.eyJleHAiOjE3MjExNDk4NTEsImlhdCI6MTcyMTE0NjI1MSwianRpIjoiMDRkMDViMWQtM2M4My00OTE5LWFmYjAtYmQ0OGM1ZTlkZmY2IiwiaXNzIjoiaHR0cHM6Ly9hY2Nlc3MuaW50dm50LmNvbS9hdXRoL3JlYWxtcy9vbmxpbmUtYXBpcyIsInN1YiI6IjQyNjg5NzZlLWVhOWEtNDI0Yi04YWEwLTY5ZWYwMjA5NTJkZSIsInR5cCI6IkJlYXJlciIsImF6cCI6ImFwcC1tdWx0aXJlZ2lvbiIsInNlc3Npb25fc3RhdGUiOiI5ZjFmYTA5OC02ODMyLTQyOTktOTgyNi00OWQ1ZDhkNzIxZDAiLCJhY3IiOiIxIiwic2NvcGUiOiJhd3MuY29nbml0by5zaWduaW4udXNlci5hZG1pbiIsImdyb3VwcyI6W10sInVzZXJuYW1lIjoiaW50ZWdyYWNpb25lc0BuaXViaXouY29tLnBlIn0.I18qLucB3v5DOAwgpFTb71LeErdMgVyRHZ0W4RdN9z4OSr7uPnuzkzKSQf8uwjnh8SbU0fpISAxRFkoKGJ6d_djQUwPGhNsjrJNniRyRT2vN8_97UI4S-wvtF97lXYfVRegFuXAQCmyhzRXfk5gz65O1aAOXZByOURH5kuEP_aTcb0O0L3ewkDUp3ZuNs2QfWQQtDZ_J-8eQD6PQtHhxSioAD_0uYpP2vEsKzpfz5saTU6LW0Oep-uog4HvnFg--r0It-7zhVW9uCY6jO7LXs2LwaIUqXGcdJf8thPEoG91Sfy_qMzol8o4Nd6ktU8IR2IG6CCKmhCcBcwoJlDJR1Q";
			$session_token = $my_niubiz->session_token($access_token["token"], $session_token_data);
			print_r($session_token);	
			
		}else echo $access_token["msg"];
		
		
	}
}
