<?php
require_once dirname(dirname(dirname(__FILE__)))."/lib/config/config.php";
require_once CMSAJAXLOCAL."common.php";

$db->module = 'content';

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
$domain_id = isset($_GET['domain']) && is_numeric($_GET['domain']) ? $_GET['domain'] : $session->domain_id;
$lang_id = isset($_GET['lang']) && is_numeric($_GET['lang']) ? $_GET['lang'] : $session->lang_id;
$version = isset($_GET['version']) && is_numeric($_GET['version']) && $user->hasRight(35) ? $_GET['version'] : 0;

$item = new Section($item_id, $domain_id, $lang_id);
if($version) {
	$item->setParams(array(
		'version' => $version,
	));
}

$config = new Config();

$smarty->assign(array(
    'ROOT' => CMSROOT,
    'DESIGN' => CMSDESIGN,
    'FILES' => FILES,
    'user' => $user,
    'item' => $item,
    'MODULE' => 'content',
    'version' => $version,
    'HELPER' => new Helper(),
));

//SECTION LIST
if($action == "list") {
	$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

	if(isset($session->search) && $session->search) {
		$children = array();
		$search = new Search($session->search, $item_id);
		$search->process();
		$result = $search->getResult();

		$count = count($result);

		$pagerObj = new Pager($count, Config::PERPAGE, $page);
		$pagerObj->process();
		$pager = $pagerObj->getPager();

		$result = $search->getResult($pager['from'], Config::PERPAGE);

		foreach($result as $section_id) {
			$children[] = new Section($section_id);
		}

	} else {
		$count = $item->hasChildren();

		$pagerObj = new Pager($count, Config::PERPAGE, $page);
		$pagerObj->process();
		$pager = $pagerObj->getPager();
	
		$item->setParams(array(
			'limit' => Config::PERPAGE,
			'from' => $pager['from'],
		));

		$children = $item->get('children', true, true);
	}

	$smarty->assign(array(
		'pager' => $pager,
		'children' => $children,
		'count' => $count,
		'search' => $session->search,
	));
    $return = $smarty->fetch('ajax_content_list.html');
//SECTION DETAIL
} elseif(substr($action, 0, 3) == "tab") {
    $tab = substr($action, 3);

    $data = "";
    foreach($config->getCols($tab) as $col) {
        $classname = "Type".ucfirst(strtolower($col['item']['type']));
        if(class_exists($classname)) {
            $type = new $classname($item, $col);
        } else {
            $type = new TypeDefault($item, $col);
        }
        $data.= $type->getDetail();
    }

    $smarty->assign(array(
        'data' => $data,
        'tab' => $tab,
    ));
    $return = $smarty->fetch('ajax_content_detail.html');
//SECTION PROPERTIES
} elseif($action == "properties") {
	$config = new Config();

	$sectionList = new SectionList();
	$homeId = $sectionList->getHomeId(0, $lang_id);
	$homeSection = new Section($homeId);

	$groups = new GroupList();
	$groups->load();

	$templates = $config->getTemplates(true);
	$template_show = false;
	foreach($templates as $row) {
		if($item->get('section', 'template') == $row['code']) {
			$template_show = true;
		}
	}

    $smarty->assign(array(
        'tab' => 'properties',
        'templates' => $templates,
        'template_show' => $template_show,
	    'home' => $homeSection,
	    'homeId' => $homeId,
	    'groups' => $groups->get(),
	    'config' => $config,
    ));
    $return = $smarty->fetch('ajax_content_properties.html');
//SECTION RELATIONS
} elseif($action == "relations") {
	$config = new Config();
	$sectionList = new SectionList();
	$homeId = $sectionList->getHomeId(0, $lang_id);
	$homeSection = new Section($homeId);

    $smarty->assign(array(
	    'home' => $homeSection,
	    'homeId' => $homeId,
	    'config' => $config,
    ));

    $return = $smarty->fetch('ajax_content_relations.html');
//SECTION VERSIONS
} elseif($action == "versions") {
    $return = $smarty->fetch('ajax_content_versions.html');
//SECTION LOG
} elseif($action == "log") {
    $return = $smarty->fetch('ajax_content_log.html');
//TREE
} elseif($action == "tree") {
    $return = $smarty->fetch('ajax_content_tree.html');
//USER SECTIONS
} elseif($action == "user_sections") {
    $return = $smarty->fetch('ajax_content_user_sections.html');
//CHANGE RANK
} elseif($action == "changeRank") {
	if($user->hasRight(41)) {
		$db->submodule = 'index';
		$to = isset($_GET['to']) && is_numeric($_GET['to']) ? $_GET['to'] : 0;
		$parent = isset($_GET['parent']) && is_numeric($_GET['parent']) ? $_GET['parent'] : 0;
		if($item_id != $to) {
			$item = new Section($item_id, $domain_id, $lang_id, $parent);
			$item->setRank($parent, $to);
		}
	}
} elseif($action == "changeRank2") {
	if($user->hasRight(41)) {
		$db->submodule = 'index';
		$to = isset($_GET['to']) && is_numeric($_GET['to']) ? $_GET['to'] : 0;
		$parent = isset($_GET['parent']) && is_numeric($_GET['parent']) ? $_GET['parent'] : 0;
		$query = "SELECT section_id
				  FROM ".Config::db_table_tree()."
				  WHERE parent_id = ".$parent." AND
				  		rank = ".$to;
		$sid = $db->select($query, true, 'section_id');
		if($sid && $item_id != $sid) {
			$item = new Section($item_id, $domain_id, $lang_id, $parent);
			$item->setRank($parent, $sid);
		}
	}
//SET MAIN RELATION
} elseif($action == "mainRelation") {
	if($user->hasRight(37)) {
		$db->submodule = 'index';
		$relation = isset($_GET['relation']) && is_numeric($_GET['relation']) ? $_GET['relation'] : 0;
		$item = new Section($item_id);
		$item->setMainRelation($relation);
	}
//IMAGE MANAGER
} elseif($action == "imageManager") {
    $return = $smarty->fetch('ajax_content_image_manager.html');
//IMAGE MANAGER LOAD FILES
} elseif($action == "imageManagerLoadFiles") {
	$dir = dir(LOCALFILES);
	$files = array();
	while($file = $dir->read()) {
		if(!in_array($file, array('.', '..'))) {
			$parts = explode('.', $file);
			if(in_array(strtolower($parts[count($parts)-1]), array('gif', 'png', 'jpg', 'jpeg'))) {
				$files[] = $file;
			}
		}
	}
	sort($files);
	$smarty->assign(array(
		'files' => $files,
	));
    $return = $smarty->fetch('ajax_content_image_manager_ul.html');
//FILE MANAGER
} elseif($action == "fileManager") {
    $return = $smarty->fetch('ajax_content_file_manager.html');
//FILE MANAGER LOAD FILES
} elseif($action == "fileManagerLoadFiles") {
	$dir = dir(LOCALFILES);
	$files = array();
	while($file = $dir->read()) {
		if(!in_array($file, array('.', '..')) && is_file(LOCALFILES.$file)) {
			$files[] = $file;
		}
	}
	sort($files);
	foreach($files as $k => $file) {
		$files[$k] = new File($file);
	}
	$smarty->assign(array(
		'files' => $files,
	));
    $return = $smarty->fetch('ajax_content_file_manager_ul.html');
}

echo $return;
