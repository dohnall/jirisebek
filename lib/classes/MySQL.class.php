<?php
class MySQL implements IDatabase {

    public $log = false;
    public $module = "";
    public $submodule = "";

    public static $conn;
    private static $instance;

    public function __construct($host, $user, $pass, $name, $charset) {
        $this->connect($host, $user, $pass, $name);
        $this->setNames($charset);
    }

    public function __destruct() {
        mysqli_close(self::$conn);
    }

    /**
     * Singleton design pattern method
     * @access public
     * @return Database
     */
    public static function getInstance($host, $user, $pass, $name, $charset) {
        if(!isset(self::$instance)) {
            $classname = __CLASS__;
            self::$instance = new $classname($host, $user, $pass, $name, $charset);
        }
        return self::$instance;
    }

    public function setNames($charset) {
        mysqli_query(self::$conn, "SET NAMES '$charset'");
    }

	public function execute($query) {
        if(!mysqli_query(self::$conn, $query)) {
            $this->error($query);
        } else {
			return true;
		}
	}

    public function insert($query, $logger = false) {
        $this->execute($query);

        $lastId = $this->lastId();

        if($this->log === true && $logger === false) {
            $this->log("INSERT", $query);
        }

        return $lastId;
    }

    public function update($query) {
        $this->execute($query);

        $affected = $this->affected();

        if($this->log === true) {
            $this->log("UPDATE", $query);
        }

        return $affected;
    }

    public function replace($query) {
        $this->execute($query);

        $lastId = $this->lastId();

        if($this->log === true) {
            $this->log("REPLACE", $query);
        }

        return $lastId;
    }

    public function delete($query) {
        $this->execute($query);

        $affected = $this->affected();

        if($this->log === true) {
            $this->log("DELETE", $query);
        }

        return $affected;
    }

    public function select($query, $one=false, $col="") {
        $return = array();
        $result = mysqli_query(self::$conn, $query);
/*
$f = fopen(LOG."log.txt", "ab");
fwrite($f, date('Y-m-d H:i:s')." - ".preg_replace('/[\s]{2,}/', ' ', $query)."\n");
fclose($f);
*/
        if(!is_object($result)) {
            $this->error($query);
        }

        while($row = mysqli_fetch_assoc($result)) {
            if($one === true) {
                if(!empty($col) && isset($row[$col])) {
                    $return = $row[$col];
                } else {
                    $return = $row;      
                }
            } else {
                $return[] = $row;
            }
        }

        $this->free($result);
        
        return $return;
    }

    public function lastId() {
        return mysqli_insert_id(self::$conn);
    }

    public function begin() {
        mysqli_query(self::$conn, "BEGIN");
    }

    public function commit() {
        mysqli_query(self::$conn, "COMMIT");
    }

    public function rollback() {
        mysqli_query(self::$conn, "ROLLBACK");
    }

    public function connect($host, $user, $pass, $name) {
        self::$conn = mysqli_connect($host, $user, $pass);
        if(!self::$conn) {
            trigger_error("DB connection failed", E_USER_ERROR);
        }
        if(!mysqli_select_db(self::$conn, $name)) {
            $this->error();
        }
    }

    public function affected() {
        return mysqli_affected_rows(self::$conn);
    }

    public function log($action, $query) {
        $this->session = Session::getInstance(MODE);
        $query = "INSERT INTO ".Config::db_table_log_action()."
                  (domain_id, lang_id, user_id, module, submodule, action, inserted, query)
                  VALUES
                  (".$this->session->domain_id.", ".$this->session->lang_id.", ".$this->session->user_id.", '".$this->module."', '".$this->submodule."', '".$action."', NOW(), '".addslashes($query)."')";
        $this->insert($query, true);
    }

    public function free($result) {
        return mysqli_free_result($result);
    }

    public function error($query) {
        //die("Error ".mysqli_errno(self::$conn).": ".mysqli_error(self::$conn)."<br />".$query);
    }

}
