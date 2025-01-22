<?php
if(!$this->user->hasRight(11)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if(isset($_POST['action']) && $_POST['action'] == 'new_item') {
	if($this->user->hasRight(12)) {
	    $v = new Validator($_POST['item']);
	    $v->addRule('name', 'required');
		$v->addRule('code', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $item = new Menu($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $data['item'] = array_merge($data['item'], $_POST['item']);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=menu");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
	if($this->user->hasRight(12)) {
	    $item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
	    $item = new Menu($item_id);
	    $item->delete();
	    $this->session->alert = $this->dictionary['item_deleted'];
	    $this->session->alert_css_class = 'success left-icon';
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$items = new MenuList();
$items->load();

$langs = new LangList();
$langs->load();

$this->smarty->assign(array(
    'items' => $items->get(),
    'langs' => $langs->get(),
));
