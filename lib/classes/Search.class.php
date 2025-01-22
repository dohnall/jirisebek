<?php
/*
$criteria = array(
	0 => 'string1 string2',
	'param1' => array(
		0 => 'string1',
		1 => 'string2'
	),
	'param2' => array(
		0 => 'string3'
	)
);
$section_id > 0 - pouze potomci zadane sekce
*/
class Search {

	protected $result = array();

    public function __construct($criteria, $section_id=0) {
		$this->criteria = $criteria;
		$this->section_id = $section_id;
		$this->db = Database::connect();
		$this->session = Session::getInstance(MODE);
    }

	public function getResult($from=0, $limit=0, $sortby="", $order="ASC") {
		if($this->result && $sortby) {
			$this->sortBy($sortby, $order);
		}
		if($limit) {
			return array_slice($this->result, $from, $limit);
		} else {
			return array_slice($this->result, $from);
		}
	}

	public function process() {
		if(is_array($this->criteria)) {
			$i = 1;
			foreach($this->criteria as $key => $criteria) {
				//fulltext
				if(is_numeric($key)) {
					//foreach(explode(" ", $criteria) as $word) {
						//if($word != "") {
							$sections = $this->getFulltextSections(trim($criteria));
							$this->mergeSections($sections, $i);
							$i++;
						//}
					//}
                    $sections = $this->getCodelistSections(trim($criteria));
                    $this->result = array_merge($this->result, $sections);
                //parametr
				} else {
					foreach($criteria as $value) {
						$sections = $this->getParamSections($key, trim($value));
						$this->mergeSections($sections, $i);
						$i++;
					}
				}
			}
		}
	}

	protected function sortBy($sortby, $order) {	
		$sectionList = new SectionList();
		$this->result = $sectionList->sortSectionsBy($sortby, $order, $this->result);
	}

	protected function mergeSections($sections, $i) {
		if($i == 1) {
			$this->result = $sections;
		} else {
			$this->result = array_merge($this->result, $sections);
		}
        $this->result = array_unique($this->result, SORT_REGULAR);
	}

	protected function getFulltextSections($param) {
		$return = array();
		
		$where = "";
		if($this->section_id) {
			$where.= " AND t1.parent_id = ".$this->section_id;
		}

		$query = "SELECT st1.section_id AS section_id, sf1.section_file_id, sf1.file, sf1.description, sf1.content,
                         MATCH (st1.name) AGAINST ('".$param."') AS rel1,
                         MATCH (stv1.varchar_val) AGAINST ('".$param."') AS rel2,
                         MATCH (stv1.text_val) AGAINST ('".$param."') AS rel3,
                         MATCH (sf1.description) AGAINST ('".$param."') AS rel4,
                         MATCH (sf1.content) AGAINST ('".$param."') AS rel5
				  FROM ".Config::db_table_section_text()." st1
				  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
				  LEFT JOIN ".Config::db_table_section_text_value()." stv1 ON (st1.section_text_id = stv1.section_text_id)
				  LEFT JOIN ".Config::db_table_section_text_value()." stv2 ON (st1.section_text_id = stv2.section_text_id AND stv2.code = 'public_from')
				  LEFT JOIN ".Config::db_table_section_text_value()." stv3 ON (st1.section_text_id = stv3.section_text_id AND stv3.code = 'public_to')
				  LEFT JOIN ".Config::db_table_tree()." t1 ON (s1.section_id = t1.section_id)
				  LEFT JOIN ".Config::db_table_section_file()." sf1 ON (st1.section_text_id = sf1.section_text_id AND sf1.code = 'attachments')
				  WHERE s1.domain_id = ".$this->session->domain_id." AND
						s1.template IN ('default', 'news-detail', 'desk-detail', 'contacts', 'contacts-department') AND
						IF(s1.template = 'desk-detail', stv2.datetime_val <= CURDATE(), true) AND
						IF(s1.template = 'desk-detail', stv3.datetime_val >= CURDATE(), true) AND
						MATCH (st1.name, stv1.varchar_val, stv1.text_val, sf1.description, sf1.content) AGAINST ('".$param."' IN BOOLEAN MODE) AND
						st1.status = '1' AND
                        st1.lang_id = ".$this->session->lang_id.$where."
                  GROUP BY section_id, section_file_id
                  HAVING (rel1*3)+(rel2*2)+(rel3*2)+(rel4*1)+(rel5*1) > 0
                  ORDER BY (rel1*3)+(rel2*2)+(rel3*2)+(rel4*1)+(rel5*1) DESC";
        //d($query);
		$res = $this->db->select($query);
/*
		foreach($res as $row) {
			$return[] = $row['section_id'];
		}
*/
		return $res;
	}

    protected function getCodelistSections($param) {
        $return = array();

        $query = "SELECT st.section_id
                  FROM cms_codelist_record cr
                  LEFT JOIN cms_codelist_text ct ON (ct.codelist_text_id = cr.codelist_text_id AND ct.lang_id = ".$this->session->lang_id.")
                  LEFT JOIN cms_codelist_record_value crv ON (cr.codelist_record_id = crv.codelist_record_id)
                  LEFT JOIN cms_column_param cp ON (cp.code = 'codelist' AND cp.int_val = ct.codelist_id)
                  LEFT JOIN cms_column c ON (cp.column_id = c.column_id)
                  LEFT JOIN cms_section_text_value stv ON (stv.code = c.code AND stv.varchar_val = cr.code)
                  LEFT JOIN cms_section_text st ON (st.section_text_id = stv.section_text_id AND st.lang_id = ".$this->session->lang_id.")
                  WHERE (cr.name LIKE '%".$param."%' OR
				  		 crv.int_val LIKE '%".$param."%' OR
				  		 crv.varchar_val LIKE '%".$param."%' OR
						 crv.text_val LIKE '%".$param."%' OR
						 crv.datetime_val LIKE '%".$param."%') AND
						 st.status = '1'
				  GROUP BY st.section_id";
        $res = $this->db->select($query);
/*
        foreach($res as $row) {
            $return[] = $row['section_id'];
        }
*/
        return $res;
    }

	protected function getParamSections($code, $param) {
		$return = array();

		$where = "";
		if($this->section_id) {
			$where.= " AND t1.parent_id = ".$this->section_id;
		}

		if(MODE != 'CMS') {
			$where.= " AND st1.status = '1'";
		}

		$query = "SELECT DISTINCT(st1.section_id) AS section_id
				  FROM ".Config::db_table_section_text_value()." stv1
				  LEFT JOIN ".Config::db_table_section_text()." st1 ON (st1.section_text_id = stv1.section_text_id)
				  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
				  LEFT JOIN ".Config::db_table_tree()." t1 ON (s1.section_id = t1.section_id)
				  WHERE stv1.code = '".$code."' AND
				  		(stv1.int_val LIKE '%".$param."%' OR
				  		 stv1.varchar_val LIKE '%".$param."%' OR
						 stv1.text_val LIKE '%".$param."%' OR
						 stv1.datetime_val LIKE '%".$param."%') AND
						st1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id) AND
						s1.domain_id = ".$this->session->domain_id." AND
                        st1.lang_id = ".$this->session->lang_id.$where;
		$res = $this->db->select($query);
/*
		foreach($res as $row) {
			$return[] = $row['section_id'];
		}
*/
		return $res;
	}

}
