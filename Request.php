<?php
require_once('Messageable.php');
class Request extends Messageable {
	public $verb;
	public $url;
	public $noun;
	public $id;
	public $parts;
	public $params = [];
	public $payload;

	public function __construct() {
		$apiFile = $GLOBALS['api_file'];
		
		$this->verb = $_SERVER['REQUEST_METHOD'];
		$this->url = explode('?', urldecode($_SERVER['REQUEST_URI']))[0];
		$this->parts = explode('/', substr( $this->url, strPos($this->url, $apiFile) + ( strlen($apiFile) + 1 ) ));
		$this->noun = $this->parts[0];

		if ($this->noun === 'php') {
			$this->setResponseCode(400);
			$this->addMessage("Don't include .php in the url.");
		} else if ($this->noun === '') {
			$this->setResponseCode(400);
			$this->addMessage("We don't know what object class you're looking for.");
			$this->addMessage("Make sure to include the noun in the url.");
		} else {

			if (isset($this->parts[1])) {
				$this->id = $this->parts[1];
			}
			$this->params = $_GET;
			$this->payload = file_get_contents("php://input");
			$this->payload = json_decode($this->payload);
		}
	}
}
?>