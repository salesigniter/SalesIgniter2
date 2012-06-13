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

	/**
	 * @TODO: Need to figure out a way to not have to go through the ProductManager for every order total!
	 *
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager $ProductManager){
		$NewTotal = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewTotal += $Product->getFinalPrice(true, true);
		}

		$this->setValue($NewTotal);
	}

	/**
	 * @TODO: Need to figure out a way to not have to go through the ProductManager for every order total!
	 *
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager $ProductManager){
		$NewTotal = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewTotal += $Product->getFinalPrice(true, true);
		}

		$this->setValue($NewTotal);
	}

	public function getText(){
		global $currencies;
		return '<b>' . $currencies->format(
			$this->getValue(),
			true,
			$this->getData('currency'),
			$this->getData('currency_value')
		) . '</b>';
	}

	public function process(array &$outputData) {
		global $order;

		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = $this->getText();
		$outputData['value'] = $this->getValue();
	}
}

?>