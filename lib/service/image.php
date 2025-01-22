<?php
define("MODE", 'CMS');
require_once dirname(dirname(__FILE__))."/config/config.php";

$db = Database::connect();

$query = "SELECT hash
		  FROM ".Config::db_table_section_file()."
		  WHERE file = '".mysqli_real_escape_string(MySQL::$conn, $_GET['file'])."'";
$hash = $db->select($query, true, "hash");

if(!$hash) {
    $query = "SELECT hash
		  FROM ".Config::db_table_codelist_record_file()."
		  WHERE file = '".mysqli_real_escape_string(MySQL::$conn, $_GET['file'])."'";
    $hash = $db->select($query, true, "hash");
}

if(!$hash) {
	exit;
}

$parts = explode('.', $_GET['file']);
$ext = array_pop($parts);

if($ext != 'svg') {
    if(isset($_GET['size']) && $_GET['size']) {
        $filename = $_GET['size']."_".$hash.'.'.$ext;
    } else {
        $filename = $hash.'.'.$ext;
    }
    $substr = substr($hash, 0, 2);

//d(md5_file(LOCALFILES.$substr.DS.$hash.'.'.$ext), md5_file($hash.'.'.$ext));
    if(!file_exists(LOCALFILES.$substr.DS.$filename)) {
        //ochrana proti utoku zvenku
        if($_GET['size'] != 'w600' && (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], ROOT) !== 0)) {
            Common::redirect(PAGE404, 404);
        }

        if(is_numeric($_GET['size'])) {
            Common::resize(LOCALFILES.$substr.DS.$hash.'.'.$ext, $_GET['size'], $_GET['size'], LOCALFILES.$substr.DS.$filename);
        } elseif(substr($_GET['size'], 0, 1) == 'w') {
            Common::resize(LOCALFILES.$substr.DS.$hash.'.'.$ext, substr($_GET['size'], 1), 0, LOCALFILES.$substr.DS.$filename);
        } elseif(substr($_GET['size'], 0, 1) == 'h') {
            Common::resize(LOCALFILES.$substr.DS.$hash.'.'.$ext, 0, substr($_GET['size'], 1), LOCALFILES.$substr.DS.$filename);
        } elseif(strpos($_GET['size'], "x") !== false) {
            list($width, $height) = explode("x", $_GET['size']);
            Common::resize(LOCALFILES.$substr.DS.$hash.'.'.$ext, $width, $height, LOCALFILES.$substr.DS.$filename);
        }
    }
    $image_properties = getimagesize(LOCALFILES.$substr.DS.$filename);
} else {
    $filename = $hash.'.'.$ext;
    $substr = substr($hash, 0, 2);
    $image_properties = [
        'mime' => 'image/svg+xml',
    ];
}

header("Content-Type:".$image_properties['mime']."; charset=utf-8");
header("Content-Disposition: inline; filename=".$_GET['file']);
echo file_get_contents(LOCALFILES.$substr.DS.$filename);
