<?php

class SectionList {

    public function __construct() {
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
    }

    public function getHomeId($domain_id = 0, $lang_id = 0) {
        $domain_id = $domain_id ? $domain_id : $this->session->domain_id;
        $lang_id = $lang_id ? $lang_id : $this->session->lang_id;
        $query = "SELECT st.section_id
                  FROM ".Config::db_table_section_text()." st
                  LEFT JOIN ".Config::db_table_section()." s ON (s.section_id = st.section_id AND st.lang_id = ".$lang_id.")
                  LEFT JOIN ".Config::db_table_tree()." t ON (t.section_id = st.section_id)
                  WHERE s.domain_id = ".$domain_id." AND
                        t.parent_id = 0";
        return $this->db->select($query, true, "section_id");
    }

	public function activateItems($item_ids) {
		$query = "UPDATE ".Config::db_table_section_text()." SET status = '1'
				  WHERE section_id IN (".implode(', ', $item_ids).") AND lang_id = ".$this->session->lang_id;
		$this->db->update($query);
	}

	public function deactivateItems($item_ids) {
		$query = "UPDATE ".Config::db_table_section_text()." SET status = '0'
				  WHERE section_id IN (".implode(', ', $item_ids).") AND lang_id = ".$this->session->lang_id;
		$this->db->update($query);
	}

	public function getSectionsByTemplate($template, $type=1, $parent_id=0, $order='') {
		$where1 = $where2 = $where3 = "";
		if(MODE != 'CMS') {
			$where1 = "AND st1.status = '1'";
			$where2 = "AND st2.status = '1'";
		}
		if($type != 1 && $parent_id) {
			$where3 = "AND t1.parent_id=".$parent_id;
		}

		if($order) {
			$orderby = $order;
		} else {
			$orderby = 's1.section_id ASC';
		}

        $query = "SELECT st1.section_id, st1.name
                  FROM ".Config::db_table_section_text()." st1
                  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
                  LEFT JOIN ".Config::db_table_tree()." t1 ON (st1.section_id = t1.section_id)
                  WHERE st1.section_text_id = (
                            SELECT MAX(st2.section_text_id)
                            FROM ".Config::db_table_section_text()." st2
    						LEFT JOIN ".Config::db_table_tree()." t2 ON (st2.section_id = t2.section_id)
                            WHERE st2.section_id = st1.section_id AND
                                  st2.lang_id = st1.lang_id AND
                                  t2.main = '1'
                                  ".$where2."
						) AND
                        s1.domain_id = ".$this->session->domain_id." AND
                        s1.template = '".$template."' AND
                        st1.lang_id = ".$this->session->lang_id." AND
                        t1.main = '1'
                        ".$where1."
                        ".$where3."
                  ORDER BY ".$orderby;
        $sections = $this->db->select($query);
        $return = array();
		foreach($sections as $section) {
			$return[$section['section_id']] = $section['name'];
		}
		return $return;
	}

	public function getSectionsByUrl($url, $preview = 0) {
        $status = $preview ? "('0', '1')" : "('1')";

        $query = "SELECT st1.section_id
                  FROM ".Config::db_table_section_text()." st1
                  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
                  WHERE st1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id) AND
                        s1.domain_id = ".$this->session->domain_id." AND
                        st1.url = '".$url."' AND
                        st1.status IN ".$status." AND
                        st1.lang_id = ".$this->session->lang_id."
                  ORDER BY st1.name ASC";
        $sections = $this->db->select($query);
        $return = array();
		foreach($sections as $section) {
			$return[$section['section_id']] = $section['section_id'];
		}
		return $return;
	}

	public function sortSectionsBy($col, $sort = "ASC", $secs = array()) {
		$where = "";
		if($secs) {
			$where = "s1.section_id IN (".implode(", ", $secs).") AND";
		}
		
		if(substr($col, 0, 5) == 'stv1.') {
			$col = "stv1.varchar_val";
			$where.= " stv1.code = 'like' AND";
		}

        $query = "SELECT DISTINCT(st1.section_id) AS section_id
                  FROM ".Config::db_table_section_text_value()." stv1
                  LEFT JOIN ".Config::db_table_section_text()." st1 ON (st1.section_text_id = stv1.section_text_id)
                  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
                  WHERE stv1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id) AND
                        s1.domain_id = ".$this->session->domain_id." AND
                        ".$where."
                        st1.status = '1' AND
                        st1.lang_id = ".$this->session->lang_id."
                  ORDER BY ".$col." ".$sort;
        $sections = $this->db->select($query);
        $return = array();
		foreach($sections as $section) {
			$return[] = $section['section_id'];
		}
		return $return;
	}

	public function getLastUpdated($count, $init = false) {
        $query = "SELECT st1.section_id
                  FROM ".Config::db_table_section_text()." st1
                  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
                  LEFT JOIN ".Config::db_table_tree()." t1 ON (st1.section_id = t1.section_id)
                  WHERE st1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        LEFT JOIN ".Config::db_table_tree()." t2 ON (st2.section_id = t2.section_id)
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id AND
								  t2.main = '1') AND
                        s1.domain_id = ".$this->session->domain_id." AND
                        st1.lang_id = ".$this->session->lang_id." AND
                        t1.main = '1'
                  ORDER BY st1.updated DESC
				  LIMIT 0, ".$count;
        $sections = $this->db->select($query);

        $return = array();
		foreach($sections as $section) {
			if($init == true) {
				$return[$section['section_id']] = Section::getInstance($section['section_id']);
			} else {
				$return[$section['section_id']] = $section['section_id'];
			}
		}
		return $return;
	}

    public function export($template, $type, $parent_id=0) {
        header("Content-Type:application/octet-stream; charset=windows-1250");
        header("Content-Disposition: inline; filename=".$template."-".date("Y-m-d-H-i-s").".csv");
        if(DEBUGGER === true) {
			NDebugger::$bar = FALSE;
		}
		$config = new Config();
		$tabs = $config->getTabs($template);
		$arr = array();
		foreach($tabs as $tab) {
			$arr[$tab['template_tab_id']] = $config->getCols($tab['template_tab_id']);
		}
		$sections = $this->getSectionsByTemplate($template, $type, $parent_id);

        $f = fopen("php://output", "w");

        $row = "name;status";
		foreach($arr as $tab) {
			foreach($tab as $column_id => $column) {
				if($column['item']['type'] != "data") {
					$row.= ";".$column['item']['code'];
				}
			}
		}
		$row.= "\n";
		fwrite($f, iconv('UTF-8', 'CP1250', $row));
        
        foreach($sections as $section_id => $name) {
        	$section = Section::getInstance($section_id);
            $row = $section->get('text', 'name').";".$section->get('text', 'status');
			foreach($arr as $tab) {
				foreach($tab as $column_id => $column) {
					if($column['item']['type'] != "data") {
						if(in_array($column['item']['type'], array('file', 'image'))) {
							$files = $section->get('file', $column['item']['code']);
							$value = array();
							foreach($files as $file) {
								$value[] = $file['file'];
							}
						} else {
							$value = $section->get('value', $column['item']['code']);
							$value = str_replace(";", "|||", $value);
						}
						if(is_array($value)) {
							$value = implode('|||', $value);
						}
						$row.= ";".preg_replace("/[\s]{2,}/", " ", $value);
					}
				}
			}
			$row.= "\n";
            fwrite($f, iconv('UTF-8', 'CP1250//IGNORE', $row));
        }
        fclose($f);
        exit;
    }

    public function import($parent_id, $template, $data) {
        $error = 0;

		$config = new Config();
		$tabs = $config->getTabs($template);
		$arr = array();
		foreach($tabs as $tab) {
			$columns = $config->getCols($tab['template_tab_id']);
			$arr = array_merge($arr, $columns);
		}

		$sectionData = [];
        foreach($data as $k => $row) {
        	if($k) {
	            $cols = explode(";", iconv('CP1250', 'UTF-8', $row));
	            if(count($arr)+2 != count($cols)) {
	                $error = $k + 1;
	                break;
				}

				$section = Section::getInstance(0);
				$section->create(array(
					'template' => $template,
					'parent' => $parent_id,
					'insert' => 2,
					'name' => $cols[0],
				));
				
				$sectionData['text']['status'] = $cols[1];
				foreach($arr as $k => $col) {
					if(strpos($cols[$k+2], '|||') !== false) {
						$value = explode('|||', $cols[$k+2]);
						if(in_array($col['item']['type'], array('file', 'image'))) {
							foreach($value as $v) {
								$sectionData['file'][$col['item']['code']]['file'][] = $v;
								$sectionData['file'][$col['item']['code']]['alt'][] = '';
								$sectionData['file'][$col['item']['code']]['description'][] = '';
							}
						} else {
							foreach($value as $v) {
								$sectionData['value'][$col['item']['code']][] = $v;
							}
						}
					} else {
						$value = $cols[$k+2];
						if(in_array($col['item']['type'], array('file', 'image'))) {
							$sectionData['file'][$col['item']['code']]['file'][] = $value;
							$sectionData['file'][$col['item']['code']]['alt'][] = '';
							$sectionData['file'][$col['item']['code']]['description'][] = '';
						} else {
							$sectionData['value'][$col['item']['code']] = $value;
						}					
					}
				}
//d($sectionData);
				$section->save($sectionData);
			}
        }
        if($error === 0) {
            return true;
        } else {
            return $error;
        }
    }

}
