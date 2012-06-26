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
 * Main accounts receivable modules class
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModules extends SystemModulesLoader
{

	/**
	 * @var string
	 */
	public static $dir = 'accountsReceivableModules';

	/**
	 * @var string
	 */
	public static $classPrefix = 'AccountsReceivableModule';

	/**
	 * @static
	 * @param      $moduleName
	 * @param bool $ignoreStatus
	 * @return bool|ModuleBase|AccountsReceivableModule
	 */
	public static function getModule($moduleName, $ignoreStatus = false)
	{
		return parent::getModule($moduleName, $ignoreStatus);
	}

	/**
	 * @static
	 * @param bool $includeDisabled
	 * @return AccountsReceivableModule[]
	 */
	public static function getModules($includeDisabled = false)
	{
		return parent::getModules($includeDisabled);
	}
}

require(__DIR__ . '/base.php');