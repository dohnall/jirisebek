<?php
$template = isset($_GET['template']) ? $_GET['template'] : "";

if(!file_exists(TEMPLATES.$template.".html")) {
	Common::redirect(CMSROOT."?module=".$this->module);
}

if(isset($_POST['file'])) {
	$file = stripslashes($_POST['file']);
	//d($file);
	if(file_put_contents(TEMPLATES.$template.".html", $file) !== false) {
	    $this->session->alert = $this->dictionary['item_saved'];
	    $this->session->alert_css_class = 'success left-icon';
	} else {
	    $this->session->alert = $this->dictionary['save_failed'];
	    $this->session->alert_css_class = 'error';
	}
/*
	$f = fopen(TEMPLATES.$template.".html", "wb");
	if(fwrite($f, $file) !== false) {
	    $this->session->alert = $this->dictionary['item_saved'];
	    $this->session->alert_css_class = 'success left-icon';
	} else {
	    $this->session->alert = $this->dictionary['save_failed'];
	    $this->session->alert_css_class = 'error';
	}
	fclose($f);
*/
	Common::redirect(CMSROOT."?module=".$this->module."&submodule=template&template=".$template);
}

$html = file_get_contents(TEMPLATES.$template.".html");

$this->smarty->assign(array(
	'html' => $html,
));
