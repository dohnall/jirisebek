<?php

class ModuleList extends ItemList {

    protected $item = "module_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_module();
    }

}
