<?php
if(isset($_POST['search'])) {
    $this->session->dictionary_lang = $_POST['lang'];
    $this->session->dictionary_search = $_POST['search'];
    Common::redirect();
} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
	if($this->user->hasRight(17)) {
	    $code = isset($_GET['code']) && preg_match(DictionaryList::RE, $_GET['code']) ? $_GET['code'] : "";
	
	    $item = new Dictionary($code);
	    if($item->delete()) {
	        $this->session->alert = $this->dictionary['item_deleted'];
	        $this->session->alert_css_class = 'success left-icon';
	    } else {
	        $this->session->alert = $this->dictionary['unknown_item'];
	        $this->session->alert_css_class = 'alert left-icon';
	    }
	
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_FILES['import'])) {
	if($this->user->hasRight(17)) {
	    $files = $_FILES['import'];
	    if($files['error'] == 0) {
	        $data = file($files['tmp_name']);
	        $items = new DictionaryList();
	        $result = $items->import($this->session->domain_id, $this->session->dictionary_lang, $data, $_POST['type']);
	        if($result === true) {
	            $this->session->alert = $this->dictionary['import_complete'];
	            $this->session->alert_css_class = 'success left-icon';
	        } else {
	            $this->session->alert = sprintf($this->dictionary['import_failed'], $result);
	            $this->session->alert_css_class = 'alert left-icon';
	        }
	    } else {
	        $this->session->alert = $this->dictionary['import_error'];
	        $this->session->alert_css_class = 'error left-icon';
	    }
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$langs = $this->user->getData('lang');
if(!isset($this->session->dictionary_lang) || !$this->user->hasLang($this->session->dictionary_lang)) {
    if(!isset($this->session->lang_id) || !$this->user->hasLang($this->session->lang_id)) {
        $this->session->dictionary_lang = current($langs[$this->session->domain_id]);
    } else {
        $this->session->dictionary_lang = $this->session->lang_id;
    }
}

$langList = new LangList();
$langList->load($langs[$this->session->domain_id]);

$items = new DictionaryList();
$items->load($this->session->domain_id, $this->session->dictionary_lang, $this->session->dictionary_search);

$this->smarty->assign(array(
    'langs' => $langList->get(),
    'items' => $items->get(),
    'dictionary_lang' => $this->session->dictionary_lang,
    'dictionary_search' => $this->session->dictionary_search,
));
