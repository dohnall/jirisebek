<?php
if(!$this->user->hasRight(46)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$codelist_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
$code = isset($_GET['code']) && $_GET['code'] ? $_GET['code'] : "";

if(isset($_POST['save'])) {
	if($this->user->hasRight(47)) {
	    $v = new Validator($_POST['item']['item']);
	    $v->addRule('code', 'required');
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST['item'];
	        Common::redirect();
	    } else {
	    	$_POST['user']['admin'] = isset($_POST['user']['admin']) ? 1 : 0;

	        if(isset($_FILES['file'])) {
	        	foreach($_FILES['file']['error'] as $c => $file) {
	        		foreach($file as $k => $error) {
						if($error == 0) {
							$parts = explode('.', $_FILES['file']['name'][$c][$k]);
							$ext = array_pop($parts);
							$ext = strtolower($ext);
							$filename = Common::friendlyUrl(implode('.', $parts)).'.'.$ext;
							move_uploaded_file($_FILES['file']['tmp_name'][$c][$k], LOCALFILES.$filename);
							$hash = md5_file(LOCALFILES.$filename);
							$_POST['item']['file'][$c]['file'][$k] = $filename;
							$_POST['item']['file'][$c]['hash'][$k] = $hash;
                            $_POST['item']['file'][$c]['download'][$k] = 0;

							rename(LOCALFILES.$filename, LOCALFILES.substr($hash, 0, 2).DS.$hash.'.'.$ext);
						}
					}
				}
			}

	        $codelist = new Codelist($codelist_id);
			$codelist->load();
			$item = $codelist->getRecords($code);
	        $data = $_POST['item'];
	        $data['codelist_id'] = $_POST['codelist_id'];

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
}

$codelist = new Codelist($codelist_id);
$codelist->load();
$item = $codelist->getRecords($code);

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data['item'] = $item->get('item');
    $data['value'] = $item->get('value');
    $data['file'] = $item->get('file');
}

$config = new Config();
$codelistData = "";
foreach($config->getCodelistCols($codelist_id) as $col) {
    $classname = "Type".ucfirst(strtolower($col['item']['type']));
    if(class_exists($classname)) {
        $type = new $classname($item, $col);
    } else {
        $type = new TypeDefault($item, $col);
    }
    $codelistData.= $type->getDetail();
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

$codelistList = new CodelistList();
$codelistList->load();

$this->smarty->assign(array(
    'codelist_id' => $codelist_id,
    'data' => $data,
    'codelistData' => $codelistData,
    'codelist' => $codelist->get(),
    'codelists' => $codelistList->get(),
    'langs' => $langList->get(),
	'codelist_lang' => $this->session->codelist_lang,
));
