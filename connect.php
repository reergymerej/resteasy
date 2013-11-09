<?php
	function connect(){
		try {
			$connection = @mysql_connect($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password']);

			if($connection){
				if(mysql_select_db($GLOBALS['db'])){
					return $connection;
				} else {
					return 'unable to select db';
				}
			} else {
				return 'unable to connect to db server';
			};

		} catch (Exception $e) {
			echo 'unable to connect';
		};
	};
?>