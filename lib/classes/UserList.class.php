<?php

class UserList {

    public function __construct() {
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
    }

    public function login($nickname, $passwd) {
    	$where = "";
    	if(MODE == 'CMS') {
			$where = " admin = '1' AND";
		}
        $query = "SELECT user_id
                  FROM ".Config::db_table_user()."
                  WHERE nickname = '".$nickname."' AND
                        passwd = MD5('".$passwd."') AND
                        ".$where."
                        status = 1 AND
                        deleted = '0'";
        $user_id = $this->db->select($query, true, "user_id");
        if(!empty($user_id)) {
        	$user = new User($user_id);
        	$user->load();
        	if($user->admin) {
	            $query = "INSERT INTO ".Config::db_table_log_login()."
	                      (user_id, time, ip)
	                      VALUES
	                      (".$user_id.", NOW(), '".$_SERVER['REMOTE_ADDR']."')";
	            $this->db->insert($query);
	    	}
            return $user_id;
        } else {
            return false;
        }
    }

    public function getUserByEmail($email, $live=false) {
        $query = "SELECT user_id
                  FROM ".Config::db_table_user()."
                  WHERE email = '".$email."'";
        if($live) {
			$query.= " AND deleted = '0'";
		}
        $user_id = $this->db->select($query, true, "user_id");
        if(!empty($user_id)) {
            return $user_id;
        } else {
            return false;
        }
    }

    public function getUserByHash($hash) {
		if(!preg_match('/[a-f0-9]{32}/', $hash)) {
			return false;
		}

        $query = "SELECT user_id
                  FROM ".Config::db_table_user()."
                  WHERE MD5(CONCAT(user_id, nickname)) = '".$hash."' AND
				  		deleted = '0' AND
						status = '1'";
        $user_id = $this->db->select($query, true, "user_id");
        if(!empty($user_id)) {
            return $user_id;
        } else {
            return false;
        }
    }

    public function getUsers($domain_id = 0, $group_rank = 0, $order="", $sort="") {
    	if($order) {
			$orderby = "a.".$order." ".$sort;
		} else {
			$orderby = "g.rank ASC, a.nickname ASC";
		}
    
    	$domain_id = $domain_id ? $domain_id : $this->session->domain_id; 
        $return = array();
        $query = "SELECT a.user_id,
                         a.nickname,
                         a.fname,
                         a.lname,
                         a.email,
                         a.status
                  FROM ".Config::db_table_user_group()." AS ag
                  LEFT JOIN ".Config::db_table_user()." AS a ON (a.user_id = ag.user_id)
                  LEFT JOIN ".Config::db_table_group()." AS g ON (ag.group_id = g.group_id)
                  WHERE a.deleted = '0' AND
                        ag.domain_id = ".$domain_id." AND
                        g.rank >= ".$group_rank."
                  ORDER BY ".$orderby;
        $return = $this->db->select($query);
        return $return;
    }

	public function getLastLogged($count) {
		$query = "SELECT a.nickname, a.fname, a.lname, ll.time, ll.ip
				  FROM ".Config::db_table_log_login()." ll
				  LEFT JOIN ".Config::db_table_user()." a ON (a.user_id = ll.user_id)
				  ORDER BY ll.time DESC
				  LIMIT 0, ".$count;
		return $this->db->select($query);
	}

	public function getActiveUsers() {
		$query = "SELECT nickname, fname, lname
				  FROM ".Config::db_table_user()."
				  WHERE last_action > NOW() - INTERVAL 15 MINUTE AND
				  		admin = '1'
				  ORDER BY last_action DESC";
		return $this->db->select($query);
	}

}
