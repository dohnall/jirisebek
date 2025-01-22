<?php

class Forum {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
    }

    public function getForums() {
        $query = "SELECT section_id
                  FROM ".Config::db_table_forum()." f
                  GROUP BY section_id
                  ORDER BY inserted DESC";
        return $this->db->select($query);
    }

    public function getForum($section_id, $page=0, $perpage=0) {
        if($section_id) {
            $limit = "";

            if($page && $perpage) {
                $from = ($page-1) * $perpage;
                $limit = " LIMIT ".$from.", ".$perpage;
            }

            if(defined("FORUM_ORDER") && FORUM_ORDER == "thread") {
                $order = "f.lft ASC";
            } else {
                $order = "f.inserted DESC";
            }

            if(defined("MODE") && MODE == "WEB") {
                $where = " AND f.status = 'public'";
            } else {
                $where = "";
            }

            $query = "SELECT f.*, u.nickname, u.fname, u.lname, u.email
                      FROM ".Config::db_table_forum()." f
                      LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = f.user_id)
                      WHERE f.section_id=".$section_id.$where."
                      ORDER BY ".$order.$limit;
            return $this->db->select($query);
        } else {
            return array();
        }
    }

    public function getLastPosts($page=0, $perpage=0) {
        $limit = "";

        if($page && $perpage) {
            $from = ($page-1) * $perpage;
            $limit = " LIMIT ".$from.", ".$perpage;
        }

        if(defined("FORUM_ORDER") && FORUM_ORDER == "thread") {
            $order = "f.lft ASC";
        } else {
            $order = "f.inserted DESC";
        }

        if(defined("MODE") && MODE == "WEB") {
            $where = " AND f.status = 'public'";
        } else {
            $where = "";
        }

        $query = "SELECT f.*, u.nickname, u.fname, u.lname, u.email
                  FROM ".Config::db_table_forum()." f
                  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = f.user_id)
                  WHERE 1".$where."
                  ORDER BY ".$order.$limit;
        return $this->db->select($query);
    }

    public function getNewPosts() {
        if(defined("FORUM_TYPE") && FORUM_TYPE == "moderated") {
            $where = "f.status = 'new'";
        } else {
            $where = "f.inserted >= NOW() - INTERVAL 1 WEEK";
        }

        $query = "SELECT f.*, u.nickname, u.fname, u.lname, u.email
				  FROM ".Config::db_table_forum()." f
				  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = f.user_id)
				  WHERE ".$where."
				  ORDER BY f.inserted DESC";
        return $this->db->select($query);
    }

    public function getMyPosts() {
        $query = "SELECT f.*
				  FROM ".Config::db_table_forum()." f
				  LEFT JOIN ".Config::db_table_user()." u ON (u.user_id = f.user_id)
				  WHERE f.user_id = ".$this->session->user_id."
				  ORDER BY f.inserted DESC";
        return $this->db->select($query);
    }

    public function getPost($forum_id) {
        $query = "SELECT * FROM ".Config::db_table_forum()." WHERE forum_id=".$forum_id;
        return $this->db->select($query, true);
    }

    public function add($section_id, $user_id, $data, $parent_id=0) {
        if($parent_id) {
            $query = "SELECT rgt, depth FROM ".Config::db_table_forum()." WHERE forum_id=".$parent_id;
            $parent = $this->db->select($query, true);
            
            $left = $parent['rgt'];
            $right = $parent['rgt']+1;
            $depth = $parent['depth']+1;
        } else {
        	if(defined("FORUM_NEW_INSERT") && FORUM_NEW_INSERT == "end") {
	            $query = "SELECT MAX(rgt) AS rgt FROM ".Config::db_table_forum()." WHERE section_id=".$section_id;
	            $rgt = $this->db->select($query, true, "rgt");
	
	            $left = $rgt + 1;
	            $right = $rgt + 2;
	            $depth = 1;
        	} else {
				$left = 1;
				$right = 2;
				$depth = 1;
			}
        }

        $query = "UPDATE ".Config::db_table_forum()." SET lft=lft+2 WHERE section_id=".$section_id." AND lft>=".$left;
        $this->db->update($query);
        $query = "UPDATE ".Config::db_table_forum()." SET rgt=rgt+2 WHERE section_id=".$section_id." AND rgt>=".$right;
        $this->db->update($query);

        $ip = gethostbyname($_SERVER['REMOTE_ADDR']);
        $status = defined("FORUM_TYPE") && FORUM_TYPE == "open" ? "public" : "new";
        $query = "INSERT INTO ".Config::db_table_forum()."
                  (user_id, section_id, lft, rgt, depth, subject, message, ip, inserted, status)
                  VALUES
                  (".$user_id.", ".$section_id.", '".$left."', '".$right."', '".$depth."', '".$data['subject']."', '".$data['message']."', '".$ip."', NOW(), '".$status."')";
        $this->db->insert($query);
    }
    
    public function delete($forum_id) {
        $query = "SELECT section_id, lft, rgt FROM ".Config::db_table_forum()." WHERE forum_id=".$forum_id;
        $forum = $this->db->select($query, true);

        if($forum) {
            $query = "UPDATE ".Config::db_table_forum()." SET lft=lft-2 WHERE section_id=".$forum['section_id']." AND lft > ".$forum['rgt'];
            $this->db->update($query);
            $query = "UPDATE ".Config::db_table_forum()." SET rgt=rgt-2 WHERE section_id=".$forum['section_id']." AND rgt > ".$forum['rgt'];
            $this->db->update($query);
            $query = "UPDATE ".Config::db_table_forum()." SET lft=lft-1, rgt=rgt-1, depth=depth-1 WHERE section_id=".$forum['section_id']." AND lft > ".$forum['lft']." AND rgt < ".$forum['rgt'];
            $this->db->update($query);
            $query = "DELETE FROM ".Config::db_table_forum()." WHERE forum_id=".$forum_id;
            $this->db->delete($query);
        }
    }

    public function update($col, $val, $forum_id) {
        $query = "UPDATE ".Config::db_table_forum()." SET ".$col."='".$val."' WHERE forum_id=".$forum_id;
        $this->db->update($query);
    }

}
