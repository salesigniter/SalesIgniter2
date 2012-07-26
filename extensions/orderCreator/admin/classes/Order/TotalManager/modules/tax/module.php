<?php
class OrderCreatorTotalModuleTax extends OrderTotalTax
{

	protected $_editable = false;

	/**
	 * @return bool
	 */
	public function isEditable()
	{
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale)
	{
		$NewTax = 0;
		foreach($Sale->ProductManager->getContents() as $Product){
			$NewTax += $Product->getPrice(true, true) - $Product->getPrice(true);
		}

		$this->setValue($NewTax);
		$this->setText(sysCurrency::format(
			$this->getValue(),
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		));

		$Total = $Sale->TotalManager->get('total');
		$Total->addToValue($this->getValue());
	}

	/**
	 * @param array $ModuleJson
	 */
	public function loadSessionData(array $ModuleJson)
	{
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
