<?php
class Request {
	public $verb;
	public $url;
	public $noun;
	public $id;
	public $parts;
	public $params = [];
	public $payload;

	public function __construct() {
		$this->verb = $_SERVER['REQUEST_METHOD'];
		$this->url = explode('?', urldecode($_SERVER['REQUEST_URI']))[0];

		// $apiFile = basename(__FILE__, '.php');
		// TODO pull this from config
		$apiFile = 'api';

		$this->parts = explode('/', substr( $this->url, strPos($this->url, $apiFile) + ( strlen($apiFile) + 1 ) ));

		$this->noun = $this->parts[0];
		if (isset($this->parts[1])) {
			$this->id = $this->parts[1];
		}

		$this->params = $_GET;
		$this->payload = file_get_contents("php://input");
		$this->payload = json_decode($this->payload);
	}
}
?>