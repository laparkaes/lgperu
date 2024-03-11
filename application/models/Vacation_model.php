<?php

class Vacation_model extends CI_Model{
	
	public function __construct(){
		parent::__construct();
		$this->tablename = "vacation";
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
	
	/* status */
	function unique_status($field, $status){
		$this->db->where($field, $status);
		$query = $this->db->get($this->tablename."_status", 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function all_status(){
		$this->db->order_by("status", "asc");
		$query = $this->db->get($this->tablename."_status");
		$result = $query->result();
		return $result;
	}
	
	/* type */
	function unique_type($field, $val){
		$this->db->where($field, $val);
		$query = $this->db->get($this->tablename."_type", 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function all_type(){
		$this->db->order_by("type", "asc");
		$query = $this->db->get($this->tablename."_type");
		$result = $query->result();
		return $result;
	}
}
?>
