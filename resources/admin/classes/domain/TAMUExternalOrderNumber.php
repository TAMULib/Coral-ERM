<?php
class TAMUExternalOrderNumber implements OrderNumberInterface {
	protected $orderNumber;
	protected $dateAdded;

	public function __construct($orderNumber) {
		$this->setOrderNumber($orderNumber);
	}

	public function getOrderNumber() {
		return $this->orderNumber;
	}

	protected function setOrderNumber($orderNumber) {
		$this->orderNumber = $orderNumber;
	}

	public function getDateAdded() {
		return $this->dateAdded;
	}

	protected function setDateAdded($dateAdded) {
		$this->dateAdded = $dateAdded;
	}
}
?>