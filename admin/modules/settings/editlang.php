<?php
if(!$this->user->hasRight(1)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if(isset($_POST['save'])) {
	if($this->user->hasRight(2)) {
	    $v = new Validator($_POST['item']);
	    $v->addRule('code', 'required', 2, 2);
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $item = new Lang($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $data['item'] = array_merge($data['item'], $_POST['item']);
	        $item->save($data);
	    
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=lang");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $item = new Lang($item_id);
    $item->load();
    $data = $item->get();
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
));
