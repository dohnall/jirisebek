<?php
if(!$this->user->hasRight(17)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$items = new DictionaryList();
$items->load($this->session->domain_id, $this->session->dictionary_lang, $this->session->dictionary_search);
$items->export();
