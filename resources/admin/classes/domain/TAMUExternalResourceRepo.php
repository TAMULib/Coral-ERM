<?php
class TAMUExternalResourceRepo implements ResourceRepoInterface {
	private $apiUrl;
	private $api;
	private $resourceObject;
	private $isbnOrIssnObjects;

	public function __construct($po) {
		$config = new Configuration();
		$this->setApiUrl($config->settings->externalResourceRepoUrl);
		$remoteData = file_get_contents($this->getApiUrl()."?po={$po}");
		$data = json_decode($remoteData,true);
		if ($data) {
			$this->setResourceObject(new TAMUExternalResource());
			$this->getResourceObject()->setTitleText($data['bib_title']);
			if (!is_array($data['bib_isbn'])) {
				$this->addIsbnOrIssnObject(new TAMUExternalIsbnOrIssn($data['bib_isbn']));
			}
			if (!is_array($data['bib_issn'])) {
				$this->addIsbnOrIssnObject(new TAMUExternalIsbnOrIssn($data['bib_issn']));
			}
		}
	}

	public function getResourceObject() {
		return $this->resourceObject;
	}

	private function setResourceObject($resourceObject) {
		$this->resourceObject = $resourceObject;
	}

	public function getIsbnOrIssnObjects() {
		return $this->isbnOrIssnObjects;
	}

	private function addIsbnOrIssnObject($isbnOrIssnObject) {
		$this->isbnOrIssnObjects[] = $isbnOrIssnObject;
	}

	protected function setTitle($title) {
		$this->title = $title;
	}

	public function getTitleText() {
		return $this->titleText;
	}

	public function getDescriptionText() {
		return null;
	}

	public function getOrderNumber() {
		return null;
	}

	public function getSystemNumber() {
		return null;
	}

	public function getProviderText() {
		return null;
	}

	public function getCoverageText() {
		return null;
	}

	protected function setApiUrl($apiUrl) {
		$this->apiUrl = $apiUrl;
	}

	protected function getApiUrl() {
		return $this->apiUrl;
	}

}

?>
