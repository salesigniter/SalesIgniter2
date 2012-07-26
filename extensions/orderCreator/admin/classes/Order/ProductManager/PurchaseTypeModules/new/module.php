<?php
if (class_exists('PurchaseType_new') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/new/module.php');
}

class OrderCreatorPurchaseTypeNew extends PurchaseType_new {
}