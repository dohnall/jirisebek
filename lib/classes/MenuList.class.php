<?php

class MenuList extends ItemListDomain {

    protected $item = "menu_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_menu();
    }

    public function load($items = array(), $limit = 0, $from = 0, $orderby = "", $sort = "ASC") {
		if(MODE == 'CMS') {
	        $query = "SELECT *
	                  FROM ".$this->table."
					  WHERE domain_id=".$this->session->domain_id;
		} else {
	        $query = "SELECT *
	                  FROM ".$this->table."
					  WHERE domain_id=".$this->session->domain_id." AND
					  		status='1'";
		}

        if($items) {
            $query .= " AND ".$this->item." IN (".implode(', ', $items).")";
        }
        $data = $this->db->select($query);
        foreach($data as $row) {
            $this->data[$row[$this->item]] = $row;
        }
    }

}
