<?php
if(!$this->user->hasRight(11)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
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
	    	$_POST['item']['status'] = isset($_POST['item']['status']) ? 1 : 0;
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
} elseif(isset($_POST['saveItems'])) {
	if($this->user->hasRight(12)) {
		if(isset($_POST['items'])) {
			foreach($_POST['items'] as $menu_item_id => $row) {
				$row['new_window'] = isset($row['new_window']) ? 1 : 0;
				$data['item'] = $row;
				$item = new MenuItem($menu_item_id);
				$item->save($data);
			}
		}
        $this->session->alert = $this->dictionary['item_saved'];
        $this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_POST['action']) && $_POST['action'] == 'addItem') {
	if($this->user->hasRight(12)) {
		$data['item'] = $_POST;
		$item = new MenuItem(0);
		$item->save($data);
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'deleteItem' && $item_id) {
	if($this->user->hasRight(12)) {
		$item = new MenuItem($item_id);
		$item->delete();
	    $this->session->alert = $this->dictionary['item_deleted'];
	    $this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'moveUp' && $item_id) {
	if($this->user->hasRight(12)) {
		$item = new MenuItem($item_id);
		$item->moveUp();
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'moveDown' && $item_id) {
	if($this->user->hasRight(12)) {
		$item = new MenuItem($item_id);
		$item->moveDown();
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
    $item = new Menu($item_id);
    $item->load();
    $data = $item->get();
}

$sectionList = new SectionList();
$homeId = $sectionList->getHomeId(0, $data['item']['lang_id']);
$homeSection = new Section($homeId);

$config = new Config();

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
    'home' => $homeSection,
    'homeId' => $homeId,
    'config' => $config,
));
