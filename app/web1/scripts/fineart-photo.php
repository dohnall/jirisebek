<?php
if(isset($_POST['submit'])) {
    $v = new Validator($_POST);
    $v->addRule('email', 'email');
    $v->addRule('message', 'required');
    $error = $v->getErrors($v->validate(), $this->dictionary);
    if($error) {
        $this->session->alert = implode('<br />', $error);
        $this->session->alert_type = 'error';
        $this->session->data = $_POST;
    } else {
        $data = $_POST;

        //if(isset($data['website']) && empty($data['website'])) {
            $mail = new PHPMailer();
            $mail->isHTML(true);
            $mail->CharSet  = 'utf-8';
            $mail->FromName = $this->dictionary['contact_from_name'];
            $mail->From = $this->dictionary['contact_from_email'];
            $mail->Subject = $this->dictionary['contact_subject'];

            $html = '';
            $html.= $this->dictionary['name'].': '.$data['name'].'<br>';
            $html.= $this->dictionary['email'].': '.$data['email'].'<br>';
            $html.= $this->dictionary['message'].': <br>'.nl2br($data['message']);
            $mail->Body = $html;

            $mail->clearAddresses();
            $mail->addAddress("dohnal@gramonet.com");
            $mail->send();

            $mail->clearAddresses();
            $mail->addAddress($this->dictionary['contact_receiver']);
            $mail->send();
        //}

        $this->session->alert = $this->dictionary['contact_sent'];
        $this->session->alert_type = 'success';
    }
    Common::redirect();
}

$otherPhotos = [];
$parent = Section::getInstance($this->section->get('section', 'parent_id'));
$photos = $parent->get('children', true);
foreach($photos as $k => $v) {
    if($v->section_id == $this->section->section_id) {
        unset($photos[$k]);
    }
}
shuffle($photos);
$otherPhotos = array_splice($photos, 0, 3);

$this->smarty->assign([
    'otherPhotos' => $otherPhotos,
]);
