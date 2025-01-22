<?php

class Module extends Item {

    protected $item = "module_id";
    protected $cols = array(
        'code' => "",
        'optional' => 0,
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_module();
    }

    public function delete() {
        parent::delete();
        $this->db->delete("DELETE FROM ".Config::db_table_user_module()." WHERE ".$this->item." = ".$this->item_id);
    }

}
