<?php

class Internal_sale_model extends CI_Model{
	
	public function __construct(){
		parent::__construct();
		
	}
	
	public function insert_product($data)
    {
        $this->db->insert('internal_sale_products', $data);
        return $this->db->insert_id();
    }

    public function insert_images($images)
    {
        $this->db->insert_batch('internal_sale_product_images', $images);
    }
	
}
?>
