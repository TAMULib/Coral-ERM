<?php
class TAMUExternalResourceRepo implements ResourceRepoInterface {
	private $apiUrl;
	private $api;
	private $resourceObject;
	private $isbnOrIssnObjects;
	private $resourcePaymentObjects;

	public function __construct($po) {
		$config = new Configuration();
		$this->setApiUrl($config->settings->externalResourceRepoUrl);
		$remoteData = file_get_contents($this->getApiUrl()."?po={$po}");
		$data = json_decode($remoteData,true);
		if ($data) {
			//currently, we only handle one RP on creation, but in the future it could be more
			$this->addResourcePaymentObject(new TAMUExternalResourcePayment($data['fund'],$po,$data['bib_id']));
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

	public function getResourcePaymentObjects() {
		return $this->resourcePaymentObjects;
	}

	private function addResourcePaymentObject($resourcePaymentObject) {
		$this->resourcePaymentObjects[] = $resourcePaymentObject;
	}

	protected function setApiUrl($apiUrl) {
		$this->apiUrl = $apiUrl;
	}

	protected function getApiUrl() {
		return $this->apiUrl;
	}

}

?>
