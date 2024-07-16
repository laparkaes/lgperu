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
		
		
		$session_token_data = [
			'channel' => 'web',
			'antifraud' => [
				'merchantDefineData' => [//you can add any business values here. Ex] i am adding orderNo and magentoId
					'orderNo' => '1234567890',
					'magentoId' => '0987654321',
					'anyKey' => 'anyValue',
				],
				'clientIp' => '24.252.107.29'//Client IP
			],
			'dataMap' => [//Client address information + phone number
				'cardholderCity' => 'Lima',
				'cardholderCountry' => 'PE',
				'cardholderAddress' => 'Av Jose Pardo 831',
				'cardholderPostalCode' => '12345',
				'cardholderState' => 'LIM',
				'cardholderPhoneNumber' => '987654321'
			],
			'amount' => 12.35//Transaction amount
		];
		
		$result = $this->my_niubiz->get_session_key($session_token_data);
		
	}
}
