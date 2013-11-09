<?php
	
	function connect(){
		$user = 'user';
		$server = 'localhost';
		$password = 'pw';
		$db = 'db';
		
		if($connection = @mysql_connect($server, $user, $password)){
			if(mysql_select_db($db)){
				return $connection;
			} else {
				return 'unable to select db';
			}
		} else {
			return 'unable to connect to db server';
		};
	};

?>