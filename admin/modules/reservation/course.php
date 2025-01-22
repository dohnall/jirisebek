<?php
$course_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
$allowed = array();
if(isset($currfoll[0])) {
	$allowed[] = $currfoll[0]['course_id'];
}
if(isset($currfoll[1])) {
	$allowed[] = $currfoll[1]['course_id'];
}

if(!$this->user->hasRight(51) && !in_array($course_id, $allowed)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

if($course_id) {
	$query = "SELECT *
			  FROM ".Config::db_table_course()."
			  WHERE course_id = ".$course_id;
	$course = $this->db->select($query, true);
	if(!$course) {
		$this->session->alert = "Neexistující kurz!";
		$this->session->alert_type = "error";
		Common::redirect();
	}
} else {
	$this->session->alert = "Neexistující kurz!";
	$this->session->alert_type = "error";
	Common::redirect();
}

$action = Section::getInstance($course['action_id']);

if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	$query = "SELECT * FROM ".Config::db_table_reservation()." WHERE reservation_id=".$_GET['delete']." AND course_id=".$course_id;
	$reservation = $this->db->select($query, true);
	if($reservation) {
		$query = "DELETE FROM ".Config::db_table_reservation()." WHERE reservation_id=".$_GET['delete'];
		$this->db->delete($query);

		$query = "UPDATE ".Config::db_table_course()." SET applied=applied-1, confirmed=confirmed-".$reservation['confirmed']." WHERE course_id=".$course_id;
		$this->db->update($query);

		$this->session->alert = "Uživatel byl odstraněn z rezervace!";
		$this->session->alert_css_class = 'success left-icon';
	} else {
		$this->session->alert = "Neexistující rezervace!";
		$this->session->alert_css_class = 'error';
	}
	Common::redirect();
} elseif(isset($_GET['anonymous'])) {
	$data['fname'] = "Anonym";
	$data['lname'] = "Anonym";
	$data['email'] = "anonym@divadlozbraslav.cz";

	$ul = new UserList();
	$user_id = $ul->getUserByEmail($data['email']);
	if(!$user_id) {
       	$user = new User(0);
        $data['user'] = $data;
        $data['user']['nickname'] = $data['user']['email'];
        $passwd = $data['user']['email'];
		$data['user']['passwd'] = md5($passwd);
        $data['user']['timezone'] = DEFAULT_TIMEZONE;
        $data['user']['admin'] = 0;
        $data['user']['cmslang'] = 'cz';
        $data['user']['status'] = 1;
		$data['domain'][] = $this->session->domain_id;
		$data['group'][$this->session->domain_id] = 7;
		$data['lang'] = array();
		$data['module'] = array();
		$data['section'] = array();
        $user->save($data);
        $user->load();
        $user_id = $user->user_id;
	}
	
	$seat = $_GET['seat'];
	if($course['applied'] < $action->get('value', 'capacity')) {
		if($action->get('section', 'parent_id') == 655 || $action->get('value', 'free') == 1) {
			for($i = 1; $i <= $action->get('value', 'capacity'); $i++) {
				$query = "SELECT COUNT(*) AS cnt
						  FROM ".Config::db_table_reservation()."
						  WHERE course_id = ".$course_id." AND
						  		seat = ".$i;
				if(!$this->db->select($query, true, "cnt")) {
					$seat = $i;
					break;
				}
			}
			$source = 'bar';
		} elseif($seat > $action->get('value', 'capacity')) {
			for($i = $action->get('value', 'capacity') + 1; $i <= $action->get('value', 'capacity') + 5; $i++) {
				$query = "SELECT COUNT(*) AS cnt
						  FROM ".Config::db_table_reservation()."
						  WHERE course_id = ".$course_id." AND
						  		seat = ".$i;
				if(!$this->db->select($query, true, "cnt")) {
					$seat = $i;
					break;
				}
			}			
			$source = 'over';
		} else {
			$source = 'bar';
		}
	} else {
		$source = 'over';
	}

	$query = "INSERT INTO ".Config::db_table_reservation()."
			  (course_id, user_id, seat, source, confirmed, inserted)
			  VALUES
			  (".$course_id.", ".$user_id.", '".$seat."', '".$source."', '1', NOW())";
	$this->db->insert($query);
	$query = "UPDATE ".Config::db_table_course()." SET applied = applied+1, confirmed = confirmed+1 WHERE course_id = ".$course_id;
	$this->db->update($query);
	$this->session->alert = "Uživatel byl přihlášen do kurzu!";
	$this->session->alert_css_class = 'success left-icon';
	Common::redirect();
} elseif(isset($_POST['save'])) {
	foreach($_POST['email'] as $k => $v) {
		if($_POST['fname'][$k] || $_POST['lname'][$k] || $_POST['email'][$k]) {
			$data['fname'] = $_POST['fname'][$k];
			$data['lname'] = $_POST['lname'][$k];
			$data['email'] = $_POST['email'][$k];
			$data['person'] = $_POST['person'][$k];
		
			$v = new Validator($data);
			$v->addRule("fname", "required");
			$v->addRule("lname", "required");
			$v->addRule("email", "email");

			$error = $v->validate();

			if($error) {
				$this->session->alert = "Je potřeba vyplnit jméno, příjmení i email u každého účastníka!";
				$this->session->alert_css_class = 'error';
				$this->session->data = $_POST;
			} else {
				$ul = new UserList();
				$user_id = $ul->getUserByEmail($data['email']);
				if(!$user_id) {
			       	$user = new User(0);
			        $data['user'] = $data;
			        $data['user']['nickname'] = $data['user']['email'];
			        $passwd = $data['user']['email'];
					$data['user']['passwd'] = md5($passwd);
			        $data['user']['timezone'] = DEFAULT_TIMEZONE;
			        $data['user']['admin'] = 0;
			        $data['user']['cmslang'] = 'cz';
			        $data['user']['status'] = 1;
					$data['domain'][] = $this->session->domain_id;
					$data['group'][$this->session->domain_id] = 7;
					$data['lang'] = array();
					$data['module'] = array();
					$data['section'] = array();
			        $user->save($data);
			        $user->load();
			        $user_id = $user->user_id;

					$query = "SELECT nuser_id
							  FROM ".Config::db_table_nuser()."
							  WHERE email = '".$data['user']['email']."'";
					$nuser_id = $this->db->select($query, true, "nuser_id");
					if(!$nuser_id) {
			            $md5check = md5($data['user']['email'].time());
			            $query = "INSERT INTO ".Config::db_table_nuser()."
			                      (email, fname, lname, inserted, md5check, status)
			                      VALUES
			                      ('".$data['user']['email']."', '".$data['user']['fname']."', '".$data['user']['lname']."', NOW(), '".$md5check."', '1')";
			            $nuser_id = $this->db->insert($query);
					}
				}
				$query = "SELECT COUNT(*) AS cnt
						  FROM ".Config::db_table_reservation()."
						  WHERE course_id = ".$course_id." AND
						  		seat = ".$k;
				if(!$this->db->select($query, true, "cnt")) {
					$seat = $k;
					if($course['applied'] < $action->get('value', 'capacity')) {
						if($action->get('section', 'parent_id') == 655 || $action->get('value', 'free') == 1) {
							for($i = 1; $i <= $action->get('value', 'capacity'); $i++) {
								$query = "SELECT COUNT(*) AS cnt
										  FROM ".Config::db_table_reservation()."
										  WHERE course_id = ".$course_id." AND
										  		seat = ".$i;
								if(!$this->db->select($query, true, "cnt")) {
									$seat = $i;
									break;
								}
							}
							$source = 'bar';
						} elseif($seat > $action->get('value', 'capacity')) {
							for($i = $action->get('value', 'capacity') + 1; $i <= $action->get('value', 'capacity') + 5; $i++) {
								$query = "SELECT COUNT(*) AS cnt
										  FROM ".Config::db_table_reservation()."
										  WHERE course_id = ".$course_id." AND
										  		seat = ".$i;
								if(!$this->db->select($query, true, "cnt")) {
									$seat = $i;
									break;
								}
							}
							$source = 'over';
						} else {
							$source = 'bar';
						}
					} else {
						$source = 'over';
					}
				
					$query = "INSERT INTO ".Config::db_table_reservation()."
							  (course_id, user_id, fname, lname, seat, person, source, confirmed, inserted)
							  VALUES
							  (".$course_id.", ".$user_id.", '".$_POST['fname'][$k]."', '".$_POST['lname'][$k]."', '".$seat."', '".$data['person']."', '".$source."', '1', NOW())";
					$this->db->insert($query);
					$query = "UPDATE ".Config::db_table_course()." SET applied = applied+1, confirmed = confirmed+1 WHERE course_id = ".$course_id;
					$this->db->update($query);
					$this->session->alert = "Rezervace byla vytvořena!";
					$this->session->alert_css_class = 'success left-icon';
					Common::redirect();
				} else {
					$this->session->alert = "Sedadlo je obsazeno!";
					$this->session->alert_css_class = 'notify';
				}
			}
		}
	}
	Common::redirect();
}

$query = "SELECT r.reservation_id, r.user_id, r.seat, r.person, r.confirmed, r.fname, r.lname, u.email
		  FROM ".Config::db_table_reservation()." r
		  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = r.user_id)
		  WHERE r.course_id = ".$course_id." AND
		  		r.source IN ('web', 'bar')";
$result = $this->db->select($query);
for($i=1; $i <= $action->get('value', 'capacity'); $i++) {
	$reservation[$i] = array();
}
foreach($result as $row) {
	$reservation[$row['seat']] = $row;
}

$query = "SELECT r.reservation_id, r.user_id, r.confirmed, r.fname, r.lname, u.email
		  FROM ".Config::db_table_reservation()." r
		  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = r.user_id)
		  WHERE r.course_id = ".$course_id." AND
		  		r.source = 'over'";
$reservation_over = $this->db->select($query);

$this->smarty->assign(array(
	'course' => $course,
	'action' => $action,
	'reservation' => $reservation,
	'reservation_over' => $reservation_over,
	'data' => $this->session->data,
));
