<?php
class OrderCreatorTotalModuleTotal extends OrderTotalTotal
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$this->setText(sysCurrency::format(
			$this->getValue(),
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		));
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
