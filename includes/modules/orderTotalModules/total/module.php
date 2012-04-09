<?php
class OrderTotalTotal extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Total');
		$this->setDescription('Order Total');

		$this->init('total');
	}

	public function process(array &$outputData) {
		global $order;

		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = '<b>' . $this->formatAmount($order->info['total']) . '</b>';
		$outputData['value'] = $order->info['total'];
	}
}

?>