<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
Test datas:
https://desarrolladores.niubiz.com.pe/docs/formulario-desacoplado
*/

class My_niubiz{
	private $is_production = false;//change to true if this is live environment
	
	//Access information will be provided by Niubiz.
	private $username = "integraciones@niubiz.com.pe";
	private $password = "_7z3@8fF";
	private $merchantId = "456879852";
	
	public function __construct(){}
	
	public function accessToken(){
		//https://desarrolladores.niubiz.com.pe/reference/get_v1-security
		
		$url_test = "https://apisandbox.vnforappstest.com/api.security/v1/security";
		$url_production = "https://apiprod.vnforapps.com/api.security/v1/security";
		
        $curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->is_production ? $url_production : $url_test,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"accept: text/plain",
				"authorization: Basic ".base64_encode($this->username.":".$this->password)
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		
		if ($err) $result["msg"] = "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			//setting return array
			$result = ["success" => false, "msg" => null, "accessToken" => null];
			
			if ($response === "Unauthorized access") $result["msg"] = $response." (Wrong username or password.)";
			else{
				$result["success"] = true;
				$result["accessToken"] = $response;	
			}
		}
		
		return $result;
    }
	
	public function sessionToken($data){
		$accessToken = $this->accessToken();
		if (!$accessToken["success"]) return $accessToken;
		
		//https://desarrolladores.niubiz.com.pe/reference/post_ecommerce-token-session-merchantid
		
		$url_test = "https://apisandbox.vnforappstest.com/api.ecommerce/v2/ecommerce/token/session/".$this->merchantId;
		$url_production = "https://apiprod.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/".$this->merchantId;

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->is_production ? $url_production : $url_test,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"Authorization: ".$accessToken['accessToken'],
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = [];
		
		if ($err) echo "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			/* success case
			{
				"sessionKey":"aa3e2b595ec2442b253c18e98c095d507591b0b140fc19f77045b093223411a4",
				"expirationTime":1721160740592
			}
			*/
			
			//save original will be in last position of result. Plain text response is here.
			$original = $response;
			
			//convert response to object
			$response = json_decode($response);
			
			//save all objecto key in result array
			foreach($response as $key => $val) $result[$key] = $val;
			
			//validate is this success case
			if (property_exists($response, "sessionKey")) $result["success"] = true;
			else{
				$result["success"] = false;
				$result["original"] = $original;
			}
		}
		
		return $result;
	}
	
	public function antifraud($data){
		$accessToken = $this->accessToken();
		if (!$accessToken["success"]) return $accessToken;
		
		//https://desarrolladores.niubiz.com.pe/reference/post_antifraud-product-merchantid

		$url_test = "https://apisandbox.vnforappstest.com/api.antifraud/v1/antifraud/ecommerce/".$this->merchantId;
		$url_production = "https://apiprod.vnforapps.com/api.antifraud/v1/antifraud/ecommerce/".$this->merchantId;

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->is_production ? $url_production : $url_test,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"Authorization: ".$accessToken['accessToken'],
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = [];
		
		if ($err) echo "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			/* success case
			{
				"header":{"ecoreTransactionUUID":"e9e02c39-4e89-494b-a899-7c0ad7ab2406","ecoreTransactionDate":1721451805433,"millis":533},
				"order":{"purchaseNumber":"1234567890","amount":1.0,"currency":"PEN"},
				"token":{"tokenId":"B6788265FB1F4717B88265FB1F871762","expiration":1721452705362,"redirectToVbV":false,"mpi":false,"dataMap":{}},
				"dataMap":{}
			}
			*/
			
			//save original will be in last position of result. Plain text response is here.
			$original = $response;
			
			//convert response to object
			$response = json_decode($response);
			
			//save all objecto key in result array
			foreach($response as $key => $val) $result[$key] = $val;
			
			//validate is this success case
			if (property_exists($response, "token")) $result["success"] = true;
			else{
				$result["success"] = false;
				$result["original"] = $original;
			}
		}
		
		return $result;
	}
	
	public function authorization($data){
		$accessToken = $this->accessToken();
		if (!$accessToken["success"]) return $accessToken;
		
		//https://desarrolladores.niubiz.com.pe/reference/post_authorization-ecommerce-merchantid

		$url_test = "https://apisandbox.vnforappstest.com/api.authorization/v3/authorization/ecommerce/".$this->merchantId;
		$url_production = "https://apiprod.vnforapps.com/api.authorization/v3/authorization/ecommerce/".$this->merchantId;

		$curl = curl_init();
		
		curl_setopt_array($curl, [
			CURLOPT_URL => $this->is_production ? $url_production : $url_test,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"Authorization: ".$accessToken['accessToken'],
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = [];
		
		if ($err) echo "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			/* success case
			{
				"header":{"ecoreTransactionUUID":"1a7a020d-b0a3-4da9-a799-ebf8fde3d20d","ecoreTransactionDate":1721494409221,"millis":1143},
				"fulfillment":{"channel":"web","merchantId":"456879852","terminalId":"00000001","captureType":"manual","countable":true,"fastPayment":false,"signature":"1a7a020d-b0a3-4da9-a799-ebf8fde3d20d"},
				"order":{"tokenId":"313FB3866E384017BFB3866E38F017BA","purchaseNumber":"01234567890","amount":1234.23,"installment":0,"currency":"PEN","authorizedAmount":1234.23,"authorizationCode":"091800","actionCode":"000","traceNumber":"496354","transactionDate":"240720115328","transactionId":"993211570048581"},
				"dataMap":{"TERMINAL":"00000001","BRAND_ACTION_CODE":"00","BRAND_HOST_DATE_TIME":"201222141839","TRACE_NUMBER":"496354","CARD_TYPE":"D","ECI_DESCRIPTION":"Transaccion no autenticada pero enviada en canal seguro","SIGNATURE":"1a7a020d-b0a3-4da9-a799-ebf8fde3d20d","CARD":"447411******2240","MERCHANT":"109705108","STATUS":"Authorized","ACTION_DESCRIPTION":"Aprobado y completado con exito","ID_UNICO":"993211570048581","AMOUNT":"1234.23","AUTHORIZATION_CODE":"091800","CURRENCY":"0604","TRANSACTION_DATE":"240720115328","ACTION_CODE":"000","CVV2_VALIDATION_RESULT":"M","ECI":"07","ID_RESOLUTOR":"420201222142237","BRAND":"visa","ADQUIRENTE":"570002","BRAND_NAME":"VI","PROCESS_CODE":"000000","TRANSACTION_ID":"993211570048581"}
			}				
			*/
			
			//save original will be in last position of result. Plain text response is here.
			$original = $response;
			
			//convert response to object
			$response = json_decode($response);
			
			//save all objecto key in result array
			foreach($response as $key => $val) $result[$key] = $val;
			
			//validate is this success case
			if (property_exists($response, "fulfillment")) $result["success"] = true;
			else{
				$result["success"] = false;
				$result["original"] = $original;
			}
		}
		
		return $result;
	}

	public function reverse($data){
		$accessToken = $this->accessToken();
		if (!$accessToken["success"]) return $accessToken;
		
		//https://desarrolladores.niubiz.com.pe/reference/post_reverse-product-merchantid

		$url_test = "https://apisandbox.vnforappstest.com/api.authorization/v3/reverse/ecommerce/".$this->merchantId;
		$url_production = "https://apiprod.vnforapps.com/api.authorization/v3/reverse/ecommerce/".$this->merchantId;
		

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->is_production ? $url_production : $url_test,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"Authorization: ".$accessToken['accessToken'],
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = [];

		if ($err) echo "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			/* success case
			{
				"header":{"ecoreTransactionUUID": "5e0bfeea-6afb-406d-82b0-2fc27cf82c42","ecoreTransactionDate": 1659653763657,"millis": 701},
				"fulfillment":{"channel": "web","merchantId": "522591303","terminalId": "1","captureType": "manual","countable": true,"fastPayment": false,"signature": "ea9e4880-fb70-40a5-a979-4a87a0e9a645"},
				"order":{"authorizationCode": "","actionCode": "400","traceNumber": "48740","transactionDate": "220804175603","transactionId": "0994222160080198","originalTraceNumber": "267624","originalDateTime": "220804175353"},
				"dataMap":{"CURRENCY": "0604","ORIGINAL_DATETIME": "220804175353","TERMINAL": "00000001","TRANSACTION_DATE": "220804175603","ACTION_CODE": "400","TRACE_NUMBER": "48740","ORIGINAL_TRACE": "267624","CARD": "491337******9111","MERCHANT": "522591303","STATUS": "Voided","ADQUIRENTE": "570009","AMOUNT": "21.00","PROCESS_CODE": "000000","TRANSACTION_ID": "0994222160080198"}
			}
			*/
			
			//save original will be in last position of result. Plain text response is here.
			$original = $response;
			
			//convert response to object
			$response = json_decode($response);
			
			//save all objecto key in result array
			foreach($response as $key => $val) $result[$key] = $val;
			
			//validate is this success case
			if (property_exists($response, "fulfillment")) $result["success"] = true;
			else{
				$result["success"] = false;
				$result["original"] = $original;
			}
		}
		
		return $result;
	}

	public function cashPayment($data){
		$accessToken = $this->accessToken();
		if (!$accessToken["success"]) return $accessToken;
		
		//https://desarrolladores.niubiz.com.pe/reference/post_create-merchantid

		$url_test = "https://apisandbox.vnforappstest.com/api.pagoefectivo/v1/create/".$this->merchantId;
		$url_production = "https://apiprod.vnforapps.com/api.pagoefectivo/v1/create/".$this->merchantId;
		
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->is_production ? $url_production : $url_test,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"Authorization: ".$accessToken['accessToken'],
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = [];
		
		if ($err) echo "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			/* success case
			{
				 "cip": 2556869,
				 "cipUrl": "https://pre1a.payment.pagoefectivo.pe/50493E3B-0872-4A98-A7CF-552CA3605C88.html",
				 "expiryDate": "2020-07-24 23:59:59"
			}				
			*/
			
			//save original will be in last position of result. Plain text response is here.
			$original = $response;
			
			//convert response to object
			$response = json_decode($response);
			
			//save all objecto key in result array
			foreach($response as $key => $val) $result[$key] = $val;
			
			//validate is this success case
			if (property_exists($response, "cip")) $result["success"] = true;
			else{
				$result["success"] = false;
				$result["original"] = $original;
			}
		}
		
		return $result;
	}
	
	public function cashPaymentCallback($data){
		//https://desarrolladores.niubiz.com.pe/reference/post_callback
		
		//no accessToken required. Same url for Test and production.
		
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://ambiente.comercio.com/api.pagoefectivocallback/v1/callback",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = [];
		
		if ($err) echo "cURL Error #:" . $err;//this is curl error. Need to check url or any autorization data.
		else{
			/* success case
			Status Code 200 OK
			Content-Type: application/json
			*/
			
			//save original will be in last position of result. Plain text response is here.
			$original = $response;
			
			//convert response to object
			$response = json_decode($response);
			
			//save all objecto key in result array
			foreach($response as $key => $val) $result[$key] = $val;
			
			//validate is this success case
			if (property_exists($response, "cip")) $result["success"] = true;
			else{
				$result["success"] = false;
				$result["original"] = $original;
			}
		}
		
		return $result;
	}
}
?>