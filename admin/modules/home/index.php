<?php
if(isset($_POST['send'])) {
    $v = new Validator($_POST);
    $v->addRule('message', 'required');
    $error = $v->getErrors($v->validate(), $this->dictionary);
    if($error) {
        $this->session->alert = implode('<br />', $error);
        $this->session->alert_css_class = 'error';
        $this->session->data = $_POST;
    } else {
		$mail = new PHPMailer();
		$mail->From = $this->user->email;
		$mail->FromName = $this->user->fname.' '.$this->user->lname;
		$mail->Subject = sprintf($this->dictionary['support_email_subject'], ROOT);
		$mail->Body = $_POST['message'];
		$mail->AddAddress(SUPPORT_MAIL);
		$mail->Send();

        $this->session->alert = $this->dictionary['support_sent'];
        $this->session->alert_css_class = 'success left-icon';
    }
    Common::redirect();
}

$userList = new UserList();
$users = $userList->getLastLogged(10);

$sectionList = new SectionList();
$sections = $sectionList->getLastUpdated(10, true);

$this->smarty->assign(array(
	'users' => $users,
	'sections' => $sections,
));
