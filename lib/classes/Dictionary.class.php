<?php

class Dictionary {

    private $item = "code";
    private $data = array();
    private $code = "";

    public function __construct($code) {
        $this->session = Session::getInstance(MODE);
        $this->db = Database::connect();
        $this->code = $code;
    }

    public function load($langs = array(), $domain_id = 0) {
        $domain_id = $domain_id === 0 ? $this->session->domain_id : $domain_id;
        $query = "SELECT *
                  FROM ".Config::db_table_dictionary()."
                  WHERE domain_id = ".$domain_id." AND
                        ".$this->item." = '".$this->code."'";
        if($langs) {
            $query.= " AND lang_id IN (".implode(', ', $langs).")";
        }
        $query.= " ORDER BY lang_id ASC";
        $result = $this->db->select($query);
        foreach($result as $row) {
            $this->data[$row['lang_id']] = array(
                'code' => $row['code'],
                'value' => $row['value'],
            );
        }
    }

    public function get() {
        return $this->data;
    }

    public function save($data) {
        foreach($data as $lang_id => $item) {
            $query = "REPLACE INTO ".Config::db_table_dictionary()."
                      (domain_id, lang_id, ".$this->item.", value)
                      VALUES
                      (".$this->session->domain_id.", ".$lang_id.", '".$item['code']."', '".$item['value']."')";
            $this->db->replace($query);
        }
    }

    public function delete() {
        $query = "DELETE FROM ".Config::db_table_dictionary()."
                  WHERE ".$this->item." = '".$this->code."' AND
                        domain_id = ".$this->session->domain_id;
        return $this->db->delete($query);
    }

}
