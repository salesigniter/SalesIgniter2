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

	/**
	 * @TODO: Need to figure out a way to not have to go through the ProductManager for every order total!
	 *
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager $ProductManager){
		$NewSubTotal = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewSubTotal += $Product->getFinalPrice(true);
		}

		$this->setValue($NewSubTotal);
	}

	/**
	 * @TODO: Need to figure out a way to not have to go through the ProductManager for every order total!
	 *
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager $ProductManager){
		$NewSubTotal = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewSubTotal += $Product->getFinalPrice(true);
		}

		$this->setValue($NewSubTotal);
	}

	public function process(array &$outputData) {
		global $order;

		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = $this->formatAmount($order->info['subtotal']);
		$outputData['value'] = $order->info['subtotal'];
	}
}

?>