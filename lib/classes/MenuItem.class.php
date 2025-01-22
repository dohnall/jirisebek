<?php

class MenuItem extends Item {

    protected $item = "menu_item_id";
    protected $cols = array(
        'menu_id' => 0,
		'parent_id' => 0,
        'rank' => 0,
        'section_id' => 0,
        'new_window' => 0,
        'name' => "",
        'url' => "",
        'title' => "",
    );
    private $innerItems = array();

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_menu_item();
    }

    public function load() {
		parent::load();		
		if($this->data['item']['section_id'] > 0) {
			$this->data['item']['section'] = Section::getInstance($this->data['item']['section_id']);
			$this->data['item']['path'] = $this->setPath($this->data['item']['menu_item_id'], array($this->data['item']['section_id']));
		}
		$this->loadItems();
    }

	public function setInnerItems($innerItems) {
		$this->innerItems = $innerItems;
	}

	public function moveUp() {
		parent::load();
		$query = "UPDATE ".$this->table." SET
					rank = rank+1
				  WHERE menu_id = ".$this->data['item']['menu_id']." AND
				  		parent_id = ".$this->data['item']['parent_id']." AND
						rank = ".($this->data['item']['rank']-1);
		$this->db->update($query);
		$query = "UPDATE ".$this->table." SET
					rank = rank-1
				  WHERE ".$this->item." = ".$this->item_id;
		$this->db->update($query);
	}

	public function moveDown() {
		parent::load();
		$query = "UPDATE ".$this->table." SET
					rank = rank-1
				  WHERE menu_id = ".$this->data['item']['menu_id']." AND
				  		parent_id = ".$this->data['item']['parent_id']." AND
						rank = ".($this->data['item']['rank']+1);
		$this->db->update($query);
		$query = "UPDATE ".$this->table." SET
					rank = rank+1
				  WHERE ".$this->item." = ".$this->item_id;
		$this->db->update($query);
	}

	public function delete() {
		parent::load();
		$query = "UPDATE ".$this->table." SET
					rank = rank-1
				  WHERE menu_id = ".$this->data['item']['menu_id']." AND
				  		parent_id = ".$this->data['item']['parent_id']." AND
						rank > ".$this->data['item']['rank'];
		$this->db->update($query);
		parent::delete();
	}

    protected function insert($data) {
    	$query = "SELECT COUNT(*) AS cnt
				  FROM ".$this->table."
				  WHERE menu_id = ".$data['item']['menu_id']." AND
				  		parent_id = ".$data['item']['parent_id'];
    	$cnt = $this->db->select($query, true, "cnt");
    	$data['item']['rank'] = ++$cnt;

        parent::insert($data);
    }

	private function loadItems() {
        $query = "SELECT menu_item_id
                  FROM ".$this->table."
                  WHERE parent_id = ".$this->item_id."
				  ORDER BY rank ASC";
        $res = $this->db->select($query);
        foreach($res as $row) {
			$child = new MenuItem($row['menu_item_id']);
			$child->setInnerItems($this->innerItems);
			$child->load();
			$this->data['children'][$row['menu_item_id']] = $child->get();
		}
	}

	private function setPath($menu_item_id, $return) {
		foreach($this->innerItems as $item) {
			if($item['parent_id'] == $menu_item_id) {
				$return[] = $item['section_id'];
				$return = $this->setPath($item['menu_item_id'], $return);
			}
		}
		return $return;
	}

}
