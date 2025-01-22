<?php

$forum = new Forum();

//dump($forum->getNewPosts());

$this->smarty->assign(array(
	'sections' => $forum->getForums(),
));
