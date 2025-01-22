<?php
$project_id = isset($_GET['project']) && is_numeric($_GET['project']) && $_GET['project'] > 0 ? $_GET['project'] : 0;
$form_id = isset($_GET['form']) && is_numeric($_GET['form']) && $_GET['form'] > 0 && $_GET['form'] < 34 ? $_GET['form'] : 0;

$efp = new EFPProject();

$this->smarty->assign(array(
	'projects' => $efp->getProjects(),
	'project_id' => $project_id,
	'form_id' => $form_id,
));
