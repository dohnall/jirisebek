<?php
if(isset($_GET['action']) && $_GET['action'] == "delete") {
    $this->db->delete("DELETE FROM ".Config::db_table_ngroup()." WHERE `ngroup_id`='".$_GET['id']."'");
    $this->db->delete("DELETE FROM ".Config::db_table_nuser_ngroup()." WHERE `ngroup_id`='".$_GET['id']."'");
    Common::redirect();
} elseif(isset($_POST['action']) && $_POST['action'] == "save") {
	if($_POST['ngroup_id'] > 0) {
		$this->db->update("UPDATE ".Config::db_table_ngroup()." SET name = '".mysql_real_escape_string($_POST['name'])."' WHERE ngroup_id = ".$_POST['ngroup_id']);
	} else {
		$this->db->insert("INSERT INTO ".Config::db_table_ngroup()." (name) VALUES ('".mysql_real_escape_string($_POST['name'])."')");
	}
	Common::redirect();
}

$query = "SELECT * FROM ".Config::db_table_ngroup()." ORDER BY `name` ASC";
$records = $this->db->select($query);

$this->smarty->assign(array(
    'records' => $records,
));
