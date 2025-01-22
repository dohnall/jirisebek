<?php
if(!$this->user->hasRight(5)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if(isset($_POST['save'])) {
	if($this->user->hasRight(6)) {
	    $v = new Validator($_POST['item']);
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $item = new Domain($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $data = array_merge($data, $_POST);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=domain");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
$item = new Domain($item_id);
$item->load();

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data = $item->get();
}

$langs = new LangList();
$langs->load();

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
    'langs' => $langs->get(),
    'domain' => $item,
));
