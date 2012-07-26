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
}
