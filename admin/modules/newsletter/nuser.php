<?php
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

if(isset($_GET['action']) && $_GET['action'] == "delete") {
    $this->db->delete("DELETE FROM ".Config::db_table_nuser()." WHERE `nuser_id`='".$_GET['id']."'");
    $this->db->delete("DELETE FROM ".Config::db_table_nuser_ngroup()." WHERE `nuser_id`='".$_GET['id']."'");
    $this->db->delete("DELETE FROM ".Config::db_table_nqueue()." WHERE `nuser_id`='".$_GET['id']."'");
    Common::redirect();
} elseif(isset($_POST['reset'])) {
	unset($this->session->nfilter);
	Common::redirect();
} elseif(isset($_POST['action']) && $_POST['action'] == "filter") {
	$this->session->nfilter = $_POST['filter'];
	Common::redirect();
} elseif(isset($_POST['action']) && $_POST['action'] == "import") {
	if(isset($_FILES['csv']['tmp_name'])) {
		$f = fopen($_FILES['csv']['tmp_name'], "rb");
/*
		if($_SERVER["SERVER_NAME"] == 'localhost') {
			$this->db->execute("TRUNCATE TABLE ".Config::db_table_nuser());
			$this->db->execute("TRUNCATE TABLE ".Config::db_table_nuser_ngroup());		
		} else {
			$db = new MySQL(DBHOST, "a63064_dz", "7VnMRhWC", DBNAME, DBCSET);
			$db->execute("TRUNCATE TABLE ".Config::db_table_nuser());
			$db->execute("TRUNCATE TABLE ".Config::db_table_nuser_ngroup());
		}
*/
		$query = "SELECT * FROM ".Config::db_table_ngroup()." ORDER BY name ASC";
		$groups = $this->db->select($query);
		$i = 1;
	    while($row = fgets($f)) {
	    	if($i > 1) {
	    		$row = iconv('CP1250', 'UTF-8', $row);
		        $cols = explode(';', trim($row));
		        if((count($cols) == 4 + count($groups)) && preg_match('/[a-zA-Z0-9\.\_\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,6}/', $cols[0])) {
		            $md5check = md5($cols[0].time());
		            $query = "INSERT INTO ".Config::db_table_nuser()."
		                      (email, fname, lname, inserted, md5check, status)
		                      VALUES
		                      ('".$cols[0]."', '".$cols[1]."', '".$cols[2]."', NOW(), '".$md5check."', '".$cols[(count($groups) + 3)]."')";
		            $nuser_id = $this->db->insert($query);

					foreach($groups as $k => $group) {
						if($cols[($k+3)] == 1) {
							$query = "INSERT INTO ".Config::db_table_nuser_ngroup()."
									  (nuser_id, ngroup_id)
									  VALUES
									  (".$nuser_id.", ".$group['ngroup_id'].")";
							$this->db->insert($query);
						}
					}
		        } else {
			        $this->session->alert = sprintf($this->dictionary['import_failed'], $i);
			        Common::redirect();
				}
			}
			$i++;
	    }
	} else {
		$this->session->alert = $this->dictionary['import_error'];
		Common::redirect();
	}

	$this->session->alert = $this->dictionary['import_complete'];
	$this->session->alert_css_class = 'success left-icon';
	Common::redirect();
} elseif(isset($_POST['page'])) {
    Common::redirect(CMSROOT."?module=".$this->module."&submodule=nuser&page=".$_POST['page']);
}

$where = "";
$join = "";
if($this->session->nfilter) {
	$filter = $this->session->nfilter;
	if(isset($filter['email']) && $filter['email']) {
		$where.= " AND u.email LIKE '%".mysqli_real_escape_string(MySQL::$conn, $filter['email'])."%'";
	}
	if(isset($filter['fname']) && $filter['fname']) {
		$where.= " AND u.fname LIKE '%".mysqli_real_escape_string(MySQL::$conn, $filter['fname'])."%'";
	}
	if(isset($filter['lname']) && $filter['lname']) {
		$where.= " AND u.lname LIKE '%".mysqli_real_escape_string(MySQL::$conn, $filter['lname'])."%'";
	}
	if(isset($filter['group']) && is_array($filter['group'])) {
		foreach($filter['group'] as $ngroup_id) {
			$join.= " LEFT JOIN ".Config::db_table_nuser_ngroup()." ug".$ngroup_id." ON (u.nuser_id = ug".$ngroup_id.".nuser_id)";
			$where.= " AND ug".$ngroup_id.".ngroup_id = ".$ngroup_id;
		}
	}
}

$query = "SELECT COUNT(*) AS cnt
		  FROM ".Config::db_table_nuser()." u
		  ".$join."
		  WHERE 1".$where;
$cnt = $this->db->select($query, true, "cnt");

$pagerObj = new Pager($cnt, Config::PERPAGE, $page);
$pagerObj->process();
$pager = $pagerObj->getPager();

$query = "SELECT *
		  FROM ".Config::db_table_nuser()." u
		  ".$join."
		  WHERE 1".$where."
		  ORDER BY u.email ASC
		  LIMIT ".$pager['from'].", ".Config::PERPAGE;
$records = $this->db->select($query);

$nuser_ids = array();
foreach($records as $row) {
	$nuser_ids[] = $row['nuser_id'];
}

$ngroup = array();
if($nuser_ids) {
	$query = "SELECT * FROM ".Config::db_table_nuser_ngroup()." WHERE nuser_id IN (".implode(', ', $nuser_ids).")";
	$result = $this->db->select($query);
	foreach($result as $row) {
		$ngroup[$row['nuser_id']][] = $row['ngroup_id'];
	}
}

$query = "SELECT * FROM ".Config::db_table_ngroup()." ORDER BY name ASC";
$groups = $this->db->select($query);

$this->smarty->assign(array(
    'records' => $records,
    'groups' => $groups,
    'ngroup' => $ngroup,
    'pager' => $pager,
    'filter' => $this->session->nfilter,
));
