<?php

class Section {

    public $domain_id = 0;
    public $lang_id = 0;
    public $section_id = 0;
    public $parent_id = 0;
    private $item = array();
    private $db = "";
    private $session = array();
    private $params = array();
    private $isActive = null;
    private static $instances = array();

    public function __construct($section_id, $domain_id = 0, $lang_id = 0, $parent_id = 0) {
        $this->section_id = $section_id;
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
        $this->domain_id = $domain_id ? $domain_id : $this->session->domain_id;
        $this->lang_id = $lang_id ? $lang_id : $this->session->lang_id;
        $this->parent_id = $parent_id;
    }

    public static function getInstance($section_id, $domain_id = 0, $lang_id = 0, $parent_id = 0) {
        if(!isset(self::$instances[$section_id])) {
            $classname = __CLASS__;
            self::$instances[$section_id] = new $classname($section_id, $domain_id, $lang_id, $parent_id);
        }
        return self::$instances[$section_id];
    }

    public function get($type = "", $sec = false, $force = false) {
        if($type) {
        	$type = $type == 'text' ? 'section' : $type;
        	if($type == 'file') {
				$force = true;
			}
            if(!isset($this->item[$type]) || $force === true) {
                switch($type) {
					case 'section': $this->loadSection(); break;
                    case 'value': $this->loadSectionValue(); break;
                    case 'file': $this->loadSectionFile($sec); break;
                    case 'children': $this->loadChildren($sec);	break;
                    case 'siblings': $this->loadSiblings($sec);	break;
                    case 'relations': $this->loadRelations($sec); break;
                    case 'path': $this->loadPath(); break;
                    case 'url': $this->loadUrl($sec); break;
                    case 'title': $this->loadTitle(); break;
                    case 'version': $this->loadVersion(); break;
                    case 'log': $this->loadLog(); break;
                    case 'previous': $this->loadPrevious($sec); break;
                    case 'next': $this->loadNext($sec); break;
                    case 'visibility': $this->loadVisibility(); break;
                    case 'forum': $this->loadForum(); break;
                    default: return false;
                }
            }
            if(in_array($type, array('section', 'value')) && $sec !== false) {
                if(isset($this->item[$type][$sec])) {
					return $this->item[$type][$sec];
				} else {
					return null;
				}
			} else {
                return $this->item[$type];
            }
        } else {
        	$this->loadSection();
        	$this->loadSectionValue();
        	$this->loadSectionFile();
            return $this->item;
        }
    }

	public function set($value, $type, $col = "") {
		if($col != "") {
			$this->item[$type][$col] = $value;
		} else {
			$this->itemp[$type] = $value;
		}
	}

	public function setParams($arr) {
		foreach($arr as $k => $v) {
			$this->params[$k] = $v;
		}
	}

    public function hasChildren() {
        //if(!isset($this->item['children'])) {
        $this->loadChildren(true);
        //}
        return count($this->item['children']);
    }

	public function hasVersion() {
        return count($this->get('version'));
	}

	public function versionExists($version) {
		$query = "SELECT COUNT(*) AS cnt
				  FROM ".Config::db_table_section_text()."
				  WHERE section_id = ".$this->section_id." AND
				  		lang_id = ".$this->session->lang_id." AND
						section_text_id = ".$version;
		return $this->db->select($query, true, "cnt");
	}

	public function isActive() {
		if(is_null($this->isActive)) {
	        $query = "SELECT st1.status
	                  FROM ".Config::db_table_section_text()." st1
	                  LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
	                  WHERE st1.section_text_id = (
						        SELECT MAX(st2.section_text_id)
						        FROM ".Config::db_table_section_text()." st2
						        WHERE st2.section_id = st1.section_id AND
						        	  st2.lang_id = st1.lang_id) AND
	                        st1.section_id IN (".implode(', ', array_keys($this->get('path'))).") AND
	                        st1.lang_id = ".$this->lang_id;
	        $result = $this->db->select($query);
			$this->isActive = true;
	        foreach($result as $row) {
				if($row['status'] == 0) {
					$this->isActive = false;
					break;
				}
			}
		}
		return $this->isActive;
	}

    public function create($data) {
        //vlozit do section
        $query = "INSERT INTO ".Config::db_table_section()."
                  (domain_id, template)
                  VALUES
                  (".$this->domain_id.", '".$data['template']."')";
        //nastavit section_id
        $this->section_id = $this->db->insert($query);

        $parent = Section::getInstance($data['parent']);
        //nastavit rank
        //na zacatek
        if($data['insert'] == 1) {
            $rank = 1;
            $query = "UPDATE ".Config::db_table_tree()." SET
                      rank = rank + 1
                      WHERE parent_id = ".$data['parent'];
            $this->db->update($query);
        //na konec
        } else {
            $children = $parent->hasChildren();
            $rank = $children + 1;
        }
        $depth = (int)$parent->get('section', 'depth') + 1;
        
        //vlozit do tree
        $query = "INSERT INTO ".Config::db_table_tree()."
                  (section_id, parent_id, depth, rank)
                  VALUES
                  (".$this->section_id.", ".$data['parent'].", ".$depth.", ".$rank.")";
        $this->db->insert($query);

        //neni uvodni stranka
        if($data['parent']) {
        	$this->set($data['parent'], 'section', 'parent_id');
            $url = $this->makeUrl('', $data['name']);
        //je uvodni stranka a tak nema zadne url
        } else {
            $url = "";
        }

        //vlozit do section_text
        $query = "INSERT INTO ".Config::db_table_section_text()."
                  (section_id, lang_id, user_id,
                   name, url, title, inserted, updated)
                  VALUES
                  (".$this->section_id.", ".$this->lang_id.", ".$this->session->user_id.",
                   '".$data['name']."', '".$url."', '".$data['name']."', NOW(), NOW())";
        $this->db->insert($query);

        return $this->section_id;
    }

	public function save($data, $new_version = false) {
		$this->get();
		if(isset($data['section'])) {
			$this->setSection($data['section']);
		}
		if(isset($data['visibility'])) {
			$this->setVisibility($data['visibility']);
		}		
		if(isset($data['text'])) {
			$this->setSectionText($data['text'], $new_version);
		} elseif($new_version) {
			$data['text'] = array();
			$this->setSectionText($data['text'], $new_version);
		} else {
			$this->setUpdated();
		}
		if(isset($data['value'])) {
			$this->setSectionValue($data['value']);
		} else {
			$this->setSectionValue(array());
		}
		if(isset($data['file'])) {
			$this->setSectionFile($data['file']);
		} else {
			$this->setSectionFile(array());
		}
	}

	public function copy() {
		$data = $this->get();
		$data['text']['name'] = $data['section']['name']." Copy";

		$copy = clone $this;

	    $copy->section_id = $copy->create(array(
			'template' => $data['section']['template'],
			'parent' => $data['section']['parent_id'],
			'name' => $data['text']['name'],
			'insert' => 1,
		));
		$newdata = $copy->get();
		unset($newdata['value']);
		unset($newdata['file']);
		$newdata = array_merge($data, $newdata);
	    $copy->save($newdata);
	    return $copy;
	}

    public function delete() {
        if($this->get('section', 'parent_id') && $this->hasChildren()) {
            $children = $this->get('children', true);
            foreach($children as $child) {
                if($child->get('section', 'removable') == '1') {
					$child->delete();
				}
            }
        }
        if($this->get('section', 'removable') == '1') {
	    	$query = "SELECT section_text_id
					  FROM ".Config::db_table_section_text()."
					  WHERE section_id = ".$this->section_id;
			$ids = $this->db->select($query);
			foreach($ids as $row) {
				$this->db->delete("DELETE FROM ".Config::db_table_section_text_value()." WHERE section_text_id = ".$row['section_text_id']);
				$this->db->delete("DELETE FROM ".Config::db_table_section_file()." WHERE section_text_id = ".$row['section_text_id']);
			}

	    	$query = "SELECT *
					  FROM ".Config::db_table_menu_item()."
					  WHERE section_id = ".$this->section_id;
			$ids = $this->db->select($query);
			foreach($ids as $row) {
				$this->db->update("UPDATE ".Config::db_table_menu_item()." SET rank = rank - 1 WHERE menu_id = ".$row['menu_id']." AND parent_id = ".$row['parent_id']." AND rank > ".$row['rank']);
				$this->db->delete("DELETE FROM ".Config::db_table_menu_item()." WHERE menu_item_id = ".$row['menu_item_id']);
			}

	        $this->db->update("UPDATE ".Config::db_table_tree()." SET rank = rank - 1 WHERE parent_id = ".$this->get('section', 'parent_id')." AND rank > ".$this->get('section', 'rank'));
	        $this->db->delete("DELETE FROM ".Config::db_table_user_section()." WHERE section_id = ".$this->section_id);
	        $this->db->delete("DELETE FROM ".Config::db_table_section()." WHERE section_id = ".$this->section_id);
	        $this->db->delete("DELETE FROM ".Config::db_table_section_group()." WHERE section_id = ".$this->section_id);
	        $this->db->delete("DELETE FROM ".Config::db_table_section_text()." WHERE section_id = ".$this->section_id);
	        $this->db->delete("DELETE FROM ".Config::db_table_tree()." WHERE section_id = ".$this->section_id);
	        $this->db->delete("DELETE FROM ".Config::db_table_forum()." WHERE section_id = ".$this->section_id);
        }
    }

	public function deleteVersion($section_text_id) {
		$query = "DELETE FROM ".Config::db_table_section_text()."
				  WHERE section_text_id = ".$section_text_id." AND
				  		section_id = ".$this->section_id." AND
						lang_id = ".$this->lang_id;
		$affected = $this->db->delete($query);
		if($affected) {
			$this->db->delete("DELETE FROM ".Config::db_table_section_text_value()." WHERE section_text_id = ".$section_text_id);
			$this->db->delete("DELETE FROM ".Config::db_table_section_file()." WHERE section_text_id = ".$section_text_id);
		}
	}

	public function setRank($pid, $sid) {
		$item = Section::getInstance($sid);
		$new_rank = $item->get('section', 'rank');
		$old_rank = $this->get('section', 'rank');

		if($old_rank < $new_rank) {
			$query = "UPDATE ".Config::db_table_tree()." SET rank = rank - 1
					  WHERE parent_id = ".$pid." AND
					  		rank > ".$old_rank." AND
							rank <= ".$new_rank;
		} else {
			$query = "UPDATE ".Config::db_table_tree()." SET rank = rank + 1
					  WHERE parent_id = ".$pid." AND
					  		rank < ".$old_rank." AND
							rank >= ".$new_rank;
		}
		$this->db->update($query);
		$query = "UPDATE ".Config::db_table_tree()." SET rank = ".$new_rank."
				  WHERE section_id = ".$this->section_id." AND
				  		parent_id = ".$pid;
		$this->db->update($query);
	}

	public function addRelation($parent_id) {
		$parent = Section::getInstance($parent_id);
		$query = "INSERT INTO ".Config::db_table_tree()."
				  (section_id, parent_id, depth, rank, main)
				  VALUES
				  (".$this->section_id.", ".$parent_id.", ".($parent->get('section', 'depth')+1).", ".($parent->hasChildren()+1).", '0')";
		$this->db->insert($query);
	}

	public function deleteRelation($parent_id) {
		$query = "SELECT rank
				  FROM ".Config::db_table_tree()."
				  WHERE section_id = ".$this->section_id." AND
				  		parent_id = ".$parent_id." AND
						main = '0'";
		$rank = $this->db->select($query, true, "rank");
		if($rank) {
			$query = "UPDATE ".Config::db_table_tree()." SET
						rank = rank-1
					  WHERE parent_id = ".$parent_id." AND
					  		rank > ".$rank;
			$this->db->update($query);
			$query = "DELETE FROM ".Config::db_table_tree()."
					  WHERE section_id = ".$this->section_id." AND
					  		parent_id = ".$parent_id." AND
							main = '0'";
			$this->db->delete($query);
		}
	}

	public function setMainRelation($parent_id) {
		$query = "UPDATE ".Config::db_table_tree()." SET
					main = '0'
				  WHERE section_id = ".$this->section_id;
		$this->db->update($query);
		$query = "UPDATE ".Config::db_table_tree()." SET
					main = '1'
				  WHERE section_id = ".$this->section_id." AND
				  		parent_id = ".$parent_id;
		$this->db->update($query);
	}

	public function setViews() {
		$query = "UPDATE ".Config::db_table_section_text()." SET
					views=views+1
				  WHERE section_text_id = ".$this->get('text', 'section_text_id');
		$this->db->update($query);
	}

	private function setSection($newdata) {
		$newdata['removable'] = isset($newdata['removable']) ? 1 : 0;

		$data = $this->get('section');

		if(isset($newdata['parent_id']) && $data['parent_id'] != $newdata['parent_id']) {
		    $query = "UPDATE ".Config::db_table_tree()." SET
						rank = rank - 1
					  WHERE parent_id = ".$data['parent_id']." AND
					        rank > ".$data['rank'];
            $this->db->update($query);

		    $parent = Section::getInstance($newdata['parent_id']);
			$query = "UPDATE ".Config::db_table_tree()." SET
						parent_id = ".$parent->section_id.",
						depth = ".($parent->get('section', 'depth')+1).",
						rank = ".($parent->hasChildren()+1)."
					  WHERE section_id = ".$this->section_id." AND
					  		main = '1'";
			$this->db->update($query);

            $this->treefix($newdata['parent_id']);
		}

		$data = array_merge($data, $newdata);
		$query = "UPDATE ".Config::db_table_section()." SET
					template = '".$data['template']."',
					removable = '".$data['removable']."'
				  WHERE section_id = ".$this->section_id;
		$this->db->update($query);
	}

	private function setVisibility($data) {
		$this->db->delete("DELETE FROM ".Config::db_table_section_group()." WHERE section_id = ".$this->section_id);
		foreach($data as $group_id) {
			if($group_id == -1) {
				break;
			} elseif(is_numeric($group_id) && $group_id >= 0) {
				$query = "INSERT INTO ".Config::db_table_section_group()."
						  (section_id, group_id)
						  VALUES
						  (".$this->section_id.", ".$group_id.")";
				$this->db->insert($query);
			}
		}
	}

	private function setSectionText($newdata, $new_version) {
		$data = $this->get('text');
		$data = array_merge($data, $newdata);
		
		if($new_version === false && $this->hasVersion()) {
			$query = "UPDATE ".Config::db_table_section_text()." SET
						name = '".$data['name']."',
						url = '".$this->makeUrl($data['url'], $data['name'])."',
						url_children = '".$data['url_children']."',
						title = '".$data['title']."',
						title_children = '".$data['title_children']."',
						description = '".$data['description']."',
						keywords = '".$data['keywords']."',
						updated = NOW(),
						status = '".$data['status']."'
					  WHERE section_text_id = ".$data['section_text_id'];
			$this->db->update($query);
		} else {
			$query = "INSERT INTO ".Config::db_table_section_text()."
					  (section_id, lang_id, user_id, name,
					   url, url_children, title, title_children,
					   description, keywords, views, inserted, updated, status)
					  VALUES
					  (".$this->section_id.", ".$this->lang_id.", ".$this->session->user_id.", '".mysqli_real_escape_string(MySQL::$conn, $data['name'])."',
					   '".$this->makeUrl($data['url'], $data['name'])."', '".mysqli_real_escape_string(MySQL::$conn, $data['url_children'])."', '".mysqli_real_escape_string(MySQL::$conn, $data['title'])."', '".mysqli_real_escape_string(MySQL::$conn, $data['title_children'])."',
					   '".mysqli_real_escape_string(MySQL::$conn, $data['description'])."', '".mysqli_real_escape_string(MySQL::$conn, $data['keywords'])."', '".$data['views']."', NOW(), NOW(), '".$data['status']."')";
			$this->item['section']['section_text_id'] = $this->db->insert($query);
		}
	}

	private function setUpdated() {
		$section_text_id = $this->get('text', 'section_text_id');
		$query = "UPDATE ".Config::db_table_section_text()." SET
					updated = NOW()
				  WHERE section_text_id = ".$section_text_id;
		$this->db->update($query);
	}

	private function setSectionValue($newdata) {
		$config = new Config();
		$data = $this->get('value');
		$data = array_merge($data, $newdata);

		foreach($config->getTabs($this->get('section', 'template')) as $row) {
			foreach($config->getCols($row['template_tab_id']) as $col) {
		        $classname = "Type".ucfirst(strtolower($col['item']['type']));
		        if(class_exists($classname)) {
		            $class = new $classname($this, $col);
		        } else {
		            $class = TypeDefault($this, $col);
		        }
				$field = $class->field;
				if($field) {
					if(isset($col['param']['relation']) && $col['param']['relation']) {
						$query = "DELETE FROM ".Config::db_table_section_text_value()."
								  WHERE code = '".$col['param']['relation']."' AND
										varchar_val = '".$this->section_id."'";
						$this->db->delete($query);
					} else {
						$query = "DELETE FROM ".Config::db_table_section_text_value()."
								  WHERE section_text_id = ".$this->get('text', 'section_text_id')." AND
								  		code = '".$col['item']['code']."'";
						$this->db->delete($query);
					}

					$value = isset($data[$col['item']['code']]) ? $data[$col['item']['code']] : "";
					if(is_array($value) && $field != 'blob') {
						foreach($value as $v) {
							if($field == 'datetime' && isset($newdata[$col['item']['code']])) {
								$datetime = new DateTime($v, new DateTimeZone(Config::getVar('USER_TIMEZONE')));
								$datetime->setTimezone(new DateTimeZone(DEFAULT_TIMEZONE));
								$v = $datetime->format('Y-m-d H:i:s');
							} elseif($field == 'text' && isset($newdata[$col['item']['code']])) {
								$v = str_replace(ROOT, '{$ROOT}', $v);
							}

							if(isset($col['param']['relation']) && $col['param']['relation']) {
								$section = Section::getInstance($v);
								$query = "INSERT INTO ".Config::db_table_section_text_value()."
										  (section_text_id, code, ".$field."_val)
										  VALUES
										  (".$section->get('text', 'section_text_id').", '".$col['param']['relation']."', '".mysqli_real_escape_string(MySQL::$conn, $this->section_id)."')";
								$this->db->insert($query);
							} else {
								$query = "INSERT INTO ".Config::db_table_section_text_value()."
										  (section_text_id, code, ".$field."_val)
										  VALUES
										  (".$this->get('text', 'section_text_id').", '".$col['item']['code']."', '".mysqli_real_escape_string(MySQL::$conn, $v)."')";
								$this->db->insert($query);
							}
						}
					} else {
						if($field == 'datetime' && isset($newdata[$col['item']['code']])) {
							$datetime = new DateTime($value, new DateTimeZone(Config::getVar('USER_TIMEZONE')));
							$datetime->setTimezone(new DateTimeZone(DEFAULT_TIMEZONE));
							$value = $datetime->format('Y-m-d H:i:s');
						} elseif($field == 'blob' && !is_string($value)) {
							$value = serialize($value);
							//$value = str_replace("'", "\'", $value);
						} elseif($field == 'text' && isset($newdata[$col['item']['code']])) {
							$value = str_replace(ROOT, '{$ROOT}', $value);
						}

						if(isset($col['param']['relation']) && $col['param']['relation']) {
							if($value) {
								$section = Section::getInstance($value);
								$query = "INSERT INTO ".Config::db_table_section_text_value()."
										  (section_text_id, code, ".$field."_val)
										  VALUES
										  (".$section->get('text', 'section_text_id').", '".$col['param']['relation']."', '".mysqli_real_escape_string(MySQL::$conn, $this->section_id)."')";
								$this->db->insert($query);
							}
						} else {
							$query = "INSERT INTO ".Config::db_table_section_text_value()."
									  (section_text_id, code, ".$field."_val)
									  VALUES
									  (".$this->get('text', 'section_text_id').", '".$col['item']['code']."', '".mysqli_real_escape_string(MySQL::$conn, $value)."')";
							$this->db->insert($query);
						}
					}
				}
			}
		}
	}

	private function setSectionFile($newdata) {
		$data = $this->get('file');
		$data = array_merge($data, $newdata);

		foreach($data as $code => $value) {
			$query = "DELETE FROM ".Config::db_table_section_file()."
					  WHERE section_text_id = ".$this->get('text', 'section_text_id')." AND
					  		code = '".$code."'";
			$this->db->delete($query);
			$rank = 1;
			foreach($value['file'] as $k => $file) {
				if(!empty($file)) {
					$query = "INSERT INTO ".Config::db_table_section_file()."
							  (section_text_id, file, hash, code, alt, description, download, rank)
							  VALUES
							  (".$this->get('text', 'section_text_id').", '".$file."',
							   '".(isset($value['hash'][$k]) && $value['hash'][$k] ? $value['hash'][$k] : '')."',
							   '".$code."',
							   '".(isset($value['alt'][$k]) && $value['alt'][$k] ? $value['alt'][$k] : '')."',
							   '".(isset($value['description'][$k]) && $value['description'][$k] ? $value['description'][$k] : '')."',
							   '".(isset($value['download'][$k]) && $value['download'][$k] ? $value['download'][$k] : 0)."',
							   ".($rank++).")";
					$section_file_id = $this->db->insert($query);
                    if(substr($file, -4) == '.pdf') {
                        $url = FILES.'download/'.$file;
                        $content = file_get_contents(PDF_READER_URL.'/?u='.$url);
                        $query = "UPDATE ".Config::db_table_section_file()." SET content = '".mysqli_real_escape_string(MySQL::$conn, $content)."' WHERE section_file_id = ".$section_file_id;
                        $this->db->update($query);
                    }
				}
			}
		}
	}

	private function makeUrl($url, $name) {
		if($this->get('section', 'parent_id') != 0) {
			if($url == '') {
				$url = Common::friendlyUrl($name);
			}
			$part = $url;
			$i = 2;
	        while($this->checkUrl($url) > 0) {
	        	$url = $part."-".$i;
				$i++;
			}
		} else {
			$url = '';
		}
		return $url;
	}

	private function checkUrl($url) {
        $query = "SELECT COUNT(*) AS cnt
                  FROM ".Config::db_table_section_text()." st1
                  LEFT JOIN ".Config::db_table_tree()." t1 ON (st1.section_id = t1.section_id)
                  WHERE st1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        LEFT JOIN ".Config::db_table_tree()." t2 ON (st2.section_id = t2.section_id)
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id AND
								  t2.main = '1') AND
                        st1.lang_id = ".$this->lang_id." AND
                        st1.url = '".$url."' AND
                        t1.main = '1' AND
                        t1.section_id != ".$this->section_id." AND
                        t1.parent_id = ".$this->get('section', 'parent_id');
		return $this->db->select($query, true, 'cnt');
	}

    private function loadSection() {
    	$where = array();
    	$where[1] = "s1.section_id = ".$this->section_id;
    	$where[2] = "st1.lang_id = ".$this->lang_id;
    	if($this->parent_id) {
			$where[3] = "t1.parent_id = ".$this->parent_id;
		} else {
			$where[3] = "t1.main = '1'";
		}

		$query = "SELECT	s1.section_id,
							s1.domain_id,
							s1.template,
							s1.removable,
							t1.parent_id,
							t1.depth,
							t1.rank,
							t1.main,
							st1.section_text_id,
							st1.lang_id,
							st1.user_id,
							st1.name,
							st1.url,
							st1.url_children,
							st1.title,
							st1.title_children,
							st1.description,
							st1.keywords,
							st1.views,
							st1.inserted,
							st1.updated,
							st1.status,
							false AS default_lang
					FROM ".Config::db_table_section_text()." st1
					LEFT JOIN ".Config::db_table_section()." s1 ON (s1.section_id = st1.section_id)
					LEFT JOIN ".Config::db_table_tree()." t1 ON (t1.section_id = st1.section_id)
					WHERE st1.section_text_id = (
					        SELECT MAX(st2.section_text_id)
					        FROM ".Config::db_table_section_text()." st2
					        WHERE st2.section_id = st1.section_id AND
					        	  st2.lang_id = st1.lang_id) AND
						  ".implode(' AND ', $where);
        $section = $this->db->select($query, true);

        if(MODE == 'CMS' && !isset($section['section_text_id'])) {
        	$where[2] = "st1.lang_id = ".$this->session->default_lang_id;
			$query = "SELECT	s1.section_id,
								s1.domain_id,
								s1.template,
								s1.removable,
								t1.parent_id,
								t1.depth,
								t1.rank,
								t1.main,
								st1.section_text_id,
								st1.lang_id,
								st1.user_id,
								st1.name,
								st1.url,
								st1.url_children,
								st1.title,
								st1.title_children,
								st1.description,
								st1.keywords,
								st1.views,
								st1.inserted,
								st1.updated,
								st1.status,
								true AS default_lang
						FROM ".Config::db_table_section_text()." st1
						LEFT JOIN ".Config::db_table_section()." s1 ON (s1.section_id = st1.section_id)
						LEFT JOIN ".Config::db_table_tree()." t1 ON (t1.section_id = st1.section_id)
						WHERE st1.section_text_id = (
						        SELECT MAX(st2.section_text_id)
						        FROM ".Config::db_table_section_text()." st2
						        WHERE st2.section_id = st1.section_id AND
						        	  st2.lang_id = st1.lang_id) AND
							  ".implode(' AND ', $where);
	        $section = $this->db->select($query, true);
        }

        $this->item['section'] = $section;
    }

	private function loadVisibility() {
		$this->item['visibility'] = array();
		$query = "SELECT group_id
				  FROM ".Config::db_table_section_group()."
				  WHERE section_id = ".$this->section_id;
		$groups = $this->db->select($query);
		foreach($groups as $row) {
			$this->item['visibility'][] = $row['group_id'];
		}
	}

    private function loadSectionValue() {
		$config = new Config();
		$relations = array();
		foreach($config->getTabs($this->get('section', 'template')) as $row) {
			foreach($config->getCols($row['template_tab_id']) as $col) {
				if(isset($col['param']['relation'])) {
					$relations[$col['param']['relation']] = $col['item']['code'];
				}
			}
		}

	    $query = "SELECT t1.*, t3.section_id, (SELECT COUNT(*)
							 FROM ".Config::db_table_section_text_value()." t2
							 WHERE t2.varchar_val = ".$this->section_id." AND
							 	   t2.code=t1.code) AS cnt
	              FROM ".Config::db_table_section_text_value()." t1
	              LEFT JOIN ".Config::db_table_section_text()." t3 ON (t1.section_text_id = t3.section_text_id)
	              WHERE t1.varchar_val = ".$this->section_id." AND
				  		t1.code IN ('".implode("', '", array_keys($relations))."')";
	    $rels = $this->db->select($query);
	    foreach($rels as $k => $v) {
			$rels[$k]['code'] = $relations[$v['code']];
			$rels[$k]['varchar_val'] = $v['section_id'];
		}

	    $query = "SELECT *, (SELECT COUNT(*)
							 FROM ".Config::db_table_section_text_value()." t2
							 WHERE t2.section_text_id = ".$this->get('text', 'section_text_id')." AND
							 	   t2.code=t1.code) AS cnt
	              FROM ".Config::db_table_section_text_value()." t1
	              WHERE t1.section_text_id = ".$this->get('text', 'section_text_id');
	    $res = $this->db->select($query);
	    $res = array_merge($res, $rels);

	    $section_values = $colTypes = array();
	    foreach($res as $row) {
	    	if(!isset($colTypes[$row['code']])) {
				$colTypes[$row['code']] = ColumnList::getColumnTypeByCode($row['code']);
			}

			$columnType = $colTypes[$row['code']];

			if(in_array($columnType, array('checkbox'))) {
				if(!is_null($row['int_val'])) {
					if($row['cnt'] > 1) {
						$section_values[$row['code']][] = $row['int_val'];
					} else {
						$section_values[$row['code']] = $row['int_val'];
					}
				}
			}
			if(in_array($columnType, array('default', 'select', 'text'))) {
				if(!is_null($row['varchar_val'])) {
					if($row['cnt'] > 1) {
						$section_values[$row['code']][] = stripslashes($row['varchar_val']);
						if($columnType == 'select') {
                            sort($section_values[$row['code']], SORT_NUMERIC);
                        }
					} else {
						$section_values[$row['code']] = stripslashes($row['varchar_val']);
					}
				}
			}
			if(in_array($columnType, array('textarea', 'html'))) {
				if(!is_null($row['text_val'])) {
					if($row['cnt'] > 1) {
						$section_values[$row['code']][] = str_replace('{$ROOT}', ROOT, stripslashes($row['text_val']));
					} else {
						$section_values[$row['code']] = str_replace('{$ROOT}', ROOT, stripslashes($row['text_val']));
					}
				}
			}
			if(in_array($columnType, array('date', 'datetime'))) {
				if(!is_null($row['datetime_val'])) {
					if($row['cnt'] > 1) {
						$section_values[$row['code']][] = $row['datetime_val'];
					} else {
						$section_values[$row['code']] = $row['datetime_val'];
					}
				}
			}
			if(in_array($columnType, array('data'))) {
				if(!is_null($row['blob_val']) && !empty($row['blob_val'])) {
					if($row['cnt'] > 1) {
						if(MODE == 'CMS') {
							$section_values[$row['code']][] = $row['blob_val'];
						} else {
							$section_values[$row['code']][] = unserialize($row['blob_val']);
						}
					} else {
						if(MODE == 'CMS') {
							$section_values[$row['code']] = $row['blob_val'];
						} else {
							$section_values[$row['code']] = unserialize($row['blob_val']);
						}
					}
				}
			}
		}

        $this->item['value'] = $section_values;
    }

	private function loadSectionFile($code = false) {
		$this->item['file'] = array();
		if($code) {
	        $query = "SELECT *
	                  FROM ".Config::db_table_section_file()."
	                  WHERE section_text_id = ".$this->get('text', 'section_text_id')." AND
					  		code = '".$code."'
					  ORDER BY rank ASC";
	        $this->item['file'] = $this->db->select($query);
	    } else {
	        $query = "SELECT *
	                  FROM ".Config::db_table_section_file()."
	                  WHERE section_text_id = ".$this->get('text', 'section_text_id')."
					  ORDER BY rank ASC";
	        $res = $this->db->select($query);
			foreach($res as $file) {
				$this->item['file'][$file['code']]['file'][] = $file['file'];
				$this->item['file'][$file['code']]['alt'][] = $file['alt'];
				$this->item['file'][$file['code']]['description'][] = $file['description'];
				$this->item['file'][$file['code']]['download'][] = $file['download'];
				$this->item['file'][$file['code']]['hash'][] = $file['hash'];
			}
		}
	}

    private function loadChildren($init = false) {
		$limit = isset($this->params['limit']) ? $this->params['limit'] : 0;
		$from = isset($this->params['from']) ? $this->params['from'] : 0;

    	$where = array();
    	$where[] = "t2.domain_id = ".$this->domain_id;
    	$where[] = "t1.parent_id = ".$this->section_id;
    	if(MODE != 'CMS') {
			$where[] = "t3.status = '1'";
    	}

		$LIMIT = "";
		if($limit) {
			$LIMIT.= " LIMIT ".$from.", ".$limit;
		}

        $query = "SELECT DISTINCT(t1.section_id) AS section_id
                  FROM ".Config::db_table_tree()." t1
                  LEFT JOIN ".Config::db_table_section()." t2 ON (t1.section_id = t2.section_id)
                  LEFT JOIN ".Config::db_table_section_text()." t3 ON (t1.section_id = t3.section_id AND t3.lang_id = ".$this->lang_id.")
                  WHERE ".implode(' AND ', $where)."
                  ORDER BY t1.rank ASC".$LIMIT;
        $children = $this->db->select($query);
        $return = array();
        foreach($children as $row) {
            if($init === false) {
                $return[$row['section_id']] = $row['section_id'];
            } else {
                $return[$row['section_id']] = new Section($row['section_id'], $this->domain_id, $this->lang_id, $this->section_id);
            }
        }
        $this->item['children'] = $return;
    }

	private function loadSiblings($count = 0) {
    	$where = array();
    	$where[] = "t2.domain_id = ".$this->domain_id;
    	$where[] = "t1.parent_id = ".$this->get('section', 'parent_id');
    	$where[] = "t1.section_id != ".$this->section_id;
    	if(MODE != 'CMS') {
			$where[] = "t3.status = '1'";
    	}

		$LIMIT = "";
		if($count) {
			$LIMIT.= " LIMIT 0, ".$count;
		}

        $query = "SELECT DISTINCT(t1.section_id) AS section_id
                  FROM ".Config::db_table_tree()." t1
                  LEFT JOIN ".Config::db_table_section()." t2 ON (t1.section_id = t2.section_id)
                  LEFT JOIN ".Config::db_table_section_text()." t3 ON (t1.section_id = t3.section_id AND t3.lang_id = ".$this->lang_id.")
                  WHERE ".implode(' AND ', $where)."
                  ORDER BY t1.rank ASC".$LIMIT;
        $result = $this->db->select($query);
        $return = array();
        foreach($result as $row) {
            $return[$row['section_id']] = Section::getInstance($row['section_id']);
        }
        $this->item['siblings'] = $return;
	}

	private function loadRelations($init = false) {
        $query = "SELECT parent_id, main
                  FROM ".Config::db_table_tree()."
                  WHERE section_id = ".$this->section_id."
                  ORDER BY parent_id ASC";
        $relations = $this->db->select($query);
        $return = array();
        foreach($relations as $row) {
            if($init === false) {
                $return[$row['parent_id']] = $row['parent_id'];
            } else {
            	$section = Section::getInstance($row['parent_id']);
            	$section->get('section');
            	$section->set($row['main'], 'section', 'main');
                $return[$row['parent_id']] = $section;
            }
        }
        $this->item['relations'] = $return;
	}

    private function loadPath() {
        $query = "SELECT";
        for($i = 1; $i <= $this->get('section', 'depth'); $i++) {
            $query.= " t".$i.".section_id AS level".$i.",";
        }
        $query = trim($query, ",");
        $query.= " FROM ".Config::db_table_tree()." t1";
        $query.= " LEFT JOIN ".Config::db_table_section()." s1 ON (t1.section_id = s1.section_id)";
        for($i = 2; $i <= $this->get('section', 'depth'); $i++) {
            $j = $i - 1;
            $query.= " LEFT JOIN ".Config::db_table_tree()." t".$i." ON (t".$i.".section_id = t".$j.".parent_id AND t".$i.".main = '1')";
        }
        $query.= " WHERE t1.section_id = ".$this->section_id." AND
                         t1.main = '1' AND
                         s1.domain_id = ".$this->domain_id;
        $path = $this->db->select($query, true);

        $return = array();
        foreach(array_reverse($path) as $section_id) {
            $return[$section_id] = Section::getInstance($section_id);
        }
        $this->item['path'] = $return;
    }

    private function loadUrl($ajax = false) {
        $return = "";
        $query = "SELECT st1.section_id, st1.url, st1.url_children
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
						s1.section_id IN (".implode(', ', array_filter(array_keys($this->get('path')))).") AND
						st1.lang_id = ".$this->lang_id." AND
                        t1.main = '1'
                  ORDER BY depth ASC";
        $result = $this->db->select($query);

        $url = "";
        foreach($result as $row) {
            $url.= $row['url']."/";
            if($row['section_id'] != $this->section_id && $row['url_children']) {
                $url = $row['url_children']."/";
            }
        }

        $return = Config::getVar('CURRENT_DOMAIN_URL');
        if(URL_LANG && $this->lang_id != $this->session->default_lang_id) {
            $return.= Config::getVar('CURRENT_LANG_CODE')."/";
        }
        if($ajax === true) {
			$return.= "ajax/";
		}
        $return.= ltrim($url, '/');

        $this->item['url'] = $return;
    }

	private function loadTitle() {
        $special_title = $this->getSpecialTitle(array_keys($this->get('path')));
        if($special_title != '') {
            $this->item['title'] = sprintf($special_title, $this->get('text', 'title'));
        } else {
            $this->item['title'] = $this->get('text', 'title');
        }
	}

    private function getSpecialTitle($path) {
        $special_title = '';
        foreach($path as $section_id) {
            if($section_id != $this->section_id) {
                $item = Section::getInstance($section_id);
                if($item->get('text', 'title_children') != "") {
                    $special_title = $item->get('text', 'title_children');
                }
            }
        }
        return $special_title;
    }

	private function loadVersion() {
		$return = array();
		$query = "SELECT section_text_id, name, inserted
				  FROM ".Config::db_table_section_text()."
				  WHERE section_id = ".$this->section_id." AND
				  		lang_id = ".$this->lang_id;
		$result = $this->db->select($query);
		foreach($result as $row) {
			$return[$row['section_text_id']] = $row;
		}
		$this->item['version'] = $return;
	}

	private function isVersion($version_id) {
		$query = "SELECT COUNT(*) AS cnt
				  FROM ".Config::db_table_section_text()."
				  WHERE section_id = ".$this->section_id." AND
				  		section_text_id = ".$version_id;
		return $this->db->select($query, true, 'cnt');
	}

    private function loadLog() {
        $query = "SELECT u.nickname, u.fname, u.lname, la.inserted
				  FROM ".Config::db_table_log_action()." la
				  LEFT JOIN ".Config::db_table_user()." u ON (la.user_id = u.user_id)
				  WHERE query LIKE 'UPDATE ".Config::db_table_section()." % WHERE section_id = ".$this->section_id."' AND
				  		lang_id = ".$this->lang_id."
				  ORDER BY la.inserted DESC";
        $return = $this->db->select($query);
        $this->item['log'] = $return;
    }

	private function loadPrevious($init) {
		$query = "SELECT section_id
				  FROM ".Config::db_table_tree()."
				  WHERE rank < ".$this->get('section', 'rank')." AND
				  		parent_id = ".$this->get('section', 'parent_id')."
				  ORDER BY rank DESC
				  LIMIT 0, 1";
		$previous = $this->db->select($query, true, "section_id");
		if($init === true && $previous) {
			$this->item['previous'] = Section::getInstance($previous);
		} elseif($previous) {
			$this->item['previous'] = $previous;
		} else {
            $this->item['previous'] = null;
        }
	}

	private function loadNext($init) {
		$query = "SELECT section_id
				  FROM ".Config::db_table_tree()."
				  WHERE rank > ".$this->get('section', 'rank')." AND
				  		parent_id = ".$this->get('section', 'parent_id')."
				  ORDER BY rank ASC
				  LIMIT 0, 1";
		$next = $this->db->select($query, true, "section_id");
		if($init === true && $next) {
			$this->item['next'] = Section::getInstance($next);
		} elseif($next) {
			$this->item['next'] = $next;
		} else {
            $this->item['next'] = null;
        }
	}

	private function loadForum() {
		$forum = new Forum();
		//pridat page a perpage
		$this->item['forum'] = $forum->getForum($this->section_id);
	}

    private function treefix($parentId = 1) {
        $query = "SELECT * FROM cms_tree WHERE main = '1' AND section_id = ".$parentId;
        $section = $this->db->select($query, true);
        $query = "SELECT * FROM cms_tree WHERE parent_id = ".$parentId." ORDER BY rank ASC";
        $children = $this->db->select($query);
        $i = 1;
        foreach($children as $kid) {
            $query = "UPDATE cms_tree SET depth = ".($section['depth']+1).", rank = ".$i." WHERE section_id = ".$kid['section_id']." AND parent_id = ".$parentId;
            $this->db->update($query);
            $this->treefix($kid['section_id']);
            $i++;
        }
    }
}
