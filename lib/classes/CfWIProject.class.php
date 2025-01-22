<?php

class CfWIProject {

	public function __construct() {
		$this->db = Database::connect();
		$this->session = Session::getInstance(MODE);
	}

	public function getProjects() {
		$return = array();
		$query = "SELECT s1.section_id
				  FROM ".Config::db_table_section_text()." st1
				  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
				  LEFT JOIN ".Config::db_table_tree()." t1 ON (s1.section_id = t1.section_id)
				  WHERE st1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id) AND
						s1.domain_id = ".$this->session->domain_id." AND
						s1.template = '".CFWI_PROJECT_TEMPLATE."' AND
                        st1.lang_id = ".$this->session->lang_id."
				  ORDER BY t1.rank ASC";
		$res = $this->db->select($query);
		foreach($res as $row) {
			$return[] = Section::getInstance($row['section_id']);
		}
		return $return;
	}

}
