<?php
class User {

    private $user_id = 0;
    private $data = array();

    public function __construct($user_id = 0) {
        $this->user_id = $user_id;
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
    }

    public function __get($key) {
        if(isset($this->data['user'][$key])) {
            return $this->data['user'][$key];
        } else {
            return null;
        }
    }

    public function get($type, $sec=false, $force=false) {
        if(!isset($this->data[$type])) {
            switch($type) {
				case 'value': $this->data['value'] = $this->getValue(); break;
				case 'file': $this->data['file'] = $this->getFile($sec); break;
                default: return false;
            }
        }
        if($type == 'value' && $sec !== false) {
	        if(isset($this->data[$type][$sec])) {
				return $this->data[$type][$sec];
			} else {
				return null;
			}
        } else {
            return $this->data[$type];
        }
    }

    public function load() {
        $this->data['user'] = $this->getUser();
        $this->data['domain'] = $this->getDomain();
        if(!isset($this->session->domain_id) || !$this->hasDomain($this->session->domain_id)) {
            $domains = array_keys($this->data['domain']);
            if($domains) {
                $this->session->domain_id = $domains[0];
                $domain = new Domain($this->session->domain_id);
                $this->session->default_lang_id = $domain->getDefaultLang();
            }
        }

        if(isset($this->session->domain_id)) {
            $this->data['group'] = $this->getGroup();
            $this->data['lang'] = $this->getLang();
            $this->data['module'] = $this->getModule();
            $this->data['section'] = $this->getSection();
            $this->data['right'] = $this->getRight();
            $this->data['value'] = $this->get('value');
        }
    }

    public function getData($key = "") {
        if($key && isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return $this->data;
        }
    }

    public function save($data) {
        if(!isset($this->data['user']) || $this->data['user'] != $data['user']) {
            $this->setUser($data['user']);
        }
		if(isset($data['value'])) {
			$this->setValue($data['value']);
		}
		if(isset($data['file'])) {
			$this->setFile($data['file']);
		}
        if(!isset($this->data['domain']) || $this->data['domain'] != $data['domain']) {
            $this->setDomain($data['domain']);
        }
        if(!isset($this->data['group']) || $this->data['group'] != $data['group']) {
            $this->setGroup($data['group']);
        }
		if(MODE == 'CMS') {
	        if(!isset($this->data['lang']) || $this->data['lang'] != $data['lang']) {
	            $this->setLang($data['lang']);
	        }
	        if(!isset($this->data['module']) || $this->data['module'] != $data['module']) {
	            $this->setModule($data['module']);
	        }
	        if(!isset($this->data['section']) || $this->data['section'] != $data['section']) {
	            $this->setSection($data['section']);
	        }
		}
    }

    public function delete() {
        $query = "UPDATE ".Config::db_table_user()." SET
                    deleted = '1'
                  WHERE user_id = ".$this->user_id;
        $this->db->update($query);
    }

    public function hasDomain($domain_id) {
        return isset($this->data['domain'][$domain_id]);
    }

    public function hasGroup($group_id, $domain_id = 0) {
        $domain_id = $domain_id === 0 ? $this->session->domain_id : $domain_id;
        if(isset($this->data['group'][$domain_id])) {
            return $this->data['group'][$domain_id] == $group_id;
        } else {
            return false;
        }
    }

    public function hasLang($lang_id, $domain_id = 0) {
        $domain_id = $domain_id === 0 ? $this->session->domain_id : $domain_id;
        return isset($this->data['lang'][$domain_id][$lang_id]);
    }

    public function hasModule($code, $domain_id = 0) {
        $domain_id = $domain_id === 0 ? $this->session->domain_id : $domain_id;
        return isset($this->data['module'][$domain_id][$code]);
    }

    public function hasSection($section_id, $domain_id = 0, $lang_id = 0) {
        $domain_id = $domain_id === 0 ? $this->session->domain_id : $domain_id;
        $lang_id = $lang_id === 0 ? $this->session->lang_id : $lang_id;
        if(is_numeric($section_id) && isset($this->data['section'][$domain_id])) {
            $section = Section::getInstance($section_id, $domain_id, $lang_id);
            $diff = array_diff($this->data['section'][$domain_id], array_keys($section->get('path')));
            if(count($diff) < count($this->data['section'][$domain_id])) {
                return true;
            }
        }
        return false;
    }

    public function hasRight($right_id) {
        return isset($this->data['right'][$right_id]);
    }

    public function setAction() {
        $query = "UPDATE ".Config::db_table_user()." SET
        			last_action = NOW()
                  WHERE user_id = ".$this->user_id;
        $this->db->update($query);
    }

    private function getUser() {
        $query = "SELECT *
                  FROM ".Config::db_table_user()."
                  WHERE user_id = ".$this->user_id;
        return $this->db->select($query, true);
    }

    private function getValue() {
	    $query = "SELECT *, (SELECT COUNT(*)
							 FROM ".Config::db_table_user_value()." t2
							 WHERE user_id = ".$this->user_id." AND
							 	   t2.code=t1.code) AS cnt
	              FROM ".Config::db_table_user_value()." t1
	              WHERE user_id = ".$this->user_id;
	    $res = $this->db->select($query);
	    
	    $user_values = array();
	    foreach($res as $row) {
			if(!is_null($row['int_val'])) {
				if($row['cnt'] > 1) {
					$user_values[$row['code']][] = $row['int_val'];
				} else {
					$user_values[$row['code']] = $row['int_val'];
				}
			}
			if(!is_null($row['varchar_val'])) {
				if($row['cnt'] > 1) {
					$user_values[$row['code']][] = $row['varchar_val'];
				} else {
					$user_values[$row['code']] = $row['varchar_val'];
				}
			}
			if(!is_null($row['text_val'])) {
				if($row['cnt'] > 1) {
					$user_values[$row['code']][] = $row['text_val'];
				} else {
					$user_values[$row['code']] = $row['text_val'];
				}
			}
			if(!is_null($row['datetime_val'])) {
				if($row['cnt'] > 1) {
					$user_values[$row['code']][] = $row['datetime_val'];
				} else {
					$user_values[$row['code']] = $row['datetime_val'];
				}
			}
		}
        return $user_values;
    }

	private function getFile($code = false) {
		$return = array();
		if($code) {
	        $query = "SELECT *
	                  FROM ".Config::db_table_user_file()."
	                  WHERE user_id = ".$this->user_id." AND
					  		code = '".$code."'
					  ORDER BY rank ASC";
	        $return = $this->db->select($query);
	    } else {
	        $query = "SELECT *
	                  FROM ".Config::db_table_user_file()."
	                  WHERE user_id = ".$this->user_id."
					  ORDER BY rank ASC";
	        $res = $this->db->select($query);
			foreach($res as $file) {
				$return[$file['code']]['file'][] = $file['file'];
				$return[$file['code']]['alt'][] = $file['alt'];
				$return[$file['code']]['description'][] = $file['description'];
			}
		}
		return $return;
	}

    private function getDomain() {
        $return = array();
        $query = "SELECT domain_id
                  FROM ".Config::db_table_user_domain()."
                  WHERE user_id = ".$this->user_id;
        $data = $this->db->select($query);
        foreach($data as $row) {
            $return[$row['domain_id']] = $row['domain_id'];
        }
        return $return;
    }

    private function getGroup() {
        $return = array();
        $query = "SELECT domain_id, group_id
                  FROM ".Config::db_table_user_group()."
                  WHERE user_id = ".$this->user_id;
        $data = $this->db->select($query);
        foreach($data as $row) {
            $return[$row['domain_id']] = $row['group_id'];
        }
        return $return;
    }

    private function getLang() {
        $return = array();
        $query = "SELECT domain_id, lang_id
                  FROM ".Config::db_table_user_lang()."
                  WHERE user_id = ".$this->user_id;
        $data = $this->db->select($query);
        foreach($data as $row) {
            $return[$row['domain_id']][$row['lang_id']] = $row['lang_id'];
        }
        return $return;
    }

    private function getModule() {
        $return = array();
        $query = "SELECT am.domain_id, am.module_id, m.code
                  FROM ".Config::db_table_user_module()." AS am
                  LEFT JOIN ".Config::db_table_module()." AS m ON (am.module_id = m.module_id)
                  WHERE user_id = ".$this->user_id;
        $data = $this->db->select($query);
        foreach($data as $row) {
            $return[$row['domain_id']][$row['code']] = $row['module_id'];
        }
        return $return;
    }

    private function getSection() {
        $return = array();
        $query = "SELECT domain_id, section_id
                  FROM ".Config::db_table_user_section()."
                  WHERE user_id = ".$this->user_id;
        $data = $this->db->select($query);
        foreach($data as $row) {
            $return[$row['domain_id']][$row['section_id']] = $row['section_id'];
        }
        return $return;
    }

    private function getRight() {
        $return = array();
        if(isset($this->data['group'][$this->session->domain_id])) {
            $query = "SELECT right_id
                      FROM ".Config::db_table_group_right()."
                      WHERE group_id = ".$this->data['group'][$this->session->domain_id];
            $data = $this->db->select($query);
            foreach($data as $row) {
                $return[$row['right_id']] = $row['right_id'];
            }
        }
        return $return;
    }

    private function setUser($data) {
    	$query = "SELECT user_id
				  FROM ".Config::db_table_user()."
				  WHERE email = '".$data['email']."' AND
				  		deleted = '1'";
		$user_id = $this->db->select($query, true, "user_id");

        if($this->user_id) {
			if($user_id) {
				$this->delete();
	            $query = "UPDATE ".Config::db_table_user()." SET
	                        nickname = '".$data['nickname']."',
	                        passwd = '".$data['passwd']."',
	                        admin = '".$data['admin']."',
	                        fname = '".$data['fname']."',
	                        lname = '".$data['lname']."',
	                        email = '".$data['email']."',
	                        cmslang = '".$data['cmslang']."',
	                        timezone = '".$data['timezone']."',
	                        status = ".$data['status'].",
	                        deleted = '0'
	                      WHERE user_id = ".$user_id;
	            $this->db->update($query);
	            $this->user_id = $user_id;
	            $this->load();
			} else {
	            $query = "UPDATE ".Config::db_table_user()." SET
	                        nickname = '".$data['nickname']."',
	                        passwd = '".$data['passwd']."',
	                        admin = '".$data['admin']."',
	                        fname = '".$data['fname']."',
	                        lname = '".$data['lname']."',
	                        email = '".$data['email']."',
	                        cmslang = '".$data['cmslang']."',
	                        timezone = '".$data['timezone']."',
	                        status = ".$data['status']."
	                      WHERE user_id = ".$this->user_id;
	            $this->db->update($query);
			}
        } else {
			if($user_id) {
	            $query = "UPDATE ".Config::db_table_user()." SET
	                        nickname = '".$data['nickname']."',
	                        passwd = '".$data['passwd']."',
	                        admin = '".$data['admin']."',
	                        fname = '".$data['fname']."',
	                        lname = '".$data['lname']."',
	                        email = '".$data['email']."',
	                        cmslang = '".$data['cmslang']."',
	                        timezone = '".$data['timezone']."',
	                        status = ".$data['status'].",
	                        deleted = '0'
	                      WHERE user_id = ".$user_id;
	            $this->db->update($query);
	            $this->user_id = $user_id;
			} else {
	            $query = "INSERT INTO ".Config::db_table_user()."
	                      (nickname, passwd, admin, fname, lname,
	                       email, cmslang, timezone, inserted, status)
	                      VALUES
	                      ('".$data['nickname']."', '".$data['passwd']."', '".$data['admin']."', '".$data['fname']."', '".$data['lname']."',
	                       '".$data['email']."', '".$data['cmslang']."', '".$data['timezone']."', NOW(), ".$data['status'].")";
	            $this->user_id = $this->db->insert($query);
			}
        }
    }

    private function setDomain($data) {
        $query = "DELETE FROM ".Config::db_table_user_domain()."
                  WHERE user_id = ".$this->user_id;
        $this->db->delete($query);
        foreach($data as $row) {
            $query = "INSERT INTO ".Config::db_table_user_domain()."
                      (user_id, domain_id)
                      VALUES
                      (".$this->user_id.", ".$row.")";
            $this->db->insert($query);
        }
    }

    private function setGroup($data) {
        $query = "DELETE FROM ".Config::db_table_user_group()."
                  WHERE user_id = ".$this->user_id;
        $this->db->delete($query);
        foreach($data as $domain_id => $group_id) {
            $query = "INSERT INTO ".Config::db_table_user_group()."
                      (user_id, domain_id, group_id)
                      VALUES
                      (".$this->user_id.", ".$domain_id.", ".$group_id.")";
            $this->db->insert($query);
        }
    }

    private function setLang($data) {
        $query = "DELETE FROM ".Config::db_table_user_lang()."
                  WHERE user_id = ".$this->user_id;
        $this->db->delete($query);
        foreach($data as $domain_id => $domain) {
            foreach($domain as $lang_id) {
                $query = "INSERT INTO ".Config::db_table_user_lang()."
                          (user_id, domain_id, lang_id)
                          VALUES
                          (".$this->user_id.", ".$domain_id.", ".$lang_id.")";
                $this->db->insert($query);
            }
        }
    }

    private function setModule($data) {
        $query = "DELETE FROM ".Config::db_table_user_module()."
                  WHERE user_id = ".$this->user_id;
        $this->db->delete($query);
        foreach($data as $domain_id => $domain) {
            foreach($domain as $module_id) {
                $query = "INSERT INTO ".Config::db_table_user_module()."
                          (user_id, domain_id, module_id)
                          VALUES
                          (".$this->user_id.", ".$domain_id.", ".$module_id.")";
                $this->db->insert($query);
            }
        }
    }

    private function setSection($data) {
        $query = "DELETE FROM ".Config::db_table_user_section()."
                  WHERE user_id = ".$this->user_id;
        $this->db->delete($query);
        foreach($data as $domain_id => $domain) {
            foreach($domain as $section_id) {
                $query = "INSERT INTO ".Config::db_table_user_section()."
                          (user_id, domain_id, section_id)
                          VALUES
                          (".$this->user_id.", ".$domain_id.", ".$section_id.")";
                $this->db->insert($query);
            }
        }
    }

	private function setValue($data) {
		$config = new Config();

		foreach($config->getUserCols() as $col) {
	        $classname = "Type".ucfirst(strtolower($col['item']['type']));
	        if(class_exists($classname)) {
	            $class = new $classname($this, $col);
	        } else {
	            $class = TypeDefault($this, $col);
	        }
			$field = $class->field;

			if($field) {
				$query = "DELETE FROM ".Config::db_table_user_value()."
						  WHERE user_id = ".$this->user_id." AND
						  		code = '".$col['item']['code']."'";
				$this->db->delete($query);

				$value = isset($data[$col['item']['code']]) ? $data[$col['item']['code']] : "";

				if(is_array($value)) {
					foreach($value as $v) {
						if($field == 'datetime' && isset($data[$col['item']['code']])) {
							$datetime = new DateTime($v, new DateTimeZone(Config::getVar('USER_TIMEZONE')));
							$datetime->setTimezone(new DateTimeZone(DEFAULT_TIMEZONE));
							$v = $datetime->format('Y-m-d H:i:s');
						}
						$query = "INSERT INTO ".Config::db_table_user_value()."
								  (user_id, code, ".$field."_val)
								  VALUES
								  (".$this->user_id.", '".$col['item']['code']."', '".$v."')";
						$this->db->insert($query);
					}
				} else {
					if($field == 'datetime' && isset($data[$col['item']['code']])) {
						$datetime = new DateTime($value, new DateTimeZone(Config::getVar('USER_TIMEZONE')));
						$datetime->setTimezone(new DateTimeZone(DEFAULT_TIMEZONE));
						$value = $datetime->format('Y-m-d H:i:s');
					}
					$query = "INSERT INTO ".Config::db_table_user_value()."
							  (user_id, code, ".$field."_val)
							  VALUES
							  (".$this->user_id.", '".$col['item']['code']."', '".$value."')";
					$this->db->insert($query);
				}
			}
		}
	}

	private function setFile($data) {
		foreach($data as $code => $value) {
			$query = "DELETE FROM ".Config::db_table_user_file()."
					  WHERE user_id = ".$this->user_id." AND
					  		code = '".$code."'";
			$this->db->delete($query);
			$rank = 1;
			foreach($value['file'] as $k => $file) {
				if(!empty($file)) {
					$query = "INSERT INTO ".Config::db_table_user_file()."
							  (user_id, file, code, alt, description, rank)
							  VALUES
							  (".$this->user_id.", '".$file."', '".$code."', '".$value['alt'][$k]."', '".$value['description'][$k]."', ".($rank++).")";
					$this->db->insert($query);
				}
			}
		}
	}
}
