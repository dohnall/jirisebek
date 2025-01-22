<?php
$codelist_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

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
	        $item = new CodelistRecord();
	        $data = $_POST;
	        $item->save($data);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&id=".$codelist_id);
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_GET['action']) && $_GET['action'] == "export") {
    if($this->user->hasRight(47)) {
        $codelistList = new CodelistList();
        $codelistList->export($codelist_id);
    } else {
        $this->session->alert = $this->dictionary['no_right'];
        $this->session->alert_css_class = 'error';
        Common::redirect(CMSROOT."?module=".$this->module);
    }
} elseif(isset($_POST['action']) && $_POST['action'] == "import") {
    if($this->user->hasRight(47)) {
        $files = $_FILES['import'];
        if($files['error'] == 0) {
            $data = file($files['tmp_name']);
            $codelistList = new CodelistList();
            $result = $codelistList->import($codelist_id, $data);
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
} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
    $code = isset($_GET['code']) ? $_GET['code'] : "";

    $codelist = new Codelist($codelist_id);
    $item = $codelist->getRecords($code);
    
    if($code && $item && $item->delete()) {
        $this->session->alert = $this->dictionary['item_deleted'];
        $this->session->alert_css_class = 'success left-icon';
    } else {
        $this->session->alert = $this->dictionary['unknown_item'];
        $this->session->alert_css_class = 'alert left-icon';
    }

    Common::redirect();
}

$codelistList = new CodelistList();
$codelistList->load();

if($codelist_id) {
	$codelist = new Codelist($codelist_id);
	$codelist->load();

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

	$this->smarty->assign(array(
		'codelist' => $codelist->get(),
		'langs' => $langList->get(),
		'items' => $codelist->getRecords(),
		'codelist_lang' => $this->session->codelist_lang,
		'codelist_id' => $codelist_id,
	));
}
//d($this->session->codelist_lang);
$this->smarty->assign(array(
	'codelist_id' => $codelist_id,
	'codelists' => $codelistList->get(),
));
