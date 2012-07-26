<?php
class OrderTotalLoworderfee extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Low Order Fee');
		$this->setDescription('Low Order Fee');

		$this->init('loworderfee');

		if ($this->isInstalled() === true){
			$this->taxClass = $this->getConfigData('TAX_CLASS');
			$this->allowFees = $this->getConfigData('MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE');
			$this->feesDestination = $this->getConfigData('MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION');
			$this->lowOrderAmount = $this->getConfigData('MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER');
			$this->lowOrderFee = $this->getConfigData('MODULE_ORDER_TOTAL_LOWORDERFEE_FEE');
		}
	}

	public function process(array &$outputData) {
		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = sysCurrency::format($this->getValue());
		$outputData['value'] = $this->getValue();
	}
}

?>