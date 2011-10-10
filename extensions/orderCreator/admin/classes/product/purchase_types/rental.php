<?php
/*
	Product Purchase Type: Rental

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
if (!class_exists('PurchaseType_Rental')){
	require(sysConfig::getDirFsCatalog() . 'extensions/rentalProducts/purchaseTypeModules/rental/module.php');
}

class OrderCreatorRentalMembershipProduct extends PurchaseType_Rental {

	public function addToOrdersProductCollection(&$ProductObj, &$CollectionObj){
	}
}
?>