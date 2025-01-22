<?php
if(isset($_POST['content_lang']) && $this->user->hasLang($_POST['content_lang'])) {
    $this->session->lang_id = $_POST['content_lang'];
    setcookie("content_lang", $this->session->lang_id, time()+60*60*24*365, "/");
    Common::redirect();
}

$userSections = $this->user->getData('section');
$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
$version = isset($_GET['version']) && is_numeric($_GET['version']) && $this->user->hasRight(35) ? $_GET['version'] : 0;
$config = new Config();

$tree = array();
if(isset($userSections[$this->session->domain_id])) {
    foreach($userSections[$this->session->domain_id] as $section_id) {
        if(!$item_id || !$this->user->hasSection($item_id)) {
            $item_id = $section_id;
        }
        $tree[] = new Section($section_id);
    }
}

//vytvorit novou polozku
if(isset($_POST['action']) && $_POST['action'] == "create") {
	$item = new Section($item_id);
	if($this->user->hasRight(23) && $this->user->hasRight(27) && $config->hasTemplateChildren($item->get('section', 'template'))) {
	    $this->session->content_insert = $_POST['insert'];
	    $v = new Validator($_POST);
	    $v->addRule('name', 'required');
	    $error = $v->getErrors($v->validate(), $this->dictionary);
	    if($error) {
	        $this->session->alert = implode('<br />', $error);
	        $this->session->alert_css_class = 'error';
	        $this->session->data = $_POST;
	        Common::redirect();
	    } else {
	        $section = new Section(0);
	        $section_id = $section->create($_POST);
	        $this->session->alert = $this->dictionary['item_saved'];
	        $this->session->alert_css_class = 'success left-icon';
	        Common::redirect(CMSROOT."?module=".$this->module."&id=".$section_id);
	    }
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_POST['action']) && $_POST['action'] == "export") {
	if($this->user->hasRight(44)) {
		$sectionList = new SectionList();
		$sectionList->export($_POST['template'], $_POST['type'], $_POST['parent']);
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
} elseif(isset($_POST['action']) && $_POST['action'] == "import") {
	if($this->user->hasRight(45)) {
	    $files = $_FILES['import'];
	    if($files['error'] == 0) {
	        $data = file($files['tmp_name']);
			$sectionList = new SectionList();
			$result = $sectionList->import($item_id, $_POST['template'], $data);
	        if($result === true) {
	            $this->session->alert = $this->dictionary['import_complete'];
	            $this->session->alert_css_class = 'success left-icon';
	        } else {
	            $this->session->alert = sprintf($this->dictionary['import_failed'], $result);
	            $this->session->alert_css_class = 'alert left-icon';
	        }
	    } else {
	        $this->session->alert = $this->dictionary['import_error'];
	        $this->session->alert_css_class = 'error left-icon';
	    }
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//smazat
} elseif(isset($_GET['action']) && $_GET['action'] == "delete") {
	if($this->user->hasRight(24)) {
		$item = new Section($item_id);
		$parent_id = $item->get('section', 'parent_id');
		$item->delete();
		$this->session->alert = $this->dictionary['item_deleted'];
		$this->session->alert_css_class = 'success left-icon';
		Common::redirect(CMSROOT."?module=".$this->module."&id=".$parent_id);
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//smazat verzi
} elseif(isset($_GET['action']) && $_GET['action'] == "delete_version") {
	if($this->user->hasRight(34)) {
		$version = isset($_GET['version']) && is_numeric($_GET['version']) ? $_GET['version'] : 0;
	    $item = new Section($item_id);
	    if($item->hasVersion() > 1) {
		    $item->deleteVersion($version);
		    $this->session->alert = $this->dictionary['version_deleted'];
		    $this->session->alert_css_class = 'success left-icon';
		}
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//aktivovat
} elseif(isset($_GET['action']) && $_GET['action'] == "activate") {
	if($this->user->hasRight(25)) {
		$sectionList = new SectionList();
		$sectionList->activateItems(array($item_id));
		$this->session->alert = $this->dictionary['item_activated'];
		$this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//deaktivovat
} elseif(isset($_GET['action']) && $_GET['action'] == "deactivate") {
	if($this->user->hasRight(25)) {
		$sectionList = new SectionList();
		$sectionList->deactivateItems(array($item_id));
		$this->session->alert = $this->dictionary['item_deactivated'];
		$this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//vytvorit kopii
} elseif(isset($_GET['action']) && $_GET['action'] == "copy") {
	if($this->user->hasRight(26)) {
		$item = new Section($item_id);
		$item->copy();
		$this->session->alert = $this->dictionary['item_copied'];
		$this->session->alert_css_class = 'success left-icon';
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//filtr dle vyrazu
} elseif(isset($_POST['filter'])) {
	if($_POST['search'][0]) {
		$this->session->search = $_POST['search'];
	} else {
		unset($this->session->search);
	}
	Common::redirect();
//pridat vazbu
} elseif(isset($_POST['action']) && $_POST['action'] == "addRelation") {
	if($this->user->hasRight(37)) {
	    $item = new Section($item_id);
	    $item->addRelation($_POST['section']);
	    $this->session->alert = $this->dictionary['relation_added'];
	    $this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//smazat vazbu
} elseif(isset($_GET['action']) && $_GET['action'] == "deleteRelation") {
	if($this->user->hasRight(37)) {
		$relation = isset($_GET['relation']) && is_numeric($_GET['relation']) ? $_GET['relation'] : 0;
	    $item = new Section($item_id);
	    $item->deleteRelation($relation);
	    $this->session->alert = $this->dictionary['relation_deleted'];
	    $this->session->alert_css_class = 'success left-icon';
		Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

//hromadna akce
if(isset($_POST['common_action']) && $_POST['common_action'] > 0) {
    switch($_POST['common_action']) {
        //aktivovat
        case 1:
			if($this->user->hasRight(25)) {
				if(isset($_POST['check'])) {
					$sectionList = new SectionList();
					$sectionList->activateItems($_POST['check']);
				}
			} else {
				$this->session->alert = $this->dictionary['no_right'];
				$this->session->alert_css_class = 'error';
				Common::redirect(CMSROOT."?module=".$this->module);
			}
			break;
        //deaktivovat
        case 2:
			if($this->user->hasRight(25)) {
				if(isset($_POST['check'])) {
					$sectionList = new SectionList();
					$sectionList->deactivateItems($_POST['check']);
				}
			} else {
				$this->session->alert = $this->dictionary['no_right'];
				$this->session->alert_css_class = 'error';
				Common::redirect(CMSROOT."?module=".$this->module);
			}
			break;
        //smazat
        case 3:
			if($this->user->hasRight(24)) {
				if(isset($_POST['check'])) {
					foreach($_POST['check'] as $item_id) {
						$item = new Section($item_id);
						$item->delete();
					}
				}
			} else {
				$this->session->alert = $this->dictionary['no_right'];
				$this->session->alert_css_class = 'error';
				Common::redirect(CMSROOT."?module=".$this->module);
			}
			break;
    }
    Common::redirect();
//ulozit
} elseif(isset($_POST['save'])) {
	if($this->user->hasRight(27)) {
		$version = isset($_POST['version']) && is_numeric($_POST['version']) && $this->user->hasRight(35) ? $_POST['version'] : 0; 
	    $_POST['item']['text']['updated'] = date('Y-m-d H:i:s');
		$item = new Section($_POST['item_id']);
		if($version) {
			$item->setParams(array(
				'version' => $version,
			));
		}

        if(isset($_FILES['file'])) {
        	foreach($_FILES['file']['error'] as $code => $file) {
        		foreach($file as $k => $error) {
					if($error == 0) {
						$parts = explode('.', $_FILES['file']['name'][$code][$k]);
						$ext = array_pop($parts);
						$ext = strtolower($ext);
                        $hash = md5_file($_FILES['file']['tmp_name'][$code][$k]);
						$filename = $hash.'.'.$ext;
						move_uploaded_file($_FILES['file']['tmp_name'][$code][$k], LOCALFILES.$filename);
						$_POST['item']['file'][$code]['file'][$k] = $_FILES['file']['name'][$code][$k];
						$_POST['item']['file'][$code]['hash'][$k] = $hash;
                        $_POST['item']['file'][$code]['description'][$k] = isset($_POST['item']['file'][$code]['description'][$k]) && $_POST['item']['file'][$code]['description'][$k] ? $_POST['item']['file'][$code]['description'][$k] : $_FILES['file']['name'][$code][$k];

						rename(LOCALFILES.$filename, LOCALFILES.substr($hash, 0, 2).DS.$hash.'.'.$ext);
					}
				}
			}
		}

	    $item->save($_POST['item']);
	    $this->session->alert = $this->dictionary['item_saved'];
	    $this->session->alert_css_class = 'success left-icon';
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//ulozit a publikovat
} elseif(isset($_POST['save_publicate'])) {
	if($this->user->hasRight(25) && $this->user->hasRight(27)) {
		$version = isset($_POST['version']) && is_numeric($_POST['version']) && $this->user->hasRight(35) ? $_POST['version'] : 0;
		$_POST['item']['text']['status'] = 1;
	    $item = new Section($_POST['item_id']);
		if($version) {
			$item->setParams(array(
				'version' => $version,
			));
		}

        if(isset($_FILES['file'])) {
        	foreach($_FILES['file']['error'] as $code => $file) {
        		foreach($file as $k => $error) {
					if($error == 0) {
						$parts = explode('.', $_FILES['file']['name'][$code][$k]);
						$ext = array_pop($parts);
						$ext = strtolower($ext);
                        $hash = md5_file($_FILES['file']['tmp_name'][$code][$k]);
                        $filename = $hash.'.'.$ext;
                        move_uploaded_file($_FILES['file']['tmp_name'][$code][$k], LOCALFILES.$filename);
						$_POST['item']['file'][$code]['file'][$k] = $_FILES['file']['name'][$code][$k];
						$_POST['item']['file'][$code]['hash'][$k] = $hash;
                        $_POST['item']['file'][$code]['description'][$k] = isset($_POST['item']['file'][$code]['description'][$k]) && $_POST['item']['file'][$code]['description'][$k] ? $_POST['item']['file'][$code]['description'][$k] : $_FILES['file']['name'][$code][$k];

						rename(LOCALFILES.$filename, LOCALFILES.substr($hash, 0, 2).DS.$hash.'.'.$ext);
					}
				}
			}
		}

	    $item->save($_POST['item']);
	    $this->session->alert = $this->dictionary['item_saved'];
	    $this->session->alert_css_class = 'success left-icon';
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
//ulozit jako novou verzi
} elseif(isset($_POST['save_version'])) {
	if($this->user->hasRight(27) && $this->user->hasRight(33)) {
	    $item = new Section($_POST['item_id']);

        if(isset($_FILES['file'])) {
        	foreach($_FILES['file']['error'] as $code => $file) {
        		foreach($file as $k => $error) {
					if($error == 0) {
						$parts = explode('.', $_FILES['file']['name'][$code][$k]);
						$ext = array_pop($parts);
						$ext = strtolower($ext);
                        $hash = md5_file($_FILES['file']['tmp_name'][$code][$k]);
                        $filename = $hash.'.'.$ext;
                        move_uploaded_file($_FILES['file']['tmp_name'][$code][$k], LOCALFILES.$filename);
						$_POST['item']['file'][$code]['file'][$k] = $_FILES['file']['name'][$code][$k];
						$_POST['item']['file'][$code]['hash'][$k] = $hash;
                        $_POST['item']['file'][$code]['description'][$k] = isset($_POST['item']['file'][$code]['description'][$k]) && $_POST['item']['file'][$code]['description'][$k] ? $_POST['item']['file'][$code]['description'][$k] : $_FILES['file']['name'][$code][$k];

						rename(LOCALFILES.$filename, LOCALFILES.substr($hash, 0, 2).DS.$hash.'.'.$ext);
					}
				}
			}
		}

	    $item->save($_POST['item'], true);
	    $this->session->alert = $this->dictionary['version_saved'];
	    $this->session->alert_css_class = 'success left-icon';
	    Common::redirect();
	} else {
		$this->session->alert = $this->dictionary['no_right'];
		$this->session->alert_css_class = 'error';
		Common::redirect(CMSROOT."?module=".$this->module);
	}
}

$langs = $this->user->getData('lang');
$langList = new LangList();
$langList->load($langs[$this->session->domain_id]);

$item = new Section($item_id);
if($version) {
	$item->setParams(array(
		'version' => $version,
	));
}

$this->smarty->assign(array(
    'tree' => $tree,
    'item_id' => $item_id,
    'item' => $item,
    'langs' => $langList->get(),
    'insert' => $this->session->content_insert,
    'config' => $config,
    'version' => $version,
));
