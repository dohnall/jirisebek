<?php

class ItemList {

    protected $data = array();
    protected $table = "";
    protected $item = "";

    public function __construct() {
        $this->db = Database::connect();
    }

    public function load($items = array(), $limit = 0, $from = 0, $orderby = "", $sort = "ASC") {
        $query = "SELECT *
                  FROM ".$this->table;
        if($items) {
            $query .= " WHERE ".$this->item." IN (".implode(', ', $items).")";
        }
        if($orderby) {
			$query .= " ORDER BY ".$orderby." ".$sort;
		}
        if($limit) {
			$query .= " LIMIT ".$from.", ".$limit;
		}
        
        $data = $this->db->select($query);
        foreach($data as $row) {
            $this->data[$row[$this->item]] = $row;
        }
    }

    public function get() {
        return $this->data;
    }

    public function count() {
        return count($this->data);
    }

}
