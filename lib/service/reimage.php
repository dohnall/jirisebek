<?php
set_time_limit(3600);
$time_start = microtime(true);

define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$db = Database::connect();
$session->domain_id = DOMAINID;

$langList = new LangList();
$domain = new Domain(DOMAINID);
$domain->load();
$session->default_lang_id = $domain->getDefaultLang();
if(!isset($session->lang_id)) {
	$session->lang_id = $session->default_lang_id;
}

/*
- projit vsechny obrazky primo v adresari files
- resizovat na max 800px
- udelat md5_file a ulozit do db
- presunout do adresare dle prvnich 2 pismen hashe
- smazat original
*/

$dir = dir(LOCALFILES);
while($file = $dir->read()) {
	if(in_array($file, array('.', '..')) || !is_file(LOCALFILES.$file)) {
		continue;
	}
	//d(LOCALFILES.$file);
	$parts = explode('.', $file);
	$ext = array_pop($parts);
	if(in_array($ext, array('gif', 'png', 'jpg', 'jpeg'))) {
		Common::resize(LOCALFILES.$file, 800, 800, LOCALFILES.$file);
	}
	$hash = md5_file(LOCALFILES.$file);

	$query = "UPDATE ".Config::db_table_section_file()." SET hash = '".$hash."' WHERE file = '".$file."'";
	$db->update($query);

	rename(LOCALFILES.$file, LOCALFILES.substr($hash, 0, 2).DS.$hash.'.'.$ext);
}
$dir->close();

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "\n<!--".memory_get_usage(true)."-->";
echo "\n<!--$time-->";
