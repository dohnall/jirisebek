<?php

class ItemDomain extends Item {

    protected $session = array();

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->session = Session::getInstance(MODE);
    }

    public function load() {
        $query = "SELECT *
                  FROM ".$this->table."
                  WHERE domain_id = ".$this->session->domain_id." AND
				  		".$this->item." = ".$this->item_id;
        $this->data['item'] = $this->db->select($query, true);
    }

    protected function insert($data) {
        $this->data['item'] = $data['item'];
        $query = "INSERT INTO ".$this->table."
                  (domain_id, ".implode(', ', array_keys($this->cols)).")
                  VALUES (".$this->session->domain_id.", ";
        foreach($this->cols as $k => $v) {
            $query.= "'".(isset($this->data['item'][$k]) ? $this->data['item'][$k] : $v)."', ";
        }
        $query = rtrim($query, ", ");
        $query.= ")";
        $this->item_id = $this->db->insert($query);
    }

}
