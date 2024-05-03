<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_pdf{
	
	public function __construct(){
		$this->CI = &get_instance();
	}
	
	public function to_text($path){
		//ini_set("memory_limit","1024M");
		//ini_set('display_errors', 0);
		
		$parser = new \Smalot\PdfParser\Parser();
		$pdf = $parser->parseFile($path);
		
		$rows = [];
		
		$pages = $pdf->getPages();
		foreach($pages as $i => $p){
			$text = @$p->getText();//$pages[$i]->getText();
			$lines = explode("\n", $text);
			$lines = array_values(array_filter($lines));
			foreach($lines as $line) $rows[] = trim($line);
		}
		return $rows;
	}
}