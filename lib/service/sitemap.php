<?php
$time_start = microtime(true);

define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

function getXMLRow($section) {
	echo "<url><loc>".$section->get('url', false, true)."</loc><changefreq>weekly</changefreq></url>\n";
	if($section->get('children', true, true)) {
		foreach($section->get('children') as $child) {
			if($child) {
				getXMLRow($child);
			}
		}
	}
}

Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$session->domain_id = DOMAINID;

$langList = new LangList();
$domain = new Domain(DOMAINID);
$domain->load();
$session->default_lang_id = $domain->getDefaultLang();
if(!isset($session->lang_id)) {
	$session->lang_id = $session->default_lang_id;
}

$data = $domain->get();

$sectionList = new SectionList();
$homeId = $sectionList->getHomeId(0, $session->lang_id);

header("Content-Type: text/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

//d($data, $session->lang_id);
foreach($data['lang'] as $dl) {
	$session->lang_id = $dl['lang_id'];

	$lang = new Lang($session->lang_id);
	$lang->load();
	$langData = $lang->get();
	Config::setVar('CURRENT_LANG_CODE', $langData['item']['code']);

	$homeSection = new Section($homeId);
	getXMLRow($homeSection);
}
echo '</urlset>';

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "\n<!--".memory_get_usage(true)."-->";
echo "\n<!--$time-->";
