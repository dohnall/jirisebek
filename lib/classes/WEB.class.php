<?php

class WEB {

    private $page = 1;
    private $domains = array();
    private $mainTemplate = "main.html";
    private $dictionary = array();
    private $menus = array();
    private $components = array();
    private $pagers = array();
    private $section = null;
    private $home = null;
    private $isAjax = false;
    private $user = null;
    private $helper = null;
	private $params = array();

    public function __construct() {
        $this->smarty = Smarty::getInstance();
        $this->smarty->template_dir = TEMPLATES;
        $this->smarty->compile_dir = TEMPLATESC;
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
        $this->preview = isset($_GET['action']) && $_GET['action'] == 'preview' ? 1 : 0;
        array_walk_recursive($_GET, "clean");
        array_walk_recursive($_POST, "clean");
    }

    public static function start() {
		if(DEBUGGER === true) {
			require_once SERVICE."nette.min.php";
			NDebugger::enable();
			NDebugger::$strictMode = TRUE;
		}
        $web = new WEB();
        $web->load();
        $web->show();
    }

    public function load() {
		$this->setPath();
    	$this->setDomain();
        $this->setLang();
        $this->setSection();
        $this->setUser();
        $this->loadDictionary();
        $this->checkVisibility();
        $this->setPage();
        $this->setTemplate();
        $this->setHelper();
		if(!$this->isAjax()) {
	        $this->loadMenus();
	        $this->loadComponents();
	        $this->loadPagers();
		}

        if(file_exists(SCRIPTS."common.php")) {
            include_once SCRIPTS."common.php";
        }
        if(file_exists(SCRIPTS.$this->template."_".$this->langCode.".php")) {
            include_once SCRIPTS.$this->template."_".$this->langCode.".php";
        } elseif(file_exists(SCRIPTS.$this->template.".php")) {
            include_once SCRIPTS.$this->template.".php";
        }
    }

    public function show() {
        if($this->smarty->templateExists($this->template."_".$this->langCode.".html")) {
            $template_lang = $this->template."_".$this->langCode.".html";
        } else {
            $template_lang = $this->template.".html";
        }

        $this->smarty->assign(array(
            'ROOT' => ROOT,
            'DESIGN' => DESIGN,
            'JS' => JS,
            'FILES' => FILES,
            'LANG' => $this->session->lang_id,
            'LANG_CODE' => $this->langCode,
            'DOMAIN' => $this->session->domain_id,
            'HELPER' => $this->helper,
            'USER_ID' => $this->session->user_id,
            'USER' => $this->user,
            'SECTION' => $this->section,
            'HOME' => $this->home,
            'TEMPLATE' => $this->template,
            'TEMPLATE_LANG' => $template_lang,
            'MENUS' => $this->menus,
            'COMPONENTS' => $this->components,
            'DICTIONARY' => $this->dictionary,
            'PAGERS' => $this->pagers,
            'ALERT' => $this->session->alert,
            'ALERT_TYPE' => (!empty($this->session->alert_type) ? $this->session->alert_type : 'danger'),
        ));

		if($this->isAjax()) {
			$this->mainTemplate = $template_lang;
		}

		$content = $this->smarty->fetch($this->mainTemplate);
		if(preg_match_all('/\{([A-Z]){1}:([\d]+)\}/', $content, $arr)) {
			$content = $this->parseDynamicVariables($content, $arr);
			$this->smarty->display('string:'.$content);
		} else {
			$this->smarty->display($this->mainTemplate);
		}

        //unset($this->session->data);
        unset($this->session->alert);
        unset($this->session->alert_type);
    }

	private function isAjax() {
		return isset($_GET['ajax']) && $_GET['ajax'] == 'ajax' ? true : false;
	}

	private function setPath() {
		$this->path = isset($_GET['path']) ? rtrim($_GET['path'], '/') : "";
		$urlParts = explode('/', $this->path);
		$this->sectionUrl = array_pop($urlParts);
	}

	private function setDomain() {
		$this->session->domain_id = DOMAINID;
		Config::setVar('CURRENT_DOMAIN_URL', ROOT);
	}

    private function setLang() {
        $langList = new LangList();
        $domain = new Domain($this->session->domain_id);
        $this->session->default_lang_id = $domain->getDefaultLang();

        $lang = isset($_GET['lang']) ? $_GET['lang'] : "";

        if(!empty($lang)) {
            $lang_id = $langList->getLangByCode($lang);
            if($lang_id == $this->session->default_lang_id) {
                Common::redirect(ROOT.$this->path, 301);
            }
            $domain->load();
            if(!$domain->hasLang($lang_id) || empty($lang_id)) {
				Common::redirect(PAGE404, 404);
			} else {
				$this->session->lang_id = $lang_id;
			}
        } else {
            $this->session->lang_id = $this->session->default_lang_id;
        }
        $lang = new Lang($this->session->lang_id);
        $lang->load();
        $langData = $lang->get();
        $this->langCode = $langData['item']['code'];
        Config::setVar('CURRENT_LANG_CODE', $this->langCode);
    }

	private function setPage() {
        if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) {
            $this->page = (int)$_GET['page'];
        }
	}

	private function setSection() {
		$sectionList = new SectionList();
		$this->home = Section::getInstance($sectionList->getHomeId());

		//homepage
		if(empty($this->path)) {
			$this->section = clone $this->home;
		//subpage
		} else {
			$sections = $sectionList->getSectionsByUrl($this->sectionUrl, $this->preview);
			foreach($sections as $section_id) {
				$section = Section::getInstance($section_id);
				$lang_code = !URL_LANG || $this->session->lang_id == $this->session->default_lang_id ? "" : $this->langCode."/";
                if($section->get('url') == ROOT.$lang_code.$this->path."/" && ($section->isActive() || $this->preview)) {
                	$this->section = $section;
                    break;
                }
			}
		}
		if(is_null($this->section)) {
            Common::redirect(PAGE404, 404);
            $params = explode("/", $this->path);
            $this->params[] = array_pop($params);
            $params = implode("/", $params);
            $this->path = $params;
			$urlParts = explode('/', $this->path);
			$this->sectionUrl = array_pop($urlParts);
            $this->setSection();
		} else {
			$this->section->setViews();
		}
	}

	private function setUser() {
		if(!isset($this->session->user_id) && isset($_COOKIE['user']) && strlen($_COOKIE['user']) == 32) {
			$userList = new UserList();
			$user_id = $userList->getUserByHash($_COOKIE['user']);
			if($user_id) {
				$this->session->user_id = $user_id;
				setcookie('user', $_COOKIE['user'], time() + 3600 * 24 * 365, '/');

				$user = new User($user_id);
				$user->load();
				if($user->admin) {
			 		$_SESSION['CMS']['user_id'] = $user_id;
				}
			}
		}

		if(isset($this->session->user_id)) {
			$this->user = new User($this->session->user_id);
			$this->user->load();
			Config::setVar('USER_TIMEZONE', $this->user->timezone);
		} else {
			Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
		}
	}

	private function checkVisibility() {
		if(isset($this->session->user_id)) {
			$user = new User($this->session->user_id);
			$user->load();
			$groups = $user->getData('group');
			if(count($this->section->get('visibility')) == 0 || in_array($groups[$this->session->domain_id], $this->section->get('visibility'))) {
				return true;
			}
		} else {
			foreach($this->section->get('path') as $section_id => $section) {
				if(count($section->get('visibility')) > 0 && !in_array(0, $section->get('visibility'))) {
					$this->session->alert = $this->dictionary['no_access'];
					Common::redirect(ROOT);
				}
			}
		}
		return true;
	}

	private function setTemplate() {
		$this->template = $this->section->get('section', 'template');
	}

	private function loadDictionary() {
		if(file_exists(STATOR.$this->langCode.".ini")) {
	    	$this->smarty->configLoad(STATOR.$this->langCode.".ini");
	        $this->dictionary = $this->smarty->getConfigVars();
		} else {
			$this->dictionary = array();
		}
	}

	private function loadMenus() {
		$menuList = new MenuList();
		$menuList->load();
		foreach($menuList->get() as $menu) {
			if($menu['lang_id'] == $this->session->lang_id) {
				$m = new Menu($menu['menu_id']);
				$m->load();
				$this->menus[$menu['code']] = $m->get();
			}
		}
	}

	private function loadComponents() {
		$componentList = new ComponentList();
		$this->components = $componentList->getComponentsByTemplate($this->template);
	}

	private function loadPagers() {
		$componentPager = new ComponentPager();
		$this->pagers = $componentPager->get($this->template, $this->page);
	}

	private function setHelper() {
		$this->helper = new Helper();
	}

	private function parseDynamicVariables($content, $arr) {
		foreach($arr[1] as $k => $type) {
			switch($type) {
				//URL
				case "U":
					$content = str_replace($arr[0][$k], $this->helper->section($arr[2][$k])->get('url'), $content);
					break;
				//NAME
				case "N":
					$content = str_replace($arr[0][$k], $this->helper->section($arr[2][$k])->get('text', 'name'), $content);
					break;
				//REMOVE INVALID VARIABLES
				default:
					$content = str_replace($arr[0][$k], "", $content);
					break;
			}
		}
		return $content;
	}

}
