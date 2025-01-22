<?php

class RightList extends ItemList {

    protected $item = "right_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_right();
    }

}
