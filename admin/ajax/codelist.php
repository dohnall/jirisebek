<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";
require_once CMSAJAXLOCAL."common.php";

$db->module = 'codelist';

//CHANGE RECORD RANKING IN CODELIST
if($action == "changeCodelistRecordRank") {
	$db->submodule = 'index';

	$from = isset($_GET['from']) ? $_GET['from'] : 0;
	$to = isset($_GET['to']) ? $_GET['to'] : 0;

	$query = "SELECT codelist_record_id, codelist_text_id, rank
			  FROM ".Config::db_table_codelist_record()."
			  WHERE codelist_record_id IN (".$from.", ".$to.")";
	$res = $db->select($query);

	$items = array();
	foreach($res as $row) {
		if($row['codelist_record_id'] == $from) {
			$items['from'] = $row;
		} else {
			$items['to'] = $row;
		}
	}

	if($items['from']['rank'] < $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_codelist_record()." SET rank = rank - 1
				  WHERE codelist_text_id = ".$items['from']['codelist_text_id']." AND
				  		rank > ".$items['from']['rank']." AND
						rank <= ".$items['to']['rank'];
	} elseif($items['from']['rank'] > $items['to']['rank']) {
		$query = "UPDATE ".Config::db_table_codelist_record()." SET rank = rank + 1
				  WHERE codelist_text_id = ".$items['from']['codelist_text_id']." AND
				  		rank < ".$items['from']['rank']." AND
						rank >= ".$items['to']['rank'];
	} else {
		exit;
	}
	$db->update($query);
	$query = "UPDATE ".Config::db_table_codelist_record()." SET rank = ".$items['to']['rank']."
			  WHERE codelist_record_id = ".$items['from']['codelist_record_id'];
	$db->update($query);
}
