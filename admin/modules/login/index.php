<?php
if(isset($_POST['login'])) {
    $userList = new UserList();
    if($user_id = $userList->login($_POST['nickname'], $_POST['passwd'])) {
        $this->session->user_id = $user_id;
        $_SESSION['WEB']['user_id'] = $user_id;
        $user = new User($this->session->user_id);
		$user->load();
    } else {
        $this->session->alert = $this->dictionary['login_failed'];
        $this->session->alert_css_class = 'error left-icon';
    }
    Common::redirect();
} elseif(isset($_POST['password'])) {
    $userList = new UserList();
    if($user_id = $userList->getUserByEmail($_POST['email'])) {
		$passwd = Common::generateString(8);
		$user = new User($user_id);
		$user->load();
		$data = $user->getData();
		$data['user']['passwd'] = md5($passwd);
		$user->save($data);

		$text = sprintf($this->dictionary['new_password_email_text'], $passwd);

		$mail = new PHPMailer();
		$mail->From = SUPPORT_MAIL;
		$mail->Subject = $this->dictionary['new_password_email_subject'];
		$mail->Body = $text;
		$mail->AddAddress($_POST['email']);
		$mail->Send();

      	$this->session->alert = $this->dictionary['new_password_email_sent'];
      	$this->session->alert_css_class = 'success left-icon';
    } else {
        $this->session->alert = $this->dictionary['unknown_email'];
        $this->session->alert_css_class = 'alert left-icon';
    }
    Common::redirect();
}

$this->mainTemplate = "main_login";
