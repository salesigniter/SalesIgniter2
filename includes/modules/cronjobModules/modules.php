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
 * Main cron job modules class
 *
 * @package   Cronjob
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class CronJobModules extends SystemModulesLoader
{

	/**
	 * @var string
	 */
	public static $dir = 'cronjobModules';

	/**
	 * @var string
	 */
	public static $classPrefix = 'CronjobModule';

	/**
	 * @static
	 * @param      $moduleName
	 * @param bool $ignoreStatus
	 * @return bool|ModuleBase|CronjobModuleBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false)
	{
		return parent::getModule($moduleName, $ignoreStatus);
	}

	/**
	 * @static
	 * @param bool $includeDisabled
	 * @return CronjobModuleBase[]
	 */
	public static function getModules($includeDisabled = false)
	{
		return parent::getModules($includeDisabled);
	}
}

require(__DIR__ . '/base.php');