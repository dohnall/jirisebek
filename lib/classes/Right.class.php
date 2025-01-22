<?php

class Right extends Item {

    protected $item = "right_id";
    protected $cols = array(
        'name' => "",
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_right();
    }

    public function delete() {
        parent::delete();
        $this->db->delete("DELETE FROM ".Config::db_table_group_right()." WHERE ".$this->item." = ".$this->item_id);
    }

}
