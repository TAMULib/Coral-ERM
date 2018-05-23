<?php
class TAMUExternalResourcePayment implements ResourcePaymentInterface {
	protected $fundCode;
	protected $purchaseOrder;
	protected $systemID;
	protected $vendorCode;

	public function __construct($fundCode=null,$purchaseOrder=null,$systemID=null,$vendorCode=null) {
		$this->setFundCode($fundCode);
		$this->setPurchaseOrder($purchaseOrder);
		$this->setSystemID($systemID);
		$this->setVendorCode($vendorCode);
	}

	public function getFundCode() {
		return $this->fundCode;
	}

	protected function setFundCode($fundCode) {
		$this->fundCode = $fundCode;
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

	protected function setVendorCode($vendorCode) {
		$this->vendorCode = $vendorCode;
	}

	public function getVendorCode() {
		return $this->vendorCode;
	}
}
?>