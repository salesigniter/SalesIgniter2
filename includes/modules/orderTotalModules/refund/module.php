<?php
class OrderTotalRefund extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Refund Amount');
		$this->setDescription('Order Refund');

		$this->init('refund');
	}

	public function process(array &$outputData) {
		global $order;

		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = $this->getText();
		$outputData['value'] = $this->getValue();
	}
}

?>