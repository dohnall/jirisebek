<?php
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

$query = "SELECT COUNT(*) AS cnt
		  FROM ".Config::db_table_nlog();
$cnt = $this->db->select($query, true, "cnt");

$pagerObj = new Pager($cnt, Config::PERPAGE, $page);
$pagerObj->process();
$pager = $pagerObj->getPager();

$query = "SELECT l.*, n.name
		  FROM ".Config::db_table_nlog()." l
		  LEFT JOIN ".Config::db_table_newsletter()." n ON (n.newsletter_id = l.newsletter_id)
		  ORDER BY l.inserted DESC
		  LIMIT ".$pager['from'].", ".Config::PERPAGE;
$records = $this->db->select($query);

$this->smarty->assign(array(
    'records' => $records,
    'pager' => $pager,
));
