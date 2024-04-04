<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_pdf{
	
	public function __construct(){
		$this->CI = &get_instance();
	}
	
	public function to_text($path){
		$parser = new \Smalot\PdfParser\Parser();
		$pdf = $parser->parseFile($path);
		
		$rows = [];
		
		$pages = $pdf->getPages();
		foreach($pages as $i => $p){
			$text = $pages[$i]->getText();
			
			$lines = explode("\n", $text);
			$lines = array_values(array_filter($lines));
			foreach($lines as $line) $line = trim($line);
			
			$rows = array_merge($rows, $lines);
		}
		
		return $rows;
	}
}