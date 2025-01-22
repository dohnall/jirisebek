<?php
if(!$this->user->hasRight(51)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$action_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
if($action_id) {
	$action = Section::getInstance($action_id);
	if($action->get('section', 'template') != 'action') {
		$this->session->alert = "Neznámá akce!";
		$this->session->alert_type = "error";
		Common::redirect();
	}
} else {
	$this->session->alert = "Neznámá akce!";
	$this->session->alert_type = "error";
	Common::redirect();
}

$query = "SELECT *
		  FROM ".Config::db_table_course()."
		  WHERE action_id = ".$action_id." AND
		  		end < NOW()
		  ORDER BY end DESC";
$items = $this->db->select($query);

$this->smarty->assign(array(
	'action' => $action,
	'items' => $items,
));
