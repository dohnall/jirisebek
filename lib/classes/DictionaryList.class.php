<?php

class DictionaryList {

    private $item = "code";
    private $db = "";
    private $data = array();
    const RE = "/^[a-z0-9\-_]{3,50}$/";

    public function __construct() {
        $this->db = Database::connect();
    }

    public function load($domain_id, $lang_id, $search="") {
        $query = "SELECT *
                  FROM ".Config::db_table_dictionary()."
                  WHERE domain_id = ".$domain_id." AND
                        lang_id = ".$lang_id;
        if($search) {
            $query.= " AND (code LIKE '%".$search."%' OR value LIKE '%".$search."%')";
        }
        $query.= " ORDER BY code ASC";
        $data = $this->db->select($query);
        foreach($data as $row) {
            $this->data[] = $row;
        }
    }

    public function get() {
        return $this->data;
    }

    public function callGeneration($domain_id, $lang_id) {
        $domain = new Domain($domain_id);
        $domain->load();
        $data = $domain->get();
		if(in_array(ROOT, $data['alias'])) {
			$url = ROOT;
		} else {
			$url = current($data['alias']);
		}
        return file_get_contents_curl($url."lib/service/dictionary-generate.php?lang=".$lang_id);
    }

    public function generate($domain_id, $lang_id) {
        $lang = new Lang($lang_id);
        $lang->load();
        $langData = $lang->get();

        $this->load($domain_id, $lang_id);
        $items = $this->get();

        if($items) {
            $f = fopen(STATOR.$langData['item']['code'].".ini", "wb");
            foreach($items as $item) {
                fwrite($f, $item['code']." = ".preg_replace("/[\n]{1,}/", "", $item['value'])."\n");
            }
            fclose($f);
            return "ok";
        } else {
            return "ko";
        }
    }

    public function export() {
        header("Content-Type:application/octet-stream; charset=windows-1250");
        header("Content-Disposition: inline; filename=dictionary-".date("Y-m-d-H-i-s").".csv");
        if(DEBUGGER === true) {
			NDebugger::$bar = FALSE;
		}
        $f = fopen("php://output", "w");
        foreach($this->get() as $item) {
            $row = $item['code'].";".preg_replace("/[\n]{1,}/", "", nl2br($item['value']))."\n";
            fwrite($f, iconv('UTF-8', 'CP1250', $row));
        }
        
        fclose($f);
        exit;
    }

    public function import($domain_id, $lang_id, $data, $type) {
        $error = 0;
        $this->db->begin();
        if($type == 'replace') {
            $query = "DELETE FROM ".Config::db_table_dictionary()."
                      WHERE domain_id = ".$domain_id." AND
                            lang_id = ".$lang_id;
            $this->db->delete($query);
        }
        foreach($data as $k => $row) {
            $arr = explode(";", iconv('CP1250', 'UTF-8', $row));
            $code = array_shift($arr);
            $value = implode(";", $arr);
            
            $code = trim($code);
            $value = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $value);
            $value = trim($value);
            if(!preg_match(self::RE, $code)) {
                $error = $k + 1;
                break;
            }
            $query = "REPLACE INTO ".Config::db_table_dictionary()."
                      (domain_id, lang_id, ".$this->item.", value)
                      VALUES
                      (".$domain_id.", ".$lang_id.", '".addslashes($code)."', '".addslashes($value)."')";
            $this->db->replace($query);
        }
        if($error === 0) {
            $this->db->commit();
            return true;
        } else {
            $this->db->rollback();
            return $error;
        }
    }

}
