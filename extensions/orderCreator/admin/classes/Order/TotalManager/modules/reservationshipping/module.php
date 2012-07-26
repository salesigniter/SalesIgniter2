<?php
class OrderCreatorTotalModuleReservationshipping extends OrderTotalReservationshipping
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$ShippingTotal = 0;

		$this->setValue($ShippingTotal);
		$this->setText(sysCurrency::format(
			$ShippingTotal,
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
