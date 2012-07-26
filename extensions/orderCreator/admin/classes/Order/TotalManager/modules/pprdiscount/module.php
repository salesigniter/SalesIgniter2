<?php
class OrderCreatorTotalModulePprdiscount extends OrderTotalPprdiscount
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$DiscountTotal = 0;

		$this->setValue($DiscountTotal);
		$this->setText(sysCurrency::format(
			$DiscountTotal,
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		));

		$Total = $Sale->TotalManager->get('total');
		$Total->subtractFromValue($this->getValue());
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
