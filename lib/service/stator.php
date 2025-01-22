<?php
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$section_id = isset($_GET['section']) && is_numeric($_GET['section']) ? $_GET['section'] : 0;
$session->lang_id = isset($_GET['lang']) && is_numeric($_GET['lang']) ? $_GET['lang'] : 0;

$domainList = new DomainList();
$session->domain_id = $domainList->getDomainByUrl(ROOT);

$domain = new Domain($session->domain_id);
$session->default_lang_id = $domain->getDefaultLang();

Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$lang = new Lang($session->lang_id);
$lang->load();
$langData = $lang->get();
Config::setVar('CURRENT_LANG_CODE', $langData['item']['code']);

$section = new Section($section_id);

$f = fopen(STATOR."section-".$section_id."-".CURRENT_LANG_CODE.".html", "wb");
$html = file_get_contents($section->get('url', true));
fwrite($f, $html);
fclose($f);
