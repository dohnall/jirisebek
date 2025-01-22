<?php
$code = isset($_GET['code']) && preg_match(DictionaryList::RE, $_GET['code']) ? $_GET['code'] : "";

if(isset($_POST['save'])) {
	if($this->user->hasRight(17)) {
	    $data = array();
	    foreach($_POST['value'] as $lang_id => $value) {
	        $data[$lang_id] = array(
	            'code' => $_POST['code'],
	            'value' => $value,
	        );
	    }
	
	    $v = new Validator($_POST);
	    $v->addRule('code', 'special', 3, 50, DictionaryList::RE);
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $data;
	        Common::redirect();
	    } else {
	        $item = new Dictionary($code);
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module);
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$langs = $this->user->getData('lang');
$langList = new LangList();
$langList->load($langs[$this->session->domain_id]);

$item = new Dictionary($code);
$item->load($langs[$this->session->domain_id]);

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data = $item->get();
}

$this->smarty->assign(array(
    'langs' => $langList->get(),
    'code' => $code,
    'items' => $data,
));
