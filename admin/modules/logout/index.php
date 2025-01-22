<?php
unset($this->session->user_id);
unset($this->session->domain_id);
if(isset($_SESSION['WEB']['user_id'])) {
	unset($_SESSION['WEB']['user_id']);
}

$this->session->alert = $this->dictionary['logged_out'];
$this->session->alert_css_class = 'success left-icon';
Common::redirect(CMSROOT);
