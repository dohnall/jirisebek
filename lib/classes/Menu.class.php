<?php

class Menu extends ItemDomain {

    protected $item = "menu_id";
    protected $cols = array(
        'lang_id' => 0,
		'code' => "",
        'name' => "",
        'status' => 0,
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_menu();
    }

	public function load() {
		parent::load();

		if(isset($this->session->user_id)) {
			$user = new User($this->session->user_id);
			$user->load();
			$groups = $user->getData('group');
		}

        $query = "SELECT menu_item_id, section_id
                  FROM ".Config::db_table_menu_item()."
                  WHERE ".$this->item." = ".$this->item_id." AND
                  		parent_id = 0
				  ORDER BY rank ASC";
        $res = $this->db->select($query);
        $innerItems = $this->getInnerItems();
        foreach($res as $row) {
        	if(MODE == 'WEB') {
				if($row['section_id'] > 0) {
					$section = Section::getInstance($row['section_id']);
					if(!$section->isActive()) {
						continue;
					}
					if(isset($this->session->user_id)) {
						if(count($section->get('visibility')) != 0 && !in_array($groups[$this->session->domain_id], $section->get('visibility'))) {
							continue;
						}
					} elseif(count($section->get('visibility')) != 0 && !in_array(0, $section->get('visibility'))) {
						continue;
					}
				}
			}
			$child = new MenuItem($row['menu_item_id']);
			$child->setInnerItems($innerItems);
			$child->load();
			$this->data['items'][$row['menu_item_id']] = $child->get();
		}
	}

    public function delete() {
        parent::delete();
        $this->db->delete("DELETE FROM ".Config::db_table_menu_item()." WHERE ".$this->item." = ".$this->item_id);
    }

	private function getInnerItems() {
        $query = "SELECT menu_item_id, parent_id, section_id
                  FROM ".Config::db_table_menu_item()."
                  WHERE ".$this->item." = ".$this->item_id." AND
                  		section_id > 0
				  ORDER BY rank ASC";
        return $this->db->select($query);
	}

}
