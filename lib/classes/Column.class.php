<?php

class Column extends Item {

    protected $item = "column_id";
    protected $cols = array(
        'name' => "",
        'code' => "",
        'type' => "",
        'required' => 0,
        'readonly' => 0,
        'hint' => "",
    );
    private $table_user = "";

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_column();
        $this->table_param = Config::db_table_column_param();
    }

	public function load() {
		parent::load();
	    $query = "SELECT *
	              FROM ".$this->table_param."
	              WHERE ".$this->item." = ".$this->item_id;
	    $res = $this->db->select($query);

	    $col_params = array();
	    foreach($res as $row) {
			if(!is_null($row['int_val'])) {
				$col_params[$row['code']] = $row['int_val'];
			}
			if(!is_null($row['varchar_val'])) {
				$col_params[$row['code']] = $row['varchar_val'];
			}
			if(!is_null($row['text_val'])) {
				$explode = explode("\n", $row['text_val']);
				foreach($explode as $k => $v) {
					$col_params[$row['code']][($k+1)] = trim($v);
				}
			}
		}
        $this->data['param'] = $col_params;
	}

	public function save($data) {
		parent::save($data);
		$this->setParams($data);
	}

    public function delete() {
        parent::delete();
        $this->db->delete("DELETE FROM ".$this->table_param." WHERE ".$this->item." = ".$this->item_id);
    }

	private function setParams($data) {
		$classtype = "Type".ucfirst(strtolower($data['item']['type']));
		if(class_exists($classtype)) {
		    $obj = new $classtype(null, null);
		} else {
		    $obj = new TypeDefault(null, null);
		}

		foreach($obj->params as $param => $paramtype) {
			if(isset($data['param'][$param])) {
				$value = $paramtype == 'int' ? (int) $data['param'][$param] : $data['param'][$param];
			} else {
				$value = $paramtype == 'int' ? 0 : '';
			}
			$query = "REPLACE INTO ".$this->table_param."
					  (column_id, code, ".$paramtype."_val)
					  VALUES
					  (".$this->item_id.", '".$param."', '".$value."')";
			$this->db->replace($query);
		}
	}

}
