<?php
if(!$this->user->hasRight(42)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if(isset($_POST['action']) && $_POST['action'] == 'addCol') {
	if($this->user->hasRight(43)) {
	    $v = new Validator($_POST);
	    $v->addRule('col', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    $item = new UserColumn();
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        Common::redirect();
	    } elseif($item->has($_POST['col'])) {
	        $this->session->alert = $this->dictionary['item_exists'];
	        $this->session->alert_css_class = 'alert left-icon';
			Common::redirect();
	    } else {
			$item->add($_POST['col']);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
			Common::redirect();
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'deleteCol' && isset($_GET['id']) && is_numeric($_GET['id'])) {
	if($this->user->hasRight(43)) {
		$item = new UserColumn();
		$item->delete($_GET['id']);
        $this->session->alert = $this->dictionary['item_deleted'];
        $this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$item = new UserColumn();
$item->load();

$columnList = new ColumnList();
$columnList->load(array(), 0, 0, 'name');

$this->smarty->assign(array(
    'item' => $item->get(),
    'cols' => $columnList->get(),
));
