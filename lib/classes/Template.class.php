<?php

class Template extends ItemDomain {

    protected $item = "template_id";
    protected $cols = array(
		'name' => "",
        'code' => "",
        'content' => "",
        'children' => "",
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_template();
    }

    public function load() {
        parent::load();
        $query = "SELECT *
                  FROM ".Config::db_table_template_tab()."
                  WHERE ".$this->item." = ".$this->item_id;
        $tabs = $this->db->select($query);
        $this->data['tab'] = array();
        foreach($tabs as $row) {
            $this->data['tab'][$row['template_tab_id']]['name'] = $row['name'];
			$query = "SELECT tc.template_tab_column_id, tc.rank, c.*
					  FROM ".Config::db_table_template_tab_column()." tc
					  LEFT JOIN ".Config::db_table_column()." c ON (c.column_id = tc.column_id)
					  WHERE template_tab_id = ".$row['template_tab_id']."
					  ORDER BY rank ASC";
			$cols = $this->db->select($query);
            foreach($cols as $col) {
				$this->data['tab'][$row['template_tab_id']]['column'][$col['column_id']] = $col;
			}
        }
    }

    public function delete() {
        parent::delete();
        $query = "SELECT template_tab_id
				  FROM ".Config::db_table_template_tab()."
				  WHERE ".$this->item." = ".$this->item_id;
		$tabs = $this->db->select($query);
		$tab_ids = array();
		foreach($tabs as $row) {
			$this->deleteTab($row['template_tab_id']);
		}
    }

	public function addTab($name) {
		$this->db->insert("INSERT INTO ".Config::db_table_template_tab()." (template_id, name) VALUES (".$this->item_id.", '".$name."')");
	}

	public function deleteTab($tab_id) {
        $affected = $this->db->delete("DELETE FROM ".Config::db_table_template_tab()." WHERE template_id = ".$this->item_id." AND template_tab_id = ".$tab_id);
        if($affected > 0) {
			$this->db->delete("DELETE FROM ".Config::db_table_template_tab_column()." WHERE template_tab_id = ".$tab_id);
		}
	}

	public function hasCol($col_id, $tab_id) {
		$query = "SELECT COUNT(*) AS cnt
				  FROM ".Config::db_table_template_tab_column()."
				  WHERE template_tab_id = ".$tab_id." AND
				  		column_id = ".$col_id;
		return $this->db->select($query, true, 'cnt');
	}

	public function addCol($col_id, $tab_id) {
		$query = "SELECT MAX(rank) AS rank
				  FROM ".Config::db_table_template_tab_column()."
				  WHERE template_tab_id = ".$tab_id;
		$rank = (int) $this->db->select($query, true, 'rank');

		$query = "INSERT INTO ".Config::db_table_template_tab_column()."
				  (template_tab_id, column_id, rank)
				  VALUES
				  (".$tab_id.", ".$col_id.", ".($rank+1).")";
		$this->db->insert($query);
	}

	public function deleteCol($tcid) {
		$query = "SELECT template_tab_id, rank
				  FROM ".Config::db_table_template_tab_column()."
				  WHERE template_tab_column_id = ".$tcid;
		$res = $this->db->select($query, true);

		$query = "UPDATE ".Config::db_table_template_tab_column()." SET
					rank = rank - 1
				  WHERE template_tab_id = ".$res['template_tab_id']." AND
				  		rank > ".$res['rank'];
		$this->db->update($query);

		$this->db->delete("DELETE FROM ".Config::db_table_template_tab_column()." WHERE template_tab_column_id = ".$tcid);
	}

}
