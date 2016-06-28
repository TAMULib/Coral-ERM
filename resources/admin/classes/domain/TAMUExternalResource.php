<?php

class TAMUExternalResource implements ExternalResource {
	private $apiUrl;
	private $api;
	protected $title;

	public function __construct($po) {
		$config = new Configuration();
		$this->setApiUrl($config->settings->resourceDataUrl);
		$remoteData = file_get_contents($this->getApiUrl()."?po={$po}");
		$data = json_decode($remoteData,true);
		if ($data) {
			$this->setTitle($data['bib_title']);
		}
	}

	protected function setApiUrl($apiUrl) {
		$this->apiUrl = $apiUrl;
	}

	protected function getApiUrl() {
		return $this->apiUrl;
	}

	public function getTitle() {
		return $this->title;
	}

	protected function setTitle($title) {
		$this->title = $title;
	}

	public function getCoralMapping() {
		return array(
					array("titleText"=>"getTitle"));
	}
}

?>
