<?php
if(!$this->user->hasRight(50)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$week = isset($_GET['week']) && is_numeric($_GET['week']) ? $_GET['week'] : date("W");
$week = (int)$week < 10 ? "0".(int)$week : $week;
if(isset($_GET['year']) && is_numeric($_GET['year'])) {
	$year = $_GET['year'];
} else {
	$year = date("Y");
	$d = date("j");

	if($week == '01' && $d > 7) {
		$year++;
	}

}

//d($year);
if(isset($_POST['copyweek'])) {
	$days = array();
	for($day=1; $day<=7; $day++) {
	    $d = date('Y-m-d', strtotime($year."W".$week.$day));
	    $days[] = $d;
	}

	$query = "SELECT action_id, (start + INTERVAL 1 WEEK) AS start
			  FROM ".Config::db_table_course()."
			  WHERE DATE(start) IN ('".implode("', '", $days)."')";
/*
	$query = "SELECT action_id, (start + INTERVAL 1 WEEK) AS start
			  FROM ".Config::db_table_course()."
			  WHERE WEEKOFYEAR(start) = ".$week." AND
			  		YEAR(start) = ".$year;
*/
	$actions = $this->db->select($query);
	$colision = 0;

	foreach($actions as $row) {
		$a = Section::getInstance($row['action_id']);
		$start = $row['start'];
		$end = date("Y-m-d H:i:s", strtotime($start." + ".($a->get('value', 'length')*5)." minute"));

		$query = "SELECT course_id
				  FROM ".Config::db_table_course()."
				  WHERE (start >= '".$start."' AND start < '".$end."') OR
				  		(end > '".$start."' AND end <= '".$end."') OR
						(start <= '".$start."' AND end >= '".$end."')
				  LIMIT 0, 1";
		$colision_id = $this->db->select($query, true, "course_id");
		if($colision_id) {
	        $colision++;
		} else {
			$query = "INSERT INTO ".Config::db_table_course()."
					  (action_id, start, end)
					  VALUES
					  (".$row['action_id'].", '".$start."', '".$end."')";
			$this->db->insert($query);
	        $this->session->alert = "Nekolizní akce byly zkopírovány do dalšího týdne! Celkem kolizí: ".$colision;
	        $this->session->alert_css_class = 'success left-icon';
		}
	}

	Common::redirect();
} elseif(isset($_POST['planning'])) {
	if($_POST['action']) {
		$a = Section::getInstance($_POST['action']);
		$start = $_POST['date']." ".$_POST['Hour'].":".$_POST['Minute'].":00";
		$end = date("Y-m-d H:i:s", strtotime($start." + ".($a->get('value', 'length')*5)." minute"));

		$query = "SELECT course_id
				  FROM ".Config::db_table_course()."
				  WHERE (start >= '".$start."' AND start < '".$end."') OR
				  		(end > '".$start."' AND end <= '".$end."') OR
						(start <= '".$start."' AND end >= '".$end."')
				  LIMIT 0, 1";
		$colision_id = $this->db->select($query, true, "course_id");
		if($colision_id) {
	        $this->session->alert = "Kolize s jinou akcí!";
	        $this->session->alert_css_class = 'error';
		} else {
			$query = "INSERT INTO ".Config::db_table_course()."
					  (action_id, start, end)
					  VALUES
					  (".$_POST['action'].", '".$start."', '".$end."')";
			$this->db->insert($query);
	        $this->session->alert = "Akce uložena!";
	        $this->session->alert_css_class = 'success left-icon';
		}
	} else {
        $this->session->alert = "Vyberte akci!";
        $this->session->alert_css_class = 'error';
	}
	Common::redirect();
} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
	$course_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
	$query = "DELETE FROM ".Config::db_table_course()." WHERE course_id = ".$course_id;
	if($this->db->delete($query)) {
        $this->session->alert = "Akce smazána!";
        $this->session->alert_css_class = 'success left-icon';
	} else {
        $this->session->alert = "Neznámá akce!";
        $this->session->alert_css_class = 'error';
	}
	Common::redirect();
} elseif(isset($_GET['action']) && $_GET['action'] == 'cancel') {
	$course_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
	$query = "UPDATE ".Config::db_table_course()." SET cancelled='1' WHERE course_id = ".$course_id;
	if($this->db->update($query)) {
		$query = "SELECT u.email
				  FROM ".Config::db_table_reservation()." r
				  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id=r.user_id)
				  WHERE r.course_id=".$course_id;
		$reservations = $this->db->select($query);
		if($reservations) {
			$query = "SELECT * FROM ".Config::db_table_course()." WHERE course_id=".$course_id;
			$course = $this->db->select($query, true);
			$action = Section::getInstance($course['action_id']);

			$this->smarty->configLoad(STATOR."cz.ini");

			$this->smarty->assign(array(
				'action' => $action,
				'course' => $course,
			));

			$message = $this->smarty->fetch("reservation_cancelled_email.html");

			$mail = new PHPMailer();
			$mail->FromName = "Divadlo Zbraslav";
			$mail->From = "kultura@mc-zbraslav.cz";
			$mail->Subject = "Akce zrušena";
			$mail->Body = $message;

			foreach($reservations as $row) {
			   	$mail->ClearAddresses();
				$mail->AddAddress($row['email']);
			   	$mail->Send();
			}
		}

        $this->session->alert = "Akce zrušena!";
        $this->session->alert_css_class = 'success left-icon';
	} else {
        $this->session->alert = "Neznámá akce!";
        $this->session->alert_css_class = 'error';
	}
	Common::redirect();
}

$days = array();
for($day=1; $day<=7; $day++) {
    $d = date('Y-m-d', strtotime($year."W".$week.$day));
//d($d, $year."W".$week.$day, $week, $day);
    $query = "SELECT *
			  FROM ".Config::db_table_course()."
			  WHERE DATE(start) = '".$d."'";
    $days[$d] = $this->db->select($query);
}
//d($days);
$prev_year = $year - 1;
$prevmaxweek = date("W", strtotime($prev_year."-12-31"));
if($prevmaxweek == '01') {
	$prevmaxweek = date("W", strtotime($prev_year."-12-24"));
}
$maxweek = date("W", strtotime($year."-12-31"));
if($maxweek == '01') {
	$maxweek = date("W", strtotime($year."-12-24"));
}

//d($week, $maxweek, $year);

$sl = new SectionList();
$actions = $sl->getSectionsByTemplate('action');

$this->smarty->assign(array(
	'actions' => $actions,
	'prevmaxweek' => $prevmaxweek,
	'maxweek' => $maxweek,
	'week' => $week,
	'year' => $year,
	'days' => $days,
));
