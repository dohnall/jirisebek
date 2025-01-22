<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";

$langs = scandir(CMSLANG);
$cms_langs = array();
foreach($langs as $lang) {
    if(strlen($lang) == 6) {
        $cms_langs[] = substr($lang, 0, 2);
    }
}

$lang = isset($_GET['lang']) && in_array($_GET['lang'], $cms_langs) ? $_GET['lang'] : $cms_langs[0];

$smarty = Smarty::getInstance();
$smarty->template_dir = CMSTEMPLATES;
$smarty->compile_dir = CMSTEMPLATESC;
$smarty->configLoad(CMSLANG.$lang.".ini");

$dictionary = $smarty->getConfigVars();

echo "var dictionary = ".json_encode($dictionary).";\n";
echo "var ROOT = '".CMSROOT."';\n";
echo "var AJAX = '".CMSAJAX."';\n";
echo "var DESIGN = '".CMSDESIGN."';\n";
echo "var JS = '".CMSJS."';\n";
echo "var FILES = '".FILES."';\n";
echo "var LANG = '".$lang."';\n";
