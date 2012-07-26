<?php
class OrderCreatorTotalModuleCoupon extends OrderTotalCoupon
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
		$CouponTotal = 0;

		$this->setValue($CouponTotal);
		$this->setText('<span style="color:red">-' . sysCurrency::format(
			$CouponTotal,
			true,
			$Sale->getCurrency(),
			$Sale->getCurrencyValue()
		) . '</span>');

		$Total = $Sale->TotalManager->get('total');
		$Total->subtractFromValue($this->getValue());
	}

	/**
	 * @param array $ModuleJson
	 */
	public function loadSessionData(array $ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
