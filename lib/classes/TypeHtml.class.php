<?php

class TypeHtml extends TypeDefault {

	public $field = 'text';
	public $params = array(
		"width" => "varchar",
		"height" => "varchar",
	);
    protected $template = "html";

}
