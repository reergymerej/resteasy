<?php

$GLOBALS['api_file'] = basename(__FILE__, '.php');

require('config.php');
require('Request.php');
require('Response.php');
require('RestEasy.php');

$request = new Request();
$response = new Response($request);

// find the class for this noun
$file = $GLOBALS['objects_dir'] . DIRECTORY_SEPARATOR; 
$file .= $request->noun . '.php';
if (file_exists($file)) {
	require($file);

	// intantiate the class defined in file
	$rest = new Rest();
	$rest->setResponse($response);

	// process the request
	$rest->process($request);

} else {
	if ($request->noun && $request->noun !== 'php') {
		$response->setResponseCode(400);
		$response->addMessage("The definition for this object could not be found.");
		$response->addMessage("Make sure $file exists.");
	}
}

$response->send();
?>