<?php
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$smarty = Smarty::getInstance();
$smarty->template_dir = CMSTEMPLATES;
$smarty->compile_dir = CMSTEMPLATESC;

$db = Database::connect();
$session = Session::getInstance(MODE);

Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$lang = new Lang(1);
$lang->load();
$langData = $lang->get();
$langCode = $langData['item']['code'];
Config::setVar('CURRENT_LANG_CODE', $langCode);

if(file_exists(STATOR.$langCode.".ini")) {
	$smarty->configLoad(STATOR.$langCode.".ini");
	$dictionary = $smarty->getConfigVars();
} else {
	$dictionary = array();
}

$hash = isset($_GET['id']) && strlen($_GET['id']) == 32 ? $_GET['id'] : "";

$query = "SELECT nuser_id
          FROM ".Config::db_table_nuser()."
          WHERE md5check = '".$hash."'";
$nuser_id = $db->select($query, true, "nuser_id");

if($nuser_id) {
	$db->update("UPDATE ".Config::db_table_nuser()." SET status = '0' WHERE nuser_id = ".$nuser_id);
	die($dictionary['newsletter_logged_out'].' <a href="'.ROOT.'">'.$dictionary['newsletter_continue'].'</a>');
} else {
	die($dictionary['newsletter_unknown_user'].' <a href="'.ROOT.'">'.$dictionary['newsletter_continue'].'</a>');
}
