<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_pdf{
	
	public function __construct(){
		$this->CI = &get_instance();
	}
	
	public function to_text($path){
		$parser = new \Smalot\PdfParser\Parser();
		$pdf = $parser->parseFile($path);
		
		$result = [];
		
		$pages = $pdf->getPages();
		foreach($pages as $i => $p) $result[] = ["page" => ($i + 1), "text" => $pages[$i]->getText()];
		
		return $result;
	}
}