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