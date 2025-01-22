<?php

class CMS {

    private $domains = array();
    private $modules = array();
    private $module = "login";
    private $submodule = "index";
    private $mainTemplate = "main";

    private $user = null;
    private $user_data = array();

    private $dictionary = array();

    public function __construct() {
        $this->smarty = Smarty::getInstance();
        $this->smarty->template_dir = CMSTEMPLATES;
        $this->smarty->compile_dir = CMSTEMPLATESC;
        $this->db = Database::connect();
        $this->session = Session::getInstance(MODE);
        array_walk_recursive($_GET, "clean");
        array_walk_recursive($_POST, "clean");
    }

    public static function start() {
		if(DEBUGGER === true) {
			require_once SERVICE."nette.min.php";
			NDebugger::enable();
			NDebugger::$strictMode = TRUE;
		}
        $cms = new CMS();
        $cms->load();
        $cms->show();
    }

    public function load() {
        if(isset($this->session->user_id)) {
            $this->user = new User($this->session->user_id);
            $this->user->setAction();
            $this->user->load();
            $this->setDomain();
            $this->setLang();
            $this->loadDomains();
            $this->loadModules();
            $this->setModule();
            $this->setSubmodule();

            $this->db->log = LOG_ACTION;
            $this->db->module = $this->module;
            $this->db->submodule = $this->submodule;
            if(LOG_ACTION) {
				Logger::logView($this->module, $this->submodule);
			}

			if($this->user->timezone) {
				Config::setVar('USER_TIMEZONE', $this->user->timezone);
			} else {
				Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
			}

            $this->smarty->configLoad(CMSLANG.$this->user->cmslang.".ini");
        } else {
        	Config::setVar('USER_TIMEZONE', DEFAULT_TIMEZONE);
            $this->smarty->configLoad(CMSLANG.DEFAULT_CMS_LANG.".ini");
        }
        $this->dictionary = $this->smarty->getConfigVars();

    	$userList = new UserList();
        $this->smarty->assign(array(
        	'COMPANY' => COMPANY,
            'ROOT' => CMSROOT,
            'ROOT_FRONTEND' => ROOT,
            'AJAX' => CMSAJAX,
            'DESIGN' => CMSDESIGN,
            'JS' => CMSJS,
            'FILES' => FILES,
            'DOMAINS' => $this->domains,
            'DOMAIN' => $this->session->domain_id,
            'MODULES' => $this->modules,
            'MODULE' => $this->module,
            'SUBMODULE' => $this->submodule,
            'LANG' => $this->session->lang_id,
            'HELPER' => new Helper(),
            'user_id' => $this->session->user_id,
            'user' => $this->user,
            'logged_users' => $userList->getActiveUsers(),
            'alert' => $this->session->alert,
            'alert_css_class' => (!empty($this->session->alert_css_class) ? $this->session->alert_css_class : 'notice'),
            'dictionary' => $this->dictionary,
        ));

        if(file_exists(CMSMODULES.$this->module."/common.php")) {
            include_once CMSMODULES.$this->module."/common.php";
        }
        if(file_exists(CMSMODULES.$this->module."/".$this->submodule.".php")) {
            include_once CMSMODULES.$this->module."/".$this->submodule.".php";
        }
    }

    public function show() {
        $this->smarty->display($this->mainTemplate.".html");
        unset($this->session->data);
        unset($this->session->alert);
    }

    private function setLang() {
        $langs = $this->user->getData('lang');
        if(!isset($this->session->lang_id) && isset($_COOKIE['content_lang']) && $this->user->hasLang($_COOKIE['content_lang'])) {
			$this->session->lang_id = $_COOKIE['content_lang'];
		} elseif(!isset($this->session->lang_id) || !$this->user->hasLang($this->session->lang_id)) {
            if(!isset($this->session->dictionary_lang) || !$this->user->hasLang($this->session->dictionary_lang)) {
                $this->session->lang_id = current($langs[$this->session->domain_id]);
            } else {
                $this->session->lang_id = $this->session->dictionary_lang;
            }
        }
        $langList = new LangList();
        $lang = $langList->getLangById($this->session->lang_id);
        Config::setVar('CURRENT_LANG_CODE', $lang['code']);
    }

    private function setDomain() {
        if(isset($_POST['change_domain']) && $this->user->hasDomain($_POST['change_domain'])) {
            $this->session->domain_id = $_POST['change_domain'];
            $domain = new Domain($this->session->domain_id);
            $this->session->default_lang_id = $domain->getDefaultLang();
            Common::redirect(CMSROOT);
        } elseif(!isset($this->session->default_lang_id)) {
			$domain = new Domain($this->session->domain_id);
            $this->session->default_lang_id = $domain->getDefaultLang();
		}
        $domainList = new DomainList();
        $domain = $domainList->getDomainById($this->session->domain_id);
        Config::setVar('CURRENT_DOMAIN_URL', ROOT);
    }

    private function loadDomains() {
        $domains = new DomainList();
        $data = $this->user->getData("domain");
        $domains->load($data);
        $this->domains = $domains->get();
    }

    private function loadModules() {
        $modules = new ModuleList();
        $data = $this->user->getData("module");
        if(isset($data[$this->session->domain_id])) {
            $modules->load($data[$this->session->domain_id]);
            $this->modules = $modules->get();
        }
    }

    private function setModule() {
        $module = isset($_GET['module']) ? $_GET['module'] : $this->module;
        if($this->user->hasModule($module)) {
            $this->module = $module;
        } else {
            $data = $this->user->getData("module");
            if(isset($data[$this->session->domain_id])) {
                $modules = array_keys($data[$this->session->domain_id]);
                if($modules) {
                    $this->module = $modules[0];
                }
            }
        }
    }

    private function setSubmodule() {
        if(isset($_GET['submodule']) && file_exists(CMSMODULES.$this->module."/".$_GET['submodule'].".php")) {
            $this->submodule = $_GET['submodule'];
        }
    }

}
