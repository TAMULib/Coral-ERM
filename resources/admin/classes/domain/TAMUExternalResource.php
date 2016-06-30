<?php
class TAMUExternalResource implements ResourceInterface {
	protected $title;

	public function setTitleText($titleText) {
		$this->titleText = $titleText;
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
}

?>
