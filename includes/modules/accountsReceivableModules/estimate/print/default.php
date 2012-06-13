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
 * Accounts receivable estimate print module
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModulesEstimatePrintDefault
{

	/**
	 * @static
	 * @return array
	 */
	public static function getModuleInfo(){
		return array(
			'code' => 'default',
			'title' => 'Estimate Default'
		);
	}

	/**
	 * @static
	 * @return mixed
	 */
	public static function getButton(){
		return htmlBase::newElement('button')
			->setType('submit')
			->setName('print')
			->val('default')
			->usePreset('print')
			->setText('Print Estimate');
	}

	/**
	 * @static
	 * @return mixed
	 */
	public static function getPrintTemplate(){
		$PrintTemplate = Doctrine_Query::create()
			->from('TemplateManagerLayouts')
			->where('page_type = ?', 'print')
			->andWhere('layout_settings LIKE ?', '%printModules%estimate%')
			->fetchOne();
		return $PrintTemplate;
	}
}