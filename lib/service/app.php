<?php
// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}

$realm = 'Restricted area';

//user => password
$users = array('app' => 'app');

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

    die($realm);
}

// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[$data['username']])) {
    die('Wrong credentials!');
}

// generate the valid response
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

//echo '<xmp>'; var_dump($data, $valid_response); exit;

if ($data['response'] != $valid_response) {
    die('Wrong credentials!');
}

define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";
$db = Database::connect();
$session->lang_id = 1;

Config::setVar('CURRENT_DOMAIN_URL', ROOT);
Config::setVar('CURRENT_LANG_CODE', 'en');

$return = array();
$datetime = isset($_GET['datetime']) && preg_match('/[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $_GET['datetime']) ? $_GET['datetime'] : "";

if($datetime) {

    $query = "SELECT COUNT(st1.section_id) AS cnt
              FROM ".Config::db_table_section_text()." st1
              LEFT JOIN ".Config::db_table_section()." s1 ON (st1.section_id = s1.section_id)
              LEFT JOIN ".Config::db_table_tree()." t1 ON (st1.section_id = t1.section_id)
              WHERE st1.section_text_id = (
				        SELECT MAX(st2.section_text_id)
				        FROM ".Config::db_table_section_text()." st2
				        LEFT JOIN ".Config::db_table_tree()." t2 ON (st2.section_id = t2.section_id)
				        WHERE st2.section_id = st1.section_id AND
				        	  st2.lang_id = st1.lang_id AND
							  t2.main = '1') AND
                    s1.domain_id = ".$session->domain_id." AND
                    st1.lang_id = ".$session->lang_id." AND
                    st1.updated > '".$datetime."' AND
                    t1.main = '1' AND
					s1.template IN ('issue', 'project-detail')";
    $count = $db->select($query, true, "cnt");
	if($count > 0) {
		$sectionList = new SectionList();

		$projects = $sectionList->getSectionsByTemplate("project-detail");
		$return['projects'] = array();
		foreach($projects as $section_id => $name) {
			$section = new Section($section_id);
			$image = $section->get('file', 'image');
			$banner = $section->get('file', 'banner');
			$return['projects'][$section_id]['status'] = $section->get('section', 'status');
			$return['projects'][$section_id]['url'] = $section->get('url');
			$return['projects'][$section_id]['name'] = $section->get('section', 'name');
			$return['projects'][$section_id]['image'] = $image ? FILES."w394/".$image[0]['file'] : "";
			$return['projects'][$section_id]['banner'] = $image ? FILES."960/".$banner[0]['file'] : "";
			$return['projects'][$section_id]['background'] = $section->get('value', 'background');
			$return['projects'][$section_id]['ideas'] = $section->get('value', 'project_drivers');
			$return['projects'][$section_id]['scenario_generation'] = $section->get('value', 'scenario_generation');
			$return['projects'][$section_id]['workforce_modelling'] = $section->get('value', 'workforce_modelling');
			$return['projects'][$section_id]['policy_analysis'] = $section->get('value', 'policy_analysis');
		}

		$issues = $sectionList->getSectionsByTemplate("issue");
		$return['ideas'] = array();
		foreach($issues as $section_id => $name) {
			$section = new Section($section_id);
			$like = $section->get('value', 'like');
			if(is_array($like)) {
				$like = count($like);
			} elseif($like > 0) {
				$like = 1;
			} else {
				$like = 0;
			}
			$return['ideas'][$section_id]['status'] = $section->get('section', 'status');
			$return['ideas'][$section_id]['url'] = $section->get('url');
			$return['ideas'][$section_id]['name'] = $section->get('section', 'name');
			$return['ideas'][$section_id]['like'] = $like;
			$return['ideas'][$section_id]['category'] = $section->get('value', 'category');
			$return['ideas'][$section_id]['summary'] = $section->get('value', 'summary');
			$return['ideas'][$section_id]['description'] = $section->get('value', 'description');
			$return['ideas'][$section_id]['frequency'] = $section->get('value', 'frequency');
			$return['ideas'][$section_id]['impact_size'] = $section->get('value', 'impact_size');
			$return['ideas'][$section_id]['uncertainty_level'] = $section->get('value', 'uncertainty_level');
			$return['ideas'][$section_id]['workforce_impact'] = $section->get('value', 'workforce_impact');
			$return['ideas'][$section_id]['came_from'] = $section->get('value', 'came_from');
			//$return['ideas'][$section_id]['source'] = $section->get('value', 'source');
			$return['ideas'][$section_id]['further_questions'] = $section->get('value', 'further_questions');
			$return['ideas'][$section_id]['sector'] = is_array($section->get('value', 'sector')) ? $section->get('value', 'sector') : array($section->get('value', 'sector'));
			$return['ideas'][$section_id]['profession'] = is_array($section->get('value', 'staff')) ? $section->get('value', 'staff') : array($section->get('value', 'staff'));
			$return['ideas'][$section_id]['care'] = $section->get('value', 'safety') + $section->get('value', 'effectiveness') + $section->get('value', 'experience');
			$return['ideas'][$section_id]['popularity'] = $section->get('section', 'views');
			$return['ideas'][$section_id]['inserted'] = $section->get('section', 'inserted');
			$return['ideas'][$section_id]['updated'] = $section->get('section', 'updated');
		}

		$query = "SELECT query
				  FROM ".Config::db_table_log_action()."
				  WHERE module LIKE 'content' AND
				  		action LIKE 'DELETE' AND
				  		query LIKE '% cfwi_section %' AND
				  		inserted > '".$datetime."'";
		$deleted = $db->select($query);
		$return['deleted'] = array();
		foreach($deleted as $row) {
			if(preg_match('/\d+$/', $row['query'], $arr)) {
				$return['deleted'][] = $arr[0];
			}
		}
	}
}

header("Content-type: application/json; charset=utf-8");
echo json_encode($return);
