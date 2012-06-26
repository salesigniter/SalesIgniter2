<?php
/**
 * Cronjob modules base class
 *
 * @package   Cronjob
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */
class CronjobModuleBase extends ModuleBase
{

	/**
	 * @param string $code
	 * @param bool   $forceEnable
	 * @param bool   $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false)
	{
		$this->import(new Installable);

		$this->setModuleType('cronjob');
		parent::init($code, $forceEnable, $moduleDir);
	}
}
