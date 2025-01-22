<?php
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 1;
$pager = new Pager($item->hasChildren(), Config::PERPAGE, $page);
$pager->process();

$this->smarty->assign(array(
	'pager' => $pager->getPager(),
));
