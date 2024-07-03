<?php

class Employee_model extends CI_Model{
	
	public function __construct(){
		parent::__construct();
		$this->tablename = "employee";
	}

	function unique($field, $value, $valid = true){
		$this->db->where($field, $value);
		if ($valid) $this->db->where("valid", true);
		$query = $this->db->get($this->tablename, 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function insert($data){
		$this->db->insert($this->tablename, $data);
		return $this->db->insert_id();
	}
	
	function all($orders = [], $limit = "", $offset = "", $valid = true){
		if ($valid) $this->db->where("valid", true);
		if ($orders) foreach($orders as $o) $this->db->order_by($o[0], $o[1]);
		$query = $this->db->get($this->tablename, $limit, $offset);
		$result = $query->result();
		return $result;
	}
	
	function qty($valid = true){
		if ($valid) $this->db->where("valid", true);
		return $this->db->count_all_results($this->tablename);
	}
	
	function update($filter, $data){
		$this->db->where($filter);
		return $this->db->update($this->tablename, $data);
	}
	
	function delete($filter){
		$this->db->where($filter);
		return $this->db->delete($this->tablename);
	}
	
	function structure(){
		$res = new stdClass();
		$aux = $this->db->list_fields($this->tablename);
		foreach($aux as $field) $res->$field = null;
		return $res;
	}
}
?>
