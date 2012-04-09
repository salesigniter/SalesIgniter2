<?php
if (!class_exists('PurchaseTypeBase')){
	require(dirname(__FILE__) . '/base.php');
}

class PurchaseTypeModules extends SystemModulesLoader {
	public static $dir = 'purchaseTypeModules';
	public static $classPrefix = 'PurchaseType_';
	public static $alwaysLoadFresh = true;
}
?>