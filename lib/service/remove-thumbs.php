<?php
define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";

$dir = dir(LOCALFILES);

while($file = $dir->read()) {
    if(in_array($file, array('.', '..', 'tinymce')) || !is_dir(LOCALFILES.$file)) {
        continue;
    }
    $subdir = dir(LOCALFILES.$file);
    while($subfile = $subdir->read()) {
        if(in_array($subfile, array('.', '..')) || is_dir(LOCALFILES.$file.DS.$subfile)) {
            continue;
        }
        if(strpos($subfile, "_") !== false) {
            unlink(LOCALFILES.$file.DS.$subfile);
        }
    }
    $subdir->close();
}