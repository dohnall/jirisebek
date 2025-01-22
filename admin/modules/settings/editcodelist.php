<?php
if(!$this->user->hasRight(46)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
	if($this->user->hasRight(47)) {
	    $v = new Validator($_POST['item']);
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $item = new Codelist($item_id);
	        $item->load();
	        $data = $item->get();
	        $data['item'] = array_merge($data['item'], $_POST['item']);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=codelist");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_POST['action']) && $_POST['action'] == 'addCol') {
	if($this->user->hasRight(47)) {
	    $v = new Validator($_POST);
	    $v->addRule('col', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    $item = new Codelist($item_id);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        Common::redirect();
	    } elseif($item->hasCol($_POST['col'])) {
	        $this->session->alert = $this->dictionary['item_exists'];
	        $this->session->alert_css_class = 'alert left-icon';
			Common::redirect();
	    } else {
			$item->addCol($_POST['col']);
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
	if($this->user->hasRight(47)) {
		$item = new Codelist($item_id);
		$item->deleteCol($_GET['id']);
        $this->session->alert = $this->dictionary['item_deleted'];
        $this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $item = new Codelist($item_id);
    $item->load();
    $data = $item->get();
}

$columnList = new ColumnList();
$columnList->load(array(), 0, 0, 'name');

$langs = $this->user->getData('lang');
$langList = new LangList();
$langList->load($langs[$this->session->domain_id]);

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
    'cols' => $columnList->get(),
    'langs' => $langList->get(),
));
