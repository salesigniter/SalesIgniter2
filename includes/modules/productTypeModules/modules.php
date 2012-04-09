<?php
if (!class_exists('ProductTypeBase')){
	require(dirname(__FILE__) . '/base.php');
}

class ProductTypeModules extends SystemModulesLoader {
	public static $dir = 'productTypeModules';
	public static $classPrefix = 'ProductType';
	public static $alwaysLoadFresh = true;
}
?>