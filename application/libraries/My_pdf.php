<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_pdf{
	
	public function __construct(){
		$this->CI = &get_instance();
	}
	
	public function to_text($path){
		$parser = new \Smalot\PdfParser\Parser();
		$pdf = $parser->parseFile($path);
		
		//print_r($pdf->getPages());
		echo "<br/><br/><br/>";
		echo $pdf->getPages()[0]->getText();
		echo "<br/><br/><br/>";
		echo $pdf->getPages()[1]->getText();
		echo "<br/><br/><br/>";
		
		return $pdf->getText();
	}
}