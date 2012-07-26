<?php
class OrderCreatorTotalModuleSubtotal extends OrderTotalSubtotal
{

	protected $_editable = false;

	/**
	 * @return bool
	 */
	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$NewSubTotal = 0;
		foreach($Sale->ProductManager->getContents() as $Product){
			$NewSubTotal += $Product->getPrice(true);
		}

		$this->setValue($NewSubTotal);
		$this->setText(sysCurrency::format(
			$this->getValue(),
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		));

		$Total = $Sale->TotalManager->get('total');
		$Total->setValue($this->getValue());
	}

	/**
	 * @param array $ModuleJson
	 */
	public function loadSessionData(array $ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
