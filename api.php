<?php

require('config.php');
require('Field.php');
require('Request.php');

$request = new Request();

// find the class for this noun
$file = $request->noun . '.php';
if (file_exists($file)) {
	require($file);
} else {
	header('HTTP/1.0 404 Not Found');
}

// intantiate the class defined in file
$rest = new Rest();

// process the request
$rest->process($request);



class RestEasy {
	public $fields;
	public $request;

	/**
	* @param {Field[]} [$fields]
	*/	
	public function __construct($fields = []) {
		if ($fields) {
			foreach ($fields as $key => $value) {
				$this->fields[$value->name] = $value;
			}
		}
	}

	/**
	* @param {Field/String} $field
	* @param {String} [$type]
	* @param {Boolean} [$required]
	*/
	public function add($field, $type = 'string', $required = false) {
		if (!is_a($field, 'Field')) {
			switch ($type) {
				case 'bool':
					$field = new BooleanField($field, $required);
					break;
				case 'number':
					$field = new NumberField($field, $required);
					break;
				case 'string':
					$field = new TextField($field, $required);
					break;
				default:
					$field = new Field($field, $type, $required);
			}
		}

		$this->fields[$field->name] = $field;
	}

	// DEBUG
	public function describe() {
		echo "fields in this collection:\n";
		if ($this->fields) {
			foreach($this->fields as $key => $field) {
				$field->describe();
			}
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
	public function getInsertSnippet($postData = null) {
		if (is_null($postData)) {
			$postData = $this->request->payload;
		}

		$fieldPieces = [];
		$valuePieces = [];
		$insert = '(';

		foreach ($postData as $key => $val) {
			if (isset($this->fields[$key])) {
				array_push($fieldPieces, $key);
				array_push($valuePieces, $this->fields[$key]->castForSQL($val));
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
	public function getUpdateSnippet($putData = null) {

		// TODO We should probably ensure there is an idField value here
		// or at least some parameters.

		if (is_null($putData)) {
			$putData = $this->request->payload;
		}

		$valuePieces = [];

		// ensure each required value was provided
		foreach ($this->fields as $field) {
			if ($field->required) {
				if (!isset($putData[$field->name])) {
					throw new Exception("putData missing required field: $field->name");
				}
			}
		}

		foreach ($putData as $key => $val) {
			if (isset($this->fields[$key])) {
				array_push($valuePieces, $key . ' = ' . $this->fields[$key]->castForSQL($val));
			}
		}

		return implode(', ', $valuePieces);
	}

	/**
	* @param $value
	* @param {String} $fieldName
	* @param {Boolean} [$forSQL=true]
	* @return value, casted according to the field's castForSQL method
	*/
	public function valueForField($value, $fieldName, $forSQL = true) {
		if (isset($this->fields[$fieldName])) {
			if ($forSQL) {
				return $this->fields[$fieldName]->castForSQL($value);
			} else {
				return $this->fields[$fieldName]->castForJSON($value);
			}
		}
	}

	/**
	* @param {Request} $request
	*/
	public function process($request) {
		$this->request = $request;
		$sql = $this->getSQL();
		
		require 'connect.php';
		$con = connect();
		
		// TODO encode all these.
		// $sql = mysql_real_escape_string($sql);
		$result = mysql_query($sql);

		if(mysql_error()){
			echo $sql . "\n\n";
			// header('HTTP/1.0 404 Not Found');
			echo mysql_error();
			return;
		} else {
			$rows = mysql_affected_rows();
			if($rows === 0){
				header('HTTP/1.0 404 Not Found');
				return;
			};

			$response = '';
			switch ($this->request->verb) {
				case 'POST':
					$response = '{"' . $this->idField . '":' . mysql_insert_id() . '}';
					break;
				case 'PUT':
					$response = mysql_affected_rows();
					break;
				default:
					if (mysql_num_rows($result) > 0) {
						$rows = [];
						while ($row = mysql_fetch_assoc($result)) {
							array_push($rows, $this->castRow($row));
						}
						$response = json_encode($rows);
					}
			}

			echo $response;

			// TODO add the proper headers
			// header('HTTP/1.0 204 No Content');
		}
		mysql_close($con);

	}

	/**
	* @return {String}
	*/
	public function getSQL() {
		$sql = '';

		switch ($this->request->verb) {
			//	create
			case 'POST':
				$sql .= "INSERT INTO $this->table "
					. $this->getInsertSnippet();
				break;

			//	read
			case 'GET':
				$sql .= 'SELECT ' 
					. $this->getSelectSnippet()
					. " FROM $this->table";
				break;

			//	update
			case 'PUT':
				$sql .= "UPDATE $this->table SET "
					. $this->getUpdateSnippet();
				break;

			//	delete
			case 'DELETE':
				$sql .= "DELETE FROM $this->table";
				break;

			default:
				echo "What the hell are you talking about?";
				break;
		}

		return $sql .= $this->getWhereClause() . $this->getOrderByClause();
	}

	/**
	* @return {String}
	*/
	public function getWhereClause() {
		$where = '';
		
		$id = $this->request->id;
		if ($id) {
			$where .= " WHERE $this->idField = " 
				. $this->valueForField($id, $this->idField);
		}

		return $where;
	}

	/**
	* @return {String}
	*/
	public function getOrderByClause() {

	}

	/**
	*
	*/
	public function castRow($row) {
		foreach ($row as $key => $val) {
			$row[$key] = $this->valueForField($val, $key, false);
		}
		return $row;
	}
}
?>