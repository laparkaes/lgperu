<?php

class Som_model extends CI_Model{

	public function __construct(){
		parent::__construct();
		$this->db_som = $this->load->database('db_som', TRUE);
	}

	function filter($tablename, $valid = true, $w = null, $l = null, $w_in = null, $orders = [], $limit = "", $offset = ""){
		if ($valid) $this->db_som->where("valid", true);
		if ($w){ $this->db_som->group_start(); $this->db_som->where($w); $this->db_som->group_end(); }
		if ($l){
			$this->db_som->group_start();
			foreach($l as $item){
				$this->db_som->group_start();
				$values = $item["values"];
				foreach($values as $v) $this->db_som->like($item["field"], $v);
				$this->db_som->group_end();
			}
			$this->db_som->group_end();
		}
		if ($w_in){
			$this->db_som->group_start();
			foreach($w_in as $item) $this->db_som->where_in($item["field"], $item["values"]);
			$this->db_som->group_end();
		}
		if ($orders) foreach($orders as $o) $this->db_som->order_by($o[0], $o[1]);
		$query = $this->db_som->get($tablename, $limit, $offset);
		$result = $query->result();
		return $result;
	}

	function unique($tablename, $field, $value, $check_valid = true){
		$this->db_som->where($field, $value);
		if ($check_valid) $this->db_som->where("valid", true);
		$query = $this->db_som->get($tablename, 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function all($tablename, $orders = [], $limit = "", $offset = "", $check_valid = true){
		if ($check_valid) $this->db_som->where("valid", true);
		if ($orders) foreach($orders as $o) $this->db_som->order_by($o[0], $o[1]);
		$query = $this->db_som->get($tablename, $limit, $offset);
		$result = $query->result();
		return $result;
	}
	
	function insert($tablename, $data){
		$this->db_som->insert($tablename, $data);
		return $this->db_som->insert_id();
	}
	
	function insert_m($tablename, $data){//multi insert
		return $this->db_som->insert_batch($tablename, $data);
	}
	
	function update($tablename, $filter, $data){
		$this->db_som->where($filter);
		return $this->db_som->update($tablename, $data);
	}
	
	function update_multi($tablename, $data, $field){ 
		return $this->db_som->update_batch($tablename, $data, $field);
	}
	
	function delete($tablename, $filter){
		$this->db_som->where($filter);
		return $this->db_som->delete($tablename);
	}
	
	function truncate($tablename){
		return $this->db_som->truncate($tablename);
	}
	
	function only($tablename, $field, $where = null){
		$this->db_som->select($field);
		if ($where) $this->db_som->where($where);
		$this->db_som->group_by($field);
		$this->db_som->order_by($field, "asc");
		$query = $this->db_som->get($tablename);
		$result = $query->result();
		return $result;
	}
	
	function sum($tablename, $col, $filter = null){
		$this->db_som->select_sum($col);
		if ($filter) $this->db_som->where($filter);
		$query = $this->db_som->get($tablename);
		$result = $query->result();
		return $result[0];
	}

	function structure($tablename){
		$res = new stdClass();
		$aux = $this->db_som->list_fields($tablename);
		foreach($aux as $field) $res->$field = null;
		return $res;
	}
}
?>
