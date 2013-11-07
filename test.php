<?php

class FieldDefinition {

	public $name;
	public $type;
	public $required;

	/**
	* @param {String} $name
	* @param {String} [$type='string']
	* @param {Boolean} [$required=false]
	*/
	public function __construct($name, $type = 'string', $required = false) {
		$this->name = $name;
		$this->type = $type;
		$this->required = $required;
	}

	// DEBUG
	public function describe() {
		echo "It looks like...\n";
		foreach($this as $key => $val) {
			echo "$key: $val\n";
		}
		echo "\n";
	}

	/**
	* @param $value
	* @return converted value, wrapped in '' if needed
	*/
	public function castValue($value) {
		switch ($this->type) {
			case 'number':
			case 'int':
			case 'float':
				$value += 0;
				break;
			case 'bool':
				$value = $value ? 1 : 0;
				break;
			case 'string':
			default:
				$value = "'$value'";
				break;
		}

		return $value;
	}
}

class FieldCollection {
	public $fields;

	/**
	* @param {FieldDefinition[]} [$fields]
	*/	
	public function __construct($fields = []) {
		foreach ($fields as $key => $value) {
			$this->fields[$value->name] = $value;
		}
	}

	/**
	* @param {FieldDefinition} $field
	*/
	public function add($field) {
		$this->fields[$field->name] = $field;
	}

	// DEBUG
	public function describe() {
		echo "fields in this collection:\n";
		foreach($this->fields as $key => $field) {
			$field->describe();
		}
	}

	/**
	* select [RETURNS THIS PART] from blah where foo
	* @return {String}
	*/
	public function getSelectSnippet() {
		$fields = [];

		foreach($this->fields as $key => $index) {
			array_push($fields, $key);
		}

		return implode(', ', $fields);
	}

	/**
	* insert into blah [RETURNS THIS PART] where foo
	* @param $postData
	* @return {String}
	*/
	public function getInsertSnippet($postData = []) {
		$fieldPieces = [];
		$valuePieces = [];
		$insert = '(';

		foreach ($postData as $key => $val) {
			if (isset($this->fields[$key])) {
				array_push($fieldPieces, $key);
				array_push($valuePieces, $this->fields[$key]->castValue($val));
			}
		}

		$insert .= implode(', ', $fieldPieces);
		$insert .= ') VALUES (';
		$insert .= implode(', ', $valuePieces);
		$insert .= ')';

		return $insert;
	}

	/**
	* update blah set [RETURNS THIS PART] where foo
	* @param $putData
	* @return {String}
	*/
	public function getUpdateSnippet($putData = []) {
		$valuePieces = [];

		// ensure each required value was provided
		foreach ($this->fields as $field) {
			if (!isset($putData[$field->name])) {
				throw new Exception("putData missing required field: $field->name");
			}
		}

		foreach ($putData as $key => $val) {
			if (isset($this->fields[$key])) {
				array_push($valuePieces, $key . ' = ' . $this->fields[$key]->castValue($val));
			}
		}

		return implode(', ', $valuePieces);
	}

	/**
	* @param $value
	* @param {String} $fieldName
	* @return value, casted according to the field's castValue method
	*/
	public function valueForField($value, $fieldName) {
		if (isset($this->fields[$fieldName])) {
			return $this->fields[$fieldName]->castValue($value);
		}
	}
}

$fieldDef1 = new FieldDefinition('field1');
$fieldDef2 = new FieldDefinition('field2', 'bool', true);
$fieldDef3 = new FieldDefinition('field3', 'int', true);
$fieldDef4 = new FieldDefinition('field4', 'float', true);

// $fc = new FieldCollection([$fieldDef1, $fieldDef2, $fieldDef3]);
$fc = new FieldCollection();
$fc->add($fieldDef1);
$fc->add($fieldDef2);
$fc->add($fieldDef3);
$fc->add($fieldDef4);

// $fc->describe();

$fakePost = [
	'a'=>1,
	'b'=>'1',
	'c'=>true,
	'field1'=>'asdf',
	'field2'=>false,
	'field3'=>'66',
	'field4'=>'66.33'
];

echo $fc->getSelectSnippet();
echo "\n";
echo $fc->getInsertSnippet($fakePost);
echo "\n";
echo $fc->getUpdateSnippet($fakePost);
echo "\n\n";

print_r($fc->fields);
echo "\n\n";

function b() {
	echo "\n";
}

// echo "$fc->fields->field1->name";
// print_r($fc->fields['field1']->type);
// print_r($fc->fields['field1']->castValue('asdf'));
// echo $fc->valueForField('asdf', 'field1');
// b();
// echo $fc->valueForField('asdf', 'field2');
// b();
// echo $fc->valueForField('asdf', 'field3');
// b();
// echo $fc->valueForField('asdf', 'field4');
// b();
// echo $fc->valueForField('asdf', 'field5');
// b();


$testValues = ['asdf', '666', '0', true, false];
foreach ($fc->fields as $field) {
	foreach ($testValues as $val) {
		echo "$val for field $field->name ($field->type)";
		b();
		echo $fc->valueForField($val, $field->name);
		b();
	}
}

?>