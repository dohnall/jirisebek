<?php

class Component extends ItemDomain {

    protected $item = "component_id";
    protected $cols = array(
        'code' => "",
        'receiver' => "",
        'template' => "",
        'whr' => "",
		'cnt' => 0,
		'perpage' => 0,
        'orderby' => "",
        'sort' => "asc",
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_component();
    }

}
