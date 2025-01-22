<?php

class TypeCheckbox extends TypeDefault {

	public $field = 'int';
	public $params = array(
		"disabled" => "int",
	);
    protected $template = "checkbox";

    protected function setValueDetail() {
        return $this->item->get('value', $this->col['item']['code']) ? 1 : 0;
    }
}
