<?php
class TAMUExternalResourcePayment implements ResourcePaymentInterface {
	protected $fundName;
	protected $purchaseOrder;

	public function __construct($fundName=null,$purchaseOrder=null) {
		$this->setFundName($fundName);
		$this->setPurchaseOrder($purchaseOrder);
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
}
?>