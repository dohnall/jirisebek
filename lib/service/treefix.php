<?php
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";
$db = Database::connect();

function fix($db, $parentId = 1) {
    $query = "SELECT * FROM cms_tree WHERE main = '1' AND section_id = ".$parentId;
    $section = $db->select($query, true);
    $query = "SELECT * FROM cms_tree WHERE parent_id = ".$parentId." ORDER BY rank ASC";
    $children = $db->select($query);
    $i = 1;
    foreach($children as $kid) {
        $query = "UPDATE cms_tree SET depth = ".($section['depth']+1).", rank = ".$i." WHERE section_id = ".$kid['section_id']." AND parent_id = ".$parentId;
        $db->update($query);
        fix($db, $kid['section_id']);
        $i++;
    }
}

fix($db);
