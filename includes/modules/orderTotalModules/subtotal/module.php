<?php
class OrderTotalSubtotal extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Sub-Total');
		$this->setDescription('Order Sub-Total');

		$this->init('subtotal');
	}

	public function process(array &$outputData) {
		global $order;

		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = $this->formatAmount($order->info['subtotal']);
		$outputData['value'] = $order->info['subtotal'];
	}
}

?>