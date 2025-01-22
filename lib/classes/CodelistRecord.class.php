<?php

class CodelistRecord {

	private $data = array();

	public function __construct($id = 0) {
		$this->id = $id;
		$this->db = Database::connect();
		$this->session = Session::getInstance(MODE);
	}

	public function get() {
		$arg_num = func_num_args();
		if($arg_num > 0) {
			$arg_list = func_get_args();
			if(!isset($this->data[$arg_list[0]])) {
				switch($arg_list[0]) {
					case "item": $this->loadItem(); break;
					case "file": $this->loadFile(); break;
					case "value": $this->loadValue(); break;
					default: return false;
				}
			}
			if(isset($arg_list[1])) {
				if(isset($this->data[$arg_list[0]][$arg_list[1]])) {
					return $this->data[$arg_list[0]][$arg_list[1]];
				} else {
					return false;
				}
			} else {
				return $this->data[$arg_list[0]];
			}
		}
	}

    public function save($data) {
    	$this->codelist_id = $data['codelist_id'];
        if(!isset($this->data['item']) || $this->data['item'] != $data['item']) {
            $this->setItem($data['item']);
        }
		if(isset($data['value'])) {
			$this->setValue($data['value']);
		}
		if(isset($data['file'])) {
			$this->setFile($data['file']);
		}
    }

	public function delete() {
		$return = false;
		$query = "SELECT ct.codelist_id, cr.code
				  FROM ".Config::db_table_codelist_record()." cr
				  LEFT JOIN ".Config::db_table_codelist_text()." ct ON (ct.codelist_text_id = cr.codelist_text_id)
				  WHERE cr.codelist_record_id = ".$this->id;
		$result1 = $this->db->select($query, true);

		$query = "SELECT codelist_text_id
				  FROM ".Config::db_table_codelist_text()."
				  WHERE codelist_id = ".$result1['codelist_id'];
		$result2 = $this->db->select($query);

		foreach($result2 as $row) {
			$query = "SELECT codelist_record_id, rank
					  FROM ".Config::db_table_codelist_record()."
					  WHERE codelist_text_id = ".$row['codelist_text_id']." AND
					  		code = '".$result1['code']."'";
			$result3 = $this->db->select($query, true);
			if($result3['codelist_record_id']) {
				$return = true;
				$this->db->update("UPDATE ".Config::db_table_codelist_record()." SET rank=rank-1 WHERE codelist_text_id = ".$row['codelist_text_id']." AND rank > ".$result3['rank']);
				$this->db->delete("DELETE FROM ".Config::db_table_codelist_record()." WHERE codelist_record_id = ".$result3['codelist_record_id']);
				$this->db->delete("DELETE FROM ".Config::db_table_codelist_record_file()." WHERE codelist_record_id = ".$result3['codelist_record_id']);
				$this->db->delete("DELETE FROM ".Config::db_table_codelist_record_value()." WHERE codelist_record_id = ".$result3['codelist_record_id']);
			}
		}

		return $return;
	}

    private function setItem($data) {
        if($this->id) {
            $query = "UPDATE ".Config::db_table_codelist_record()." SET
                        code = '".mysqli_real_escape_string(MySQL::$conn, $data['code'])."',
                        name = '".mysqli_real_escape_string(MySQL::$conn, $data['name'])."'
                      WHERE codelist_record_id = ".$this->id;
            $this->db->update($query);
        } else {
			foreach($data['name'] as $lang_id => $name) {
				$query = "SELECT codelist_text_id
						  FROM ".Config::db_table_codelist_text()."
						  WHERE codelist_id = ".$this->codelist_id." AND
						  		lang_id = ".$lang_id;
				$codelist_text_id = $this->db->select($query, true, "codelist_text_id");

				$query = "SELECT COUNT(*)+1 AS rnk
						  FROM ".Config::db_table_codelist_record()."
						  WHERE codelist_text_id = ".$codelist_text_id;
				$rank = $this->db->select($query, true, "rnk");

	            $query = "INSERT INTO ".Config::db_table_codelist_record()."
	                      (codelist_text_id, code, name, rank, inserted)
	                      VALUES
	                      ('".$codelist_text_id."', '".mysqli_real_escape_string(MySQL::$conn, $data['code'])."', '".mysqli_real_escape_string(MySQL::$conn, $name)."', ".$rank.", NOW())";
	            $this->id = $this->db->insert($query);
			}
        }
    }

	private function setValue($data) {
		$config = new Config();

		foreach($config->getCodelistCols($this->codelist_id) as $col) {
	        $classname = "Type".ucfirst(strtolower($col['item']['type']));
	        if(class_exists($classname)) {
	            $class = new $classname($this, $col);
	        } else {
	            $class = TypeDefault($this, $col);
	        }
			$field = $class->field;
			if($field) {
				$query = "DELETE FROM ".Config::db_table_codelist_record_value()."
						  WHERE codelist_record_id = ".$this->id." AND
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
						$query = "INSERT INTO ".Config::db_table_codelist_record_value()."
								  (user_id, code, ".$field."_val)
								  VALUES
								  (".$this->id.", '".mysqli_real_escape_string(MySQL::$conn, $col['item']['code'])."', '".mysqli_real_escape_string(MySQL::$conn, stripslashes($v))."')";
						$this->db->insert($query);
					}
				} else {
					if($field == 'datetime' && isset($data[$col['item']['code']])) {
						$datetime = new DateTime($value, new DateTimeZone(Config::getVar('USER_TIMEZONE')));
						$datetime->setTimezone(new DateTimeZone(DEFAULT_TIMEZONE));
						$value = $datetime->format('Y-m-d H:i:s');
					}
					$query = "INSERT INTO ".Config::db_table_codelist_record_value()."
							  (codelist_record_id, code, ".$field."_val)
							  VALUES
							  (".$this->id.", '".mysqli_real_escape_string(MySQL::$conn, $col['item']['code'])."', '".mysqli_real_escape_string(MySQL::$conn, stripslashes($value))."')";
					$this->db->insert($query);
				}
			}
		}
	}

	private function setFile($data) {
		foreach($data as $code => $value) {
			$query = "DELETE FROM ".Config::db_table_codelist_record_file()."
					  WHERE codelist_record_id = ".$this->id." AND
					  		code = '".$code."'";
			$this->db->delete($query);
			$rank = 1;
			foreach($value['file'] as $k => $file) {
				if(!empty($file)) {
					$query = "INSERT INTO ".Config::db_table_codelist_record_file()."
							  (codelist_record_id, file, hash, code, alt, description, download, rank)
							  VALUES
							  (".$this->id.", '".$file."', '".$value['hash'][$k]."', '".$code."', '".$value['alt'][$k]."', '".$value['description'][$k]."', '".$value['download'][$k]."', ".($rank++).")";
					$this->db->insert($query);
				}
			}
		}
	}

	private function loadItem() {
		$query = "SELECT *
				  FROM ".Config::db_table_codelist_record()."
				  WHERE codelist_record_id = ".$this->id;
		$this->data['item'] = $this->db->select($query, true);
	}

	private function loadFile() {
		$this->data['file'] = array();

        $query = "SELECT *
                  FROM ".Config::db_table_codelist_record_file()."
                  WHERE codelist_record_id = ".$this->id."
				  ORDER BY rank ASC";
        $res = $this->db->select($query);
		foreach($res as $k => $file) {
			$this->data['file'][$file['code']][$k]['file'] = $file['file'];
            $this->data['file'][$file['code']][$k]['hash'] = $file['hash'];
			$this->data['file'][$file['code']][$k]['alt'] = $file['alt'];
			$this->data['file'][$file['code']][$k]['description'] = $file['description'];
			$this->data['file'][$file['code']][$k]['download'] = $file['download'];
		}
	}

	private function loadValue() {
	    $query = "SELECT *, (SELECT COUNT(*)
							 FROM ".Config::db_table_codelist_record_value()." t2
							 WHERE codelist_record_id = ".$this->id." AND
							 	   t2.code=t1.code) AS cnt
	              FROM ".Config::db_table_codelist_record_value()." t1
	              WHERE codelist_record_id = ".$this->id;
	    $res = $this->db->select($query);

	    $this->data['value'] = array();
	    foreach($res as $row) {
			if(!is_null($row['int_val'])) {
				if($row['cnt'] > 1) {
					$this->data['value'][$row['code']][] = $row['int_val'];
				} else {
					$this->data['value'][$row['code']] = $row['int_val'];
				}
			}
			if(!is_null($row['varchar_val'])) {
				if($row['cnt'] > 1) {
					$this->data['value'][$row['code']][] = $row['varchar_val'];
				} else {
					$this->data['value'][$row['code']] = $row['varchar_val'];
				}
			}
			if(!is_null($row['text_val'])) {
				if($row['cnt'] > 1) {
					$this->data['value'][$row['code']][] = $row['text_val'];
				} else {
					$this->data['value'][$row['code']] = $row['text_val'];
				}
			}
			if(!is_null($row['datetime_val'])) {
				if($row['cnt'] > 1) {
					$this->data['value'][$row['code']][] = $row['datetime_val'];
				} else {
					$this->data['value'][$row['code']] = $row['datetime_val'];
				}
			}
		}
	}

}
