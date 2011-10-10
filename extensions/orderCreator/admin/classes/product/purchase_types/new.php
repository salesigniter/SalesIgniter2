<?php
/*
	Product Purchase Type: New

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

if (!class_exists('PurchaseType_new')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/new/module.php');
}

class OrderCreatorProductPurchaseTypeNew extends PurchaseType_new {

	public function addToOrdersProductCollection(&$ProductObj, &$CollectionObj){
		if(!isset($_POST['estimateOrder'])){
			$this->inventoryCls->addStockToCollection($ProductObj, $CollectionObj);
		}
	}
}
?>