<?php
if(!$this->user->hasRight(13)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
	if($this->user->hasRight(14)) {
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
			$_POST['item']['required'] = isset($_POST['item']['required']) ? 1 : 0;
			$_POST['item']['readonly'] = isset($_POST['item']['readonly']) ? 1 : 0;
			$_POST['param']['disabled'] = isset($_POST['param']['disabled']) ? 1 : 0;
			$_POST['param']['multiselect'] = isset($_POST['param']['multiselect']) ? 1 : 0;
	
	        $item = new Column($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $data['item'] = array_merge($data['item'], $_POST['item']);
	        $data['param'] = array_merge($data['param'], $_POST['param']);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect();
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $item = new Column($item_id);
    $item->load();
    $data = $item->get();
}

$classtype = "Type".ucfirst(strtolower($data['item']['type']));
if(class_exists($classtype)) {
    $obj = new $classtype(null, null);
} else {
    $obj = new TypeDefault(null, null);
}

if($data['item']['type'] == 'select') {
	$config = new Config();
	$codelist = new CodelistList();
	$codelist->load();
	$this->smarty->assign(array(
	    'templates' => $config->getTemplates(),
	    'codelists' => $codelist->get(),
	));
	if($data['param']['values']) {
		$data['param']['values'] = implode("\n", $data['param']['values']);
	}
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
    'dataTypes' => Config::$dataTypes,
    'params' => $obj->params,
));
