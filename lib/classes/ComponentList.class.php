<?php

class ComponentList extends ItemListDomain {

    protected $item = "component_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_component();
    }

	public function getComponentsByTemplate($template) {
		$return = array();
        $query = "SELECT *
                  FROM ".$this->table."
				  WHERE domain_id=".$this->session->domain_id." AND
				  		receiver IN ('', '".$template."')";
        $data = $this->db->select($query);
        $content = new Content();
        foreach($data as $row) {
            $return[$row['code']] = $content->get($row);
        }
        return $return;
	}

}
