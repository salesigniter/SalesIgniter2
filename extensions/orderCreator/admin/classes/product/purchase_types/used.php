<?php
/*
	Product Purchase Type: Used

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
if (!class_exists('PurchaseType_used')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/used/module.php');
}

class OrderCreatorProductPurchaseTypeUsed extends PurchaseType_used {

	public function addToOrdersProductCollection(&$ProductObj, &$CollectionObj){
		if(!isset($_POST['estimateOrder'])){
			$this->inventoryCls->addStockToCollection($ProductObj, $CollectionObj);
		}
	}
}
?>