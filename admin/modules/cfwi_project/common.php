<?php
$project_id = isset($_GET['project']) && is_numeric($_GET['project']) && $_GET['project'] > 0 ? $_GET['project'] : 0;

$cfwi = new CfWIProject();

$this->smarty->assign(array(
	'projects' => $cfwi->getProjects(),
	'project_id' => $project_id,
));
