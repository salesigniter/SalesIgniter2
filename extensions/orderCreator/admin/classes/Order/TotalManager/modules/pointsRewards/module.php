<?php
class OrderCreatorTotalModulePointsRewards extends OrderTotalPointsRewards
{

	protected $_editable = false;

	public function isEditable()
	{
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale)
	{
		$discountAmount = 0;
		if (Session::exists('pointsRewards_points') === true && Session::get('pointsRewards_points') > 0){
			$purchaseTypes = array();
			$discountAmount = 0;
			foreach($Sale->ProductManager->getContents() as $SaleProduct){
				$ProductType = $SaleProduct->getProductTypeClass();
				if (method_exists($ProductType, 'getPurchaseTypeClass')){
					$PurchaseType = $ProductType->getPurchaseTypeClass();
					if (in_array($PurchaseType, $purchaseTypes) === false){
						$purchaseTypes[] = $PurchaseType;
					}
				}
			}

			if (empty($purchaseTypes) === false){
				foreach($purchaseTypes as $PurchaseType){
					$discountAmount += $this->getCustomerPRAmount($userAccount->getCustomerId(), $PurchaseType);
				}
			}
		}

		$this->setValue($discountAmount);
		$this->setText(sysCurrency::format(
			$discountAmount,
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		));

		$TotalModule = $Sale->TotalManager->get('total');
		$TotalModule->subtractFromValue($this->getValue());
	}

	public function loadSessionData($ModuleJson)
	{
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
