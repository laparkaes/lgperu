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
		//$err = curl_error($curl);

		curl_close($curl);
		
		//setting return array
		$result = ["success" => false, "msg" => null, "token" => null];
		
		if ($response === "Unauthorized access") $result["msg"] = $response." (Wrong username or password.)";
		else{
			$result["success"] = true;
			$result["token"] = $response;	
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
		//$err = curl_error($curl);

		curl_close($curl);
		
		/*
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
		*/
	}
	
	
}
?>