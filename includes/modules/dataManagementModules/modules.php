<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Main data management modules class
 *
 * @package   DataManagement
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class DataManagementModules extends SystemModulesLoader
{

	/**
	 * @var string
	 */
	public static $dir = 'dataManagementModules';

	/**
	 * @var string
	 */
	public static $classPrefix = 'DataManagementModule';

	/**
	 * @static
	 * @param      $moduleName
	 * @param bool $ignoreStatus
	 * @return bool|ModuleBase|DataManagementModuleBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false){
		return parent::getModule($moduleName, $ignoreStatus);
	}

	/**
	 * @static
	 * @param bool $includeDisabled
	 * @return DataManagementModuleBase[]
	 */
	public static function getModules($includeDisabled = false)
	{
		return parent::getModules($includeDisabled);
	}
}

require(__DIR__ . '/base.php');