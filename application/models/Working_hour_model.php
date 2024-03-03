<?php

class Working_hour_model extends CI_Model{
	
	public function __construct(){
		parent::__construct();
		$this->tablename = "working_hour";
	}

	function unique($field, $value, $valid = true){
		$this->db->where($field, $value);
		if ($valid) $this->db->where("valid", true);
		$query = $this->db->get($this->tablename, 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function get_by_employee($employee_id, $date = null){
		$this->db->where("employee_id", $employee_id);
		if ($date){
			$this->db->where("date_from <=", $date);
			$this->db->where("date_to >=", $date);	
		}
		$this->db->where("valid", true);
		$this->db->order_by("register", "desc");
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
	
	//working_hour_option
	function unique_option($field, $value, $valid = true){
		$this->db->where($field, $value);
		if ($valid) $this->db->where("valid", true);
		$query = $this->db->get($this->tablename."_option", 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function filter_option($entrance_time, $exit_time, $valid = true){
		$this->db->where("entrance_time", $entrance_time);
		$this->db->where("exit_time", $exit_time);
		if ($valid) $this->db->where("valid", true);
		$query = $this->db->get($this->tablename."_option", 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
}
?>
