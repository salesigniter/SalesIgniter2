<?php
/*
	Product Purchase Type: New

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

/**
 * New Purchase Type
 * @package ProductPurchaseTypes
 */
class PurchaseType_new extends PurchaseTypeBase
{

	public function __construct($forceEnable = false) {
		$this->setTitle('New');
		$this->setDescription('New Products In Retail Or Oem Packaging');

		$this->init('new', $forceEnable);
	}
}

?>