<?php
$record_id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT * FROM ".Config::db_table_newsletter()." WHERE `newsletter_id`='".$record_id."'";
$newsletter = $this->db->select($query, true);

$this->smarty->assign(array(
    'newsletter' => $newsletter,
));
