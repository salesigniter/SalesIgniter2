<?php
class OrderTotalCoupon extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Discount Coupons');
		$this->setDescription('Discount Coupon');

		$this->init('coupon');

		if ($this->isInstalled() === true){
			$this->credit_class = true;
			$this->include_shipping = $this->getConfigData('MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING');
			$this->include_tax = $this->getConfigData('MODULE_ORDER_TOTAL_COUPON_INC_TAX');
			$this->calculate_tax = $this->getConfigData('MODULE_ORDER_TOTAL_COUPON_CALC_TAX');
			$this->tax_class = $this->getConfigData('TAX_CLASS');
			$this->user_prompt = '';
			$this->header = sysLanguage::get('MODULE_ORDER_TOTAL_COUPON_HEADER');
		}
	}

	public function process(array &$outputData) {
		//if ($this->getValue() > 0){
			$outputData['title'] = $this->getTitle() . ':';
			$outputData['text'] = $this->getText();
			$outputData['value'] = $this->getValue();
		//}
	}
}
