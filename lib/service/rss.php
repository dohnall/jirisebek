<?php
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

$sl = new SectionList();
$articles = $sl->getSectionsByTemplate('news-detail', 1, 0, 's1.section_id DESC');

$file = '';

//header("Content-Type: application/rss+xml; charset=utf-8");
$file.= '<?xml version="1.0" encoding="utf-8"?><rss version="2.0"><channel>';
$file.= '<title>Obec Církvice</title>';
$file.= '<link>'.ROOT.'</link>';
$file.= '<description>Informace pro občany</description>';
foreach($articles as $article_id => $name) {
    $article = Section::getInstance($article_id);

    if($article->get('value', 'norss')) {
        continue;
    }

    $image = $article->get('file', 'image');

    $file.= '<item>';
    $file.= '<title><![CDATA['.$name.']]></title>';
    $file.= '<link>'.$article->get('url').'</link>';
    $file.= '<description><![CDATA['.mb_substr(strip_tags($article->get('value', 'content')), 0, 200).'...]]></description>';
    $file.= '<pubDate>'.date('r', strtotime($article->get('value', 'date'))).'</pubDate>';
    $file.= '<guid isPermaLink="false">'.$article->get('url').'</guid>';
    $file.= '<category>Aktuality / Tiskové zprávy</category>';
    if($image) {
        $fileClass = new File($image[0]['file']);
        switch($fileClass->getExt()) {
            case 'jpg':
            case 'jpeg': $mimetype = 'image/jpeg'; break;
            case 'png': $mimetype = 'image/png'; break;
            case 'gif': $mimetype = 'image/gif'; break;
            case 'svg': $mimetype = 'image/svg+xml'; break;
            case 'tiff': $mimetype = 'image/tiff'; break;
            case 'avif': $mimetype = 'image/avif'; break;
            case 'webp': $mimetype = 'image/webp'; break;
            default: $mimetype = 'image/jpeg';
        }
        $file.= '<enclosure url="'.FILES.'0/'.$image[0]['file'].'" length="'.$fileClass->getSize(5).'" type="'.$mimetype.'"></enclosure>';
    }
    $file.= '</item>';
}
$file.= '</channel></rss>';

echo $file;

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "\n<!--".memory_get_usage(true)."-->";
echo "\n<!--$time-->";
