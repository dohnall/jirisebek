<?php
define("MODE", 'CMS');
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

$newsletter_id = isset($_GET['newsletter']) && is_numeric($_GET['newsletter']) ? $_GET['newsletter'] : 0;

$query = "SELECT *
          FROM ".Config::db_table_newsletter()."
          WHERE newsletter_id = ".$newsletter_id;
$newsletter = $db->select($query, true);

if($newsletter) {
	$smarty->assign(array(
		'subject' => $newsletter['subject'],
		'content' => $newsletter['content'],
		'DESIGN' => DESIGN,
		'SERVICE' => ROOT.'lib/service/',
	));
	
	$smarty->display('newsletter-email.html');
} else {
	die($dictionary['newsletter_unknown_newsletter'].' <a href="'.ROOT.'">'.$dictionary['newsletter_continue'].'</a>');
}
