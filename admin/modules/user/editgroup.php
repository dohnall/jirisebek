<?php
if(!$this->user->hasRight(21)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
	if($this->user->hasRight(22) && (!$this->user->hasGroup($item_id) || $this->user->hasGroup(1))) {
	    $v = new Validator($_POST['item']);
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $item = new Group($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $data = array_merge($data, $_POST);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=group");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$item = new Group($item_id);
$item->load();

$rights = new RightList();
$rights->load();

if($groups = $this->user->getData("group")) {
    $group_id = $groups[$this->session->domain_id];
} else {
    $group_id = 0;
}

$items = new GroupList();
$rank = $items->getRankByGroup($group_id);
$countGroups = $items->getCount();

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data = $item->get();
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
    'rights' => $rights->get(),
    'group' => $item,
    'groupsRank' => range($rank + 1, $item_id > 0 ? $countGroups : $countGroups + 1),
    'change' => ($group_id != $item_id),
));
