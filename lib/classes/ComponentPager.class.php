<?php

class ComponentPager {

	private $pagers = array();

    public function __construct() {
    	$this->db = Database::connect();
    	$this->session = Session::getInstance(MODE);
        $this->table = Config::db_table_component();
    }

	public function get($template, $page) {
		//najdu komponenty ktere jsou pro danou sablonu a maji perpage
		$components = $this->getComponents($template);
		//zjistim celkovy pocet podle typove sablony
		foreach($components as $row) {
			$pagerObj = new Pager($this->getCountByTemplate($row['template']), $row['perpage'], $page);
			$pagerObj->process();
			$this->pagers[$row['code']] = $pagerObj->getPager();
		}
		//vratim pole pageru
		return $this->pagers;
	}

	private function getComponents($template) {
		$query = "SELECT code, template, perpage
				  FROM ".$this->table."
				  WHERE domain_id=".$this->session->domain_id." AND
				  		receiver IN ('', '".$template."') AND
						perpage > 0";
		return $this->db->select($query);
	}

	private function getCountByTemplate($template) {
		$sectionList = new SectionList();
		return count($sectionList->getSectionsByTemplate($template));
	}

}
