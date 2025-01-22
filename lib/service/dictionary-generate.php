<?php
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$domainList = new DomainList();
$domain_id = $domainList->getDomainByUrl(ROOT);
$lang_id = isset($_GET['lang']) && is_numeric($_GET['lang']) ? $_GET['lang'] : 0;

$dictionaryList = new DictionaryList();
echo $dictionaryList->generate($domain_id, $lang_id);
