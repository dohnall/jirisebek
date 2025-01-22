<?php
$item_id = $this->session->user_id;

if(isset($_POST['save'])) {
    $v = new Validator($_POST['user']);
    $v->addRule('nickname', 'required');
    if(!$item_id) {
        $v->addRule('passwd', 'required');
    }
    $v->addRule('passwd', 'password');
    $v->addRule('email', 'email');
    $error = $v->getErrors($v->validate(), $this->dictionary);
    if($error) {
        $this->session->alert = implode('<br />', $error);
        $this->session->alert_css_class = 'error';
        $this->session->data = $_POST;
        Common::redirect();
    } else {
        $item = new User($_POST['item_id']);
        $item->load();
        $data = $item->getData();

        $newdata = $data;
        $newdata['user'] = array_merge($newdata['user'], $_POST['user']);
        if(empty($_POST['user']['passwd'])) {
            $newdata['user']['passwd'] = $data['user']['passwd'];
        } else {
            $newdata['user']['passwd'] = md5($newdata['user']['passwd']);
        }

        $item->save($newdata);
        $this->session->alert = $this->dictionary['item_saved'];
        $this->session->alert_css_class = 'success left-icon';
        Common::redirect();
    }
}

$langs = scandir(CMSLANG);
$cms_langs = array();
foreach($langs as $lang) {
    if(strlen($lang) == 6) {
        $cms_langs[] = substr($lang, 0, 2);
    }
}

$item = new User($item_id);
$item->load();

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data = $item->getData();
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $item,
    'data' => $data,
    'cmslang' => $cms_langs,
    'timezones' => timezone_identifiers_list(),
));
