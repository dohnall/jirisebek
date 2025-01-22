<?php

class TypeTextarea extends TypeDefault {

	public $field = 'text';
	public $params = array(
		"width" => "varchar",
		"height" => "varchar",
	);
    protected $template = "textarea";

}
