<?php

class TypeText extends TypeDefault {

	public $field = 'varchar';
	public $params = array(
		"size" => "int",
		"maxlength" => "int",
		"disabled" => "int",
	);
    protected $template = "text";

}
