<?php
//exit;
//set_time_limit(3600);
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$db = Database::connect();
$session = Session::getInstance(MODE);

$session->lang_id = 1;
$session->user_id = 1;

Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
Config::setVar('CURRENT_DOMAIN_URL', ROOT);

$query = "SELECT *
          FROM cms_section_file
          WHERE code = 'attachments' AND
                file LIKE '%.pdf' AND
                content IS NULL";
$files = $db->select($query);

foreach($files as $file) {
    try {
        $url = FILES.'download/'.$file['file'];
        $content = file_get_contents(PDF_READER_URL.'/?u='.$url);
        $query = "UPDATE cms_section_file SET content = '".mysqli_real_escape_string(MySQL::$conn, $content)."' WHERE section_file_id = ".$file['section_file_id'];
        $db->update($query);
    } catch(Exception $e) {
        echo $file['section_file_id'].' - '.$e->getMessage().'<br>';
    }
}

d('ok');