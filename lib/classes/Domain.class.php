<?php

class Domain extends Item {

    protected $item = "domain_id";
    protected $cols = array(
        'name' => "",
    );

    public function __construct($item_id = 0) {
        parent::__construct($item_id);
        $this->table = Config::db_table_domain();
    }

    public function load() {
        parent::load();
        $query = "SELECT dl.lang_id, l.name, l.code, dl.dflt
                  FROM ".Config::db_table_domain_lang()." AS dl
                  LEFT JOIN ".Config::db_table_lang()." AS l ON (l.lang_id = dl.lang_id)
                  WHERE dl.".$this->item." = ".$this->item_id;
        $langs = $this->db->select($query);
        $this->data['lang'] = array();
        foreach($langs as $row) {
            $this->data['lang'][$row['lang_id']] = $row;
        }
        $query = "SELECT alias
                  FROM ".Config::db_table_domain_alias()."
                  WHERE ".$this->item." = ".$this->item_id."
				  ORDER BY alias ASC";
        $aliases = $this->db->select($query);
        $this->data['alias'] = array();
        foreach($aliases as $row) {
            $this->data['alias'][] = $row['alias'];
        }
    }

    public function save($data) {
        parent::save($data);
        if(isset($data['lang'])) {
            $this->setLang($data['lang'], $data['default']);
        }
        if(isset($data['alias'])) {
            $this->setAlias($data['alias']);
        }
    }

    public function delete() {
        parent::delete();
        $this->db->delete("DELETE FROM ".Config::db_table_user_domain()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_user_group()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_user_lang()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_user_module()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_user_section()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_domain_alias()." WHERE ".$this->item." = ".$this->item_id);
		$this->db->delete("DELETE FROM ".Config::db_table_domain_lang()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_dictionary()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_component()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_template()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_menu()." WHERE ".$this->item." = ".$this->item_id);
        $this->db->delete("DELETE FROM ".Config::db_table_section()." WHERE ".$this->item." = ".$this->item_id);
    }

    public function hasLang($lang_id) {
        return isset($this->data['lang'][$lang_id]);
    }

    public function getDefaultLang() {
        $query = "SELECT lang_id
                  FROM ".Config::db_table_domain_lang()."
                  WHERE domain_id = ".$this->item_id." AND
                        dflt = '1'";
        return $this->db->select($query, true, "lang_id");
    }

	protected function insert($data) {
		parent::insert($data);
		$session = Session::getInstance(MODE);
		$session->domain_id = $this->item_id;

		$template = new Template();
		$template->save(array(
			'item' => array(
				'name' => 'Homepage',
				'code' => 'index',
			),
		));

		$section = Section::getInstance(0, $this->item_id, $this->getDefaultLang());
		$section->create(array(
			'template' => 'index',
			'parent' => 0,
			'insert' => 1,
			'name' => 'Home',
		));
		$data['text']['status'] = 1;
		$section->save($data);
	}

    private function setLang($langs = array(), $default) {
        if($langs) {
            $this->db->delete("DELETE FROM ".Config::db_table_domain_lang()." WHERE ".$this->item." = ".$this->item_id);
            foreach($langs as $lang_id) {
                $dflt = $lang_id == $default ? 1 : 0;
                $query = "INSERT INTO ".Config::db_table_domain_lang()."
                          (domain_id, lang_id, dflt)
                          VALUES
                          (".$this->item_id.", ".$lang_id.", '".$dflt."')";
                $this->db->insert($query);
            }
            $this->db->delete("DELETE FROM ".Config::db_table_user_lang()." WHERE ".$this->item." = ".$this->item_id." AND lang_id NOT IN (".implode(', ', $langs).")");
        }
    }

    private function setAlias($aliases = array()) {
        if($aliases) {
            $this->db->delete("DELETE FROM ".Config::db_table_domain_alias()." WHERE ".$this->item." = ".$this->item_id);
            foreach($aliases as $alias) {
            	if($alias) {
	                $query = "INSERT INTO ".Config::db_table_domain_alias()."
	                          (domain_id, alias)
	                          VALUES
	                          (".$this->item_id.", '".$alias."')";
	                $this->db->insert($query);
				}
            }
        }
    }

}
