<?php
class OrderTotalTax extends OrderTotalModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Tax');
		$this->setDescription('Order Tax');

		$this->init('tax');
	}

	public function getText(){
		global $currencies;
		return $currencies->format($this->getValue());
	}

	/**
	 * @TODO: Need to figure out a way to not have to go through the ProductManager for every order total!
	 *
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager $ProductManager){
		$NewTax = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewTax += $Product->getFinalPrice(true) * $Product->getTaxRate();
		}

		$this->setValue($NewTax);
	}

	/**
	 * @TODO: Need to figure out a way to not have to go through the ProductManager for every order total!
	 *
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager $ProductManager){
		$NewTax = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewTax += $Product->getFinalPrice(true) * $Product->getTaxRate();
		}

		$this->setValue($NewTax);
	}

	public function process(array &$outputData) {
		global $order, $currencies;
		reset($order->info['tax_groups']);
		foreach($order->info['tax_groups'] as $key => $value){
			if ($value > 0){
				$outputData['title'] = $key . ':';
				$outputData['text'] = $this->formatAmount($value);
				$outputData['value'] = $value;
			}
		}
	}
}

?>