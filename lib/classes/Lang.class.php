<?php

class Lang extends Item {

    protected $item = "lang_id";
    protected $cols = array(
        'code' => "",
        'name' => "",
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_lang();
    }

    public function delete() {
        parent::delete();
        $this->db->delete("DELETE FROM ".Config::db_table_user_lang()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_domain_lang()." WHERE ".$this->item." = ".$this->item_id);

		$query = "SELECT cr.codelist_record_id
				  FROM ".Config::db_table_codelist_record()." cr
				  LEFT JOIN ".Config::db_table_codelist_text()." ct ON (cr.codelist_text_id = ct.codelist_text_id)
				  WHERE ct.".$this->item." = ".$this->item_id;
		$result = $this->db->select($query);
		foreach($result as $row) {
			$this->db->delete("DELETE FROM ".Config::db_table_codelist_record_file()." WHERE codelist_record_id = ".$row['codelist_record_id']);
			$this->db->delete("DELETE FROM ".Config::db_table_codelist_record_value()." WHERE codelist_record_id = ".$row['codelist_record_id']);
			$this->db->delete("DELETE FROM ".Config::db_table_codelist_record()." WHERE codelist_record_id = ".$row['codelist_record_id']);
		}
        $this->db->delete("DELETE FROM ".Config::db_table_codelist_text()." WHERE ".$this->item." = ".$this->item_id);
    }

}
