<?php

class DomainList extends ItemList {

    protected $item = "domain_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_domain();
    }

    public function getDomainByUrl($url) {
        $query = "SELECT ".$this->item."
                  FROM ".Config::db_table_domain_alias()."
                  WHERE alias = '".$url."'";
        return $this->db->select($query, true, $this->item);
    }

    public function getDomainById($domain_id) {
        $query = "SELECT *
                  FROM ".$this->table."
                  WHERE ".$this->item." = ".$domain_id;
        return $this->db->select($query, true);
    }

}
