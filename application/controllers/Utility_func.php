<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Utility_func extends CI_Controller {

	public function __construct(){
		parent::__construct();
		//if (!$this->session->userdata('logged_in')) redirect("/auth/login");
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');

		set_time_limit(0);
	}
	
	public function trade_marketing_download($filename = "tm_db"){
		//utility_func/trade_marketing_download
		
		$spreadsheet = IOFactory::load("./test_files/".$filename.".xlsx");
		$sheet = $spreadsheet->getActiveSheet();
		
		$img_cols = range('D', 'T');
		
		$max_row = $sheet->getHighestRow();
		for ($i = 2; $i <= $max_row; $i++){
			
			$base = trim($sheet->getCell('B'.$i)->getValue());
			$code = trim($sheet->getCell('C'.$i)->getValue());
			$clave = $base.$code;
			
			//echo $clave." /// ".$base." /// ".$code."<br/>";
			
			$imgs = [];
			foreach($img_cols as $col){
				$aux = trim($sheet->getCell($col.$i)->getValue());
				if ($aux) $imgs[] = $aux;
			}
			
			if ($imgs){
				$dir = "./download";
				if (!file_exists($dir)) mkdir($dir, 0777, true);
				
				$dir .= "/".$base;
				if (!file_exists($dir)) mkdir($dir, 0777, true);
				
				$dir .= "/".$code;
				if (!file_exists($dir)) mkdir($dir, 0777, true);
				
				$dir .= "/";
				
				foreach($imgs as $img_i => $item){
					$aux = explode("/", $item);
					$filepath = $dir.$aux[count($aux) - 1];
					
					if (file_exists($filepath)){
						//echo $filepath." already exists.<br/>";
					}else{
						$fileContent = @file_get_contents($item);
						if ($fileContent !== false){
							file_put_contents($filepath, $fileContent);
							//echo "[Downloaded] ".$filepath."<br/>";
						}else{
							echo "[Error!!!] ".$item."<br/>";
						}				
					}
					
				}
				
				echo $dir." done!<br/>";	
			}
			
		}
		
	}

	public function cloud_pc_shutdown_email(){
		//llamasys/utility_func/cloud_pc_shutdown_email
		
		$to = "lg-lgepr@lge.com";
		//$to = "georgio.park@lge.com";
		
		$subject = "[Comunicado PI] Apague Cloud PC antes de retirar de la oficina !!!";
		$content = $this->load->view('email/cloud_pc_shutdown_email', null, true);
		
		echo $this->my_func->send_email("comunicacionpi@lge.com", $to, $subject, $content);
	}
	
	public function xml_reader(){
		
		echo "hola";
	}
}
