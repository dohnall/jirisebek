<?php

class ColumnList extends ItemList {

    protected $item = "column_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_column();
    }

	public static function getColumnTypeByCode($code) {
		$db = Database::connect();
		$query = "SELECT type
				  FROM ".Config::db_table_column()."
				  WHERE code = '".$code."'";
		return $db->select($query, true, "type");
	}

}
