<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";
require_once CMSAJAXLOCAL."common.php";

$db->module = 'settings';

//CHANGE COLUMN RANKING IN TAB
if($action == "changeTabRank") {
	$db->submodule = 'edittemplate';

	$from = isset($_GET['from']) ? $_GET['from'] : 0;
	$to = isset($_GET['to']) ? $_GET['to'] : 0;

	$query = "SELECT template_tab_column_id, template_tab_id, rank
			  FROM ".Config::db_table_template_tab_column()."
			  WHERE template_tab_column_id IN (".$from.", ".$to.")";
	$res = $db->select($query);

	$items = array();
	foreach($res as $row) {
		if($row['template_tab_column_id'] == $from) {
			$items['from'] = $row;
		} else {
			$items['to'] = $row;
		}
	}

	if($items['from']['rank'] < $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_template_tab_column()." SET rank = rank - 1
				  WHERE template_tab_id = ".$items['from']['template_tab_id']." AND
				  		rank > ".$items['from']['rank']." AND
						rank <= ".$items['to']['rank'];
	} elseif($items['from']['rank'] > $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_template_tab_column()." SET rank = rank + 1
				  WHERE template_tab_id = ".$items['from']['template_tab_id']." AND
				  		rank < ".$items['from']['rank']." AND
						rank >= ".$items['to']['rank'];
	} else {
		exit;
	}
	$db->update($query);
	$query = "UPDATE ".Config::db_table_template_tab_column()." SET rank = ".$items['to']['rank']."
			  WHERE template_tab_column_id = ".$items['from']['template_tab_column_id'];
	$db->update($query);
} elseif($action == "changeUserRank") {
	$db->submodule = 'user';

	$from = isset($_GET['from']) ? $_GET['from'] : 0;
	$to = isset($_GET['to']) ? $_GET['to'] : 0;

	$query = "SELECT user_column_id, rank
			  FROM ".Config::db_table_user_column()."
			  WHERE user_column_id IN (".$from.", ".$to.")";
	$res = $db->select($query);

	$items = array();
	foreach($res as $row) {
		if($row['user_column_id'] == $from) {
			$items['from'] = $row;
		} else {
			$items['to'] = $row;
		}
	}

	if($items['from']['rank'] < $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_user_column()." SET rank = rank - 1
				  WHERE rank > ".$items['from']['rank']." AND
						rank <= ".$items['to']['rank'];
	} elseif($items['from']['rank'] > $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_user_column()." SET rank = rank + 1
				  WHERE rank < ".$items['from']['rank']." AND
						rank >= ".$items['to']['rank'];
	} else {
		exit;
	}
	$db->update($query);
	$query = "UPDATE ".Config::db_table_user_column()." SET rank = ".$items['to']['rank']."
			  WHERE user_column_id = ".$items['from']['user_column_id'];
	$db->update($query);
} elseif($action == "changeCodelistRank") {
	$db->submodule = 'editcodelist';

	$from = isset($_GET['from']) ? $_GET['from'] : 0;
	$to = isset($_GET['to']) ? $_GET['to'] : 0;

	$query = "SELECT codelist_column_id, codelist_id, rank
			  FROM ".Config::db_table_codelist_column()."
			  WHERE codelist_column_id IN (".$from.", ".$to.")";
	$res = $db->select($query);

	$items = array();
	foreach($res as $row) {
		if($row['codelist_column_id'] == $from) {
			$items['from'] = $row;
		} else {
			$items['to'] = $row;
		}
	}

	if($items['from']['rank'] < $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_codelist_column()." SET rank = rank - 1
				  WHERE codelist_id = ".$items['from']['codelist_id']." AND
				  		rank > ".$items['from']['rank']." AND
						rank <= ".$items['to']['rank'];
	} elseif($items['from']['rank'] > $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_codelist_column()." SET rank = rank + 1
				  WHERE codelist_id = ".$items['from']['codelist_id']." AND
				  		rank < ".$items['from']['rank']." AND
						rank >= ".$items['to']['rank'];
	} else {
		exit;
	}
	$db->update($query);
	$query = "UPDATE ".Config::db_table_codelist_column()." SET rank = ".$items['to']['rank']."
			  WHERE codelist_column_id = ".$items['from']['codelist_column_id'];
	$db->update($query);
}
