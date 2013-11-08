<?php

//	GET = read
require 'connect.php';
$con = connect();

$noun = $request[0];
$user_id = 0;

//	select from DB
$sql = "SELECT * FROM $noun"; 

if (count($request) > 1 && $request[1] !== '') {
	$id = mysql_real_escape_string($request[1]);
	$sql .= " WHERE id = $id";
}
$result = mysql_query($sql);

if(mysql_error()){
	return;
};

$rows = [];
while ($row = mysql_fetch_assoc($result)) {

	// HACK convert ints back into ints
	$fieldCount = mysql_num_fields($result);
	for ($i = 0; $i < $fieldCount; $i++) {
		$fieldType = mysql_field_type($result, $i);
		if ($fieldType === 'int') {
			$fieldValue = $row[mysql_field_name($result, $i)];
			$row[mysql_field_name($result, $i)] = $fieldValue + 0;
		}
	}

	array_push($rows, $row);
}
echo json_encode($rows);
?>