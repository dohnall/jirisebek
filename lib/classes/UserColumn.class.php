<?php

class UserColumn {

	private $data = array();

    public function __construct() {
    	$this->db = Database::connect();
        $this->table = Config::db_table_user_column();
    }

    public function load() {
		$query = "SELECT uc.user_column_id, uc.rank, c.*
				  FROM ".Config::db_table_user_column()." uc
				  LEFT JOIN ".Config::db_table_column()." c ON (c.column_id = uc.column_id)
				  ORDER BY rank ASC";
		$cols = $this->db->select($query);
        foreach($cols as $col) {
			$this->data[$col['column_id']] = $col;
		}
    }

    public function get() {
        return $this->data;
    }

	public function has($col_id) {
		$query = "SELECT COUNT(*) AS cnt
				  FROM ".Config::db_table_user_column()."
				  WHERE column_id = ".$col_id;
		return $this->db->select($query, true, 'cnt');
	}

	public function add($col_id) {
		$query = "SELECT MAX(rank) AS rank
				  FROM ".Config::db_table_user_column();
		$rank = (int) $this->db->select($query, true, 'rank');
		$query = "INSERT INTO ".Config::db_table_user_column()."
				  (column_id, rank)
				  VALUES
				  (".$col_id.", ".($rank+1).")";
		$this->db->insert($query);
	}

	public function delete($ucid) {
		$query = "SELECT rank
				  FROM ".Config::db_table_user_column()."
				  WHERE user_column_id = ".$ucid;
		$rank = $this->db->select($query, true, "rank");

		$query = "UPDATE ".Config::db_table_user_column()." SET
					rank = rank - 1
				  WHERE rank > ".$rank;
		$this->db->update($query);

		$this->db->delete("DELETE FROM ".Config::db_table_user_column()." WHERE user_column_id = ".$ucid);
	}

}
