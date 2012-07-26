<?php
class OrderCreatorTotalModuleShipping extends OrderTotalShipping
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$NewShippingTotal = 0;

		$this->setValue($NewShippingTotal);
		$this->setText(sysCurrency::format(
			$this->getValue(),
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		));

		$TotalModule = $Sale->TotalManager->get('total');
		$Total->addToValue($this->getValue());
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
