<?php

class LangList extends ItemList {

    protected $item = "lang_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_lang();
    }

    public function getLangById($lang_id) {
        $query = "SELECT *
                  FROM ".$this->table."
                  WHERE ".$this->item." = ".$lang_id;
        return $this->db->select($query, true);
    }

    public function getLangByCode($code) {
        $query = "SELECT ".$this->item."
                  FROM ".$this->table."
                  WHERE code = '".$code."'";
        return $this->db->select($query, true, $this->item);
    }

}
