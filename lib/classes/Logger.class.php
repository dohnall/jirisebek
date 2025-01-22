<?php

class Logger {

	public static function log($str) {
		$f = fopen(LOG."log.txt", "ab");
		fwrite($f, date("Y-m-d H:i:s")." - ".preg_replace("/[\s]{2,}/", " ", $str)."\n");
		fclose($f);
	}

    public static function logView($module, $submodule) {
        $session = Session::getInstance(MODE);
        $db = Database::connect();

		$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

        $query = "INSERT INTO ".Config::db_table_log_view()."
                  (domain_id, lang_id, user_id, module, submodule, item_id, inserted, url)
                  VALUES
                  (".$session->domain_id.", ".$session->lang_id.", ".$session->user_id.", '".$module."', '".$submodule."', '".$item_id."', NOW(), '".$_SERVER['QUERY_STRING']."')";
        $db->insert($query, true);
    }

}
