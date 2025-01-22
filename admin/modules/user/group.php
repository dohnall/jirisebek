<?php
if(!$this->user->hasRight(21)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if($groups = $this->user->getData("group")) {
    $group_id = $groups[$this->session->domain_id];
} else {
    $group_id = 0;
}

if(isset($_GET['action']) && $_GET['action'] == 'delete') {
	if($this->user->hasRight(22)) {
	    $item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
	    if($item_id != $group_id) {
	        $item = new Group($item_id);
	        $item->delete();
	        $this->session->alert = $this->dictionary['item_deleted'];
	        $this->session->alert_css_class = 'success left-icon';
	    } else {
	        $this->session->alert = $this->dictionary['cannot_delete_own_group'];
	        $this->session->alert_css_class = 'error left-icon';
	    }
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$items = new GroupList();
$items->load($items->getRankByGroup($group_id));

$this->smarty->assign(array(
    'items' => $items->get(),
    'group_id' => $group_id,
));
