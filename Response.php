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
			$this->body .= $messages;
		}

		if ($GLOBALS['dev_mode']) {
			if ($messages = $this->getMessages()) {
				$this->body .= $messages;
			}
		}

		header("HTTP/1.0 {$this->getHeader()}");
		echo $this->body;
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