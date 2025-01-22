<?php

class CodelistList extends ItemListDomain {

    protected $item = "codelist_id";

    public function __construct() {
        parent::__construct();
        $this->table = Config::db_table_codelist();
        $this->lang_id = isset($this->session->codelist_lang) ? $this->session->codelist_lang : $this->session->lang_id;
    }

    public function load($items = array(), $limit = 0, $from = 0, $orderby = "t2.name", $sort = "ASC") {
        $query = "SELECT t1.*, t2.name
                  FROM ".$this->table." t1
                  LEFT JOIN ".Config::db_table_codelist_text()." t2 ON (t1.".$this->item." = t2.".$this->item." AND t2.lang_id = ".$this->lang_id.")
				  WHERE domain_id=".$this->session->domain_id;

        if($items) {
            $query.= " AND ".$this->item." IN (".implode(', ', $items).")";
        }

        if($orderby) {
			$query.= " ORDER BY ".$orderby." ".$sort;
		}

        if($limit) {
			$query.= " LIMIT ".$from." ".$limit;
		}

        $data = $this->db->select($query);
        foreach($data as $row) {
            $this->data[$row[$this->item]] = $row;
        }
    }

	public function getCodelist($code) {
		$query = "SELECT codelist_id
				  FROM ".$this->table."
				  WHERE code = '".mysqli_real_escape_string(MySQL::$conn, $code)."'";
		$codelist_id = $this->db->select($query, true, "codelist_id");
		if($codelist_id) {
			return new Codelist($codelist_id);
		} else {
			return false;
		}
	}

    public function export($codelist_id) {
        $codelist = new Codelist($codelist_id);
        $codelist->load();
        $codelistData = $codelist->get();

        header("Content-Type:application/octet-stream; charset=windows-1250");
        header("Content-Disposition: inline; filename=".$codelistData['item']['code']."-".date("Y-m-d-H-i-s").".csv");
        if(DEBUGGER === true) {
            NDebugger::$bar = FALSE;
        }
        $config = new Config();
        $cols = $config->getCodelistCols($codelist_id);

        $records = $codelist->getRecords();

        $f = fopen("php://output", "w");
        $row = "code;name";
        foreach($cols as $column_id => $column) {
            if($column['item']['type'] != "data") {
                $row.= ";".$column['item']['code'];
            }
        }
        $row.= "\n";

        fwrite($f, iconv('UTF-8', 'CP1250', $row));

        foreach($records as $record_id => $record) {
            $row = $record->get('item', 'code').";".$record->get('item', 'name');
            foreach($cols as $column_id => $column) {
                if($column['item']['type'] != "data") {
                    if(in_array($column['item']['type'], array('file', 'image'))) {
                        $files = $record->get('file', $column['item']['code']);
                        $value = array();
                        if($files) {
                            foreach($files as $file) {
                                $value[] = $file['file'];
                            }
                        }
                    } else {
                        $value = $record->get('value', $column['item']['code']);
                        $value = str_replace(";", "|||", $value);
                    }
                    if(is_array($value)) {
                        $value = implode('|||', $value);
                    }
                    $row.= ";".preg_replace("/[\s]{2,}/", " ", $value);
                }
            }
            $row.= "\n";
            fwrite($f, iconv('UTF-8', 'CP1250//IGNORE', $row));
        }
        fclose($f);
        exit;
    }

    public function import($codelist_id, $data) {
        $error = 0;

        $config = new Config();
        $arr = $config->getCodelistCols($codelist_id);

        foreach($data as $k => $row) {
            if($k) {
                $cols = explode(";", iconv('CP1250', 'UTF-8', $row));
                if(count($arr)+2 != count($cols)) {
                    $error = $k + 1;
                    break;
                }

                $item = new CodelistRecord();

                $recordData['codelist_id'] = $codelist_id;
                $recordData['lang'] = $this->lang_id;
                $recordData['item'] = [
                    'code' => $cols[0],
                    'name' => [
                        $this->lang_id => $cols[1],
                    ],
                ];

                $j = 0;
                foreach($arr as $i => $col) {
                    if(strpos($cols[$j+2], '|||') !== false) {
                        $value = explode('|||', $cols[$j+2]);
                        if(in_array($col['item']['type'], array('file', 'image'))) {
                            /*
                            foreach($value as $v) {
                                $recordData['file'][$col['item']['code']]['file'][] = $v;
                                $recordData['file'][$col['item']['code']]['alt'][] = '';
                                $recordData['file'][$col['item']['code']]['description'][] = '';
                            }
                            */
                        } else {
                            foreach($value as $v) {
                                $recordData['value'][$col['item']['code']][] = $v;
                            }
                        }
                    } else {
                        $value = $cols[$j+2];
                        if(in_array($col['item']['type'], array('file', 'image'))) {
                            /*
                            $recordData['file'][$col['item']['code']]['file'][] = $value;
                            $recordData['file'][$col['item']['code']]['alt'][] = '';
                            $recordData['file'][$col['item']['code']]['description'][] = '';
                            */
                        } else {
                            $recordData['value'][$col['item']['code']] = $value;
                        }
                    }
                    $j++;
                }
                $item->save($recordData);
            }
        }
        if($error === 0) {
            return true;
        } else {
            return $error;
        }
    }

}
