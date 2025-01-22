<?php

class ItemListDomain extends ItemList {

    protected $session = array();

    public function __construct() {
        parent::__construct();
        $this->session = Session::getInstance(MODE);
    }

    public function load($items = array(), $limit = 0, $from = 0, $orderby = "", $sort = "ASC") {
        $query = "SELECT *
                  FROM ".$this->table."
				  WHERE domain_id=".$this->session->domain_id;

        if($items) {
            $query.= " AND ".$this->item." IN (".implode(', ', $items).")";
        }
        
        if($orderby) {
			$query.= " ORDER BY ".$orderby." ".$sort;
		}

        if($limit) {
			$query.= " LIMIT ".$from." ".$limit;
		}
        
        $data = $this->db->select($query);
        foreach($data as $row) {
            $this->data[$row[$this->item]] = $row;
        }
    }

}
