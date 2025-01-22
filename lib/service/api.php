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
$users = array('fryit' => 'cfWi#386');

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

if ($data['response'] != $valid_response) {
    die('Wrong credentials!');
}

define("MODE", 'WEB');
require_once dirname(dirname(__FILE__))."/config/config.php";
$db = Database::connect();
$session->lang_id = 1;

define("ERR_OK", 0);
define("ERR_UNKNOWN_USER", 1);
define("ERR_EMPTY_PASSWD", 2);
define("ERR_MIN_LENGTH", 3);
define("ERR_LOGIN_FAIL", 4);
define("ERR_USER_DATA", 5);

$return = array();
$action = isset($_GET['action']) ? $_GET['action'] : "";

//authorization
if($action == 'auth') {
	$username = isset($_GET['username']) ? addslashes($_GET['username']) : "";
	$password = isset($_GET['password']) ? addslashes($_GET['password']) : "";

	$query = "SELECT user_id
			  FROM ".Config::db_table_user()."
			  WHERE nickname = '".$username."' AND
			  		passwd = MD5('".$password."') AND
			  		status = 1 AND
					deleted = '0'";
	$user_id = $db->select($query, true, "user_id");
	if($user_id) {
		$status = 1;
		$error = ERR_OK;
	} else {
		$status = 0;
		$error = ERR_LOGIN_FAIL;
	}

	$return = array(
		'status' => $status,
		'username' => $username,
		'error' => $error,
	);
//enumeration
} elseif($action == 'enum') {
	$username = isset($_GET['username']) ? addslashes($_GET['username']) : "";
	$match = isset($_GET['exact_match']) && $_GET['exact_match'] == 0 ? 0 : 1;
	$config = new Config;
	$helper = new Helper;

	if(strlen($username) >= 5) {
		$userOptions = $config->getUserCols();
	
		$query = "SELECT user_id
				  FROM ".Config::db_table_user()."
				  WHERE nickname";
		if($match) {
			$query.= " = '".$username."' AND ";
		} else {
			$query.= " LIKE '".$username."%' AND ";
		}
		$query.= "status = 1 AND
				  deleted = '0'";
		$result = $db->select($query);

		foreach($result as $k => $row) {
			$user = new User($row['user_id']);
			$user->load();
	
			$return[$k]['username'] = $user->nickname;
			$return[$k]['fname'] = $user->fname;
			$return[$k]['lname'] = $user->lname;
			$return[$k]['email'] = $user->email;
			$return[$k]['job'] = $user->get('value', 'job');
			$return[$k]['organization'] = $user->get('value', 'organization');
			$return[$k]['otype'] = isset($userOptions[93]['param']['values'][$user->get('value', 'otype')]) ? $userOptions[93]['param']['values'][$user->get('value', 'otype')] : "";
			$return[$k]['olocation'] = isset($userOptions[16]['param']['values'][$user->get('value', 'olocation')]) ? $userOptions[16]['param']['values'][$user->get('value', 'olocation')] : "";
			$return[$k]['country'] = $helper->codebook('country', $user->get('value', 'country'))->get('item', 'name');;
			$return[$k]['phone'] = $user->get('value', 'phone');
		}
	} else {
		$return = array(
			'status' => 0,
			'username' => $username,
			'error' => ERR_MIN_LENGTH,
		);
	}
//add user
} elseif($action == 'add') {
	$username = isset($_POST['username']) ? addslashes($_POST['username']) : "";
	$password = isset($_POST['password']) ? addslashes($_POST['password']) : "";
	$fname = isset($_POST['fname']) ? addslashes($_POST['fname']) : "";
	$lname = isset($_POST['lname']) ? addslashes($_POST['lname']) : "";
	$email = isset($_POST['email']) ? addslashes($_POST['email']) : "";
	$job = isset($_POST['job']) ? addslashes($_POST['job']) : "";
	$organization = isset($_POST['organization']) ? addslashes($_POST['organization']) : "";
	$otype = isset($_POST['otype']) ? addslashes($_POST['otype']) : "";
	$olocation = isset($_POST['olocation']) ? addslashes($_POST['olocation']) : "";
	$country = isset($_POST['country']) ? addslashes($_POST['country']) : "";
	$phone = isset($_POST['phone']) ? addslashes($_POST['phone']) : "";

	if($country) {
		$query = "SELECT code
				  FROM ".Config::db_table_codelist_record()."
				  WHERE name = '".$country."' AND
				  		codelist_text_id = 1";
		$countryCode = $db->select($query, true, "code");
	} else {
		$countryCode = "";
	}

	if($username && $password) {
	    $user = new User(0);

	    $data['user']['nickname'] = $username;
		$data['user']['passwd'] = md5($password);
	    $data['user']['timezone'] = DEFAULT_TIMEZONE;
	    $data['user']['admin'] = 0;
	    $data['user']['cmslang'] = 'en';
	    $data['user']['status'] = 1;

		$data['value']['fname'] = $fname;
		$data['value']['lname'] = $lname;
		$data['value']['email'] = $email;
		$data['value']['job'] = $job;
		$data['value']['organization'] = $organization;
		$data['value']['otype'] = $otype;
		$data['value']['olocation'] = $olocation;
		$data['value']['country'] = $countryCode;
		$data['value']['phone'] = $phone;

		$data['domain'][] = $session->domain_id;
		$data['group'][$session->domain_id] = 3;

	    $user->save($data);

		$return = array(
			'status' => 1,
			'username' => $username,
			'error' => ERR_OK,
		);
	} else {
		$return = array(
			'status' => 0,
			'username' => $username,
			'error' => ERR_USER_DATA,
		);
	}
//change password
} elseif($action == 'chpwd') {
	$username = isset($_POST['username']) ? addslashes($_POST['username']) : "";
	$password = isset($_POST['password']) ? addslashes($_POST['password']) : "";

	if($password) {
		$query = "UPDATE ".Config::db_table_user()." SET
					passwd = MD5('".$password."')
			  	  WHERE nickname = '".$username."' AND
				  		status = 1 AND
						deleted = '0'";
		$status = $db->update($query);
		if($status) {
			$error = ERR_OK;
		} else {
			$error = ERR_UNKNOWN_USER;
		}
	} else {
		$status = 0;
		$error = ERR_EMPTY_PASSWD;
	}

	$return = array(
		'status' => $status,
		'username' => $username,
		'error' => $error,
	);
//introspection
} elseif($action == 'intro') {
	$query = "SELECT nickname
			  FROM ".Config::db_table_user()."
			  WHERE status = 1 AND
			  		deleted = '0'";
	$result = $db->select($query);
	foreach($result as $row) {
		$return[] = $row['nickname'];
	}
//update user
} elseif($action == 'update') {
	$username = isset($_POST['username']) ? addslashes($_POST['username']) : "";
	$fname = isset($_POST['fname']) ? addslashes($_POST['fname']) : "";
	$lname = isset($_POST['lname']) ? addslashes($_POST['lname']) : "";
	$email = isset($_POST['email']) ? addslashes($_POST['email']) : "";
	$job = isset($_POST['job']) ? addslashes($_POST['job']) : "";
	$organization = isset($_POST['organization']) ? addslashes($_POST['organization']) : "";
	$otype = isset($_POST['otype']) ? addslashes($_POST['otype']) : "";
	$olocation = isset($_POST['olocation']) ? addslashes($_POST['olocation']) : "";
	$country = isset($_POST['country']) ? addslashes($_POST['country']) : "";
	$phone = isset($_POST['phone']) ? addslashes($_POST['phone']) : "";

	if($country) {
		$query = "SELECT code
				  FROM ".Config::db_table_codelist_record()."
				  WHERE name = '".$country."' AND
				  		codelist_text_id = 1";
		$countryCode = $db->select($query, true, "code");
	} else {
		$countryCode = "";
	}

	if($username) {
		$query = "SELECT user_id
				  FROM ".Config::db_table_user()."
				  WHERE nickname = '".$username."' AND
				  		status = 1 AND
						deleted = '0'";
		$user_id = $db->select($query, true, "user_id");

		if($user_id) {
		    $user = new User($user_id);
			$user->load();
			$data = $user->getData();
			$data['value'] = $user->get('value');

			if($fname) {
				$data['user']['fname'] = $fname;
			}
			if($lname) {
				$data['user']['lname'] = $lname;
			}
			if($email) {
				$data['user']['email'] = $email;
			}
			if($job) {
				$data['value']['job'] = $job;
			}
			if($organization) {
				$data['value']['organization'] = $organization;
			}
			if($otype) {
				$data['value']['otype'] = $otype;
			}
			if($olocation) {
				$data['value']['olocation'] = $olocation;
			}
			if($countryCode) {
				$data['value']['country'] = $countryCode;
			}
			if($phone) {
				$data['value']['phone'] = $phone;
			}

		    $user->save($data);

			$return = array(
				'status' => 1,
				'username' => $username,
				'error' => ERR_OK,
			);
		} else {
			$return = array(
				'status' => 0,
				'username' => $username,
				'error' => ERR_UNKNOWN_USER,
			);
		}
	} else {
		$return = array(
			'status' => 0,
			'username' => $username,
			'error' => ERR_USER_DATA,
		);
	}
}

echo json_encode($return);
