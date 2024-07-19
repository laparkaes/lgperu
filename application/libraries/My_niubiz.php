<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
Steps: https://desarrolladores.niubiz.com.pe/docs/formulario-desacoplado
*/

class My_niubiz{
	private $is_production = false;
	private $username = "integraciones@niubiz.com.pe";
	private $password = "_7z3@8fF";
	private $merchant_id = "456879852";
	
	public function __construct(){
		
	}
	
	public function get_merchant_id(){
		return $this->$merchant_id;
	}
	
	public function access_token(){
		//Manual: https://desarrolladores.niubiz.com.pe/reference/get_v1-security
		
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
		
		if ($err) $result["msg"] = "cURL Error #:" . $err;
		else{
			//setting return array
			$result = ["success" => false, "msg" => null, "token" => null];
			
			if ($response === "Unauthorized access") $result["msg"] = $response." (Wrong username or password.)";
			else{
				$result["success"] = true;
				$result["token"] = $response;	
			}
		}
		
		return $result;
    }
	
	public function session_token($access_token, $data){
		//Manual: https://desarrolladores.niubiz.com.pe/reference/post_ecommerce-token-session-merchantid
		
		$url_test = "https://apisandbox.vnforappstest.com/api.ecommerce/v2/ecommerce/token/session/".$this->merchant_id;
		$url_production = "https://apiprod.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/".$this->merchant_id;
		
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
				"Authorization: ".$access_token,
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		
		if ($err) $result["msg"] = "cURL Error #:" . $err;
		else{
			//setting return array
			$result = ["success" => false, "sessionKey" => null, "expirationTime" => null, "errorCode" => null, "errorMessage" => null, "data" => null];
			
			if ($response === "Unauthorized access") $result["errorMessage"] = $response." (Wrong session token.)";
			elseif ($response === "Not Acceptable"){
				//return in plain text. May be this is fraude case
				$result["errorCode"] = 406;
				$result["errorMessage"] = $response;
			}else{
				$response = json_decode($response);
				if (property_exists($response, "errorCode")){//error ocurred
					//{"errorCode":400,"errorMessage":"Token has been used before","data":{}} <= one of cases
					$result["errorCode"] = $response->errorCode;
					$result["errorMessage"] = $response->errorMessage;
					$result["data"] = $response->data;
				}else{
					//{"sessionKey":"aa3e2b595ec2442b253c18e98c095d507591b0b140fc19f77045b093223411a4","expirationTime":1721160740592}
					$result["success"] = true;
					$result["sessionKey"] = $response->sessionKey;
					$result["expirationTime"] = $response->expirationTime;
				}
			}
		}
		
		return $result;
	}
	
	public function get_session_key($session_token_data = null){
		//define defualt result array
		$result = ["success" => false, "msg" => null];
		
		if ($session_token_data){
			
			//generaete access token
			$access_token = $this->access_token(); //echo "Access token result:<br/>"; print_r($access_token); echo "<br/><br/>";
			if ($access_token["success"]){
				
				//generate session token
				$session_token = $this->session_token($access_token["token"], $session_token_data); //echo "Session token result:<br/>"; print_r($session_token); echo "<br/><br/>";
				if ($session_token["success"]){
					$result["success"] = true;
					$result["msg"] = "Session key generated.";
					$result["sessionKey"] = $session_token["sessionKey"];
					$result["expirationTime"] = $session_token["expirationTime"];
				}else $result["msg"] = $session_token["errorMessage"];
			}else $result["msg"] = $access_token["msg"];
		}else $result["msg"] = "Session token data is null.";
		
		return $result;
	}
}
?>