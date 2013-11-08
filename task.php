<?php

class Rest extends RestEasy {
	public $table = 'task';
	public $idField = 'id';
	public function __construct() {
		$this->add('id', 'int');
		$this->add('label', 'string', false);
		$this->add('description', 'string', false);
		$this->add('start', 'number', false);
		$this->add('end', 'number', false);
		$this->add('duration', 'number', false);
		$this->add('inProgress', 'bool', false);
		$this->add('parentTaskId', 'number', false);
	}
}
?>