<?php
$record_id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT * FROM ".Config::db_table_nuser()." WHERE `nuser_id`='".$record_id."'";
$data = $this->db->select($query, true);

if(isset($_POST['save'])) {
    $v = new Validator($_POST);
    $v->addRule('email', 'email');
    $error = $v->getErrors($v->validate(), $this->dictionary);
    if($error) {
        $this->session->alert = implode('<br />', $error);
        $this->session->alert_css_class = 'error';
        $this->session->data = $_POST;
        Common::redirect();
    } else {
    	$data = $_POST;
	    $data['status'] = isset($data['status']) ? $data['status'] : 0;
        if($record_id) {
            $query = "UPDATE ".Config::db_table_nuser()." SET `email`='".$data['email']."', `fname`='".$data['fname']."', `lname`='".$data['lname']."', `status`='".$data['status']."' WHERE `nuser_id`='".$record_id."'";
            $this->db->update($query);
        } else {
            $md5check = md5($data['email'].time());
            $query = "INSERT INTO ".Config::db_table_nuser()." (`email`, `fname`, `lname`, `md5check`, `status`, `inserted`) VALUES ('".$data['email']."', '".$data['fname']."', '".$data['lname']."', '".$md5check."', '".$data['status']."', NOW())";
            $this->db->insert($query);
        }
        $this->session->alert = $this->dictionary['item_saved'];
        $this->session->alert_css_class = 'success left-icon';
        Common::redirect(CMSROOT."?module=".$this->module."&submodule=nuser");
    }
    Common::redirect();
}

$data = $this->session->data ? $this->session->data : $data;

$this->smarty->assign(array(
    'data' => $data,
));