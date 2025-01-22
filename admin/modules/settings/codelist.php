<?php
if(!$this->user->hasRight(46)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}
if(isset($_POST['lang'])) {
    $this->session->codelist_lang = $_POST['lang'];
    Common::redirect();
} elseif(isset($_POST['action']) && $_POST['action'] == 'new_item') {
	if($this->user->hasRight(47)) {
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
	        $item = new Codelist($_POST['item_id']);
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
} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
	if($this->user->hasRight(47)) {
	    $item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
	    $item = new Codelist($item_id);
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

$langs = $this->user->getData('lang');

if(!isset($this->session->codelist_lang) || !$this->user->hasLang($this->session->codelist_lang)) {
    if(!isset($this->session->lang_id) || !$this->user->hasLang($this->session->lang_id)) {
        $this->session->codelist_lang = current($langs[$this->session->domain_id]);
    } else {
        $this->session->codelist_lang = $this->session->lang_id;
    }
}

$langList = new LangList();
$langList->load($langs[$this->session->domain_id]);

$items = new CodelistList();
$items->load();

$this->smarty->assign(array(
    'items' => $items->get(),
    'langs' => $langList->get(),
    'codelist_lang' => $this->session->codelist_lang,
));
