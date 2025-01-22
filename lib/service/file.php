<?php
define("MODE", 'CMS');
require_once dirname(dirname(__FILE__))."/config/config.php";

$db = Database::connect();

$filename = $_GET['file'];
$substr = substr($filename, 0, 2);

if(file_exists(LOCALFILES.$substr.DIRECTORY_SEPARATOR.$filename)) {
	$query = "UPDATE ".Config::db_table_section_file()." SET
			  	download = download + 1
			  WHERE file = '".$filename."'";
	$db->update($query);
	$file_properties = getimagesize(LOCALFILES.$substr.DIRECTORY_SEPARATOR.$filename);
	header("Content-Type:".$file_properties['mime']."; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$filename);
	echo file_get_contents(LOCALFILES.$substr.DIRECTORY_SEPARATOR.$filename);
}
