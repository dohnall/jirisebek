<?php
header("Content-Type:application/octet-stream; charset=windows-1250");
header("Content-Disposition: inline; filename=export-".date("Y-m-d-H-i-s").".csv");

if(DEBUGGER === true) {
	NDebugger::$bar = FALSE;
}

$query = "SELECT * FROM ".Config::db_table_nuser()." ORDER BY `nuser_id` ASC";
$records = $this->db->select($query);

$query = "SELECT * FROM ".Config::db_table_nuser_ngroup();
$result = $this->db->select($query);
$ngroup = array();
foreach($result as $row) {
	$ngroup[$row['nuser_id']][] = $row['ngroup_id'];
}

$query = "SELECT * FROM ".Config::db_table_ngroup()." ORDER BY name ASC";
$groups = $this->db->select($query);

$this->smarty->assign(array(
    'records' => $records,
    'ngroup' => $ngroup,
    'groups' => $groups,
));

$content = $this->smarty->fetch("newsletter_export.html");
echo iconv("UTF-8", "CP1250", $content); exit;
