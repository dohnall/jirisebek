<?php
//exit;
set_time_limit(3600);
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$xml = simplexml_load_file('http://old.meucaslav.cz/rss2/?13');
if(!$xml) {
    exit('Failed to open import file.');
}

$db = Database::connect();
$session = Session::getInstance(MODE);

$session->lang_id = 1;
$session->user_id = 1;

Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$categories = [];
$newCategories = [];
$helper = new Helper();
foreach($helper->codebook('deskcategory') as $category) {
    $categories[trim($category->get('item', 'name'))] = $category->get('item', 'code');
}
$categories['Volby do zastupitelstev krajů 2020'] = 18;
$categories['Nové volby ZO 2020 (Schořov)'] = 18;
$categories['Nové volby ZO 2019 (Adamov)'] = 18;
$categories['Komunální volby 2018'] = 18;
$categories['Zprávy o kontrolách za rok 2019'] = 22;
$categories['Zprávy o kontrolách za rok 2018'] = 22;
$categories['Mikroregion Čáslavsko old'] = 2;
$categories['Usnesení, zápisy a zvukové záznamy Zastupitelstva - ke smazání'] = 11;

foreach($xml->channel->item as $item) {
    $guid = strval($item->guid);

    $query = "SELECT st.section_id
              FROM cms_section_text_value stv
              LEFT JOIN cms_section_text st ON (st.section_text_id = stv.section_text_id)
              WHERE stv.code = 'guid' AND
                    stv.varchar_val = '".$guid."'";
    $section_id = $db->select($query, true, "section_id");

    if($section_id) {
        continue;
    }

    $name = strval($item->title);
    $path = strval($item->path);

    if(strpos($path, ' / ') !== false && substr_count($path, ' / ') > 1) {
        list($webhouse, $dokumenty, $desk_category_name) = explode(' / ', $path);
    } else {
        continue;
    }
    if(!$desk_category_name) {
        continue;
    }

    $section = new Section(0);
    $section_id = $section->create(array(
        'template' => 'desk-detail',
        'parent' => 10,
        'insert' => 2,
        'name' => $name,
    ));

    $data = [];
    $data['text']['status'] = 1;
    $data['value'] = [
        'guid' => $guid,
        'desk_category' => $categories[trim($desk_category_name)],
        'header' => $name,
        'content' => '',
        'public_from' => strval($item->ud_od),
        'public_to' => strval($item->ud_do),
    ];

    $i = 1;
    foreach($item->attachments->attachment as $attachment) {
        $file = 'http://old.meucaslav.cz'.strval($attachment['url']);
        $hash = md5_file($file);
        $subdir = substr($hash, 0, 2);
        $filename = strval($attachment['filename']);
        $parts = explode('.', $filename);
        $extension = array_pop($parts);
        $extension = strtolower($extension);
        $newfile = $hash.'.'.$extension;

        if(!file_exists(LOCALFILES.$subdir.DS.$newfile)) {
            copy($file, LOCALFILES.$subdir.DS.$newfile);
        }

        $data['file']['attachments']['file'][$i] = $newfile;
        $data['file']['attachments']['alt'][$i] = '';
        $data['file']['attachments']['description'][$i] = strval($attachment['name']);
        $data['file']['attachments']['download'][$i] = 0;
        $data['file']['attachments']['hash'][$i] = $hash;
        $i++;
    }

    $section->save($data);
}

d('ok');