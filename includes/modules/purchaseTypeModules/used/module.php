<?php
/*
	Product Purchase Type: Used

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

/**
 * Used Purchase Type
 * @package ProductPurchaseTypes
 */
class PurchaseType_used extends PurchaseTypeBase
{

	public function __construct($forceEnable = false) {
		$this->setTitle('Used');
		$this->setDescription('Used Products Such As Open Box Or Returned Products');

		$this->init('used', $forceEnable);
	}
}

?>