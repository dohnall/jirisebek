<?php

class TemplateList extends ItemListDomain {

    protected $item = "template_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_template();
    }

}
