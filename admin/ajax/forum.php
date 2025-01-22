<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";
require_once CMSAJAXLOCAL."common.php";

$db->module = 'forum';

$action = isset($_POST['action']) ? $_POST['action'] : "";
$forum_id = isset($_POST['id']) && is_numeric($_POST['id']) ? $_POST['id'] : 0;

$forum = new Forum();

//UPDATE
if($action == "update") {
	$col = isset($_POST['col']) ? $_POST['col'] : "";
	$val  = isset($_POST['val']) ? $_POST['val'] : "";
	$forum->update($col, $val, $forum_id);
//UPDATE STATUS
} elseif($action == "updateStatus") {
	$col = isset($_POST['col']) ? $_POST['col'] : "";
	$val  = isset($_POST['val']) ? $_POST['val'] : "";
	$forum->update($col, $val, $forum_id);
//DELETE
} elseif($action == "delete") {
	$forum->delete($forum_id);
}

echo $return;
