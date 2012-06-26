<?php
if (!class_exists('ProductTypeBase')){
	require(dirname(__FILE__) . '/base.php');
}

class ProductTypeModules extends SystemModulesLoader {
	public static $dir = 'productTypeModules';
	public static $classPrefix = 'ProductType';
	public static $alwaysLoadFresh = true;

	/**
	 * @static
	 * @param string     $moduleName
	 * @param bool       $ignoreStatus
	 * @return bool|ModuleBase|ProductTypeBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false)
	{
		return parent::getModule($moduleName, $ignoreStatus);
	}
}
?>