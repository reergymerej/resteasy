<?php
class Field {

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
				// is_null($value);
				$value += 0;
				break;
			case 'bool':
				// if (is_numeric($value)) {
				// 	$value = $value === 1;
				// } else {
				$value = $value ? 1 : 0;
				break;
			case 'string':
			default:
				$value = "'$value'";
				break;
		}

		return $value;
	}

	public function castForSQL($value) {
		return $value;
	}

	public function castForJSON($value) {
		return $value;
	}
}

class TextField extends Field {
	public function __construct($fieldName, $required = false) {
		parent::__construct($fieldName, 'string', $required);
	}

	public function castForSQL($value) {
		return "'$value'";
	}
}

class NumberField extends Field {
	public function __construct($fieldName, $required = false) {
		parent::__construct($fieldName, 'number', $required);
	}

	public function castForSQL($value) {
		return $value + 0;
	}

	public function castForJSON($value) {
		return $value + 0;
	}
}

class BooleanField extends Field {
	public function __construct($fieldName, $required = false) {
		parent::__construct($fieldName, 'bool', $required);
	}

	public function castForSQL($value) {
		return $value ? 1 : 0;
	}

	public function castForJSON($value) {
		return $value == true;
	}
}
?>