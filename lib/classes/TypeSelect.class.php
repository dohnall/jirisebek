<?php

class TypeSelect extends TypeDefault {

	public $field = 'varchar';
	public $params = array(
		'disabled' => 'int',
		'multiselect' => 'int',
		'relation' => 'varchar',
		'size' => 'int',
		'selecttype' => 'varchar',
		'values' => 'text',
		'template' => 'varchar',
		'codelist' => 'int',
	);
    protected $template = "select";

    public function getDetail() {
        $this->smarty->assign(array(
            'type_value' => $this->setValueDetail(),
            'type_options' => $this->setOptions(),
        ));
        return $this->smarty->fetch('_detail_'.$this->template.'.html');
    }

    protected function setOptions() {
    	$return = array();
    	switch($this->col['param']['selecttype']) {
			case "values":
				$return = $this->col['param']['values'];
				break;
			case "template":
				$sectionList = new SectionList();
				$return = $sectionList->getSectionsByTemplate($this->col['param']['template'], 1, 0, 'st1.name ASC');
				break;
			case "codelist":
				$codelist = new Codelist($this->col['param']['codelist']);
				$records = $codelist->getRecords();
				foreach($records as $record) {
					$return[$record->get('item', 'code')] = $record->get('item', 'name');
				}
				break;
			case "users":
				$userList = new UserList();
				$records = $userList->getUsers();
				foreach($records as $record) {
					$return[$record['user_id']] = $record['fname'].' '.$record['lname'];
				}
				break;
		}
    	
        return $return;
    }

    protected function setValueDetail() {
        return $this->item->get('value', $this->col['item']['code']);
    }

}
