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
 * @package   EmailModules
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class EmailModules extends SystemModulesLoader
{

	/**
	 * @var string
	 */
	public static $dir = 'emailModules';

	/**
	 * @var string
	 */
	public static $classPrefix = 'EmailModule';

	/**
	 * @static
	 * @param      $moduleName
	 * @param bool $ignoreStatus
	 * @return bool|ModuleBase|EmailModuleBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false){
		return parent::getModule($moduleName, $ignoreStatus);
	}

	/**
	 * @static
	 * @param bool $includeDisabled
	 * @return EmailModuleBase[]
	 */
	public static function getModules($includeDisabled = false)
	{
		return parent::getModules($includeDisabled);
	}
}

require(__DIR__ . '/base.php');