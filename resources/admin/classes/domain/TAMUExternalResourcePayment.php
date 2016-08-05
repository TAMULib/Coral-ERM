<?php
class TAMUExternalResourcePayment implements ResourcePaymentInterface {
	protected $fundName;
	protected $purchaseOrder;
	protected $systemID;

	public function __construct($fundName=null,$purchaseOrder=null,$systemID=null) {
		$this->setFundName($fundName);
		$this->setPurchaseOrder($purchaseOrder);
		$this->setSystemID($systemID);
	}

	public function getFundName() {
		return $this->fundName;
	}

	protected function setFundName($fundName) {
		$this->fundName = $fundName;
	}

	public function getPurchaseOrder() {
		return $this->purchaseOrder;
	}

	protected function setPurchaseOrder($purchaseOrder) {
		$this->purchaseOrder = $purchaseOrder;
	}

	public function getSystemID() {
		return $this->systemID;
	}

	protected function setSystemID($systemID) {
		$this->systemID = $systemID;
	}
}
?>