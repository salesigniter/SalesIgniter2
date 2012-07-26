<?php
class OrderCreatorTotalModuleRefund extends OrderTotalRefund
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$RefundTotal = 0;

		$this->setValue($RefundTotal);
		$this->setText(sysCurrency::format(
			$RefundTotal,
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
