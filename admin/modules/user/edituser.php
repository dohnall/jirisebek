<?php
if(!$this->user->hasRight(19)) {
	$this->session->alert = $this->dictionary['no_right'];
	$this->session->alert_css_class = 'error';
	Common::redirect(CMSROOT."?module=".$this->module);
}

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

$item = new User($item_id);
$item->load();

$groups = $this->user->getData('group');
$group = $groups[$this->session->domain_id];
$same_group = $item->hasGroup($group, $this->session->domain_id);

if(isset($_POST['save'])) {
	if($this->user->hasRight(20) || ($this->user->hasRight(49) && $same_group)) {
	    $v = new Validator($_POST['user']);
	    $v->addRule('nickname', 'required');
	    if(!$item_id) {
	        $v->addRule('passwd', 'required');
	    }
	    $v->addRule('passwd', 'password');
	    $v->addRule('email', 'email');
	    $error = $v->getErrors($v->validate(), $this->dictionary);

		$ul = new UserList();
		$uid = $ul->getUserByEmail($_POST['user']['email'], true);
		if($uid && $uid != $item_id) {
			$error[] = $this->dictionary['email_exists'];
		}

	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	    	$_POST['user']['admin'] = isset($_POST['user']['admin']) ? 1 : 0;

	        $data = $item->getData();
	
	        $newdata = $data;
	        $newdata['user'] = array_merge($newdata['user'], $_POST['user']);
	        if(isset($_POST['item']['value'])) {
				$newdata['value'] = $_POST['item']['value'];
			}
	        if(isset($_POST['item']['file'])) {
				$newdata['file'] = $_POST['item']['file'];
			}
	        $newdata['domain'] = $_POST['domain'];
	        $newdata['group'] = $_POST['group'];
	        $newdata['module'] = isset($_POST['module']) ? $_POST['module'] : array();
	        $newdata['lang'] = isset($_POST['lang']) ? $_POST['lang'] : array();
	        $newdata['section'] = isset($_POST['section']) ? $_POST['section'] : array();
	        if(empty($_POST['user']['passwd'])) {
	            $newdata['user']['passwd'] = $data['user']['passwd'];
	        } else {
	            $newdata['user']['passwd'] = md5($newdata['user']['passwd']);
	        }
	
	        $item->save($newdata);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&submodule=user");
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$langs = scandir(CMSLANG);
$cms_langs = array();
foreach($langs as $lang) {
    if(strlen($lang) == 6) {
        $cms_langs[] = substr($lang, 0, 2);
    }
}

if(isset($this->session->data)) {
    $data = $this->session->data;
} else {
    $data = $item->getData();
}

$moduleList = new ModuleList();
$moduleList->load();

$groupList = new GroupList();
$groups = array();

foreach($this->user->getData("group") as $domain_id => $grp_id) {
    $groups[$domain_id] = $groupList->getRankByGroup($grp_id);
}

$domainList = new DomainList();
$domainList->load();
$domains = $domainList->get();

$userSections = $this->user->getData('section');

$accordion = array();
foreach($domains as $domain_id => $row) {
    $domain = new Domain($domain_id);
    $domain->load();
    $lang_id = $domain->getDefaultLang();
    $domain = $domain->get();

    if(isset($groups[$domain_id])) {
        $groupList->load($groups[$domain_id]);
    } else {
        $groupList->load(max($groups));
    }

    $sections = array();
    if(isset($userSections[$domain_id])) {
        foreach($userSections[$domain_id] as $section_id) {
            $sections[] = new Section($section_id, $domain_id, $lang_id);
        }
    } else {
        $sectionList = new SectionList();
        $sections[] = new Section($sectionList->getHomeId($domain_id, $lang_id), $domain_id, $lang_id);
    }

    $accordion[$domain_id]['domain'] = $domain['item'];
    $accordion[$domain_id]['lang'] = $domain['lang'];
    $accordion[$domain_id]['group'] = $groupList->get();
    $accordion[$domain_id]['module'] = $moduleList->get();
    $accordion[$domain_id]['section'] = $sections;
}

$config = new Config();
$userData = "";
foreach($config->getUserCols() as $col) {
    $classname = "Type".ucfirst(strtolower($col['item']['type']));
    if(class_exists($classname)) {
        $type = new $classname($item, $col);
    } else {
        $type = new TypeDefault($item, $col);
    }
    $userData.= $type->getDetail();
}

$this->smarty->assign(array(
    'item_id' => $item_id,
    'item' => $item,
    'data' => $data,
    'userData' => $userData,
    'cmslang' => $cms_langs,
    'timezones' => timezone_identifiers_list(),
    'accordion' => $accordion,
    'same_group' => $same_group,
));
