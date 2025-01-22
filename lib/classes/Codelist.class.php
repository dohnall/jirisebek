<?php

class Codelist extends ItemDomain {

    protected $item = "codelist_id";
    protected $cols = array(
		'code' => "",
    );
    private $table_item = "";

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_codelist();
        $this->lang_id = isset($this->session->codelist_lang) ? $this->session->codelist_lang : $this->session->lang_id;
    }

    public function load() {
        $query = "SELECT t1.*, t2.name
                  FROM ".$this->table." t1
                  LEFT JOIN ".Config::db_table_codelist_text()." t2 ON (t1.".$this->item." = t2.".$this->item." AND t2.lang_id = ".$this->lang_id.")
				  WHERE domain_id=".$this->session->domain_id." AND
				  		t1.".$this->item." = ".$this->item_id;
        $this->data['item'] = $this->db->select($query, true);

        $this->data['names'] = array();
		$query = "SELECT lang_id, name
				  FROM ".Config::db_table_codelist_text()."
				  WHERE codelist_id = ".$this->item_id;
        $result = $this->db->select($query);
        foreach($result as $row) {
			$this->data['names'][$row['lang_id']] = $row['name'];
		}

        $this->data['column'] = array();
		$query = "SELECT cc.codelist_column_id, cc.rank, c.*
				  FROM ".Config::db_table_codelist_column()." cc
				  LEFT JOIN ".Config::db_table_column()." c ON (c.column_id = cc.column_id)
				  WHERE cc.codelist_id = ".$this->item_id."
				  ORDER BY cc.rank ASC";
		$cols = $this->db->select($query);
        foreach($cols as $col) {
			$this->data['column'][$col['column_id']] = $col;
		}
    }

    public function delete() {
        parent::delete();
		$query = "DELETE FROM ".Config::db_table_codelist_text()." WHERE ".$this->item." = ".$this->item_id;
		$this->db->delete($query);
		$query = "DELETE FROM ".Config::db_table_codelist_column()." WHERE ".$this->item." = ".$this->item_id;
		$this->db->delete($query);
    }

	public function hasCol($col_id) {
		$query = "SELECT COUNT(*) AS cnt
				  FROM ".Config::db_table_codelist_column()."
				  WHERE codelist_id = ".$this->item_id." AND
				  		column_id = ".$col_id;
		return $this->db->select($query, true, 'cnt');
	}

	public function addCol($col_id) {
		$query = "SELECT MAX(rank) AS rank
				  FROM ".Config::db_table_codelist_column()."
				  WHERE codelist_id = ".$this->item_id;
		$rank = (int) $this->db->select($query, true, 'rank');

		$query = "INSERT INTO ".Config::db_table_codelist_column()."
				  (codelist_id, column_id, rank)
				  VALUES
				  (".$this->item_id.", ".$col_id.", ".($rank+1).")";
		$this->db->insert($query);
	}

	public function deleteCol($ccid) {
		$query = "SELECT rank
				  FROM ".Config::db_table_codelist_column()."
				  WHERE codelist_column_id = ".$ccid;
		$rank = $this->db->select($query, true, "rank");

		$query = "UPDATE ".Config::db_table_codelist_column()." SET
					rank = rank - 1
				  WHERE codelist_id = ".$this->item_id." AND
				  		rank > ".$rank;
		$this->db->update($query);

		$this->db->delete("DELETE FROM ".Config::db_table_codelist_column()." WHERE codelist_column_id = ".$ccid);
	}

	public function getRecords($code = "") {
		$where = "";
		if($code) {
			$where = " AND cr.code = '".mysqli_real_escape_string(MySQL::$conn, $code)."'";
		}
		$query = "SELECT cr.codelist_record_id
				  FROM ".Config::db_table_codelist_record()." cr
				  LEFT JOIN ".Config::db_table_codelist_text()." ct ON (ct.codelist_text_id = cr.codelist_text_id)
				  WHERE ct.codelist_id = ".$this->item_id." AND
				  		ct.lang_id = ".$this->lang_id.$where."
				  ORDER BY cr.rank ASC";
		if($code) {
			$codelist_record_id = $this->db->select($query, true, "codelist_record_id");
			if($codelist_record_id) {
				return new CodelistRecord($codelist_record_id);
			} else {
				return false;
			}
		} else {
			$result = $this->db->select($query);
			$return = array();
			foreach($result as $row) {
				$return[$row['codelist_record_id']] = new CodelistRecord($row['codelist_record_id']);
			}
			return $return;
		}
	}

	protected function insert($data) {
		parent::insert($data);
		
		foreach($data['item']['name'] as $lang_id => $name) {
			$query = "INSERT INTO ".Config::db_table_codelist_text()."
					  (codelist_id, lang_id, name)
					  VALUES
					  (".$this->item_id.", ".$lang_id.", '".mysqli_real_escape_string(MySQL::$conn, $name)."')";
			$this->db->insert($query);
		}
	}

	protected function update($data) {
		parent::update($data);

		foreach($data['item']['name'] as $lang_id => $name) {
            $query = "SELECT COUNT(*) AS cnt
                      FROM ".Config::db_table_codelist_text()."
                      WHERE ".$this->item." = ".$this->item_id." AND
                            lang_id = ".$lang_id;
            $cnt = $this->db->select($query, true, 'cnt');
            if($cnt) {
                $query = "UPDATE ".Config::db_table_codelist_text()." SET
					  name = '".mysqli_real_escape_string(MySQL::$conn, $name)."'
					  WHERE ".$this->item." = ".$this->item_id." AND
					  		lang_id = ".$lang_id;
                $this->db->update($query);
            } else {
                $query = "INSERT INTO ".Config::db_table_codelist_text()."
                        (codelist_id, lang_id, name)
                        VALUES
                        (".$this->item_id.", ".$lang_id.", '".mysqli_real_escape_string(MySQL::$conn, $name)."')";
                $this->db->insert($query);
            }

		}
	}

}
