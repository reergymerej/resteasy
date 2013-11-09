<?php

class Rest extends RestEasy {
	public $table = 'task';
	public $idField = 'id';
	public function __construct() {
		$this->add('id', 'int');
		$this->add('field1');
		$this->add('field2', 'string', false);
		$this->add('field3', 'number', true);
		$this->add('field4', 'bool', true);
	}
}
?>