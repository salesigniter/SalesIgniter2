<?php
/*
	Product Purchase Type: Member Stream

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
if (!class_exists('PurchaseType_MembershipStream')){
	require(sysConfig::getDirFsCatalog() . 'extensions/streamProducts/purchaseTypeModules/membershipStream/module.php');
}

class OrderCreatorProductPurchaseTypeMembershipStream extends PurchaseType_MembershipStream {

	public function addToOrdersProductCollection(&$ProductObj, &$CollectionObj){
	}
}
?>