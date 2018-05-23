<?php
class TAMUExternalIsbnOrIssn implements IsbnOrIssnInterface {
	protected $isbnOrIssn;

	public function __construct($isbnOrIssn) {
		$this->setIsbnOrIssn($isbnOrIssn);
	}

	public function setIsbnOrIssn($isbnOrIssn) {
		$this->isbnOrIssn = $isbnOrIssn;
	}

	public function getIsbnOrIssn() {
		return $this->isbnOrIssn;
	}
}

?>
