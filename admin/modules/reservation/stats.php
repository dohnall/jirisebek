<?php
if(!$this->user->hasRight(51)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$sl = new SectionList();
$actions = $sl->getSectionsByTemplate('action');

$items = array();
foreach($actions as $action_id => $name) {
	$action = Section::getInstance($action_id);
	if($action->get('value', 'private') != 1) {
		$items[$action_id]['name'] = $name;
		$items[$action_id]['capacity'] = $action->get('value', 'capacity');

		$query = "SELECT AVG(applied) AS filled FROM ".Config::db_table_course()." WHERE end < NOW() AND action_id = ".$action_id." GROUP BY action_id";
		$items[$action_id]['filled'] = $this->db->select($query, true, "filled");
		
		$items[$action_id]['efficiency'] = round((int)$items[$action_id]['filled'] / $items[$action_id]['capacity'] * 100, 2);
	}
}

//d($items);

$this->smarty->assign(array(
	'items' => $items,
));
