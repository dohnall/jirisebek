<?php

$this->smarty->assign(array(
	'posts' => $forum->getNewPosts(),
	'FORUM_TYPE' => FORUM_TYPE,
));
