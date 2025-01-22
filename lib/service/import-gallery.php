<?php
exit;
set_time_limit(3600);
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$db = Database::connect();
$session = Session::getInstance(MODE);

$session->lang_id = 1;
$session->user_id = 1;

Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$query = "SELECT *
          FROM gallery
          WHERE display = 1
          ORDER BY priority DESC, created DESC";
$galleries = $db->select($query);

foreach($galleries as $gallery) {
    $section = new Section(0);
    $section_id = $section->create(array(
        'template' => 'gallery',
        'parent' => 106,
        'insert' => 2,
        'name' => $gallery['name'],
    ));

    $data = [];
    $data['text']['status'] = 1;
    $data['value']['header'] = $gallery['name'];

    $query = "SELECT *
              FROM photo
              WHERE gallery_id = ".$gallery['id']."
              ORDER BY priority DESC";
    $photos = $db->select($query);

    $n = 0;
    foreach($photos as $photo) {
        $img = '../../_backup/document_root/pictures/'.$photo['id'].'.'.$photo['extension'];
        if(file_exists($img)) {
            $hash = md5_file($img);
            $subdir = substr($hash, 0, 2);
            $imagename = $photo['id'].'.'.$photo['extension'];

            if(!file_exists(LOCALFILES.$subdir.DS.$hash.'.'.$photo['extension'])) {
                copy($img, LOCALFILES.$subdir.DS.$hash.'.'.$photo['extension']);
            }

            $data['file']['gallery']['file'][$n] = $imagename;
            $data['file']['gallery']['alt'][$n] = $photo['name'];
            $data['file']['gallery']['description'][$n] = '';
            $data['file']['gallery']['download'][$n] = 0;
            $data['file']['gallery']['hash'][$n] = $hash;
            $n++;
        }
    }
    $section->save($data);
}

die('ok');

