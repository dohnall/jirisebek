<?php

class Content {

    public function __construct() {
		$this->db = Database::connect();
		$this->session = Session::getInstance(MODE);
    }

	public function get($component) {
		$return = array();
		$this->setTemplate($component['template']);
		$this->setWhere($component['whr']);
		$this->setCount($component['cnt']);
		$this->setPerpage($component['perpage']);
		$this->setLimit();
		$this->setOrderBy($component['orderby'], $component['sort']);

		$result = $this->db->select($this->query($component));
		foreach($result as $row) {
			$return[] = Section::getInstance($row['section_id']);
		}

		return $return;
	}

	private function setTemplate($template) {
		$this->template = $template;
	}

	private function setWhere($where) {
		$this->where = preg_replace('/\n/', " AND ", $where);
		if($this->where) {
			$this->where = " AND ".$this->where;
		}
	}

	private function setCount($count) {
		$this->count = $count;
	}

	private function setPerpage($perpage) {
		$this->perpage = $perpage;
	}

	private function setLimit() {
		$this->limit = "";
		if($this->count) {
			$this->limit.= "LIMIT 0, ".$this->count;
		} elseif($this->perpage) {
			$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 1;
			$from = ($page - 1) * $this->perpage;
			$this->limit.= "LIMIT ".$from.", ".$this->perpage;
		}
	}

	private function setOrderBy($orderby, $sort) {
		$this->joinSTV = "";
		if($orderby == 'rand') {
			$this->orderby = "RAND()";
		} elseif(substr($orderby, 0, 4) == "stv.") {
			$col = substr($orderby, 4);
			$query = "SELECT type
					  FROM ".Config::db_table_column()."
					  WHERE code = '".$col."'";
			$type = $this->db->select($query, true, "type");

			$classtype = "Type".ucfirst(strtolower($type));
			if(class_exists($classtype)) {
			    $obj = new $classtype(null, null);
			} else {
			    $obj = new TypeDefault(null, null);
			}

			$this->joinSTV = "LEFT JOIN ".Config::db_table_section_text_value()." stv ON (st.section_text_id = stv.section_text_id AND code='".$col."')";
			$this->orderby = "stv.".$obj->field."_val ".$sort;
		} else {
			$this->orderby = $orderby." ".$sort;
		}
	}

	private function query($component) {
        $query = "SELECT DISTINCT(s.section_id) AS section_id
                  FROM ".Config::db_table_section()." s
                  LEFT JOIN ".Config::db_table_section_text()." st ON (st.section_id = s.section_id)
                  LEFT JOIN ".Config::db_table_tree()." t ON (s.section_id = t.section_id)
                  ".$this->joinSTV."
                  WHERE st.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        WHERE st2.section_id = st.section_id AND
					        	  st2.lang_id = st.lang_id AND
								  st2.status = '1') AND
                        s.domain_id = ".$this->session->domain_id." AND
                        s.template = '".$this->template."' AND
                        st.lang_id = ".$this->session->lang_id." AND
                        st.status = '1'
                        ".$this->where."
                  ORDER BY ".$this->orderby."
				  ".$this->limit;
		return $query;
	}

}
