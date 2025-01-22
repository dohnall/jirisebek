<?php

abstract class Item {

    protected $item_id = 0;
    protected $data = array();
    protected $table = "";
    protected $item = "item_id";
    protected $cols = array();

    public function __construct($item_id = 0) {
        $this->item_id = $item_id;
        $this->db = Database::connect();
    }

    public function load() {
        $query = "SELECT *
                  FROM ".$this->table."
                  WHERE ".$this->item." = ".$this->item_id;
        $this->data['item'] = $this->db->select($query, true);
    }

    public function get() {
        return $this->data;
    }

    public function save($data) {
        if($this->item_id) {
            $this->update($data);
        } else {
            $this->insert($data);
        }
        $this->data = $data;
    }

    public function delete() {
        $query = "DELETE FROM ".$this->table."
                  WHERE ".$this->item." = ".$this->item_id;
        $this->db->delete($query);
    }

    protected function insert($data) {
        $this->data['item'] = $data['item'];
        $query = "INSERT INTO ".$this->table."
                (".implode(', ', array_keys($this->cols)).")
                VALUES (";
        foreach($this->cols as $k => $v) {
            $query.= "'".(isset($this->data['item'][$k]) ? $this->data['item'][$k] : $v)."', ";
        }
        $query = rtrim($query, ", ");
        $query.= ")";

        $this->item_id = $this->db->insert($query);
    }

    protected function update($data) {
        if(!$this->data || $this->data['item'] != $data['item']) {
            $this->data['item'] = $data['item'];
            $query = "UPDATE ".$this->table." SET ";
            foreach($this->cols as $k => $v) {
                $query.= $k." = '".$this->data['item'][$k]."', ";
            }
            $query = rtrim($query, ", ");
			$query.= " WHERE ".$this->item." = ".$this->item_id;
            $this->db->update($query);
        }
    }

}
