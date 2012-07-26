<?php
class OrderCreatorTotalModuleGiftCertificate extends OrderTotalGiftCertificate
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$NewGiftCertificateValue = 0;

		$this->setValue($NewGiftCertificateValue);

		$Total = $Sale->TotalManager->get('total');
		$Total->subtractFromValue($this->getValue());
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
