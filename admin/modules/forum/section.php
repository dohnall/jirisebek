<?php
$section_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if($section_id) {
	$this->smarty->assign(array(
		'posts' => $forum->getForum($section_id),
		'section_id' => $section_id,
		'section' => new Section($section_id),
		'forum_order' => FORUM_ORDER,
	));
}
