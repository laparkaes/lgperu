<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obs_niubiz extends CI_Controller {

	public function __construct(){
		parent::__construct();
		date_default_timezone_set('America/Lima');
		
		//load library or make instance of My_niubiz class
		$this->load->library('my_niubiz');
	}
	
	public function index(){
		///////////////////////////////////////////////////////////////// Step 0: transaction basic data
		$channel = 'web';//'pasarela' is correct value. We will ask to Niubiz about this problem.
		
		$clientIp = '24.212.107.30';
		
		$card = [
			'cardNumber' => '4551708161768059',//test card with installment
			//'cardNumber' => '4474118355632240',//test card without installment
			'expirationMonth' => 3,
			'expirationYear' => 2028,
			'cvv2' => '111'
		];
		
		$cardHolder = [
			'firstName' => 'Pedro',
			'lastName' => 'Galdamez',//this is diference with Mercadopago. New field required.
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
		
		///////////////////////////////////////////////////////////////// Checkout Step 1: session token
		$data = [
			'channel' => $channel,
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
		
		$sessionToken = $this->my_niubiz->sessionToken($data);
		
		echo "Session token result:<br/>"; 
		if ($sessionToken["success"]) print_r($sessionToken); 
		else echo $sessionToken["errorCode"]." - ".$sessionToken["errorMessage"];
		echo "<br/><br/>=====================<br/><br/>";
		
		///////////////////////////////////////////////////////////////// Checkout Step 2: antifraud
		$data = [
			'channel' => $channel,
			'order' => $order,
			'card' => $card,
			'cardHolder' => $cardHolder,
			'clientIp' => $clientIp,
		];
		
		$antifraud = $this->my_niubiz->antifraud($data);
		
		echo "Antifraud result:<br/>"; 
		if ($antifraud["success"]) print_r($antifraud);
		else echo $antifraud["errorCode"]." - ".$antifraud["errorMessage"];
		echo "<br/><br/>=====================<br/><br/>";
		
		///////////////////////////////////////////////////////////////// Checkout Step 3: autorization
		$order['tokenId'] = $antifraud['token']->tokenId;
		$order['installment'] = 12;//set installment here. But with test card installment always will be 4
		
		$data = [
			'channel' => $channel,
			'captureType' => 'manual',
			'countable' => true,
			'order' => $order,
			'card' => $card,
			'cardHolder' => $cardHolder,
		];
		
		$autorization = $this->my_niubiz->authorization($data);
		
		echo "Autorization result:<br/>";
		if ($autorization["success"]) print_R($autorization); 
		else echo $autorization["errorCode"]." - ".$autorization["errorMessage"];
		echo "<br/><br/>=====================<br/><br/>";
		
		///////////////////////////////////////////////////////////////// Checkout Step 4: reverse =====> need to test with LG's Niubiz account
		$data = [
			'channel' => $channel,
			'order' => [
				'purchaseNumber' => $order['purchaseNumber'],
				'transactionDate' => $autorization['order']->transactionDate,
			]
		];
		
		$reverse = $this->my_niubiz->reverse($data);
		
		echo "Reverse result:<br/>"; 
		if ($reverse["success"]) print_R($reverse);
		else echo $reverse["errorCode"]." - ".$reverse["errorMessage"];
		echo "<br/><br/>=====================<br/><br/>";
		
		///////////////////////////////////////////////////////////////// Cash Payment Step 1: creation =====> need to test with LG's Niubiz account
		$data = [
			'channel' => $channel,
			'email' => $cardHolder['email'],
			'amount' => $order['amount'],
			'externalTransactionId' => $order['purchaseNumber']
		];
		
		$cashPayment = $this->my_niubiz->cashPayment($data);
		
		echo "Cash payment result:<br/>"; 
		if ($cashPayment["success"]) print_R($cashPayment);
		else echo $cashPayment["errorCode"]." - ".$cashPayment["errorMessage"];
		echo "<br/><br/>=====================<br/><br/>";
		
		///////////////////////////////////////////////////////////////// Cash Payment Step 2: callback (validation) =====> need to test with LG's Niubiz account
		$cashPayment["cip"] = "301";//make test value 
		
		$data = [
			'status' => 'Paid',
			'operationNumber' => $order['purchaseNumber'],
			'cip' => $cashPayment["cip"],
			'amount' => $order['amount']
		];
		
		$cashPaymentCallback = $this->my_niubiz->cashPaymentCallback($data);
		
		echo "Cash payment callback result:<br/>"; 
		if ($cashPaymentCallback["success"]) print_R($cashPaymentCallback);
		else echo $cashPaymentCallback["errorCode"]." - ".$cashPaymentCallback["errorMessage"];
		echo "<br/><br/>=====================<br/><br/>";
	}
}
