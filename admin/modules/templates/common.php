<?php
$template = isset($_GET['template']) ? $_GET['template'] : "";

$dir = dir(TEMPLATES);
$templates = array();
while($row = $dir->read()) {
	if(substr($row, -5) == '.html') {
		$templates[] = substr($row, 0, -5);
	}
}

sort($templates);

$this->smarty->assign(array(
	'templates' => $templates,
	'template' => $template,
));
