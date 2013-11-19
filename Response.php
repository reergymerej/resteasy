<?php
require_once('Messageable.php');
class Response extends Messageable {

	private $body;
	private $request;

	/**
	* @param {Request} $req
	*/
	public function __construct($req){
		$this->body = '';
		$this->request = $req;
	}

	public function send() {
		if ($messages = $this->request->getMessages()) {
			if ($GLOBALS['dev_mode']) {
				$this->body .= $messages . "\n";
			}
		}

		if ($GLOBALS['dev_mode']) {
			if ($messages = $this->getMessages()) {
				$this->body .= $messages;
			}
		}


		$header = "HTTP/1.0 {$this->getHeader()}";
		if (!strpos($header, '204')) {
			header($header);
			echo $this->body;
		} else {
			// TODO 204 responses seem to break header().
		}
	}

	private function getHeader() {
		$header = $this->request->getResponseCode();
		if (!$header) {
			$header = $this->getResponseCode();
		}
		return $header;
	}

	public function setBody($msg) {
		$this->body = $msg;
	}
}
?>