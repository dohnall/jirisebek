<?php

class TypeDefault {

	public $field = 'varchar';
	public $params = array();
    protected $template = "text";
    
    public function __construct($item, $col) {
        $this->smarty = Smarty::getInstance();
        $this->item = $item;
        $this->col = $col;

        $this->smarty->assign(array(
            'type_item' => $this->item,
            'type_col' => $this->col,
        ));
    }

    public function getDetail() {
        $this->smarty->assign(array(
            'type_value' => $this->setValueDetail(),
        ));
        return $this->smarty->fetch('_detail_'.$this->template.'.html');
    }

    protected function setValueDetail() {
        return $this->item->get('value', $this->col['item']['code']);
    }

}
