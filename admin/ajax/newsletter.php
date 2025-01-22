<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";
require_once CMSAJAXLOCAL."common.php";

$db->module = 'newsletter';

$action = isset($_POST['action']) ? $_POST['action'] : "";

//USER TO GROUP
if($action == "setUserGroup") {
	$str = isset($_POST['str']) ? $_POST['str'] : "0_0";
	list($nuser_id, $ngroup_id) = explode('_', $str);
	$query = "SELECT COUNT(*) AS cnt
			  FROM ".Config::db_table_nuser_ngroup()."
			  WHERE nuser_id = ".$nuser_id." AND
			  		ngroup_id = ".$ngroup_id;
	$cnt = $db->select($query, true, "cnt");
	if($cnt) {
		$db->delete("DELETE FROM ".Config::db_table_nuser_ngroup()." WHERE nuser_id = ".$nuser_id." AND ngroup_id = ".$ngroup_id);
	} else {
		$db->insert("INSERT INTO ".Config::db_table_nuser_ngroup()." (nuser_id, ngroup_id) VALUES (".$nuser_id.", ".$ngroup_id.")");
	}
}

echo $return;
