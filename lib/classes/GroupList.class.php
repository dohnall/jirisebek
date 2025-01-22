<?php

class GroupList extends ItemList {

    const DEFAULT_RANK = 1;

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_group();
    }

    public function load($items = array(), $limit = 0, $from = 0, $orderby = "", $sort = "ASC") {
    	if($items) {
			$rank = $items;
		} else {
			$rank = self::DEFAULT_RANK;
		}

        $query = "SELECT *
                  FROM ".$this->table."
                  WHERE rank >= ".$rank."
                  ORDER BY rank ASC";
        $this->data = $this->db->select($query);
    }

    public function getRankByGroup($group_id) {
        $query = "SELECT rank
                  FROM ".$this->table."
                  WHERE group_id = ".$group_id;
        if($rank = $this->db->select($query, true, "rank")) {
            return $rank;
        } else {
            return self::DEFAULT_RANK;
        }
    }

    public function getCount() {
        $return = 0;
        $query = "SELECT COUNT(*) AS cnt FROM ".$this->table;
        $return = $this->db->select($query, true, "cnt");
        return $return;
    }

}
