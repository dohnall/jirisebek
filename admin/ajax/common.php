<?php
$smarty = Smarty::getInstance();
$smarty->template_dir = CMSTEMPLATES;
$smarty->compile_dir = CMSTEMPLATESC;

$db = Database::connect();
$db->log = LOG_ACTION;
$session = Session::getInstance(MODE);

$user = new User($session->user_id);
$user->load();

if($user->timezone) {
	Config::setVar('USER_TIMEZONE', $user->timezone);
} else {
	Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
}

$smarty->configLoad(CMSLANG.$user->cmslang.".ini");
$dictionary = $smarty->getConfigVars();

$return = "";

$action = isset($_GET['action']) ? $_GET['action'] : "";
