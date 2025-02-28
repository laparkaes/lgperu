<?php

class General_espr_model extends CI_Model{

	public function __construct() {
		parent::__construct();
		$this->espr = $this->load->database('espr', TRUE);
	}

	function filter($tablename, $valid = true, $w = null, $l = null, $w_in = null, $orders = [], $limit = "", $offset = ""){
		if ($valid) $this->db->where("valid", true);
		if ($w){ $this->db->group_start(); $this->db->where($w); $this->db->group_end(); }
		if ($l){
			$this->db->group_start();
			foreach($l as $item){
				$this->db->group_start();
				$values = $item["values"];
				foreach($values as $v) $this->db->like($item["field"], $v);
				$this->db->group_end();
			}
			$this->db->group_end();
		}
		if ($w_in){
			$this->db->group_start();
			foreach($w_in as $item) $this->db->where_in($item["field"], $item["values"]);
			$this->db->group_end();
		}
		if ($orders) foreach($orders as $o) $this->db->order_by($o[0], $o[1]);
		$query = $this->db->get($tablename, $limit, $offset);
		$result = $query->result();
		return $result;
	}
	
	function filter_select($tablename, $valid = true, $s = null, $w = null, $l = null, $w_in = null, $orders = [], $limit = "", $offset = "", $group_by = null){
		if ($s) $this->db->select($s);
		if ($valid) $this->db->where("valid", true);
		if ($w){ $this->db->group_start(); $this->db->where($w); $this->db->group_end(); }
		if ($l){
			$this->db->group_start();
			foreach($l as $item){
				$this->db->group_start();
				$values = $item["values"];
				foreach($values as $v) $this->db->like($item["field"], $v);
				$this->db->group_end();
			}
			$this->db->group_end();
		}
		if ($w_in){
			$this->db->group_start();
			foreach($w_in as $item) $this->db->where_in($item["field"], $item["values"]);
			$this->db->group_end();
		}
		if ($orders) foreach($orders as $o) $this->db->order_by($o[0], $o[1]);
		if ($group_by) $this->db->group_by($group_by);
		$query = $this->db->get($tablename, $limit, $offset);
		$result = $query->result();
		return $result;
	}

	function unique($tablename, $field, $value, $check_valid = true){
		$this->db->where($field, $value);
		if ($check_valid) $this->db->where("valid", true);
		$query = $this->db->get($tablename, 1, 0);
		$result = $query->result();
		if ($result) return $result[0]; else return null;
	}
	
	function all($tablename, $orders = [], $limit = "", $offset = "", $check_valid = true){
		if ($check_valid) $this->db->where("valid", true);
		if ($orders) foreach($orders as $o) $this->db->order_by($o[0], $o[1]);
		$query = $this->db->get($tablename, $limit, $offset);
		$result = $query->result();
		return $result;
	}
	
	function insert($tablename, $data){
		$this->db->insert($tablename, $data);
		return $this->db->insert_id();
	}
	
	function insert_m($tablename, $data){//multi insert
		return $this->db->insert_batch($tablename, $data);
	}
	
	function update($tablename, $filter, $data){
		$this->db->where($filter);
		return $this->db->update($tablename, $data);
	}
	
	function update_multi($tablename, $data, $field){ 
		return $this->db->update_batch($tablename, $data, $field);
	}
	
	function delete($tablename, $filter = null){
		if ($filter) $this->db->where($filter);
		return $this->db->delete($tablename);
	}
	
	function delete_in($tablename, $field = null, $values = null){
		if ($field and $values){
			$this->db->where_in($field, $values);
			return $this->db->delete($tablename);
		}else return null;
	}
	
	function truncate($tablename){
		return $this->db->truncate($tablename);
	}
	
	function only($tablename, $field, $where = null){
		$this->db->select($field);
		if ($where) $this->db->where($where);
		$this->db->group_by($field);
		$this->db->order_by($field, "asc");
		$query = $this->db->get($tablename);
		$result = $query->result();
		return $result;
	}
	
	function only_multi($tablename, $fields, $where = null, $groups = null){
		$this->db->select($fields);
		if ($where) $this->db->where($where);
		$groups = $groups ? $groups : $fields;
		foreach($groups as $g) $this->db->group_by($g);
		$this->db->order_by($fields[0], "asc");
		$query = $this->db->get($tablename);
		$result = $query->result();
		return $result;
	}
	
	function sum($tablename, $col, $w = null, $w_in = null){
		$this->db->select_sum($col);
		if ($w) $this->db->where($w);
		if ($w_in){
			$this->db->group_start();
			foreach($w_in as $item) $this->db->where_in($item["field"], $item["values"]);
			$this->db->group_end();
		}
		$query = $this->db->get($tablename);
		$result = $query->result();
		return $result[0];
	}
		
	function avg($tablename, $col, $w = null, $w_in = null){
		$this->db->select_avg($col);
		if ($w) $this->db->where($w);
		if ($w_in){
			$this->db->group_start();
			foreach($w_in as $item) $this->db->where_in($item["field"], $item["values"]);
			$this->db->group_end();
		}
		$query = $this->db->get($tablename);
		$result = $query->result();
		return $result[0];
	}
	
	function get_group($tablename, $where, $groups){
		$this->db->select(implode(",", $groups));
		if ($where) $this->db->where($where);
		$this->db->group_by($groups);
		$this->db->order_by($groups[0], "asc");
		$query = $this->db->get($tablename);
		return $query->result();
	}

	function structure($tablename){
		$res = new stdClass();
		$aux = $this->db->list_fields($tablename);
		foreach($aux as $field) $res->$field = null;
		return $res;
	}

	function run_query($q){
		return $this->db->query($q);
	}

	function get_insert_query($tablename, $data){//related to insert_m
		// 컬럼과 데이터 분리
		$columns = array_keys($data[0]);
		$values  = array_map(function ($row) {
			return '(' . implode(',', array_map([$this->db, 'escape'], $row)) . ')';
		}, $data);

		// 쿼리 문자열 생성
		$sql = sprintf(
			"INSERT INTO `%s` (%s) VALUES %s;",
			$tablename,
			implode(',', array_map(function ($col) {
				return "`" . $col . "`";
			}, $columns)),
			implode(',', $values)
		);

		return $sql;
	}
	
	
	
	function get_delete_query($tablename, $field = null, $values = null){//related to delete_in
		if ($field and $values){
			$this->db->where_in($field, $values);
			return $this->db->get_compiled_delete($tablename);
		}else return "";
	}
}
?>
