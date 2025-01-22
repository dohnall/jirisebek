<?php
if(!$this->user->hasRight(19)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if(isset($_GET['action']) && $_GET['action'] == 'delete') {
	if($this->user->hasRight(20)) {
	    $item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
	    if($item_id != $this->session->user_id) {
	        $item = new User($item_id);
	        $item->delete();
	        $this->session->alert = $this->dictionary['item_deleted'];
	        $this->session->alert_css_class = 'success left-icon';
	    } else {
	        $this->session->alert = $this->dictionary['cannot_delete_your_own'];
	        $this->session->alert_css_class = 'error left-icon';
	    }
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$order = isset($_GET['order']) && in_array($_GET['order'], array('nickname', 'fname', 'lname', 'email', 'status')) ? $_GET['order'] : "";
$sort = isset($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')) ? $_GET['sort'] : "";

$items = new UserList();

$userData = $this->user->getData();
$group_id = $userData['group'][$this->session->domain_id];
$groupList = new GroupList();
$group_rank = $groupList->getRankByGroup($group_id);

$this->smarty->assign(array(
    'items' => $items->getUsers($this->session->domain_id, $group_rank, $order, $sort),
    'order' => $order,
    'sort' => $sort,
));
