<?php

// The filename is the "noun" in the url.
// http://somedomain.com/api/example

class Rest extends RestEasy {

	// the table your object is stored in
	public $table = 'sometable';

	// the id field of your object
	public $idField = 'id';

	public function __construct() {

		// Define the fields in your table/object.
		// 	field name
		// 	field type (number/string/boolean - defaults to string)
		// 	required (true/false - defaults to false)
		$this->add('id', 'number', true);
		$this->add('field1');
		$this->add('field2', 'string', true);
		$this->add('field3', 'boolean');
	}
}
?>