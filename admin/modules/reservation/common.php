<?php
$query = "SELECT course_id
		  FROM ".Config::db_table_course()."
		  WHERE end > NOW()
		  ORDER BY end ASC
		  LIMIT 0, 2";
$currfoll = $this->db->select($query);

$this->smarty->assign(array(
	'currfoll' => $currfoll,
));
