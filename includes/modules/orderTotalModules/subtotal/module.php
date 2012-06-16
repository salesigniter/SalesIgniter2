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
		//echo __FILE__ . '::' . __LINE__  . '<br>';
		//echo '<div style="margin-left: 15px;">';
		$NewSubTotal = 0;
		foreach($ProductManager->getContents() as $Product){
			$NewSubTotal += $Product->getFinalPrice(true);
			//echo __FILE__ . '::' . __LINE__  . '::' . $Product->getFinalPrice(true) . '<br>';
		}

		$this->setValue($NewSubTotal);
		//echo $this->getCode() . '::' . $NewSubTotal . '<br>';
		//echo '</div>';
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
		//echo __FILE__ . '::' . __LINE__ . '::' . $NewSubTotal;
	}

	public function process(array &$outputData) {
		global $order;

		$outputData['title'] = $this->getTitle() . ':';
		$outputData['text'] = $this->getValue();
		$outputData['value'] = $this->getValue();
	}
}

?>