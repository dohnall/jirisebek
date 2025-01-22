<?php
if(!$this->user->hasRight(18)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$items = new DictionaryList();
$result = $items->generate($this->session->domain_id, $this->session->dictionary_lang);
if($result == "ok") {
    $this->session->alert = $this->dictionary['dictionary_generation_ok'];
    $this->session->alert_css_class = 'success left-icon';
} else {
    $this->session->alert = $this->dictionary['dictionary_generation_failed'];
    $this->session->alert_css_class = 'error left-icon';
}

Common::redirect();
