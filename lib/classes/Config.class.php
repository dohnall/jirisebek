<?php

class Config {

    const PERPAGE = 20;

	public static $dataTypes = array(
		'text',
		'checkbox',
		'select',
		'date',
		'datetime',
		'textarea',
		'html',
		'image',
		'file',
		'data',
	);

	public static $const = array();

	private $db;
	private $session;

	public function __construct() {
		$this->db = Database::connect();
		$this->session = Session::getInstance(MODE);
	}

	public function getTemplates($content = false) {
		$where = "";
		if($content) {
			$where.= " AND content = '1'";
		}
		$query = "SELECT template_id, name, code
				  FROM ".self::db_table_template()."
				  WHERE domain_id = ".$this->session->domain_id.$where."
				  ORDER BY name ASC";
		return $this->db->select($query);
	}

	public function hasTemplateChildren($template) {
		$query = "SELECT children
				  FROM ".self::db_table_template()."
				  WHERE domain_id = ".$this->session->domain_id." AND
				  		code = '".$template."'";
		return $this->db->select($query, true, "children");
	}

	public function getTabs($template_code) {
		$query = "SELECT tt.template_tab_id, tt.name
				  FROM ".self::db_table_template_tab()." tt
				  LEFT JOIN ".self::db_table_template()." t ON (t.template_id = tt.template_id)
				  WHERE t.code = '".$template_code."' AND
				  		t.domain_id = ".$this->session->domain_id;
		return $this->db->select($query);
	}

	public function getCols($tab_id) {
		$return = array();
		$query = "SELECT column_id
				  FROM ".self::db_table_template_tab_column()."
				  WHERE template_tab_id = ".$tab_id."
				  ORDER BY rank ASC";
		$col_ids = $this->db->select($query);
		foreach($col_ids as $row) {
			$col = new Column($row['column_id']);
			$col->load();
			$return[$row['column_id']] = $col->get();
		}
		return $return;
	}

	public function getAllCols() {
		$return = array();
		$query = "SELECT column_id
				  FROM ".self::db_table_column();
		$col_ids = $this->db->select($query);
		foreach($col_ids as $row) {
			$col = new Column($row['column_id']);
			$col->load();
			$return[$row['column_id']] = $col->get();
		}
		return $return;
	}

	public function getUserCols() {
		$return = array();
		$query = "SELECT column_id
				  FROM ".self::db_table_user_column()."
				  ORDER BY rank ASC";
		$col_ids = $this->db->select($query);
		foreach($col_ids as $row) {
			$col = new Column($row['column_id']);
			$col->load();
			$return[$row['column_id']] = $col->get();
		}
		return $return;
	}

	public function getCodelistCols($codelist_id) {
		$return = array();
		$query = "SELECT column_id
				  FROM ".self::db_table_codelist_column()."
				  WHERE codelist_id = ".$codelist_id."
				  ORDER BY rank ASC";
		$col_ids = $this->db->select($query);
		foreach($col_ids as $row) {
			$col = new Column($row['column_id']);
			$col->load();
			$return[$row['column_id']] = $col->get();
		}
		return $return;
	}

	public static function setVar($name, $value) {
		self::$const[$name] = $value;
	}

	public static function getVar($name) {
		return self::$const[$name];
	}

	public static function db_table_codelist() {return DBPREF."codelist";}
	public static function db_table_codelist_column() {return DBPREF."codelist_column";}
	public static function db_table_codelist_record() {return DBPREF."codelist_record";}
	public static function db_table_codelist_record_file() {return DBPREF."codelist_record_file";}
	public static function db_table_codelist_record_value() {return DBPREF."codelist_record_value";}
	public static function db_table_codelist_text() {return DBPREF."codelist_text";}
	public static function db_table_column() {return DBPREF."column";}
	public static function db_table_column_param() {return DBPREF."column_param";}
	public static function db_table_component() {return DBPREF."component";}
	public static function db_table_course() {return DBPREF."course";}
	public static function db_table_dictionary() {return DBPREF."dictionary";}
	public static function db_table_domain() {return DBPREF."domain";}
	public static function db_table_domain_alias() {return DBPREF."domain_alias";}
	public static function db_table_domain_lang() {return DBPREF."domain_lang";}
	public static function db_table_group() {return DBPREF."group";}
	public static function db_table_group_right() {return DBPREF."group_right";}
	public static function db_table_forum() {return DBPREF."forum";}
	public static function db_table_lang() {return DBPREF."lang";}
	public static function db_table_log_action() {return DBPREF."log_action";}
	public static function db_table_log_community() {return DBPREF."log_community";}
	public static function db_table_log_login() {return DBPREF."log_login";}
	public static function db_table_log_view() {return DBPREF."log_view";}
	public static function db_table_menu() {return DBPREF."menu";}
	public static function db_table_menu_item() {return DBPREF."menu_item";}
	public static function db_table_module() {return DBPREF."module";}
	public static function db_table_newsletter() {return DBPREF."newsletter";}
	public static function db_table_newsletter_ngroup() {return DBPREF."newsletter_ngroup";}
	public static function db_table_ngroup() {return DBPREF."ngroup";}
	public static function db_table_nlog() {return DBPREF."nlog";}
	public static function db_table_nqueue() {return DBPREF."nqueue";}
	public static function db_table_nuser() {return DBPREF."nuser";}
	public static function db_table_nuser_ngroup() {return DBPREF."nuser_ngroup";}
	public static function db_table_orders() {return DBPREF."orders";}
	public static function db_table_orders_items() {return DBPREF."orders_items";}
	public static function db_table_reservation() {return DBPREF."reservation";}
	public static function db_table_right() {return DBPREF."right";}
	public static function db_table_section() {return DBPREF."section";}
	public static function db_table_section_group() {return DBPREF."section_group";}
	public static function db_table_section_file() {return DBPREF."section_file";}
	public static function db_table_section_text() {return DBPREF."section_text";}
	public static function db_table_section_text_value() {return DBPREF."section_text_value";}
	public static function db_table_section_view() {return DBPREF."section_view";}
	public static function db_table_template() {return DBPREF."template";}
	public static function db_table_template_tab() {return DBPREF."template_tab";}
	public static function db_table_template_tab_column() {return DBPREF."template_tab_column";}
	public static function db_table_tree() {return DBPREF."tree";}
	public static function db_table_user() {return DBPREF."user";}
	public static function db_table_user_domain() {return DBPREF."user_domain";}
	public static function db_table_user_group() {return DBPREF."user_group";}
	public static function db_table_user_lang() {return DBPREF."user_lang";}
	public static function db_table_user_module() {return DBPREF."user_module";}
	public static function db_table_user_section() {return DBPREF."user_section";}
	public static function db_table_user_column() {return DBPREF."user_column";}
	public static function db_table_user_value() {return DBPREF."user_value";}
	public static function db_table_user_file() {return DBPREF."user_file";}

}
