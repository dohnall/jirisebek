<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";
require_once CMSAJAXLOCAL."common.php";

$db->module = 'reservation';

//CHANGE RECORD RANKING IN CODELIST
if($action == "confirmation") {
	$db->submodule = 'course';

	$reservation_id = isset($_GET['rid']) ? $_GET['rid'] : 0;

	$query = "SELECT *
			  FROM ".Config::db_table_reservation()."
			  WHERE reservation_id=".$reservation_id;
	$reservation = $db->select($query, true);
	if($reservation) {
		if($reservation['confirmed'] == 1) {
			$query = "UPDATE ".Config::db_table_reservation()." SET
						confirmed='0'
					  WHERE reservation_id=".$reservation_id;
			$db->update($query);
	
			$query = "UPDATE ".Config::db_table_course()." SET
						confirmed=confirmed-1
					  WHERE course_id=".$reservation['course_id'];
			$db->update($query);
		} else {
			$query = "UPDATE ".Config::db_table_reservation()." SET
						confirmed='1'
					  WHERE reservation_id=".$reservation_id;
			$db->update($query);
	
			$query = "UPDATE ".Config::db_table_course()." SET
						confirmed=confirmed+1
					  WHERE course_id=".$reservation['course_id'];
			$db->update($query);
		}		
	}
}
