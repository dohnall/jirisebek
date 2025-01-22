<?php
if(!$this->user->hasRight(15)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if(isset($_POST['save'])) {
	if($this->user->hasRight(16)) {
	    $v = new Validator($_POST['item']);
		$v->addRule('code', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $item = new Component($_POST['item_id']);
	        $item->load();
	        $data = $item->get();
	        $data['item'] = array_merge($data['item'], $_POST['item']);
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
    $item = new Component($item_id);
    $item->load();
    $data = $item->get();
}

$config = new Config();
$cols = array();
foreach($config->getTabs($data['item']['template']) as $row) {
	$cols = array_merge($cols, $config->getCols($row['template_tab_id']));
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $data,
	'templates' => $config->getTemplates(),
	'cols' => $cols,
));
