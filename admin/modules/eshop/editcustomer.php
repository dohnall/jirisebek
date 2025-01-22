<?php
$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

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
        $newdata['value'] = $_POST['value'];
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

$item = new User($item_id);
$item->load();

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data = $item->getData();
    $data['value'] = $item->get('value');
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $item,
    'data' => $data,
));
