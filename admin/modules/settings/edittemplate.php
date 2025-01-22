<?php
if(!$this->user->hasRight(9)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
	if($this->user->hasRight(10)) {
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
	        $item = new Template($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $_POST['item']['content'] = isset($_POST['item']['content']) ? 1 : 0;
	        $_POST['item']['children'] = isset($_POST['item']['children']) ? 1 : 0;
	        $data['item'] = array_merge($data['item'], $_POST['item']);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=template");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_POST['action']) && $_POST['action'] == 'addTab') {
	if($this->user->hasRight(10)) {
	    $v = new Validator($_POST);
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        Common::redirect();
	    } else {
			$item = new Template($item_id);
			$item->addTab($_POST['name']);
	        $this->session->alert = $this->dictionary['tab_saved'];
	        $this->session->alert_css_class = 'success left-icon';
			Common::redirect();
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'deleteTab' && isset($_GET['tab']) && is_numeric($_GET['tab'])) {
	if($this->user->hasRight(10)) {
			$item = new Template($item_id);
			$item->deleteTab($_GET['tab']);
	        $this->session->alert = $this->dictionary['tab_deleted'];
	        $this->session->alert_css_class = 'success left-icon';
			Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_POST['action']) && $_POST['action'] == 'addCol') {
	if($this->user->hasRight(10)) {
	    $v = new Validator($_POST);
	    $v->addRule('col', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    $item = new Template($item_id);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        Common::redirect();
	    } elseif($item->hasCol($_POST['col'], $_POST['tab_id'])) {
	        $this->session->alert = $this->dictionary['item_exists'];
	        $this->session->alert_css_class = 'alert left-icon';
			Common::redirect();
	    } else {
			$item->addCol($_POST['col'], $_POST['tab_id']);
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
	if($this->user->hasRight(10)) {
		$item = new Template($item_id);
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
    $item = new Template($item_id);
    $item->load();
    $data = $item->get();
}

$columnList = new ColumnList();
$columnList->load(array(), 0, 0, 'name');

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
    'cols' => $columnList->get(),
));
