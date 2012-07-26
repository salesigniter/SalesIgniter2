<?php
class OrderTotalPaymentfee extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Payment Fee');
		$this->setDescription('Payment Fee');

		$this->init('paymentfee');

		if ($this->isInstalled() === true){
			$this->showPaymentFee = $this->getConfigData('MODULE_ORDER_TOTAL_PAYMENTFEE_STATUS');
			$this->allowPaymentFee = $this->getConfigData('MODULE_ORDER_TOTAL_PAYMENTFEE_ENABLE');
		}
	}

	public function process(array &$outputData) {
		if ($this->getValue() > 0 && $this->showPaymentFee == 'True'){
			$outputData['title'] = $this->getTitle() . ':';
			$outputData['text'] = sysCurrency::format($this->getValue());
			$outputData['value'] = $this->getValue();
		}
	}
}

?>