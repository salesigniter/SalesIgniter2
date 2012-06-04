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
 * Main accounts receivable print modules class
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivablePrintModules extends SystemModulesLoader
{

	/**
	 * @var string
	 */
	public static $dir = 'accountsReceivableModules';

	/**
	 * @var string
	 */
	public static $classPrefix = 'AccountsReceivableModulePrintModule';

}
