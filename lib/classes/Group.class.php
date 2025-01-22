<?php

class Group extends Item {

    protected $item = "group_id";
    protected $cols = array(
        'name' => "",
        'description' => "",
        'rank' => 1,
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_group();
    }

    public function load() {
        parent::load();
        $query = "SELECT right_id
                  FROM ".Config::db_table_group_right()."
                  WHERE ".$this->item." = ".$this->item_id;
        $rights = $this->db->select($query);
        $this->data['right'] = array();
        foreach($rights as $row) {
            $this->data['right'][$row['right_id']] = $row['right_id'];
        }
    }

    public function save($data) {
        parent::save($data);
        if(isset($data['right'])) {
            $this->setRight($data['right']);
        }
    }

    public function delete() {
        $query = "SELECT rank
                  FROM ".$this->table."
                  WHERE ".$this->item." = ".$this->item_id;
        $rank = $this->db->select($query, true, "rank");
        $this->db->delete("UPDATE ".$this->table." SET rank = rank - 1 WHERE rank > ".$rank);
        parent::delete();
        $this->db->delete("DELETE FROM ".Config::db_table_user_group()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_group_right()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_section_group()." WHERE ".$this->item." = ".$this->item_id);
    }

    public function hasRight($right_id) {
        return isset($this->data['right'][$right_id]);
    }

    protected function insert($data) {
        $query = "UPDATE ".$this->table." SET
                  rank = rank + 1
                  WHERE rank >= ".$data['item']['rank'];
        $this->db->update($query);
        parent::insert($data);
    }

    protected function update($data) {
        $query = "SELECT rank
                  FROM ".$this->table."
                  WHERE ".$this->item." = ".$this->item_id;
        $rank = $this->db->select($query, true, "rank");

        if($rank != $data['item']['rank']) {
            if($rank > $data['item']['rank']) {
                $this->db->update("UPDATE ".$this->table." SET rank = rank + 1 WHERE rank >= ".$data['item']['rank']." AND rank < ".$rank);
            } elseif($rank < $data['item']['rank']) {
                $this->db->update("UPDATE ".$this->table." SET rank = rank - 1 WHERE rank <= ".$data['item']['rank']." AND rank > ".$rank);
            }
        }

        parent::update($data);
    }

    private function setRight($rights = array()) {
        if($rights) {
            $this->db->delete("DELETE FROM ".Config::db_table_group_right()." WHERE ".$this->item." = ".$this->item_id);
            foreach($rights as $right_id) {
                $query = "INSERT INTO ".Config::db_table_group_right()."
                          (group_id, right_id)
                          VALUES
                          (".$this->item_id.", ".$right_id.")";
                $this->db->insert($query);
            }
        }
    }

}
